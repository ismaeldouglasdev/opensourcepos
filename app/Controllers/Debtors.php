<?php

namespace App\Controllers;

/**
 * Fiado V1 — lista de devedores (clientes com vendas fiado pendentes)
 */
class Debtors extends Secure_Controller
{
    public function __construct()
    {
        parent::__construct('home');
    }

    /**
     * @return void
     */
    public function getIndex(): void
    {
        $p = db_connect()->getPrefix();
        $decimals = totals_decimals();

        $builder = db_connect()->table('sales_payments');
        $builder->select("CONCAT({$p}people.first_name, ' ', IFNULL({$p}people.last_name, '')) AS customer_name")
            ->select("{$p}people.person_id AS customer_id")
            ->select("COUNT(DISTINCT {$p}sales.sale_id) AS sale_count")
            ->select("ROUND(SUM({$p}sales_payments.payment_amount), $decimals) AS total_devido");
        $builder->join('sales', 'sales.sale_id = sales_payments.sale_id');
        $builder->join('customers', 'customers.person_id = sales.customer_id');
        $builder->join('people', 'people.person_id = customers.person_id');
        $builder->where('sales_payments.payment_type', lang('Sales.account_receivable'));
        $builder->where('sales.sale_status', COMPLETED);
        $builder->groupBy('people.person_id', 'people.first_name', 'people.last_name');
        $builder->orderBy('total_devido', 'DESC');

        $data['debtors'] = $builder->get()->getResult();

        $data['total_devido_geral'] = 0;
        foreach ($data['debtors'] as $debtor) {
            $data['total_devido_geral'] += (float)$debtor->total_devido;
        }

        echo view('debtors/manage', $data);
    }
}
