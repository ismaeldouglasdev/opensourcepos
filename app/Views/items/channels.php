<?php
/**
 * @var string $controller_name
 * @var string $table_headers
 * @var array $filters
 * @var array $config
 */

use App\Models\Employee;
?>

<?= view('partial/header') ?>
<script>
window._tutorialSteps = [
    {
        target: '.search input',
        title: 'Buscar Produtos',
        text: 'Digite o <strong>nome</strong> ou <strong>código de barras</strong> do produto para filtrar a lista.'
    },
    {
        target: '#filters',
        title: 'Filtros de Canal',
        text: 'Filtre por <strong>anúncios na ML</strong>, <strong>produtos na loja</strong>, itens <strong>fora de sincronia</strong> ou anúncios <strong>pausados / em análise</strong>.'
    },
    {
        target: '#table',
        title: 'Lista de Produtos nos Canais',
        text: 'Cada linha mostra o status do produto no <strong>Mercado Livre</strong>, <strong>Loja Online</strong> e <strong>Shopee</strong>, com a data do último sync.'
    }
];
</script>

<script type="text/javascript">
    $(document).ready(function() {
        $(".search input").focus();

        // When any filter is chosen and the dropdown window is closed
        $('#filters').on('hidden.bs.select', function() {
            table_support.refresh();
        });

        $('#channel_filter_form').on('submit', function(e) {
            e.preventDefault();
            table_support.refresh();
        });

        $('#channel_reset_btn').on('click', function() {
            $('#search').val('');
            $('#filters').selectpicker('deselectAll');
            table_support.refresh();
        });

        <?php
        echo view('partial/bootstrap_tables_locale');
        $employee = model(Employee::class);
        ?>

        var currency_symbol = <?= json_encode($config['currency_symbol'] ?? 'R$') ?>;

        var statusFormatter = function(value) {
            var cls = 'label label-default';
            if (value === 'Ativo' || value === 'Publicado') {
                cls = 'label label-success';
            } else if (value === 'Pausado' || value === 'Oculto') {
                cls = 'label label-warning';
            } else if (value === 'Em Análise') {
                cls = 'label label-info';
            }
            return '<span class="' + cls + '">' + value + '</span>';
        };

        var priceFormatter = function(value) {
            if (value === null || value === undefined || value === '') {
                return '—';
            }
            return currency_symbol + ' ' + Number(value).toFixed(2).replace('.', ',');
        };

        var headers = <?= $table_headers ?>;
        $.each(headers, function(i, col) {
            if (col.field === 'ml_status' || col.field === 'loja_status' || col.field === 'shopee_status') {
                col.formatter = statusFormatter;
                col.escape = false;
            }
            if (col.field === 'ml_price' || col.field === 'loja_price') {
                col.formatter = priceFormatter;
                col.escape = false;
            }
        });

        table_support.init({
            employee_id: <?= $employee->get_logged_in_employee_info()->person_id ?>,
            resource: 'channels',
            headers: headers,
            pageSize: <?= $config['lines_per_page'] ?? 25 ?>,
            uniqueId: 'items.item_id',
            sortName: 'name',
            sortOrder: 'asc',
            queryParams: function() {
                return $.extend(arguments[0], {
                    "search": $("#search").val(),
                    "filters": $("#filters").val()
                });
            }
        });
    });
</script>

<div id="toolbar" style="display:none;"></div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-6">
                        <h4><?= lang('Items.channels') ?></h4>
                    </div>
                    <div class="col-xs-6 text-right">
                        <a href="<?= site_url('items') ?>" class="btn btn-sm btn-default print_hide" role="button">
                            <span class="glyphicon glyphicon-th-large">&nbsp;</span><?= lang('Items.items') ?>
                        </a>
                        <a href="<?= site_url('items/view') ?>" class="btn btn-sm btn-primary print_hide modal-dlg" role="button" data-href="<?= site_url('items/view') ?>" title="<?= lang('Items.new') ?>">
                            <span class="glyphicon glyphicon-plus">&nbsp;</span><?= lang('Items.new') ?>
                        </a>
                    </div>
                </div>
            </div>
            <div class="panel-body">
                <?= form_open('channels', ['id' => 'channel_filter_form', 'class' => 'form-horizontal']) ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group form-group-sm">
                                <label class="control-label" for="search"><?= lang('Items.search') ?></label>
                                <div class="input-group">
                                    <?= form_input(['name' => 'search', 'class' => 'form-control input-sm', 'id' => 'search', 'placeholder' => lang('Items.search_placeholder')]) ?>
                                    <span class="input-group-btn">
                                        <button type="submit" class="btn btn-sm btn-default" id="channel_search_btn">
                                            <span class="glyphicon glyphicon-search">&nbsp;</span><?= lang('Common.search') ?>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-default" id="channel_reset_btn">
                                            <span class="glyphicon glyphicon-refresh">&nbsp;</span><?= lang('Common.reset') ?>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group form-group-sm">
                                <label class="control-label" for="filters"><?= lang('Items.filters') ?></label>
                                <?= form_multiselect('filters[]', $filters, [''], [
                                    'id'                        => 'filters',
                                    'class'                     => 'selectpicker show-menu-arrow',
                                    'data-none-selected-text'   => lang('Common.none_selected_text'),
                                    'data-selected-text-format' => 'count > 1',
                                    'data-style'                => 'btn-default btn-sm',
                                    'data-width'                => 'fit'
                                ]) ?>
                            </div>
                        </div>
                    </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div id="table_holder" class="items-manage">
                <table id="table"></table>
            </div>
        </div>
    </div>
</div>

<?= view('partial/footer') ?>
