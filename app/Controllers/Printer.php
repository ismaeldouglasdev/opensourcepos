<?php

namespace App\Controllers;

use App\Libraries\Barcode_lib;
use App\Libraries\Sale_lib;
use App\Libraries\Tax_lib;
use App\Libraries\ThermalPrinter;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Sale;
use Config\OSPOS;

/**
 * Printer Controller
 *
 * Handles ESC/POS thermal printer operations:
 * - Test print
 * - Manual reprint of receipts/invoices
 */
class Printer extends Secure_Controller
{
    private ThermalPrinter $thermalPrinter;
    private Sale_lib $sale_lib;
    private Tax_lib $tax_lib;
    private Barcode_lib $barcode_lib;
    private Sale $sale;
    private Customer $customer;
    private array $config;

    public function __construct()
    {
        parent::__construct('config');
        $this->config = config(OSPOS::class)->settings;
        $this->thermalPrinter = new ThermalPrinter($this->config);
        $this->sale_lib = new Sale_lib();
        $this->tax_lib = new Tax_lib();
        $this->barcode_lib = new Barcode_lib();
        $this->sale = model(Sale::class);
        $this->customer = model(Customer::class);
    }

    /**
     * Print a test page.
     * GET /printer/test
     *
     * @return void
     * @noinspection PhpUnused
     */
    public function getTest(): void
    {
        $printerName = $this->request->getGet('printer') ?? ($this->config['escpos_printer'] ?? 'TM-T20');

        try {
            $this->thermalPrinter->connect($printerName);
            $this->thermalPrinter->printTest();

            echo json_encode([
                'success' => true,
                'message' => 'Teste enviado para impressora "' . $printerName . '" com sucesso!'
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Print a sale receipt.
     * GET /printer/printReceipt/{sale_id}
     *
     * @param int $sale_id
     * @return void
     * @noinspection PhpUnused
     */
    public function getPrintReceipt(int $sale_id): void
    {
        $printerName = $this->request->getGet('printer') ?? ($this->config['escpos_printer'] ?? 'TM-T20');

        try {
            $saleData = $this->loadSaleData($sale_id);
            if (!$saleData) {
                echo json_encode(['success' => false, 'message' => 'Venda #' . $sale_id . ' não encontrada.']);
                return;
            }

            $this->thermalPrinter->connect($printerName);
            $this->thermalPrinter->printReceipt($saleData);

            echo json_encode([
                'success' => true,
                'message' => 'Recibo da venda #' . $sale_id . ' enviado para impressora "' . $printerName . '".'
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Quick print called via AJAX after sale completion.
     * POST /printer/quickPrint
     *
     * @return void
     * @noinspection PhpUnused
     */
    public function postQuickPrint(): void
    {
        $saleId = $this->request->getPost('sale_id');
        if (!$saleId) {
            echo json_encode(['success' => false, 'message' => 'ID da venda não fornecido.']);
            return;
        }

        $printerName = $this->config['escpos_printer'] ?? 'TM-T20';

        try {
            $saleData = $this->loadSaleData((int)$saleId);
            if (!$saleData) {
                echo json_encode(['success' => false, 'message' => 'Venda #' . $saleId . ' não encontrada.']);
                return;
            }

            $this->thermalPrinter->connect($printerName);
            $this->thermalPrinter->printReceipt($saleData);

            echo json_encode(['success' => true, 'message' => 'Impresso com sucesso!']);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Load sale data for thermal printing.
     */
    private function loadSaleData(int $saleId): ?array
    {
        $saleInfo = $this->sale->get_info($saleId)->getRowArray();
        if (!$saleInfo) {
            return null;
        }

        $this->sale_lib->clear_all();
        $this->sale_lib->copy_entire_sale($saleId);

        $data = [];
        $data['cart'] = $this->sale_lib->get_cart();
        $data['payments'] = $this->sale_lib->get_payments();
        $data['selected_payment_type'] = $this->sale_lib->get_payment_type();

        $taxDetails = $this->tax_lib->get_taxes($data['cart'], $saleId);
        $data['taxes'] = $this->sale->get_sales_taxes($saleId);
        $data['discount'] = $this->sale_lib->get_discount();
        $data['transaction_time'] = to_datetime(strtotime($saleInfo['sale_time']));
        $data['transaction_date'] = to_date(strtotime($saleInfo['sale_time']));

        $totals = $this->sale_lib->get_totals($taxDetails[0]);
        $data['subtotal'] = $totals['subtotal'];
        $data['prediscount_subtotal'] = $totals['prediscount_subtotal'];
        $data['total'] = $totals['total'];
        $data['amount_change'] = ($totals['amount_due'] ?? 0) * -1;

        $employeeInfo = $this->employee->get_info($this->sale_lib->get_employee());
        $data['employee'] = $employeeInfo->first_name . ' ' . mb_substr($employeeInfo->last_name, 0, 1);

        $customerId = $this->sale_lib->get_customer();
        if ($customerId) {
            $customerInfo = $this->customer->get_info($customerId);
            if ($customerInfo) {
                $data['customer'] = $customerInfo->first_name . ' ' . $customerInfo->last_name;
                $data['customer_cnpf'] = $customerInfo->account_number ?? '';
            }
        }

        $data['sale_id_num'] = $saleId;
        $data['sale_id'] = 'POS ' . $saleId;
        $data['comments'] = $saleInfo['comment'] ?? '';
        $data['invoice_number'] = $saleInfo['invoice_number'] ?? '';
        $data['quote_number'] = $saleInfo['quote_number'] ?? '';
        $data['sale_status'] = $saleInfo['sale_status'] ?? '';
        $data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['sale_id']);

        return $data;
    }
}
