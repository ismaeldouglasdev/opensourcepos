<?php
/**
 * @var string $controller_name
 * @var array $modes
 * @var array $mode
 * @var array $empty_tables
 * @var array $selected_table
 * @var array $stock_locations
 * @var array $stock_location
 * @var array $cart
 * @var bool $items_module_allowed
 * @var bool $change_price
 * @var int $customer_id
 * @var int $customer_discount_type
 * @var float $customer_discount
 * @var float $customer_total
 * @var string $customer_required
 * @var float|int $item_count
 * @var float|int $total_units
 * @var float $subtotal
 * @var array $taxes
 * @var float $total
 * @var float $payments_total
 * @var float $amount_due
 * @var bool $payments_cover_total
 * @var array $payment_options
 * @var array $selected_payment_type
 * @var bool $pos_mode
 * @var array $payments
 * @var string $mode_label
 * @var string $comment
 * @var bool $print_after_sale
 * @var bool $email_receipt
 * @var bool $price_work_orders
 * @var string $invoice_number
 * @var int $cash_mode
 * @var float $non_cash_total
 * @var float $cash_amount_due
 * @var array $config
 */

use App\Models\Employee;

?>

<?= view('partial/header') ?>
<script>
window._tutorialSteps = [
    {
        target: '#item_search',
        title: 'Buscar Produtos',
        text: 'Digite o <strong>código de barras</strong> ou <strong>nome</strong> do produto aqui. Use <strong>Enter</strong> para adicionar ao carrinho.'
    },
    {
        target: '#cart_contents',
        title: 'Carrinho de Compras',
        text: 'Os itens adicionados aparecem aqui. Você pode <strong>ajustar quantidades</strong>, <strong>aplicar desconto</strong> ou <strong>remover</strong> itens.'
    },
    {
        target: '#select_customer',
        title: 'Selecionar Cliente',
        text: 'Opcional: selecione um <strong>cliente</strong> para associar à venda. Útil para vendas fiado ou controle de histórico.'
    },
    {
        target: '#sale_total',
        title: 'Total da Venda',
        text: 'Aqui mostra o <strong>total acumulado</strong> da venda. O valor devido é exibido abaixo.'
    },
    {
        target: '#suspend_sale_button',
        title: 'Suspender / Finalizar',
        text: 'Use <strong>Suspender</strong> para pausar a venda ou <strong>Finalizar</strong> para concluir e registrar o pagamento.'
    }
];
</script>
<style>
.checkout-modal .modal-body { padding: 10px 15px; }
.checkout-total { font-weight: 600; text-align: center; background: var(--os-primary-light, #e8f8f0); border-radius: var(--os-radius-lg, 12px); margin-bottom: 8px; padding: 8px; font-size: 14px; }

/* Payment list */
.payment-list { margin-bottom: 5px; }
.payment-list button { margin-bottom: 3px; height: 40px; font-size: 15px; text-align: center; line-height: 22px; display: flex; align-items: center; justify-content: center; gap: 6px; border-radius: var(--os-radius, 8px) !important; }

/* Payment buttons */
.payment-btn.cash { background: var(--pay-cash, #16a34a); border-color: var(--pay-cash, #16a34a); color: white; }
.payment-btn.debit { background: var(--pay-debit, #2563eb); border-color: var(--pay-debit, #2563eb); color: white; }
.payment-btn.credit { background: var(--pay-credit, #7c3aed); border-color: var(--pay-credit, #7c3aed); color: white; }
.payment-btn.pix { background: var(--pay-pix, #0891b2); border-color: var(--pay-pix, #0891b2); color: white; }
.payment-btn.fiado { background: var(--pay-fiado, #ea580c); border-color: var(--pay-fiado, #ea580c); color: white; }
.payment-btn:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.15); transform: translateY(-1px); }
.payment-btn.active { box-shadow: 0 0 0 3px rgba(0,0,0,0.1), 0 4px 12px rgba(0,0,0,0.15); transform: translateY(-1px); }

/* Troco */
.troco-display { font-size: 15px; padding: 6px; margin-bottom: 8px; text-align: center; font-weight: 600; }
.troco-display.negative { background: #fef2f2; color: #991b1b; }

/* Checkout form */
.form-group { margin-bottom: 5px; }
.form-group input { height: 36px; font-size: 17px; text-align: center; }

/* Finalizar venda button */
#btn_finalizar_venda { height: 44px; font-size: 17px; font-weight: bold; margin-top: 8px; }
#finish_checkout_btn { height: 40px; font-size: 17px; }

/* Hidden payment details */
#payment_details { display: none !important; }
</style>

<style>
/* Discount toggle override */
#register td:nth-child(6) { overflow: hidden; }
#register td .input-group { display: flex; align-items: center; width: 100%; }
#register td .input-group .form-control { flex: 1 1 0%; min-width: 0; height: 28px; font-size: 12px; padding: 1px 4px; }
#register td .input-group .input-group-btn { flex: 0 0 auto; display: flex; align-items: center; }
#register td .input-group .input-group-btn .bootstrap-toggle { margin: 0; flex: 0 0 46px; width: 46px; }
#register td .input-group .input-group-btn .bootstrap-toggle .toggle { width: 46px !important; min-width: 46px !important; min-height: 26px !important; }
#register td .input-group .input-group-btn .bootstrap-toggle .toggle.btn-sm { width: 46px !important; min-width: 46px !important; }
#register td .input-group .input-group-btn .bootstrap-toggle .toggle-on { padding-right: 0 !important; text-align: center; }
#register td .input-group .input-group-btn .bootstrap-toggle .toggle-off { padding-left: 0 !important; text-align: center; }
#register td .input-group .input-group-btn .bootstrap-toggle .toggle-on.btn { padding-right: 0 !important; }
#register td .input-group .input-group-btn .bootstrap-toggle .toggle-off.btn { padding-left: 0 !important; }

/* Custom discount toggle */
.disc-toggle { display: inline-flex; border-radius: 4px; overflow: hidden; border: 1px solid #ccc; cursor: pointer; }
.disc-toggle-btn { padding: 3px 7px; font-size: 11px; font-weight: 700; border: none; cursor: pointer; background: #f0f0f0; color: #888; line-height: 1; min-width: 22px; text-align: center; transition: background 0.15s, color 0.15s; }
.disc-toggle-btn:hover { background: #e0e0e0; }
.disc-toggle-btn.active { background: #5cb85c; color: #fff; }

/* Stock status badges */
.stock-badge { font-size: 11px; padding: 1px 4px; vertical-align: middle; }
.stock-badge-zerado { background: #f59e0b; color: #000; }
.stock-badge-irregular { background: #dc2626; color: #fff; }

/* Empty cart state */
.empty-cart-row td {
    padding: 40px 20px !important;
    text-align: center !important;
}
.empty-cart-msg {
    color: var(--os-text-light, #9ca3af) !important;
    font-size: 18px !important;
    font-weight: 500 !important;
}
.empty-cart-icon {
    font-size: 48px !important;
    opacity: 0.3 !important;
    margin-bottom: 8px !important;
    display: block !important;
}
.empty-cart-sub {
    color: var(--os-text-light, #9ca3af) !important;
    font-size: 14px !important;
}
</style>

<style>
.checkout-modal .modal-content { border-radius: 10px; }
.payment-list button { margin-bottom: 2px; height: 34px; font-size: 13px; text-align: center; font-size: 14px; }
.troco-display.negative { background: #ffebee; }
</style>


<?php
if (isset($error)) {
    echo '<div class="alert alert-dismissible alert-danger">' . esc($error) . '</div>';
}

if (!empty($warning)) {
    echo '<div class="alert alert-dismissible alert-warning">' . esc($warning) . '</div>';
}

if (isset($success)) {
    echo '<div class="alert alert-dismissible alert-success">' . esc($success) . '</div>';
}

if (!empty($editing_sale_id)) {
    echo '<div class="alert alert-info" style="text-align:center; margin-bottom:10px;">';
    echo '<strong>Editando Venda #' . $editing_sale_id . '</strong> - ';
    echo anchor("sales/manage", "Voltar para lista", ['style' => 'color:#fff;']);
    echo '</div>';
}
?>

<div id="register_wrapper">

    <!-- Top register controls -->
    <?= form_open("$controller_name/changeMode", ['id' => 'mode_form', 'class' => 'form-horizontal panel panel-default']) ?>
        <div class="panel-body form-group">
            <ul>
                <li class="pull-left first_li">
                    <label class="control-label"><?= lang(ucfirst($controller_name) . '.mode') ?></label>
                </li>
                <li class="pull-left">
                    <?= form_dropdown('mode', $modes, $mode, ['onchange' => "$('#mode_form').submit();", 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit']) ?>
                </li>
                <?php if ($config['dinner_table_enable']) { ?>
                    <li class="pull-left first_li">
                        <label class="control-label"><?= lang(ucfirst($controller_name) . '.table') ?></label>
                    </li>
                    <li class="pull-left">
                        <?= form_dropdown('dinner_table', $empty_tables, $selected_table, ['onchange' => "$('#mode_form').submit();", 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit']) ?>
                    </li>
                <?php } ?>
                <?php if (count($stock_locations) > 1) { ?>
                    <li class="pull-left">
                        <label class="control-label"><?= lang(ucfirst($controller_name) . '.stock_location') ?></label>
                    </li>
                    <li class="pull-left">
                        <?= form_dropdown('stock_location', $stock_locations, $stock_location, ['onchange' => "$('#mode_form').submit();", 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit']) ?>
                    </li>
                <?php } ?>

                <li class="pull-right">
                    <button class="btn btn-default btn-sm modal-dlg" id="show_suspended_sales_button" data-href="<?= esc("$controller_name/suspended") ?>"
                        title="<?= lang(ucfirst($controller_name) . '.suspended_sales') ?>">
                        <span class="glyphicon glyphicon-align-justify">&nbsp;</span><?= lang(ucfirst($controller_name) . '.suspended_sales') ?>
                    </button>
                </li>

                <?php
                $employee = model(Employee::class);
                if ($employee->has_grant('reports_sales', session('person_id'))) {
                ?>
                    <li class="pull-right">
                        <?= anchor(
                            "$controller_name/manage",
                            '<span class="glyphicon glyphicon-list-alt">&nbsp;</span>' . lang(ucfirst($controller_name) . '.takings'),
                            array('class' => 'btn btn-primary btn-sm', 'id' => 'sales_takings_button', 'title' => lang(ucfirst($controller_name) . '.takings'))
                        ) ?>
                    </li>
                <?php } ?>
            </ul>
        </div>
    <?= form_close() ?>

    <?php $tabindex = 0; ?>

    <?= form_open("$controller_name/add", ['id' => 'add_item_form', 'class' => 'form-horizontal panel panel-default']) ?>
        <div class="panel-body form-group">
            <ul>
                <li class="pull-left first_li">
                    <label for="item" class="control-label"><?= lang(ucfirst($controller_name) . '.find_or_scan_item_or_receipt') ?></label>
                </li>
                <li class="pull-left">
                    <?= form_input(['name' => 'item', 'id' => 'item', 'class' => 'form-control input-sm', 'size' => '50', 'tabindex' => ++$tabindex]) ?>
                    <span class="ui-helper-hidden-accessible" role="status"></span>
                </li>
                <li class="pull-right">
                    <button type="button" id="new_item_button" class="btn btn-info btn-sm pull-right modal-dlg" data-btn-new="<?= lang('Common.new') ?>" data-btn-submit="<?= lang('Common.submit') ?>" data-href="<?= "items/view" ?>" title="<?= lang(ucfirst($controller_name) . ".new_item") ?>">
                        <span class="glyphicon glyphicon-tag">&nbsp;</span><?= lang(ucfirst($controller_name) . ".new_item") ?>
                    </button>
                </li>
            </ul>
        </div>
    <?= form_close() ?>


    <!-- Sale Items List -->

    <table class="sales_table_100" id="register">
        <thead>
            <tr>
                <th style="width: 4%;"><?= lang('Common.delete') ?></th>
                <th style="width: 10%;"><?= lang(ucfirst($controller_name) . '.item_number') ?></th>
                <th style="width: 35%;"><?= lang(ucfirst($controller_name) . '.item_name') ?></th>
                <th style="width: 9%;"><?= lang(ucfirst($controller_name) . '.price') ?></th>
                <th style="width: 9%;"><?= lang(ucfirst($controller_name) . '.quantity') ?></th>
                <th style="width: 13%;"><?= lang(ucfirst($controller_name) . '.discount') ?></th>
                <th style="width: 9%;"><?= lang(ucfirst($controller_name) . '.total') ?></th>
                <th style="width: 4%;"><?= lang(ucfirst($controller_name) . '.update') ?></th>
            </tr>
        </thead>

        <tbody id="cart_contents">
            <?php if (count($cart) == 0) { ?>
                <tr>
                    <td colspan="8">
                        <div class="alert alert-dismissible alert-info"><?= lang(ucfirst($controller_name) . '.no_items_in_cart') ?></div>
                    </td>
                </tr>
            <?php
            } else {
                foreach (array_reverse($cart, true) as $line => $item) {
            ?>
                    <?= form_open("$controller_name/editItem/$line", ['class' => 'form-horizontal', 'id' => "cart_$line"]) ?>
                        <tr>
                            <td>
                                <?php
                                echo anchor("$controller_name/deleteItem/$line", '<span class="glyphicon glyphicon-trash"></span>');
                                echo form_hidden('location', (string)$item['item_location']);
                                echo form_input(['type' => 'hidden', 'name' => 'item_id', 'value' => $item['item_id']]);
                                ?>
                            </td>
                            <?php if ($item['item_type'] == ITEM_TEMP) { ?>
                                <td><?= form_input(['name' => 'item_number', 'id' => 'item_number', 'class' => 'form-control input-sm', 'value' => $item['item_number'], 'tabindex' => ++$tabindex]) ?></td>
                                <td style="align: center;">
                                    <?= form_input(['name' => 'name', 'id' => 'name', 'class' => 'form-control input-sm', 'value' => $item['name'], 'tabindex' => ++$tabindex]) ?>
                                </td>
                            <?php } else { ?>
                                <td><?= esc($item['item_number'] ?? '') ?></td>
                                <td style="align: center;">
                                    <?= esc($item['name'] ?? '') . ' ' . implode(' ', [$item['attribute_values'] ?? '', $item['attribute_dtvalues'] ?? '']) ?>
                                    <br>
                                    <?php if (($item['stock_type'] ?? '') == '0'):
                                        $stock_qty = to_quantity_decimals($item['in_stock'] ?? 0);
                                        $stock_lbl = $item['stock_name'] ?? '';
                                        $stk_status = $item['stock_status'] ?? 0;
                                        echo '[' . $stock_qty . ' ' . lang('Items.stock') . ']';
                                        if ($stk_status == 2) {
                                            echo ' <span class="stock-badge stock-badge-irregular" title="Estoque irregular - precisa contagem">IRREGULAR</span>';
                                        } elseif ($stk_status == 1) {
                                            echo ' <span class="stock-badge stock-badge-zerado" title="Produto zerado">ZERADO</span>';
                                        }
                                    endif; ?>
                                </td>
                            <?php } ?>

                            <td>
                                <?php
                                if ($items_module_allowed && $change_price) {
                                    echo form_input(['name' => 'price', 'class' => 'form-control input-sm', 'value' => to_currency_no_money($item['price']), 'tabindex' => ++$tabindex, 'onClick' => 'this.select();']);
                                } else {
                                    echo to_currency($item['price']);
                                    echo form_hidden('price', to_currency_no_money($item['price']));
                                }
                                ?>
                            </td>

                            <td>
                                <?php
                                if (!empty($item['is_serialized'])) {
                                    echo to_quantity_decimals($item['quantity']);
                                    echo form_hidden('quantity', $item['quantity']);
                                } else {
                                    echo form_input(['name' => 'quantity', 'class' => 'form-control input-sm', 'value' => to_quantity_decimals($item['quantity'] ?? 0), 'tabindex' => ++$tabindex, 'onClick' => 'this.select();']);
                                }
                                ?>
                            </td>

                            <td>
                                <div class="input-group">
                                    <?= form_input(['name' => 'discount', 'class' => 'form-control input-sm', 'value' => $item['discount_type'] ? to_currency_no_money($item['discount']) : to_decimals($item['discount']), 'tabindex' => ++$tabindex, 'onClick' => 'this.select();']) ?>
                                    <span class="input-group-btn">
                                        <span class="disc-toggle" data-line="<?= $line ?>">
                                            <button type="button" class="disc-toggle-btn <?= $item['discount_type'] == 1 ? 'active' : '' ?>" data-val="1"><?= $config['currency_symbol'] ?></button>
                                            <button type="button" class="disc-toggle-btn <?= $item['discount_type'] != 1 ? 'active' : '' ?>" data-val="0">%</button>
                                        </span>
                                    </span>
                                </div>
                            </td>

                            <td>
                                <?php
                                if (($item['item_type'] ?? '') == ITEM_AMOUNT_ENTRY) {
                                    echo form_input(['name' => 'discounted_total', 'class' => 'form-control input-sm', 'value' => to_currency_no_money($item['discounted_total'] ?? 0), 'tabindex' => ++$tabindex, 'onClick' => 'this.select();']);
                                } else {
                                    echo to_currency($item['discounted_total'] ?? 0);
                                }
                                ?>
                            </td>

                            <td>
                                <a href="javascript:document.getElementById('<?= "cart_$line" ?>').submit();" title="<?= lang(ucfirst($controller_name) . '.update') ?>">
                                    <span class="glyphicon glyphicon-refresh"></span>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <?php if ($item['item_type'] == ITEM_TEMP) { ?>
                                <td><?= form_input(['type' => 'hidden', 'name' => 'item_id', 'value' => $item['item_id']]) ?></td>
                                <td style="align: center;" colspan="6">
                                    <?= form_input(['name' => 'item_description', 'id' => 'item_description', 'class' => 'form-control input-sm', 'value' => $item['description'], 'tabindex' => ++$tabindex]) ?>
                                </td>
                                <td> </td>
                            <?php } else { ?>
                                <td>&nbsp;</td>
                                <?php if ($item['allow_alt_description']) { ?>
                                    <td style="color: #2F4F4F;"><?= lang(ucfirst($controller_name) . '.description_abbrv') ?></td>
                                <?php } ?>

                                <td colspan="2" style="text-align: left;">
                                    <?php
                                    if ($item['allow_alt_description']) {
                                        echo form_input(['name' => 'description', 'class' => 'form-control input-sm', 'value' => $item['description'], 'onClick' => 'this.select();']);
                                    } else {
                                        if ($item['description'] != '') {
                                            echo $item['description'];
                                            echo form_hidden('description', $item['description']);
                                        } else {
                                            echo lang(ucfirst($controller_name) . '.no_description');
                                            echo form_hidden('description', '');
                                        }
                                    }
                                    ?>
                                </td>
                                <td>&nbsp;</td>
                                <td style="color: #2F4F4F;">
                                    <?php
                                    if ($item['is_serialized']) {
                                        echo lang(ucfirst($controller_name) . '.serial');
                                    }
                                    ?>
                                </td>
                                <td colspan="4" style="text-align: left;">
                                    <?php
                                    if ($item['is_serialized']) {
                                        echo form_input(['name' => 'serialnumber', 'class' => 'form-control input-sm', 'value' => $item['serialnumber'], 'onClick' => 'this.select();']);
                                    } else {
                                        echo form_hidden('serialnumber', '');
                                    }
                                    ?>
                                </td>
                            <?php } ?>
                        </tr>
                    <?= form_close() ?>
            <?php
                }
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Overall Sale -->

<div id="overall_sale" class="panel panel-default">
    <div class="panel-body">
        <?= form_open("$controller_name/selectCustomer", ['id' => 'select_customer_form', 'class' => 'form-horizontal']) ?>
            <?php if (isset($customer)) { ?>
                <table class="sales_table_100">
                    <tr>
                        <th style="width: 55%;"><?= lang(ucfirst($controller_name) . '.customer') ?></th>
                        <th style="width: 45%; text-align: right;"><?= anchor("customers/view/$customer_id", $customer, ['class' => 'modal-dlg', 'data-btn-submit' => lang('Common.submit'), 'title' => lang('Customers.update')]) ?></th>
                    </tr>
                    <?php if (!empty($customer_email)) { ?>
                        <tr>
                            <th style="width: 55%;"><?= lang(ucfirst($controller_name) . '.customer_email') ?></th>
                            <th style="width: 45%; text-align: right;"><?= esc($customer_email) ?></th>
                        </tr>
                    <?php } ?>
                    <?php if (!empty($customer_address)) { ?>
                        <tr>
                            <th style="width: 55%;"><?= lang(ucfirst($controller_name) . '.customer_address') ?></th>
                            <th style="width: 45%; text-align: right;"><?= esc($customer_address) ?></th>
                        </tr>
                    <?php } ?>
                    <?php if (!empty($customer_location)) { ?>
                        <tr>
                            <th style="width: 55%;"><?= lang(ucfirst($controller_name) . '.customer_location') ?></th>
                            <th style="width: 45%; text-align: right;"><?= esc($customer_location) ?></th>
                        </tr>
                    <?php } ?>
                    <tr>
                        <th style="width: 55%;"><?= lang(ucfirst($controller_name) . '.customer_discount') ?></th>
                        <th style="width: 45%; text-align: right;"><?= ($customer_discount_type == FIXED) ? to_currency($customer_discount) : $customer_discount . '%' ?></th>
                    </tr>
                    <?php if ($config['customer_reward_enable']): ?>
                        <?php if (!empty($customer_rewards)) { ?>
                            <tr>
                                <th style="width: 55%;"><?= lang(ucfirst($controller_name) . '.rewards_package') ?></th>
                                <th style="width: 45%; text-align: right;"><?= esc($customer_rewards['package_name']) ?></th>
                            </tr>
                            <tr>
                                <th style="width: 55%;"><?= lang('Customers.available_points') ?></th>
                                <th style="width: 45%; text-align: right;"><?= esc($customer_rewards['points']) ?></th>
                            </tr>
                        <?php } ?>
                    <?php endif; ?>
                    <tr>
                        <th style="width: 55%;"><?= lang(ucfirst($controller_name) . '.customer_total') ?></th>
                        <th style="width: 45%; text-align: right;"><?= to_currency($customer_total) ?></th>
                    </tr>
                    <?php if (!empty($mailchimp_info)) { ?>
                        <tr>
                            <th style="width: 55%;"><?= lang(ucfirst($controller_name) . '.customer_mailchimp_status') ?></th>
                            <th style="width: 45%; text-align: right;"><?= esc($mailchimp_info['status']) ?></th>
                        </tr>
                    <?php } ?>
                </table>

                <?= anchor(
                    "$controller_name/removeCustomer",
                    '<span class="glyphicon glyphicon-remove">&nbsp;</span>' . lang('Common.remove') . ' ' . lang('Customers.customer'),
                    ['class' => 'btn btn-danger btn-sm', 'id' => 'remove_customer_button', 'title' => lang('Common.remove') . ' ' . lang('Customers.customer')]
                )
                ?>
            <?php } else { ?>
                <div class="form-group" id="select_customer">
                    <label id="customer_label" for="customer" class="control-label" style="margin-bottom: 1em; margin-top: -1em;">
                        <?= lang(ucfirst($controller_name) . '.select_customer') . esc(" $customer_required") ?>
                    </label>
                    <?= form_input(['name' => 'customer', 'id' => 'customer', 'class' => 'form-control input-sm', 'value' => lang(ucfirst($controller_name) . '.start_typing_customer_name')]) ?>

                    <button class="btn btn-info btn-sm modal-dlg" data-btn-submit="<?= lang('Common.submit') ?>" data-href="<?= "customers/view" ?>" title="<?= lang(ucfirst($controller_name) . ".new_customer") ?>">
                        <span class="glyphicon glyphicon-user">&nbsp;</span><?= lang(ucfirst($controller_name) . ".new_customer") ?>
                    </button>
                    <button class="btn btn-default btn-sm modal-dlg" id="show_keyboard_help" data-href="<?= esc("$controller_name/salesKeyboardHelp") ?>" title="<?= lang(ucfirst($controller_name) . '.key_title') ?>">
                        <span class="glyphicon glyphicon-share-alt">&nbsp;</span><?= lang(ucfirst($controller_name) . '.key_help') ?>
                    </button>
                </div>
            <?php } ?>
        <?= form_close() ?>

        <table class="sales_table_100" id="sale_totals">
            <tr>
                <th style="width: 55%;"><?= lang(ucfirst($controller_name) . '.quantity_of_items', [$item_count]) ?></th>
                <th style="width: 45%; text-align: right;"><?= $total_units ?></th>
            </tr>
            <tr>
                <th style="width: 55%;"><?= lang(ucfirst($controller_name) . '.sub_total') ?></th>
                <th style="width: 45%; text-align: right;"><?= to_currency($subtotal) ?></th>
            </tr>
            <?php foreach ($taxes as $tax_group_index => $tax) { ?>
                <tr>
                    <th style="width: 55%;"><?= (float)$tax['tax_rate'] . '% ' . $tax['tax_group'] ?></th>
                    <th style="width: 45%; text-align: right;"><?= to_currency_tax($tax['sale_tax_amount']) ?></th>
                </tr>
            <?php } ?>
            <tr>
                <th style="width: 55%; font-size: 160%"><?= lang(ucfirst($controller_name) . '.total') ?></th>
                <th style="width: 45%; font-size: 160%; text-align: right;"><span id="sale_total"><?= to_currency($total) ?></span></th>
            </tr>
        </table>

        <?php if (count($cart) > 0) { // Only show this part if there are Items already in the register ?>
            <table class="sales_table_100" id="payment_totals">
                <tr>
                    <th style="width: 55%;"><?= lang(ucfirst($controller_name) . '.payments_total') ?></th>
                    <th style="width: 45%; text-align: right;"><?= to_currency($payments_total) ?></th>
                </tr>
                <tr>
                    <th style="width: 55%; font-size: 130%"><?= lang(ucfirst($controller_name) . '.amount_due') ?></th>
                    <th style="width: 45%; font-size: 130%; text-align: right;"><span id="sale_amount_due"><?= to_currency($amount_due) ?></span></th>
                </tr>
            </table>

            <div style="margin: 6px 0;">
                <?php if (!empty($editing_sale_id)): ?>
    <button type="button" class="btn btn-warning btn-lg btn-block" id="btn_finalizar_venda" onclick="openCheckoutModal()">
        <span class="glyphicon glyphicon-pencil"></span> ATUALIZAR VENDA #<?= $editing_sale_id ?>
    </button>
<?php else: ?>
    <button type="button" class="btn btn-success btn-lg btn-block" id="btn_finalizar_venda" onclick="openCheckoutModal()">
        <span class="glyphicon glyphicon-shopping-cart"></span> FINALIZAR VENDA (F2)
    </button>
<?php endif; ?>
</div>

<div id="payment_details">
                <?php if ($payments_cover_total) { // Show Complete sale button instead of Add Payment if there is no amount due left ?>
                    <?= form_open("$controller_name/addPayment", ['id' => 'add_payment_form', 'class' => 'form-horizontal']) ?>
                        <table class="sales_table_100">
                            <tr>
                                <td><?= lang(ucfirst($controller_name) . '.payment') ?></td>
                                <td>
                                    <?= form_dropdown('payment_type', $payment_options, $selected_payment_type, ['id' => 'payment_types', 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit', 'disabled' => 'disabled']) ?>
                                </td>
                            </tr>
                            <tr>
                                <td><span id="amount_tendered_label"><?= lang(ucfirst($controller_name) . '.amount_tendered') ?></span></td>
                                <td>
                                    <?= form_input(['name' => 'amount_tendered', 'id' => 'amount_tendered', 'class' => 'form-control input-sm disabled', 'disabled' => 'disabled', 'value' => '0', 'size' => '5', 'tabindex' => ++$tabindex, 'onClick' => 'this.select();']) ?>
                                </td>
                            </tr>
                        </table>
                    <?= form_close() ?>

                    <?php
                    // Only show this part if in sale or return mode
                    if ($pos_mode) {
                        $due_payment = false;

                        if (count($payments) > 0) {
                            foreach ($payments as $payment_id => $payment) {
                                if ($payment['payment_type'] == lang(ucfirst($controller_name) . '.due')) {
                                    $due_payment = true;
                                }
                            }
                        }

                        if (!$due_payment || ($due_payment && isset($customer))) {    // TODO: $due_payment is not needed because the first clause insures that it will always be true if it gets to this point.  Can be shortened to if (!$due_payment || isset($customer))
                    ?>
                            <div class="btn btn-sm btn-success pull-right" id="finish_sale_button" tabindex="<?= ++$tabindex ?>">
                                <span class="glyphicon glyphicon-ok">&nbsp;</span><?= lang(ucfirst($controller_name) . '.complete_sale') ?>
                            </div>
                    <?php
                        }
                    }
                    ?>
                <?php } else { ?>
                    <?= form_open("$controller_name/addPayment", ['id' => 'add_payment_form', 'class' => 'form-horizontal']) ?>
                        <table class="sales_table_100">
                            <tr>
                                <td><?= lang(ucfirst($controller_name) . '.payment') ?></td>
                                <td>
                                    <?= form_dropdown('payment_type', $payment_options,  $selected_payment_type, ['id' => 'payment_types', 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit']) ?>
                                </td>
                            </tr>
                            <tr>
                                <td><span id="amount_tendered_label"><?= lang(ucfirst($controller_name) . '.amount_tendered') ?></span></td>
                                <td>
                                    <?= form_input(['name' => 'amount_tendered', 'id' => 'amount_tendered', 'class' => 'form-control input-sm non-giftcard-input', 'value' => to_currency_no_money($amount_due), 'size' => '5', 'tabindex' => ++$tabindex, 'onClick' => 'this.select();']) ?>
                                    <?= form_input(['name' => 'amount_tendered', 'id' => 'amount_tendered', 'class' => 'form-control input-sm giftcard-input', 'disabled' => true, 'value' => to_currency_no_money($amount_due), 'size' => '5', 'tabindex' => ++$tabindex]) ?>
                                </td>
                            </tr>
                        </table>
                    <?= form_close() ?>

                    <div class="btn btn-sm btn-success pull-right" id="add_payment_button" tabindex="<?= ++$tabindex ?>">
                        <span class="glyphicon glyphicon-credit-card">&nbsp;</span><?= lang(ucfirst($controller_name) . '.add_payment') ?>
                    </div>
                <?php } ?>

                <?php if (count($payments) > 0) { // Only show this part if there is at least one payment entered. ?>
                    <table class="sales_table_100" id="register">
                        <thead>
                            <tr>
                                <th style="width: 10%;"><?= lang('Common.delete') ?></th>
                                <th style="width: 60%;"><?= lang(ucfirst($controller_name) . '.payment_type') ?></th>
                                <th style="width: 20%;"><?= lang(ucfirst($controller_name) . '.payment_amount') ?></th>
                            </tr>
                        </thead>

                        <tbody id="payment_contents">
                            <?php foreach ($payments as $payment_id => $payment) { ?>
                                <tr>
                                    <td><?= anchor("$controller_name/deletePayment/". base64_encode($payment_id), '<span class="glyphicon glyphicon-trash"></span>') ?></td>
                                    <td><?= $payment['payment_type'] ?></td>
                                    <td style="text-align: right;"><?= to_currency($payment['payment_amount']) ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
            </div>

            <?= form_open("$controller_name/cancel", ['id' => 'buttons_form']) ?>
            <div class="form-group" id="buttons_sale">
                <div class="btn btn-sm btn-default pull-left" id="suspend_sale_button"><span class="glyphicon glyphicon-align-justify">&nbsp;</span><?= lang(ucfirst($controller_name) . '.suspend_sale') ?></div>
                <?php if (!$pos_mode && isset($customer)) { // Only show this part if the payment covers the total ?>
                    <div class="btn btn-sm btn-success" id="finish_invoice_quote_button"><span class="glyphicon glyphicon-ok">&nbsp;</span><?= esc($mode_label) ?></div>
                <?php } ?>

                <div class="btn btn-sm btn-danger pull-right" id="cancel_sale_button"><span class="glyphicon glyphicon-remove">&nbsp;</span><?= lang(ucfirst($controller_name) . '.cancel_sale') ?></div>
            </div>
            <?= form_close() ?>

            <?php if ($payments_cover_total || !$pos_mode) { // Only show this part if the payment cover the total ?>
                <div class="container-fluid">
                    <div class="no-gutter row">
                        <div class="form-group form-group-sm">
                            <div class="col-xs-12">
                                <?= form_label(lang('Common.comments'), 'comments', ['class' => 'control-label', 'id' => 'comment_label', 'for' => 'comment']) ?>
                                <?= form_textarea(['name' => 'comment', 'id' => 'comment', 'class' => 'form-control input-sm', 'value' => $comment, 'rows' => '2']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group form-group-sm">
                            <div class="col-xs-6">
                                <label for="sales_print_after_sale" class="control-label checkbox">
                                    <?= form_checkbox(['name' => 'sales_print_after_sale', 'id' => 'sales_print_after_sale', 'value' => 1, 'checked' => $print_after_sale]) ?>
                                    <?= lang(ucfirst($controller_name) . '.print_after_sale') ?>
                                </label>
                            </div>

                            <?php if (!empty($customer_email)) { ?>
                                <div class="col-xs-6">
                                    <label for="email_receipt" class="control-label checkbox">
                                        <?= form_checkbox(['name' => 'email_receipt', 'id' => 'email_receipt', 'value' => 1, 'checked' => $email_receipt]) ?>
                                        <?= lang(ucfirst($controller_name) . '.email_receipt') ?>
                                    </label>
                                </div>
                            <?php } ?>
                            <?php if ($mode == 'sale_work_order') { ?>
                                <div class="col-xs-6">
                                    <label for="price_work_orders" class="control-label checkbox">
                                        <?= form_checkbox(['name' => 'price_work_orders', 'id' => 'price_work_orders', 'value' => 1, 'checked' => $price_work_orders]) ?>
                                        <?= lang(ucfirst($controller_name) . '.include_prices') ?>
                                    </label>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php if (($mode == 'sale_invoice') && $config['invoice_enable']) { ?>
                        <div class="row">
                            <div class="form-group form-group-sm">
                                <div class="col-xs-6">
                                    <label for="sales_invoice_number" class="control-label checkbox">
                                        <?= lang(ucfirst($controller_name) . '.invoice_enable') ?>
                                    </label>
                                </div>

                                <div class="col-xs-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-addon input-sm">#</span>
                                        <?= form_input(['name' => 'sales_invoice_number', 'id' => 'sales_invoice_number', 'class' => 'form-control input-sm', 'value' => $invoice_number]) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
        <?php
            }
        }
        ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        const redirect = function() {
            window.location.href = "<?= site_url('sales'); ?>";
        };

        $("#remove_customer_button").click(function() {
            $.post("<?= site_url('sales/removeCustomer'); ?>", redirect);
        });

        $(".delete_item_button").click(function() {
            const item_id = $(this).data('item-id');
            $.post("<?= site_url('sales/deleteItem/'); ?>" + item_id, redirect);
        });

        $(".delete_payment_button").click(function() {
            const item_id = $(this).data('payment-id');
            $.post("<?= site_url('sales/deletePayment/'); ?>" + item_id, redirect);
        });

        $("input[name='item_number']").change(function() {
            var item_id = $(this).parents('tr').find("input[name='item_id']").val();
            var item_number = $(this).val();
            $.ajax({
                url: "<?= site_url('sales/changeItemNumber') ?>",
                method: 'post',
                data: {
                    'item_id': item_id,
                    'item_number': item_number,
                },
                dataType: 'json'
            });
        });

        $("input[name='name']").change(function() {
            var item_id = $(this).parents('tr').find("input[name='item_id']").val();
            var item_name = $(this).val();
            $.ajax({
                url: "<?= site_url('sales/changeItemName') ?>",
                method: 'post',
                data: {
                    'item_id': item_id,
                    'item_name': item_name,
                },
                dataType: 'json'
            });
        });

        $("input[name='item_description']").change(function() {
            var item_id = $(this).parents('tr').find("input[name='item_id']").val();
            var item_description = $(this).val();
            $.ajax({
                url: "<?= site_url('sales/changeItemDescription') ?>",
                method: 'post',
                data: {
                    'item_id': item_id,
                    'item_description': item_description,
                },
                dataType: 'json'
            });
        });

        $('#item').focus();

        // Autocomplete com opção "Diversos"
        $('#item').autocomplete({
            source: function(request, response) {
                var term = request.term.trim().toLowerCase();
                
                $.ajax({
                    url: "<?= esc("$controller_name/itemSearch") ?>",
                    dataType: "json",
                    data: { term: term },
                    success: function(data) {
                        if (term === '1' || term === 'diversos') {
                            data.unshift({
                                label: '💰 DIVERSOS',
                                value: '1',
                                item_id: 'DIVERSOS'
                            });
                        }
                        response(data);
                    }
                });
            },
            minChars: 0,
            autoFocus: false,
            delay: 200,
            select: function(a, ui) {
                if (ui.item.item_id === 'DIVERSOS') {
                    showDiversosModal();
                    $(this).val('');
                    return false;
                }
                $(this).val(ui.item.value);
                document.getElementById('add_item_form').submit();
                return false;
            }
        });

        $('#item').on('keydown', function(e) {
            if (e.which == 13) {
                var valor = $(this).val().trim().toLowerCase();
                if (valor === '1' || valor === 'diversos') {
                    e.preventDefault();
                    showDiversosModal();
                    $(this).val('');
                    return false;
                }
                // Se o menu do autocomplete estiver aberto, não submete
                // o formulário com o texto digitado — deixa o autocomplete
                // cuidar da seleção via callback select.
                if ($('#item').autocomplete('widget').is(':visible')) {
                    return false;
                }
                document.getElementById('add_item_form').submit();
                return false;
            }
        });

        var clear_fields = function() {
            if ($(this).val().match("<?= lang(ucfirst($controller_name) . '.start_typing_item_name') . '|' . lang(ucfirst($controller_name) . '.start_typing_customer_name') ?>")) {
                $(this).val('');
            }
        };

        $('#item, #customer').click(clear_fields).dblclick(function(event) {
            $(this).autocomplete('search');
        });

        $('#customer').blur(function() {
            $(this).val("<?= lang(ucfirst($controller_name) . '.start_typing_customer_name') ?>");
        });

        $('#customer').autocomplete({
            source: "<?= site_url('customers/suggest') ?>",
            minChars: 0,
            delay: 10,
            select: function(a, ui) {
                $(this).val(ui.item.value);
                $('#select_customer_form').submit();
                return false;
            }
        });

        $('#customer').keypress(function(e) {
            if (e.which == 13) {
                $('#select_customer_form').submit();
                return false;
            }
        });

        $('.giftcard-input').autocomplete({
            source: "<?= site_url('giftcards/suggest') ?>",
            minChars: 0,
            delay: 10,
            select: function(a, ui) {
                $(this).val(ui.item.value);
                $('#add_payment_form').submit();
                return false;
            }
        });

        $('#comment').keyup(function() {
            $.post("<?= esc(site_url("$controller_name/setComment")) ?>", {
                comment: $('#comment').val()
            });
        });

        <?php if ($config['invoice_enable']) { ?>
            $('#sales_invoice_number').keyup(function() {
                $.post("<?= esc(site_url("$controller_name/setInvoiceNumber")) ?>", {
                    sales_invoice_number: $('#sales_invoice_number').val()
                });
            });

        <?php } ?>

        $('#sales_print_after_sale').change(function() {
            $.post("<?= esc(site_url("$controller_name/setPrintAfterSale")) ?>", {
                sales_print_after_sale: $(this).is(':checked')
            });
        });

        $('#price_work_orders').change(function() {
            $.post("<?= esc(site_url("$controller_name/setPriceWorkOrders")) ?>", {
                price_work_orders: $(this).is(':checked')
            });
        });

        $('#email_receipt').change(function() {
            $.post("<?= esc(site_url("$controller_name/setEmailReceipt")) ?>", {
                email_receipt: $(this).is(':checked')
            });
        });

        $('#finish_sale_button').click(function() {
            $('#buttons_form').attr('action', "<?= "$controller_name/complete" ?>");
            $('#buttons_form').submit();
        });

        $('#finish_invoice_quote_button').click(function() {
            $('#buttons_form').attr('action', "<?= "$controller_name/complete" ?>");
            $('#buttons_form').submit();
        });

        $('#suspend_sale_button').click(function() {
            $('#buttons_form').attr('action', "<?= site_url("$controller_name/suspend") ?>");
            document.getElementById('buttons_form').submit();
        });

        $('#cancel_sale_button').click(function() {
            if (confirm("<?= lang(ucfirst($controller_name) . '.confirm_cancel_sale') ?>")) {
                $('#buttons_form').attr('action', "<?= site_url("$controller_name/cancel") ?>");
                document.getElementById('buttons_form').submit();
            }
        });

        $('#add_payment_button').click(function() {
            $('#add_payment_form').submit();
        });

        $('#payment_types').change(check_payment_type).ready(check_payment_type);

        $('#amount_tendered').keypress(function(event) {
            if (event.which == 13) {
                $('#add_payment_form').submit();
            }
        });

        $('#finish_sale_button').keypress(function(event) {
            if (event.which == 13) {
                $('#finish_sale_form').submit();
            }
        });

        dialog_support.init('a.modal-dlg, button.modal-dlg');

        table_support.handle_submit = function(resource, response, stay_open) {
            $.notify({
                message: response.message
            }, {
                type: response.success ? 'success' : 'danger'
            })

            if (response.success) {
                if (resource.match(/customers$/)) {
                    $('#customer').val(response.id);
                    $('#select_customer_form').submit();
                } else {
                    var $stock_location = $("select[name='stock_location']").val();
                    $('#item_location').val($stock_location);
                    $('#item').val(response.id);
                    if (stay_open) {
                        $('#add_item_form').ajaxSubmit();
                    } else {
                        $('#add_item_form').submit();
                    }
                }
            }
        }

        $('[name="price"],[name="quantity"],[name="discount"],[name="description"],[name="serialnumber"],[name="discounted_total"]').change(function() {
            $(this).parents('tr').prevAll('form:first').submit()
        });

        // Custom discount toggle
        $('.disc-toggle-btn').click(function() {
            var $group = $(this).closest('.disc-toggle');
            var line = $group.data('line');
            var val = $(this).data('val');
            $group.find('.disc-toggle-btn').removeClass('active');
            $(this).addClass('active');
            var input = $('<input>').attr('type', 'hidden').attr('name', 'discount_type').val(val);
            $('#cart_' + line).append(input);
            $('#cart_' + line).submit();
        });
    });

    function check_payment_type() {
        var cash_mode = <?= json_encode($cash_mode) ?>;

        if ($("#payment_types").val() == "<?= lang(ucfirst($controller_name) . '.giftcard') ?>") {
            $("#sale_total").html("<?= to_currency($total) ?>");
            $("#sale_amount_due").html("<?= to_currency($amount_due) ?>");
            $("#amount_tendered_label").html("<?= lang(ucfirst($controller_name) . '.giftcard_number') ?>");
            $("#amount_tendered:enabled").val('').focus();
            $(".giftcard-input").attr('disabled', false);
            $(".non-giftcard-input").attr('disabled', true);
            $(".giftcard-input:enabled").val('').focus();
        } else if (($("#payment_types").val() == "<?= lang(ucfirst($controller_name) . '.cash') ?>" && cash_mode == '1')) {
            $("#sale_total").html("<?= to_currency($non_cash_total) ?>");
            $("#sale_amount_due").html("<?= to_currency($cash_amount_due) ?>");
            $("#amount_tendered_label").html("<?= lang(ucfirst($controller_name) . '.amount_tendered') ?>");
            $("#amount_tendered:enabled").val("<?= to_currency_no_money($cash_amount_due) ?>");
            $(".giftcard-input").attr('disabled', true);
            $(".non-giftcard-input").attr('disabled', false);
        } else {
            $("#sale_total").html("<?= to_currency($non_cash_total) ?>");
            $("#sale_amount_due").html("<?= to_currency($amount_due) ?>");
            $("#amount_tendered_label").html("<?= lang(ucfirst($controller_name) . '.amount_tendered') ?>");
            $("#amount_tendered:enabled").val("<?= to_currency_no_money($amount_due) ?>");
            $(".giftcard-input").attr('disabled', true);
            $(".non-giftcard-input").attr('disabled', false);
        }
    }

    // Add Keyboard Shortcuts/Hotkeys to Sale Register
    document.body.onkeyup = function(e) {
        switch (e.altKey && e.keyCode) {
            case 49: // Alt + 1 Items Seach
                $("#item").focus();
                $("#item").select();
                break;
            case 50: // Alt + 2 Customers Search
                $("#customer").focus();
                $("#customer").select();
                break;
            case 51: // Alt + 3 Suspend Current Sale
                $("#suspend_sale_button").click();
                break;
            case 52: // Alt + 4 Check Suspended
                $("#show_suspended_sales_button").click();
                break;
            case 53: // Alt + 5 Edit Amount Tendered Value
                $("#amount_tendered").focus();
                $("#amount_tendered").select();
                break;
            case 54: // Alt + 6 Add Payment
                $("#add_payment_button").click();
                break;
            case 55: // Alt + 7 Add Payment and Complete Sales/Invoice
                $("#add_payment_button").click();
                window.location.href = "<?= 'sales/complete' ?>";
                break;
            case 56: // Alt + 8 Finish Quote/Invoice without payment
                $("#finish_invoice_quote_button").click();
                break;
            case 57: // Alt + 9 Open Shortcuts Help Modal
                $("#show_keyboard_help").click();
                break;
        }

        switch (e.keyCode) {
            case 27: // ESC Cancel Current Sale
                $("#cancel_sale_button").click();
                break;
        }
    }
</script>

<!-- Modal de Checkout com Múltiplos Pagamentos -->
<style>
.checkout-modal .modal-dialog { width: 520px !important; max-width: 520px !important; }
.checkout-modal .modal-content { border-radius: 12px; }
.checkout-modal .modal-body { padding: 12px 16px !important; }
.checkout-modal .payment-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: center;
    margin: 10px 0;
}
.checkout-modal .payment-btn {
    flex: 1 1 calc(50% - 8px);
    min-width: 130px;
    max-width: 200px;
    padding: 10px 8px !important;
    font-size: 15px !important;
    border-radius: 8px !important;
    margin: 0 !important;
    white-space: nowrap;
}
.checkout-finish-btn { flex-shrink: 0; margin-top: 8px; }
</style>
<div class="modal fade checkout-modal" id="checkoutModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="padding: 8px 15px; background: #5cb85c; color: white;">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" style="text-align:center; margin:0;">
                    <?= !empty($editing_sale_id) ? 'ATUALIZAR VENDA' : 'FINALIZAR VENDA' ?>
                </h4>
            </div>
            <div class="modal-body">
                <div style="display: flex; gap: 8px; margin-bottom: 8px; align-items: stretch;">
                    <div class="checkout-total" style="flex: 1; min-width: 0; min-height: 80px; background: #e8f5e9; padding: 10px; border-radius: 6px; text-align: center; display: flex; flex-direction: column; justify-content: center;">
                        <strong>TOTAL:</strong><br><span id="checkout_total" style="font-size: 24px; color: #2e7d32; font-weight: bold;">R$ 0,00</span>
                    </div>
                    <div class="checkout-restante" style="flex: 1; min-width: 0; min-height: 80px; background: #ffebee; padding: 10px; border-radius: 6px; text-align: center; display: flex; flex-direction: column; justify-content: center;">
                        <strong>RESTANTE:</strong><br><span id="checkout_restante" style="font-size: 24px; color: #c62828; font-weight: bold;">R$ 0,00</span>
                    </div>
                </div>
                
                <div id="payment_summary_list" style="margin: 5px 0;"></div>
                
                <div class="payment-list">
                    <button type="button" class="btn payment-btn cash" id="payment_btn_cash" onclick="selectPayment('cash', '<?= lang("Sales.cash") ?>')">💵 Dinheiro</button>
                    <button type="button" class="btn payment-btn debit" id="payment_btn_debit" onclick="selectPayment('debit', '<?= lang("Sales.debit") ?>')">💳 Débito</button>
                    <button type="button" class="btn payment-btn credit" id="payment_btn_credit" onclick="selectPayment('credit', '<?= lang("Sales.credit") ?>')">💳 Crédito</button>
                    <button type="button" class="btn payment-btn pix" id="payment_btn_pix" onclick="selectPayment('pix', '<?= lang("Sales.pix") ?>')">📱 PIX</button>
                    <button type="button" class="btn payment-btn fiado" id="payment_btn_fiado" onclick="selectPayment('fiado', '<?= lang("Sales.account_receivable") ?>')">📝 Fiado</button>
                </div>
                <div class="form-group" id="amount_group" style="margin: 8px 0; display: none;">
                    <div class="input-group">
                        <span class="input-group-addon" id="payment_type_label">R$</span>
                        <input type="number" class="form-control" id="checkout_amount" step="0.01" min="0" placeholder="0,00" style="text-align: center;">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-primary" id="add_payment_btn" onclick="addPayment()">+</button>
                        </span>
                    </div>
                </div>
                <div class="troco-display" id="troco_display" style="font-size: 14px; padding: 5px; text-align: center; font-weight: bold;"></div>
                <button type="button" class="btn <?= !empty($editing_sale_id) ? 'btn-warning' : 'btn-success' ?> btn-block" id="finish_checkout_btn" onclick="finishCheckout()" disabled>
                    <?= !empty($editing_sale_id) ? 'ATUALIZAR' : 'FINALIZAR' ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Produto Diversos -->
<div class="modal fade" id="diversosModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header" style="background: #FF9800; color: white;">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" style="text-align:center;">💰 PRODUTO DIVERSOS</h4>
            </div>
            <div class="modal-body">
                <p style="text-align:center; margin-bottom: 15px;">Digite o valor do produto:</p>
                <div class="form-group">
                    <label for="diversos_valor">Valor (R$):</label>
                    <input type="number" class="form-control" id="diversos_valor" step="0.01" min="0.01" placeholder="0,00" style="text-align: center; font-size: 24px; height: 60px;">
                </div>
                <div class="form-group">
                    <label for="diversos_qtd">Quantidade:</label>
                    <input type="number" class="form-control" id="diversos_qtd" value="1" min="1" style="text-align: center; font-size: 20px; height: 50px;">
                </div>
                <div class="form-group">
                    <label for="diversos_desc">Descrição (opcional):</label>
                    <input type="text" class="form-control" id="diversos_desc" placeholder="Ex: Café, Lanche..." style="text-align: center;">
                </div>
            </div>
            <div class="modal-footer" style="text-align: center;">
                <button type="button" class="btn btn-default" data-dismiss="modal" style="padding: 10px 30px;">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="addDiversos()" style="padding: 10px 30px; font-size: 18px;">Adicionar</button>
            </div>
        </div>
    </div>
</div>

<script>
var payment_type_selected = '';
var payments_list = [];
var total_venda = 0;

function showDiversosModal() {
    $('#diversos_valor').val('');
    $('#diversos_qtd').val('1');
    $('#diversos_desc').val('');
    $('#diversosModal').off('shown.bs.modal').on('shown.bs.modal', function() {
        $('#diversos_valor').focus();
    }).modal('show');
}

function addDiversos() {
    var valor = $('#diversos_valor').val();
    var quantidade = $('#diversos_qtd').val() || '1';
    
    if (!valor || isNaN(valor) || parseFloat(valor) <= 0) {
        alert('Digite um valor válido');
        return;
    }
    
    $.ajax({
        url: '<?= site_url('sales/addDiversos') ?>',
        type: 'POST',
        data: {
            price: valor,
            quantity: quantidade,
            description: $('#diversos_desc').val() || 'Diversos',
            <?= csrf_token() ?>: '<?= csrf_hash() ?>'
        },
        dataType: 'json',
        success: function(response) {
            $('#diversosModal').modal('hide');
            if (response.success) {
                location.reload();
            } else {
                alert(response.message || 'Erro ao adicionar item');
            }
        },
        error: function() {
            alert('Erro de conexão');
        }
    });
}

// Enter no campo de valor avança para quantidade
$('#diversos_valor').keypress(function(e) {
    if (e.which == 13) {
        e.preventDefault();
        $('#diversos_qtd').focus();
    }
});

$('#diversos_qtd').keypress(function(e) {
    if (e.which == 13) {
        e.preventDefault();
        $('#diversos_desc').focus();
    }
});

$('#diversos_desc').keypress(function(e) {
    if (e.which == 13) {
        e.preventDefault();
        $('#diversosModal .btn-warning').focus();
    }
});

// Enter no botão "Adicionar" do modal
$('#diversosModal .btn-warning').keypress(function(e) {
    if (e.which == 13) {
        e.preventDefault();
        addDiversos();
    }
});

function openCheckoutModal() {
    var span = document.getElementById('sale_total');
    if (!span) { alert('Nao encontrou sale_total'); return; }
    var totalText = span.textContent || '0';
    total_venda = parseFloat(totalText.replace(/[^0-9.,]/g, '').replace(',', '.')) || 0;
    if (total_venda <= 0) { alert('Carrinho vazio'); return; }
    
    payments_list = [];
    payment_type_selected = '';
    
    document.getElementById('checkout_total').textContent = 'R$ ' + total_venda.toFixed(2).replace('.', ',');
    document.getElementById('checkout_restante').textContent = 'R$ ' + total_venda.toFixed(2).replace('.', ',');
    document.getElementById('checkout_amount').value = '';
    document.getElementById('checkout_amount').placeholder = total_venda.toFixed(2).replace('.', ',');
    document.getElementById('troco_display').textContent = ' ';
    document.getElementById('amount_group').style.display = 'none';
    document.getElementById('payment_summary_list').innerHTML = '';
    document.getElementById('finish_checkout_btn').disabled = true;
    
    document.querySelectorAll('.payment-btn').forEach(function(btn) { btn.classList.remove('active'); });
    
    jQuery('#checkoutModal').modal('show');
}

function selectPayment(type, lang_key) {
    payment_type_selected = lang_key;
    document.querySelectorAll('.payment-btn').forEach(function(btn) { btn.classList.remove('active'); });
    var btn = document.getElementById('payment_btn_' + type);
    if (btn) btn.classList.add('active');
    
    if (type === 'cash') {
        document.getElementById('amount_group').style.display = 'block';
        document.getElementById('payment_type_label').textContent = 'R$';
        document.getElementById('checkout_amount').placeholder = total_venda.toFixed(2).replace('.', ',');
    } else {
        document.getElementById('amount_group').style.display = 'block';
        document.getElementById('payment_type_label').textContent = lang_key + ':';
        document.getElementById('checkout_amount').placeholder = getRestante().toFixed(2).replace('.', ',');
    }
    
    setTimeout(function() {
        var amountInput = document.getElementById('checkout_amount');
        if (amountInput) {
            amountInput.focus();
            amountInput.select();
        }
    }, 50);
}

function getRestante() {
    var pago = payments_list.reduce(function(sum, p) { return sum + p.amount; }, 0);
    return Math.max(0, total_venda - pago);
}

function addPayment() {
    var amountInput = document.getElementById('checkout_amount');
    var amount = parseFloat((amountInput ? amountInput.value : '0').replace(',', '.')) || 0;
    var restante = getRestante();
    
    if (amount <= 0) { alert('Informe o valor'); return; }
    if (!payment_type_selected) { alert('Selecione a forma de pagamento'); return; }
    
    // Para não-dinheiro, usar o valor restante se for maior
    if (payment_type_selected !== '<?= lang("Sales.cash") ?>' && amount > restante) {
        amount = restante;
    }
    
    payments_list.push({
        type: payment_type_selected,
        amount: amount
    });
    
    updatePaymentSummary();
    
    document.getElementById('checkout_amount').value = '';
    payment_type_selected = '';
    document.querySelectorAll('.payment-btn').forEach(function(btn) { btn.classList.remove('active'); });
    document.getElementById('amount_group').style.display = 'none';
    
    checkIfCanFinish();
    
    // Se o pagamento cobriu o total
    var novoRestante = getRestante();
    if (novoRestante <= 0.01 && payments_list.length > 0) {
        var lastType = payments_list[payments_list.length - 1].type;
        if (lastType !== '<?= lang("Sales.cash") ?>' && lastType !== '<?= lang("Sales.pix") ?>') {
            finishCheckout();       // Débito/Crédito/Fiado: finaliza direto
            return;
        }
        // Dinheiro/PIX: mostra troco, foco no FINALIZAR pra confirmar
        setTimeout(function() {
            document.getElementById('finish_checkout_btn').focus();
        }, 100);
    }
    
    // Senão, voltar foco pro primeiro botão de pagamento
    var firstBtn = document.querySelector('.payment-btn');
    if (firstBtn) firstBtn.focus();
}

function removePayment(index) {
    payments_list.splice(index, 1);
    updatePaymentSummary();
    checkIfCanFinish();
}

function updatePaymentSummary() {
    var container = document.getElementById('payment_summary_list');
    var html = '';
    var totalPago = 0;
    
    payments_list.forEach(function(p, index) {
        totalPago += p.amount;
        html += '<div class="payment-item" style="display:flex; justify-content:space-between; padding: 5px; background:#e8f5e9; margin-bottom:4px; border-radius:4px;">';
        html += '<span style="flex:1;">' + p.type + ': <strong>R$ ' + p.amount.toFixed(2).replace('.', ',') + '</strong></span>';
        html += '<button type="button" class="btn btn-xs btn-danger" onclick="removePayment(' + index + ')" style="padding:2px 8px;">×</button>';
        html += '</div>';
    });
    
    container.innerHTML = html;
    
    var restante = total_venda - totalPago;
    var restanteSpan = document.getElementById('checkout_restante');
    restanteSpan.textContent = 'R$ ' + Math.max(0, restante).toFixed(2).replace('.', ',');
    restanteSpan.style.color = restante > 0 ? '#f44336' : '#4caf50';
    
    var troco = totalPago - total_venda;
    var trocoDisplay = document.getElementById('troco_display');
    if (troco > 0) {
        trocoDisplay.innerHTML = '<span style="color:#4caf50;">Troco: R$ ' + troco.toFixed(2).replace('.', ',') + '</span>';
    } else {
        trocoDisplay.textContent = ' ';
    }
}

function checkIfCanFinish() {
    var btn = document.getElementById('finish_checkout_btn');
    var restante = getRestante();
    btn.disabled = (restante > 0.01 || payments_list.length === 0);
}

var finishingCheckout = false;

function finishCheckout() {
    if (finishingCheckout) return;
    if (payments_list.length === 0) { alert('Adicione pelo menos um pagamento'); return; }
    
    var restante = getRestante();
    if (restante > 0.01) { alert('Valor insuficiente'); return; }
    
    finishingCheckout = true;
    var btn = document.getElementById('finish_checkout_btn');
    btn.disabled = true;
    btn.textContent = 'FINALIZANDO...';
    
    jQuery('#checkoutModal').modal('hide');
    
    var csrfData = {};
    csrfData['<?= csrf_token() ?>'] = '<?= csrf_hash() ?>';
    
    var paymentsData = JSON.stringify(payments_list);
    
    jQuery.ajax({
        url: '<?= site_url("sales/quickFinish") ?>',
        type: 'POST',
        data: Object.assign(csrfData, {
            payments_json: paymentsData
        }),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                window.location.href = '<?= site_url("sales/receipt/") ?>' + response.sale_id;
            } else {
                alert('Erro: ' + (response.message || 'Falha ao finalizar'));
                location.reload();
            }
        },
        error: function(xhr, status, error) {
            alert('Erro de conexão: ' + error);
            location.reload();
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    var amountInput = document.getElementById('checkout_amount');
    if (amountInput) {
        amountInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addPayment();
            }
        });
    }

    // Enter no botão FINALIZAR
    var finishBtn = document.getElementById('finish_checkout_btn');
    if (finishBtn) {
        finishBtn.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (!this.disabled) finishCheckout();
            }
        });
    }

    // Enter em campos de edição inline no carrinho (preço, qtd, desconto)
    document.querySelectorAll('#cart_contents input').forEach(function(el) {
        el.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var form = el.closest('form');
                if (form) form.submit();
            }
        });
    });

    // Enter no campo de observação
    var commentField = document.getElementById('comment');
    if (commentField) {
        commentField.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                commentField.blur();
            }
        });
    }

    // Foco automático no campo "Valor" ao selecionar forma de pagamento
    document.querySelectorAll('.payment-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            setTimeout(function() {
                var amt = document.getElementById('checkout_amount');
                if (amt) amt.focus();
            }, 50);
        });
    });

    // Keyboard navigation between payment buttons
    var paymentBtns = document.querySelectorAll('.payment-btn');
    paymentBtns.forEach(function(btn, index) {
        btn.setAttribute('tabindex', '0');
        btn.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                e.preventDefault();
                var next = paymentBtns[(index + 1) % paymentBtns.length];
                next.focus();
            } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                e.preventDefault();
                var prev = paymentBtns[(index - 1 + paymentBtns.length) % paymentBtns.length];
                prev.focus();
            } else if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                btn.click();
            }
        });
    });

    // When modal opens, focus first payment button
    jQuery('#checkoutModal').on('shown.bs.modal', function() {
        var firstBtn = document.querySelector('.payment-btn');
        if (firstBtn) firstBtn.focus();
    });

    // Escape in amount input goes back to payment buttons
    if (amountInput) {
        amountInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                var firstBtn = document.querySelector('.payment-btn');
                if (firstBtn) firstBtn.focus();
                payment_type_selected = '';
                document.querySelectorAll('.payment-btn').forEach(function(b) { b.classList.remove('active'); });
                document.getElementById('amount_group').style.display = 'none';
            }
        });
    }

    // Tab navigation: amount input -> finish button -> payment buttons
    if (amountInput) {
        amountInput.addEventListener('keydown', function(e) {
            if (e.key === 'Tab' && !e.shiftKey) {
                e.preventDefault();
                var finishBtn = document.getElementById('finish_checkout_btn');
                if (finishBtn && !finishBtn.disabled) {
                    finishBtn.focus();
                } else {
                    var firstBtn = document.querySelector('.payment-btn');
                    if (firstBtn) firstBtn.focus();
                }
            }
        });
    }

    var finishBtn = document.getElementById('finish_checkout_btn');
    if (finishBtn) {
        finishBtn.setAttribute('tabindex', '0');
        finishBtn.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                e.preventDefault();
                var firstBtn = document.querySelector('.payment-btn');
                if (firstBtn) firstBtn.focus();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                finishCheckout();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'F2') { e.preventDefault(); openCheckoutModal(); }
        if (e.key === 'F8') { e.preventDefault(); showDiversosModal(); }
    });
});
</script>

<?= view('partial/footer') ?>
