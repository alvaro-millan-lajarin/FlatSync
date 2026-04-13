<?php

namespace App\Models;

use CodeIgniter\Model;

class MessageModel extends Model
{
    protected $table         = 'messages';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['home_id', 'user_id', 'message'];
    protected $useTimestamps = false;

    public function __construct()
    {
        parent::__construct();
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('messages')) {
            $db->query("
                CREATE TABLE messages (
                    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    home_id    INT UNSIGNED NOT NULL,
                    user_id    INT UNSIGNED NOT NULL,
                    message    TEXT NOT NULL,
                    created_at DATETIME NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }
    }

    public function getMessages(int $homeId, int $limit = 60, int $afterId = 0): array
    {
        $q = $this->db->table('messages m')
            ->select('m.id, m.message, m.created_at, u.username, u.id AS user_id, u.avatar_url')
            ->join('users u', 'u.id = m.user_id')
            ->where('m.home_id', $homeId)
            ->orderBy('m.created_at', 'ASC')
            ->limit($limit);

        if ($afterId > 0) {
            $q->where('m.id >', $afterId);
        }

        return $q->get()->getResultArray();
    }
}
