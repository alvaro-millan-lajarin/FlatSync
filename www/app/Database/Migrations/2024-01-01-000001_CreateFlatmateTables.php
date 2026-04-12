<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFlatmateTables extends Migration
{
    public function up(): void
    {
        // ── homes ──────────────────────────────────────────────────────────
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'            => ['type' => 'VARCHAR', 'constraint' => 100],
            'invite_code'     => ['type' => 'VARCHAR', 'constraint' => 10, 'unique' => true],
            'default_penalty' => ['type' => 'DECIMAL', 'constraint' => '8,2', 'default' => '2.00'],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('homes');

        // ── users ──────────────────────────────────────────────────────────
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'home_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'username'   => ['type' => 'VARCHAR', 'constraint' => 50],
            'email'      => ['type' => 'VARCHAR', 'constraint' => 150, 'unique' => true],
            'password'   => ['type' => 'VARCHAR', 'constraint' => 255],
            'is_admin'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('home_id', 'homes', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('users');

        // ── chores ─────────────────────────────────────────────────────────
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'home_id'          => ['type' => 'INT', 'unsigned' => true],
            'assigned_user_id' => ['type' => 'INT', 'unsigned' => true],
            'task_name'        => ['type' => 'VARCHAR', 'constraint' => 150],
            'icon'             => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => '🏠'],
            'due_date'         => ['type' => 'DATE'],
            'status'           => ['type' => 'ENUM', 'constraint' => ['pending', 'done', 'missed'], 'default' => 'pending'],
            'recurrence'       => ['type' => 'ENUM', 'constraint' => ['none', 'weekly', 'biweekly', 'monthly'], 'default' => 'none'],
            'penalty_amount'   => ['type' => 'DECIMAL', 'constraint' => '8,2', 'default' => '2.00'],
            'completed_at'     => ['type' => 'DATETIME', 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('home_id', 'homes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('assigned_user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('chores');

        // ── expenses ───────────────────────────────────────────────────────
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'home_id'       => ['type' => 'INT', 'unsigned' => true],
            'paid_by'       => ['type' => 'INT', 'unsigned' => true],
            'title'         => ['type' => 'VARCHAR', 'constraint' => 150],
            'description'   => ['type' => 'TEXT', 'null' => true],
            'amount'        => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'category'      => ['type' => 'ENUM', 'constraint' => ['food', 'cleaning', 'bills', 'other'], 'default' => 'other'],
            'date'          => ['type' => 'DATE'],
            'receipt_image' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('home_id', 'homes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('paid_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('expenses');

        // ── swap_requests ──────────────────────────────────────────────────
        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'chore_id'           => ['type' => 'INT', 'unsigned' => true],
            'requester_user_id'  => ['type' => 'INT', 'unsigned' => true],
            'target_user_id'     => ['type' => 'INT', 'unsigned' => true],
            'compensation'       => ['type' => 'DECIMAL', 'constraint' => '8,2', 'default' => '0.00'],
            'message'            => ['type' => 'TEXT', 'null' => true],
            'status'             => ['type' => 'ENUM', 'constraint' => ['pending', 'accepted', 'declined'], 'default' => 'pending'],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('chore_id', 'chores', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('requester_user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('target_user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('swap_requests');

        // ── settlements ────────────────────────────────────────────────────
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'home_id'      => ['type' => 'INT', 'unsigned' => true],
            'from_user_id' => ['type' => 'INT', 'unsigned' => true],
            'to_user_id'   => ['type' => 'INT', 'unsigned' => true],
            'amount'       => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'settled_at'   => ['type' => 'DATETIME'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('home_id', 'homes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('from_user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('to_user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('settlements');
    }

    public function down(): void
    {
        $this->forge->dropTable('settlements', true);
        $this->forge->dropTable('swap_requests', true);
        $this->forge->dropTable('expenses', true);
        $this->forge->dropTable('chores', true);
        $this->forge->dropTable('users', true);
        $this->forge->dropTable('homes', true);
    }
}
