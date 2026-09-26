-- Tarefa 14 do plano simplificar-fluxo-venda-pdv
-- Adiciona o interruptor das setinhas de ajuda na ficha do employee.
--
-- default 1 = setinhas LIGADAS. Ao contrario do simple_mode (default 0), aqui o
-- padrao e ligado: o dono e quem liga e desliga, e desligar setinhas que
-- acabaram de ser instaladas nao faz sentido para ninguem.
--
-- Coluna separada de simple_mode de proposito. As duas opcoes nao se anulam:
-- o modo define o CAMINHO (a interface) e o interruptor define a BUSSOLA (as
-- setinhas). E o dono precisa poder treinar no Modo Completo COM setinhas.
--
-- NOTA: este fork nao roda migrations automaticamente. Em producao e teste
-- executar manualmente:
--   ALTER TABLE ospos_employees ADD COLUMN show_guide TINYINT NOT NULL DEFAULT 1 AFTER simple_mode;

ALTER TABLE `ospos_employees`
    ADD COLUMN `show_guide` TINYINT NOT NULL DEFAULT 1 AFTER `simple_mode`;
