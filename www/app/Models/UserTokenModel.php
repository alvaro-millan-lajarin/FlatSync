<?php

namespace App\Models;

use CodeIgniter\Model;

class UserTokenModel extends Model
{
    protected $table      = 'api_tokens';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'token', 'home_id'];
    protected $useTimestamps = true;

    public function __construct()
    {
        parent::__construct();
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('api_tokens')) {
            $db->query("
                CREATE TABLE api_tokens (
                    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    user_id    INT UNSIGNED NOT NULL,
                    token      VARCHAR(64) NOT NULL UNIQUE,
                    home_id    INT UNSIGNED NULL,
                    created_at DATETIME NULL,
                    updated_at DATETIME NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }
    }

    public function generate(int $userId, ?int $homeId = null): string
    {
        // Remove old tokens for this user
        $this->where('user_id', $userId)->delete();

        $token = bin2hex(random_bytes(32)); // 64 chars
        $this->insert([
            'user_id' => $userId,
            'token'   => $token,
            'home_id' => $homeId,
        ]);
        return $token;
    }

    public function getUserByToken(string $token): ?array
    {
        $row = $this->select('api_tokens.*, users.username, users.email, users.avatar_url, homes.name AS home_name')
                    ->join('users', 'users.id = api_tokens.user_id')
                    ->join('homes', 'homes.id = api_tokens.home_id', 'left')
                    ->where('api_tokens.token', $token)
                    ->first();
        return $row ?: null;
    }

    public function updateHome(string $token, int $homeId): void
    {
        $this->where('token', $token)->set('home_id', $homeId)->update();
    }
}
