<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLeadsSystem extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 255],
            'category'   => ['type' => 'VARCHAR', 'constraint' => 50],
            'phone'      => ['type' => 'VARCHAR', 'constraint' => 50],
            'email'      => ['type' => 'VARCHAR', 'constraint' => 255],
            'website'    => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'city'       => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'active'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('service_providers');

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'provider_id' => ['type' => 'INT', 'unsigned' => true],
            'user_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'ip'          => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('provider_id', 'service_providers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('leads');
    }

    public function down(): void
    {
        $this->forge->dropTable('leads', true);
        $this->forge->dropTable('service_providers', true);
    }
}
