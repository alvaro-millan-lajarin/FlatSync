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
            'pageTitle'      => 'Calendario de Tareas',
            'pageSubtitle'   => $calendarTitle,
            'activeNav'      => 'chores',
            'view'          => $view,
            'offset'        => $offset,
            'calDays'       => $calDays,
            'calendarTitle' => $calendarTitle,
            'chores'        => $allChores,
            'missedChores'  => $missedChores,
            'members'       => $members,
            'defaultPenalty'=> $home['default_penalty'] ?? 2.00,
        ]);
    }

    /** GET JSON: tareas nuevas desde ?after=id (igual que el chat) */
    public function poll()
    {
        if (!session()->get('isLoggedIn') || !session()->get('home_id')) {
            return $this->response->setJSON(['chores' => []]);
        }
        $homeId  = session()->get('home_id');
        $afterId = (int) ($this->request->getGet('after') ?? 0);

        $uhModel  = new UserHomesModel();
        $members  = $uhModel->getMembersOfHome($homeId);
        $colorMap = [];
        foreach ($members as $i => $m) {
            $colorMap[$m['id']] = self::USER_COLORS[$i % count(self::USER_COLORS)];
        }

        $choreModel = new ChoreModel();
        $chores = $choreModel
            ->select('chores.*, users.username AS assigned_name')
            ->join('users', 'users.id = chores.assigned_user_id')
            ->where('chores.home_id', $homeId)
            ->where('chores.id >', $afterId)
            ->orderBy('chores.due_date', 'ASC')
            ->findAll();

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

        $newId = $choreModel->insert($data);

        // If recurring, generate next occurrences (4 iterations)
        $recurrence = $data['recurrence'];
        if ($recurrence !== 'none') {
            $intervals = ['weekly' => '+1 week', 'biweekly' => '+2 weeks', 'monthly' => '+1 month'];
            $interval  = $intervals[$recurrence] ?? null;
            $members   = (new UserHomesModel())->getMembersOfHome($homeId);
            $userCount = count($members);
            if ($interval && $userCount > 0) {
                $baseDate   = $data['due_date'];
                $userIndex  = array_search($data['assigned_user_id'], array_column($members, 'id'));
                for ($i = 1; $i <= 4; $i++) {
                    $baseDate = date('Y-m-d', strtotime($baseDate . ' ' . $interval));
                    $nextUser = $members[($userIndex + $i) % $userCount];
                    $choreModel->insert(array_merge($data, [
                        'due_date'         => $baseDate,
                        'assigned_user_id' => $nextUser['id'],
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

        return redirect()->to('/chores')->with('success', 'Tarea creada correctamente.');
    }

    public function markDone(int $id)
    {
        if ($this->requireHome()) return;

        $choreModel = new ChoreModel();
        $chore      = $choreModel->find($id);

        if (!$chore || $chore['assigned_user_id'] != session()->get('user_id')) {
            return redirect()->back()->with('error', 'No tienes permiso para esto.');
        }

        $choreModel->update($id, ['status' => 'done', 'completed_at' => date('Y-m-d H:i:s')]);

        if ($this->isApi()) return $this->apiOk();

        return redirect()->back()->with('success', '¡Tarea completada!');
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
            return redirect()->back()->with('error', 'Tarea no encontrada.');
        }

        $choreModel->update($id, [
            'task_name'        => $this->request->getPost('task_name'),
            'assigned_user_id' => $this->request->getPost('assigned_user_id'),
            'due_date'         => $this->request->getPost('due_date'),
            'penalty_amount'   => $this->request->getPost('penalty_amount'),
        ]);

        if ($this->isApi()) return $this->apiOk();

        return redirect()->back()->with('success', 'Tarea actualizada.');
    }

    public function delete(int $id)
    {
        if ($this->requireHome()) return;

        $choreModel = new ChoreModel();
        $chore      = $choreModel->find($id);

        if (!$chore || $chore['home_id'] != session()->get('home_id')) {
            return redirect()->back()->with('error', 'Tarea no encontrada.');
        }

        $choreModel->delete($id);

        if ($this->isApi()) return $this->apiOk();

        return redirect()->back()->with('success', 'Tarea eliminada.');
    }

    public function swapRequests()
    {
        if ($this->requireHome()) return;

        $userId    = session()->get('user_id');
        $swapModel = new SwapModel();

        $incoming = $swapModel
            ->select('swap_requests.*, chores.task_name, chores.due_date, u1.username AS requester_name')
            ->join('chores', 'chores.id = swap_requests.chore_id')
            ->join('users AS u1', 'u1.id = swap_requests.requester_user_id')
            ->where('swap_requests.target_user_id', $userId)
            ->orderBy('swap_requests.created_at', 'DESC')
            ->findAll();

        $outgoing = $swapModel
            ->select('swap_requests.*, chores.task_name, chores.due_date, u2.username AS target_name')
            ->join('chores', 'chores.id = swap_requests.chore_id')
            ->join('users AS u2', 'u2.id = swap_requests.target_user_id')
            ->where('swap_requests.requester_user_id', $userId)
            ->orderBy('swap_requests.created_at', 'DESC')
            ->findAll();

        return view('chores/swaps', [
            'pageTitle'    => 'Intercambios de Tareas',
            'pageSubtitle' => 'Gestiona las solicitudes de cambio',
            'activeNav'    => 'swaps',
            'incoming'     => $incoming,
            'outgoing'     => $outgoing,
        ]);
    }

    public function swapRequest()
    {
        if ($this->requireHome()) return;

        $userId = session()->get('user_id');

        $rules = [
            'chore_id'       => 'required|is_natural_no_zero',
            'target_user_id' => 'required|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('error', 'Datos de solicitud inválidos.');
        }

        $swapModel = new SwapModel();
        $swapModel->insert([
            'chore_id'           => $this->request->getPost('chore_id'),
            'requester_user_id'  => $userId,
            'target_user_id'     => $this->request->getPost('target_user_id'),
            'compensation'       => (float) ($this->request->getPost('compensation') ?? 0),
            'message'            => $this->request->getPost('message'),
            'status'             => 'pending',
        ]);

        return redirect()->to('/chores/swap-requests')->with('success', 'Solicitud de intercambio enviada.');
    }

    public function swapAccept(int $id)
    {
        if ($this->requireHome()) return;

        $userId    = session()->get('user_id');
        $swapModel = new SwapModel();
        $swap      = $swapModel->find($id);

        if (!$swap || $swap['target_user_id'] != $userId) {
            return redirect()->back()->with('error', 'No tienes permiso.');
        }

        // Reassign the chore
        $choreModel = new ChoreModel();
        $choreModel->update($swap['chore_id'], ['assigned_user_id' => $userId]);

        $swapModel->update($id, ['status' => 'accepted']);

        // If compensation, add to expense balance as a penalty/credit
        if ($swap['compensation'] > 0) {
            $expenseModel = new ExpenseModel();
            $chore = $choreModel->find($swap['chore_id']);
            $expenseModel->insert([
                'home_id'     => session()->get('home_id'),
                'title'       => 'Compensación intercambio: ' . ($chore['task_name'] ?? 'tarea'),
                'amount'      => $swap['compensation'],
                'paid_by'     => $swap['requester_user_id'],
                'category'    => 'other',
                'date'        => date('Y-m-d'),
                'description' => 'Pago automático por intercambio de tarea aceptado.',
            ]);
        }

        return redirect()->back()->with('success', 'Intercambio aceptado. La tarea es tuya ahora.');
    }

    public function swapDecline(int $id)
    {
        if ($this->requireHome()) return;

        $userId    = session()->get('user_id');
        $swapModel = new SwapModel();
        $swap      = $swapModel->find($id);

        if (!$swap || $swap['target_user_id'] != $userId) {
            return redirect()->back()->with('error', 'No tienes permiso.');
        }

        $swapModel->update($id, ['status' => 'declined']);
        return redirect()->back()->with('success', 'Solicitud rechazada.');
    }

    public function swapCancel(int $id)
    {
        if ($this->requireHome()) return;

        $userId    = session()->get('user_id');
        $swapModel = new SwapModel();
        $swap      = $swapModel->find($id);

        if (!$swap || $swap['requester_user_id'] != $userId) {
            return redirect()->back()->with('error', 'No tienes permiso.');
        }

        $swapModel->delete($id);
        return redirect()->back()->with('success', 'Solicitud cancelada.');
    }

    // ── Private helpers ──────────────────────────────────────────────────────

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
