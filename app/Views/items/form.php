<?php
/**
 * @var object $item_info
 * @var array $categories
 * @var int $selected_category
 * @var bool $standard_item_locked
 * @var bool $item_kit_disabled
 * @var int $allow_temp_item
 * @var array $suppliers
 * @var int $selected_supplier
 * @var bool $use_destination_based_tax
 * @var float $default_tax_1_rate
 * @var float $default_tax_2_rate
 * @var string $tax_category
 * @var int $tax_category_id
 * @var bool $include_hsn
 * @var string $hsn_code
 * @var array $stock_locations
 * @var bool $logo_exists
 * @var string $image_path
 * @var string $selected_low_sell_item
 * @var int $selected_low_sell_item_id
 * @var string $controller_name
 * @var array $config
 * @var bool $quick_mode
 */
$quick_mode = $quick_mode ?? false;
?>

<div id="required_fields_message"><?= lang('Common.fields_required_message') ?></div>
<ul id="error_message_box" class="error_message_box"></ul>

<div id="item_draft_notice" style="display:none; background:#fffbe6; border:1px solid #ffe58f; border-radius:3px; padding:5px 10px; margin-bottom:8px; font-size:12px; color:#8c6e1f;">
    <span class="glyphicon glyphicon-pencil" style="margin-right:4px;"></span>
    <span class="item_draft_when"></span>
    <a href="#" id="item_draft_discard" style="float:right; font-weight:bold; margin-left:10px; color:#8c6e1f;">Descartar</a>
</div>

<?= form_open("items/save/$item_info->item_id", ['id' => 'item_form', 'enctype' => 'multipart/form-data']) ?>
    <?php $has_price_history_tab = ($item_info->item_id > 0); ?>
    <?php if ($has_price_history_tab): ?>
    <ul class="nav nav-tabs" id="item_form_tabs" style="margin-bottom: 12px;">
        <li class="active"><a href="#item_basic_info" data-toggle="tab"><?= lang('Items.tab_product') ?></a></li>
        <li><a href="#item_price_history" data-toggle="tab"><?= lang('Items.tab_price_history') ?></a></li>
    </ul>
    <?php endif; ?>
    <div class="tab-content">
        <fieldset id="item_basic_info" class="tab-pane active">

        <div class="row form-group form-group-sm">
            <div class="col-xs-<?= $quick_mode ? 12 : 6 ?>">
                <?= form_label(lang('Items.item_number'), 'item_number', ['class' => 'control-label']) ?>
                <div class="input-group">
                    <span class="input-group-addon input-sm"><span class="glyphicon glyphicon-barcode"></span></span>
                    <?= form_input([
                        'name'  => 'item_number',
                        'id'    => 'item_number',
                        'class' => 'form-control input-sm',
                        'value' => $item_info->item_number
                    ]) ?>
                    <span class="input-group-btn">
                        <button type="button" id="barcode_generate" class="btn btn-default btn-sm" title="Gerar um código interno aleatório (letras e números) para produtos sem código de barras" style="height: 30px;">
                            <span class="glyphicon glyphicon-random"></span> Gerar código
                        </button>
                    </span>
                </div>
            </div>
            <?php if ($quick_mode): ?>
                <?= form_hidden('category', $selected_category) ?>
            <?php else: ?>
            <div class="col-xs-6">
                <?= form_label(lang('Items.category'), 'category', ['class' => 'required control-label']) ?>
                <div class="input-group">
                    <span class="input-group-addon input-sm"><span class="glyphicon glyphicon-tag"></span></span>
                    <?php
                    // Category is always a dropdown populated from the categories in use
                    // (ospos_items.category) — see Items::getView().
                    echo form_dropdown('category', $categories, $selected_category, ['id' => 'category', 'class' => 'form-control']);
                    ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('Items.name'), 'name', ['class' => 'required control-label']) ?>
            <?= form_input([
                'name'  => 'name',
                'id'    => 'name',
                'class' => 'form-control input-sm',
                'value' => $item_info->name
            ]) ?>
        </div>

        <div class="row form-group form-group-sm">
            <?php if ($quick_mode): ?>
                <?= form_hidden('cost_price', to_currency_no_money($item_info->cost_price)) ?>
            <?php else: ?>
            <div class="col-xs-6">
                <?= form_label(lang('Items.cost_price'), 'cost_price', ['class' => 'required control-label']) ?>
                <div class="input-group input-group-sm">
                    <?php if (!is_right_side_currency_symbol()): ?>
                        <span class="input-group-addon input-sm"><b><?= esc($config['currency_symbol']) ?></b></span>
                    <?php endif; ?>
                    <?= form_input([
                        'name'    => 'cost_price',
                        'id'      => 'cost_price',
                        'class'   => 'form-control input-sm',
                        'onClick' => 'this.select();',
                        'value'   => to_currency_no_money($item_info->cost_price)
                    ]) ?>
                    <?php if (is_right_side_currency_symbol()): ?>
                        <span class="input-group-addon input-sm"><b><?= esc($config['currency_symbol']) ?></b></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <div class="col-xs-<?= $quick_mode ? 12 : 6 ?>">
                <?= form_label(lang('Items.unit_price'), 'unit_price', ['class' => 'required control-label']) ?>
                <div class="input-group input-group-sm">
                    <?php if (!is_right_side_currency_symbol()): ?>
                        <span class="input-group-addon input-sm"><b><?= esc($config['currency_symbol']) ?></b></span>
                    <?php endif; ?>
                    <?= form_input([
                        'name'    => 'unit_price',
                        'id'      => 'unit_price',
                        'class'   => 'form-control input-sm',
                        'onClick' => 'this.select();',
                        'value'   => to_currency_no_money($item_info->unit_price)
                    ]) ?>
                    <?php if (is_right_side_currency_symbol()): ?>
                        <span class="input-group-addon input-sm"><b><?= esc($config['currency_symbol']) ?></b></span>
                    <?php endif; ?>
                </div>
                <?php if (!$quick_mode): ?>
                <div class="input-group input-group-sm" style="margin-top: 4px;" title="<?= lang('Items.markup_hint') ?>">
                    <span class="input-group-addon input-sm"><b><?= lang('Items.markup') ?></b></span>
                    <input type="text" id="markup_percent" class="form-control input-sm" inputmode="decimal"
                           autocomplete="off" placeholder="<?= lang('Items.markup_placeholder') ?>"
                           aria-label="<?= lang('Items.markup') ?>">
                    <span class="input-group-addon input-sm"><b>%</b></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!$quick_mode): ?>
        <div class="row form-group form-group-sm">
            <?php foreach ($stock_locations as $key => $location_detail) { ?>
                <div class="col-xs-6">
                    <?= form_label(lang('Items.quantity') . ' ' . $location_detail['location_name'], "quantity_$key", ['class' => 'required control-label']) ?>
                    <?= form_input([
                        'name'    => "quantity_$key",
                        'id'      => "quantity_$key",
                        'class'   => 'required quantity form-control',
                        'onClick' => 'this.select();',
                        'value'   => isset($item_info->item_id) ? to_quantity_decimals($location_detail['quantity']) : to_quantity_decimals(0)
                    ]) ?>
                </div>
            <?php } ?>
            <div class="col-xs-6">
                <?= form_label(lang('Items.reorder_level'), 'reorder_level', ['class' => 'required control-label']) ?>
                <?= form_input([
                    'name'    => 'reorder_level',
                    'id'      => 'reorder_level',
                    'class'   => 'form-control input-sm',
                    'onClick' => 'this.select();',
                    'value'   => isset($item_info->item_id) ? to_quantity_decimals($item_info->reorder_level) : to_quantity_decimals(0)
                ]) ?>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('Items.description'), 'description', ['class' => 'control-label']) ?>
            <?= form_textarea([
                'name'  => 'description',
                'id'    => 'description',
                'rows'  => 2,
                'class' => 'form-control input-sm description-box',
                'value' => $item_info->description
            ]) ?>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('Items.supplier'), 'supplier', ['class' => 'control-label']) ?>
            <?= form_dropdown('supplier_id', $suppliers, $selected_supplier, ['id' => 'supplier_id', 'class' => 'form-control']) ?>
            <a href="#" id="supplier_quick_add_toggle" style="font-size: 12px;">
                <span class="glyphicon glyphicon-plus"></span> <?= lang('Items.supplier_new') ?>
            </a>
        </div>

        <div class="form-group form-group-sm clearfix" id="supplier_quick_add" style="display: none;">
            <div style="border-left: 3px solid var(--os-primary, #1a9c6a); padding-left: 10px;">
                <p class="text-muted" style="font-size: 12px; margin-bottom: 6px;"><?= lang('Items.supplier_new_hint') ?></p>
                <input type="text" id="supplier_new_name" class="form-control input-sm" style="margin-bottom: 4px;" placeholder="<?= esc(lang('Suppliers.company_name'), 'attr') ?>">
                <input type="text" id="supplier_new_tax_id" class="form-control input-sm" style="margin-bottom: 4px;" placeholder="<?= esc(lang('Suppliers.tax_id'), 'attr') ?>">
                <input type="text" id="supplier_new_phone" class="form-control input-sm" style="margin-bottom: 6px;" placeholder="<?= esc(lang('Common.phone_number'), 'attr') ?>">
                <button type="button" id="supplier_quick_add_save" class="btn btn-primary btn-xs"><?= lang('Items.supplier_new_save') ?></button>
                <button type="button" id="supplier_quick_add_cancel" class="btn btn-default btn-xs"><?= lang('Items.supplier_new_cancel') ?></button>
                <span id="supplier_quick_add_msg" style="font-size: 12px; margin-left: 6px;"></span>
            </div>
        </div>

        <div class="form-group form-group-sm clearfix" id="item_extra_codes_fg">
            <?= form_label(lang('Items.extra_codes'), 'extra_code_input', ['class' => 'control-label']) ?>
            <p class="text-muted" style="font-size: 12px; margin-bottom: 4px;"><?= lang('Items.extra_codes_hint') ?></p>
            <div class="input-group input-group-sm" style="margin-bottom: 4px;">
                <input type="text" id="extra_code_input" class="form-control input-sm" placeholder="<?= esc(lang('Items.extra_code_placeholder'), 'attr') ?>">
                <span class="input-group-btn">
                    <button type="button" id="extra_code_add" class="btn btn-primary btn-sm"><?= lang('Items.extra_code_add') ?></button>
                </span>
            </div>
            <div id="extra_codes_list"></div>
            <span id="extra_code_msg" style="font-size: 12px;"></span>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('Items.image'), 'items_image', ['class' => 'control-label']) ?>
            <div class="fileinput <?= $logo_exists ? 'fileinput-exists' : 'fileinput-new' ?>" data-provides="fileinput">
                <div class="fileinput-new thumbnail" style="width: 100px; height: 100px;"></div>
                <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 100px; max-height: 100px;">
                    <a href="#" class="form-item-img" data-img-view="<?= esc($image_path, 'attr') ?>" data-img-title="<?= esc($item_info->name ?? 'Produto', 'attr') ?>" title="<?= lang('Items.view_image') ?>" style="display:block;">
                        <img data-src="holder.js/100%x100%" alt="<?= lang('Items.image') ?>"
                            src="<?= $image_path ?>"
                            style="max-height: 100%; max-width: 100%; cursor: zoom-in;">
                    </a>
                </div>
                <div>
                    <span class="btn btn-default btn-sm btn-file">
                        <span class="fileinput-new"><?= lang('Items.select_image') ?></span>
                        <span class="fileinput-exists"><?= lang('Items.change_image') ?></span>
                        <input type="file" name="items_image" accept="image/*">
                    </span>
                    <a href="#" class="btn btn-default btn-sm fileinput-exists" data-dismiss="fileinput"><?= lang('Items.remove_image') ?></a>
                </div>
            </div>
        </div>

        <?php $item_pics = $item_pics ?? []; $gallery_item_id = (int)($item_info->item_id ?? NEW_ENTRY); ?>
        <div class="form-group form-group-sm" id="item_gallery_group">
            <label class="control-label">Galeria de fotos</label>
            <div id="item_gallery" class="item-gallery" data-item-id="<?= $gallery_item_id ?>">
                <?php foreach ($item_pics as $pic): ?>
                    <div class="item-gallery-item" data-pic-id="<?= (int)$pic['item_pic_id'] ?>">
                        <a href="#" class="form-item-img" data-img-view="<?= esc($pic['url'], 'attr') ?>" data-img-title="<?= esc($item_info->name ?? 'Produto', 'attr') ?>" title="<?= lang('Items.view_image') ?>">
                            <img src="<?= esc($pic['url'], 'attr') ?>" alt="">
                        </a>
                        <button type="button" class="btn btn-xs btn-default js-set-main" title="Definir como principal">&#9733;</button>
                        <button type="button" class="btn btn-xs btn-danger js-del-pic" title="Remover">&times;</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <div id="item_gallery_removed"></div>
            <span class="btn btn-default btn-sm btn-file" style="margin-top: 6px;">
                <span class="glyphicon glyphicon-plus"></span>&nbsp;Adicionar fotos
                <input type="file" name="items_images[]" accept="image/*" multiple>
            </span>
            <small class="text-muted" style="display:block; margin-top: 4px;">Envie várias fotos do produto (até 10). A foto principal fica no topo do cadastro.</small>
        </div>

        <div class="form-group form-group-sm" style="margin-top: 6px;">
            <a href="#item_extra_fields" role="button" data-toggle="collapse" aria-expanded="false" aria-controls="item_extra_fields" class="btn btn-default btn-sm btn-block" id="item_extra_toggle">
                <span class="glyphicon glyphicon-chevron-down" id="item_extra_caret" style="font-size: 10px;"></span>&nbsp;<?= lang('Items.extra_options') ?>
            </a>
        </div>
        <?php endif; // !$quick_mode — quantity/reorder, description, supplier, extra codes, image e extra options são defaults no cadastro rápido ?>

        <?php if (!$quick_mode): ?>
        <div id="item_extra_fields" class="collapse">
            <div style="padding: 8px 2px 2px;">

                <div class="form-group form-group-sm">
                    <?= form_label(lang('Items.stock_type'), 'stock_type', ['class' => 'control-label col-xs-3']) ?>
                    <div class="col-xs-8">
                        <label class="radio-inline">
                            <?= form_radio([
                                'name'    => 'stock_type',
                                'type'    => 'radio',
                                'id'      => 'stock_type',
                                'value'   => 0,
                                'checked' => $item_info->stock_type == HAS_STOCK
                            ]) ?> <?= lang('Items.stock') ?>
                        </label>
                        <label class="radio-inline">
                            <?= form_radio([
                                'name'    => 'stock_type',
                                'type'    => 'radio',
                                'id'      => 'stock_type',
                                'value'   => 1,
                                'checked' => $item_info->stock_type == HAS_NO_STOCK
                            ]) ?><?= lang('Items.nonstock') ?>
                        </label>
                    </div>
                </div>

                <div class="form-group form-group-sm">
                    <?= form_label(lang('Items.type'), 'item_type', ['class' => 'control-label col-xs-3']) ?>
                    <div class="col-xs-8">
                        <label class="radio-inline">
                            <?php
                            $radio_button = [
                                'name'    => 'item_type',
                                'type'    => 'radio',
                                'id'      => 'item_type',
                                'value'   => 0,
                                'checked' => $item_info->item_type == ITEM
                            ];

                            if ($standard_item_locked) {
                                $radio_button['disabled'] = true;
                            }
                            echo form_radio($radio_button) ?> <?= lang('Items.standard') ?>
                        </label>
                        <label class="radio-inline">
                            <?php
                            $radio_button = [
                                'name'    => 'item_type',
                                'type'    => 'radio',
                                'id'      => 'item_type',
                                'value'   => 1,
                                'checked' => $item_info->item_type == ITEM_KIT
                            ];

                            if ($item_kit_disabled) {
                                $radio_button['disabled'] = true;
                            }
                            echo form_radio($radio_button) ?> <?= lang('Items.kit') ?>
                        </label>
                        <?php if ($config['derive_sale_quantity'] == '1') { ?>
                            <label class="radio-inline">
                                <?= form_radio([
                                    'name'    => 'item_type',
                                    'type'    => 'radio',
                                    'id'      => 'item_type',
                                    'value'   => 2,
                                    'checked' => $item_info->item_type == ITEM_AMOUNT_ENTRY
                                ]) ?><?= lang('Items.amount_entry') ?>
                            </label>
                        <?php } ?>
                        <?php if ($allow_temp_item == 1) { ?>
                            <label class="radio-inline">
                                <?= form_radio([
                                    'name'    => 'item_type',
                                    'type'    => 'radio',
                                    'id'      => 'item_type',
                                    'value'   => 3,
                                    'checked' => $item_info->item_type == ITEM_TEMP
                                ]) ?> <?= lang('Items.temp') ?>
                            </label>
                        <?php } ?>
                    </div>
                </div>

                <?php if (!$use_destination_based_tax) { ?>
                    <div class="form-group form-group-sm">
                        <?= form_label(lang('Items.tax_1'), 'tax_percent_1', ['class' => 'control-label col-xs-3']) ?>
                        <div class="col-xs-8">
                            <div class="row">
                                <div class="col-xs-6">
                                    <?= form_input([
                                        'name'  => 'tax_names[]',
                                        'id'    => 'tax_name_1',
                                        'class' => 'form-control input-sm',
                                        'value' => $item_tax_info[0]['name'] ?? $config['default_tax_1_name']
                                    ]) ?>
                                </div>
                                <div class="col-xs-6">
                                    <div class="input-group input-group-sm">
                                        <?= form_input([
                                            'name'  => 'tax_percents[]',
                                            'id'    => 'tax_percent_name_1',
                                            'class' => 'form-control input-sm',
                                            'value' => isset($item_tax_info[0]['percent']) ? to_tax_decimals($item_tax_info[0]['percent']) : to_tax_decimals($default_tax_1_rate)
                                        ]) ?>
                                        <span class="input-group-addon input-sm"><b>%</b></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group form-group-sm">
                        <?= form_label(lang('Items.tax_2'), 'tax_percent_2', ['class' => 'control-label col-xs-3']) ?>
                        <div class="col-xs-8">
                            <div class="row">
                                <div class="col-xs-6">
                                    <?= form_input([
                                        'name'  => 'tax_names[]',
                                        'id'    => 'tax_name_2',
                                        'class' => 'form-control input-sm',
                                        'value' => $item_tax_info[1]['name'] ?? $config['default_tax_2_name']
                                    ]) ?>
                                </div>
                                <div class="col-xs-6">
                                    <div class="input-group input-group-sm">
                                        <?= form_input([
                                            'name'  => 'tax_percents[]',
                                            'class' => 'form-control input-sm',
                                            'id'    => 'tax_percent_name_2',
                                            'value' => isset($item_tax_info[1]['percent']) ? to_tax_decimals($item_tax_info[1]['percent']) : to_tax_decimals($default_tax_2_rate)
                                        ]) ?>
                                        <span class="input-group-addon input-sm"><b>%</b></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php if ($use_destination_based_tax): ?>
                    <div class="form-group form-group-sm">
                        <?= form_label(lang('Taxes.tax_category'), 'tax_category', ['class' => 'control-label col-xs-3']) ?>
                        <div class="col-xs-8">
                            <div class="input-group input-group-sm">
                                <?= form_input([
                                    'name'  => 'tax_category',
                                    'id'    => 'tax_category',
                                    'class' => 'form-control input-sm',
                                    'size'  => '50',
                                    'value' => $tax_category
                                ]) ?>
                                <?= form_hidden('tax_category_id', $tax_category_id) ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($include_hsn): ?>
                    <div class="form-group form-group-sm">
                        <?= form_label(lang('Items.hsn_code'), 'category', ['class' => 'control-label col-xs-3']) ?>
                        <div class="col-xs-8">
                            <div class="input-group">
                                <?= form_input([
                                    'name'  => 'hsn_code',
                                    'id'    => 'hsn_code',
                                    'class' => 'form-control input-sm',
                                    'value' => $hsn_code
                                ]) ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-group form-group-sm">
                    <?= form_label(lang('Items.receiving_quantity'), 'receiving_quantity', ['class' => 'control-label col-xs-3']) ?>
                    <div class="col-xs-4">
                        <?= form_input([
                            'name'    => 'receiving_quantity',
                            'id'      => 'receiving_quantity',
                            'class'   => 'form-control input-sm',
                            'onClick' => 'this.select();',
                            'value'   => isset($item_info->item_id) ? to_quantity_decimals($item_info->receiving_quantity) : to_quantity_decimals(0)
                        ]) ?>
                    </div>
                </div>

                <div class="form-group form-group-sm">
                    <?= form_label(lang('Items.allow_alt_description'), 'allow_alt_description', ['class' => 'control-label col-xs-3']) ?>
                    <div class="col-xs-1">
                        <?= form_checkbox([
                            'name'    => 'allow_alt_description',
                            'id'      => 'allow_alt_description',
                            'value'   => 1,
                            'checked' => $item_info->allow_alt_description == 1
                        ]) ?>
                    </div>
                </div>

                <div class="form-group form-group-sm">
                    <?= form_label(lang('Items.is_serialized'), 'is_serialized', ['class' => 'control-label col-xs-3']) ?>
                    <div class="col-xs-1">
                        <?= form_checkbox([
                            'name'    => 'is_serialized',
                            'id'      => 'is_serialized',
                            'value'   => 1,
                            'checked' => $item_info->is_serialized == 1
                        ]) ?>
                    </div>
                </div>

                <?php if ($config['multi_pack_enabled'] == '1') { ?>
                    <div class="form-group form-group-sm">
                        <?= form_label(lang('Items.qty_per_pack'), 'qty_per_pack', ['class' => 'control-label col-xs-3']) ?>
                        <div class="col-xs-4">
                            <?= form_input([
                                'name'  => 'qty_per_pack',
                                'id'    => 'qty_per_pack',
                                'class' => 'form-control input-sm',
                                'value' => isset($item_info->item_id) ? to_quantity_decimals($item_info->qty_per_pack) : to_quantity_decimals(0)
                            ]) ?>
                        </div>
                    </div>
                    <div class="form-group form-group-sm">
                        <?= form_label(lang('Items.pack_name'), 'name', ['class' => 'control-label col-xs-3']) ?>
                        <div class="col-xs-8">
                            <?= form_input([
                                'name'  => 'pack_name',
                                'id'    => 'pack_name',
                                'class' => 'form-control input-sm',
                                'value' => $item_info->pack_name
                            ]) ?>
                        </div>
                    </div>
                    <div class="form-group  form-group-sm">
                        <?= form_label(lang('Items.low_sell_item'), 'low_sell_item_name', ['class' => 'control-label col-xs-3']) ?>
                        <div class="col-xs-8">
                            <div class="input-group input-group-sm">
                                <?= form_input([
                                    'name'  => 'low_sell_item_name',
                                    'id'    => 'low_sell_item_name',
                                    'class' => 'form-control input-sm',
                                    'value' => $selected_low_sell_item
                                ]) ?>
                                <?= form_hidden('low_sell_item_id', $selected_low_sell_item_id) ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <div class="form-group form-group-sm">
                    <?= form_label(lang('Items.is_deleted'), 'is_deleted', ['class' => 'control-label col-xs-3']) ?>
                    <div class="col-xs-1">
                        <?= form_checkbox([
                            'name'    => 'is_deleted',
                            'id'      => 'is_deleted',
                            'value'   => 1,
                            'checked' => $item_info->deleted == 1
                        ]) ?>
                    </div>
                </div>

            </div>
        </div>
        <?php endif; // !$quick_mode — stock_type/item_type/taxes/etc. são defaults no cadastro rápido ?>

    </fieldset>

    <?php if ($has_price_history_tab): ?>
    <div class="tab-pane" id="item_price_history" style="padding: 10px 5px;">
        <div class="row">
            <div class="col-xs-12">
                <p class="text-muted" style="font-size: 12px; margin-bottom: 12px;">
                    <?= lang('Items.price_history_hint') ?>
                </p>
                <?php if (empty($price_history)): ?>
                    <div class="alert alert-info" style="font-size: 13px;">
                        <?= lang('Items.price_history_empty') ?>
                    </div>
                <?php else: ?>
                    <table class="table table-striped table-condensed table-hover" style="font-size: 12px;">
                        <thead>
                            <tr>
                                <th><?= lang('Common.date') ?></th>
                                <th><?= lang('Items.cost_price') ?></th>
                                <th><?= lang('Items.unit_price') ?></th>
                                <th><?= lang('Items.price_history_source') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($price_history as $history_row): ?>
                            <?php
                                $prev_cost = $history_row['previous_cost_price'];
                                $curr_cost = $history_row['cost_price'];
                                $cost_changed = ($prev_cost !== null && (string)$prev_cost !== '' && (string)$prev_cost !== (string)$curr_cost);

                                $prev_unit = $history_row['previous_unit_price'];
                                $curr_unit = $history_row['unit_price'];
                                $unit_changed = ($prev_unit !== null && (string)$prev_unit !== '' && (string)$prev_unit !== (string)$curr_unit);

                                $source_label = match($history_row['source'] ?? '') {
                                    'cadastro' => 'Cadastro / Edição',
                                    'recebimento' => 'Recebimento',
                                    'baseline' => 'Inicial',
                                    default => !empty($history_row['source']) ? ucfirst($history_row['source']) : 'Sistema'
                                };
                            ?>
                            <tr>
                                <td style="white-space: nowrap;">
                                    <?= date('d/m/Y H:i', strtotime($history_row['changed_at'])) ?>
                                </td>
                                <td>
                                    <?php if ($cost_changed): ?>
                                        <span class="text-muted" style="text-decoration: line-through;"><?= to_currency($prev_cost) ?></span>
                                        <span class="glyphicon glyphicon-arrow-right" style="font-size: 10px; color: #888;"></span>
                                    <?php endif; ?>
                                    <strong><?= to_currency($curr_cost) ?></strong>
                                </td>
                                <td>
                                    <?php if ($unit_changed): ?>
                                        <span class="text-muted" style="text-decoration: line-through;"><?= to_currency($prev_unit) ?></span>
                                        <span class="glyphicon glyphicon-arrow-right" style="font-size: 10px; color: #888;"></span>
                                    <?php endif; ?>
                                    <strong style="color: var(--os-primary, #1a9c6a);"><?= to_currency($curr_unit) ?></strong>
                                </td>
                                <td>
                                    <span class="label label-default" style="font-weight: normal;"><?= esc($source_label) ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (!empty($price_history_truncated)): ?>
                        <p class="text-muted" style="font-size: 11px; text-align: right;">
                            <?= lang('Items.price_history_limited') ?>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    </div><!-- /.tab-content -->
<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        $('#new').click(function() {
            let stay_open = true;
            $('#item_form').submit();
        });

        $('#submit').click(function() {
            let stay_open = false;
        });

        // Rotate the caret when the "Mais opções" section opens/closes
        $('#item_extra_fields').on('show.bs.collapse', function() {
            $('#item_extra_caret').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
        }).on('hidden.bs.collapse', function() {
            $('#item_extra_caret').removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
        });

        $("input[name='tax_category']").change(function() {
            !$(this).val() && $(this).val('');
        });

        var fill_tax_category_value = function(event, ui) {
            event.preventDefault();
            $("input[name='tax_category_id']").val(ui.item.value);
            $("input[name='tax_category']").val(ui.item.label);
        };

        $('#tax_category').autocomplete({
            source: "<?= 'taxes/suggestTaxCategories' ?>",
            minChars: 0,
            delay: 15,
            cacheLength: 1,
            appendTo: '.modal-content',
            select: fill_tax_category_value,
            focus: fill_tax_category_value
        });

        var fill_low_sell_value = function(event, ui) {
            event.preventDefault();
            $("input[name='low_sell_item_id']").val(ui.item.value);
            $("input[name='low_sell_item_name']").val(ui.item.label);
        };

        $('#low_sell_item_name').autocomplete({
            source: "<?= 'items/suggestLowSell' ?>",
            minChars: 0,
            delay: 15,
            cacheLength: 1,
            appendTo: '.modal-content',
            select: fill_low_sell_value,
            focus: fill_low_sell_value
        });

        $('#category').autocomplete({
            source: "<?= 'items/suggestCategory' ?>",
            delay: 10,
            appendTo: '.modal-content'
        });

        $('a.fileinput-exists').click(function() {
            $.ajax({
                type: 'GET',
                url: '<?= "$controller_name/removeLogo/$item_info->item_id" ?>',
                dataType: 'json'
            })
        });

        // ── Galeria multi-foto ───────────────────────────────────────────
        var GALLERY_MAX = 10;
        var gallery_item_id = parseInt($('#item_gallery').data('item-id'), 10) || 0;

        var gallery_count = function() {
            return $('#item_gallery .item-gallery-item').length;
        };

        var render_gallery = function(resp) {
            if (resp && resp.main_url) {
                var $main = $('a.form-item-img').first();
                $main.attr('data-img-view', resp.main_url);
                $main.find('img').attr('src', resp.main_url);
            }
            var $g = $('#item_gallery').empty();
            (resp && resp.photos ? resp.photos : []).forEach(function(p) {
                $g.append(
                    '<div class="item-gallery-item" data-pic-id="' + p.item_pic_id + '">' +
                        '<a href="#" class="form-item-img" data-img-view="' + p.url + '" data-img-title=""><img src="' + p.url + '" alt=""></a>' +
                        '<button type="button" class="btn btn-xs btn-default js-set-main" title="Definir como principal">&#9733;</button>' +
                        '<button type="button" class="btn btn-xs btn-danger js-del-pic" title="Remover">&times;</button>' +
                    '</div>'
                );
            });
        };

        // Reduz as fotos no próprio navegador (máx. 1600px, JPEG 85%) para que
        // várias fotos caibam no post_max_size do PHP — o app Android faz o
        // mesmo no cliente. Sem suporte a canvas/DataTransfer, envia o original.
        var downscale_images = function(fileList, done) {
            var files = Array.prototype.slice.call(fileList);
            var canvas_ok = false;
            try { canvas_ok = !!document.createElement('canvas').toBlob; } catch (e) {}
            if (!window.File || !window.DataTransfer || !canvas_ok) { done(files); return; }

            var out = new DataTransfer();
            var i = 0;
            var next = function() {
                if (i >= files.length) { done(out.files); return; }
                var f = files[i++];
                var img = new Image();
                var url = URL.createObjectURL(f);
                img.onload = function() {
                    var w = img.width, h = img.height, MAX = 1600;
                    if (w > MAX || h > MAX) {
                        if (w >= h) { h = Math.round(h * MAX / w); w = MAX; }
                        else { w = Math.round(w * MAX / h); h = MAX; }
                    }
                    var canvas = document.createElement('canvas');
                    canvas.width = w; canvas.height = h;
                    canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                    URL.revokeObjectURL(url);
                    canvas.toBlob(function(blob) {
                        var name = f.name.replace(/\.[^.]+$/, '') + '.jpg';
                        try { out.items.add(new File([blob], name, { type: 'image/jpeg' })); }
                        catch (e) { out.items.add(f); }
                        next();
                    }, 'image/jpeg', 0.85);
                };
                img.onerror = function() { URL.revokeObjectURL(url); out.items.add(f); next(); };
                img.src = url;
            };
            next();
        };

        // Pré-visualização das fotos escolhidas (sobem junto com o formulário)
        $('input[name="items_images[]"]').on('change', function() {
            var input = this;
            var files = input.files || [];
            $('#item_gallery_pending').remove();
            if (files.length === 0) { return; }

            var room = GALLERY_MAX - gallery_count();
            if (files.length > room) {
                $.notify({ message: 'Limite de ' + GALLERY_MAX + ' fotos. As excedentes foram ignoradas.' }, { type: 'danger', timer: 3500 });
            }
            var chosen = Array.prototype.slice.call(files, 0, Math.max(0, room));

            downscale_images(chosen, function(processed) {
                try { input.files = processed; } catch (e) {}
                var $pending = $('<div id="item_gallery_pending" class="item-gallery"></div>');
                for (var i = 0; i < processed.length; i++) {
                    $pending.append('<div class="item-gallery-item pending"><img src="' + URL.createObjectURL(processed[i]) + '" alt=""></div>');
                }
                $('#item_gallery_group').append($pending);
            });
        });

        // Remover foto extra: marca para exclusão no submit (o arquivo só é
        // apagado quando o formulário é salvo, evitando perda por cancelamento).
        $('#item_gallery').on('click', '.js-del-pic', function() {
            var $item = $(this).closest('.item-gallery-item');
            var picId = $item.data('pic-id');
            if (!picId || gallery_item_id <= 0) { return; }
            $('#item_gallery_removed').append('<input type="hidden" name="remove_item_pic[]" value="' + picId + '">');
            $item.remove();
        });

        // Definir como principal: aplica no servidor imediatamente.
        $('#item_gallery').on('click', '.js-set-main', function() {
            var picId = $(this).closest('.item-gallery-item').data('pic-id');
            if (!picId || gallery_item_id <= 0) { return; }
            $.post('<?= "$controller_name/setMainImage" ?>/' + picId, { item_id: gallery_item_id }, function(resp) {
                if (resp && resp.success) {
                    render_gallery(resp);
                    $.notify({ message: 'Foto principal atualizada.' }, { type: 'success', timer: 2000 });
                } else {
                    $.notify({ message: 'Não foi possível definir a foto principal.' }, { type: 'danger', timer: 2500 });
                }
            }, 'json');
        });

        $.validator.addMethod('valid_chars', function(value, element) {
            return value.match(/(\||_)/g) == null;
        }, "<?= lang('Attributes.attribute_value_invalid_chars') ?>");

        var init_validation = function() {
            $('#item_form').validate($.extend({
                submitHandler: function(form, event) { // Event is not used as a parameter here
                    if (!dialog_support.intent()) {
                        return false;
                    }
                    $(form).ajaxSubmit({
                        success: function(response) {
                            let stay_open = dialog_support.clicked_id() != 'submit';
                            // Rascunho salvo com sucesso: limpa o localStorage deste item
                            if (typeof response !== 'undefined' && response !== null && response.success) {
                                window.POSItemDraft && POSItemDraft.successSaved();
                            }
                            if (stay_open) {
                                // Set action of item_form to url without item id, so a new one can be created
                                $('#item_form').attr('action', "<?= 'items/save/' ?>");
                                // Use a whitelist of fields to minimize unintended side effects
                                $(':text, :password, :file, #description, #item_form').not('.quantity, #reorder_level, #tax_name_1, #receiving_quantity, ' +
                                    '#tax_percent_name_1, #category, #reference_number, #name, #cost_price, #unit_price, #taxed_cost_price, #taxed_unit_price, #definition_name, [name^="attribute_links"]').val('');
                                // De-select any checkboxes, radios and drop-down menus
                                $(':input', '#item_form').removeAttr('checked').removeAttr('selected');
                                // The item was just saved as a new one: the chips
                                // of the previous product must not carry over.
                                $('#extra_codes_list').empty();
                                $('#extra_code_input').val('');
                                $('#extra_code_msg').text('');
                            } else {
                                dialog_support.hide();
                            }
                            table_support.handle_submit('<?= 'items' ?>', response, stay_open);
                            init_validation();
                            if (typeof window.initExtraCodes === 'function') {
                                window.initExtraCodes();
                            }
                        },
                        error: function() {
                            $('#submit').prop('disabled', false).css('opacity', '');
                        },
                        dataType: 'json'
                    });
                },

                errorLabelContainer: '#error_message_box',

                rules: {
                    name: 'required',
                    category: 'required',
                    item_number: {
                        required: false,
                        remote: {
                            url: "<?= esc("$controller_name/checkItemNumber") ?>",
                            type: 'POST',
                            data: {
                                'item_id': "<?= $item_info->item_id ?>"
                                // item_number should be passed into the function by default
                            }
                        }
                    },
                    cost_price: {
                        required: true,
                        remote: "<?= esc("$controller_name/checkNumeric") ?>"
                    },
                    unit_price: {
                        required: true,
                        remote: "<?= esc("$controller_name/checkNumeric") ?>"
                    },
                    <?php foreach ($stock_locations as $key => $location_detail) { ?>
                        <?= 'quantity_' . $key ?>: {
                            required: true,
                            remote: "<?= esc("$controller_name/checkNumeric") ?>"
                        },
                    <?php } ?>
                    receiving_quantity: {
                        required: true,
                        remote: "<?= esc("$controller_name/checkNumeric") ?>"
                    },
                    reorder_level: {
                        required: true,
                        remote: "<?= esc("$controller_name/checkNumeric") ?>"
                    },
                    tax_percent: {
                        required: false,
                        remote: "<?= esc("$controller_name/checkNumeric") ?>"
                    }
                },

                messages: {
                    name: "<?= lang('Items.name_required') ?>",
                    item_number: "<?= lang('Items.item_number_duplicate') ?>",
                    category: "<?= lang('Items.category_required') ?>",
                    cost_price: {
                        required: "<?= lang('Items.cost_price_required') ?>",
                        number: "<?= lang('Items.cost_price_number') ?>"
                    },
                    unit_price: {
                        required: "<?= lang('Items.unit_price_required') ?>",
                        number: "<?= lang('Items.unit_price_number') ?>"
                    },
                    <?php foreach ($stock_locations as $key => $location_detail) { ?>
                        <?= esc("quantity_$key", 'js') ?>: {
                            required: "<?= lang('Items.quantity_required') ?>",
                            number: "<?= lang('Items.quantity_number') ?>"
                        },
                    <?php } ?>
                    receiving_quantity: {
                        required: "<?= lang('Items.quantity_required') ?>",
                        number: "<?= lang('Items.quantity_number') ?>"
                    },
                    reorder_level: {
                        required: "<?= lang('Items.reorder_level_required') ?>",
                        number: "<?= lang('Items.reorder_level_number') ?>"
                    },
                    tax_percent: {
                        number: "<?= lang('Items.tax_percent_number') ?>"
                    }
                }
            }, form_support.error))
        };

        // Calculadora de markup: custo <-> % <-> preço de venda.
        // Bidirecional e ao vivo: o campo que está sendo digitado NUNCA é
        // sobrescrito — ele recalcula os outros dois.
        //   digitar %      => recalcula o preço
        //   digitar preço  => recalcula a %
        //   digitar custo  => recalcula o preço (se há %), senão a %
        (function() {
            var $cost_price = $('#cost_price');
            var $unit_price = $('#unit_price');
            var $markup_percent = $('#markup_percent');

            if ($cost_price.length === 0 || $unit_price.length === 0 || $markup_percent.length === 0) {
                return;
            }

            var MONEY_DECIMALS = <?= (int) ($config['currency_decimals'] ?? 2) ?>;

            var parse_amount = function(value) {
                var text = String(value === undefined || value === null ? '' : value).replace(/[^\d.,-]/g, '');
                if (text === '' || text === '-' || text === '.' || text === ',') {
                    return null;
                }
                // Aceita "1.234,56" e "1234.56" (o sistema aceita os dois separadores).
                if (text.indexOf(',') !== -1 && text.indexOf('.') !== -1) {
                    text = text.replace(/\./g, '').replace(',', '.');
                } else {
                    text = text.replace(',', '.');
                }
                var number = parseFloat(text);
                return isNaN(number) ? null : number;
            };

            var round_money = function(number) {
                var factor = Math.pow(10, MONEY_DECIMALS);
                return Math.round(number * factor) / factor;
            };

            // Preço com os centavos de verdade: R$ 10,37 continua 10,37.
            // O arredondamento para inteiro (Math.round antigo) engessava o preço.
            var format_money = function(number) {
                return round_money(number).toFixed(MONEY_DECIMALS);
            };

            // Porcentagem sem zeros à direita: 50 em vez de 50.00, 33.33 continua 33.33.
            var format_percent = function(number) {
                return (Math.round(number * 100) / 100).toFixed(2).replace(/\.?0+$/, '');
            };

            var price_for = function(cost, percent) {
                var price = round_money(cost * (1 + percent / 100));
                return price < 0 ? 0 : price;
            };

            var percent_for = function(cost, price) {
                // Preço zero significa "sem preço de venda definido", não
                // markup de -100%. Mostrar 0 evita que o campo abra com -100
                // e que digitar nele zere o preço (cost * (1 + -100/100) = 0).
                if (price === 0) {
                    return 0;
                }
                return (price / cost - 1) * 100;
            };

            // source: 'cost' | 'price' | 'percent' = campo sendo editado;
            // 'init' = só preenche o que falta (porcentagem atual do produto).
            var recalculate = function(source) {
                var cost = parse_amount($cost_price.val());
                var percent = parse_amount($markup_percent.val());
                var price = parse_amount($unit_price.val());

                // Sem custo informado não há markup que faça sentido
                // (produto com custo 0,00 fica sem porcentagem mesmo).
                if (cost === null || cost <= 0) {
                    return;
                }

                if (source === 'percent') {
                    if (percent === null) {
                        return;
                    }
                    $unit_price.val(format_money(price_for(cost, percent)));
                    return;
                }

                if (source === 'price') {
                    if (price === null) {
                        return;
                    }
                    $markup_percent.val(format_percent(percent_for(cost, price)));
                    return;
                }

                // Editar o CUSTO nunca reescreve o preço de venda.
                // Recalcular o preço aqui aplicava uma % que foi calculada
                // sobre o custo ANTIGO: mudar o custo de 6,03 para 10,00 num
                // item com 40,96% inventava um preço de 14,10, apagando o
                // preço de venda real (8,50). E num item com preço 0,00 a %
                // inicial é 0, então digitar o custo fazia o preço virar o
                // próprio custo — lucro zero sem nenhum aviso. O preço de
                // venda é decisão do dono, não efeito colateral do custo.
                // O que faz sentido ao editar o custo é o inverso: mostrar a
                // porcentagem que o preço atual representa sobre o novo custo.
                if (source === 'cost') {
                    if (price !== null && price > 0) {
                        $markup_percent.val(format_percent(percent_for(cost, price)));
                    }
                    return;
                }

                // init: mostra a porcentagem que o preço atual já representa,
                // em todo produto aberto para edição (pedido do dono).
                // percent_for() já trata price === 0 retornando 0 (nunca -100).
                if (source === 'init') {
                    if (percent === null && price !== null) {
                        $markup_percent.val(format_percent(percent_for(cost, price)));
                    }
                }
            };

            var recalculate_price = function(source) {
                recalculate(source);
                $unit_price.valid();
            };

            // 'input' (e não só 'change') para responder enquanto digita.
            $markup_percent.on('input', function() { recalculate_price('percent'); })
                .on('change', function() { recalculate_price('percent'); });
            $cost_price.on('input', function() { recalculate_price('cost'); });
            $unit_price.on('input', function() { recalculate('price'); });

            recalculate('init');
        })();

        // Cadastro rápido de fornecedor dentro do modal do produto.
        (function() {
            var $panel = $('#supplier_quick_add');
            if ($panel.length === 0) {
                return;
            }

            var $msg = $('#supplier_quick_add_msg');
            var $name = $('#supplier_new_name');

            var clear_panel = function() {
                $('#supplier_new_name, #supplier_new_tax_id, #supplier_new_phone').val('');
                $msg.text('');
            };

            var show_message = function(text, color) {
                $msg.css('color', color).text(text);
            };

            $('#supplier_quick_add_toggle').on('click', function(e) {
                e.preventDefault();
                $panel.toggle();
                if ($panel.is(':visible')) {
                    $name.trigger('focus');
                }
            });

            $('#supplier_quick_add_cancel').on('click', function() {
                $panel.hide();
                clear_panel();
            });

            var save_supplier_panel = function() {
                var company_name = $.trim($name.val());

                if (company_name === '') {
                    show_message("<?= esc(lang('Items.supplier_new_name_required'), 'js') ?>", '#a94442');
                    $name.trigger('focus');
                    return;
                }

                var $button = $('#supplier_quick_add_save').prop('disabled', true);
                show_message("<?= esc(lang('Items.supplier_new_saving'), 'js') ?>", '#8a6d3b');

                // ospos_people/ospos_suppliers têm colunas NOT NULL sem default:
                // o formulário normal do OSPOS manda strings vazias nos campos
                // não preenchidos, então replicamos isso aqui (getPost() devolve
                // null quando o campo não vai no POST).
                var data = {
                    first_name: company_name,
                    last_name: '',
                    gender: '',
                    email: '',
                    phone_number: $.trim($('#supplier_new_phone').val()),
                    address_1: '',
                    address_2: '',
                    city: '',
                    state: '',
                    zip: '',
                    country: '',
                    comments: '',
                    company_name: company_name,
                    agency_name: '',
                    category: 0,  // 0 = fornecedor de mercadorias (GOODS_SUPPLIER)
                    account_number: '',
                    tax_id: $.trim($('#supplier_new_tax_id').val())
                };

                $.post('suppliers/save', data, null, 'text')
                    .done(function(response) {
                        var parsed = null;
                        if (typeof response === 'object') {
                            parsed = response;
                        } else {
                            // Tolera HTML extra colado na resposta (debugbar em
                            // ambiente de desenvolvimento) — recorta o JSON.
                            var text = String(response || '');
                            var start = text.indexOf('{');
                            var end = text.lastIndexOf('}');
                            if (start !== -1 && end > start) {
                                try {
                                    parsed = JSON.parse(text.substring(start, end + 1));
                                } catch (e) {
                                    parsed = null;
                                }
                            }
                        }

                        var new_id = parsed && parsed.success ? parsed.id : null;

                        if (!new_id) {
                            show_message("<?= esc(lang('Items.supplier_new_failed'), 'js') ?>", '#a94442');
                            return;
                        }

                        var $select = $('#supplier_id');
                        $select.append($('<option>').val(new_id).text(company_name));
                        $select.val(new_id).trigger('change');
                        $panel.hide();
                        clear_panel();

                        if ($.notify) {
                            $.notify(
                                { message: "<?= esc(lang('Items.supplier_new_success'), 'js') ?> " + company_name },
                                { type: 'success', timer: 3000 }
                            );
                        }
                    })
                    .fail(function() {
                        show_message("<?= esc(lang('Items.supplier_new_failed'), 'js') ?>", '#a94442');
                    })
                    .always(function() {
                        $button.prop('disabled', false);
                    });
            };

            $('#supplier_quick_add_save').on('click', save_supplier_panel);

            // Enter dentro do painel de fornecedor salva o FORNECEDOR, não o produto.
            // O hotkey do Bootstrap-Dialog escuta 'keyup' no modal e submeteria o item;
            // por isso paramos a propagação e chamamos o salvamento do painel.
            $('#supplier_new_name, #supplier_new_tax_id, #supplier_new_phone')
                .on('keydown.supplierQuickAdd', function(e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        save_supplier_panel();
                    }
                })
                .on('keyup.supplierQuickAdd', function(e) {
                    if (e.which === 13) {
                        e.stopPropagation();
                    }
                });
        })();

        init_validation();

        if (typeof window.initExtraCodes === 'function') {
            window.initExtraCodes();
        }
    });

    // --- Códigos extras (ospos_item_codes): qualquer um resolve o produto ---
    window.initExtraCodes = function() {
        var $list = $('#extra_codes_list');
        var $input = $('#extra_code_input');
        var $msg = $('#extra_code_msg');

        function setMsg(text, color) {
            $msg.text(text || '').css('color', color || '');
        }

        function codesInList() {
            var codes = [];
            $list.find('input[type=hidden][name="extra_codes[]"]').each(function() {
                codes.push($(this).val());
            });
            return codes;
        }

        function addChip(code) {
            var $chip = $('<span class="label label-default js-extra-code-chip"></span>')
                .css({ display: 'inline-block', margin: '0 4px 4px 0', fontSize: '12px', padding: '4px 8px' })
                .text(code);
            $('<input type="hidden" name="extra_codes[]">').val(code).appendTo($chip);
            $('<a href="#" class="js-extra-code-remove"></a>')
                .css({ marginLeft: '6px', color: '#fff', cursor: 'pointer', fontWeight: 'bold' })
                .attr('title', "<?= esc(lang('Items.extra_code_remove'), 'js') ?>")
                .html('&times;')
                .appendTo($chip);
            $chip.appendTo($list);
        }

        function tryAdd() {
            var raw = String($input.val() || '').trim();

            if (raw === '') {
                setMsg("<?= esc(lang('Items.extra_code_invalid'), 'js') ?>", '#a94442');
                return;
            }

            var existing = codesInList();
            var lower = raw.toLowerCase();
            for (var i = 0; i < existing.length; i++) {
                if (String(existing[i]).toLowerCase() === lower) {
                    setMsg("<?= esc(lang('Items.extra_code_duplicate'), 'js') ?>", '#a94442');
                    return;
                }
            }

            addChip(raw);
            $input.val('').focus();
            setMsg('');
        }

        $('#extra_code_add').off('click.extraCodes').on('click.extraCodes', tryAdd);
        $input.off('keydown.extraCodes').on('keydown.extraCodes', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                tryAdd();
            }
        });
        // O hotkey do Bootstrap-Dialog escuta 'keyup' no modal (nao 'keydown'), entao
        // preventDefault no keydown nao basta: ele chama form.submit() por click programatico
        // no botao 'Enviar'. Barrar o keyup aqui impede o submit sem afetar outros modais.
        $input.off('keyup.extraCodes').on('keyup.extraCodes', function(e) {
            if (e.which === 13) {
                e.stopPropagation();
            }
        });

        $list.off('click.extraCodes').on('click.extraCodes', '.js-extra-code-remove', function(e) {
            e.preventDefault();
            $(this).closest('.js-extra-code-chip').remove();
        });

        // Códigos já gravados vêm do controller (item_codes), renderizados como chips.
        $list.empty();
        var existingCodes = <?= json_encode(array_column($item_codes ?? [], 'code')) ?>;
        for (var j = 0; j < existingCodes.length; j++) {
            addChip(existingCodes[j]);
        }
    };

    // --- Gerar código interno aleatório (produtos sem código de barras) ---
    // Charset sem ambíguos (O/0, I/1, L/1); 5 chars → 32^5 ≈ 33 milhões de combinações.
    var GERAR_CHARSET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    function gerarCodigoInterno() {
        var code = '';
        for (var i = 0; i < 5; i++) {
            code += GERAR_CHARSET.charAt(Math.floor(Math.random() * GERAR_CHARSET.length));
        }
        return code;
    }

    $('#barcode_generate').on('click', function() {
        var $field = $('#item_number');
        $field.val(gerarCodigoInterno());
        $field.trigger('change'); // re-valida remote (checkItemNumber) p/ feedback imediato de duplicado
        $.notify({ message: 'Código gerado. Clique novamente se já existir.' }, { type: 'info', timer: 2500 });
    });
</script>

<?php if (!$quick_mode): ?>
<script type="text/javascript">
    // Rascunho automático estilo Google Forms: salva o formulário em localStorage
    // a cada alteração (debounce) e restaura ao reabrir o modal, para não perder
    // o trabalho se o modal fechar por acidente (clique fora do modal, ESC, X).
    // O rascunho é limpo quando o item é salvo com sucesso, e o usuário pode
    // descartá-lo pelo aviso amarelo "Rascunho restaurado".
    (function() {
        'use strict';

        var ITEM_ID = <?= (int)$item_info->item_id ?>;
        var KEY = 'ospos_item_draft_' + (ITEM_ID > 0 ? ITEM_ID : 'new');
        var $root = $('#item_form');
        var data = null;      // rascunho parseado {g, ts}
        var suspend = false;  // após salvar com sucesso, não rascunhar mais
        var armed = false;    // houve edição nesta abertura → salvar ao fechar
        var discardSuppressUntil = 0;  // janela pós-descartar: ignora events disparados pelo clear
        var timer = null;

        function collect($scope) {
            var groups = {};
            $scope.find(':input').not('[type=file]').each(function() {
                var $el = $(this), name = $el.attr('name');
                if (!name) { return; }
                if ($el.is(':radio') || $el.is(':checkbox')) {
                    (groups[name] = groups[name] || []).push({v: $el.val(), c: $el.is(':checked')});
                } else {
                    (groups[name] = groups[name] || []).push({v: $el.val()});
                }
            });
            return groups;
        }

        function apply(groups, $scope) {
            if (!groups) { return; }
            var idx = {};
            $scope.find(':input').not('[type=file]').each(function() {
                var $el = $(this), name = $el.attr('name');
                if (!name || !groups[name]) { return; }
                var entry = groups[name][idx[name] = (idx[name] || 0)];
                if (entry === undefined) { return; }
                idx[name]++;
                if (entry.c !== undefined) {
                    $el.prop('checked', !!entry.c);
                } else {
                    $el.val(entry.v);
                }
            });
        }

        function hhmm(ts) {
            var d = ts ? new Date(ts) : new Date();
            return ('0' + d.getHours()).slice(-2) + ':' + ('0' + d.getMinutes()).slice(-2);
        }

        function saveNow() {
            if (suspend) { return; }
            try {
                localStorage.setItem(KEY, JSON.stringify({g: collect($root), ts: Date.now()}));
            } catch (e) { /* localStorage indisponível — segue sem rascunho */ }
        }

        function saveSoon() {
            if (Date.now() < discardSuppressUntil) { return; }  // após descartar, eventos de clear não re-salvam
            armed = true;
            clearTimeout(timer);
            timer = setTimeout(saveNow, 800);
        }

        function clearFields() {
            $('#item_form').find(':input').not('[type=file], [type=hidden], [name=submit], [type=submit], [type=button]').each(function() {
                var $el = $(this);
                if ($el.is(':radio') || $el.is(':checkbox')) {
                    $el.prop('checked', false);
                } else if ($el.is('select')) {
                    $el.prop('selectedIndex', 0);  // volta ao placeholder/primeira opção
                } else {
                    $el.val('');
                }
            });
        }

        function restore() {
            data = null;
            try {
                var raw = localStorage.getItem(KEY);
                if (raw) { data = JSON.parse(raw); }
            } catch (e) { data = null; }
            if (!data || !data.g) {
                data = null;
                return;
            }
            apply(data.g, $root);
            $('#item_draft_notice').show()
                .find('.item_draft_when').text('Rascunho restaurado (salvo às ' + hhmm(data.ts) + ')');
        }

        window.POSItemDraft = {
            successSaved: function() {
                suspend = true;
                try { localStorage.removeItem(KEY); } catch (e) { /* noop */ }
                data = null;
                $('#item_draft_notice').hide();
            },
            saveSoon: function() {
                if (Date.now() < discardSuppressUntil) { return; }  // após descartar, eventos de clear não re-salvam
                armed = true;
                clearTimeout(timer);
                timer = setTimeout(saveNow, 800);
            },
            discard: function() {
                armed = false;
                clearTimeout(timer);
                try { localStorage.removeItem(KEY); } catch (e) { /* noop */ }
                data = null;
                $('#item_draft_notice').hide();
                // Suprime o re-salvamento durante a limpeza — senão o estado limpo
                // viraria um rascunho novo em cima do que acabou de ser descartado.
                discardSuppressUntil = Date.now() + 1200;
                if (ITEM_ID > 0 && $root[0]) {
                    // EDIÇÃO (aberta pela grid, pela busca ou pelo scanner): descartar
                    // devolve os valores que vieram do banco. Esvaziar o formulário
                    // aqui apagaria o cadastro na tela — se o usuário enviasse em
                    // seguida, salvaria um item sem nome nem preço.
                    $root[0].reset();
                } else {
                    // Item novo: limpa TODOS os campos (usuário pediu).
                    clearFields();
                }
            },
            restore: restore
        };

        $(function() {
            var DRAFT = window.POSItemDraft;

            // Salva a cada alteração (debounce)
            $('#item_form').on('change input', ':input', function() { DRAFT.saveSoon(); });

            // Descarta o rascunho (mantém aviso oculto e não restaura mais nesta abertura)
            $('#item_draft_discard').on('click', function(e) {
                e.preventDefault();
                DRAFT.discard();
            });

            // Ao fechar o modal (clique fora / ESC / X), grava pendências na hora
            $(document).on('hidden.bs.modal.posdraft', function(e) {
                if ($root[0] && $(e.target).has($root[0]).length) {
                    clearTimeout(timer);
                    if (armed && !suspend) { saveNow(); }
                }
            });

            DRAFT.restore();
        });
    })();
</script>
<?php endif; // !$quick_mode — rascunho desativado no cadastro rápido (não pode sobrescrever a categoria default) ?>