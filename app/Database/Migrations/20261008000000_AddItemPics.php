<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_AddItemPics extends Migration
{
    public function up(): void
    {
        helper('migration');
        execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.5.0_item_pics.sql');
    }

    public function down(): void
    {
        $this->forge->dropTable('ospos_item_pics', true);
    }
}