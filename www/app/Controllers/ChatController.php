<?php

namespace App\Controllers;

use App\Models\MessageModel;
use App\Models\NoteModel;

class ChatController extends BaseController
{
    public function index()
    {
        if ($this->requireHome()) return;

        $homeId   = session()->get('home_id');
        $msgModel  = new MessageModel();
        $noteModel = new NoteModel();
        $messages  = $msgModel->getMessages($homeId, 60);
        $notes     = $noteModel->getForHome($homeId);

        if ($this->isApi()) {
            return $this->apiOk([
                'messages' => $messages,
                'notes'    => $notes,
            ]);
        }

        return view('chat/index', [
            'pageTitle' => lang('App.chat_title'),
            'activeNav' => 'chat',
            'messages'  => $messages,
            'notes'     => $notes,
        ]);
    }

    /** POST: enviar mensaje */
    public function send()
    {
        if ($this->requireHome()) return;

        $text = trim($this->request->getPost('message'));

        if ($text === '' || mb_strlen($text) > 1000) {
            return redirect()->back()->with('error', 'Mensaje inválido.');
        }

        $msgModel = new MessageModel();
        $msgModel->insert([
            'home_id'    => session()->get('home_id'),
            'user_id'    => session()->get('user_id'),
            'message'    => $text,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // If AJAX fetch, return JSON; otherwise redirect (form fallback)
        if ($this->request->isAJAX() || $this->request->getHeaderLine('Accept') === 'application/json') {
            return $this->response->setJSON(['ok' => true]);
        }
        return redirect()->to('/chat');
    }

    /** POST: añadir nota */
    public function noteStore()
    {
        if ($this->requireHome()) return;

        $content = trim($this->request->getPost('content'));
        if ($content === '' || mb_strlen($content) > 500) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON(['ok' => false]);
            }
            return redirect()->to('/chat')->with('error', 'Nota inválida.');
        }

        $noteModel = new NoteModel();
        $id = $noteModel->insert([
            'home_id' => session()->get('home_id'),
            'user_id' => session()->get('user_id'),
            'content' => $content,
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'ok'   => true,
                'note' => [
                    'id'         => $id,
                    'content'    => $content,
                    'username'   => session()->get('username'),
                    'user_id'    => session()->get('user_id'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'ts'         => time(),
                ],
            ]);
        }

        return redirect()->to('/chat');
    }

    /** POST: eliminar nota */
    public function noteDelete(int $id)
    {
        if ($this->requireHome()) return;

        $noteModel = new NoteModel();
        $note = $noteModel->find($id);
        if ($note && $note['home_id'] == session()->get('home_id')) {
            $noteModel->delete($id);
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['ok' => true]);
        }

        return redirect()->to('/chat');
    }

    /** POST: eliminar mensaje propio */
    public function messageDelete(int $id)
    {
        if ($this->requireHome()) return;

        $msgModel = new MessageModel();
        $msg = $msgModel->find($id);

        if ($msg && $msg['home_id'] == session()->get('home_id') && $msg['user_id'] == session()->get('user_id')) {
            $msgModel->delete($id);
            return $this->response->setJSON(['ok' => true]);
        }

        return $this->response->setStatusCode(403)->setJSON(['ok' => false]);
    }

    /** POST: editar mensaje propio */
    public function messageEdit(int $id)
    {
        if ($this->requireHome()) return;

        $text = trim($this->request->getPost('message'));
        if ($text === '' || mb_strlen($text) > 1000) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false]);
        }

        $msgModel = new MessageModel();
        $msg = $msgModel->find($id);

        if ($msg && $msg['home_id'] == session()->get('home_id') && $msg['user_id'] == session()->get('user_id')) {
            $msgModel->update($id, ['message' => $text, 'edited' => 1]);
            return $this->response->setJSON(['ok' => true, 'message' => $text]);
        }

        return $this->response->setStatusCode(403)->setJSON(['ok' => false]);
    }

    /** GET JSON: mensajes nuevos + estado actual de notas (para polling) */
    public function poll()
    {
        if (!session()->get('isLoggedIn') || !session()->get('home_id')) {
            return $this->response->setJSON(['messages' => [], 'notes' => []]);
        }

        $afterId   = (int) ($this->request->getGet('after') ?? 0);
        $homeId    = session()->get('home_id');
        $msgModel  = new MessageModel();
        $noteModel = new NoteModel();
        $messages  = $msgModel->getMessages($homeId, 50, $afterId);
        $notes     = $noteModel->getForHome($homeId);

        foreach ($messages as &$m) {
            $m['ts'] = strtotime($m['created_at']);
        }
        foreach ($notes as &$n) {
            $n['ts'] = strtotime($n['created_at']);
        }

        return $this->response->setJSON(['messages' => $messages, 'notes' => $notes]);
    }
}
