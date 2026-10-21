<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_CreateItemCodes extends Migration
{
    public function up(): void
    {
        helper('migration');
        execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.5.1_item_codes.sql');
    }

    public function down(): void
    {
        $this->forge->dropTable('ospos_item_codes', true);
    }
}
