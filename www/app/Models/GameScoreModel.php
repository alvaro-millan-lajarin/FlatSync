<?php

namespace App\Models;

use CodeIgniter\Model;

class GameScoreModel extends Model
{
    protected $table         = 'game_scores';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['user_id', 'home_id', 'score', 'updated_at'];
    protected $useTimestamps = false;

    public function getRanking(int $homeId): array
    {
        return $this->select('game_scores.score, game_scores.user_id, users.username, users.avatar_url')
                    ->join('users', 'users.id = game_scores.user_id')
                    ->where('game_scores.home_id', $homeId)
                    ->orderBy('game_scores.score', 'DESC')
                    ->findAll();
    }

    /** Save score only if it beats the existing record. Returns true if updated. */
    public function saveIfBetter(int $userId, int $homeId, int $score): bool
    {
        $existing = $this->where('user_id', $userId)->where('home_id', $homeId)->first();
        if (!$existing) {
            $this->insert(['user_id' => $userId, 'home_id' => $homeId, 'score' => $score, 'updated_at' => date('Y-m-d H:i:s')]);
            return true;
        }
        if ($score > (int) $existing['score']) {
            $this->where('user_id', $userId)->where('home_id', $homeId)
                 ->set(['score' => $score, 'updated_at' => date('Y-m-d H:i:s')])->update();
            return true;
        }
        return false;
    }
}
