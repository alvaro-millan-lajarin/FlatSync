<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGameScores extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'unsigned' => true],
            'home_id'    => ['type' => 'INT', 'unsigned' => true],
            'score'      => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['user_id', 'home_id']);
        $this->forge->createTable('game_scores');
    }

    public function down(): void
    {
        $this->forge->dropTable('game_scores', true);
    }
}
