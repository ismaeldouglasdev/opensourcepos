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
        var recent = {};
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
        // Falha HTTP de AJAX: falhas de rede ou status >= 400 (ex.: 404 de rota
        // quebrada). NAO disparam window.onerror -> o modal "morre" em silencio.
        var ajaxFailure = function(method, target, status) {
            if (!target || target.indexOf('/guardrail/') !== -1) return;
            var key = method + '|' + target + '|' + status;
            var now = Date.now();
            if (recent[key] && now - recent[key] < 30000) return;
            recent[key] = now;
            report({
                kind: 'ajax',
                message: 'ajax ' + (status || 'NET') + ' ' + String(method || '?').toUpperCase() + ' ' + target,
                source: '',
                line: 0,
                col: 0,
                stack: '',
                method: method || '',
                status: status || 0,
                target: target
            });
        };
        window.addEventListener('error', function(e) {
            report({
                kind: 'js',
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
                kind: 'js',
                message: 'unhandledrejection: ' + String(r && r.message ? r.message : r).slice(0, 500),
                source: '',
                line: 0,
                col: 0,
                stack: String(r && r.stack ? r.stack : '').slice(0, 1500)
            });
        });

        // ---- Deteccao de XHR/fetch que falharam ----
        try {
            var XHR = window.XMLHttpRequest;
            if (XHR) {
                var origOpen = XHR.prototype.open;
                var origSend = XHR.prototype.send;
                XHR.prototype.open = function(method, url) {
                    this.__gr_method = method;
                    this.__gr_url = url;
                    return origOpen.apply(this, arguments);
                };
                XHR.prototype.send = function() {
                    var self = this;
                    var fired = false;
                    var fire = function(status) {
                        if (fired) return;
                        fired = true;
                        if (status === 0 || status >= 400) {
                            ajaxFailure(self.__gr_method || '?', self.__gr_url || '', status);
                        }
                    };
                    if (this.addEventListener) {
                        this.addEventListener('load', function() { fire(self.status); });
                        this.addEventListener('error', function() { fire(0); });
                        this.addEventListener('timeout', function() { fire(0); });
                    }
                    return origSend.apply(this, arguments);
                };
            }
            var origFetch = window.fetch;
            if (origFetch) {
                window.fetch = function(input, init) {
                    var method = (init && init.method) || (input && input.method) || 'GET';
                    var url = typeof input === 'string' ? input : (input && input.url) || '';
                    return origFetch.apply(this, arguments).then(function(res) {
                        if (!res.ok) ajaxFailure(method, url, res.status);
                        return res;
                    }).catch(function(err) {
                        ajaxFailure(method, url, 0);
                        throw err;
                    });
                };
            }
        } catch (e) {}
    })();
    </script>
</body>

</html>
