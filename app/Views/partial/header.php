<?php
/**
 * @var object $user_info
 * @var array $allowed_modules
 * @var CodeIgniter\HTTP\IncomingRequest $request
 * @var array $config
 */

use Config\Services;

$request = Services::request();
?>

<!doctype html>
<html lang="<?= $request->getLocale() ?>">

<head>
    <meta charset="utf-8">
    <base href="<?= base_url() ?>">
    <title><?= esc($config['company']) . ' | ' . lang('Common.powered_by') . ' OSPOS ' . esc(config('App')->application_version) ?></title>
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="stylesheet" href="<?= 'resources/bootswatch/' . (empty($config['theme']) ? 'flatly' : esc($config['theme'])) . '/bootstrap.min.css' ?>">

    <?php if (ENVIRONMENT == 'development' || get_cookie('debug') == 'true' || $request->getGet('debug') == 'true') : ?>
        <!-- inject:debug:css -->
        <link rel="stylesheet" href="resources/css/jquery-ui-fe010342cb.css">
        <link rel="stylesheet" href="resources/css/bootstrap-dialog-1716ef6e7c.css">
        <link rel="stylesheet" href="resources/css/jasny-bootstrap-40bf85f3ed.css">
        <link rel="stylesheet" href="resources/css/bootstrap-datetimepicker-66374fba71.css">
        <link rel="stylesheet" href="resources/css/bootstrap-select-66d5473b84.css">
        <link rel="stylesheet" href="resources/css/bootstrap-table-ed9d1a3360.css">
        <link rel="stylesheet" href="resources/css/bootstrap-table-sticky-header-07d65e7533.css">
        <link rel="stylesheet" href="resources/css/daterangepicker-85523b7dfe.css">
        <link rel="stylesheet" href="resources/css/chartist-c19aedb81a.css">
        <link rel="stylesheet" href="resources/css/chartist-plugin-tooltip-2e0ec92e60.css">
        <link rel="stylesheet" href="resources/css/bootstrap-tagsinput-5a6d46a06c.css">
        <link rel="stylesheet" href="resources/css/bootstrap-toggle-e12db6c1f3.css">
        <link rel="stylesheet" href="resources/css/bootstrap-4875cf7b0d.autocomplete.css">
        <link rel="stylesheet" href="resources/css/invoice-a99a4dfac3.css">
        <link rel="stylesheet" href="resources/css/ospos_print-bf10c1438b.css">
        <link rel="stylesheet" href="resources/css/ospos-d0b91fdf8f.css">
        <link rel="stylesheet" href="resources/css/popupbox-57d45cb822.css">
        <link rel="stylesheet" href="resources/css/receipt-0606f1c54e.css">
        <link rel="stylesheet" href="resources/css/register-a6a6cc948d.css">
        <link rel="stylesheet" href="resources/css/reports-ace7faf688.css">
        <!-- endinject -->
        <!-- inject:debug:js -->
        <script src="resources/js/jquery-12e87d2f3a.js"></script>
        <script src="resources/js/jquery-4fa896f615.form.js"></script>
        <script src="resources/js/jquery-a0350e8820.validate.js"></script>
        <script src="resources/js/jquery-ui-cbc65ff85e.js"></script>
        <script src="resources/js/bootstrap-894d79839f.js"></script>
        <script src="resources/js/bootstrap-dialog-27123abb65.js"></script>
        <script src="resources/js/jasny-bootstrap-7c6d7b8adf.js"></script>
        <script src="resources/js/bootstrap-datetimepicker-25e39b7ef8.js"></script>
        <script src="resources/js/bootstrap-select-b01896a67b.js"></script>
        <script src="resources/js/bootstrap-table-bdb06552ea.js"></script>
        <script src="resources/js/bootstrap-table-export-6389dc2aa5.js"></script>
        <script src="resources/js/bootstrap-table-mobile-fc655b68ab.js"></script>
        <script src="resources/js/bootstrap-table-sticky-header-cb4d83d172.js"></script>
        <script src="resources/js/moment-d65dc6d2e6.min.js"></script>
        <script src="resources/js/daterangepicker-048c56a690.js"></script>
        <script src="resources/js/es6-promise-855125e6f5.js"></script>
        <script src="resources/js/FileSaver-e73b1946e8.js"></script>
        <script src="resources/js/html2canvas-e1d3a8d7cd.js"></script>
        <script src="resources/js/jspdf-bbbebb610c.umd.js"></script>
        <script src="resources/js/purify-d160df429f.js"></script>
        <script src="resources/js/jspdf-92d87e47e8.plugin.autotable.js"></script>
        <script src="resources/js/tableExport-3d506dfa61.min.js"></script>
        <script src="resources/js/chartist-8a7ecb4445.js"></script>
        <script src="resources/js/chartist-plugin-pointlabels-0a1ab6aa4e.js"></script>
        <script src="resources/js/chartist-plugin-tooltip-116cb48831.js"></script>
        <script src="resources/js/chartist-plugin-axistitle-80a1198058.js"></script>
        <script src="resources/js/chartist-plugin-barlabels-4165273742.js"></script>
        <script src="resources/js/bootstrap-notify-376bc6eb87.js"></script>
        <script src="resources/js/bootstrap-tagsinput-855a7c7670.js"></script>
        <script src="resources/js/bootstrap-toggle-1c7a19a049.js"></script>
        <script src="resources/js/clipboard-908af414ab.js"></script>
        <script src="resources/js/imgpreview-1db063409f.full.jquery.js"></script>
        <script src="resources/js/manage_tables-e5dae00ba1.js"></script>
        <script src="resources/js/nominatim-89be77a11a.autocomplete.js"></script>
        <!-- endinject -->
    <?php else : ?>
        <!--inject:prod:css -->
        <link rel="stylesheet" href="resources/opensourcepos-8f45024eca.min.css">
        <!-- endinject -->

        <!-- Tweaks to the UI for a particular theme should drop here  -->
        <?php if ($config['theme'] != 'flatly' && file_exists($_SERVER['DOCUMENT_ROOT'] . '/public/css/' . esc($config['theme']) . '.css')) { ?>
            <link rel="stylesheet" href="<?= 'css/' . esc($config['theme']) . '.css' ?>">
        <?php } ?>
        <!-- inject:prod:js -->
        <script src="resources/jquery-2c872dbe60.min.js"></script>
        <script src="resources/opensourcepos-0c4b48a0bf.min.js"></script>
        <!-- endinject -->
    <?php endif; ?>

    <?= view('partial/header_js') ?>
    <?= view('partial/lang_lines') ?>

    <style>
        html {
            overflow: auto;
        }
    </style>

    <!-- CSS Acessível para Idosos -->
    <link rel="stylesheet" href="<?= base_url('resources/css/accessible.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/custom.css') ?>">
</head>

<body>
    <?php if (ENVIRONMENT === 'development'): ?>
    <style>
    .test-banner {
        background: #28a745; color: white; text-align: center; padding: 6px; font-weight: bold; position: fixed; top: 0; left: 0; right: 0; z-index: 9999;
    }
    .topbar {   padding-top: 32px;    }
    </style>
    <div class="test-banner">AMBIENTE DE TESTE</div>
    <?php endif; ?>
    <div class="wrapper">
        <div class="topbar">
            <div class="container">
                <div class="navbar-left">
                    <div id="liveclock"><?= date($config['dateformat'] . ' ' . $config['timeformat']) ?></div>
                </div>

                <div class="navbar-right" style="margin: 0;">
                    <?= anchor("home/changePassword/$user_info->person_id", "$user_info->first_name $user_info->last_name", ['class' => 'modal-dlg', 'data-btn-submit' => lang('Common.submit'), 'title' => lang('Employees.change_password')]) ?>
                    <span>&nbsp;|&nbsp;</span>
                    <?= anchor('home/logout', lang('Login.logout')) ?>
                </div>

                <div class="navbar-center" style="text-align: center;">
                    <strong><?= esc($config['company']) ?></strong>
                </div>
            </div>
        </div>

        <div class="navbar navbar-default" role="navigation">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
                        <span class="sr-only">Menu</span>
                        <span style="font-size: 20px;">☰</span>
                    </button>
                </div>

                <div class="navbar-collapse collapse">
                    <ul class="nav navbar-nav">
                        <?php
                        $current = $request->getUri()->getSegment(1);
                        $current_url = current_url();
                        $is_home = ($current == 'home');
                        $is_resumo = ($current == 'sales' && strpos($current_url, 'sales/manage') !== false);
                        $is_vendas = ($current == 'sales' && (strpos($current_url, 'sales/add')!==false || strpos($current_url, 'sales/register')!==false));
                        $is_itens = ($current == 'items');
                        $is_clientes = ($current == 'customers');
                        ?>
                        <li class="<?=$is_home ? 'active': ' ' ?>">
                            <a href="<?=base_url('home') ?>" style="font-size: 16px;">
                                🏠 HOME
                            </a>
                        </li>
                        <li class="<?=$is_resumo ? 'active' : ' ' ?>">
                            <a href="<?=base_url('sales/manage') ?>" style="font-size: 16px;">
                            📊 RESUMO
                            </a>
                        </li>
                        <li class="<?=$is_vendas ? 'active' : ' ' ?>">
                            <a href="<?=base_url('sales/add') ?>" style="font-size: 16px;">
                            💰 VENDAS
                            </a>
                        </li>
                        <li class="<?=$is_itens ? 'active' : ' ' ?> ">
                            <a href="<?=base_url('items') ?>" style="font-size: 16px;">
                                📦 ITENS
                            </a>
                        </li>
                        <li class="<?= $is_clientes ? 'active' : ' ' ?>">
                            <a href="<?=base_url('customers')?>" style="font-size: 16px;">
                                👥 CLIENTES
                            </a>
                        </li>
                    </ul>

                    <!-- Menu Hamburguer-->
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="font-size: 20px; padding: 10px;">
                            ☰ <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="<?=site_url('items')?>"><span class="glyphicon glyphicon-tag"></span> Produtos </a></li>
                                <li><a href="<?=site_url('customers')?>"><span class="glyphicon glyphicon-user"></span> Clientes</a></li>
                                <li><a href="<?=site_url('employees')?>"><span class="glyphicon glyphicon-user"></span> Funcionários</a></li>
                                <li class="divider"></li>
                                <li><a href="<?=site_url('sales/manage') ?>"><span class="glyphicon glyphicon-list-alt"></span> Vendas</a></li>
                                <li><a href="<?=site_url('reports')?>"><span class="glyphicon glyphicon-stats"></span> Relatórios</a></li>
                                <li class="divider"></li>
                                <li><a href="<?=site_url('config') ?>"><span class="glyphicon glyphicon-cog"></span> Configurações</a></li>
                                <li><a href="<?=site_url('sales/suspended') ?>"><span class="glyphicon glyphicon-pause"></span> Vendas Suspensas</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row">
