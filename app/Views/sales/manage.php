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
        });
        <?= view("partial/bootstrap_tables_locale") ?>
        
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
