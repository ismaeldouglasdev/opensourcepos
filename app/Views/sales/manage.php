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
    .detail-table {
        margin: 10px;
        background: #fff;
    }
    .detail-row {
        background: #f9f9f9;
    }
    .detail-row td {
        padding: 10px !important;
    }
    .expand-btn {
        cursor: pointer;
        color: #337ab7;
        font-size: 14px;
    }
    .expand-btn:hover {
        color: #23527c;
    }
    #report_summary {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 10px !important;
        justify-content: center !important;
        padding: 15px 0 !important;
    }
    .summary_row {
        flex: 1 1 140px !important;
        max-width: 200px !important;
        min-width: 120px !important;
    }
    @media (max-width: 768px) {
        .summary_row {
            flex: 1 1 45% !important;
            max-width: none !important;
            min-width: 100px !important;
            padding: 8px 12px !important;
        }
        #report_summary {
            gap: 8px !important;
            padding: 10px 5px !important;
        }
    }
    @media (max-width: 480px) {
        .summary_row {
            flex: 1 1 100% !important;
            max-width: 100% !important;
        }
        #payment_summary {
            font-size: 22px !important;
        }
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
        
        $(document).on('click', '.expand-btn', function() {
            var btn = $(this);
            var sale_id = btn.data('sale_id');
            var row = btn.closest('tr');
            var nextRow = row.next();
            
            if (nextRow.hasClass('detail-row')) {
                nextRow.remove();
                btn.removeClass('glyphicon-minus').addClass('glyphicon-plus');
            } else {
                btn.removeClass('glyphicon-plus').addClass('glyphicon-minus');
                loadSaleItems(sale_id, row);
            }
        });
        
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
                $("#table tbody tr[data-uniqueid]").each(function() {
                    var sale_id = $(this).data('uniqueid');
                    var firstCell = $(this).find('td:first');
                    if (sale_id && sale_id !== '-' && !$(this).find('.expand-btn').length) {
                        firstCell.prepend('<span class="expand-btn glyphicon glyphicon-plus" data-sale_id="' + sale_id + '"></span> ');
                    }
                });
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

    function loadSaleItems(sale_id, row) {
        $.ajax({
            url: "<?= site_url('sales/getSaleItems') ?>",
            type: "GET",
            data: { sale_id: sale_id },
            dataType: 'json',
            success: function(data) {
                var html = '<tr class="detail-row"><td colspan="9">';
                html += '<table class="table table-bordered detail-table" style="margin:10px; width:auto;">';
                html += '<thead><tr><th>Produto</th><th style="text-align:center;">Qtd</th><th style="text-align:right;">Preço Unit.</th><th style="text-align:right;">Total</th></tr></thead>';
                html += '<tbody>';
                
                if (data.length > 0) {
                    $.each(data, function(i, item) {
                        html += '<tr>';
                        html += '<td>' + item.name + '</td>';
                        html += '<td style="text-align:center;">' + item.quantity_purchased + '</td>';
                        html += '<td style="text-align:right;">' + item.item_unit_price + '</td>';
                        html += '<td style="text-align:right;">' + item.total + '</td>';
                        html += '</tr>';
                    });
                } else {
                    html += '<tr><td colspan="4" class="text-muted">Sem itens</td></tr>';
                }
                
                html += '</tbody></table></td></tr>';
                row.after(html);
            },
            error: function(xhr, status, error) {
                console.log("Erro:", error, xhr.responseText);
                row.after('<tr class="detail-row"><td colspan="9"><div class="text-danger" style="padding:10px;">Erro ao carregar: ' + error + '</div></td></tr>');
            }
        });
    }
    
    function printTakingsReport() {
        window.print();
    }

    function reprintThermal() {
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

<div id="payment_summary" style="font-size: 28px; margin-bottom: 20px; text-align: center; background: #f5f5f5; padding: 15px; border-radius: 5px;">
    <?= $payment_summary ?>
</div>

<div id="toolbar">
    <div class="pull-left form-inline" role="toolbar">
        <button id="delete" class="btn btn-default btn-sm print_hide">
            <span class="glyphicon glyphicon-trash">&nbsp;</span><?= lang("Common.delete") ?>
        </button>
        <button id="reprint_thermal" class="btn btn-primary btn-sm print_hide" onclick="reprintThermal()">
            <span class="glyphicon glyphicon-print">&nbsp;</span>Reimprimir Térmica
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

<?= view("partial/footer") ?>
