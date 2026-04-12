<?php

namespace App\Models;

use CodeIgniter\Model;

class ExpenseModel extends Model
{
    protected $table         = 'expenses';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'home_id', 'title', 'description', 'amount',
        'category', 'paid_by', 'date', 'receipt_image',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
