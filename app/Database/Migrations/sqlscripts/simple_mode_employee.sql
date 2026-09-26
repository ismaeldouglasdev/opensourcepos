-- Tarefa 1 do plano simplificar-fluxo-venda-pdv
-- Adiciona o flag de Modo Simples na ficha do employee.
--
-- default 0 = Modo Completo. Ninguem tem o caixa mudado de surpresa:
-- so muda depois que alguem ligar explicitamente em Configuracoes.
--
-- NOTA: este fork nao roda migrations automaticamente. Em producao e teste
-- executar manualmente:
--   ALTER TABLE ospos_employees ADD COLUMN simple_mode TINYINT NOT NULL DEFAULT 0 AFTER deleted;

ALTER TABLE `ospos_employees`
    ADD COLUMN `simple_mode` TINYINT NOT NULL DEFAULT 0 AFTER `deleted`;
