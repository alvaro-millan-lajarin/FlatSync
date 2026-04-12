<?php

namespace App\Controllers;

use App\Models\UserModel;

class ProfileController extends BaseController
{
    public function index()
    {
        if ($this->requireLogin()) return;

        $userModel = new UserModel();
        $user = $userModel->find(session()->get('user_id'));

        return view('profile/index', [
            'pageTitle' => 'Mi Perfil',
            'activeNav' => 'profile',
            'user'      => $user,
        ]);
    }

    public function edit()
    {
        if ($this->requireLogin()) return;

        $userModel = new UserModel();
        $user = $userModel->find(session()->get('user_id'));

        return view('profile/edit', [
            'pageTitle' => 'Editar perfil',
            'activeNav' => 'profile',
            'user'      => $user,
            'errors'    => [],
        ]);
    }

    public function update()
    {
        if ($this->requireLogin()) return;

        $userId    = session()->get('user_id');
        $userModel = new UserModel();
        $user      = $userModel->find($userId);

        $username = trim($this->request->getPost('username'));
        $email    = trim($this->request->getPost('email'));

        // Validate
        $errors = [];
        if (strlen($username) < 2) {
            $errors[] = 'El nombre debe tener al menos 2 caracteres.';
        }
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email no tiene un formato válido.';
        }
        if ($email && $email !== $user['email']) {
            $existing = $userModel->where('email', $email)->where('id !=', $userId)->first();
            if ($existing) {
                $errors[] = 'Ese email ya está en uso por otra cuenta.';
            }
        }

        if (!empty($errors)) {
            return view('profile/edit', ['user' => array_merge($user, ['username' => $username, 'email' => $email]), 'errors' => $errors]);
        }

        $data = [
            'username' => $username,
            'email'    => $email ?: $user['email'],
        ];

        // Handle avatar upload
        $file = $this->request->getFile('avatar');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Delete old avatar
            if ($user['avatar_url']) {
                $oldPath = ROOTPATH . 'public/' . $user['avatar_url'];
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $newName = 'avatar_' . $userId . '_' . time() . '.' . $file->getExtension();
            $file->move(ROOTPATH . 'public/uploads/avatars', $newName);
            $data['avatar_url'] = 'uploads/avatars/' . $newName;
        }

        // Handle avatar removal
        if ($this->request->getPost('remove_avatar') === '1') {
            if ($user['avatar_url']) {
                $oldPath = ROOTPATH . 'public/' . $user['avatar_url'];
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $data['avatar_url'] = null;
        }

        $userModel->update($userId, $data);

        // Refresh session
        session()->set([
            'username'   => $data['username'],
            'user_email' => $data['email'],
            'avatar_url' => $data['avatar_url'] ?? ($user['avatar_url'] ?? null),
        ]);

        return redirect()->to('/profile')->with('success', 'Perfil actualizado correctamente.');
    }
}
