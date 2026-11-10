<?php
/**
 * Partial compartilhado: modal de quitação de fiado + CSS + JS.
 *
 * Parâmetros:
 *   - reload_on_close (bool, default false):
 *       true  => ao fechar o modal após alguma quitação, recarrega a página inteira
 *                (usado em /debtors para atualizar a lista de devedores);
 *       false => apenas avisa via window.settleFiadoReloadHook() (usado no register/sales/add,
 *                onde nada do carrinho muda com a quitação).
 *
 * Gatilho: qualquer elemento com a classe CSS `.fiado-quit-open` e atributos
 *   data-customer (int) e data-name (nome do cliente, opcional).
 * Sem data-customer (ou 0) => toast pedindo para selecionar o cliente (e, se existir
 * o campo de busca de cliente `#customer`, foca nele).
 *
 * Hook opcional: window.settleHistoryRefresh() — chamado após cada quitação bem-sucedida
 * (a página de /debtors usa para atualizar o painel de histórico se estiver visível).
 * Hook opcional: window.settleFiadoReloadHook() — chamado no hidden.bs.modal quando
 * reload_on_close=false e houve quitação (fluxo de reload customizado na página hospedeira).
 */
$reload_on_close = $reload_on_close ?? false;
?>
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
#settleModal .modal-body { max-height:60vh; overflow-y:auto; }
#settle-debts-body .settle-done-row td { text-align:center; color:var(--os-success); font-weight:600; }
.settle-total-line { text-align:right; font-weight:700; margin-top:6px; }
.debt-sale-time { font-size:11px; color:#777; }
</style>

<script>
(function($) {
    'use strict';

    var currentCustomerId = 0;
    var settleDirty = false;   // something settled while the modal was open
    var reloadOnClose = <?= $reload_on_close ? 'true' : 'false' ?>;

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
                    if (typeof window.settleHistoryRefresh === 'function') { window.settleHistoryRefresh(); }
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

    $(document).on('click', '.fiado-quit-open', function() {
        var customerId = parseInt($(this).data('customer'), 10);
        if (!customerId) {
            toast('info', 'Selecione o cliente que está pagando a dívida primeiro.');
            var $customer = $('#customer');
            if ($customer.length && $customer.is(':visible')) {
                $customer.focus();
            } else if ($('#add_customer_button').length) {
                if (window.pickerForQuitar !== undefined) { window.pickerForQuitar = true; }
                $('#add_customer_button').click();
            }
            return;
        }
        currentCustomerId = customerId;
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
        if (settleDirty) {
            if (reloadOnClose) {
                window.location.reload();
            } else if (typeof window.settleFiadoReloadHook === 'function') {
                window.settleFiadoReloadHook();
            }
        }
        settleDirty = false;
    });
})(window.jQuery);
</script>
