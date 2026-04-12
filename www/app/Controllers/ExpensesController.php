<?php

namespace App\Controllers;

use App\Models\ExpenseModel;
use App\Models\UserHomesModel;
use App\Models\SettlementModel;

class ExpensesController extends BaseController
{
    public function index()
    {
        if ($this->requireHome()) return;

        $homeId          = session()->get('home_id');
        $userId          = session()->get('user_id');
        $filterMonth     = $this->request->getGet('month') ?? date('Y-m');
        $filterCategory  = $this->request->getGet('category') ?? '';
        $filterPaidBy    = $this->request->getGet('paid_by') ?? '';

        [$year, $month] = explode('-', $filterMonth);

        $expenseModel = new ExpenseModel();
        $uhModel      = new UserHomesModel();

        $query = $expenseModel
            ->select('expenses.*, users.username AS paid_by_name')
            ->join('users', 'users.id = expenses.paid_by')
            ->where('expenses.home_id', $homeId)
            ->where('YEAR(expenses.date)', $year)
            ->where('MONTH(expenses.date)', $month);

        if ($filterCategory) $query->where('expenses.category', $filterCategory);
        if ($filterPaidBy)   $query->where('expenses.paid_by', $filterPaidBy);

        $expenses = $query->orderBy('expenses.date', 'DESC')->findAll();

        $members     = $uhModel->getMembersOfHome($homeId);
        $memberCount = count($members);

        $monthTotal = array_sum(array_column($expenses, 'amount'));
        $myShare    = $memberCount > 0 ? ($monthTotal / $memberCount) : 0;

        $myPaid    = array_sum(array_map(
            fn($e) => $e['paid_by'] == $userId ? $e['amount'] : 0,
            $expenses
        ));
        $myBalance = $myPaid - $myShare;

        if ($this->isApi()) {
            return $this->apiOk([
                'expenses'   => $expenses,
                'members'    => $members,
                'monthTotal' => (float) $monthTotal,
                'myPaid'     => (float) $myPaid,
                'myShare'    => (float) $myShare,
                'myBalance'  => (float) $myBalance,
            ]);
        }

        return view('expenses/index', [
            'pageTitle'      => 'Gastos Compartidos',
            'pageSubtitle'   => 'Registro de gastos del hogar',
            'activeNav'      => 'expenses',
            'expenses'       => $expenses,
            'members'        => $members,
            'monthTotal'     => $monthTotal,
            'myPaid'         => $myPaid,
            'myShare'        => $myShare,
            'myBalance'      => $myBalance,
            'filterMonth'    => $filterMonth,
            'filterCategory' => $filterCategory,
            'filterPaidBy'   => $filterPaidBy,
        ]);
    }

    public function store()
    {
        if ($this->requireHome()) return;

        $rules = [
            'title'   => 'required|min_length[2]',
            'amount'  => 'required|decimal|greater_than[0]',
            'paid_by' => 'required|is_natural_no_zero',
            'date'    => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $receiptFile = null;
        $file = $this->request->getFile('receipt_image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName     = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads', $newName);
            $receiptFile = $newName;
        }

        $expenseModel = new ExpenseModel();
        $newId = $expenseModel->insert([
            'home_id'       => session()->get('home_id'),
            'title'         => $this->request->getPost('title'),
            'description'   => $this->request->getPost('description'),
            'amount'        => (float) $this->request->getPost('amount'),
            'category'      => $this->request->getPost('category') ?? 'other',
            'paid_by'       => $this->request->getPost('paid_by'),
            'date'          => $this->request->getPost('date'),
            'receipt_image' => $receiptFile,
        ]);

        if ($this->isApi()) {
            return $this->apiOk(['expense' => $expenseModel->find($newId)]);
        }

        return redirect()->to('/expenses')->with('success', 'Gasto añadido correctamente.');
    }

    public function update(int $id)
    {
        if ($this->requireHome()) return;

        $userId       = session()->get('user_id');
        $expenseModel = new ExpenseModel();
        $expense      = $expenseModel->find($id);

        if (!$expense || $expense['home_id'] != session()->get('home_id')) {
            return redirect()->back()->with('error', 'Gasto no encontrado.');
        }

        $expenseModel->update($id, [
            'title'    => $this->request->getPost('title'),
            'amount'   => (float) $this->request->getPost('amount'),
            'category' => $this->request->getPost('category'),
            'date'     => $this->request->getPost('date'),
        ]);

        if ($this->isApi()) return $this->apiOk();

        return redirect()->to('/expenses')->with('success', 'Gasto actualizado.');
    }

    public function delete(int $id)
    {
        if ($this->requireHome()) return;

        $homeId       = session()->get('home_id');
        $expenseModel = new ExpenseModel();
        $expense      = $expenseModel->find($id);

        if (!$expense || $expense['home_id'] != $homeId) {
            return redirect()->back()->with('error', 'Gasto no encontrado.');
        }


        // Remove receipt file if exists
        if ($expense['receipt_image']) {
            $path = ROOTPATH . 'public/uploads/' . $expense['receipt_image'];
            if (file_exists($path)) unlink($path);
        }

        $expenseModel->delete($id);

        if ($this->isApi()) return $this->apiOk();

        return redirect()->to('/expenses')->with('success', 'Gasto eliminado.');
    }

    public function balance()
    {
        if ($this->requireHome()) return;

        $homeId       = session()->get('home_id');
        $expenseModel = new ExpenseModel();
        $settleModel  = new SettlementModel();
        $uhModel      = new UserHomesModel();

        $members     = $uhModel->getMembersOfHome($homeId);
        $memberCount = count($members);

        // Total all-time expenses (unsettled)
        $totalAll = $expenseModel
            ->selectSum('amount')
            ->where('home_id', $homeId)
            ->first()['amount'] ?? 0;

        $fairShare = $memberCount > 0 ? $totalAll / $memberCount : 0;

        // Per-member balance
        $memberBalances = [];
        foreach ($members as $m) {
            $paid = $expenseModel
                ->selectSum('amount')
                ->where('home_id', $homeId)
                ->where('paid_by', $m['id'])
                ->first()['amount'] ?? 0;

            // Subtract what they've already settled (received)
            $received = $settleModel
                ->selectSum('amount')
                ->where('to_user_id', $m['id'])
                ->where('home_id', $homeId)
                ->first()['amount'] ?? 0;

            $sent = $settleModel
                ->selectSum('amount')
                ->where('from_user_id', $m['id'])
                ->where('home_id', $homeId)
                ->first()['amount'] ?? 0;

            $memberBalances[] = [
                'id'         => $m['id'],
                'username'   => $m['username'],
                'paid'       => $paid,
                'should_pay' => $fairShare,
                'balance'    => $paid - $fairShare + $received - $sent,
            ];
        }

        // Compute minimal settlements (positive = owed to others, negative = owe others)
        $settlements = $this->computeSettlements($memberBalances);

        // Settlement history
        $settleHistory = $settleModel
            ->select('settlements.*, u1.username AS from_name, u2.username AS to_name')
            ->join('users AS u1', 'u1.id = settlements.from_user_id')
            ->join('users AS u2', 'u2.id = settlements.to_user_id')
            ->where('settlements.home_id', $homeId)
            ->orderBy('settlements.settled_at', 'DESC')
            ->limit(20)
            ->findAll();

        if ($this->isApi()) {
            return $this->apiOk([
                'memberBalances' => $memberBalances,
                'settlements'    => $settlements,
                'settleHistory'  => $settleHistory,
            ]);
        }

        return view('expenses/balance', [
            'pageTitle'      => 'Balance de Gastos',
            'pageSubtitle'   => 'Quién debe qué a quién',
            'activeNav'      => 'balance',
            'memberBalances' => $memberBalances,
            'settlements'    => $settlements,
            'settleHistory'  => $settleHistory,
        ]);
    }

    public function settle()
    {
        if ($this->requireHome()) return;

        $userId    = session()->get('user_id');
        $homeId    = session()->get('home_id');
        $toUserId  = $this->request->getPost('to_user_id');
        $amount    = (float) $this->request->getPost('amount');

        if (!$toUserId || $amount <= 0) {
            return redirect()->back()->with('error', 'Datos inválidos.');
        }

        $settleModel = new SettlementModel();
        $settleModel->insert([
            'home_id'      => $homeId,
            'from_user_id' => $userId,
            'to_user_id'   => $toUserId,
            'amount'       => $amount,
            'settled_at'   => date('Y-m-d H:i:s'),
        ]);

        if ($this->isApi()) return $this->apiOk();

        return redirect()->to('/expenses/balance')->with('success', 'Pago registrado correctamente.');
    }

    public function summary()
    {
        if ($this->requireHome()) return;

        $homeId  = session()->get('home_id');
        $month   = $this->request->getGet('month') ?? date('Y-m');
        [$year, $mon] = explode('-', $month);

        $expenseModel = new ExpenseModel();
        $uhModel      = new UserHomesModel();

        $members     = $uhModel->getMembersOfHome($homeId);
        $memberCount = count($members);

        // Total this month
        $totalMonth = $expenseModel
            ->selectSum('amount')
            ->where('home_id', $homeId)
            ->where('YEAR(date)', $year)
            ->where('MONTH(date)', $mon)
            ->first()['amount'] ?? 0;

        // Count
        $expenseCount = $expenseModel
            ->where('home_id', $homeId)
            ->where('YEAR(date)', $year)
            ->where('MONTH(date)', $mon)
            ->countAllResults();

        // By category
        $byCategory = $expenseModel
            ->select('category, SUM(amount) AS total')
            ->where('home_id', $homeId)
            ->where('YEAR(date)', $year)
            ->where('MONTH(date)', $mon)
            ->groupBy('category')
            ->orderBy('total', 'DESC')
            ->findAll();

        // By member
        $byMember = $expenseModel
            ->select('users.username, SUM(expenses.amount) AS paid')
            ->join('users', 'users.id = expenses.paid_by')
            ->where('expenses.home_id', $homeId)
            ->where('YEAR(expenses.date)', $year)
            ->where('MONTH(expenses.date)', $mon)
            ->groupBy('expenses.paid_by')
            ->orderBy('paid', 'DESC')
            ->findAll();

        // Top 5 expenses
        $topExpenses = $expenseModel
            ->select('expenses.*, users.username AS paid_by_name')
            ->join('users', 'users.id = expenses.paid_by')
            ->where('expenses.home_id', $homeId)
            ->where('YEAR(expenses.date)', $year)
            ->where('MONTH(expenses.date)', $mon)
            ->orderBy('expenses.amount', 'DESC')
            ->limit(5)
            ->findAll();

        // Last 6 months evolution
        $monthlyEvolution = [];
        for ($i = 5; $i >= 0; $i--) {
            $ts   = strtotime("-{$i} months", mktime(0, 0, 0, $mon, 1, $year));
            $y    = date('Y', $ts);
            $m    = date('m', $ts);
            $tot  = $expenseModel
                ->selectSum('amount')
                ->where('home_id', $homeId)
                ->where('YEAR(date)', $y)
                ->where('MONTH(date)', $m)
                ->first()['amount'] ?? 0;
            $monthlyEvolution[] = ['year' => $y, 'month' => $m, 'total' => $tot];
        }

        // vs last month
        $lastMonthTs  = strtotime('-1 month', mktime(0, 0, 0, $mon, 1, $year));
        $lastMonthTot = $expenseModel
            ->selectSum('amount')
            ->where('home_id', $homeId)
            ->where('YEAR(date)', date('Y', $lastMonthTs))
            ->where('MONTH(date)', date('m', $lastMonthTs))
            ->first()['amount'] ?? null;

        $vsLastMonth = ($lastMonthTot > 0 && $totalMonth !== null)
            ? (($totalMonth - $lastMonthTot) / $lastMonthTot) * 100
            : null;

        $prevMonth = date('Y-m', strtotime('-1 month', mktime(0, 0, 0, $mon, 1, $year)));
        $nextMonth = date('Y-m', strtotime('+1 month', mktime(0, 0, 0, $mon, 1, $year)));

        return view('expenses/summary', [
            'pageTitle'        => 'Resumen Mensual',
            'pageSubtitle'     => 'Análisis y estadísticas de gastos',
            'activeNav'        => 'summary',
            'month'            => $month,
            'monthLabel'       => date('F Y', mktime(0, 0, 0, $mon, 1, $year)),
            'prevMonth'        => $prevMonth,
            'nextMonth'        => $nextMonth,
            'totalMonth'       => $totalMonth,
            'expenseCount'     => $expenseCount,
            'memberCount'      => $memberCount,
            'byCategory'       => $byCategory,
            'byMember'         => $byMember,
            'topExpenses'      => $topExpenses,
            'monthlyEvolution' => $monthlyEvolution,
            'vsLastMonth'      => $vsLastMonth,
        ]);
    }

    public function export()
    {
        if ($this->requireHome()) return;

        $homeId       = session()->get('home_id');
        $expenseModel = new ExpenseModel();

        $expenses = $expenseModel
            ->select('expenses.title, expenses.amount, expenses.category, expenses.date, users.username AS paid_by, expenses.description')
            ->join('users', 'users.id = expenses.paid_by')
            ->where('expenses.home_id', $homeId)
            ->orderBy('expenses.date', 'DESC')
            ->findAll();

        $csv = "Descripción,Importe,Categoría,Fecha,Pagado por,Notas\n";
        foreach ($expenses as $e) {
            $csv .= implode(',', [
                '"' . str_replace('"', '""', $e['title']) . '"',
                number_format($e['amount'], 2),
                $e['category'],
                $e['date'],
                '"' . $e['paid_by'] . '"',
                '"' . str_replace('"', '""', $e['description'] ?? '') . '"',
            ]) . "\n";
        }

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="gastos_' . date('Y-m') . '.csv"')
            ->setBody("\xEF\xBB\xBF" . $csv); // BOM for Excel UTF-8
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    /**
     * Compute minimal number of transactions to settle debts.
     * Uses greedy matching between debtors and creditors.
     */
    private function computeSettlements(array $memberBalances): array
    {
        $settlements = [];
        $balances    = array_map(fn($m) => ['id' => $m['id'], 'name' => $m['username'], 'bal' => round($m['balance'], 2)], $memberBalances);

        for ($iter = 0; $iter < 50; $iter++) {
            usort($balances, fn($a, $b) => $a['bal'] <=> $b['bal']);
            $debtor   = $balances[0];
            $creditor = $balances[count($balances) - 1];

            if (abs($debtor['bal']) < 0.01 || abs($creditor['bal']) < 0.01) break;
            if ($debtor['bal'] >= 0) break;

            $amount = min(-$debtor['bal'], $creditor['bal']);
            $settlements[] = [
                'from_id'   => $debtor['id'],
                'from_name' => $debtor['name'],
                'to_id'     => $creditor['id'],
                'to_name'   => $creditor['name'],
                'amount'    => $amount,
            ];

            $balances[0]['bal']                    += $amount;
            $balances[count($balances) - 1]['bal'] -= $amount;
        }

        return $settlements;
    }
}
