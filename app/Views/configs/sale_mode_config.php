<?php
/**
 * Secao "Modo de venda" em Configuracoes.
 *
 * Tarefa 4 do plano simplificar-fluxo-venda-pdv. O flag pertence ao employee
 * logado, nunca a loja: o dono atende sozinho no mesmo terminal, entao um
 * valor global obrigaria a ligar e desligar todo dia.
 *
 * @var bool $simple_mode  estado atual do employee logado
 * @var string $controller_name
 *
 * Sao dois forms porque sao duas preferencias independentes, com endpoints
 * separados. O modo define o CAMINHO (a interface) e o interruptor define a
 * BUSSOLA (as setinhas) — nao se anulam, e o dono precisa poder treinar no
 * Modo Completo com as setinhas ligadas.
 */
?>
<?= form_open('config/saveSimpleMode/', ['id' => 'simple_mode_config_form', 'class' => 'form-horizontal']) ?>
    <div id="config_wrapper">
        <fieldset id="config_info">

            <div id="required_fields_message"><?= lang('Common.fields_required_message') ?></div>
            <ul id="simple_mode_error_message_box" class="error_message_box"></ul>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Config.simple_mode_label'), 'simple_mode', ['class' => 'control-label col-xs-2']) ?>
                <div class="col-xs-1">
                    <?= form_checkbox([
                        'name'    => 'simple_mode',
                        'value'   => '1',
                        'id'      => 'simple_mode',
                        'checked' => $simple_mode === true
                    ]) ?>
                </div>
                <div class="col-xs-9">
                    <span class="help-block"><?= lang('Config.simple_mode_help') ?></span>
                </div>
            </div>

        </fieldset>

        <fieldset class="form-buttons">
            <div class="form-group form-group-sm">
                <div class="col-sm-offset-2 col-sm-10">
                    <button class="btn btn-primary" type="submit" name="submit" value="submit">
                        <?= lang('Common.submit') ?>
                    </button>
                </div>
            </div>
        </fieldset>
    </div>
<?= form_close() ?>


<script type="text/javascript">
    // Mesmo padrao das outras abas de Config: o form_support faz ajaxSubmit e
    // mostra a resposta com $.notify. Sem isto o POST seria um submit comum e a
    // tela trocaria pelo JSON cru.
    $('#simple_mode_config_form').validate($.extend(form_support.handler, {
        submitHandler: function(form) {
            $(form).ajaxSubmit({
                success: function(response) {
                    $.notify({
                        message: response.message
                    }, {
                        type: response.success ? 'success' : 'danger',
                        placement: { from: 'top', align: 'center' }
                    });
                },
                dataType: 'json'
            });
        }
    }));
</script>
