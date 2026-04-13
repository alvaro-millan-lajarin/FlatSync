<?php

namespace App\Controllers;

use App\Models\ChoreModel;
use App\Models\ExpenseModel;
use App\Models\UserModel;
use App\Models\HomeModel;
use App\Models\SwapModel;
use App\Models\UserHomesModel;

class HomeController extends BaseController
{
    public function index()
    {
        if ($this->requireHome()) return;

        $homeId = session()->get('home_id');
        $userId = session()->get('user_id');

        $choreModel   = new ChoreModel();
        $expenseModel = new ExpenseModel();
        $userModel    = new UserModel();
        $homeModel    = new HomeModel();
        $swapModel    = new SwapModel();

        $home = $homeModel->find($homeId);

        // Stale session: home no longer exists → clear and redirect
        if (!$home) {
            session()->remove(['home_id', 'home_name', 'is_admin']);
            return redirect()->to('/homes')->with('error', 'Tu sesión de hogar ha caducado. Selecciona uno de nuevo.');
        }

        // Today's chores assigned to this user
        $todayChores = $choreModel
            ->where('assigned_user_id', $userId)
            ->where('due_date', date('Y-m-d'))
            ->where('home_id', $homeId)
            ->findAll();

        // Pending chores count
        $pendingChores = $choreModel
            ->where('assigned_user_id', $userId)
            ->where('status', 'pending')
            ->where('home_id', $homeId)
            ->countAllResults();

        // Recent expenses (last 5)
        $recentExpenses = $expenseModel
            ->select('expenses.*, users.username AS paid_by_name')
            ->join('users', 'users.id = expenses.paid_by')
            ->where('expenses.home_id', $homeId)
            ->orderBy('expenses.date', 'DESC')
            ->limit(5)
            ->findAll();

        // ── Selected month (used for expenses stats) ──────────────────
        $filterMonth = $this->request->getGet('month') ?? date('Y-m');
        [$year, $mon] = explode('-', $filterMonth);

        // Month total
        $monthExpenses = $expenseModel
            ->selectSum('amount')
            ->where('home_id', $homeId)
            ->where('MONTH(date)', $mon)
            ->where('YEAR(date)', $year)
            ->first()['amount'] ?? 0;

        // My balance
        $uhModel     = new UserHomesModel();
        $members     = $uhModel->getMembersOfHome($homeId);
        $memberCount = count($members);
        $myPaid      = $expenseModel
            ->selectSum('amount')
            ->where('home_id', $homeId)
            ->where('paid_by', $userId)
            ->where('MONTH(date)', $mon)
            ->where('YEAR(date)', $year)
            ->first()['amount'] ?? 0;
        $fairShare   = $memberCount > 0 ? ($monthExpenses / $memberCount) : 0;
        $myBalance   = $myPaid - $fairShare;

        // Pending swap requests involving me
        $pendingSwaps = $swapModel
            ->select('swap_requests.*, chores.task_name, u1.username AS requester_name')
            ->join('chores', 'chores.id = swap_requests.chore_id')
            ->join('users AS u1', 'u1.id = swap_requests.requester_user_id')
            ->where('swap_requests.target_user_id', $userId)
            ->where('swap_requests.status', 'pending')
            ->findAll();

        // ── Summary data (selected month) ─────────────────────────────

        $expenseCount = $expenseModel
            ->where('home_id', $homeId)
            ->where('MONTH(date)', $mon)
            ->where('YEAR(date)', $year)
            ->countAllResults();

        $doneChores = $choreModel
            ->where('home_id', $homeId)
            ->where('status', 'done')
            ->where('MONTH(due_date)', $mon)
            ->where('YEAR(due_date)', $year)
            ->countAllResults();

        $byCategory = $expenseModel
            ->select('category, SUM(amount) AS total')
            ->where('home_id', $homeId)
            ->where('MONTH(date)', $mon)
            ->where('YEAR(date)', $year)
            ->groupBy('category')
            ->orderBy('total', 'DESC')
            ->findAll();

        $byMember = $expenseModel
            ->select('users.username, SUM(expenses.amount) AS paid')
            ->join('users', 'users.id = expenses.paid_by')
            ->where('expenses.home_id', $homeId)
            ->where('MONTH(expenses.date)', $mon)
            ->where('YEAR(expenses.date)', $year)
            ->groupBy('expenses.paid_by')
            ->orderBy('paid', 'DESC')
            ->findAll();

        $topExpenses = $expenseModel
            ->select('expenses.*, users.username AS paid_by_name')
            ->join('users', 'users.id = expenses.paid_by')
            ->where('expenses.home_id', $homeId)
            ->where('MONTH(expenses.date)', $mon)
            ->where('YEAR(expenses.date)', $year)
            ->orderBy('expenses.amount', 'DESC')
            ->limit(5)
            ->findAll();

        $monthlyEvolution = [];
        for ($i = 5; $i >= 0; $i--) {
            $ts  = strtotime("-{$i} months", mktime(0, 0, 0, $mon, 1, $year));
            $y   = date('Y', $ts);
            $m   = date('m', $ts);
            $tot = $expenseModel
                ->selectSum('amount')
                ->where('home_id', $homeId)
                ->where('YEAR(date)', $y)
                ->where('MONTH(date)', $m)
                ->first()['amount'] ?? 0;
            $monthlyEvolution[] = ['year' => $y, 'month' => $m, 'total' => $tot];
        }

        $lastMonthTs  = strtotime('-1 month', mktime(0, 0, 0, $mon, 1, $year));
        $lastMonthTot = $expenseModel
            ->selectSum('amount')
            ->where('home_id', $homeId)
            ->where('YEAR(date)', date('Y', $lastMonthTs))
            ->where('MONTH(date)', date('m', $lastMonthTs))
            ->first()['amount'] ?? null;
        $vsLastMonth = ($lastMonthTot > 0)
            ? (($monthExpenses - $lastMonthTot) / $lastMonthTot) * 100
            : null;

        $prevMonth  = date('Y-m', strtotime('-1 month', mktime(0, 0, 0, $mon, 1, $year)));
        $nextMonth  = date('Y-m', strtotime('+1 month', mktime(0, 0, 0, $mon, 1, $year)));
        $monthLabel = date('F Y', mktime(0, 0, 0, $mon, 1, $year));

        return view('dashboard', [
            'pageTitle'         => 'Dashboard',
            'pageSubtitle'      => '¡Hola, ' . session()->get('username') . '!',
            'activeNav'         => 'dashboard',
            'todayChores'       => $todayChores,
            'pendingChores'     => $pendingChores,
            'recentExpenses'    => $recentExpenses,
            'monthExpenses'     => $monthExpenses,
            'myBalance'         => $myBalance,
            'memberCount'       => $memberCount,
            'pendingSwaps'      => $pendingSwaps,
            'inviteCode'        => $home['invite_code'] ?? '------',
            'expenseCount'      => $expenseCount,
            'doneChores'        => $doneChores,
            'byCategory'        => $byCategory,
            'byMember'          => $byMember,
            'topExpenses'       => $topExpenses,
            'monthlyEvolution'  => $monthlyEvolution,
            'vsLastMonth'       => $vsLastMonth,
            'filterMonth'       => $filterMonth,
            'prevMonth'         => $prevMonth,
            'nextMonth'         => $nextMonth,
            'monthLabel'        => $monthLabel,
        ]);
    }
}
