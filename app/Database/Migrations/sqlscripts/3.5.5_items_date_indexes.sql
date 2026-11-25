-- Índices para os contadores do dashboard (Home) e o delta sync da loja.
-- get_created_today_count / get_updated_today_count filtram created_at e
-- last_modified a cada carga do /home; fetch_items_total (inventory-service)
-- filtra last_modified no sync incremental. Sem índice = full scan em
-- ospos_items (~10k linhas) a cada chamada.
ALTER TABLE `ospos_items` ADD INDEX `idx_items_last_modified` (`last_modified`);
ALTER TABLE `ospos_items` ADD INDEX `idx_items_created_at` (`created_at`);
