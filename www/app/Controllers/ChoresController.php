<?php

namespace App\Controllers;

use App\Models\ChoreModel;
use App\Models\UserModel;
use App\Models\HomeModel;
use App\Models\SwapModel;
use App\Models\ExpenseModel;
use App\Models\UserHomesModel;

class ChoresController extends BaseController
{
    // Colours assigned per user for calendar display
    private const USER_COLORS = [
        ['rgba(124,106,247,0.25)', 'var(--accent)'],
        ['rgba(247,179,106,0.25)', 'var(--accent2)'],
        ['rgba(106,247,160,0.25)', 'var(--success)'],
        ['rgba(247,106,106,0.25)', 'var(--danger)'],
    ];

    public function index()
    {
        if ($this->requireHome()) return;

        $homeId = session()->get('home_id');
        $userId = session()->get('user_id');
        $view   = $this->request->getGet('view') ?? 'week';
        $offset = (int) ($this->request->getGet('offset') ?? 0);

        $choreModel = new ChoreModel();
        $homeModel  = new HomeModel();
        $uhModel    = new UserHomesModel();

        $members = $uhModel->getMembersOfHome($homeId);
        $home    = $homeModel->find($homeId);

        // Build colour map: user_id => color pair
        $colorMap = [];
        foreach ($members as $i => $m) {
            $colorMap[$m['id']] = self::USER_COLORS[$i % count(self::USER_COLORS)];
        }

        // Auto-mark missed chores (past due, still pending)
        $choreModel->where('home_id', $homeId)
            ->where('status', 'pending')
            ->where('due_date <', date('Y-m-d'))
            ->set(['status' => 'missed'])
            ->update();

        // Auto-extend recurring series: ensure ≥3 pending future instances exist
        $this->extendRecurringSeries($homeId, $choreModel, $members);

        // Calendar data
        [$calDays, $calendarTitle, $startDate, $endDate] = $this->buildCalendar($view, $offset);

        // Fetch chores in range
        $chores = $choreModel
            ->select('chores.*, users.username AS assigned_name')
            ->join('users', 'users.id = chores.assigned_user_id')
            ->where('chores.home_id', $homeId)
            ->where('chores.due_date >=', $startDate)
            ->where('chores.due_date <=', $endDate)
            ->orderBy('chores.due_date', 'ASC')
            ->findAll();

        // Inject tasks into calendar days
        foreach ($chores as $c) {
            $colors = $colorMap[$c['assigned_user_id']] ?? self::USER_COLORS[0];
            $c['color']      = $colors[0];
            $c['text_color'] = $colors[1];
            foreach ($calDays as &$day) {
                if ($day['date'] === $c['due_date']) {
                    $day['tasks'][] = $c;
                }
            }
            unset($day);
        }

        // Missed chores with penalty
        $missedChores = $choreModel
            ->select('chores.*, users.username AS assigned_name, homes.default_penalty AS penalty_amount')
            ->join('users', 'users.id = chores.assigned_user_id')
            ->join('homes', 'homes.id = chores.home_id')
            ->where('chores.home_id', $homeId)
            ->where('chores.status', 'missed')
            ->orderBy('chores.due_date', 'DESC')
            ->limit(10)
            ->findAll();

        // Recent swaps
        $swapModel   = new SwapModel();
        $recentSwaps = $swapModel
            ->select('swap_requests.*, chores.task_name, chores.due_date, u1.username AS requester_name, u2.username AS target_name')
            ->join('chores', 'chores.id = swap_requests.chore_id')
            ->join('users AS u1', 'u1.id = swap_requests.requester_user_id')
            ->join('users AS u2', 'u2.id = swap_requests.target_user_id')
            ->where('chores.home_id', $homeId)
            ->orderBy('swap_requests.created_at', 'DESC')
            ->limit(10)
            ->findAll();

        // List view: all chores
        $allChores = $choreModel
            ->select('chores.*, users.username AS assigned_name')
            ->join('users', 'users.id = chores.assigned_user_id')
            ->where('chores.home_id', $homeId)
            ->orderBy('chores.due_date', 'DESC')
            ->findAll();

        if ($this->isApi()) {
            return $this->apiOk([
                'chores'       => $allChores,
                'missedChores' => $missedChores,
                'members'      => $members,
            ]);
        }

        return view('chores/index', [
            'pageTitle'      => lang('App.chores_title'),
            'pageSubtitle'   => $calendarTitle,
            'activeNav'      => 'chores',
            'view'          => $view,
            'offset'        => $offset,
            'calDays'       => $calDays,
            'calendarTitle' => $calendarTitle,
            'chores'        => $allChores,
            'missedChores'  => $missedChores,
            'recentSwaps'   => $recentSwaps,
            'members'       => $members,
            'defaultPenalty'=> $home['default_penalty'] ?? 2.00,
        ]);
    }

    /** GET JSON: tareas nuevas o actualizadas desde ?after=id&after_ts=timestamp */
    public function poll()
    {
        if (!session()->get('isLoggedIn') || !session()->get('home_id')) {
            return $this->response->setJSON(['chores' => []]);
        }
        $homeId  = session()->get('home_id');
        $afterId = (int) ($this->request->getGet('after') ?? 0);
        $afterTs = $this->request->getGet('after_ts') ?? null;

        $uhModel  = new UserHomesModel();
        $members  = $uhModel->getMembersOfHome($homeId);
        $colorMap = [];
        foreach ($members as $i => $m) {
            $colorMap[$m['id']] = self::USER_COLORS[$i % count(self::USER_COLORS)];
        }

        $choreModel = new ChoreModel();
        $builder = $choreModel
            ->select('chores.*, users.username AS assigned_name')
            ->join('users', 'users.id = chores.assigned_user_id')
            ->where('chores.home_id', $homeId);

        if ($afterTs) {
            $builder->groupStart()
                ->where('chores.id >', $afterId)
                ->orWhere('chores.updated_at >=', $afterTs)
                ->groupEnd();
        } else {
            $builder->where('chores.id >', $afterId);
        }

        $chores = $builder->orderBy('chores.due_date', 'ASC')->findAll();

        foreach ($chores as &$c) {
            $colors          = $colorMap[$c['assigned_user_id']] ?? self::USER_COLORS[0];
            $c['color']      = $colors[0];
            $c['text_color'] = $colors[1];
        }

        return $this->response->setJSON(['chores' => $chores]);
    }

    public function store()
    {
        if ($this->requireHome()) return;

        $homeId = session()->get('home_id');

        $rules = [
            'task_name'        => 'required|min_length[2]',
            'assigned_user_id' => 'required|is_natural_no_zero',
            'due_date'         => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            $errors = implode(' ', $this->validator->getErrors());
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['ok' => false, 'error' => $errors]);
            }
            return redirect()->back()->with('error', $errors);
        }

        $choreModel = new ChoreModel();
        $data = [
            'home_id'          => $homeId,
            'task_name'        => $this->request->getPost('task_name'),
            'icon'             => $this->request->getPost('icon') ?: '🏠',
            'assigned_user_id' => $this->request->getPost('assigned_user_id'),
            'due_date'         => $this->request->getPost('due_date'),
            'penalty_amount'   => $this->request->getPost('penalty_amount') ?? 2.00,
            'recurrence'       => $this->request->getPost('recurrence') ?? 'none',
            'status'           => 'pending',
        ];

        $data['recurrence_parent_id'] = null;
        $newId = $choreModel->insert($data);

        // If recurring, generate instances up to 10 weeks / occurrences ahead
        $recurrence = $data['recurrence'];
        if ($recurrence !== 'none') {
            $intervals = ['weekly' => '+1 week', 'biweekly' => '+2 weeks', 'monthly' => '+1 month'];
            $interval  = $intervals[$recurrence] ?? null;
            $members   = (new UserHomesModel())->getMembersOfHome($homeId);
            $userCount = count($members);
            if ($interval && $userCount > 0) {
                $baseDate  = $data['due_date'];
                $userIndex = array_search($data['assigned_user_id'], array_column($members, 'id'));
                $limit     = $recurrence === 'monthly' ? 6 : 10;
                for ($i = 1; $i <= $limit; $i++) {
                    $baseDate = date('Y-m-d', strtotime($baseDate . ' ' . $interval));
                    $nextUser = $members[($userIndex + $i) % $userCount];
                    $choreModel->insert(array_merge($data, [
                        'due_date'             => $baseDate,
                        'assigned_user_id'     => $nextUser['id'],
                        'recurrence_parent_id' => $newId,
                    ]));
                }
            }
        }

        if ($this->request->isAJAX()) {
            // Devolver la tarea completa con color, igual que el chat devuelve la nota
            $members  = (new UserHomesModel())->getMembersOfHome($homeId);
            $colorMap = [];
            foreach ($members as $i => $m) {
                $colorMap[$m['id']] = self::USER_COLORS[$i % count(self::USER_COLORS)];
            }
            $assignedId   = (int) $data['assigned_user_id'];
            $colors       = $colorMap[$assignedId] ?? self::USER_COLORS[0];
            $assignedName = '';
            foreach ($members as $m) {
                if ((int)$m['id'] === $assignedId) { $assignedName = $m['username']; break; }
            }
            return $this->response->setJSON([
                'ok'    => true,
                'chore' => array_merge($data, [
                    'id'            => $newId,
                    'assigned_name' => $assignedName,
                    'color'         => $colors[0],
                    'text_color'    => $colors[1],
                ]),
            ]);
        }

        return redirect()->to('/chores')->with('success', lang('App.flash_chore_created'));
    }

    public function markDone(int $id)
    {
        if ($this->requireHome()) return;

        $choreModel = new ChoreModel();
        $chore      = $choreModel->find($id);

        if (!$chore || $chore['assigned_user_id'] != session()->get('user_id')) {
            return redirect()->back()->with('error', lang('App.flash_chore_no_perm'));
        }

        $choreModel->update($id, ['status' => 'done', 'completed_at' => date('Y-m-d H:i:s')]);

        if ($this->isApi()) return $this->apiOk();

        return redirect()->back()->with('success', lang('App.flash_chore_done'));
    }

    public function toggleDone(int $id)
    {
        if ($this->requireHome()) return;

        $choreModel = new ChoreModel();
        $chore      = $choreModel->find($id);

        if (!$chore || $chore['assigned_user_id'] != session()->get('user_id')) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false]);
        }

        if ($chore['status'] === 'done') {
            $choreModel->update($id, ['status' => 'pending', 'completed_at' => null]);
            $newStatus = 'pending';
        } else {
            $choreModel->update($id, ['status' => 'done', 'completed_at' => date('Y-m-d H:i:s')]);
            $newStatus = 'done';
        }

        return $this->response->setJSON(['ok' => true, 'status' => $newStatus]);
    }

    public function update(int $id)
    {
        if ($this->requireHome()) return;

        $choreModel = new ChoreModel();
        $chore      = $choreModel->find($id);

        if (!$chore || $chore['home_id'] != session()->get('home_id')) {
            return redirect()->back()->with('error', lang('App.flash_chore_not_found'));
        }

        $choreModel->update($id, [
            'task_name'        => $this->request->getPost('task_name'),
            'assigned_user_id' => $this->request->getPost('assigned_user_id'),
            'due_date'         => $this->request->getPost('due_date'),
            'penalty_amount'   => $this->request->getPost('penalty_amount'),
        ]);

        if ($this->isApi()) return $this->apiOk();

        return redirect()->back()->with('success', lang('App.flash_chore_updated'));
    }

    public function delete(int $id)
    {
        if ($this->requireHome()) return;

        $homeId     = session()->get('home_id');
        $choreModel = new ChoreModel();
        $chore      = $choreModel->find($id);

        if (!$chore || $chore['home_id'] != $homeId) {
            return redirect()->back()->with('error', lang('App.flash_chore_not_found'));
        }

        $scope = $this->request->getPost('scope') ?? 'this';

        if ($scope === 'future' && $chore['recurrence'] !== 'none') {
            $rootId = $chore['recurrence_parent_id'] ?? $id;
            db_connect()->table('chores')
                ->where('home_id', $homeId)
                ->groupStart()
                    ->where('id', $id)
                    ->orGroupStart()
                        ->where('recurrence_parent_id', $rootId)
                        ->where('due_date >=', $chore['due_date'])
                        ->where('status', 'pending')
                    ->groupEnd()
                ->groupEnd()
                ->delete();
        } else {
            $choreModel->delete($id);
        }

        if ($this->isApi()) return $this->apiOk();

        return redirect()->back()->with('success', lang('App.flash_chore_deleted'));
    }

    public function swapRequest()
    {
        if ($this->requireHome()) return;

        $userId = session()->get('user_id');
        $homeId = session()->get('home_id');

        $rules = [
            'chore_id'       => 'required|is_natural_no_zero',
            'target_user_id' => 'required|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('error', lang('App.flash_swap_invalid'));
        }

        $choreId      = (int) $this->request->getPost('chore_id');
        $targetUserId = (int) $this->request->getPost('target_user_id');

        $choreModel = new ChoreModel();
        $chore      = $choreModel->find($choreId);

        if (!$chore || $chore['home_id'] != $homeId || $chore['assigned_user_id'] != $userId) {
            return redirect()->back()->with('error', lang('App.flash_swap_no_perm'));
        }

        $choreModel->update($choreId, ['assigned_user_id' => $targetUserId]);

        (new SwapModel())->insert([
            'chore_id'          => $choreId,
            'requester_user_id' => $userId,
            'target_user_id'    => $targetUserId,
            'compensation'      => 0,
            'message'           => $this->request->getPost('message'),
            'status'            => 'completed',
        ]);

        return redirect()->to('/chores')->with('success', lang('App.flash_swap_sent'));
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function extendRecurringSeries(int $homeId, ChoreModel $choreModel, array $members): void
    {
        $today    = date('Y-m-d');
        $intervals = ['weekly' => '+1 week', 'biweekly' => '+2 weeks', 'monthly' => '+1 month'];

        // Find all series roots for this home
        $roots = $choreModel
            ->where('home_id', $homeId)
            ->where('recurrence !=', 'none')
            ->where('recurrence_parent_id IS NULL')
            ->findAll();

        foreach ($roots as $root) {
            $interval = $intervals[$root['recurrence']] ?? null;
            if (!$interval || empty($members)) continue;

            // Count pending future instances
            $pendingCount = $choreModel
                ->where('home_id', $homeId)
                ->where('due_date >', $today)
                ->where('status', 'pending')
                ->groupStart()
                    ->where('id', $root['id'])
                    ->orWhere('recurrence_parent_id', $root['id'])
                ->groupEnd()
                ->countAllResults();

            if ($pendingCount >= 3) continue;

            // Find the last task in series (to continue from)
            $last = $choreModel
                ->where('home_id', $homeId)
                ->groupStart()
                    ->where('id', $root['id'])
                    ->orWhere('recurrence_parent_id', $root['id'])
                ->groupEnd()
                ->orderBy('due_date', 'DESC')
                ->first();

            if (!$last) continue;

            $baseDate  = $last['due_date'];
            $userIndex = array_search($last['assigned_user_id'], array_column($members, 'id'));
            if ($userIndex === false) $userIndex = 0;
            $limit     = $root['recurrence'] === 'monthly' ? 4 : 6;

            for ($i = 1; $i <= $limit; $i++) {
                $baseDate  = date('Y-m-d', strtotime($baseDate . ' ' . $interval));
                $nextUser  = $members[($userIndex + $i) % count($members)];
                $choreModel->insert([
                    'home_id'              => $homeId,
                    'task_name'            => $root['task_name'],
                    'icon'                 => $root['icon'],
                    'assigned_user_id'     => $nextUser['id'],
                    'due_date'             => $baseDate,
                    'penalty_amount'       => $root['penalty_amount'],
                    'recurrence'           => $root['recurrence'],
                    'recurrence_parent_id' => $root['id'],
                    'status'               => 'pending',
                ]);
            }
        }
    }

    private function buildCalendar(string $view, int $offset): array
    {
        $today = date('Y-m-d');

        if ($view === 'week') {
            $monday    = date('Y-m-d', strtotime('monday this week', strtotime("+{$offset} weeks")));
            $sunday    = date('Y-m-d', strtotime("+6 days", strtotime($monday)));
            $title     = date('d M', strtotime($monday)) . ' – ' . date('d M Y', strtotime($sunday));
            $days      = [];
            for ($i = 0; $i < 7; $i++) {
                $date = date('Y-m-d', strtotime("+{$i} days", strtotime($monday)));
                $days[] = ['date' => $date, 'num' => date('d', strtotime($date)), 'today' => $date === $today, 'other_month' => false, 'tasks' => []];
            }
            return [$days, $title, $monday, $sunday];
        }

        // Month view
        $year  = (int) date('Y', strtotime("+{$offset} months"));
        $month = (int) date('m', strtotime("+{$offset} months"));
        $firstDay = mktime(0, 0, 0, $month, 1, $year);
        $lastDay  = mktime(0, 0, 0, $month + 1, 0, $year);
        $title    = date('F Y', $firstDay);

        // ISO Monday-based: 1=Mon ... 7=Sun
        $startDow = (int) date('N', $firstDay); // 1–7
        $days     = [];

        // Padding before month
        for ($i = $startDow - 1; $i > 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days", $firstDay));
            $days[] = ['date' => $date, 'num' => date('d', strtotime($date)), 'today' => false, 'other_month' => true, 'tasks' => []];
        }
        // Days of month
        for ($d = 1; $d <= date('t', $firstDay); $d++) {
            $date = date('Y-m-d', mktime(0, 0, 0, $month, $d, $year));
            $days[] = ['date' => $date, 'num' => $d, 'today' => $date === $today, 'other_month' => false, 'tasks' => []];
        }
        // Padding after month (fill to complete rows of 7)
        $trailing = (7 - (count($days) % 7)) % 7;
        for ($i = 1; $i <= $trailing; $i++) {
            $date = date('Y-m-d', strtotime("+{$i} days", $lastDay));
            $days[] = ['date' => $date, 'num' => date('d', strtotime($date)), 'today' => false, 'other_month' => true, 'tasks' => []];
        }

        $startDate = date('Y-m-d', $firstDay);
        $endDate   = date('Y-m-d', $lastDay);

        return [$days, $title, $startDate, $endDate];
    }
}
