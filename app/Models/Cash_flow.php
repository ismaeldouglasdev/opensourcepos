<?php

namespace App\Models;

use CodeIgniter\Model;
use stdClass;

/**
 * Cash_flow class
 * Registra sangrias e suprimentos de caixa.
 */
class Cash_flow extends Model
{
    protected $table = 'cash_flow';
    protected $primaryKey = 'cash_flow_id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'employee_id',
        'type',
        'amount',
        'note',
        'deleted',
        'created_at'
    ];

    public const TYPE_SANGRIA = 'sangria';
    public const TYPE_SUPRIMENTO = 'suprimento';

    /**
     * Determines if a given cash_flow_id exists
     */
    public function exists(int $cash_flow_id): bool
    {
        $builder = $this->db->table('cash_flow');
        $builder->where('cash_flow_id', $cash_flow_id);

        return ($builder->get()->getNumRows() == 1);
    }

    /**
     * Gets rows
     */
    public function get_found_rows(string $search, array $filters): int
    {
        return $this->search($search, $filters, 0, 0, 'cash_flow_id', 'asc', true);
    }

    /**
     * Searches cash flow entries
     */
    public function search(string $search, array $filters, ?int $rows = 0, ?int $limit_from = 0, ?string $sort = 'cash_flow_id', ?string $order = 'desc', ?bool $count_only = false)
    {
        if ($rows == null) $rows = 0;
        if ($limit_from == null) $limit_from = 0;
        if ($sort == null) $sort = 'cash_flow_id';
        if ($order == null) $order = 'desc';
        if ($count_only == null) $count_only = false;

        $builder = $this->db->table('cash_flow AS cash_flow');

        if ($count_only) {
            $builder->select('COUNT(cash_flow.cash_flow_id) as count');
        } else {
            $builder->select('
                cash_flow.cash_flow_id,
                cash_flow.employee_id,
                cash_flow.type,
                cash_flow.amount,
                cash_flow.note,
                cash_flow.deleted,
                cash_flow.created_at,
                employees.first_name,
                employees.last_name
            ');
        }

        $builder->join('people AS employees', 'employees.person_id = cash_flow.employee_id', 'LEFT');

        $builder->groupStart();
        $builder->like('cash_flow.type', $search);
        $builder->orLike('cash_flow.amount', $search);
        $builder->orLike('cash_flow.note', $search);
        $builder->orLike('CONCAT(employees.first_name, " ", employees.last_name)', $search);
        $builder->groupEnd();

        $builder->where('cash_flow.deleted', $filters['is_deleted'] ?? 0);

        $start_date = $filters['start_date'] ?? null;
        $end_date = $filters['end_date'] ?? null;
        if (!empty($start_date) && !empty($end_date)) {
            $builder->where('DATE_FORMAT(cash_flow.created_at, "%Y-%m-%d") BETWEEN ' . $this->db->escape($start_date) . ' AND ' . $this->db->escape($end_date));
        }

        if ($count_only) {
            return $builder->get()->getRow()->count;
        }

        $builder->orderBy($sort, $order);

        if ($rows > 0) {
            $builder->limit($rows, $limit_from);
        }

        return $builder->get();
    }

    /**
     * Gets information about a particular cash flow entry
     */
    public function get_info(int $cash_flow_id): object
    {
        $builder = $this->db->table('cash_flow AS cash_flow');
        $builder->select('
            cash_flow.cash_flow_id,
            cash_flow.employee_id,
            cash_flow.type,
            cash_flow.amount,
            cash_flow.note,
            cash_flow.deleted,
            cash_flow.created_at,
            employees.first_name,
            employees.last_name
        ');
        $builder->join('people AS employees', 'employees.person_id = cash_flow.employee_id', 'LEFT');
        $builder->where('cash_flow.cash_flow_id', $cash_flow_id);

        $query = $builder->get();
        if ($query->getNumRows() == 1) {
            return $query->getRow();
        }

        return $this->getEmptyObject('cash_flow');
    }

    /**
     * Initializes an empty object based on database definitions
     */
    private function getEmptyObject(string $table_name): object
    {
        $empty_obj = new stdClass();

        foreach ($this->db->getFieldData($table_name) as $field) {
            $field_name = $field->name;

            if (in_array($field->type, ['int', 'tinyint', 'decimal'])) {
                $empty_obj->$field_name = ($field->primary_key == 1) ? NEW_ENTRY : 0;
            } else {
                $empty_obj->$field_name = null;
            }
        }

        return $empty_obj;
    }

    /**
     * Inserts or updates a cash flow entry
     */
    public function save_value(array &$cash_flow_data, int $cash_flow_id = NEW_ENTRY): bool
    {
        if ($cash_flow_id == NEW_ENTRY || !$this->exists($cash_flow_id)) {
            $cash_flow_data['created_at'] = date('Y-m-d H:i:s');

            $builder = $this->db->table('cash_flow');
            if ($builder->insert($cash_flow_data)) {
                $cash_flow_data['cash_flow_id'] = $this->db->insertID();

                return true;
            }

            return false;
        }

        $builder = $this->db->table('cash_flow');
        $builder->where('cash_flow_id', $cash_flow_id);

        return $builder->update($cash_flow_data);
    }

    /**
     * Deletes a list of cash flow entries
     */
    public function delete_list(array $cash_flow_ids): bool
    {
        $this->db->transStart();
        $builder = $this->db->table('cash_flow');
        $builder->whereIn('cash_flow_id', $cash_flow_ids);
        $success = $builder->update(['deleted' => 1]);
        $this->db->transComplete();

        return $success;
    }

    /**
     * Returns today's sangria/suprimento totals
     */
    public function get_today_summary(): object
    {
        $builder = $this->db->table('cash_flow');
        $builder->select("
            IFNULL(SUM(CASE WHEN type = '" . self::TYPE_SANGRIA . "' THEN amount ELSE 0 END), 0) AS sangria,
            IFNULL(SUM(CASE WHEN type = '" . self::TYPE_SUPRIMENTO . "' THEN amount ELSE 0 END), 0) AS suprimento
        ");
        $builder->where('deleted', 0);
        $builder->where('DATE(created_at)', date('Y-m-d'));

        return $builder->get()->getRow();
    }
}
