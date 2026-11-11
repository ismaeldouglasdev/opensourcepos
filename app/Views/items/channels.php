<?php
/**
 * @var string $controller_name
 * @var string $table_headers
 * @var array $filters
 * @var array $stock_locations
 * @var int $stock_location
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
        text: 'Digite o <strong>nome</strong>, <strong>código de barras</strong> ou <strong>código</strong> do produto para filtrar a lista.'
    },
    {
        target: '#toolbar',
        title: 'Ações em Lote',
        text: 'Selecione produtos e use <strong>Publicar em Todos</strong>, <strong>Pausar em Todos</strong> ou <strong>Sincronizar Selecionados</strong> para gerenciar múltiplos canais de uma vez.'
    },
    {
        target: '#table',
        title: 'Lista de Produtos nos Canais',
        text: 'Cada linha mostra o status do produto no <strong>Mercado Livre</strong>, <strong>Loja Online</strong> e <strong>Shopee</strong>. Clique nas ações para gerenciar por canal.'
    },
    {
        target: '.modal-dlg',
        title: 'Novo Produto',
        text: 'Clique em <strong>Novo</strong> para cadastrar um produto no OSPOS. Depois use as ações para publicar nos canais.'
    }
];
</script>

<script type="text/javascript">
    $(document).ready(function() {
        $(".search input").focus();

        // When any filter is clicked and the dropdown window is closed
        $('#filters').on('hidden.bs.select', function(e) {
            table_support.refresh();
        });

        // Load the preset daterange picker
        <?= view('partial/daterangepicker') ?>
        // Set the beginning of time as starting date
        $('#daterangepicker').data('daterangepicker').setStartDate("<?= date($config['dateformat'], mktime(0, 0, 0, 01, 01, 2010)) ?>");
        // Update the hidden inputs with the selected dates before submitting the search data
        var start_date = "<?= date('Y-m-d', mktime(0, 0, 0, 01, 01, 2010)) ?>";
        var end_date = "<?= date('Y-m-d') ?>";
        $("#daterangepicker").on('apply.daterangepicker', function(ev, picker) {
            table_support.refresh();
        });

        $("#stock_location").change(function() {
            table_support.refresh();
        });

        // Bulk actions for channels
        $('#publish_all').click(function() {
            var ids = table_support.selected_ids();
            if (ids.length === 0) {
                $.notify({ message: '<?= lang('Items.select_products') ?>' }, { type: 'warning' });
                return;
            }
            if (!confirm('Publicar ' + ids.length + ' produtos em TODOS os canais?')) return;
            channelBulkAction('publish_all', ids);
        });

        $('#pause_all').click(function() {
            var ids = table_support.selected_ids();
            if (ids.length === 0) {
                $.notify({ message: '<?= lang('Items.select_products') ?>' }, { type: 'warning' });
                return;
            }
            if (!confirm('Pausar ' + ids.length + ' produtos em TODOS os canais?')) return;
            channelBulkAction('pause_all', ids);
        });

        $('#sync_selected').click(function() {
            var ids = table_support.selected_ids();
            if (ids.length === 0) {
                $.notify({ message: '<?= lang('Items.select_products') ?>' }, { type: 'warning' });
                return;
            }
            if (!confirm('Sincronizar ' + ids.length + ' produtos selecionados?')) return;
            channelBulkAction('sync_selected', ids);
        });

        // Channel-specific actions
        $(document).on('click', '.channel-publish', function(e) {
            e.stopPropagation();
            var $btn = $(this);
            var itemId = $btn.data('item-id');
            var channel = $btn.data('channel');
            var action = $btn.data('action');
            if (!confirm(($btn.hasClass('btn-success') ? 'Publicar' : 'Pausar') + ' no ' + channel + '?')) return;
            channelSingleAction(itemId, channel, action, $btn);
        });

        <?php
        echo view('partial/bootstrap_tables_locale');
        $employee = model(Employee::class);
        ?>
    });

    // Channel bulk actions
    function channelBulkAction(action, ids) {
        $.post('<?= site_url("items/channelBulkAction") ?>', {
            action: action,
            item_ids: ids
        }, function(data) {
            if (data.success) {
                $.notify({ message: data.message }, { type: 'success' });
                table_support.refresh();
            } else {
                $.notify({ message: data.message || 'Erro na ação em lote' }, { type: 'danger' });
            }
        }).fail(function() {
            $.notify({ message: 'Erro de comunicação com o servidor' }, { type: 'danger' });
        });
    }

    // Channel single action
    function channelSingleAction(itemId, channel, action, $btn) {
        $btn.prop('disabled', true);
        $.post('<?= site_url("items/channelSingleAction") ?>', {
            item_id: itemId,
            channel: channel,
            action: action
        }, function(data) {
            if (data.success) {
                $.notify({ message: data.message }, { type: 'success' });
                table_support.refresh();
            } else {
                $.notify({ message: data.message || 'Erro na ação' }, { type: 'danger' });
                $btn.prop('disabled', false);
            }
        }).fail(function() {
            $.notify({ message: 'Erro de comunicação com o servidor' }, { type: 'danger' });
            $btn.prop('disabled', false);
        });
    }
</script>

<?php if (isset($success)): ?>
    <div class="alert alert-dismissible alert-success">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <?= $success ?>
    </div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
    <div class="alert alert-dismissible alert-danger">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <?= $error_message ?>
    </div>
<?php endif; ?>

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
                <?= form_open('items/channels', ['id' => 'channel_filter_form', 'class' => 'form-horizontal panel panel-default']) ?>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group form-group-sm">
                                <label class="control-label" for="stock_location"><?= lang('Items.stock_location') ?></label>
                                <?= form_dropdown('stock_location', $stock_locations, $stock_location, ['id' => 'stock_location', 'class' => 'form-control selectpicker show-menu-arrow', 'data-width' => 'fit']) ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group form-group-sm">
                                <label class="control-label" for="daterangepicker"><?= lang('Items.date_range') ?></label>
                                <?= form_input(['name' => 'daterangepicker', 'class' => 'form-control input-sm', 'id' => 'daterangepicker']) ?>
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
                    <div class="row">
                        <div class="col-md-12">
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
                    </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>

<div class="row" id="toolbar" style="display:none;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-success print_hide" id="publish_all" title="<?= lang('Items.publish_all') ?>">
                        <span class="glyphicon glyphicon-send">&nbsp;</span><?= lang('Items.publish_all') ?>
                    </button>
                    <button type="button" class="btn btn-sm btn-warning print_hide" id="pause_all" title="<?= lang('Items.pause_all') ?>">
                        <span class="glyphicon glyphicon-pause">&nbsp;</span><?= lang('Items.pause_all') ?>
                    </button>
                    <button type="button" class="btn btn-sm btn-info print_hide" id="sync_selected" title="<?= lang('Items.sync_selected') ?>">
                        <span class="glyphicon glyphicon-refresh">&nbsp;</span><?= lang('Items.sync_selected') ?>
                    </button>
                </div>
                <div class="btn-group pull-right" role="group">
                    <button type="button" class="btn btn-sm btn-default" id="delete_button" disabled>
                        <span class="glyphicon glyphicon-trash">&nbsp;</span><?= lang('Common.delete') ?>
                    </button>
                    <button type="button" class="btn btn-sm btn-default" id="bulk_edit_button" disabled>
                        <span class="glyphicon glyphicon-edit">&nbsp;</span><?= lang('Items.bulk_edit') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div id="table_holder" class="items-manage" style="overflow:auto;">
                <table id="table"
                    data-toggle="table"
                    data-url="<?= site_url('channels/search') ?>"
                    data-query-params="queryParams"
                    data-side-pagination="server"
                    data-pagination="true"
                    data-page-list="[25, 50, 100, 200, All]"
                    data-page-size="25"
                    data-search="false"
                    data-show-columns="true"
                    data-show-refresh="true"
                    data-minimum-count-columns="2"
                    data-click-to-select="true"
                    data-single-select="false"
                    data-toolbar="#toolbar"
                    data-show-export="true"
                    data-export-types="['json', 'xml', 'csv', 'txt', 'sql', 'excel']"
                    data-export-options='{"fileName": "channels-<?= date('Y-m-d') ?>", "ignoreColumn": [0]}'
                    data-maintain-selected="true"
                    data-id-field="item_id"
                    data-response-handler="responseHandler"
                    data-on-check="toggleToolbar"
                    data-on-uncheck="toggleToolbar"
                    data-on-check-all="toggleToolbar"
                    data-on-uncheck-all="toggleToolbar"
                    data-on-load-success="onLoadSuccess"
                    data-row-style="rowStyle"
                    data-format-loading-message="loadingTemplate"
                    data-format-no-matches="noMatchesTemplate"
                    data-icons="icons"
                >
                    <?= $table_headers ?>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Channel-specific toolbar toggle
    function toggleToolbar() {
        var selections = $('#table').bootstrapTable('getSelections');
        var $toolbar = $('#toolbar');
        var $delete = $('#delete_button');
        var $bulk = $('#bulk_edit_button');
        var $publish = $('#publish_all');
        var $pause = $('#pause_all');
        var $sync = $('#sync_selected');

        if (selections.length > 0) {
            $toolbar.show();
            $delete.prop('disabled', false);
            $bulk.prop('disabled', false);
            $publish.prop('disabled', false);
            $pause.prop('disabled', false);
            $sync.prop('disabled', false);
        } else {
            $toolbar.hide();
            $delete.prop('disabled', true);
            $bulk.prop('disabled', true);
            $publish.prop('disabled', true);
            $pause.prop('disabled', true);
            $sync.prop('disabled', true);
        }
    }

    // Query params for bootstrap-table
    function queryParams(params) {
        var formData = $('#channel_filter_form').serializeArray();
        var filters = {};
        formData.forEach(function(field) {
            if (field.name === 'filters[]') {
                filters[field.value] = true;
            } else {
                filters[field.name] = field.value;
            }
        });
        return $.extend({}, params, filters, {
            sort: params.sort,
            order: params.order,
            limit: params.limit,
            offset: params.offset
        });
    }

    // Response handler
    function responseHandler(res) {
        if (res.rows && res.total) {
            return { total: res.total, rows: res.rows };
        }
        return { total: 0, rows: [] };
    }

    // Row styling
    function rowStyle(row, index) {
        var classes = [];
        if (row.ml_status === 'Ativo' || row.loja_status === 'Publicado') {
            classes.push('table-success');
        }
        if (row.ml_status === 'Pausado' || row.ml_status === 'Encerrado') {
            classes.push('table-warning');
        }
        if (row.ml_status === 'Em Análise') {
            classes.push('table-info');
        }
        return { classes: classes.join(' ') };
    }

    function onLoadSuccess(data) {
        toggleToolbar();
    }

    function loadingTemplate() {
        return '<div class="text-center text-muted"><i class="glyphicon glyphicon-refresh glyphicon-spin"></i> <?= lang('Items.ml_loading') ?></div>';
    }

    function noMatchesTemplate() {
        return '<div class="text-center text-muted"><?= lang('Items.ml_no_listings') ?></div>';
    }

    var icons = {
        refresh: 'glyphicon-refresh',
        columns: 'glyphicon-th-list',
        export: 'glyphicon-export'
    };
</script>

<?= view('partial/footer') ?>