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
        text: 'Use <strong>Excluir</strong> ou <strong>Edição em lote</strong> para alterar vários produtos de uma vez.'
    },
    {
        target: '#table',
        title: 'Lista de Produtos',
        text: 'Clique em um produto para <strong>editar</strong>. Marque a caixa para selecionar.'
    },
    {
        target: '.modal-dlg',
        title: 'Novo Produto',
        text: 'Clique em <strong>Novo</strong> para cadastrar um produto. Use "Diversos" na venda para itens avulsos.'
    }
];
</script>

<script type="text/javascript">
    $(document).ready(function() {
        $('#generate_barcodes').click(function() {
            window.open(
                'index.php/items/generateBarcodes/' + table_support.selected_ids().join(':'),
                '_blank'
            );
        });

        $(".search input").focus();

        // When any filter is clicked and the dropdown window is closed
        $('#filters').on('hidden.bs.select', function(e) {
            table_support.refresh();
        });

        $(".search input").focus();

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

        $(".search input").focus();

        $("#stock_location").change(function() {
            table_support.refresh();
        });

        $(".search input").focus();

        <?php
        echo view('partial/bootstrap_tables_locale');
        $employee = model(Employee::class);
        ?>

        table_support.init({
            employee_id: <?= $employee->get_logged_in_employee_info()->person_id ?>,
            resource: '<?= esc($controller_name) ?>',
            headers: <?= $table_headers ?>,
            pageSize: <?= $config['lines_per_page'] ?>,
            uniqueId: 'items.item_id',
            sortName: 'last_modified',
            sortOrder: 'desc',
            queryParams: function() {
                return $.extend(arguments[0], {
                    "start_date": start_date,
                    "end_date": end_date,
                    "stock_location": $("#stock_location").val(),
                    "filters": $("#filters").val()
                });

        $(".search input").focus();
            },
            onLoadSuccess: function(response) {
                $('a.rollover').imgPreview({
                    imgCSS: {
                        width: 200
                    },
                    distanceFromCursor: {
                        top: 8,
                        left: 8
                    }
                })
            }
        });

        $(".search input").focus();
    });

    // Open the item edit modal directly when arriving with ?edit_item={id}
    $(function() {
        var edit_item = new URLSearchParams(window.location.search).get('edit_item');
        if (edit_item && /^\d+$/.test(edit_item)) {
            $('<a/>', {
                id: 'edit-from-home',
                class: 'modal-dlg modal-dlg-1000',
                href: <?= json_encode(esc("$controller_name/view/")) ?> + edit_item,
                title: <?= json_encode(lang(ucfirst($controller_name) . '.update')) ?>,
                'data-btn-submit': <?= json_encode(lang('Common.submit')) ?>,
                style: 'display:none'
            }).appendTo('body');
            dialog_support.init('#edit-from-home');
            $('#edit-from-home').trigger('click');
            history.replaceState(null, '', <?= json_encode(esc("$controller_name")) ?>);
        }
    });

    // ── Scan de código de barras na busca da grid ──
    // Escaneia na busca: produto existente → abre o modal de edição;
    // inexistente → abre o cadastro novo com o código preenchido e a
    // categoria UTILIDADES já selecionada.
    // Assinatura de scanner (mesma do register): todos os gaps entre chars ≤ 120ms.
    // Delegado em document: o .search input só existe depois do bootstrap-table init.
    (function() {
        var charTimes = [];
        $(document).on('input', '.search input', function() {
            var now = Date.now();
            var val = $(this).val() || '';
            if (val.length <= charTimes.length) {
                // deleção/paste: marca como humano
                charTimes = [];
                for (var i = 0; i < val.length; i++) charTimes.push(now + i * 10000);
            } else {
                while (charTimes.length < val.length) charTimes.push(now);
            }
        });

        $(document).on('keydown', '.search input', function(e) {
            if (e.key !== 'Enter' && e.keyCode !== 13) return;

            var code = ($(this).val() || '').trim();
            var isScan = false;
            if (code.length >= 4 && /^[0-9A-Za-z]+$/.test(code) && charTimes.length >= code.length) {
                isScan = true;
                for (var i = 1; i < code.length; i++) {
                    if (charTimes[i] - charTimes[i - 1] > 120) { isScan = false; break; }
                }
            }
            charTimes = [];
            if (!isScan) return; // busca normal da tabela

            e.preventDefault();
            e.stopPropagation();

            var $el = $(this);
            $el.val('');
            $('#table').bootstrapTable('resetSearch', '');

            $.getJSON('<?= site_url('items/scanLookup') ?>/' + encodeURIComponent(code))
                .done(function(r) {
                    var id = 'scan-open-item';
                    $('#' + id).remove();
                    var href, title;
                    if (r.found && r.item_id) {
                        href = <?= json_encode(esc("$controller_name/view/")) ?> + r.item_id;
                        title = <?= json_encode(lang(ucfirst($controller_name) . '.update')) ?>;
                    } else {
                        href = <?= json_encode(esc("$controller_name/view")) ?> + '?item_number=' + encodeURIComponent(code) + '&category=UTILIDADES';
                        title = <?= json_encode(lang(ucfirst($controller_name) . '.new')) ?>;
                    }
                    $('<a/>', {
                        id: id,
                        class: 'modal-dlg modal-dlg-1000',
                        href: href,
                        title: title,
                        'data-btn-submit': <?= json_encode(lang('Common.submit')) ?>,
                        style: 'display:none'
                    }).appendTo('body');
                    dialog_support.init('#' + id);
                    $('#' + id).trigger('click');
                })
                .fail(function() {
                    $el.val(code); // devolve o código se o lookup falhar
                });
        });
    })();

    // ── Inline editing on double-click ──
    (function() {
        var INLINE_FIELDS = {
            name:        { type: 'text',  css: 'items-col-name' },
            item_number: { type: 'text',  css: 'items-col-mono' },
            category:    { type: 'text',  css: 'items-col-wrap' },
            cost_price:  { type: 'money', css: 'items-col-num' },
            unit_price:  { type: 'money', css: 'items-col-num' },
            quantity:    { type: 'qty',   css: 'items-col-qty' }
        };
        // field -> column index, filled by markInlineCells() from the thead
        var FIELD_INDEX = {};

        // Link each body cell to its column field by index (bootstrap-table
        // does not put data-field on <td>, only on <th>). Call after every load.
        window.markInlineCells = function() {
            var $ths = $('#table_holder.items-manage thead th');
            FIELD_INDEX = {};
            $ths.each(function(i, th) {
                var f = $(th).attr('data-field');
                if (f && INLINE_FIELDS[f]) {
                    FIELD_INDEX[f] = i;
                }
            });
            // tag cells so CSS/JS can address them generically
            $('#table_holder.items-manage td').removeClass('editable-cell');
            $('#table_holder.items-manage tbody tr').each(function() {
                var $tds = $(this).children('td');
                for (var f in FIELD_INDEX) {
                    var $c = $tds.eq(FIELD_INDEX[f]);
                    if ($c.length) $c.addClass('editable-cell').attr('data-efield', f);
                }
            });
        };

        $(document).on('dblclick', '#table_holder.items-manage td.editable-cell', function() {
            var $td = $(this);
            var field = $td.attr('data-efield');
            if (!INLINE_FIELDS[field]) return;
            if ($td.find('input, select').length) return;

            var $tr = $td.closest('tr');
            var itemId = $tr.data('uniqueid');
            if (!itemId) return;

            // clickToSelect toggles the row checkbox on each click of the
            // double-click (bootstrap-table checks the td), which would leave
            // the caixinha checked/parity-blocked. Deselect the row so the
            // edit always opens and the checkbox is never left marked.
            var rowIdx = $tr.data('index');
            if (rowIdx !== undefined && $.fn.bootstrapTable) {
                $('#table').bootstrapTable('uncheck', rowIdx);
            }

            var raw = $.trim($td.text());
            var cfg = INLINE_FIELDS[field];
            var inputmode = (cfg.type === 'money' || cfg.type === 'qty') ? 'decimal' : '';
            var inputClass = 'inline-edit-input inline-edit-' + cfg.type;

            var $input = $('<input>', {
                type: 'text',
                inputmode: inputmode,
                'class': inputClass,
                value: raw,
                'data-original': raw
            });

            $td.data('original-html', $td.html());
            $td.empty().append($input);
            $input.focus().select();

            $input.on('keydown', function(ke) {
                if (ke.key === 'Enter') {
                    ke.preventDefault();
                    $(this).blur();
                } else if (ke.key === 'Escape') {
                    cancelEdit($td);
                }
            });

            $input.on('blur', function() {
                var $inp = $(this);
                var newVal = $.trim($inp.val());
                var oldVal = $inp.data('original');

                if (newVal === oldVal || (!newVal && cfg.type === 'money')) {
                    cancelEdit($td);
                    return;
                }

                saveInlineEdit($td, itemId, field, newVal, cfg);
            });
        });

        function cancelEdit($td) {
            var orig = $td.data('original-html');
            if (orig !== undefined) $td.html(orig);
        }

        function saveInlineEdit($td, itemId, field, value, cfg) {
            $td.css('opacity', '0.5');
            $td.find('input').prop('disabled', true);

            $.post('items/inlineUpdate', {
                item_id: itemId,
                field: field,
                value: value
            }, function(resp) {
                if (resp.success && resp.value !== undefined) {
                    $td.html(resp.value);
                    $td.css('opacity', '');
                    $td.addClass('inline-edit-flash');
                    setTimeout(function() { $td.removeClass('inline-edit-flash'); }, 1200);
                    $.notify(resp.message, { type: 'success', placement: { from: 'bottom', align: 'right' }, timer: 2000 });
                } else {
                    cancelEdit($td);
                    $.notify(resp.message || 'Erro ao salvar', { type: 'danger', placement: { from: 'bottom', align: 'right' } });
                }
            }, 'json').fail(function() {
                cancelEdit($td);
                $.notify('Falha na comunicação com o servidor', { type: 'danger', placement: { from: 'bottom', align: 'right' } });
            });
        }

        // Hook into the existing table_support init / load success. We grab the
        // onLoadSuccess that manage.php already passes, wrap it to also mark cells.
        var __inlineHooked = false;
        $(function() {
            if (__inlineHooked) return;
            __inlineHooked = true;
            var origInit = window.table_support && table_support.init;
            // call markInlineCells after table renders (bootstrap-table draws async)
            var timer = setInterval(function() {
                if ($('#table_holder.items-manage tbody tr').length) {
                    markInlineCells();
                    clearInterval(timer);
                }
            }, 300);
            // also on pagination/search load success
            $(document).on('load-success.bs.table', function(e) {
                markInlineCells();
            });
        });
    })();

    // ── Real-time item-update notifications (phone app → PC) ──
    (function() {
        var wsUrl = 'ws://' + window.location.hostname + ':8000/v1/store/item-update/ws';
        var seen = {};
        var reconnectDelay = 2000;

        function connect() {
            try {
                var ws = new WebSocket(wsUrl);
                ws.onopen = function() {
                    reconnectDelay = 2000;
                    ws.send('ping');
                    setInterval(function() { try { ws.send('ping'); } catch(e) {} }, 25000);
                };
                ws.onmessage = function(evt) {
                    if (evt.data === 'pong') return;
                    try {
                        var d = JSON.parse(evt.data);
                        if (d.type !== 'item_update') return;
                        var key = d.item_id + ':' + d.action + ':' + (d.ts || '');
                        if (seen[key]) return;
                        seen[key] = true;
                        setTimeout(function() { delete seen[key]; }, 10000);

                        var msg, type;
                        if (d.action === 'created') {
                            msg = '<b>' + (d.item_name || 'Produto') + '</b> cadastrado no celular.';
                            type = 'success';
                        } else if (d.action === 'updated') {
                            msg = '<b>' + (d.item_name || 'Produto') + '</b> atualizado no celular.';
                            type = 'info';
                        } else if (d.action === 'deleted') {
                            msg = 'Produto #' + d.item_id + ' excluído no celular.';
                            type = 'warning';
                        }
                        if (msg) {
                            $.notify({ message: msg, icon: 'glyphicon glyphicon-phone' }, {
                                type: type, placement: { from: 'bottom', align: 'right' }, timer: 5000,
                                offset: { y: 60 }
                            });
                        }
                        if (typeof table_support !== 'undefined' && table_support.refresh) {
                            table_support.refresh();
                        }
                    } catch(e) {}
                };
                ws.onclose = function() {
                    setTimeout(connect, reconnectDelay);
                    reconnectDelay = Math.min(reconnectDelay * 1.5, 30000);
                };
                ws.onerror = function() { ws.close(); };
            } catch(e) {}
        }
        connect();
    })();
</script>

<div id="title_bar" class="btn-toolbar print_hide">
    <button class="btn btn-info btn-sm pull-right modal-dlg" data-btn-submit="<?= lang('Common.submit') ?>" data-href="<?= "$controller_name/csvImport" ?>" title="<?= lang('Items.import_items_csv') ?>">
        <span class="glyphicon glyphicon-import">&nbsp;</span><?= lang('Common.import_csv') ?>
    </button>

    <button class="btn btn-info btn-sm pull-right modal-dlg modal-dlg-1000" data-btn-new="<?= lang('Common.new') ?>" data-btn-submit="<?= lang('Common.submit') ?>" data-href="<?= "$controller_name/view" ?>" title="<?= lang(ucfirst($controller_name) . '.new') ?>">
        <span class="glyphicon glyphicon-tag">&nbsp;</span><?= lang(ucfirst($controller_name) . '.new') ?>
    </button>
</div>

<div id="toolbar">
    <div class="pull-left form-inline" role="toolbar">
        <?php /* P1/DESIGN.md: acao destrutiva com sinal proprio. Antes era
               btn-default, identica a "Editar Multiplos" e "Gerar Codigos",
               e nao dava para distinguir sem passar o mouse. */ ?>
        <button id="delete" class="btn btn-sm btn-danger-outline print_hide" data-destructive="1">
            <span class="glyphicon glyphicon-trash">&nbsp;</span><?= lang('Common.delete') ?>
        </button>
        <span class="items-today-counters print_hide" style="margin-left:12px; font-size:13px; color:var(--os-text-muted); vertical-align:middle;">
            Produtos Cadastrados Hoje: <strong><?= (int) ($items_created_today ?? 0) ?></strong>
            &nbsp;|&nbsp;
            Produtos Atualizados Hoje: <strong><?= (int) ($items_updated_today ?? 0) ?></strong>
        </span>
        <button id="bulk_edit" class="btn btn-default btn-sm modal-dlg print_hide" data-btn-submit="<?= lang('Common.submit') ?>" data-href="<?= "items/bulkEdit" ?>" title="<?= lang('Items.edit_multiple_items') ?>">
            <span class="glyphicon glyphicon-edit">&nbsp;</span><?= lang('Items.bulk_edit') ?>
        </button>
        <button id="generate_barcodes" class="btn btn-default btn-sm print_hide" data-href="<?= "$controller_name/generateBarcodes" ?>" title="<?= lang('Items.generate_barcodes') ?>">
            <span class="glyphicon glyphicon-barcode">&nbsp;</span><?= lang('Items.generate_barcodes') ?>
        </button>
        <a href="#" id="ml_listings_btn" class="btn btn-default btn-sm print_hide" role="button" title="<?= lang('Items.ml_listings_title') ?>">
            <span class="glyphicon glyphicon-send">&nbsp;</span><?= lang('Items.ml_manage') ?>
        </a>
        <?= form_input(['name' => 'daterangepicker', 'class' => 'form-control input-sm', 'id' => 'daterangepicker']) ?>
        <?= form_multiselect('filters[]', $filters, [''], [
            'id'                        => 'filters',
            'class'                     => 'selectpicker show-menu-arrow',
            'data-none-selected-text'   => lang('Common.none_selected_text'),
            'data-selected-text-format' => 'count > 1',
            'data-style'                => 'btn-default btn-sm',
            'data-width'                => 'fit'
        ]) ?>
        <?php
        if (count($stock_locations) > 1) {
            echo form_dropdown(
                'stock_location',
                $stock_locations,
                $stock_location,
                [
                    'id'         => 'stock_location',
                    'class'      => 'selectpicker show-menu-arrow',
                    'data-style' => 'btn-default btn-sm',
                    'data-width' => 'fit'
                ]
            );
        }
        ?>
    </div>
</div>

<div id="table_holder" class="items-manage">
    <table id="table"></table>
</div>

<div class="modal fade" id="ml_listings_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><?= lang('Items.ml_listings_title') ?></h4>
            </div>
            <div class="modal-body" style="max-height:60vh; overflow-y:auto;">
                <table class="table table-condensed table-striped" style="font-size:13px;">
                    <thead>
                        <tr><th>SKU</th><th>Anúncio</th><th>Status</th><th>Link</th><th></th></tr>
                    </thead>
                    <tbody id="ml_listings_body"></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ml_publish_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><?= lang('Items.ml_publish') ?></h4>
            </div>
            <div class="modal-body" id="ml_publish_body" style="max-height:60vh; overflow-y:auto;"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="ml_publish_confirm" disabled>Publicar</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    (function() {
        var mlMap = {};
        var mlPublishItem = null;
        var ML_LABELS = {
            active: 'ATIVO', paused: 'PAUSADO', closed: 'ENCERRADO',
            inactive: 'INATIVO', under_review: 'EM ANÁLISE'
        };
        function mlEscape(value) {
            return $('<div>').text(value === null || value === undefined ? '' : String(value)).html();
        }

        function mlStatusLabel(status) {
            return ML_LABELS[status] || (status || '');
        }

        function mlStatusClass(status) {
            if (status === 'active') { return 'success'; }
            if (status === 'paused') { return 'warning'; }
            return 'default';
        }

        function mlRequest(type, url, data) {
            return $.ajax({ url: url, type: type, data: data, dataType: 'json' });
        }

        function mlNotify(message, type) {
            $.notify(message, { type: type, placement: { from: 'bottom', align: 'right' }, timer: 3000 });
        }

        function mlErrorMessage(xhr, fallback) {
            var payload = xhr && xhr.responseJSON;
            if (payload && payload.error) { return payload.error; }
            if (payload && payload.message) { return payload.message; }
            return fallback;
        }

        function mlDecorateRows() {
            $('#table_holder.items-manage tbody tr').each(function() {
                var $button = $(this).find('.ml-publish-btn');
                if (!$button.length) { return; }
                var mapping = mlMap[$button.attr('data-ml-sku')];
                $button.removeClass('ml-status-active ml-status-paused ml-status-closed ml-status-inactive ml-status-under_review');
                if (mapping) {
                    $button.addClass('ml-published ml-status-' + mapping.status);
                    $button.attr('title', 'Publicado no Mercado Livre — ' + mlStatusLabel(mapping.status));
                } else {
                    $button.removeClass('ml-published');
                    $button.attr('title', <?= json_encode(lang('Items.ml_publish')) ?>);
                }
            });
        }

        function mlRenderListings() {
            var mappings = [];
            $.each(mlMap, function(sku, mapping) { mappings.push(mapping); });
            var $body = $('#ml_listings_body');
            if (!mappings.length) {
                $body.html('<tr><td colspan="5" class="text-center text-muted">' + <?= json_encode(lang('Items.ml_no_listings')) ?> + '</td></tr>');
                return;
            }
            var html = '';
            $.each(mappings, function(index, mapping) {
                var actions = '';
                if (mapping.status === 'active') {
                    actions = '<button type="button" class="btn btn-xs btn-warning ml-status-btn" data-ml-action="pause" data-ml-sku="' + mlEscape(mapping.sku) + '">' + <?= json_encode(lang('Items.ml_pause')) ?> + '</button>';
                } else if (mapping.status === 'paused') {
                    actions = '<button type="button" class="btn btn-xs btn-success ml-status-btn" data-ml-action="activate" data-ml-sku="' + mlEscape(mapping.sku) + '">' + <?= json_encode(lang('Items.ml_activate')) ?> + '</button>';
                }
                html += '<tr>'
                    + '<td><code>' + mlEscape(mapping.sku) + '</code></td>'
                    + '<td>' + mlEscape(mapping.external_id) + '</td>'
                    + '<td><span class="label label-' + mlStatusClass(mapping.status) + '">' + mlEscape(mlStatusLabel(mapping.status)) + '</span></td>'
                    + '<td>' + (mapping.external_url ? '<a href="' + mlEscape(mapping.external_url) + '" target="_blank" rel="noopener">abrir anúncio</a>' : '-') + '</td>'
                    + '<td class="text-right">' + actions + '</td>'
                    + '</tr>';
            });
            $body.html(html);
        }

        function mlLoadMappings() {
            return mlRequest('GET', 'mercadolivre/mappings').done(function(response) {
                mlMap = {};
                $.each(response.mappings || [], function(index, mapping) { mlMap[mapping.sku] = mapping; });
                mlDecorateRows();
                mlRenderListings();
            }).fail(function(xhr) {
                mlNotify(mlErrorMessage(xhr, 'Falha ao consultar o Mercado Livre.'), 'danger');
            });
        }

        function mlCollectAttributes() {
            var attributes = [];
            $('#ml_publish_body .ml-attr-input').each(function() {
                var value = $.trim($(this).val());
                if (value !== '') {
                    attributes.push({ id: $(this).attr('data-attr-id'), value_name: value });
                }
            });
            return attributes;
        }

        function mlPublish(dryRun, attributes) {
            var data = { item_id: mlPublishItem.item_id, dry_run: dryRun ? 1 : 0 };
            $.each(attributes, function(index, attribute) {
                data['attributes[' + index + '][id]'] = attribute.id;
                data['attributes[' + index + '][value_name]'] = attribute.value_name;
            });
            return mlRequest('POST', 'mercadolivre/publish', data);
        }

        function mlRenderPublishPreview(response) {
            var body = (response && response.body) || {};
            var attributes = body.attributes || [];
            var missing = body.missing_required_attributes || [];
            var blocked = body.blocked_reasons || [];
            var html = '<dl class="dl-horizontal">'
                + '<dt>Categoria</dt><dd>' + mlEscape(body.category_id || '-') + '</dd>'
                + '<dt>Preço</dt><dd>' + (body.price !== undefined && body.price !== null ? 'R$ ' + Number(body.price).toFixed(2).replace('.', ',') : '-') + '</dd>'
                + '<dt>Estoque</dt><dd>' + (body.available_quantity !== undefined && body.available_quantity !== null ? body.available_quantity : '-') + '</dd>'
                + '<dt>Título</dt><dd>' + mlEscape(body.family_name || '') + '</dd>'
                + '</dl>';
            if (attributes.length) {
                html += '<p><strong>Atributos:</strong></p><ul style="margin:0 0 10px 18px;">';
                $.each(attributes, function(index, attribute) {
                    html += '<li>' + mlEscape(attribute.id) + ': ' + mlEscape(attribute.value_name) + '</li>';
                });
                html += '</ul>';
            }
            if (missing.length) {
                html += '<p class="text-danger"><strong>' + <?= json_encode(lang('Items.ml_required_attrs')) ?> + ':</strong></p>';
                $.each(missing, function(index, attribute) {
                    var id = attribute.id || attribute;
                    var name = attribute.name || id;
                    html += '<div class="form-group form-group-sm">'
                        + '<label style="display:block; font-weight:normal;">' + mlEscape(name) + ' <small class="text-muted">(' + mlEscape(id) + ')</small></label>'
                        + '<input type="text" class="form-control input-sm ml-attr-input" data-attr-id="' + mlEscape(id) + '">'
                        + '</div>';
                });
            }
            if (blocked.length) {
                html += '<p class="text-danger"><strong>' + <?= json_encode(lang('Items.ml_blocked')) ?> + '</strong></p><ul style="margin:0 0 0 18px;">';
                $.each(blocked, function(index, reason) { html += '<li>' + mlEscape(reason) + '</li>'; });
                html += '</ul>';
            }
            $('#ml_publish_body').html(html);
            $('#ml_publish_confirm').prop('disabled', blocked.length > 0);
        }

        $(document).on('load-success.bs.table', function() { mlDecorateRows(); });

        $(document).on('click', '#ml_listings_btn', function(event) {
            event.preventDefault();
            $('#ml_listings_body').html('<tr><td colspan="5" class="text-center text-muted">' + <?= json_encode(lang('Items.ml_loading')) ?> + '</td></tr>');
            $('#ml_listings_modal').modal('show');
            mlLoadMappings();
        });

        $(document).on('click', '.ml-publish-btn', function(event) {
            event.preventDefault();
            event.stopPropagation();
            mlPublishItem = { item_id: $(this).attr('data-ml-item'), sku: $(this).attr('data-ml-sku') };
            $('#ml_publish_body').html(<?= json_encode(lang('Items.ml_loading')) ?>);
            $('#ml_publish_confirm').prop('disabled', true);
            $('#ml_publish_modal').modal('show');
            mlPublish(true, []).done(mlRenderPublishPreview).fail(function(xhr) {
                $('#ml_publish_body').html('<p class="text-danger">' + mlEscape(mlErrorMessage(xhr, 'Falha ao consultar o Mercado Livre.')) + '</p>');
            });
        });

        $(document).on('click', '#ml_publish_confirm', function() {
            var $button = $(this).prop('disabled', true);
            mlPublish(false, mlCollectAttributes()).done(function(response) {
                var listingId = response && response.body && response.body.id ? response.body.id : '';
                $('#ml_publish_modal').modal('hide');
                mlNotify('Publicado no Mercado Livre' + (listingId ? ': ' + listingId : '.'), 'success');
                mlLoadMappings();
                if (window.table_support && table_support.refresh) { table_support.refresh(); }
            }).fail(function(xhr) {
                var payload = (xhr && xhr.responseJSON) || {};
                var message = mlEscape(mlErrorMessage(xhr, 'Falha ao publicar o produto.'));
                if (payload.external_url) {
                    message += ' <a href="' + mlEscape(payload.external_url) + '" target="_blank" rel="noopener">abrir anúncio</a>';
                }
                $('#ml_publish_body').prepend('<p class="text-danger">' + message + '</p>');
                $button.prop('disabled', false);
            });
        });

        $(document).on('click', '.ml-status-btn', function() {
            var $button = $(this);
            var sku = $button.attr('data-ml-sku');
            var action = $button.attr('data-ml-action');
            var question = action === 'pause' ? 'Pausar o anúncio ' + sku + '?' : 'Reativar o anúncio ' + sku + '?';
            if (!window.confirm(question)) { return; }
            $button.prop('disabled', true);
            mlRequest('POST', 'mercadolivre/' + action, { sku: sku, dry_run: 0 }).done(function() {
                mlNotify(action === 'pause' ? 'Anúncio pausado.' : 'Anúncio reativado.', 'success');
                mlLoadMappings();
            }).fail(function(xhr) {
                mlNotify(mlErrorMessage(xhr, 'Falha ao alterar o anúncio.'), 'danger');
                $button.prop('disabled', false);
            });
        });

        $(function() { mlLoadMappings(); });
    })();
</script>

<?= view('partial/footer') ?>
