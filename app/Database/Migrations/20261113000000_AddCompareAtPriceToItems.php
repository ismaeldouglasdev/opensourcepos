<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_AddCompareAtPriceToItems extends Migration
{
    public function up(): void
    {
        helper('migration');
        execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.5.2_compare_at_price.sql');
    }

    public function down(): void
    {
        $this->forge->dropColumn('ospos_items', 'compare_at_price');
    }
}