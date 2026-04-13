<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGoogleAuthToUsers extends Migration
{
    public function up(): void
    {
        // Add google_id column
        $this->forge->addColumn('users', [
            'google_id' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'default' => null],
        ]);

        // Make password nullable (Google users have no password)
        $this->db->query('ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NULL DEFAULT NULL');
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', 'google_id');
        $this->db->query('ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NOT NULL');
    }
}
