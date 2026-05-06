<?php

namespace App\Controllers;

use App\Models\GameScoreModel;

class GameController extends BaseController
{
    public function index()
    {
        if ($this->requireHome()) return;

        $homeId  = session()->get('home_id');
        $model   = new GameScoreModel();
        $ranking = $model->getRanking($homeId);

        return view('game/index', [
            'pageTitle'     => lang('App.game_title'),
            'pageSubtitle'  => lang('App.game_subtitle'),
            'activeNav'     => 'game',
            'ranking'       => $ranking,
            'currentUserId' => (int) session()->get('user_id'),
        ]);
    }

    public function saveScore()
    {
        if ($this->requireHome()) return;

        $userId = (int) session()->get('user_id');
        $homeId = (int) session()->get('home_id');
        $score  = (int) ($this->request->getPost('score') ?? 0);

        if ($score < 0) {
            return $this->response->setJSON(['ok' => false]);
        }

        $model     = new GameScoreModel();
        $newRecord = $model->saveIfBetter($userId, $homeId, $score);
        $ranking   = $model->getRanking($homeId);

        return $this->response->setJSON([
            'ok'         => true,
            'new_record' => $newRecord,
            'ranking'    => $ranking,
        ]);
    }
}
