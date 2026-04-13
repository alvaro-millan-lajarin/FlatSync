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

    // ── Google OAuth ─────────────────────────────────────────────────────────

    public function googleRedirect()
    {
        if (session()->get('isLoggedIn')) return redirect()->to('/dashboard');

        $clientId = getenv('GOOGLE_CLIENT_ID');
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Google OAuth no configurado.');
        }

        $state = bin2hex(random_bytes(16));
        session()->set('oauth_state', $state);

        $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id'     => $clientId,
            'redirect_uri'  => getenv('GOOGLE_REDIRECT_URI'),
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'state'         => $state,
            'access_type'   => 'online',
            'prompt'        => 'select_account',
        ]);

        return redirect()->to($url);
    }

    public function googleCallback()
    {
        $state = $this->request->getGet('state');
        $code  = $this->request->getGet('code');
        $error = $this->request->getGet('error');

        if ($error) {
            return redirect()->to('/login')->with('error', 'Inicio de sesión con Google cancelado.');
        }

        if (!$state || $state !== session()->get('oauth_state')) {
            return redirect()->to('/login')->with('error', 'Estado OAuth inválido. Inténtalo de nuevo.');
        }
        session()->remove('oauth_state');

        if (!$code) {
            return redirect()->to('/login')->with('error', 'No se recibió código de autorización.');
        }

        // Exchange code → access token
        $tokenData = $this->googleExchangeCode($code);
        if (empty($tokenData['access_token'])) {
            return redirect()->to('/login')->with('error', 'Error al obtener token de Google.');
        }

        // Get user profile
        $googleUser = $this->googleGetUserInfo($tokenData['access_token']);
        if (empty($googleUser['email'])) {
            return redirect()->to('/login')->with('error', 'No se pudo obtener el perfil de Google.');
        }

        $userModel = new UserModel();

        // 1) Find by google_id
        $user = $userModel->where('google_id', $googleUser['sub'])->first();

        if (!$user) {
            // 2) Find by email (link existing account)
            $user = $userModel->where('email', $googleUser['email'])->first();
            if ($user) {
                $userModel->update($user['id'], [
                    'google_id'  => $googleUser['sub'],
                    'avatar_url' => $user['avatar_url'] ?: ($googleUser['picture'] ?? null),
                ]);
                $user = $userModel->find($user['id']);
            } else {
                // 3) Create new user
                $username = trim($googleUser['given_name'] ?? explode('@', $googleUser['email'])[0]);
                $userId = $userModel->insert([
                    'username'   => $username,
                    'email'      => $googleUser['email'],
                    'password'   => null,
                    'google_id'  => $googleUser['sub'],
                    'avatar_url' => $googleUser['picture'] ?? null,
                ]);
                $user = $userModel->find($userId);
            }
        }

        $uhModel = new UserHomesModel();
        $homes   = $uhModel->getHomesForUser($user['id']);

        session()->set([
            'user_id'    => $user['id'],
            'username'   => $user['username'],
            'user_email' => $user['email'],
            'avatar_url' => $user['avatar_url'] ?? null,
            'isLoggedIn' => true,
        ]);

        return redirect()->to('/homes');
    }

    private function googleExchangeCode(string $code): ?array
    {
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'code'          => $code,
                'client_id'     => getenv('GOOGLE_CLIENT_ID'),
                'client_secret' => getenv('GOOGLE_CLIENT_SECRET'),
                'redirect_uri'  => getenv('GOOGLE_REDIRECT_URI'),
                'grant_type'    => 'authorization_code',
            ]),
        ]);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res ? json_decode($res, true) : null;
    }

    private function googleGetUserInfo(string $accessToken): ?array
    {
        $ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
        ]);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res ? json_decode($res, true) : null;
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
