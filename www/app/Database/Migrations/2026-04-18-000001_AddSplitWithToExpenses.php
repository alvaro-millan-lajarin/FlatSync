<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSplitWithToExpenses extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('expenses', [
            'split_with' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
                'after'   => 'receipt_image',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('expenses', 'split_with');
    }
}
