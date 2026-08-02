<?php
/**
 * @var array $debtors
 * @var float $total_devido_geral
 */
?>

<?= view('partial/header') ?>

<div id="title_bar" class="print_hide btn-toolbar">
    <h4 style="margin:0;font-weight:700;">📝 Fiado — Lista de Devedores</h4>
</div>

<div class="debtor-summary">
    <div class="debtor-summary-item">
        <span class="debtor-summary-label">Total a Receber</span>
        <span class="debtor-summary-value"><?= to_currency($total_devido_geral) ?></span>
    </div>
</div>

<div id="table_holder">
    <table class="table table-striped table-bordered debtor-table">
        <thead>
            <tr>
                <th style="width:50px;">#</th>
                <th>Cliente</th>
                <th style="width:110px;text-align:center;">Vendas Fiado</th>
                <th style="width:160px;text-align:right;">Total Devido</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($debtors)): ?>
            <tr>
                <td colspan="4" style="text-align:center;color:var(--os-text-muted);padding:24px;">
                    Nenhum cliente com fiado em aberto.
                </td>
            </tr>
            <?php else: ?>
                <?php foreach ($debtors as $i => $debtor): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td>
                        <a href="<?= site_url('sales/manage?customer=' . (int)$debtor->customer_id) ?>" title="Ver vendas do cliente">
                            <?= esc($debtor->customer_name) ?>
                        </a>
                    </td>
                    <td style="text-align:center;"><?= (int)$debtor->sale_count ?></td>
                    <td style="text-align:right;font-weight:700;color:var(--os-danger);"><?= to_currency($debtor->total_devido) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <?php if (!empty($debtors)): ?>
        <tfoot>
            <tr style="font-weight:700;">
                <td colspan="3" style="text-align:right;">Total Geral</td>
                <td style="text-align:right;color:var(--os-danger);"><?= to_currency($total_devido_geral) ?></td>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>
</div>

<style>
.debtor-summary { display:flex; gap:14px; flex-wrap:wrap; margin-bottom:16px; }
.debtor-summary-item { flex:1; min-width:220px; max-width:340px; background:var(--os-surface); border:1px solid var(--os-border); border-radius:var(--os-radius-lg); padding:14px 16px; box-shadow:var(--os-shadow-sm); display:flex; flex-direction:column; gap:4px; }
.debtor-summary-label { font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:var(--os-text-muted); }
.debtor-summary-value { font-size:22px; font-weight:700; color:var(--os-danger); }
.debtor-table tbody tr:hover { background:var(--os-surface-muted, #f8fafc); }
</style>

<?= view('partial/footer') ?>
