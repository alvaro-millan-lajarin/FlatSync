<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\HomeModel;
use App\Models\UserHomesModel;
use App\Models\ExpenseModel;
use App\Models\ChoreModel;

class DemoController extends BaseController
{
    private const DEMO_EMAIL = 'demo@flatsync.internal';
    public  const DEMO_HOME  = 'Demo Piso';

    private const EXTRA_USERS = [
        ['email' => 'demo-maria@flatsync.internal', 'username' => 'María'],
        ['email' => 'demo-jorge@flatsync.internal', 'username' => 'Jorge'],
    ];

    private const SEED_DATA = [
        'es' => [
            'expenses' => [
                ['title' => 'Alquiler',            'amount' => 450.00, 'category' => 'rent',      'payer' => 0, 'date' => '-20 days'],
                ['title' => 'Mercadona',            'amount' =>  87.30, 'category' => 'groceries', 'payer' => 1, 'date' => '-15 days'],
                ['title' => 'Electricidad',         'amount' =>  54.80, 'category' => 'utilities', 'payer' => 0, 'date' => '-10 days'],
                ['title' => 'Netflix',              'amount' =>  15.99, 'category' => 'other',     'payer' => 2, 'date' => '-8 days'],
                ['title' => 'Internet',             'amount' =>  29.99, 'category' => 'utilities', 'payer' => 1, 'date' => '-5 days'],
                ['title' => 'Material limpieza',    'amount' =>  22.50, 'category' => 'groceries', 'payer' => 2, 'date' => '-3 days'],
            ],
            'chores' => [
                ['task_name' => 'Fregar los platos',         'assignee' => 0, 'days' => 0],
                ['task_name' => 'Pasar la aspiradora',       'assignee' => 1, 'days' => 1],
                ['task_name' => 'Sacar la basura',           'assignee' => 2, 'days' => 1],
                ['task_name' => 'Limpiar el baño',           'assignee' => 0, 'days' => 3],
                ['task_name' => 'Comprar papel higiénico',   'assignee' => 1, 'days' => 4],
            ],
        ],
        'en' => [
            'expenses' => [
                ['title' => 'Rent',                'amount' => 450.00, 'category' => 'rent',      'payer' => 0, 'date' => '-20 days'],
                ['title' => 'Supermarket',         'amount' =>  87.30, 'category' => 'groceries', 'payer' => 1, 'date' => '-15 days'],
                ['title' => 'Electricity',         'amount' =>  54.80, 'category' => 'utilities', 'payer' => 0, 'date' => '-10 days'],
                ['title' => 'Netflix',             'amount' =>  15.99, 'category' => 'other',     'payer' => 2, 'date' => '-8 days'],
                ['title' => 'Internet',            'amount' =>  29.99, 'category' => 'utilities', 'payer' => 1, 'date' => '-5 days'],
                ['title' => 'Cleaning supplies',   'amount' =>  22.50, 'category' => 'groceries', 'payer' => 2, 'date' => '-3 days'],
            ],
            'chores' => [
                ['task_name' => 'Wash the dishes',           'assignee' => 0, 'days' => 0],
                ['task_name' => 'Vacuum the flat',           'assignee' => 1, 'days' => 1],
                ['task_name' => 'Take out the trash',        'assignee' => 2, 'days' => 1],
                ['task_name' => 'Clean the bathroom',        'assignee' => 0, 'days' => 3],
                ['task_name' => 'Buy toilet paper',          'assignee' => 1, 'days' => 4],
            ],
        ],
        'ca' => [
            'expenses' => [
                ['title' => 'Lloguer',             'amount' => 450.00, 'category' => 'rent',      'payer' => 0, 'date' => '-20 days'],
                ['title' => 'Mercadona',            'amount' =>  87.30, 'category' => 'groceries', 'payer' => 1, 'date' => '-15 days'],
                ['title' => 'Electricitat',         'amount' =>  54.80, 'category' => 'utilities', 'payer' => 0, 'date' => '-10 days'],
                ['title' => 'Netflix',              'amount' =>  15.99, 'category' => 'other',     'payer' => 2, 'date' => '-8 days'],
                ['title' => 'Internet',             'amount' =>  29.99, 'category' => 'utilities', 'payer' => 1, 'date' => '-5 days'],
                ['title' => 'Material de neteja',   'amount' =>  22.50, 'category' => 'groceries', 'payer' => 2, 'date' => '-3 days'],
            ],
            'chores' => [
                ['task_name' => 'Fregar els plats',          'assignee' => 0, 'days' => 0],
                ['task_name' => "Passar l'aspiradora",       'assignee' => 1, 'days' => 1],
                ['task_name' => 'Treure les escombraries',   'assignee' => 2, 'days' => 1],
                ['task_name' => 'Netejar el bany',           'assignee' => 0, 'days' => 3],
                ['task_name' => 'Comprar paper higiènic',    'assignee' => 1, 'days' => 4],
            ],
        ],
    ];

    public function enter()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        $lang      = session()->get('lang') ?? 'es';
        $userModel = new UserModel();
        $homeModel = new HomeModel();
        $uhModel   = new UserHomesModel();

        // Find or create all demo users; index 0 = main visitor account
        $allDemoUsers = array_merge(
            [['email' => self::DEMO_EMAIL, 'username' => 'Demo']],
            self::EXTRA_USERS
        );
        $users = [];
        foreach ($allDemoUsers as $u) {
            $existing = $userModel->where('email', $u['email'])->first();
            if (!$existing) {
                $id       = $userModel->insert([
                    'username'       => $u['username'],
                    'email'          => $u['email'],
                    'password'       => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
                    'email_verified' => 1,
                ]);
                $existing = $userModel->find($id);
            }
            $users[] = $existing;
        }

        // Find or create demo home — track whether it's brand new
        $home       = $homeModel->where('name', self::DEMO_HOME)->first();
        $homeIsNew  = !$home;
        if ($homeIsNew) {
            $homeId = $homeModel->insert([
                'name'        => self::DEMO_HOME,
                'invite_code' => 'DEMO-' . strtoupper(substr(md5(self::DEMO_HOME), 0, 6)),
            ]);
            $home = $homeModel->find($homeId);
        }

        // Ensure every demo user is a member
        foreach ($users as $i => $u) {
            if (!$uhModel->where('user_id', $u['id'])->where('home_id', $home['id'])->first()) {
                $uhModel->insert(['user_id' => $u['id'], 'home_id' => $home['id'], 'is_admin' => ($i === 0) ? 1 : 0]);
            }
        }

        // Seed sample data only the first time the home is created
        if ($homeIsNew) {
            $this->seedDemoData(array_column($users, 'id'), (int)$home['id'], $lang);
        }

        $main = $users[0];
        session()->set([
            'user_id'    => $main['id'],
            'username'   => $main['username'],
            'user_email' => $main['email'],
            'avatar_url' => null,
            'isLoggedIn' => true,
            'home_id'    => $home['id'],
            'home_name'  => $home['name'],
            'is_admin'   => true,
        ]);

        return redirect()->to('/dashboard');
    }

    private function seedDemoData(array $userIds, int $homeId, string $lang): void
    {
        $data         = self::SEED_DATA[$lang] ?? self::SEED_DATA['es'];
        $expenseModel = new ExpenseModel();
        $choreModel   = new ChoreModel();
        $allIds       = json_encode($userIds);

        foreach ($data['expenses'] as $e) {
            $paidBy = $userIds[$e['payer']] ?? $userIds[0];
            $expenseModel->insert([
                'home_id'    => $homeId,
                'title'      => $e['title'],
                'amount'     => $e['amount'],
                'category'   => $e['category'],
                'paid_by'    => $paidBy,
                'date'       => date('Y-m-d', strtotime($e['date'])),
                'split_with' => $allIds,
            ]);
        }

        foreach ($data['chores'] as $c) {
            $assignee = $userIds[$c['assignee']] ?? $userIds[0];
            $choreModel->insert([
                'home_id'          => $homeId,
                'task_name'        => $c['task_name'],
                'assigned_user_id' => $assignee,
                'due_date'         => date('Y-m-d', strtotime('+' . $c['days'] . ' days')),
                'status'           => 'pending',
            ]);
        }
    }
}
