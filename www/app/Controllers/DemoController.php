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
    private const DEMO_HOME  = 'Demo Piso';

    public function enter()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        $userModel  = new UserModel();
        $homeModel  = new HomeModel();
        $uhModel    = new UserHomesModel();

        // Find or create demo user
        $user = $userModel->where('email', self::DEMO_EMAIL)->first();
        if (!$user) {
            $userId = $userModel->insert([
                'username'       => 'Demo',
                'email'          => self::DEMO_EMAIL,
                'password'       => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
                'email_verified' => 1,
            ]);
            $user = $userModel->find($userId);
        }

        // Find or create demo home
        $home = $homeModel->where('name', self::DEMO_HOME)->first();
        if (!$home) {
            $homeId = $homeModel->insert([
                'name'        => self::DEMO_HOME,
                'invite_code' => 'DEMO-' . strtoupper(substr(md5(self::DEMO_HOME), 0, 6)),
            ]);
            $home = $homeModel->find($homeId);
        }

        // Ensure demo user is a member of demo home
        $membership = $uhModel->where('user_id', $user['id'])->where('home_id', $home['id'])->first();
        if (!$membership) {
            $uhModel->insert(['user_id' => $user['id'], 'home_id' => $home['id'], 'is_admin' => 1]);
            $this->seedDemoData((int)$user['id'], (int)$home['id']);
        }

        session()->set([
            'user_id'    => $user['id'],
            'username'   => $user['username'],
            'user_email' => $user['email'],
            'avatar_url' => null,
            'isLoggedIn' => true,
            'home_id'    => $home['id'],
            'home_name'  => $home['name'],
            'is_admin'   => true,
        ]);

        return redirect()->to('/dashboard');
    }

    private function seedDemoData(int $userId, int $homeId): void
    {
        $expenseModel = new ExpenseModel();
        $choreModel   = new ChoreModel();

        $expenses = [
            ['title' => 'Alquiler',      'amount' => 450.00, 'category' => 'rent',     'date' => date('Y-m-01')],
            ['title' => 'Mercadona',     'amount' =>  87.30, 'category' => 'groceries', 'date' => date('Y-m-05')],
            ['title' => 'Electricidad',  'amount' =>  54.80, 'category' => 'utilities', 'date' => date('Y-m-08')],
            ['title' => 'Netflix',       'amount' =>  15.99, 'category' => 'other',     'date' => date('Y-m-10')],
            ['title' => 'Internet',      'amount' =>  29.99, 'category' => 'utilities', 'date' => date('Y-m-12')],
        ];
        foreach ($expenses as $e) {
            $expenseModel->insert(array_merge($e, ['home_id' => $homeId, 'paid_by' => $userId]));
        }

        $chores = [
            ['task_name' => 'Fregar los platos',  'due_date' => date('Y-m-d'), 'status' => 'pending'],
            ['task_name' => 'Pasar la aspiradora', 'due_date' => date('Y-m-d', strtotime('+1 day')), 'status' => 'pending'],
            ['task_name' => 'Comprar papel higiénico', 'due_date' => date('Y-m-d', strtotime('+2 days')), 'status' => 'pending'],
        ];
        foreach ($chores as $c) {
            $choreModel->insert(array_merge($c, ['home_id' => $homeId, 'assigned_user_id' => $userId]));
        }
    }
}
