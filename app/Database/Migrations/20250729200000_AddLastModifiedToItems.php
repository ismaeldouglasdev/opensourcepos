<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_AddLastModifiedToItems extends Migration
{
    public function up(): void
    {
        helper('migration');
        execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.4.2_last_modified_item.sql');
    }

    public function down(): void
    {
        $this->forge->dropColumn('ospos_items', 'last_modified');
    }
}
