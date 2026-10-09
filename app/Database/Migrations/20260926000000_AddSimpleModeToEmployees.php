<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_AddSimpleModeToEmployees extends Migration
{
    /**
     * Perform a migration step.
     *
     * A coluna simple_mode ja existe nos tres ambientes (foi criada manualmente
     * antes desta migration existir). Sem o guard, o ALTER TABLE falha com
     * "Duplicate column name 'simple_mode'" (MySQL 1060) e a migration fica
     * presa como pendente para sempre.
     */
    public function up(): void
    {
        if (! $this->db->tableExists('ospos_employees')) {
            return;
        }

        if ($this->db->fieldExists('simple_mode', 'ospos_employees')) {
            return;
        }

        helper('migration');
        execute_script(APPPATH . 'Database/Migrations/sqlscripts/simple_mode_employee.sql');
    }

    public function down(): void
    {
        $this->forge->dropColumn('ospos_employees', 'simple_mode');
    }
}
