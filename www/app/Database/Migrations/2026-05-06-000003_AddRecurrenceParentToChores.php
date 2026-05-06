<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRecurrenceParentToChores extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('chores', [
            'recurrence_parent_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'recurrence',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('chores', 'recurrence_parent_id');
    }
}
