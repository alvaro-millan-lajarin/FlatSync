<?php

namespace App\Models;

use CodeIgniter\Model;

class SwapModel extends Model
{
    protected $table         = 'swap_requests';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'chore_id', 'requester_user_id', 'target_user_id',
        'compensation', 'message', 'status',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
