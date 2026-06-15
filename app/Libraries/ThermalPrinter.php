<?php

namespace App\Libraries;

use Config\OSPOS;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\CapabilityProfile;

/**
 * ThermalPrinter Library
 *
 * Integrates OSPOS with ESC/POS thermal printers (Epson TM-T20, etc.)
 * Uses mike42/escpos-php to send raw ESC/POS commands directly to the printer.
 */
class ThermalPrinter
{
    private Printer $printer;
    private array $config;
    private int $paperWidth;     // 48 or 56 characters (for 58mm or 80mm paper)

    /**
     * @param array $config OSPOS settings array
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->paperWidth = ($config['escpos_paper_width'] ?? '80') === '58' ? 32 : 48;
    }

    /**
     * Connect to the printer via CUPS or direct method.
     *
     * @param string $printerName CUPS printer name (default: TM-T20)
     * @return bool
     * @throws \RuntimeException
     */
    public function connect(string $printerName = 'TM-T20'): bool
    {
        $connector = null;
        $connectionType = $this->config['escpos_connection'] ?? 'cups';

        try {
            switch ($connectionType) {
                case 'cups':
                    $connector = new CupsPrintConnector($printerName);
                    break;

                case 'file':
                    $device = $this->config['escpos_device'] ?? '/dev/usb/lp0';
                    $connector = new FilePrintConnector($device);
                    break;

                case 'network':
                    $host = $this->config['escpos_host'] ?? 'localhost';
                    $port = (int)($this->config['escpos_port'] ?? 9100);
                    $connector = new NetworkPrintConnector($host, $port);
                    break;

                default:
                    $connector = new CupsPrintConnector($printerName);
            }

            $profile = CapabilityProfile::load('default');
            $this->printer = new Printer($connector, $profile);
            return true;

        } catch (\Exception $e) {
            throw new \RuntimeException(
                'Não foi possível conectar à impressora "' . $printerName . '": ' . $e->getMessage()
            );
        }
    }

    /**
     * Print a full sale receipt from sale data array.
     *
     * @param array $saleData The sale data array (from Sales controller or _load_sale_data)
     * @param string $type 'receipt', 'invoice', or 'quote'
     * @return void
     * @throws \RuntimeException
     */
    public function printReceipt(array $saleData, string $type = 'receipt'): void
    {
        if (!isset($this->printer)) {
            throw new \RuntimeException('Printer not connected. Call connect() first.');
        }

        $printer = $this->printer;
        $width = $this->paperWidth;

        try {
            // ---- Header: Company Info ----
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->setTextSize(2, 2);
            $this->writeLn($this->config['company'] ?? 'OSPOS');
            $printer->setTextSize(1, 1);
            $printer->setEmphasis(false);

            // Company logo (if exists and configured)
            if (!empty($this->config['company_logo']) && !empty($this->config['escpos_show_logo']) && $this->config['escpos_show_logo'] == 1) {
                $logoPath = FCPATH . 'uploads/' . $this->config['company_logo'];
                if (file_exists($logoPath)) {
                    try {
                        $logo = EscposImage::load($logoPath);
                        $printer->bitImage($logo);
                        $printer->feed(1);
                    } catch (\Exception $e) {
                        // Silently skip logo if it can't be rendered
                    }
                }
            }

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->writeLn($this->config['address'] ?? '');
            if (!empty($this->config['phone'])) {
                $this->writeLn('Tel: ' . $this->config['phone']);
            }
            if (!empty($this->config['tax_id'])) {
                $this->writeLn('CNPJ/CPF: ' . $this->config['tax_id']);
            }
            $printer->feed(1);

            // ---- Receipt Header ----
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $receiptLabel = match ($type) {
                'invoice' => 'FATURA',
                'quote'   => 'ORCAMENTO',
                default   => 'CUPOM FISCAL',
            };
            $this->writeLn($receiptLabel);
            $printer->setEmphasis(false);
            $printer->feed(1);

            // ---- Sale Info ----
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->writeLn('Pedido: ' . ($saleData['sale_id'] ?? ''));
            if (!empty($saleData['transaction_time'])) {
                $this->writeLn('Data: ' . $saleData['transaction_time']);
            }
            $this->writeLn('Operador: ' . ($saleData['employee'] ?? ''));

            if (!empty($saleData['customer'])) {
                $this->writeLn('Cliente: ' . $saleData['customer']);
            }
            if (!empty($saleData['customer_cnpf'])) {
                $this->writeLn('CPF/CNPJ: ' . $saleData['customer_cnpf']);
            }
            $printer->feed(1);

            // ---- Divider ----
            $this->writeDivider();

            // ---- Items Header ----
            $printer->setEmphasis(true);
            $this->writeRow(
                str_pad('ITEM', $width - 22),
                str_pad('QTD', 6),
                'VALOR'
            );
            $printer->setEmphasis(false);
            $this->writeDivider('.', '-');

            // ---- Items ----
            $cart = $saleData['cart'] ?? [];
            foreach ($cart as $item) {
                // Skip items not meant for printing
                if (isset($item['print_option']) && $item['print_option'] !== PRINT_YES) {
                    continue;
                }

                $name = $item['name'] ?? 'Item';
                if (!empty($item['attribute_values'])) {
                    $name .= ' (' . $item['attribute_values'] . ')';
                }

                // Item name (may wrap)
                $this->writeLn($this->truncate($name, $width));

                $qty = $item['quantity'] ?? 1;
                $price = $item['price'] ?? 0;
                $total = $item[($this->config['receipt_show_total_discount'] ? 'total' : 'discounted_total')] ?? 0;

                $qtyStr = number_format((float)$qty, 2, ',', '.');
                $priceStr = 'R$ ' . number_format((float)$price, 2, ',', '.');
                $totalStr = 'R$ ' . number_format((float)$total, 2, ',', '.');

                $line = str_pad($priceStr, 16, ' ', STR_PAD_LEFT)
                      . '  '
                      . str_pad($qtyStr, 6, ' ', STR_PAD_LEFT)
                      . '  '
                      . str_pad($totalStr, 12, ' ', STR_PAD_LEFT);

                $this->writeLn($line);

                // Description (if enabled)
                if (!empty($this->config['receipt_show_description']) && !empty($item['description'])) {
                    $this->writeLn('  ' . $this->truncate($item['description'], $width - 2));
                }

                // Serial number (if enabled)
                if (!empty($this->config['receipt_show_serialnumber']) && !empty($item['serialnumber'])) {
                    $this->writeLn('  S/N: ' . $item['serialnumber']);
                }
            }

            $this->writeDivider();

            // ---- Totals ----
            if (!empty($saleData['prediscount_subtotal']) && $saleData['prediscount_subtotal'] != $saleData['subtotal']) {
                $this->writeTotalLine('Subtotal s/ desconto', $saleData['prediscount_subtotal']);
            }
            $this->writeTotalLine('Subtotal', $saleData['subtotal']);

            // Taxes
            $taxes = $saleData['taxes'] ?? [];
            foreach ($taxes as $tax) {
                $taxName = $tax['tax_group'] ?? $tax['percent'] ?? 'Imposto';
                $taxValue = $tax['tax_value'] ?? 0;
                $this->writeTotalLine((string)$taxName, (float)$taxValue);
            }

            // Total
            $printer->setEmphasis(true);
            $printer->setTextSize(2, 2);
            $this->writeTotalLine('TOTAL', $saleData['total'] ?? 0);
            $printer->setTextSize(1, 1);
            $printer->setEmphasis(false);

            // Payments
            $payments = $saleData['payments'] ?? [];
            foreach ($payments as $payment) {
                $paymentType = $payment['payment_type'] ?? 'Pagamento';
                $paymentAmount = $payment['payment_amount'] ?? 0;
                $this->writeTotalLine((string)$paymentType, (float)$paymentAmount);
            }

            // Change
            if (!empty($saleData['amount_change']) && $saleData['amount_change'] > 0) {
                $this->writeTotalLine('Troco', $saleData['amount_change']);
            }

            $printer->feed(1);

            // ---- Barcode ----
            if (!empty($saleData['sale_id'])) {
                try {
                    $printer->setJustification(Printer::JUSTIFY_CENTER);
                    $barcodeText = 'POS ' . ($saleData['sale_id_num'] ?? $saleData['sale_id']);
                    $printer->barcode($barcodeText, Printer::BARCODE_CODE39);
                    $printer->feed(1);
                } catch (\Exception $e) {
                    // Barcode may fail on some printers, continue
                }
            }

            // ---- Footer ----
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->feed(1);
            if (!empty($this->config['return_policy'])) {
                $this->writeLn($this->config['return_policy']);
            }
            $this->writeLn('Obrigado pela preferencia!');

            // Paper cut
            $printer->feed(3);
            $printer->cut(Printer::CUT_FULL);

            // Open cash drawer (usually pin 2 on RJ-11 connector)
            try {
                $printer->pulse();
            } catch (\Exception $e) {
                // Drawer pulse is optional
            }

            $printer->close();

        } catch (\Exception $e) {
            if (isset($this->printer)) {
                try { $this->printer->close(); } catch (\Exception $ignored) {}
            }
            throw new \RuntimeException('Erro ao imprimir recibo: ' . $e->getMessage());
        }
    }

    /**
     * Print a test page to verify the printer is working.
     *
     * @return void
     * @throws \RuntimeException
     */
    public function printTest(): void
    {
        if (!isset($this->printer)) {
            throw new \RuntimeException('Printer not connected. Call connect() first.');
        }

        $printer = $this->printer;

        try {
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->setTextSize(2, 2);
            $this->writeLn('=== TESTE ===');
            $printer->setTextSize(1, 1);
            $printer->setEmphasis(false);
            $printer->feed(1);

            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->writeLn('OSPOS - Impressao Termica');
            $this->writeLn('Printer: ' . ($this->config['escpos_printer'] ?? 'TM-T20'));
            $this->writeLn('Papel: ' . ($this->config['escpos_paper_width'] ?? '80') . 'mm');
            $this->writeLn('Data: ' . date('d/m/Y H:i:s'));
            $printer->feed(1);

            // Font tests
            $printer->setEmphasis(true);
            $this->writeLn('** NEGRITO **');
            $printer->setEmphasis(false);

            $printer->setDoubleStrike(true);
            $this->writeLn('Dupla impressao');
            $printer->setDoubleStrike(false);

            $printer->setUnderline(true);
            $this->writeLn('Sublinhado');
            $printer->setUnderline(false);

            $printer->feed(1);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->writeLn('Se voce esta lendo isso,');
            $this->writeLn('a impressora esta funcionando!');
            $printer->feed(1);

            // Barcode test
            try {
                $printer->barcode('TEST-OSPOS', Printer::BARCODE_CODE39);
            } catch (\Exception $e) {}

            $printer->feed(3);
            $printer->cut(Printer::CUT_FULL);
            $printer->close();

        } catch (\Exception $e) {
            if (isset($this->printer)) {
                try { $this->printer->close(); } catch (\Exception $ignored) {}
            }
            throw new \RuntimeException('Erro no teste de impressao: ' . $e->getMessage());
        }
    }

    // ========================
    //   Helper Methods
    // ========================

    /**
     * Write a line of text with proper encoding.
     */
    private function writeLn(string $text): void
    {
        $this->printer->text($text . "\n");
    }

    /**
     * Write a divider line.
     */
    private function writeDivider(string $char = '=', string $margin = '-'): void
    {
        $this->writeLn(str_repeat($char, $this->paperWidth));
    }

    /**
     * Write a formatted total line (label right-padded, value left-padded).
     */
    private function writeTotalLine(string $label, float $value): void
    {
        $valueStr = 'R$ ' . number_format($value, 2, ',', '.');
        $label = $this->truncate($label, $this->paperWidth - strlen($valueStr) - 1);
        $line = str_pad($label, $this->paperWidth - strlen($valueStr), ' ', STR_PAD_RIGHT)
              . str_pad($valueStr, strlen($valueStr), ' ', STR_PAD_LEFT);
        $this->writeLn($line);
    }

    /**
     * Write a row with three columns.
     */
    private function writeRow(string $left, string $middle, string $right): void
    {
        $this->writeLn($left . ' ' . $middle . ' ' . $right);
    }

    /**
     * Truncate text to fit the line width.
     */
    private function truncate(string $text, int $maxLength): string
    {
        // Account for multi-byte characters (UTF-8)
        $length = mb_strlen($text);
        if ($length <= $maxLength) {
            return $text;
        }
        return mb_substr($text, 0, $maxLength - 1) . '…';
    }
}
