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
