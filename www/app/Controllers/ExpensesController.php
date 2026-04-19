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
        $filterMonth     = $this->request->getGet('month') ?: '';
        $filterCategory  = $this->request->getGet('category') ?? '';
        $filterPaidBy    = $this->request->getGet('paid_by') ?? '';

        $expenseModel = new ExpenseModel();
        $uhModel      = new UserHomesModel();

        $query = $expenseModel
            ->select('expenses.*, users.username AS paid_by_name')
            ->join('users', 'users.id = expenses.paid_by')
            ->where('expenses.home_id', $homeId);

        if ($filterMonth !== '') {
            [$year, $month] = explode('-', $filterMonth);
            $query->where('YEAR(expenses.date)', $year)
                  ->where('MONTH(expenses.date)', $month);
        }

        if ($filterCategory) $query->where('expenses.category', $filterCategory);
        if ($filterPaidBy)   $query->where('expenses.paid_by', $filterPaidBy);

        $expenses = $query->orderBy('expenses.date', 'DESC')->findAll();

        $members     = $uhModel->getMembersOfHome($homeId);
        $allMemberIds = array_column($members, 'id');

        $monthTotal = array_sum(array_column($expenses, 'amount'));

        $myShare = 0;
        $myPaid  = 0;
        foreach ($expenses as $e) {
            if ($e['paid_by'] == $userId) $myPaid += $e['amount'];
            $splitWith    = $e['split_with'] ?? null;
            $participants = $splitWith ? json_decode($splitWith, true) : $allMemberIds;
            $count = count($participants);
            if ($count > 0 && in_array((int)$userId, array_map('intval', $participants))) {
                $myShare += $e['amount'] / $count;
            }
        }
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
            'pageTitle'      => lang('App.expenses_title'),
            'pageSubtitle'   => lang('App.expenses_subtitle'),
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

    /** GET JSON: gastos nuevos desde ?after=id — igual que el chat */
    public function poll()
    {
        if (!session()->get('isLoggedIn') || !session()->get('home_id')) {
            return $this->response->setJSON(['expenses' => []]);
        }
        $homeId      = session()->get('home_id');
        $userId      = session()->get('user_id');
        $afterId     = (int) ($this->request->getGet('after') ?? 0);
        $filterMonth = $this->request->getGet('month') ?: '';

        $query = (new ExpenseModel())
            ->select('expenses.*, users.username AS paid_by_name')
            ->join('users', 'users.id = expenses.paid_by')
            ->where('expenses.home_id', $homeId)
            ->where('expenses.id >', $afterId);

        if ($filterMonth !== '') {
            [$year, $month] = explode('-', $filterMonth);
            $query->where('YEAR(expenses.date)', $year)
                  ->where('MONTH(expenses.date)', $month);
        }

        $expenses = $query->orderBy('expenses.date', 'DESC')->findAll();

        return $this->response->setJSON(['expenses' => $expenses]);
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
            $errors = implode(' ', $this->validator->getErrors());
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['ok' => false, 'error' => $errors]);
            }
            return redirect()->back()->with('error', $errors);
        }

        $receiptFile = null;
        $file = $this->request->getFile('receipt_image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName     = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads', $newName);
            $receiptFile = $newName;
        }

        $homeId       = session()->get('home_id');
        $userId       = session()->get('user_id');

        // Participants: null = all members, otherwise JSON array of user IDs
        $splitWith = null;
        $rawSplit  = $this->request->getPost('split_with');
        if (is_array($rawSplit) && count($rawSplit) > 0) {
            $allMembers  = (new UserHomesModel())->getMembersOfHome($homeId);
            $allIds      = array_column($allMembers, 'id');
            $selected    = array_map('intval', $rawSplit);
            sort($selected); sort($allIds);
            if ($selected !== $allIds) {
                $splitWith = json_encode($selected);
            }
        }

        $expenseModel = new ExpenseModel();
        $insertData = [
            'home_id'       => $homeId,
            'title'         => $this->request->getPost('title'),
            'description'   => $this->request->getPost('description'),
            'amount'        => (float) $this->request->getPost('amount'),
            'category'      => $this->request->getPost('category') ?? 'other',
            'paid_by'       => $this->request->getPost('paid_by'),
            'date'          => $this->request->getPost('date'),
            'receipt_image' => $receiptFile,
        ];
        if ($splitWith !== null) $insertData['split_with'] = $splitWith;
        $newId = $expenseModel->insert($insertData);

        if ($this->request->isAJAX()) {
            // Devolver el gasto completo (con nombre) + stats actualizadas, igual que el chat devuelve la nota
            $expense = (new ExpenseModel())
                ->select('expenses.*, users.username AS paid_by_name')
                ->join('users', 'users.id = expenses.paid_by')
                ->where('expenses.id', $newId)
                ->first();

            $filterMonth = substr($this->request->getPost('date'), 0, 7) ?: date('Y-m');
            [$year, $month] = explode('-', $filterMonth);
            $all = (new ExpenseModel())
                ->select('amount, paid_by, split_with')
                ->where('home_id', $homeId)
                ->where('YEAR(date)', $year)
                ->where('MONTH(date)', $month)
                ->findAll();
            $allMembers  = (new UserHomesModel())->getMembersOfHome($homeId);
            $allMemberIds = array_column($allMembers, 'id');
            $monthTotal  = array_sum(array_column($all, 'amount'));
            $myShare = 0; $myPaid = 0;
            foreach ($all as $e) {
                if ($e['paid_by'] == $userId) $myPaid += $e['amount'];
                $splitWith    = $e['split_with'] ?? null;
                $participants = $splitWith ? json_decode($splitWith, true) : $allMemberIds;
                $count = count($participants);
                if ($count > 0 && in_array((int)$userId, array_map('intval', $participants))) {
                    $myShare += $e['amount'] / $count;
                }
            }

            return $this->response->setJSON([
                'ok'      => true,
                'expense' => $expense,
                'stats'   => [
                    'monthTotal' => round($monthTotal, 2),
                    'myPaid'     => round($myPaid, 2),
                    'myBalance'  => round($myPaid - $myShare, 2),
                ],
            ]);
        }

        return redirect()->to('/expenses')->with('success', lang('App.flash_expense_added'));
    }

    public function update(int $id)
    {
        if ($this->requireHome()) return;

        $userId       = session()->get('user_id');
        $expenseModel = new ExpenseModel();
        $expense      = $expenseModel->find($id);

        if (!$expense || $expense['home_id'] != session()->get('home_id') || $expense['paid_by'] != $userId) {
            return redirect()->back()->with('error', lang('App.flash_expense_no_perm'));
        }

        $expenseModel->update($id, [
            'title'    => $this->request->getPost('title'),
            'amount'   => (float) $this->request->getPost('amount'),
            'category' => $this->request->getPost('category'),
            'date'     => $this->request->getPost('date'),
        ]);

        if ($this->isApi()) return $this->apiOk();

        return redirect()->to('/expenses')->with('success', lang('App.flash_expense_updated'));
    }

    public function delete(int $id)
    {
        if ($this->requireHome()) return;

        $homeId       = session()->get('home_id');
        $userId       = session()->get('user_id');
        $expenseModel = new ExpenseModel();
        $expense      = $expenseModel->find($id);

        if (!$expense || $expense['home_id'] != $homeId || $expense['paid_by'] != $userId) {
            return redirect()->back()->with('error', lang('App.flash_expense_no_del'));
        }


        // Remove receipt file if exists
        if ($expense['receipt_image']) {
            $path = ROOTPATH . 'public/uploads/' . $expense['receipt_image'];
            if (file_exists($path)) unlink($path);
        }

        $expenseModel->delete($id);

        if ($this->isApi()) return $this->apiOk();

        return redirect()->to('/expenses')->with('success', lang('App.flash_expense_deleted'));
    }

    public function balance()
    {
        if ($this->requireHome()) return;

        $homeId       = session()->get('home_id');
        $expenseModel = new ExpenseModel();
        $settleModel  = new SettlementModel();
        $uhModel      = new UserHomesModel();

        $members    = $uhModel->getMembersOfHome($homeId);
        $allMemberIds = array_column($members, 'id');

        // Fetch all expenses once
        $allExpenses = $expenseModel
            ->select('amount, paid_by, split_with')
            ->where('home_id', $homeId)
            ->findAll();

        // Per-member: how much each person should pay across all expenses
        $shouldPay = array_fill_keys($allMemberIds, 0.0);
        $paid      = array_fill_keys($allMemberIds, 0.0);

        foreach ($allExpenses as $e) {
            $paidBy = (int) $e['paid_by'];
            if (isset($paid[$paidBy])) $paid[$paidBy] += (float) $e['amount'];

            $splitWith    = $e['split_with'] ?? null;
            $participants = $splitWith
                ? array_map('intval', json_decode($splitWith, true))
                : $allMemberIds;
            $count = count($participants);
            if ($count === 0) continue;
            $share = (float) $e['amount'] / $count;
            foreach ($participants as $uid) {
                if (isset($shouldPay[$uid])) $shouldPay[$uid] += $share;
            }
        }

        // Per-member balance
        $memberBalances = [];
        foreach ($members as $m) {
            $uid = (int) $m['id'];

            $received = (float) ($settleModel
                ->selectSum('amount')
                ->where('to_user_id', $uid)
                ->where('home_id', $homeId)
                ->first()['amount'] ?? 0);

            $sent = (float) ($settleModel
                ->selectSum('amount')
                ->where('from_user_id', $uid)
                ->where('home_id', $homeId)
                ->first()['amount'] ?? 0);

            $memberBalances[] = [
                'id'         => $uid,
                'username'   => $m['username'],
                'paid'       => $paid[$uid] ?? 0,
                'should_pay' => $shouldPay[$uid] ?? 0,
                'balance'    => ($paid[$uid] ?? 0) - ($shouldPay[$uid] ?? 0) + $received - $sent,
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
            'pageTitle'      => lang('App.balance_title'),
            'pageSubtitle'   => lang('App.balance_subtitle'),
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
            return redirect()->back()->with('error', lang('App.flash_expense_invalid'));
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

        return redirect()->to('/expenses/balance')->with('success', lang('App.flash_payment_ok'));
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
            'pageTitle'        => lang('App.summary_title'),
            'pageSubtitle'     => lang('App.summary_subtitle'),
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
