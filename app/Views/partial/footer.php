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
</body>

</html>
