<?php
/**
 * @var string $title
 * @var string $subtitle
 * @var string $chart_type
 * @var array $summary_data_1
 */
?>

<?= view('partial/header') ?>

<script type="text/javascript">
    dialog_support.init("a.modal-dlg");
</script>

<div id="page_title"><?= esc($title) ?></div>

<div id="page_subtitle"><?= esc($subtitle) ?></div>

<button id="export_csv" class="btn btn-primary btn-sm" type="button">
    <?= lang('Common.export_csv') ?>
</button>

<div class="ct-chart ct-golden-section" id="chart1"></div>

<?= view($chart_type) ?>

<div id="chart_report_summary">
    <?php foreach ($summary_data_1 as $name => $value) { ?>
        <div class="summary_row"><?= lang("Reports.$name") . ': ' . to_currency($value) ?></div>
    <?php } ?>
</div>

<?= view('partial/footer') ?>

<script type="text/javascript">
    document.getElementById('export_csv').addEventListener('click', function() {
        var xTitle = <?= json_encode(esc($xaxis_title ?? '', 'js')) ?>;
        var yTitle = <?= json_encode(esc($yaxis_title ?? '', 'js')) ?>;
        var labels = <?= json_encode(esc($labels_1 ?? [], 'js')) ?> || [];
        var series = <?= json_encode(esc($series_data_1 ?? [], 'js')) ?> || [];

        function valueOf(entry) {
            return typeof entry === 'object' && entry !== null && 'value' in entry ? entry.value : entry;
        }

        function csvCell(value) {
            var s = String(value).replace(/"/g, '""');
            return /[";\n]/.test(s) ? '"' + s + '"' : s;
        }

        var lines = [];
        lines.push(csvCell(xTitle) + ';' + csvCell(yTitle));
        for (var i = 0; i < labels.length; i++) {
            var raw = valueOf(series[i]);
            var num = Number(raw);
            var formatted = isNaN(num) ? String(raw) : num.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            lines.push(csvCell(labels[i]) + ';' + csvCell(formatted));
        }

        var blob = new Blob(['\uFEFF' + lines.join('\r\n')], { type: 'text/csv;charset=utf-8' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = <?= json_encode(esc($title ?? 'report', 'js')) ?> + '.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    });
</script>
