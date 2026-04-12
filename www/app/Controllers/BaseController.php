<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected $request;
    protected $helpers = ['url', 'form', 'filesystem', 'avatar'];

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);
    }

    // ── API helpers ───────────────────────────────────────────────────────────

    /** Detecta si la petición viene de la app móvil */
    protected function isApi(): bool
    {
        return $this->request->hasHeader('Authorization') ||
               str_contains($this->request->getHeaderLine('Accept'), 'application/json');
    }

    /** Valida el Bearer token y carga el usuario en sesión */
    protected function loadApiUser(): bool
    {
        $header = $this->request->getHeaderLine('Authorization');
        if (!preg_match('/Bearer\s+(.+)/i', $header, $m)) {
            return false;
        }

        $tokenModel = new \App\Models\UserTokenModel();
        $row = $tokenModel->getUserByToken(trim($m[1]));
        if (!$row) return false;

        // Determinar home_id: cabecera X-Home-Id tiene prioridad sobre el token
        $homeId   = (int) ($this->request->getHeaderLine('X-Home-Id') ?: $row['home_id']);
        $homeName = $row['home_name'] ?? null;

        // Si X-Home-Id es distinto al del token, actualizar en DB
        if ($homeId && $homeId != $row['home_id']) {
            $homeModel = new \App\Models\HomeModel();
            $home = $homeModel->find($homeId);
            $homeName = $home['name'] ?? null;
            $tokenModel->updateHome(trim($m[1]), $homeId);
        }

        session()->set([
            'user_id'    => $row['user_id'],
            'username'   => $row['username'],
            'user_email' => $row['email'],
            'avatar_url' => $row['avatar_url'] ?? null,
            'isLoggedIn' => true,
            'home_id'    => $homeId ?: null,
            'home_name'  => $homeName,
        ]);

        return true;
    }

    protected function apiOk(array $data = []): \CodeIgniter\HTTP\Response
    {
        return $this->response->setStatusCode(200)->setJSON(array_merge(['ok' => true], $data));
    }

    protected function apiError(string $message, int $status = 400): \CodeIgniter\HTTP\Response
    {
        return $this->response->setStatusCode($status)->setJSON(['ok' => false, 'error' => $message]);
    }

    // ── Auth guards ───────────────────────────────────────────────────────────

    protected function requireLogin(): bool
    {
        if ($this->isApi()) {
            if (!$this->loadApiUser()) {
                $this->apiError('No autorizado', 401)->send();
                return true;
            }
            return false;
        }

        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login')->send() || true;
        }
        return false;
    }

    protected function requireHome(): bool
    {
        if ($this->requireLogin()) return true;

        if (!session()->get('home_id')) {
            if ($this->isApi()) {
                $this->apiError('Debes seleccionar un hogar. Envía X-Home-Id en la cabecera.', 400)->send();
                return true;
            }
            return redirect()->to('/homes')->send() || true;
        }
        return false;
    }
}
