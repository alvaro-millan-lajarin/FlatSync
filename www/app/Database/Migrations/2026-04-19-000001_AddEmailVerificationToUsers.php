<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmailVerificationToUsers extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'email_verified' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'after'      => 'avatar_url',
            ],
            'verify_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'default'    => null,
                'after'      => 'email_verified',
            ],
        ]);

        // New registrations will explicitly set email_verified = 0.
        // Existing users keep default = 1 (already active).
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', ['email_verified', 'verify_token']);
    }
}
