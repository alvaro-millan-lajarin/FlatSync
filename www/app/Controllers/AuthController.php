<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\UserTokenModel;
use App\Models\UserHomesModel;

class AuthController extends BaseController
{
    // ── Web ───────────────────────────────────────────────────────────────────

    public function login()
    {
        if (session()->get('isLoggedIn')) return redirect()->to('/dashboard');
        return view('auth/login');
    }

    public function loginPost()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            if ($this->isApi()) {
                return $this->apiError(implode(' ', $this->validator->getErrors()));
            }
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();
        $email     = $this->request->getPost('email');
        $password  = $this->request->getPost('password');
        $user      = $userModel->where('email', $email)->first();

        if (!$user || !password_verify($password, $user['password'])) {
            if ($this->isApi()) {
                return $this->apiError('Email o contraseña incorrectos.', 401);
            }
            return redirect()->back()->withInput()->with('error', 'Email o contraseña incorrectos.');
        }

        // Cargar hogares del usuario
        $uhModel = new UserHomesModel();
        $homes   = $uhModel->getHomesForUser($user['id']);
        $homeId  = !empty($homes) ? $homes[0]['id'] : null;
        $homeName= !empty($homes) ? $homes[0]['name'] : null;

        if ($this->isApi()) {
            $tokenModel = new UserTokenModel();
            $token = $tokenModel->generate($user['id'], $homeId);

            return $this->apiOk([
                'token'     => $token,
                'user'      => [
                    'id'       => $user['id'],
                    'username' => $user['username'],
                    'email'    => $user['email'],
                    'avatar_url'=> $user['avatar_url'] ?? null,
                ],
                'homes'     => $homes,
                'active_home_id' => $homeId,
            ]);
        }

        session()->set([
            'user_id'    => $user['id'],
            'username'   => $user['username'],
            'user_email' => $user['email'],
            'avatar_url' => $user['avatar_url'] ?? null,
            'isLoggedIn' => true,
        ]);

        return redirect()->to('/homes');
    }

    public function register()
    {
        if (session()->get('isLoggedIn')) return redirect()->to('/dashboard');
        return view('auth/register');
    }

    public function registerPost()
    {
        $rules = [
            'username' => 'required|min_length[2]|max_length[50]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
        ];

        $errors = ['email' => ['is_unique' => 'Este email ya está registrado.']];

        if (!$this->validate($rules, $errors)) {
            if ($this->isApi()) {
                return $this->apiError(implode(' ', $this->validator->getErrors()));
            }
            return view('auth/register', ['errors' => $this->validator->getErrors()]);
        }

        $userModel = new UserModel();
        $userId = $userModel->insert([
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
        ]);

        if ($this->isApi()) {
            $user = $userModel->find($userId);
            $tokenModel = new UserTokenModel();
            $token = $tokenModel->generate($userId);
            return $this->apiOk([
                'token' => $token,
                'user'  => [
                    'id'       => $user['id'],
                    'username' => $user['username'],
                    'email'    => $user['email'],
                ],
                'homes' => [],
            ]);
        }

        return redirect()->to('/login')->with('success', 'Cuenta creada. ¡Inicia sesión!');
    }

    public function logout()
    {
        if ($this->isApi()) {
            $header = $this->request->getHeaderLine('Authorization');
            if (preg_match('/Bearer\s+(.+)/i', $header, $m)) {
                $tokenModel = new UserTokenModel();
                $tokenModel->where('token', trim($m[1]))->delete();
            }
            return $this->apiOk(['message' => 'Sesión cerrada.']);
        }

        session()->destroy();
        return redirect()->to('/login');
    }
}
