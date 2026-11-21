<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_AddSalePaymentInstallments extends Migration
{
    public function up(): void
    {
        helper('migration');
        execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.5.4_sale_payment_installments.sql');
    }

    public function down(): void
    {
        $this->forge->dropColumn('ospos_sales_payments', 'installments');
    }
}
