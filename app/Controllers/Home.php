<?php

namespace App\Controllers;

use App\Models\Item_quantity;
use CodeIgniter\HTTP\RedirectResponse;
use Config\OSPOS;

class Home extends Secure_Controller
{
    private $db;
    private $item;

    public function __construct()
    {
        parent::__construct('home', null, 'home');
        $this->db = db_connect();
        $this->item = model(\App\Models\Item::class);
    }

    /**
     * Dashboard: fetch sales KPIs, top items, stock alerts, pending receivables
     */
    public function getIndex(): void
    {
        $decimals = totals_decimals();
        $data = [];
        $p = $this->db->getPrefix();

        // --- Sales price expression (with prefix) ---
        $sale_price = "CASE WHEN {$p}sales_items.discount_type = " . PERCENT
            . " THEN {$p}sales_items.quantity_purchased * {$p}sales_items.item_unit_price - ROUND({$p}sales_items.quantity_purchased * {$p}sales_items.item_unit_price * {$p}sales_items.discount / 100, $decimals)"
            . " ELSE {$p}sales_items.quantity_purchased * ({$p}sales_items.item_unit_price - {$p}sales_items.discount) END";

        // Amount actually received per sale — mirrors the sales/manage payments summary
        // (SUM(payment_amount - cash_refund) over positive payments). This keeps the
        // dashboard totals consistent with sales/manage even when a sale is underpaid.
        $pay_amount = "IFNULL({$p}sales_payments.payment_amount, 0) - IFNULL({$p}sales_payments.cash_refund, 0)";

        // 1. Today's sales total
        $builder = $this->db->table('sales_payments');
        $builder->select("ROUND(SUM($pay_amount), $decimals) AS total");
        $builder->join('sales', 'sales.sale_id = sales_payments.sale_id');
        $builder->where('sales.sale_status', COMPLETED);
        $builder->where('sales_payments.payment_amount !=', 0);
        $builder->where("DATE({$p}sales.sale_time)", date('Y-m-d'));
        $row = $builder->get()->getRow();
        $data['today_sales'] = (float)($row->total ?? 0);

        // 1b. Returns/refunds registered today. The totals above already net
        // them out (payment_amount < 0 subtracts), but the operator needs to
        // see HOW MUCH came back — a day of R$ 0 in sales may mean "nothing
        // sold" or "everything was returned", and those are different days.
        $builder = $this->db->table('sales_payments');
        $builder->select("ROUND(SUM($pay_amount), $decimals) AS total");
        $builder->join('sales', 'sales.sale_id = sales_payments.sale_id');
        $builder->where('sales.sale_status', COMPLETED);
        $builder->where('sales_payments.payment_amount <', 0);
        $builder->where("DATE({$p}sales.sale_time)", date('Y-m-d'));
        $row = $builder->get()->getRow();
        $data['today_returns'] = abs((float)($row->total ?? 0));

        // 1c. Number of sales (transactions) today — count of COMPLETED sales.
        // The "Vendas Hoje" card shows the R$ total; the operator also wants
        // to know HOW MANY sales made up that total.
        $builder = $this->db->table('sales');
        $builder->select("COUNT({$p}sales.sale_id) AS count");
        $builder->where('sales.sale_status', COMPLETED);
        $builder->where("DATE({$p}sales.sale_time)", date('Y-m-d'));
        $row = $builder->get()->getRow();
        $data['sales_count_today'] = (int)($row->count ?? 0);

        // 2. Yesterday's sales total
        $builder = $this->db->table('sales_payments');
        $builder->select("ROUND(SUM($pay_amount), $decimals) AS total");
        $builder->join('sales', 'sales.sale_id = sales_payments.sale_id');
        $builder->where('sales.sale_status', COMPLETED);
        $builder->where('sales_payments.payment_amount !=', 0);
        $builder->where("DATE({$p}sales.sale_time)", date('Y-m-d', strtotime('-1 day')));
        $row = $builder->get()->getRow();
        $data['yesterday_sales'] = (float)($row->total ?? 0);

        // 3. This week's sales total
        $builder = $this->db->table('sales_payments');
        $builder->select("ROUND(SUM($pay_amount), $decimals) AS total");
        $builder->join('sales', 'sales.sale_id = sales_payments.sale_id');
        $builder->where('sales.sale_status', COMPLETED);
        $builder->where('sales_payments.payment_amount !=', 0);
        $builder->where("YEARWEEK({$p}sales.sale_time, 1)", 'YEARWEEK(CURDATE(), 1)', false);
        $row = $builder->get()->getRow();
        $data['week_sales'] = (float)($row->total ?? 0);

        // 4. This month's sales total
        $builder = $this->db->table('sales_payments');
        $builder->select("ROUND(SUM($pay_amount), $decimals) AS total");
        $builder->join('sales', 'sales.sale_id = sales_payments.sale_id');
        $builder->where('sales.sale_status', COMPLETED);
        $builder->where('sales_payments.payment_amount !=', 0);
        $builder->where("MONTH({$p}sales.sale_time)", date('m'));
        $builder->where("YEAR({$p}sales.sale_time)", date('Y'));
        $row = $builder->get()->getRow();
        $data['month_sales'] = (float)($row->total ?? 0);

        // 5. Items sold today (distinct items count)
        $builder = $this->db->table('sales_items');
        $builder->select("COUNT(DISTINCT {$p}sales_items.item_id) AS count");
        $builder->join('sales', 'sales.sale_id = sales_items.sale_id');
        $builder->where('sales.sale_status', COMPLETED);
        $builder->where("DATE({$p}sales.sale_time)", date('Y-m-d'));
        $row = $builder->get()->getRow();
        $data['items_sold_today'] = (int)($row->count ?? 0);

        // 5b. Lucro estimado do dia: receita por item (mesma expressão do top
        // items, que respeita discount_type) menos o custo SNAPSHOT gravado na
        // venda (sales_items.item_cost_price). Base por item, não por
        // pagamento: devoluções (pagamentos negativos) NÃO são descontadas
        // aqui — por isso o card se rotula "estimado".
        $builder = $this->db->table('sales_items');
        $builder->select("ROUND(SUM($sale_price) - SUM({$p}sales_items.quantity_purchased * {$p}sales_items.item_cost_price), $decimals) AS profit");
        $builder->join('sales', 'sales.sale_id = sales_items.sale_id');
        $builder->where('sales.sale_status', COMPLETED);
        $builder->where("DATE({$p}sales.sale_time)", date('Y-m-d'));
        $row = $builder->get()->getRow();
        $data['today_profit'] = (float)($row->profit ?? 0);

        // 6. Top 5 items this week
        $builder = $this->db->table('sales_items');
        $builder->select("{$p}items.item_id, {$p}items.name, SUM({$p}sales_items.quantity_purchased) AS qty, ROUND(SUM($sale_price), $decimals) AS revenue");
        $builder->join('sales', 'sales.sale_id = sales_items.sale_id');
        $builder->join('items', 'items.item_id = sales_items.item_id');
        $builder->where('sales.sale_status', COMPLETED);
        $builder->where("YEARWEEK({$p}sales.sale_time, 1)", 'YEARWEEK(CURDATE(), 1)', false);
        $builder->groupBy('sales_items.item_id');
        $builder->orderBy('qty', 'DESC');
        $builder->limit(5);
        $data['top_items'] = $builder->get()->getResult();

        // 7. Stock alerts: items with ZERADO or IRREGULAR status
        $builder = $this->db->table('items');
        $builder->select("{$p}items.item_id, {$p}items.name, {$p}items.reorder_level, {$p}item_quantities.quantity, {$p}item_quantities.stock_status, {$p}item_quantities.location_id");
        $builder->join('item_quantities', 'item_quantities.item_id = items.item_id');
        $builder->where('items.stock_type', HAS_STOCK);
        $builder->where('items.deleted', 0);
        $builder->whereIn('item_quantities.stock_status', [Item_quantity::STOCK_ZERADO, Item_quantity::STOCK_IRREGULAR]);
        $builder->orderBy('item_quantities.stock_status', 'DESC');
        $builder->limit(10);
        $data['stock_alerts'] = $builder->get()->getResult();

        // 8. Pending receivables (fiado / conta a receber)
        $builder = $this->db->table('sales_payments');
        $builder->select("IFNULL(SUM({$p}sales_payments.payment_amount), 0) AS total");
        $builder->where('payment_type', credit_payment_type_name());
        $row = $builder->get()->getRow();
        $data['pending_receivables'] = (float)($row->total ?? 0);

        // Stock alert count for badge in header
        $item_quantity = model(Item_quantity::class);
        $data['stock_alert_count'] = $item_quantity->get_stock_alert_count();

        // 8b. Inventory value at cost — how much money is sitting on the shelves.
        $builder = $this->db->table('item_quantities');
        $builder->select("ROUND(SUM({$p}item_quantities.quantity * {$p}items.cost_price), 2) AS total");
        $builder->join('items', 'items.item_id = item_quantities.item_id');
        $builder->where('items.deleted', 0);
        $builder->where('items.stock_type', HAS_STOCK);
        $row = $builder->get()->getRow();
        $data['stock_value'] = (float)($row->total ?? 0);

        // 8c. Items priced below cost — every sale of these is a loss until repriced.
        // Same conditions as the below_cost filter in Item::search().
        $builder = $this->db->table('items');
        $builder->select("COUNT({$p}items.item_id) AS count");
        $builder->where('items.deleted', 0);
        $builder->whereIn('items.item_type', [ITEM, ITEM_KIT, ITEM_AMOUNT_ENTRY]);
        $builder->where('items.cost_price >', 0);
        $builder->where('items.unit_price >', 0);
        $builder->where("{$p}items.unit_price < {$p}items.cost_price", null, false);
        $row = $builder->get()->getRow();
        $data['below_cost_count'] = (int)($row->count ?? 0);

        // 8d. No turnover: stock on the shelf and no COMPLETED sale in the last
        // 90 days. Same conditions as the no_movement filter in Item::search().
        $builder = $this->db->table('items');
        $builder->select("COUNT({$p}items.item_id) AS count");
        $builder->where('items.deleted', 0);
        $builder->whereIn('items.item_type', [ITEM, ITEM_KIT, ITEM_AMOUNT_ENTRY]);
        $builder->where('EXISTS (SELECT 1 FROM ' . $this->db->prefixTable('item_quantities') . ' iq2'
            . " WHERE iq2.item_id = {$p}items.item_id AND iq2.quantity > 0)", null, false);
        $builder->where('NOT EXISTS (SELECT 1 FROM ' . $this->db->prefixTable('sales_items') . ' si'
            . ' JOIN ' . $this->db->prefixTable('sales') . ' s ON s.sale_id = si.sale_id'
            . " WHERE si.item_id = {$p}items.item_id AND s.sale_status = " . COMPLETED
            . ' AND s.sale_time >= DATE_SUB(NOW(), INTERVAL 90 DAY))', null, false);
        $row = $builder->get()->getRow();
        $data['no_movement_count'] = (int)($row->count ?? 0);

        // 9. Daily sales target (meta diária)
        $config = config(OSPOS::class)->settings;
        $data['daily_sales_target'] = (float)($config['daily_sales_target'] ?? 0);
        $data['daily_sales_target_pct'] = $data['daily_sales_target'] > 0
            ? min(100, round($data['today_sales'] / $data['daily_sales_target'] * 100))
            : 0;

        // 10. Sales per hour today (gráfico por hora)
        $builder = $this->db->table('sales_payments');
        $builder->select("HOUR({$p}sales.sale_time) AS hour, ROUND(SUM($pay_amount), $decimals) AS total");
        $builder->join('sales', 'sales.sale_id = sales_payments.sale_id');
        $builder->where('sales.sale_status', COMPLETED);
        $builder->where('sales_payments.payment_amount !=', 0);
        $builder->where("DATE({$p}sales.sale_time)", date('Y-m-d'));
        $builder->groupBy("HOUR({$p}sales.sale_time)");
        $builder->orderBy('hour', 'ASC');
        $hourly_result = $builder->get()->getResult();

        $hourly = array_fill(0, 24, 0.0);
        foreach ($hourly_result as $h) {
            $hourly[(int)$h->hour] = (float)$h->total;
        }
        $data['hourly_sales'] = $hourly;
        $data['hourly_max'] = max(array_values($hourly)) ?: 0;

        // 8. Vendas que chegaram de canal externo (Mercado Livre / Shopee).
        // Entram sozinhas, sem o operador tocar no caixa — por isso precisam
        // ficar visíveis aqui. O canal é identificado pelo reference_code que o
        // inventory-service grava em sales_payments ao escrever a venda
        // (client_sale_id "ml-<pedido>" / "shopee-<pedido>").
        $builder = $this->db->table('sales');
        $builder->select("{$p}sales.sale_id, {$p}sales.sale_time, {$p}sales_payments.reference_code, {$p}sales_payments.payment_amount");
        $builder->join('sales_payments', 'sales_payments.sale_id = sales.sale_id');
        $builder->where('sales.sale_status', COMPLETED);
        $builder->groupStart();
        $builder->like('sales_payments.reference_code', 'ml-', false);
        $builder->orLike('sales_payments.reference_code', 'shopee-', false);
        $builder->groupEnd();
        $builder->orderBy('sales.sale_id', 'DESC');
        $builder->limit(8);
        $data['channel_sales'] = $builder->get()->getResult();
        $data['channel_sales_total'] = count($data['channel_sales']);

        $data['items_created_today'] = $this->item->get_created_today_count();
        $data['items_updated_today'] = $this->item->get_updated_today_count();

        echo view('home/home', $data);
    }

    /**
     * Saves the daily sales target (meta diária). Used in app/Views/home/home.php
     *
     * @return void
     * @noinspection PhpUnused
     */
    public function postSaveDailyTarget(): void
    {
        $target = parse_decimals($this->request->getPost('daily_sales_target'));

        if ($target === false || $target < 0) {
            $target = 0;
        }

        $appconfig = model(\App\Models\Appconfig::class);
        if ($appconfig->save(['daily_sales_target' => (string)$target])) {
            $this->response->setContentType('application/json');
            echo json_encode(['success' => true, 'message' => 'Meta diária atualizada.']);
        } else {
            $this->response->setContentType('application/json');
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar a meta diária.']);
        }
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
