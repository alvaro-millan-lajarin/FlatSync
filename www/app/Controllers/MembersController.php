<?php

namespace App\Controllers;

use App\Models\HomeModel;
use App\Models\ChoreModel;
use App\Models\ExpenseModel;
use App\Models\UserHomesModel;

class MembersController extends BaseController
{
    public function index()
    {
        if ($this->requireHome()) return;

        $homeId       = session()->get('home_id');
        $homeModel    = new HomeModel();
        $expenseModel = new ExpenseModel();
        $choreModel   = new ChoreModel();
        $uhModel      = new UserHomesModel();

        $members = $uhModel->getMembersOfHome($homeId);
        $home    = $homeModel->find($homeId);

        // Stats per member
        $memberStats = [];
        foreach ($members as $m) {
            $paid = $expenseModel
                ->selectSum('amount')
                ->where('home_id', $homeId)
                ->where('paid_by', $m['id'])
                ->first()['amount'] ?? 0;

            $choresDone = $choreModel
                ->where('home_id', $homeId)
                ->where('assigned_user_id', $m['id'])
                ->where('status', 'done')
                ->countAllResults();

            $choresMissed = $choreModel
                ->where('home_id', $homeId)
                ->where('assigned_user_id', $m['id'])
                ->where('status', 'missed')
                ->countAllResults();

            $memberStats[] = [
                'id'            => $m['id'],
                'username'      => $m['username'],
                'email'         => $m['email'],
                'is_admin'      => $m['is_admin'],
                'total_paid'    => $paid,
                'chores_done'   => $choresDone,
                'chores_missed' => $choresMissed,
            ];
        }

        if ($this->isApi()) {
            return $this->apiOk([
                'home'    => $home,
                'members' => $memberStats,
            ]);
        }

        return view('members', [
            'pageTitle'    => lang('App.members_title'),
            'pageSubtitle' => $home['name'] ?? '',
            'activeNav'    => 'members',
            'memberStats'  => $memberStats,
            'home'         => $home,
        ]);
    }
}
