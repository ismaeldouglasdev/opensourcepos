<?php

namespace App\Controllers;

use App\Models\Customer;

/**
 * Fiado V1 — lista de devedores (clientes com vendas fiado pendentes)
 * Fiado V2 — quitação: registra o pagamento de dívidas sem criar venda fictícia
 */
class Debtors extends Secure_Controller
{
    /** Métodos reais aceitos na quitação (mesmos tipos gravados em sales_payments) */
    private const ALLOWED_PAYMENT_METHODS = ['Dinheiro', 'Cartão Débito', 'Cartão Crédito', 'PIX'];

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
        $builder->join('people', 'people.person_id = sales.customer_id');
        $builder->where('sales_payments.payment_type', credit_payment_type_name());
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

    /**
     * GET debtors/debts/(:num)
     * Dívidas fiado em aberto de um cliente, por venda (mais antiga primeiro).
     *
     * @return void
     */
    public function getDebts(int $customer_id): void
    {
        $this->response->setContentType('application/json');

        $customer_info = model(Customer::class)->get_info($customer_id);
        $debts = model(\App\Models\Sale::class)->get_fiado_open_sales($customer_id);

        $total = '0';
        $scale = totals_decimals();
        foreach ($debts as $debt) {
            $total = \bcadd($total, (string) $debt['fiado_outstanding'], $scale);
        }

        echo json_encode([
            'customer_id'   => $customer_id,
            'customer_name' => $customer_info->first_name . ' ' . $customer_info->last_name,
            'debts'         => $debts,
            'total_devido'  => $total
        ]);
    }

    /**
     * POST debtors/settle
     * Registra o pagamento (parcial ou total) do fiado de UMA venda.
     * POST: sale_id, payment_type (método real), amount.
     *
     * @return void
     */
    public function postSettle(): void
    {
        $this->response->setContentType('application/json');

        $sale_id = (int) $this->request->getPost('sale_id');
        $payment_type = trim((string) $this->request->getPost('payment_type'));
        $amount = trim((string) $this->request->getPost('amount'));
        $scale = totals_decimals();

        if ($sale_id <= 0 || !in_array($payment_type, self::ALLOWED_PAYMENT_METHODS, true) || !is_numeric($amount)) {
            log_message('error', "debtors/settle: dados inválidos. sale_id=$sale_id payment_type='$payment_type' amount='$amount'");
            echo json_encode(['success' => false, 'message' => 'Dados inválidos para quitação.']);
            return;
        }

        $sale = db_connect()->table('sales')
            ->select('sale_id, customer_id')
            ->where('sale_id', $sale_id)
            ->where('sale_status', COMPLETED)
            ->get()->getRow();

        if ($sale === null) {
            log_message('error', "debtors/settle: venda $sale_id não encontrada ou não concluída");
            echo json_encode(['success' => false, 'message' => 'Venda não encontrada ou não concluída.']);
            return;
        }

        try {
            $remaining = model(\App\Models\Sale::class)->settle_fiado_payment(
                $sale_id,
                $payment_type,
                \bcadd('0', $amount, $scale)
            );
        } catch (\RuntimeException | \InvalidArgumentException $e) {
            log_message('error', "debtors/settle: falha na quitação da venda $sale_id: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            return;
        }

        log_message('info', "debtors/settle: fiado quitado. sale_id=$sale_id método=$payment_type valor=$amount restante=$remaining");

        echo json_encode([
            'success'          => true,
            'message'          => 'Pagamento registrado: ' . $payment_type . '.',
            'paid'             => \bcadd('0', $amount, $scale),
            'remaining'        => $remaining,
            'customer_id'      => (int) $sale->customer_id
        ]);
    }

    /**
     * GET debtors/history
     * Últimas quitações registradas (linhas de pagamento com reference_code = FIADO).
     *
     * @return void
     */
    public function getHistory(): void
    {
        $this->response->setContentType('application/json');

        $p = db_connect()->getPrefix();

        $builder = db_connect()->table('sales_payments AS payments');
        $builder->select('payments.payment_time, payments.payment_type, payments.payment_amount, payments.sale_id');
        $builder->select("CONCAT({$p}people.first_name, ' ', IFNULL({$p}people.last_name, '')) AS customer_name");
        $builder->join('sales', 'sales.sale_id = payments.sale_id');
        $builder->join('customers', 'customers.person_id = sales.customer_id', 'left');
        $builder->join('people', 'people.person_id = sales.customer_id', 'left');
        $builder->where('payments.reference_code', 'FIADO');
        $builder->orderBy('payments.payment_time', 'DESC');
        $builder->limit(50);

        echo json_encode(['history' => $builder->get()->getResultArray()]);
    }
}
