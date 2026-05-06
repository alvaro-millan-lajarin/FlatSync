<?php

namespace App\Models;

use CodeIgniter\Model;

class ChoreModel extends Model
{
    protected $table         = 'chores';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'home_id', 'task_name', 'icon', 'assigned_user_id',
        'due_date', 'status', 'recurrence', 'recurrence_parent_id', 'penalty_amount', 'completed_at',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
