<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_AddSimpleModeToEmployees extends Migration
{
    public function up(): void
    {
        helper('migration');
        execute_script(APPPATH . 'Database/Migrations/sqlscripts/simple_mode_employee.sql');
    }

    public function down(): void
    {
        $this->forge->dropColumn('ospos_employees', 'simple_mode');
    }
}
