-- 3.5.4: Parcelas (installments) nos pagamentos de venda (16/nov/2026).
-- Metadado na linha de pagamento: 1 = a vista, maior que 1 = pago em N parcelas.
ALTER TABLE `ospos_sales_payments` ADD COLUMN IF NOT EXISTS `installments` TINYINT UNSIGNED NOT NULL DEFAULT 1;
