<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceProviderModel extends Model
{
    protected $table         = 'service_providers';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['name', 'category', 'phone', 'email', 'website', 'city', 'active'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
