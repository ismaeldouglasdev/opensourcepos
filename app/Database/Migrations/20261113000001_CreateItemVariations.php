<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_CreateItemVariations extends Migration
{
    public function up(): void
    {
        helper('migration');
        execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.5.3_item_variations.sql');
    }

    public function down(): void
    {
        $this->forge->dropTable('ospos_item_variations', true);
    }
}