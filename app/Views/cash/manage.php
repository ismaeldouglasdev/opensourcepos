<?php
/**
 * @var string $controller_name
 * @var string $table_headers
 * @var object $today_summary
 * @var array $config
 */
?>

<?= view('partial/header') ?>

<script type="text/javascript">
    $(document).ready(function() {
        // Load the preset datarange picker
        <?= view('partial/daterangepicker') ?>

        $("#daterangepicker").on('apply.daterangepicker', function(ev, picker) {
            table_support.refresh();
        });

        <?= view('partial/bootstrap_tables_locale') ?>

        table_support.init({
            resource: '<?= esc($controller_name) ?>',
            headers: <?= $table_headers ?>,
            pageSize: <?= $config['lines_per_page'] ?>,
            uniqueId: 'cash_flow_id',
            queryParams: function() {
                return $.extend(arguments[0], {
                    "start_date": start_date,
                    "end_date": end_date
                });
            }
        });
    });
</script>

<div id="title_bar" class="print_hide btn-toolbar">
    <button class="btn btn-info btn-sm pull-right modal-dlg" data-btn-submit="<?= lang('Common.submit') ?>" data-href="<?= "$controller_name/view" ?>" title="<?= lang(ucfirst($controller_name) . '.new') ?>">
        <span class="glyphicon glyphicon-plus">&nbsp;</span><?= lang(ucfirst($controller_name) . '.new') ?>
    </button>
</div>

<div id="toolbar">
    <div class="pull-left form-inline" role="toolbar">
        <button id="delete" class="btn btn-default btn-sm print_hide">
            <span class="glyphicon glyphicon-trash">&nbsp;</span><?= lang('Common.delete') ?>
        </button>
        <?= form_input(['name' => 'daterangepicker', 'class' => 'form-control input-sm', 'id' => 'daterangepicker']) ?>
    </div>
</div>

<div class="cash-summary">
    <div class="cash-summary-item sangria">
        <span class="cash-summary-label"><?= lang('Cash.today_sangria') ?></span>
        <span class="cash-summary-value">- <?= to_currency($today_summary->sangria) ?></span>
    </div>
    <div class="cash-summary-item suprimento">
        <span class="cash-summary-label"><?= lang('Cash.today_suprimento') ?></span>
        <span class="cash-summary-value">+ <?= to_currency($today_summary->suprimento) ?></span>
    </div>
    <div class="cash-summary-item balance">
        <span class="cash-summary-label"><?= lang('Cash.today_balance') ?></span>
        <span class="cash-summary-value"><?= to_currency($today_summary->suprimento - $today_summary->sangria) ?></span>
    </div>
</div>

<div id="table_holder">
    <table id="table"></table>
</div>

<style>
.cash-summary { display:flex; gap:14px; flex-wrap:wrap; margin-bottom:16px; }
.cash-summary-item { flex:1; min-width:180px; background:var(--os-surface); border:1px solid var(--os-border); border-radius:var(--os-radius-lg); padding:14px 16px; box-shadow:var(--os-shadow-sm); display:flex; flex-direction:column; gap:4px; }
.cash-summary-label { font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:var(--os-text-muted); }
.cash-summary-value { font-size:22px; font-weight:700; }
.cash-summary-item.sangria .cash-summary-value { color:var(--os-danger); }
.cash-summary-item.suprimento .cash-summary-value { color:var(--os-success); }
.cash-summary-item.balance .cash-summary-value { color:var(--os-primary); }
</style>

<?= view('partial/footer') ?>
