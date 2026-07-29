<?php
/**
 * @var string $controller_name
 * @var string $table_headers
 * @var array $filters
 * @var array $selected_filters
 * @var array $config
 */
?>

<?= view("partial/header") ?>

<style>
    #table td,
    #table th {
        border-right: 1px solid #94a3b8 !important;
    }
    #table td:last-child,
    #table th:last-child {
        border-right: none !important;
    }
    #table td:nth-child(4),
    #table td:nth-child(5),
    #table td:nth-child(6) {
        font-family: var(--os-font-mono) !important;
        text-align: right !important;
        font-weight: 600 !important;
    }
    .btn-items {
        background: #f0f4f8;
        border: 1px solid #d0d7de;
        border-radius: 4px;
        padding: 3px 8px;
        font-size: 12px;
        color: #2c3e50;
        transition: all 0.15s ease;
        white-space: nowrap;
    }
    .btn-items:hover {
        background: #dce8f5;
        border-color: #6c8ebf;
        color: #1a3a5c;
    }
    .btn-items:active {
        background: #c5d6eb;
    }

    /* Modal for sale items */
    .sale-items-modal .modal-dialog {
        max-width: 700px;
    }
    .sale-items-modal .modal-body {
        padding: 0;
    }
    .sale-items-modal .table {
        margin-bottom: 0;
    }
    .sale-items-modal .table th {
        background: #f4f6f8;
        border-bottom: 2px solid #dde2e6;
        font-weight: 600;
        font-size: 13px;
        padding: 10px 12px;
    }
    .sale-items-modal .table td {
        padding: 8px 12px;
        font-size: 14px;
        border-bottom: 1px solid #eef1f4;
    }
    .sale-items-modal .table tbody tr:hover {
        background: #f8faff;
    }
    .sale-items-modal .modal-header {
        background: #f4f6f8;
        border-bottom: 1px solid #dde2e6;
        padding: 12px 16px;
    }
    .sale-items-modal .modal-header h4 {
        font-size: 16px;
        font-weight: 600;
        margin: 0;
    }
    .sale-items-modal .modal-footer {
        border-top: 1px solid #eef1f4;
        padding: 10px 16px;
    }
</style>

<script type="text/javascript">
    $(document).ready(function() {
        $("#filters").on("hidden.bs.select", function(e) {
            table_support.refresh();
        });
        <?= view("partial/daterangepicker") ?>
        $("#daterangepicker").on("apply.daterangepicker", function(ev, picker) {
            start_date = picker.startDate.format("YYYY-MM-DD");
            end_date = picker.endDate.format("YYYY-MM-DD");
            table_support.refresh();
            updatePaymentSummary();
        });
        
        function updatePaymentSummary() {
            var filters = $("#filters").val() || [];
            $.ajax({
                url: "<?= site_url('sales/getPaymentSummary') ?>",
                type: "GET",
                data: {
                    start_date: start_date,
                    end_date: end_date,
                    filters: filters
                },
                dataType: 'json',
                success: function(response) {
                    $("#payment_summary").html(response.payment_summary);
                },
                error: function(xhr, status, error) {
                    console.log("Erro ao atualizar resumo:", error);
                }
            });
        }
        <?= view("partial/bootstrap_tables_locale") ?>
        
        table_support.query_params = function() {
            return {
                "start_date": start_date,
                "end_date": end_date,
                "filters": $("#filters").val() || []
            }
        };
        
        table_support.init({
            resource: "<?= esc($controller_name) ?>",
            headers: <?= $table_headers ?>,
            pageSize: <?= $config["lines_per_page"] ?>,
            uniqueId: "sale_id",
            showCheckbox: true,
            clickToSelect: true,
            singleSelect: true,
            queryParamsType: "normal",
            onLoadSuccess: function(response) {
                if ($("#table tbody tr").length > 1) {
                    $("#table tbody tr:last td:first").html("");
                    $("#table tbody tr:last").css("font-weight", "bold");
                }
                if (response.payment_summary) {
                    $("#payment_summary").html(response.payment_summary);
                }
            },
            queryParams: function(params) {
                params.start_date = start_date;
                params.end_date = end_date;
                params.filters = $("#filters").val() || [];
                return params;
            },
            columns: {
                "invoice": {
                    align: "center"
                }
            }
        });
    });

    function openSaleItems(event, saleId) {
        event.stopPropagation();
        var modal = $('#saleItemsModal');
        var tbody = modal.find('#saleItemsBody');
        var saleIdLabel = modal.find('#saleItemsSaleId');

        saleIdLabel.text(saleId);
        tbody.html('<tr><td colspan="5" style="text-align:center;padding:30px;color:#999;">Carregando...</td></tr>');
        modal.modal('show');

        $.ajax({
            url: "<?= site_url('sales/getSaleItems') ?>",
            type: "GET",
            data: { sale_id: saleId },
            dataType: 'json',
            success: function(data) {
                if (data.length > 0) {
                    var html = '';
                    $.each(data, function(i, item) {
                        html += '<tr>';
                        html += '<td>' + item.name + '</td>';
                        html += '<td style="text-align:center;">' + item.quantity_purchased + '</td>';
                        html += '<td style="text-align:right;">' + item.item_unit_price + '</td>';
                        html += '<td style="text-align:right;">' + item.total + '</td>';
                        html += '</tr>';
                    });
                    tbody.html(html);
                } else {
                    tbody.html('<tr><td colspan="4" style="text-align:center;padding:30px;color:#999;">Nenhum item encontrado</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                tbody.html('<tr><td colspan="4" style="text-align:center;padding:30px;color:#c00;">Erro ao carregar: ' + error + '</td></tr>');
            }
        });
    }
    
    function printTakingsReport() {
        window.print();
    }

    function printReceipt() {
        var rows = $('#table').bootstrapTable('getSelections');
        if (rows.length === 0) {
            $.notify({message: 'Selecione uma venda primeiro'}, {type: 'warning'});
            return;
        }
        var sale_id = rows[0].sale_id;
        $.post('<?= site_url('printer/quickPrint') ?>', {sale_id: sale_id}, function(response) {
            $.notify({message: response.message}, {type: response.success ? 'success' : 'danger'});
        }, 'json');
    }
</script>

<?= view("partial/print_receipt", ["print_after_sale" => false, "selected_printer" => "takings_printer"]) ?>

<div id="title_bar" class="print_hide btn-toolbar">
    <button onclick="javascript:printTakingsReport()" class="btn btn-primary btn-sm pull-right" style="margin-right: 5px;">
        <span class="glyphicon glyphicon-print">&nbsp;</span>Imprimir Relatório
    </button>
    <button onclick="javascript:printdoc()" class="btn btn-info btn-sm pull-right" style="margin-right: 5px;">
        <span class="glyphicon glyphicon-print">&nbsp;</span><?= lang("Common.print") ?>
    </button>
    <?= anchor("sales", "<span class=\"glyphicon glyphicon-shopping-cart\">&nbsp;</span>" . lang("Sales.register"), ["class" => "btn btn-info btn-sm pull-right", "id" => "show_sales_button"]) ?>
</div>

<div id="payment_summary">
    <?= $payment_summary ?>
</div>

<div id="toolbar">
    <div class="pull-left form-inline" role="toolbar">
        <button id="delete" class="btn btn-default btn-sm print_hide">
            <span class="glyphicon glyphicon-trash">&nbsp;</span><?= lang("Common.delete") ?>
        </button>
        <button id="print_receipt" class="btn btn-primary btn-sm print_hide" onclick="printReceipt()">
            <span class="glyphicon glyphicon-print">&nbsp;</span>Imprimir Venda
        </button>

        <?= form_input(["name" => "daterangepicker", "class" => "form-control input-sm", "id" => "daterangepicker"]) ?>
        <?= form_hidden("start_date", date("Y-m-d")) ?>
        <?= form_hidden("end_date", date("Y-m-d")) ?>
        <?= form_multiselect("filters[]", $filters, $selected_filters, [
            "id"                        => "filters",
            "data-none-selected-text"   => lang("Common.none_selected_text"),
            "class"                     => "selectpicker show-menu-arrow",
            "data-selected-text-format" => "count > 1",
            "data-style"                => "btn-default btn-sm",
            "data-width"                => "fit"
        ]) ?>
    </div>
</div>

<div id="table_holder">
    <table id="table"></table>
</div>

<div class="modal fade sale-items-modal" id="saleItemsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span>&times;</span></button>
                <h4 class="modal-title">Itens da Venda #<span id="saleItemsSaleId"></span></h4>
            </div>
            <div class="modal-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th style="text-align:center;width:80px;">Qtd</th>
                            <th style="text-align:right;width:120px;">Preço Unit.</th>
                            <th style="text-align:right;width:120px;">Total</th>
                        </tr>
                    </thead>
                    <tbody id="saleItemsBody">
                        <tr><td colspan="4" style="text-align:center;padding:30px;color:#999;">Carregando...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<?= view("partial/footer") ?>
