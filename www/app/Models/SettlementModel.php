<?php

namespace App\Models;

use CodeIgniter\Model;

class SettlementModel extends Model
{
    protected $table         = 'settlements';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['home_id', 'from_user_id', 'to_user_id', 'amount', 'settled_at'];
    protected $useTimestamps = false;
}
