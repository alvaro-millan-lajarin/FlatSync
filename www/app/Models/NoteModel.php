<?php

namespace App\Models;

use CodeIgniter\Model;

class NoteModel extends Model
{
    protected $table      = 'notes';
    protected $primaryKey = 'id';
    protected $allowedFields = ['home_id', 'user_id', 'content'];
    protected $useTimestamps = true;

    public function __construct()
    {
        parent::__construct();
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('notes')) {
            $db->query("
                CREATE TABLE notes (
                    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    home_id    INT UNSIGNED NOT NULL,
                    user_id    INT UNSIGNED NOT NULL,
                    content    TEXT NOT NULL,
                    created_at DATETIME NULL,
                    updated_at DATETIME NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }
    }

    public function getForHome(int $homeId): array
    {
        return $this->select('notes.*, users.username')
                    ->join('users', 'users.id = notes.user_id')
                    ->where('notes.home_id', $homeId)
                    ->orderBy('notes.created_at', 'DESC')
                    ->findAll();
    }
}
