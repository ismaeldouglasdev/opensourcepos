<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_AddShowGuideToEmployees extends Migration
{
    public function up(): void
    {
        helper('migration');
        execute_script(APPPATH . 'Database/Migrations/sqlscripts/show_guide_employee.sql');
    }

    public function down(): void
    {
        $this->forge->dropColumn('ospos_employees', 'show_guide');
    }
}
