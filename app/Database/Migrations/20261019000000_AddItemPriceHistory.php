<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_AddItemPriceHistory extends Migration
{
    public function up(): void
    {
        helper('migration');
        execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.5.0_item_price_history.sql');
    }

    public function down(): void
    {
        $db = \Config\Database::connect();
        $db->simpleQuery('DROP TRIGGER IF EXISTS `ospos_items_price_history_au`;');
        $this->forge->dropTable('ospos_items_price_history', true);
    }
}
