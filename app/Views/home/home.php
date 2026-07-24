<?php
/**
 * Dashboard Home View
 *
 * @var float  $today_sales
 * @var float  $yesterday_sales
 * @var float  $week_sales
 * @var float  $month_sales
 * @var int    $items_sold_today
 * @var array  $top_items
 * @var array  $stock_alerts
 * @var float  $pending_receivables
 * @var int    $stock_alert_count
 * @var array  $allowed_modules
 */

$pct_yesterday = $yesterday_sales > 0
    ? round((($today_sales - $yesterday_sales) / $yesterday_sales) * 100)
    : ($today_sales > 0 ? 100 : 0);
$arrow_today = $pct_yesterday >= 0 ? '▲' : '▼';
$class_today = $pct_yesterday >= 0 ? 'dash-badge-up' : 'dash-badge-down';

$dias = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
$day_name = $dias[(int)date('w')];
$date_str = $day_name . ', ' . date('d/m/Y');
?>

<?= view('partial/header') ?>

<style>
/* ============================================
   DASHBOARD
   ============================================ */
.dash-date {
    font-size: 13px;
    color: var(--os-text-muted);
    margin-bottom: 18px;
    font-weight: 500;
}

.dash-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 24px;
}

.dash-kpi {
    background: var(--os-surface);
    border: 1px solid var(--os-border);
    border-radius: var(--os-radius-lg);
    padding: 18px 16px 14px;
    box-shadow: var(--os-shadow-sm);
    transition: box-shadow var(--os-transition);
    position: relative;
    overflow: hidden;
}

.dash-kpi:hover {
    box-shadow: var(--os-shadow);
}

.dash-kpi-icon {
    width: 36px;
    height: 36px;
    border-radius: var(--os-radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: #fff;
    margin-bottom: 12px;
}

.dash-kpi-icon.bg-primary  { background: var(--os-primary); }
.dash-kpi-icon.bg-success  { background: var(--os-success); }
.dash-kpi-icon.bg-info     { background: var(--os-info); }
.dash-kpi-icon.bg-warning  { background: var(--os-warning); }

.dash-kpi-value {
    font-size: 24px;
    font-weight: 700;
    color: var(--os-text);
    line-height: 1.1;
    margin-bottom: 4px;
    font-family: var(--os-font);
}

.dash-kpi-label {
    font-size: 12px;
    color: var(--os-text-muted);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.dash-badge {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 7px;
    border-radius: 20px;
    margin-top: 6px;
}

.dash-badge-up   { background: rgba(22,163,74,0.1); color: var(--os-success); }
.dash-badge-down { background: rgba(220,38,38,0.1); color: var(--os-danger); }
.dash-badge-neutral { background: rgba(107,114,128,0.1); color: var(--os-text-muted); }

/* Sections */
.dash-section {
    margin-bottom: 24px;
}

.dash-section-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--os-text);
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.dash-section-title .glyphicon {
    color: var(--os-text-muted);
    font-size: 14px;
}

/* Top Items Table */
.dash-top-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: var(--os-surface);
    border: 1px solid var(--os-border);
    border-radius: var(--os-radius-lg);
    overflow: hidden;
    box-shadow: var(--os-shadow-sm);
}

.dash-top-table thead th {
    background: var(--os-bg);
    border-bottom: 1px solid var(--os-border);
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--os-text-muted);
    padding: 10px 14px;
}

.dash-top-table tbody td {
    padding: 10px 14px;
    font-size: 13px;
    color: var(--os-text);
    border-bottom: 1px solid var(--os-border-light);
}

.dash-top-table tbody tr:last-child td {
    border-bottom: none;
}

.dash-top-table tbody tr:hover {
    background: var(--os-surface-hover);
}

.dash-rank {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    background: var(--os-primary-light);
    color: var(--os-primary);
}

.dash-empty {
    text-align: center;
    padding: 24px;
    color: var(--os-text-muted);
    font-size: 13px;
    background: var(--os-surface);
    border: 1px solid var(--os-border);
    border-radius: var(--os-radius-lg);
}

/* Stock Alerts */
.dash-alerts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 10px;
}

.dash-alert-card {
    background: var(--os-surface);
    border: 1px solid var(--os-border);
    border-radius: var(--os-radius);
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: var(--os-shadow-sm);
    transition: box-shadow var(--os-transition);
}

.dash-alert-card:hover {
    box-shadow: var(--os-shadow);
}

.dash-alert-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}

.dash-alert-dot.zerado   { background: var(--os-warning); }
.dash-alert-dot.irregular { background: var(--os-danger); }

.dash-alert-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--os-text);
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.dash-alert-status {
    font-size: 10px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    flex-shrink: 0;
}

.dash-alert-status.zerado {
    background: rgba(217,119,6,0.12);
    color: var(--os-warning);
}

.dash-alert-status.irregular {
    background: rgba(220,38,38,0.12);
    color: var(--os-danger);
}

.dash-alert-qty {
    font-size: 12px;
    color: var(--os-text-muted);
    font-weight: 500;
    flex-shrink: 0;
}

/* Quick Actions */
.dash-actions {
    display: flex;
    justify-content: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 8px;
}

/* Stock alert notification badge */
.dash-alert-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    border-radius: 10px;
    background: var(--os-danger);
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    margin-left: 4px;
    vertical-align: middle;
}

/* Responsive */
@media (max-width: 1024px) {
    .dash-kpi-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    .dash-kpi-grid {
        grid-template-columns: 1fr;
    }
    .dash-kpi-value {
        font-size: 20px;
    }
    .dash-alerts-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="home-container" style="max-width:900px !important;">

    <h1 class="home-title"><?= lang('Common.welcome_message') ?></h1>
    <p class="home-subtitle">Sistema PDV - Ponto de Venda</p>
    <p class="dash-date"><?= esc($date_str) ?></p>

    <?php if ($stock_alert_count > 0): ?>
    <div style="text-align:center;margin-bottom:16px;">
        <a href="<?= base_url('items') ?>" style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;background:rgba(220,38,38,0.08);color:var(--os-danger);font-size:12px;font-weight:600;text-decoration:none;transition:background 0.15s;">
            <span class="glyphicon glyphicon-alert"></span>
            <?= $stock_alert_count ?> <?= $stock_alert_count === 1 ? 'item com alerta de estoque' : 'itens com alerta de estoque' ?>
        </a>
    </div>
    <?php endif; ?>

    <!-- KPI Cards -->
    <div class="dash-kpi-grid">
        <div class="dash-kpi">
            <div class="dash-kpi-icon bg-success"><span class="glyphicon glyphicon-usd"></span></div>
            <div class="dash-kpi-value"><?= to_currency($today_sales) ?></div>
            <div class="dash-kpi-label">Vendas Hoje</div>
            <?php if ($yesterday_sales > 0 || $today_sales > 0): ?>
            <div class="dash-badge <?= $class_today ?>"><?= $arrow_today ?> <?= abs($pct_yesterday) ?>% vs ontem</div>
            <?php endif; ?>
        </div>

        <div class="dash-kpi">
            <div class="dash-kpi-icon bg-info"><span class="glyphicon glyphicon-calendar"></span></div>
            <div class="dash-kpi-value"><?= to_currency($week_sales) ?></div>
            <div class="dash-kpi-label">Vendas Semana</div>
        </div>

        <div class="dash-kpi">
            <div class="dash-kpi-icon bg-primary"><span class="glyphicon glyphicon-stats"></span></div>
            <div class="dash-kpi-value"><?= to_currency($month_sales) ?></div>
            <div class="dash-kpi-label">Vendas Mês</div>
        </div>

        <div class="dash-kpi">
            <div class="dash-kpi-icon bg-warning"><span class="glyphicon glyphicon-shopping-cart"></span></div>
            <div class="dash-kpi-value"><?= esc((string)$items_sold_today) ?></div>
            <div class="dash-kpi-label">Itens Vendidos Hoje</div>
        </div>
    </div>

    <?php if ($pending_receivables > 0): ?>
    <div style="text-align:center;margin-bottom:24px;">
        <div style="display:inline-flex;align-items:center;gap:8px;padding:8px 18px;border-radius:var(--os-radius);background:rgba(217,119,6,0.08);border:1px solid rgba(217,119,6,0.2);">
            <span class="glyphicon glyphicon-time" style="color:var(--os-warning);"></span>
            <span style="font-size:13px;color:var(--os-text-muted);">Pendente (Fiado):</span>
            <strong style="font-size:15px;color:var(--os-warning);"><?= to_currency($pending_receivables) ?></strong>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($top_items)): ?>
    <div class="dash-section">
        <div class="dash-section-title">
            <span class="glyphicon glyphicon-star"></span>
            Top 5 Itens da Semana
        </div>
        <table class="dash-top-table">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th>Item</th>
                    <th style="width:80px;text-align:right;">Qtd</th>
                    <th style="width:120px;text-align:right;">Receita</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top_items as $i => $item): ?>
                <tr>
                    <td><span class="dash-rank"><?= $i + 1 ?></span></td>
                    <td><?= esc($item->name) ?></td>
                    <td style="text-align:right;font-weight:600;"><?= esc((string)$item->qty) ?></td>
                    <td style="text-align:right;font-weight:600;"><?= to_currency($item->revenue) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="dash-section">
        <div class="dash-section-title">
            <span class="glyphicon glyphicon-star"></span>
            Top 5 Itens da Semana
        </div>
        <div class="dash-empty">
            <span class="glyphicon glyphicon-info-sign" style="margin-right:6px;"></span>
            Nenhuma venda registrada nesta semana.
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($stock_alerts)): ?>
    <div class="dash-section">
        <div class="dash-section-title">
            <span class="glyphicon glyphicon-exclamation-sign"></span>
            Alertas de Estoque
            <?php if ($stock_alert_count > 10): ?>
            <span style="font-size:12px;color:var(--os-text-muted);font-weight:400;">(mostrando 10 de <?= $stock_alert_count ?>)</span>
            <?php endif; ?>
        </div>
        <div class="dash-alerts-grid">
            <?php foreach ($stock_alerts as $alert): ?>
            <?php
                $is_zerado = ((int)$alert->stock_status === 1);
                $status_class = $is_zerado ? 'zerado' : 'irregular';
                $status_label = $is_zerado ? 'ZERADO' : 'IRREGULAR';
            ?>
            <div class="dash-alert-card">
                <span class="dash-alert-dot <?= $status_class ?>"></span>
                <span class="dash-alert-name" title="<?= esc($alert->name) ?>"><?= esc($alert->name) ?></span>
                <span class="dash-alert-qty">Qtd: <?= esc((string)$alert->quantity) ?></span>
                <span class="dash-alert-status <?= $status_class ?>"><?= $status_label ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="dash-section">
        <div class="dash-section-title">
            <span class="glyphicon glyphicon-flash"></span>
            Ações Rápidas
        </div>
        <div class="dash-actions">
            <a href="<?= base_url('sales/manage') ?>" class="home-btn home-btn-resumo">
                <span class="home-btn-icon glyphicon glyphicon-list-alt"></span>
                <span class="home-btn-text">RESUMO</span>
            </a>
            <a href="<?= base_url('sales/add') ?>" class="home-btn home-btn-vendas">
                <span class="home-btn-icon glyphicon glyphicon-shopping-cart"></span>
                <span class="home-btn-text">VENDAS</span>
            </a>
            <a href="<?= base_url('items') ?>" class="home-btn home-btn-itens">
                <span class="home-btn-icon glyphicon glyphicon-tag"></span>
                <span class="home-btn-text">ITENS</span>
            </a>
        </div>
    </div>

</div>

<?= view('partial/footer') ?>
