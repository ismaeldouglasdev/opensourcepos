<?php
/**
 * @var array $allowed_modules
 */
?>

<?= view('partial/header') ?>

<style>
.home-container {
    max-width: 600px;
    margin: 40px auto;
    text-align: center;
}

.home-buttons {
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
    margin-top: 40px;
}

.home-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 160px;
    height: 140px;
    border-radius: 15px;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.home-btn:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
}

.home-btn-resumo {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.home-btn-vendas {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.home-btn-itens {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

.home-btn-icon {
    font-size: 48px;
    margin-bottom: 10px;
}

.home-btn-text {
    font-size: 18px;
    font-weight: bold;
}

.home-title {
    font-size: 32px;
    color: #333;
    margin-bottom: 10px;
}

.home-subtitle {
    font-size: 16px;
    color: #666;
}
</style>

<div class="home-container">
    <h1 class="home-title"><?= lang('Common.welcome_message') ?></h1>
    <p class="home-subtitle">Sistema PDV - Ponto de Venda</p>
    
    <div class="home-buttons">
        <a href="<?= base_url('sales/manage') ?>" class="home-btn home-btn-resumo">
            <span class="home-btn-icon">📊</span>
            <span class="home-btn-text">RESUMO</span>
        </a>
        <a href="<?= base_url('sales/add') ?>" class="home-btn home-btn-vendas">
            <span class="home-btn-icon">💰</span>
            <span class="home-btn-text">VENDAS</span>
        </a>
        <a href="<?= base_url('items') ?>" class="home-btn home-btn-itens">
            <span class="home-btn-icon">📦</span>
            <span class="home-btn-text">ITENS</span>
        </a>
    </div>
</div>

<?= view('partial/footer') ?>
