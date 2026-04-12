<?php

namespace App\Models;

use CodeIgniter\Model;

class HomeModel extends Model
{
    protected $table         = 'homes';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['name', 'invite_code', 'default_penalty'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
