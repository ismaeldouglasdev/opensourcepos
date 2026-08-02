<?php
/**
 * @var int $sale_id_num
 * @var bool $print_after_sale
 * @var array $config
 */

use App\Models\Employee;

?>

<?= view('partial/header') ?>

<?php
if (isset($error_message)) {
    echo '<div class="alert alert-dismissible alert-danger">' . $error_message . '</div>';
    exit;
}
?>

<?php if (!empty($customer_email)): ?>
    <script type="text/javascript">
        $(document).ready(function() {
            var send_email = function() {
                $.get('<?= site_url() . esc("/sales/sendPdf/$sale_id_num/receipt") ?>',
                    function(response) {
                        $.notify({
                            message: response.message
                        }, {
                            type: response.success ? 'success' : 'danger'
                        })
                    }, 'json'
                );
            };

            $("#show_email_button").click(send_email);

            <?php if (!empty($email_receipt)): ?>
                send_email();
            <?php endif; ?>
        });
    </script>
<?php endif; ?>

<script type="text/javascript">
    $(document).ready(function() {
        var seconds = 5;
        var target = '<?= site_url('sales') ?>';

        var timer = setInterval(function() {
            seconds--;
            if (seconds <= 0) {
                clearInterval(timer);
                window.location.href = target;
            }
            $('#redirect-counter').text(seconds);
        }, 1000);

        $('#cancel-redirect').on('click', function() {
            clearInterval(timer);
            $('#redirect-bar').fadeOut();
        });
    });
</script>

<?= view('partial/print_receipt', ['print_after_sale' => $print_after_sale, 'selected_printer' => 'receipt_printer']) ?>

<div class="print_hide" id="redirect-bar" style="text-align:center; padding:10px; background:#eef2f7; border-radius:6px; margin-bottom:12px; font-size:15px;">
    Redirecionando para nova venda em <strong id="redirect-counter">5</strong>s
    <button id="cancel-redirect" class="btn btn-default btn-xs" style="margin-left:12px;">Cancelar</button>
</div>

<div class="print_hide" id="control_buttons" style="text-align: right;">
    <a href="javascript:void(0);" id="print-receipt-btn">
        <button class="btn btn-primary btn-sm print_hide"><span class="glyphicon glyphicon-print">&nbsp;</span> Imprimir Nota</button>
    </a>
    <script>
        $(document).ready(function() {
            $('#print-receipt-btn').click(function() {
                <?php if (!empty($config['escpos_enabled'])): ?>
                    $.post('<?= site_url('printer/quickPrint') ?>', {
                        sale_id: <?= $sale_id_num ?>
                    }, function(response) {
                        $.notify({message: response.message}, {type: response.success ? 'success' : 'danger'});
                    }, 'json');
                <?php else: ?>
                    window.print();
                <?php endif; ?>
            });
        });
    </script>
    <?php if (!empty($customer_email)): ?>
        <a href="javascript:void(0);">
            <div class="btn btn-info btn-sm" id="show_email_button"><?= '<span class="glyphicon glyphicon-envelope">&nbsp;</span>' . lang('Sales.send_receipt') ?></div>
        </a>
    <?php endif; ?>
    <?= anchor('sales', '<span class="glyphicon glyphicon-shopping-cart">&nbsp;</span>' . lang('Sales.register'), ['class' => 'btn btn-info btn-sm', 'id' => 'show_sales_button']) ?>
    <?php
    $employee = model(Employee::class);
    if ($employee->has_grant('reports_sales', session('person_id'))): ?>
        <?= anchor('sales/manage', '<span class="glyphicon glyphicon-list-alt">&nbsp;</span>' . lang('Sales.takings'), ['class' => 'btn btn-info btn-sm', 'id' => 'show_takings_button']) ?>
    <?php endif; ?>
</div>

<?= view('sales/' . $config['receipt_template']) ?>

<?= view('partial/footer') ?>
