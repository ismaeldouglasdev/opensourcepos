<?php

namespace App\Controllers;

use App\Models\Item_quantity;
use CodeIgniter\HTTP\RedirectResponse;

class Home extends Secure_Controller
{
    public function __construct()
    {
        parent::__construct('home', null, 'home');
    }

    /**
     * Dashboard: fetch sales KPIs, top items, stock alerts, pending receivables
     */
    public function getIndex(): void
    {
        $decimals = totals_decimals();
        $data = [];

        // --- Sales price expression (with discount) ---
        $sale_price = "CASE WHEN sales_items.discount_type = " . PERCENT
            . " THEN sales_items.quantity_purchased * sales_items.item_unit_price - ROUND(sales_items.quantity_purchased * sales_items.item_unit_price * sales_items.discount / 100, $decimals)"
            . " ELSE sales_items.quantity_purchased * (sales_items.item_unit_price - sales_items.discount) END";

        // 1. Today's sales total
        $builder = $this->db->table('sales_items');
        $builder->select("ROUND(SUM($sale_price), $decimals) AS total");
        $builder->join('sales', 'sales.sale_id = sales_items.sale_id');
        $builder->where('sales.sale_status', COMPLETED);
        $builder->where('DATE(sales.sale_time)', date('Y-m-d'));
        $row = $builder->get()->getRow();
        $data['today_sales'] = (float)($row->total ?? 0);

        // 2. Yesterday's sales total
        $builder = $this->db->table('sales_items');
        $builder->select("ROUND(SUM($sale_price), $decimals) AS total");
        $builder->join('sales', 'sales.sale_id = sales_items.sale_id');
        $builder->where('sales.sale_status', COMPLETED);
        $builder->where('DATE(sales.sale_time)', date('Y-m-d', strtotime('-1 day')));
        $row = $builder->get()->getRow();
        $data['yesterday_sales'] = (float)($row->total ?? 0);

        // 3. This week's sales total (YEARWEEK column-to-column: raw where, no escape)
        $builder = $this->db->table('sales_items');
        $builder->select("ROUND(SUM($sale_price), $decimals) AS total");
        $builder->join('sales', 'sales.sale_id = sales_items.sale_id');
        $builder->where('sales.sale_status', COMPLETED);
        $builder->where('YEARWEEK(sales.sale_time, 1)', 'YEARWEEK(CURDATE(), 1)', false);
        $row = $builder->get()->getRow();
        $data['week_sales'] = (float)($row->total ?? 0);

        // 4. This month's sales total
        $builder = $this->db->table('sales_items');
        $builder->select("ROUND(SUM($sale_price), $decimals) AS total");
        $builder->join('sales', 'sales.sale_id = sales_items.sale_id');
        $builder->where('sales.sale_status', COMPLETED);
        $builder->where('MONTH(sales.sale_time)', date('m'));
        $builder->where('YEAR(sales.sale_time)', date('Y'));
        $row = $builder->get()->getRow();
        $data['month_sales'] = (float)($row->total ?? 0);

        // 5. Items sold today (distinct items count)
        $builder = $this->db->table('sales_items');
        $builder->selectCount('DISTINCT sales_items.item_id', 'count');
        $builder->join('sales', 'sales.sale_id = sales_items.sale_id');
        $builder->where('sales.sale_status', COMPLETED);
        $builder->where('DATE(sales.sale_time)', date('Y-m-d'));
        $row = $builder->get()->getRow();
        $data['items_sold_today'] = (int)($row->count ?? 0);

        // 6. Top 5 items this week
        $builder = $this->db->table('sales_items');
        $builder->select('items.name, SUM(sales_items.quantity_purchased) AS qty, ROUND(SUM(' . $sale_price . '), ' . $decimals . ') AS revenue');
        $builder->join('sales', 'sales.sale_id = sales_items.sale_id');
        $builder->join('items', 'items.item_id = sales_items.item_id');
        $builder->where('sales.sale_status', COMPLETED);
        $builder->where('YEARWEEK(sales.sale_time, 1)', 'YEARWEEK(CURDATE(), 1)', false);
        $builder->groupBy('sales_items.item_id');
        $builder->orderBy('qty', 'DESC');
        $builder->limit(5);
        $data['top_items'] = $builder->get()->getResult();

        // 7. Stock alerts: items with ZERADO or IRREGULAR status
        $builder = $this->db->table('items');
        $builder->select('items.name, items.reorder_level, item_quantities.quantity, item_quantities.stock_status, item_quantities.location_id');
        $builder->join('item_quantities', 'item_quantities.item_id = items.item_id');
        $builder->where('items.stock_type', HAS_STOCK);
        $builder->where('items.deleted', 0);
        $builder->where('item_quantities.stock_status IN', [Item_quantity::STOCK_ZERADO, Item_quantity::STOCK_IRREGULAR]);
        $builder->orderBy('item_quantities.stock_status', 'DESC');
        $builder->limit(10);
        $data['stock_alerts'] = $builder->get()->getResult();

        // 8. Pending receivables (fiado / conta a receber)
        $builder = $this->db->table('sales_payments');
        $builder->select('IFNULL(SUM(payment_amount), 0) AS total');
        $builder->where('payment_type', lang('Sales.account_receivable'));
        $row = $builder->get()->getRow();
        $data['pending_receivables'] = (float)($row->total ?? 0);

        // Stock alert count for badge in header
        $item_quantity = model(Item_quantity::class);
        $data['stock_alert_count'] = $item_quantity->get_stock_alert_count();

        echo view('home/home', $data);
    }

    /**
     * Logs the currently logged in employee out of the system.  Used in app/Views/partial/header.php
     *
     * @return RedirectResponse
     * @noinspection PhpUnused
     */
    public function getLogout(): RedirectResponse
    {
        $this->employee->logout();
        return redirect()->to('login');
    }

    /**
     * Load "change employee password" form
     *
     * @noinspection PhpUnused
     */
    public function getChangePassword(int $employee_id = -1): void    // TODO: Replace -1 with a constant
    {
        $person_info = $this->employee->get_info($employee_id);
        foreach (get_object_vars($person_info) as $property => $value) {
            $person_info->$property = $value;
        }
        $data['person_info'] = $person_info;

        echo view('home/form_change_password', $data);
    }

    /**
     * Change employee password
     */
    public function postSave(int $employee_id = -1): void    // TODO: Replace -1 with a constant
    {
        if (!empty($this->request->getPost('current_password')) && $employee_id != -1) {
            if ($this->employee->check_password($this->request->getPost('username', FILTER_SANITIZE_FULL_SPECIAL_CHARS), $this->request->getPost('current_password'))) {
                $employee_data = [
                    'username'     => $this->request->getPost('username', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
                    'password'     => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                    'hash_version' => 2
                ];

                if ($this->employee->change_password($employee_data, $employee_id)) {
                    echo json_encode([
                        'success' => true,
                        'message' => lang('Employees.successful_change_password'),
                        'id'      => $employee_id
                    ]);
                } else { // Failure    // TODO: Replace -1 with constant
                    echo json_encode([
                        'success' => false,
                        'message' => lang('Employees.unsuccessful_change_password'),
                        'id'      => -1
                    ]);
                }
            } else {    // TODO: Replace -1 with constant
                echo json_encode([
                    'success' => false,
                    'message' => lang('Employees.current_password_invalid'),
                    'id'      => -1
                ]);
            }
        } else {    // TODO: Replace -1 with constant
            echo json_encode([
                'success' => false,
                'message' => lang('Employees.current_password_invalid'),
                'id'      => -1
            ]);
        }
    }
}
