<?php
/**
 * @var array $allowed_modules
 */
?>

<?= view('partial/header') ?>

<div class="home-container">
    <h1 class="home-title"><?= lang('Common.welcome_message') ?></h1>
    <p class="home-subtitle">Sistema PDV - Ponto de Venda</p>
    
    <div class="home-buttons">
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

<?= view('partial/footer') ?>
