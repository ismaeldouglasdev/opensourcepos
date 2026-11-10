<?php
/**
 * @var array $debtors
 * @var float $total_devido_geral
 */
?>

<?= view('partial/header') ?>

<div id="title_bar" class="print_hide btn-toolbar">
    <h4 style="margin:0;font-weight:700;">📝 Fiado — Lista de Devedores</h4>
</div>

<div class="debtor-summary">
    <div class="debtor-summary-item">
        <span class="debtor-summary-label">Total a Receber</span>
        <span class="debtor-summary-value"><?= to_currency($total_devido_geral) ?></span>
    </div>
</div>

<div id="table_holder">
    <table class="table table-striped table-bordered debtor-table">
        <thead>
            <tr>
                <th style="width:50px;">#</th>
                <th>Cliente</th>
                <th style="width:110px;text-align:center;">Vendas Fiado</th>
                <th style="width:160px;text-align:right;">Total Devido</th>
                <th style="width:120px;text-align:center;">Quitação</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($debtors)): ?>
            <tr>
                <td colspan="5" style="text-align:center;color:var(--os-text-muted);padding:24px;">
                    Nenhum cliente com fiado em aberto.
                </td>
            </tr>
            <?php else: ?>
                <?php foreach ($debtors as $i => $debtor): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td>
                        <a href="<?= site_url('sales/manage?customer=' . (int)$debtor->customer_id) ?>" title="Ver vendas do cliente">
                            <?= esc($debtor->customer_name) ?>
                        </a>
                    </td>
                    <td style="text-align:center;"><?= (int)$debtor->sale_count ?></td>
                    <td style="text-align:right;font-weight:700;color:var(--os-danger);"><?= to_currency($debtor->total_devido) ?></td>
                    <td style="text-align:center;">
                        <button type="button" class="btn btn-xs btn-success fiado-quit-open"
                                data-customer="<?= (int)$debtor->customer_id ?>"
                                data-name="<?= esc($debtor->customer_name, 'attr') ?>">
                            <span class="glyphicon glyphicon-check"></span> QUITAR
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <?php if (!empty($debtors)): ?>
        <tfoot>
            <tr style="font-weight:700;">
                <td colspan="4" style="text-align:right;">Total Geral</td>
                <td style="text-align:right;color:var(--os-danger);"><?= to_currency($total_devido_geral) ?></td>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>
</div>

<div style="margin:14px 0;">
    <button type="button" id="history-toggle" class="btn btn-sm btn-default print_hide">
        <span class="glyphicon glyphicon-time"></span> Histórico de quitações
    </button>
    <div id="history-panel" style="display:none;margin-top:10px;">
        <table class="table table-condensed table-bordered">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Cliente</th>
                    <th>Forma</th>
                    <th style="text-align:right;">Valor</th>
                    <th>Venda</th>
                </tr>
            </thead>
            <tbody id="history-body">
                <tr><td colspan="5" style="text-align:center;padding:14px;">Carregando…</td></tr>
            </tbody>
        </table>
    </div>
</div>

<style>
.debtor-summary { display:flex; gap:14px; flex-wrap:wrap; margin-bottom:16px; }
.debtor-summary-item { flex:1; min-width:220px; max-width:340px; background:var(--os-surface); border:1px solid var(--os-border); border-radius:var(--os-radius-lg); padding:14px 16px; box-shadow:var(--os-shadow-sm); display:flex; flex-direction:column; gap:4px; }
.debtor-summary-label { font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:var(--os-text-muted); }
.debtor-summary-value { font-size:22px; font-weight:700; color:var(--os-danger); }
.debtor-table tbody tr:hover { background:var(--os-surface-muted, #f8fafc); }
</style>

<?= view('partial/footer') ?>

<?= view('partial/settle_modal', ['reload_on_close' => true]) ?>

<script>
(function($) {
    'use strict';

    function moneyFmt(v) {
        var n = parseFloat(v);
        if (!isFinite(n)) { n = 0; }
        return 'R$ ' + n.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function loadHistoryIfOpen() {
        return $('#history-panel').is(':visible');
    }

    function loadHistory() {
        $.getJSON('debtors/history', function(res) {
            var $tbody = $('#history-body').empty();
            var rows = (res && res.history) || [];
            if (!rows.length) {
                $tbody.append('<tr><td colspan="5" style="text-align:center;padding:14px;">Nenhuma quitação registrada ainda.</td></tr>');
                return;
            }
            $.each(rows, function(i, h) {
                var nm = (h.customer_name || '').trim();
                var $tr = $('<tr/>')
                    .append('<td>' + String(h.payment_time).replace(' ', ' · ') + '</td>')
                    .append('<td>' + (nm || '—') + '</td>')
                    .append('<td>' + h.payment_type + '</td>')
                    .append('<td style="text-align:right;font-weight:600;color:var(--os-success);">' + moneyFmt(h.payment_amount) + '</td>')
                    .append('<td>POS #' + h.sale_id + '</td>');
                $tbody.append($tr);
            });
        });
    }

    // Hook usado pelo partial compartilhado settle_modal: atualiza o histórico
    // após cada quitação (somente se o painel estiver aberto).
    window.settleHistoryRefresh = function() {
        if (loadHistoryIfOpen()) { loadHistory(); }
    };

    $('#history-toggle').on('click', function() {
        var $panel = $('#history-panel');
        $panel.toggle();
        if ($panel.is(':visible')) { loadHistory(); }
    });
})(window.jQuery);
</script>
