<?php

namespace App\Controllers;

use App\Models\HomeModel;
use App\Models\UserHomesModel;

class HomesController extends BaseController
{
    /** Home selector (cuando el usuario no tiene sesión activa o quiere cambiar) */
    public function select()
    {
        if ($this->requireLogin()) return;

        $userId  = session()->get('user_id');
        $uhModel = new UserHomesModel();
        $myHomes = $uhModel->getHomesForUser($userId);

        if ($this->isApi()) {
            return $this->apiOk(['homes' => $myHomes]);
        }

        return view('homes/select', ['myHomes' => $myHomes]);
    }

    /** Cambia la sesión activa */
    public function switchHome(int $homeId)
    {
        if ($this->requireLogin()) return;

        $userId  = session()->get('user_id');
        $uhModel = new UserHomesModel();

        if (!$uhModel->isMember($userId, $homeId)) {
            return redirect()->to('/homes')->with('error', 'No perteneces a ese hogar.');
        }

        $homeModel = new HomeModel();
        $home      = $homeModel->find($homeId);

        if (!$home) {
            return redirect()->to('/homes')->with('error', 'Hogar no encontrado.');
        }

        $isAdmin   = $uhModel->isAdmin($userId, $homeId);

        session()->set([
            'home_id'   => $homeId,
            'home_name' => $home['name'],
            'is_admin'  => $isAdmin,
        ]);

        if ($this->isApi()) {
            $tokenModel = new \App\Models\UserTokenModel();
            $header = $this->request->getHeaderLine('Authorization');
            if (preg_match('/Bearer\s+(.+)/i', $header, $m)) {
                $tokenModel->updateHome(trim($m[1]), $homeId);
            }
            return $this->apiOk(['home' => $home]);
        }

        return redirect()->to('/dashboard')->with('success', 'Sesión cambiada a «' . $home['name'] . '».');
    }

    /** Desactivar sesión activa (sigue siendo miembro del hogar) */
    public function leaveSession()
    {
        session()->remove(['home_id', 'home_name', 'is_admin']);
        return redirect()->to('/homes');
    }

    /** Abandonar hogar definitivamente (se elimina de user_homes) */
    public function leaveHome(int $homeId)
    {
        if ($this->requireLogin()) return;

        $userId  = session()->get('user_id');
        $uhModel = new UserHomesModel();

        if (!$uhModel->isMember($userId, $homeId)) {
            return redirect()->to('/homes')->with('error', 'No perteneces a ese hogar.');
        }

        // If only member and admin — must delete the home instead
        $members = $uhModel->getMembersOfHome($homeId);
        if (count($members) === 1) {
            return redirect()->to('/homes')->with('error', 'Eres el único miembro. Elimina el hogar en lugar de abandonarlo.');
        }

        $uhModel->where('user_id', $userId)->where('home_id', $homeId)->delete();

        // Clear session if this was the active home
        if (session()->get('home_id') == $homeId) {
            session()->remove(['home_id', 'home_name', 'is_admin']);
        }

        return redirect()->to('/homes')->with('success', 'Has abandonado el hogar.');
    }

    /** Eliminar hogar completo (solo admin) */
    public function deleteHome(int $homeId)
    {
        if ($this->requireLogin()) return;

        $userId  = session()->get('user_id');
        $uhModel = new UserHomesModel();

        if (!$uhModel->isAdmin($userId, $homeId)) {
            return redirect()->to('/homes')->with('error', 'Solo un administrador puede eliminar el hogar.');
        }

        $db = \Config\Database::connect();

        // Delete swap_requests for chores of this home
        $choreIds = $db->table('chores')->select('id')->where('home_id', $homeId)->get()->getResultArray();
        if (!empty($choreIds)) {
            $ids = array_column($choreIds, 'id');
            $db->table('swap_requests')->whereIn('chore_id', $ids)->delete();
        }

        $db->table('messages')->where('home_id', $homeId)->delete();
        $db->table('chores')->where('home_id', $homeId)->delete();
        $db->table('settlements')->where('home_id', $homeId)->delete();
        $db->table('expenses')->where('home_id', $homeId)->delete();
        $db->table('user_homes')->where('home_id', $homeId)->delete();
        $db->table('homes')->where('id', $homeId)->delete();

        // Clear session if this was the active home
        if (session()->get('home_id') == $homeId) {
            session()->remove(['home_id', 'home_name', 'is_admin']);
        }

        return redirect()->to('/homes')->with('success', 'Hogar eliminado correctamente.');
    }

    /** Unirse a un hogar con código (desde dentro de la app) */
    public function joinGet()
    {
        if ($this->requireLogin()) return;
        $prefill = $this->request->getGet('code') ?? '';
        return view('homes/join', ['prefill' => strtoupper($prefill)]);
    }

    public function joinByLink(string $code)
    {
        $code = strtoupper(trim($code));

        // Not logged in — save code and send to login/register
        if (!session()->get('isLoggedIn')) {
            session()->set('pending_invite', $code);
            return redirect()->to('/login')->with('info', lang('App.join_invite_login_prompt'));
        }

        $homeModel = new HomeModel();
        $home      = $homeModel->where('invite_code', $code)->first();

        if (!$home) {
            return redirect()->to('/homes')->with('error', lang('App.join_invalid_code'));
        }

        if ($home['name'] === \App\Controllers\DemoController::DEMO_HOME) {
            return redirect()->to('/homes')->with('error', lang('App.join_demo_blocked'));
        }

        $userId  = session()->get('user_id');
        $uhModel = new UserHomesModel();

        if ($uhModel->isMember($userId, $home['id'])) {
            session()->set(['home_id' => $home['id'], 'home_name' => $home['name'], 'is_admin' => $uhModel->isAdmin($userId, $home['id'])]);
            return redirect()->to('/dashboard')->with('success', lang('App.join_already_member'));
        }

        $uhModel->insert(['user_id' => $userId, 'home_id' => $home['id'], 'is_admin' => 0]);
        session()->set(['home_id' => $home['id'], 'home_name' => $home['name'], 'is_admin' => false]);

        return redirect()->to('/dashboard')->with('success', lang('App.join_welcome') . ' ' . $home['name'] . '!');
    }

    public function joinPost()
    {
        if ($this->requireLogin()) return;

        $userId     = session()->get('user_id');
        $inviteCode = strtoupper(trim($this->request->getPost('invite_code')));

        if (!$inviteCode) {
            return view('homes/join', ['error' => 'Introduce el código de invitación.']);
        }

        $homeModel = new HomeModel();
        $home      = $homeModel->where('invite_code', $inviteCode)->first();

        if (!$home) {
            return view('homes/join', ['error' => 'Código inválido. Compruébalo con el administrador.']);
        }

        if ($home['name'] === \App\Controllers\DemoController::DEMO_HOME) {
            return view('homes/join', ['error' => 'No puedes unirte a este hogar.']);
        }

        $uhModel = new UserHomesModel();

        if ($uhModel->isMember($userId, $home['id'])) {
            // Ya es miembro: simplemente cambiar a esa sesión
            session()->set([
                'home_id'   => $home['id'],
                'home_name' => $home['name'],
                'is_admin'  => $uhModel->isAdmin($userId, $home['id']),
            ]);
            return redirect()->to('/dashboard')->with('success', 'Ya eres miembro. Sesión cambiada a «' . $home['name'] . '».');
        }

        $uhModel->insert([
            'user_id'   => $userId,
            'home_id'   => $home['id'],
            'is_admin'  => 0,
            'joined_at' => date('Y-m-d H:i:s'),
        ]);

        session()->set([
            'home_id'   => $home['id'],
            'home_name' => $home['name'],
            'is_admin'  => false,
        ]);

        if ($this->isApi()) {
            return $this->apiOk([
                'home'        => $home,
                'invite_code' => $home['invite_code'],
            ]);
        }

        return redirect()->to('/dashboard')->with('success', '¡Te has unido a «' . $home['name'] . '»!');
    }

    /** Crear un nuevo hogar desde dentro de la app */
    public function createGet()
    {
        if ($this->requireLogin()) return;
        return view('homes/create');
    }

    public function createPost()
    {
        if ($this->requireLogin()) return;

        $userId   = session()->get('user_id');
        $homeName = trim($this->request->getPost('home_name'));

        if (strlen($homeName) < 2) {
            return view('homes/create', ['error' => 'El nombre del hogar debe tener al menos 2 caracteres.']);
        }

        $homeModel = new HomeModel();
        $homeId    = $homeModel->insert([
            'name'            => $homeName,
            'invite_code'     => strtoupper(substr(md5(uniqid('', true) . random_bytes(8)), 0, 4)) . '-' . strtoupper(substr(md5(uniqid('', true) . random_bytes(8)), 0, 4)) . '-' . strtoupper(substr(md5(uniqid('', true) . random_bytes(8)), 0, 4)),
            'default_penalty' => 2.00,
        ]);

        $uhModel = new UserHomesModel();
        $uhModel->insert([
            'user_id'   => $userId,
            'home_id'   => $homeId,
            'is_admin'  => 1,
            'joined_at' => date('Y-m-d H:i:s'),
        ]);

        $home = $homeModel->find($homeId);

        session()->set([
            'home_id'   => $homeId,
            'home_name' => $home['name'],
            'is_admin'  => true,
        ]);

        if ($this->isApi()) {
            return $this->apiOk([
                'home'        => $home,
                'invite_code' => $home['invite_code'],
            ]);
        }

        return redirect()->to('/dashboard')->with('success', '¡Hogar «' . $homeName . '» creado! Comparte el código en Miembros.');
    }
}