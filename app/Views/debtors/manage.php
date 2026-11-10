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
                        <button type="button" class="btn btn-xs btn-success btn-quit"
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

<div class="modal fade" id="settleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">&times;</button>
                <h4 class="modal-title">Quitar Fiado — <span id="settle-customer-name"></span></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="settle-method">Forma de pagamento</label>
                    <select id="settle-method" class="form-control input-sm" style="max-width:230px;">
                        <option>Dinheiro</option>
                        <option>Cartão Débito</option>
                        <option>Cartão Crédito</option>
                        <option>PIX</option>
                    </select>
                </div>
                <table class="table table-condensed">
                    <thead>
                        <tr>
                            <th>Venda</th>
                            <th style="text-align:right;">Devido</th>
                            <th style="width:100px;text-align:right;">Pagar</th>
                            <th style="width:95px;"></th>
                        </tr>
                    </thead>
                    <tbody id="settle-debts-body"></tbody>
                </table>
                <div class="settle-total-line">Total a pagar nesta operação: <span id="settle-total-to-pay">R$ 0,00</span></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success" id="settle-all">
                    <span class="glyphicon glyphicon-ok"></span> QUITAR TUDO
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.debtor-summary { display:flex; gap:14px; flex-wrap:wrap; margin-bottom:16px; }
.debtor-summary-item { flex:1; min-width:220px; max-width:340px; background:var(--os-surface); border:1px solid var(--os-border); border-radius:var(--os-radius-lg); padding:14px 16px; box-shadow:var(--os-shadow-sm); display:flex; flex-direction:column; gap:4px; }
.debtor-summary-label { font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:var(--os-text-muted); }
.debtor-summary-value { font-size:22px; font-weight:700; color:var(--os-danger); }
.debtor-table tbody tr:hover { background:var(--os-surface-muted, #f8fafc); }
#settleModal .modal-body { max-height:60vh; overflow-y:auto; }
#settle-debts-body .settle-done-row td { text-align:center; color:var(--os-success); font-weight:600; }
.settle-total-line { text-align:right; font-weight:700; margin-top:6px; }
</style>

<?= view('partial/footer') ?>

<script>
(function($) {
    'use strict';

    var currentCustomerId = 0;
    var settleDirty = false;   // reload the page on modal close only if something was settled

    function moneyFmt(v) {
        var n = parseFloat(v);
        if (!isFinite(n)) { n = 0; }
        return 'R$ ' + n.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

        function normAmt(s) {
            var text = String(s === undefined || s === null ? '' : s).replace(/[R$\s]/g, '').trim();
            if (!text) { return null; }
            if (text.indexOf(',') !== -1) {
                text = text.replace(/\./g, '').replace(',', '.');
            } else if (text.indexOf('.') !== -1) {
                var parts = text.split('.');
                if (!(parts.length === 2 && parts[1].length <= 2)) {
                    text = parts.join('');
                }
            }
            var n = parseFloat(text);
            return (isFinite(n) && n > 0) ? n.toFixed(2) : null;
        }

    function toast(type, message) {
        if ($.notify) {
            $.notify({ message: message }, { type: type, z_index: 100000 });
        } else {
            alert(message);
        }
    }

    function loadDebts() {
        $.getJSON('debtors/debts/' + currentCustomerId, function(res) {
            var rows = res.debts || [];
            var $tbody = $('#settle-debts-body').empty();
            if (!rows.length) {
                $tbody.append('<tr class="settle-done-row"><td colspan="4" style="padding:18px;">✔ Tudo quitado para este cliente.</td></tr>');
                $('#settle-all').prop('disabled', true);
            } else {
                $('#settle-all').prop('disabled', false);
                $.each(rows, function(i, d) {
                    var due = parseFloat(String(d.fiado_outstanding).replace(',', '.'));
                    if (!isFinite(due)) { due = 0; }
                    var $tr = $('<tr/>').attr('data-sale-id', d.sale_id);
                    $tr.append('<td>POS #' + d.sale_id + '<div class="debt-sale-time">' + String(d.sale_time).replace(' ', ' · ') + '</div></td>');
                    $tr.append('<td style="text-align:right;font-weight:600;">' + moneyFmt(due) + '</td>');
                    $tr.append('<td><input type="text" class="form-control input-sm settle-amt" inputmode="decimal" value="' + due.toFixed(2) + '"></td>');
                    $tr.append('<td style="text-align:right;"><button type="button" class="btn btn-xs btn-success btn-settle">QUITAR</button></td>');
                    $tbody.append($tr);
                });
            }
            updateSettleTotal();
        });
    }

    function updateSettleTotal() {
        var $btnAll = $('#settle-all');
        var total = 0;
        $('#settle-debts-body tr[data-sale-id]').each(function() {
            var amt = normAmt($(this).find('.settle-amt').val());
            if (amt) { total += parseFloat(amt); }
        });
        $('#settle-total-to-pay').text(moneyFmt(total));
        $btnAll.prop('disabled', $('#settle-debts-body tr[data-sale-id]').length === 0);
    }

    function settleRow($tr, amountStr) {
        var data = {
            sale_id: $tr.attr('data-sale-id'),
            payment_type: $('#settle-method').val(),
            amount: amountStr
        };
        return $.post('debtors/settle', data)
            .then(function(res) {
                if (res.success) {
                    settleDirty = true;
                    toast('success', res.message + ' (' + moneyFmt(res.paid) + ')');
                    if (loadHistoryIfOpen()) { loadHistory(); }
                    loadDebts();   // re-render: partial settle reduces the outstanding row instead of removing it
                } else {
                    toast('danger', res.message || 'Falha ao quitar.');
                }
                return res.success;
            }, function() {
                toast('danger', 'Erro de rede ao quitar.');
                return false;
            });
    }

    $(document).on('click', '.btn-quit', function() {
        currentCustomerId = parseInt($(this).data('customer'), 10);
        $('#settle-customer-name').text($(this).data('name') || '');
        $('#settle-method').val('Dinheiro');
        $('#settleModal').modal('show');
        loadDebts();
    });

    $(document).on('input change', '#settle-debts-body .settle-amt', updateSettleTotal);
    $(document).on('change', '#settle-method', updateSettleTotal);

    $(document).on('click', '.btn-settle', function() {
        var $tr = $(this).closest('tr');
        var amt = normAmt($tr.find('.settle-amt').val());
        if (!amt) {
            toast('danger', 'Informe um valor válido para pagar.');
            return;
        }
        var $btn = $(this).prop('disabled', true);
        settleRow($tr, amt).always(function() {
            if ($('#settle-debts-body tr[data-sale-id="' + $tr.attr('data-sale-id') + '"]').length) {
                $btn.prop('disabled', false);
            }
        });
    });

    $('#settle-all').on('click', function() {
        var $btn = $(this).prop('disabled', true);
        var rows = $('#settle-debts-body tr[data-sale-id]').toArray();
        if (!rows.length) {
            $btn.prop('disabled', false);
            return;
        }
        var chain = Promise.resolve();
        $.each(rows, function(_, el) {
            var $el = $(el);
            var amt = normAmt($el.find('td:eq(1)').text());
            chain = chain.then(function(prevOk) {
                if (prevOk === false) { return false; }
                var $row = $('#settle-debts-body tr[data-sale-id="' + $el.attr('data-sale-id') + '"]');
                if (!$row.length) { return true; }   // row already gone (all settled)
                return settleRow($row, amt);
            });
        });
        chain.then(function() { $btn.prop('disabled', false); });
    });

    $('#settleModal').on('hidden.bs.modal', function() {
        if (settleDirty) { window.location.reload(); }
    });

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

    $('#history-toggle').on('click', function() {
        var $panel = $('#history-panel');
        $panel.toggle();
        if ($panel.is(':visible')) { loadHistory(); }
    });
})(window.jQuery);
</script>
<style>
.debt-sale-time { font-size:11px; color:#777; }
</style>
