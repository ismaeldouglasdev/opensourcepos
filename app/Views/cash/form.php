<?php
/**
 * @var object $cash_flow_info
 * @var array $employees
 * @var string $controller_name
 * @var array $config
 */
?>

<div id="required_fields_message"><?= lang('Common.fields_required_message') ?></div>
<ul id="error_message_box" class="error_message_box"></ul>

<?= form_open("$controller_name/save/$cash_flow_info->cash_flow_id", ['id' => 'cash_flow_edit_form', 'class' => 'form-horizontal']) ?>
    <fieldset>

        <div class="form-group form-group-sm">
            <?= form_label(lang('Cash.type'), 'type', ['class' => 'required control-label col-xs-3']) ?>
            <div class="col-xs-6">
                <?= form_dropdown('type', [\App\Models\Cash_flow::TYPE_SANGRIA => lang('Cash.type_sangria'), \App\Models\Cash_flow::TYPE_SUPRIMENTO => lang('Cash.type_suprimento')], $cash_flow_info->type, ['id' => 'type', 'class' => 'form-control']) ?>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('Cash.amount'), 'amount', ['class' => 'required control-label col-xs-3']) ?>
            <div class="col-xs-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon input-sm"><b><?= esc($config['currency_symbol']) ?></b></span>
                    <?= form_input([
                        'name'  => 'amount',
                        'id'    => 'amount',
                        'class' => 'form-control input-sm',
                        'value' => $cash_flow_info->amount > 0 ? to_currency_no_money($cash_flow_info->amount) : ''
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('Cash.note'), 'note', ['class' => 'control-label col-xs-3']) ?>
            <div class="col-xs-6">
                <?= form_textarea([
                    'name'  => 'note',
                    'id'    => 'note',
                    'class' => 'form-control input-sm',
                    'value' => $cash_flow_info->note ?? ''
                ]) ?>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('Cash.employee'), 'employee', ['class' => 'control-label col-xs-3']) ?>
            <div class="col-xs-6">
                <?= form_dropdown('employee_id', $employees, $cash_flow_info->employee_id, 'id="employee_id" class="form-control"') ?>
            </div>
        </div>

    </fieldset>
<?= form_close() ?>

<script type="text/javascript">
    $(document).ready(function() {
        $('#cash_flow_edit_form').validate($.extend({
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    success: function(response) {
                        dialog_support.hide();
                        table_support.handle_submit("<?= esc($controller_name) ?>", response);
                    },
                    dataType: 'json'
                });
            },

            errorLabelContainer: '#error_message_box',

            ignore: '',

            rules: {
                amount: {
                    required: true,
                    remote: "<?= "$controller_name/checkNumeric" ?>"
                }
            },

            messages: {
                amount: {
                    required: "<?= lang('Cash.amount_required') ?>",
                    remote: "<?= lang('Cash.amount_required') ?>"
                }
            }
        }, form_support.error));
    });
</script>
