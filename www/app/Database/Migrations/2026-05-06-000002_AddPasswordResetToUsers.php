<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPasswordResetToUsers extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'reset_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'default'    => null,
                'after'      => 'verify_token',
            ],
            'reset_expires' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
                'after'   => 'reset_token',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', ['reset_token', 'reset_expires']);
    }
}
