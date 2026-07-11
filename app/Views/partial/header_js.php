<?php
/**
 * @var array $config
 */
?>

<script type="text/javascript">
    // Live clock
    var clock_tick = function clock_tick() {
        setInterval('update_clock();', 1000);
    }

    // Start the clock immediately
    clock_tick();

    var update_clock = function update_clock() {
        var dias = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
        var agora = new Date();
        document.getElementById('liveclock').innerHTML = dias[agora.getDay()] + ', ' + moment().format("<?= dateformat_momentjs($config['dateformat'] . ' ' . $config['timeformat']) ?>");
    }

    $.notifyDefaults({
        placement: {
            align: "<?= esc($config['notify_horizontal_position'], 'js') ?>",
            from: "<?= esc($config['notify_vertical_position'], 'js') ?>"
        }
    });

    var cookie_name = "<?= esc(config('Cookie')->prefix, 'js') . esc(config('Security')->cookieName, 'js') ?>";

    var csrf_token = function() {
        return Cookies.get(cookie_name);
    };

    var csrf_form_base = function() {
        return {
            <?= esc(config('Security')->tokenName, 'js') ?>: csrf_token() || ''
        }
    };

    var setup_csrf_token = function() {
        var token = csrf_token();
        if (token) {
            $('input[name="<?= esc(config('Security')->tokenName, 'js') ?>"]').val(token);
        }
    };

    var ajax = $.ajax;

    $.ajax = function() {
        var args = arguments[0];
        if (args['type'] && args['type'].toLowerCase() == 'post' && csrf_token()) {
            if (typeof args['data'] === 'string') {
                args['data'] += '&' + $.param(csrf_form_base());
            } else {
                args['data'] = $.extend(args['data'], csrf_form_base());
            }
        }

        return ajax.apply(this, arguments);
    };

    $(document).ajaxComplete(setup_csrf_token);

    $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
        if (settings.dataType === 'json' && jqxhr.responseJSON === undefined) {
            try { JSON.parse(jqxhr.responseText); } catch(e) {
                console.warn('JSON.parse error suppressed for:', settings.url);
            }
        }
    });
    $(document).ready(function() {
        $("#logout").click(function(event) {
            event.preventDefault();
            $.ajax({
                url: "<?= site_url('home/logout'); ?>",
                data: csrf_form_base(),
                success: function() {
                    window.location.href = '<?= site_url(); ?>';
                },
                method: "POST"
            });
        });
    });

    var submit = $.fn.submit;

    $.fn.submit = function() {
        setup_csrf_token();
        submit.apply(this, arguments);
    };

    // Foco automático no campo de busca em páginas de listagem (Bootstrap-Table)
    $(document).ready(function() {
        setTimeout(function() {
            var searchInput = $('.bootstrap-table .search input, .fixed-table-toolbar .search input');
            if (searchInput.length) {
                searchInput.first().focus();
            }
        }, 500);
    });
</script>
