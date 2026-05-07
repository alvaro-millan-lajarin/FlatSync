<?php

namespace App\Controllers;

use App\Libraries\NotificationService;
use App\Models\HomeModel;
use App\Models\UserHomesModel;

class DevController extends BaseController
{
    public function emailPreview()
    {
        // Only accessible when logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $homeId = session()->get('home_id');
        $userId = session()->get('user_id');

        if (!$homeId) {
            return $this->response->setBody('<p>No home selected.</p>');
        }

        $homeModel = new HomeModel();
        $uhModel   = new UserHomesModel();

        $home    = $homeModel->find($homeId);
        $members = $uhModel->getMembersOfHome($homeId);
        $member  = null;
        foreach ($members as $m) {
            if ((int)$m['id'] === (int)$userId) { $member = $m; break; }
        }
        if (!$member) $member = $members[0] ?? null;
        if (!$member || !$home) {
            return $this->response->setBody('<p>No data found.</p>');
        }

        $year  = (int)($this->request->getGet('year')  ?? date('Y'));
        $month = (int)($this->request->getGet('month') ?? date('m'));

        $svc   = new NotificationService();
        $stats = $this->callPrivate($svc, 'computeStats', [$homeId, $year, $month, $members]);
        $html  = $this->callPrivate($svc, 'summaryBody',  [$member, $home['name'], $stats, $year, $month]);

        return $this->response->setHeader('Content-Type', 'text/html')->setBody($html);
    }

    private function callPrivate(object $obj, string $method, array $args): mixed
    {
        $ref = new \ReflectionMethod($obj, $method);
        $ref->setAccessible(true);
        return $ref->invokeArgs($obj, $args);
    }
}
