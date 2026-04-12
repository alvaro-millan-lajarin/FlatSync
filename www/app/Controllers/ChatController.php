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
            'pageTitle' => 'Chat del hogar',
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
            'home_id' => session()->get('home_id'),
            'user_id' => session()->get('user_id'),
            'message' => $text,
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
            return redirect()->to('/chat')->with('error', 'Nota inválida.');
        }

        $noteModel = new NoteModel();
        $noteModel->insert([
            'home_id' => session()->get('home_id'),
            'user_id' => session()->get('user_id'),
            'content' => $content,
        ]);

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

        return redirect()->to('/chat');
    }

    /** GET JSON: mensajes nuevos desde un id (para polling) */
    public function poll()
    {
        if (!session()->get('isLoggedIn') || !session()->get('home_id')) {
            return $this->response->setJSON(['messages' => []]);
        }

        $afterId  = (int) ($this->request->getGet('after') ?? 0);
        $homeId   = session()->get('home_id');
        $msgModel = new MessageModel();
        $messages = $msgModel->getMessages($homeId, 50, $afterId);

        return $this->response->setJSON(['messages' => $messages]);
    }
}
