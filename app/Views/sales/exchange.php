<?php
/**
 * Trocas e Devoluções — busca de venda (sem cupom).
 *
 * Fase 1: apenas localizar a venda e ver os itens como foram cobrados.
 * A devolução em si será uma etapa separada.
 *
 * @var string $start_date
 * @var string $end_date
 */
?>

<?= view('partial/header') ?>

<style>
    #exchange_filters .form-group {
        margin-bottom: 8px;
    }
    #exchange_filters label {
        font-size: 12px;
        margin-bottom: 2px;
        color: #475569;
    }
    #exchange_results tbody tr {
        cursor: pointer;
    }
    #exchange_results tbody tr.selected {
        background: #d1fae5 !important;
    }
    #exchange_results tbody tr:hover {
        background: #ecfdf5;
    }
    #exchange_detail .sale-head {
        display: flex;
        flex-wrap: wrap;
        gap: 18px;
        margin-bottom: 10px;
        font-size: 14px;
    }
    #exchange_detail .sale-head strong {
        display: block;
        font-size: 11px;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .4px;
    }
    #exchange_detail table td,
    #exchange_detail table th {
        font-size: 13px;
    }
    .exchange-empty {
        text-align: center;
        padding: 26px;
        color: #94a3b8;
    }
</style>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <span class="glyphicon glyphicon-transfer"></span>
                        Trocas e Devoluções
                    </h4>
                </div>
                <div class="panel-body">
                    <p class="text-muted" style="margin-bottom:12px;">
                        Localize a venda original pelo cliente, produto, valor ou número. Não é necessário o cupom.
                    </p>

                    <form id="exchange_filters" class="row" onsubmit="return false;">
                        <div class="col-sm-2 form-group">
                            <label for="ex_start_date">De</label>
                            <input type="date" class="form-control input-sm" id="ex_start_date" value="<?= esc($start_date) ?>">
                        </div>
                        <div class="col-sm-2 form-group">
                            <label for="ex_end_date">Até</label>
                            <input type="date" class="form-control input-sm" id="ex_end_date" value="<?= esc($end_date) ?>">
                        </div>
                        <div class="col-sm-3 form-group">
                            <label for="ex_customer">Cliente</label>
                            <input type="text" class="form-control input-sm" id="ex_customer" placeholder="nome do cliente">
                        </div>
                        <div class="col-sm-3 form-group">
                            <label for="ex_product">Produto</label>
                            <input type="text" class="form-control input-sm" id="ex_product" placeholder="nome ou código do produto">
                        </div>
                        <div class="col-sm-2 form-group">
                            <label for="ex_sale_ref">Nº da venda / nota</label>
                            <input type="text" class="form-control input-sm" id="ex_sale_ref" placeholder="ex.: 7621" inputmode="numeric">
                        </div>
                        <div class="col-sm-2 form-group">
                            <label for="ex_value_min">Valor de (R$)</label>
                            <input type="text" class="form-control input-sm" id="ex_value_min" placeholder="0,00" inputmode="decimal">
                        </div>
                        <div class="col-sm-2 form-group">
                            <label for="ex_value_max">Valor até (R$)</label>
                            <input type="text" class="form-control input-sm" id="ex_value_max" placeholder="0,00" inputmode="decimal">
                        </div>
                        <div class="col-sm-12 form-group" style="margin-top:6px;">
                            <button type="button" class="btn btn-primary btn-sm" id="ex_search_btn">
                                <span class="glyphicon glyphicon-search"></span> Buscar
                            </button>
                            <button type="button" class="btn btn-default btn-sm" id="ex_clear_btn">
                                Limpar
                            </button>
                            <span id="ex_status" class="text-muted" style="margin-left:10px;font-size:13px;"></span>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped table-condensed" id="exchange_results">
                            <thead>
                                <tr>
                                    <th style="width:70px;">Venda</th>
                                    <th style="width:135px;">Data</th>
                                    <th>Cliente</th>
                                    <th style="width:90px;">Itens</th>
                                    <th style="width:170px;">Pagamento</th>
                                    <th style="width:110px;text-align:right;">Total pago</th>
                                    <th style="width:90px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td colspan="7" class="exchange-empty">Informe um filtro e clique em Buscar.</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-right">
                        <button type="button" class="btn btn-default btn-xs" id="ex_prev" disabled>&laquo; Anterior</button>
                        <span id="ex_page_info" class="text-muted" style="font-size:12px;margin:0 8px;"></span>
                        <button type="button" class="btn btn-default btn-xs" id="ex_next" disabled>Próxima &raquo;</button>
                    </div>
                </div>
            </div>

            <div class="panel panel-default" id="exchange_detail" style="display:none;">
                <div class="panel-heading">
                    <h4 class="panel-title">Itens da venda</h4>
                </div>
                <div class="panel-body">
                    <div class="sale-head" id="ex_sale_head"></div>
                    <div class="table-responsive">
                        <table class="table table-condensed table-bordered">
                            <thead>
                                <tr>
                                    <th style="width:40px;text-align:center;">
                                        <input type="checkbox" id="ex_select_all" title="Selecionar todos">
                                    </th>
                                    <th>Produto</th>
                                    <th style="width:110px;">Código</th>
                                    <th style="width:70px;text-align:right;">Qtd orig.</th>
                                    <th style="width:90px;text-align:right;">Devolver</th>
                                    <th style="width:95px;text-align:right;">Preço</th>
                                    <th style="width:95px;text-align:right;">Desc.</th>
                                    <th style="width:105px;text-align:right;">Total</th>
                                </tr>
                            </thead>
                            <tbody id="ex_items_body"></tbody>
                        </table>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-condensed">
                            <thead>
                                <tr>
                                    <th>Forma de pagamento</th>
                                    <th style="width:120px;text-align:right;">Valor</th>
                                </tr>
                            </thead>
                            <tbody id="ex_payments_body"></tbody>
                        </table>
                    </div>
                    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-top:10px;">
                        <button type="button" class="btn btn-warning btn-sm" id="ex_return_btn" disabled>
                            <span class="glyphicon glyphicon-transfer"></span> Devolver selecionados
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" id="ex_exchange_btn" disabled>
                            <span class="glyphicon glyphicon-refresh"></span> Trocar por outros itens
                        </button>
                        <span class="text-muted" style="font-size:12px;">
                            Marque os itens e ajuste a quantidade a devolver. Itens não marcados não entram na devolução.
                            <strong>Devolver</strong> reembolsa o valor; <strong>Trocar</strong> abre o carrinho com os itens
                            devolvidos e você adiciona os novos — só a diferença é paga ou devolvida.
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    var baseUrl = '<?= site_url('sales') ?>';
    var limit = 25;
    var offset = 0;
    var total = 0;
    var selectedSaleId = null;

    function escHtml(value) {
        return String(value === null || value === undefined ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function money(value) {
        return 'R$ ' + escHtml(value);
    }

    function filters() {
        return {
            start_date: $('#ex_start_date').val(),
            end_date: $('#ex_end_date').val(),
            customer: $('#ex_customer').val(),
            product: $('#ex_product').val(),
            value_min: $('#ex_value_min').val(),
            value_max: $('#ex_value_max').val(),
            sale_ref: $('#ex_sale_ref').val()
        };
    }

    function setStatus(text) {
        $('#ex_status').text(text);
    }

    function renderRows(rows) {
        var $body = $('#exchange_results tbody');

        if (!rows.length) {
            $body.html('<tr><td colspan="7" class="exchange-empty">Nenhuma venda encontrada com esses filtros.</td></tr>');
            return;
        }

        var html = '';
        rows.forEach(function (row) {
            var items = row.line_count + ' linha(s) / ' + parseFloat(row.units) + ' un.';
            html += '<tr data-sale-id="' + escHtml(row.sale_id) + '">'
                + '<td><strong>' + escHtml(row.sale_id) + '</strong></td>'
                + '<td>' + escHtml(row.sale_time_display) + '</td>'
                + '<td>' + (escHtml(row.customer_name) || '<span class="text-muted">—</span>') + '</td>'
                + '<td>' + escHtml(items) + '</td>'
                + '<td>' + (escHtml(row.payment_types) || '<span class="text-muted">—</span>') + '</td>'
                + '<td style="text-align:right;font-family:monospace;">' + money(row.total_paid_display) + '</td>'
                + '<td><button type="button" class="btn btn-info btn-xs ex-view">Ver itens</button></td>'
                + '</tr>';
        });

        $body.html(html);
    }

    function updatePager() {
        var from = total === 0 ? 0 : offset + 1;
        var to = Math.min(offset + limit, total);
        $('#ex_page_info').text(from + '–' + to + ' de ' + total);
        $('#ex_prev').prop('disabled', offset <= 0);
        $('#ex_next').prop('disabled', offset + limit >= total);
    }

    function search(resetOffset) {
        if (resetOffset) {
            offset = 0;
        }

        setStatus('Buscando...');
        $('#ex_search_btn').prop('disabled', true);
        $('#exchange_detail').hide();
        selectedSaleId = null;

        var params = filters();
        params.limit = limit;
        params.offset = offset;

        $.ajax({
            url: baseUrl + '/exchangeSearch',
            data: params,
            dataType: 'json',
            cache: false
        }).done(function (response) {
            total = parseInt(response.total, 10) || 0;
            renderRows(response.rows || []);
            updatePager();
            setStatus(total + ' venda(s) encontrada(s).');
        }).fail(function (xhr) {
            $('#exchange_results tbody').html('<tr><td colspan="7" class="exchange-empty">Erro ao buscar vendas (' + xhr.status + ').</td></tr>');
            setStatus('');
        }).always(function () {
            $('#ex_search_btn').prop('disabled', false);
        });
    }

    function showDetail(saleId) {
        selectedSaleId = saleId;
        $('#exchange_results tbody tr').removeClass('selected');
        $('#exchange_results tbody tr[data-sale-id="' + saleId + '"]').addClass('selected');

        $.ajax({
            url: baseUrl + '/exchangeSale/' + saleId,
            dataType: 'json',
            cache: false
        }).done(function (response) {
            var sale = response.sale || {};

            $('#ex_sale_head').html(
                '<div><strong>Venda</strong>#' + escHtml(sale.sale_id) + '</div>'
                + '<div><strong>Data</strong>' + escHtml(sale.sale_time_display) + '</div>'
                + '<div><strong>Nota</strong>' + (escHtml(sale.invoice_number) || '—') + '</div>'
                + '<div><strong>Total pago</strong>' + money(response.payments_total_display) + '</div>'
            );

            var itemsHtml = '';
            (response.items || []).forEach(function (item) {
                var discount = (item.discount_type === 1 || item.discount_type === '1')
                    ? money(item.discount)
                    : escHtml(item.discount) + '%';
                var q = parseFloat(item.quantity_purchased);
                var absQ = Math.abs(q);
                itemsHtml += '<tr>'
                    + '<td style="text-align:center;"><input type="checkbox" class="ex-item-check" data-item-id="' + escHtml(item.item_id) + '" data-max="' + absQ + '"></td>'
                    + '<td>' + escHtml(item.item_name) + '</td>'
                    + '<td>' + (escHtml(item.item_number) || '—') + '</td>'
                    + '<td style="text-align:right;">' + absQ + '</td>'
                    + '<td style="text-align:right;"><input type="number" class="form-control input-sm ex-item-qty" data-item-id="' + escHtml(item.item_id) + '" value="' + absQ + '" min="0.001" step="0.001" style="width:80px;display:inline-block;text-align:right;" disabled></td>'
                    + '<td style="text-align:right;">' + money(item.unit_price_display) + '</td>'
                    + '<td style="text-align:right;">' + discount + '</td>'
                    + '<td style="text-align:right;font-family:monospace;">' + money(item.line_total_display) + '</td>'
                    + '</tr>';
            });
            $('#ex_items_body').html(itemsHtml || '<tr><td colspan="6" class="exchange-empty">Sem itens registrados.</td></tr>');

            var paymentsHtml = '';
            (response.payments || []).forEach(function (payment) {
                paymentsHtml += '<tr>'
                    + '<td>' + escHtml(payment.payment_type) + '</td>'
                    + '<td style="text-align:right;font-family:monospace;">' + money(payment.net_display) + '</td>'
                    + '</tr>';
            });
            $('#ex_payments_body').html(paymentsHtml || '<tr><td colspan="2" class="exchange-empty">Sem pagamentos registrados.</td></tr>');

            $('#exchange_detail').show();
        }).fail(function (xhr) {
            alert('Não foi possível carregar a venda ' + saleId + ' (' + xhr.status + ').');
        });
    }

    $('#ex_search_btn').on('click', function () {
        search(true);
    });

    $('#exchange_filters input').on('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            search(true);
        }
    });

    $('#ex_clear_btn').on('click', function () {
        $('#ex_customer, #ex_product, #ex_value_min, #ex_value_max, #ex_sale_ref').val('');
        $('#exchange_results tbody').html('<tr><td colspan="7" class="exchange-empty">Informe um filtro e clique em Buscar.</td></tr>');
        $('#exchange_detail').hide();
        setStatus('');
        total = 0;
        offset = 0;
        updatePager();
    });

    $('#ex_prev').on('click', function () {
        offset = Math.max(0, offset - limit);
        search(false);
    });

    $('#ex_next').on('click', function () {
        offset = offset + limit;
        search(false);
    });

    $('#exchange_results').on('click', 'tr[data-sale-id]', function (event) {
        event.preventDefault();
        showDetail($(this).data('sale-id'));
    });

    // Enable/disable qty input based on checkbox
    $('#ex_items_body').on('change', '.ex-item-check', function () {
        var $cb = $(this);
        var id = $cb.data('item-id');
        var $qty = $('.ex-item-qty[data-item-id="' + id + '"]');
        $qty.prop('disabled', !$cb.is(':checked'));
        updateReturnButton();
    });

    $('#ex_items_body').on('input', '.ex-item-qty', function () {
        updateReturnButton();
    });

    $('#ex_select_all').on('change', function () {
        var checked = $(this).is(':checked');
        $('.ex-item-check').prop('checked', checked).trigger('change');
    });

    function updateReturnButton() {
        var any = $('.ex-item-check:checked').length > 0;
        $('#ex_return_btn, #ex_exchange_btn').prop('disabled', !any);
    }

    function submitSelected(mode) {
        if (!selectedSaleId) return;
        var selected = {};
        $('.ex-item-check:checked').each(function () {
            var id = $(this).data('item-id');
            var qty = parseFloat($('.ex-item-qty[data-item-id="' + id + '"]').val());
            if (!isNaN(qty) && qty > 0) {
                selected[id] = qty;
            }
        });
        if (Object.keys(selected).length === 0) {
            alert('Selecione ao menos um item.');
            return;
        }

        var $form = $('<form method="POST" action="' + baseUrl + '/exchangeReturn"></form>');
        $form.append('<input type="hidden" name="sale_id" value="' + selectedSaleId + '">');
        $form.append('<input type="hidden" name="mode" value="' + mode + '">');
        $form.append('<input type="hidden" name="selected" value="' + JSON.stringify(selected).replace(/"/g, '&quot;') + '">');
        $('body').append($form);
        $form.submit();
    }

    $('#ex_return_btn').on('click', function () {
        submitSelected('return');
    });

    $('#ex_exchange_btn').on('click', function () {
        submitSelected('exchange');
    });

    updatePager();
    search(true);
})();
</script>

<?= view('partial/footer') ?>
