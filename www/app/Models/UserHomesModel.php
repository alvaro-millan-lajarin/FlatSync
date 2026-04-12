<?php

namespace App\Models;

use CodeIgniter\Model;

class UserHomesModel extends Model
{
    protected $table         = 'user_homes';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['user_id', 'home_id', 'is_admin'];
    protected $useTimestamps = false;

    /** Returns all homes a user belongs to, with home details */
    public function getHomesForUser(int $userId): array
    {
        return $this->db->table('user_homes uh')
            ->select('homes.id, homes.name, homes.invite_code, homes.default_penalty, uh.is_admin')
            ->join('homes', 'homes.id = uh.home_id')
            ->where('uh.user_id', $userId)
            ->orderBy('uh.joined_at', 'ASC')
            ->get()->getResultArray();
    }

    /** Returns all members of a home, with user details */
    public function getMembersOfHome(int $homeId): array
    {
        return $this->db->table('user_homes uh')
            ->select('users.id, users.username, users.email, users.avatar_url, users.created_at, uh.is_admin')
            ->join('users', 'users.id = uh.user_id')
            ->where('uh.home_id', $homeId)
            ->get()->getResultArray();
    }

    public function isMember(int $userId, int $homeId): bool
    {
        return $this->where('user_id', $userId)->where('home_id', $homeId)->countAllResults() > 0;
    }

    public function isAdmin(int $userId, int $homeId): bool
    {
        $row = $this->where('user_id', $userId)->where('home_id', $homeId)->first();
        return $row && (bool) $row['is_admin'];
    }
}