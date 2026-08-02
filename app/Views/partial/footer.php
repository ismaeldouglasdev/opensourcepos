<?php

use Config\OSPOS;

?>

        </div>
    </div>

    <div id="footer">
        <div class="jumbotron push-spaces">
            <strong>
                <?= lang('Common.copyrights', [date('Y')]) ?> ·
                <a href="https://opensourcepos.org" target="_blank"><?= lang('Common.website') ?></a> ·
                <?= esc(config('App')->application_version) ?> -
                <a target="_blank" href="https://github.com/opensourcepos/opensourcepos/commit/<?= esc(config(OSPOS::class)->commit_sha1) ?>">
                    <?= esc(substr(config(OSPOS::class)->commit_sha1, 0, 6)); ?>
                </a>
            </strong>.
        </div>
    </div>

    <script>
    // Beeps sonoros via WebAudio (sem arquivo externo)
    var beep = (function() {
        var ctx = null;
        var ensure = function() {
            if (!ctx) {
                var AC = window.AudioContext || window.webkitAudioContext;
                if (!AC) return null;
                ctx = new AC();
            }
            if (ctx.state === 'suspended') ctx.resume();
            return ctx;
        };
        var tone = function(freq, ms, when) {
            var c = ensure();
            if (!c) return;
            var t = c.currentTime + (when || 0);
            var o = c.createOscillator();
            var g = c.createGain();
            o.type = 'sine';
            o.frequency.value = freq;
            g.gain.setValueAtTime(0.0001, t);
            g.gain.exponentialRampToValueAtTime(0.25, t + 0.01);
            g.gain.exponentialRampToValueAtTime(0.0001, t + ms / 1000);
            o.connect(g);
            g.connect(c.destination);
            o.start(t);
            o.stop(t + ms / 1000 + 0.05);
        };
        return {
            // item adicionado ao carrinho / leitura ok
            ok: function() { tone(1200, 90); },
            // venda finalizada com sucesso
            success: function() { tone(880, 120); tone(1175, 140, 0.12); },
            // erro / ação bloqueada
            error: function() { tone(220, 220); }
        };
    })();
    </script>

    <script>
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F2') {
            var currentPath = window.location.pathname;
            if (currentPath.indexOf('/sales') === -1) {
                e.preventDefault();
                window.location.href = '<?= site_url('sales') ?>';
            }
        }
    });
    </script>

    <script>
    (function() {
        var GR_URL = '<?= site_url('guardrail/js-error') ?>';
        var lastReport = 0;
        var report = function(data) {
            var now = Date.now();
            if (now - lastReport < 1000) return;
            lastReport = now;
            data.url = data.url || window.location.href;
            data.ua = navigator.userAgent;
            try {
                if (navigator.sendBeacon) {
                    var fd = new FormData();
                    for (var k in data) {
                        if (data.hasOwnProperty(k)) fd.append(k, String(data[k]));
                    }
                    navigator.sendBeacon(GR_URL, fd);
                } else {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', GR_URL, true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.send(URLSearchParams ? new URLSearchParams(data).toString() : 'msg=' + encodeURIComponent(data.message));
                }
            } catch (e) {}
        };
        window.addEventListener('error', function(e) {
            report({
                message: String(e.message || '').slice(0, 500),
                source: e.filename || '',
                line: e.lineno || 0,
                col: e.colno || 0,
                stack: String(e.error && e.error.stack ? e.error.stack : '').slice(0, 1500)
            });
        });
        window.addEventListener('unhandledrejection', function(e) {
            var r = e.reason;
            report({
                message: 'unhandledrejection: ' + String(r && r.message ? r.message : r).slice(0, 500),
                source: '',
                line: 0,
                col: 0,
                stack: String(r && r.stack ? r.stack : '').slice(0, 1500)
            });
        });
    })();
    </script>
</body>

</html>
