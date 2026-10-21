# Agent Instructions

This document provides guidance for AI agents working on the Open Source Point of Sale (OSPOS) codebase.

## Code Style

- Follow PHP CodeIgniter 4 coding standards
- Run PHP-CS-Fixer before committing: `vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.no-header.php`
- Write PHP 8.1+ compatible code with proper type declarations
- Use PSR-12 naming conventions: `camelCase` for variables and functions, `PascalCase` for classes, `UPPER_CASE` for constants

## Development

- Create a new git worktree for each issue, based on the latest state of `origin/master`
- Commit fixes to the worktree and push to the remote

## Testing

- Run PHPUnit tests: `composer test`
- Tests must pass before submitting changes

## Build

- Install dependencies: `composer install && npm install`
- Build assets: `npm run build` or `gulp`

## Conventions

- Controllers go in `app/Controllers/`
- Models go in `app/Models/`
- Views go in `app/Views/`
- Database migrations in `app/Database/Migrations/`
- Use CodeIgniter 4 framework patterns and helpers
- Sanitize user input; escape output using `esc()` helper

## Security

- Never commit secrets, credentials, or `.env` files
- Use parameterized queries to prevent SQL injection
- Validate and sanitize all user input

---

## Session Context (2026-07-08)

### User
ismael — dono de pequeno comércio, usa OSPOS fork em produção.

### Environment
- **Fork repo:** `/home/ismael/opensourcepos-fork/`
- **Produção:** `/var/www/html/pos` — Apache porta 80, banco `ospos`
- **Teste:** `/var/www/html/pos-test` — Apache porta 8080, banco `ospos_test`
- **Deploy teste:** `sudo /home/ismael/deploy-test.sh` (rsync do repo para pos-test)
- **Docker dev:** `docker-compose.dev.yml` (porta 80 conflita com Apache)
- **PHP:** 8.3.6 (host), 8.2 (container Docker), 8.4 exigido por dev deps travadas

### Audit Status
Auditoria completa do sistema (174+ issues) já realizada. Relatório completo está no histórico.

### Fixed So Far (12 files, +110/-81 lines)

#### Critical (8/8)
- `Sale_lib.php:1213` — `return false` → `return true` (edit_item)
- `Barcode_lib.php:150` — aspas simples → concatenação
- `Item.php:204` — SQL injection via `having()` → `escapeLikeString()`
- `Item.php:362` — `orderBy('items.item_id')` adicionado
- `Item.php:380` — `groupStart/groupEnd` no orWhere
- `Item_kit.php:126` — `groupStart/groupEnd` no orWhere
- `Item_kit_items.php:29` — `groupStart/groupEnd` no orWhere
- `Dinner_table.php:60` — `groupStart/groupEnd` no orWhere

#### High (8/8)
- `Item.php:712-932` — 3 queries de atributos: join items + prefixo items. nas colunas
- `Security.php` — `tokenRandomize=true`, `regenerate=true`
- `Routes.php` — `setAutoRoute(false)`, todas rotas convertidas de add() para get/post
- `Database.php` — env() no construtor (hostname, username, password, database, DBDriver, DBPrefix, port)
- `header.php:24` — debug mode só com `ENVIRONMENT === 'development'`
- `.env.example` — senhas comentadas
- `register.php:933,967` — `event` → `e` (atalhos Alt+Num)
- `register.php:848` — handler Enter duplicado do carrinho removido

### Pending
- Deploy para teste: `sudo rsync -av --delete --exclude='.env' --exclude='.git' /home/ismael/opensourcepos-fork/ /var/www/html/pos-test/`
- Testar em http://localhost:8080

---

## Session Context (2026-10-20)

### Trabalho: códigos extras multi-barcode + markup honesto + Enter no modal

#### 1. `ospos_item_codes` — múltiplos códigos por produto
- Migration `20261020000000_CreateItemCodes` + `sqlscripts/3.5.1_item_codes.sql`
  (`CREATE TABLE IF NOT EXISTS`, InnoDB/utf8mb4, UNIQUE em `code`).
- `Item::get_info_by_id_or_number()` (l.351-380) — o **funil único** usado por vendas,
  recebimentos e scan. Subquery `item_id IN (SELECT item_id FROM ospos_item_codes WHERE code = ?)`.
- `Item::get_item_id_by_code()` (l.456-470), `resolve_code_to_item_id()`, `get_codes()`,
  `save_codes()` (normaliza/deduplica, ignora colisão com `item_number` de outro item).
- ⚠️ **ORDEM DE DEPLOY EM PROD**: a migration PRIMEIRO, o código DEPOIS. O funil compila a
  subquery — se o código subir antes da tabela, toda venda/scan dá erro no caixa.

#### 2. Markup — fim do recálculo destrutivo (bug relatado pelo dono)
- **Regra:** editar o CUSTO nunca reescreve o preço de venda. O que muda é só a % exibida.
- `app/Views/items/form.php` `recalculate()` — `source === 'cost'` não toca `$unit_price`.
- `init`: `price === 0` → markup = `'0'` (nunca `-100`, que zerava o preço ao digitar nele).
- `percent_for(cost, price)` retorna 0 quando `price === 0`.
- Repro: `_repro_markup_cost.py` (item 9956 cost=8/price=0 e item 44 cost=6,03/price=8,50).

#### 3. Enter no modal não salva mais sozinho
- **Root cause:** Bootstrap-Dialog escuta `keydown`/`keyup` no nível do modal
  (`bootstrap-dialog-*.js:1086-1097`) e, com key 13, faz `$button.focus().trigger('click')`
  no botão `hotkey:13`. `preventDefault()` no keydown NÃO cancela o keyup.
- **Fix:** handler `keyup.extraCodes` em `#extra_code_input` com `stopPropagation()` para key 13.
- ⚠️ **Armadilha:** o PRIMEIRO `.bootstrap-dialog-footer .btn` é o "Enviar" (submit). Fechar o
  modal pelo ✕ do header (`.bootstrap-dialog-close-button button.close`); clicar no footer SALVA.

#### 4. Guard em modais (`dialog_support`)
- 8 views ganharam o guard: `items`, `customers`, `suppliers`, `expenses`, `giftcards`,
  `item_kits`, `employees`, `cash`.

### Verificação
- `validate_extra_codes.py` — PASS (A1–D1 + Z1, zero pageerror).
- `validate_modal_features.py` — PASS (A/B/C/D/Z; inclui `test_discard.py` PASS).
- `validate_markup_zero.py` — 3/3 PASS (9956→'0', 9850→'-99.65', 44→'40.96').
- `validate_markup_typing.py` — 2/2 PASS (markup 0 → price 8.00; markup 50 → price 12.00).
- `php -l` 15 arquivos + `node --check` 2 bundles — OK. Estilo: sem TAB, sem trailing WS, sem `trans()` novo.
- Fixtures de teste limpos (`ospos_item_codes` = 0 rows); item 9956 e 44 restaurados ao baseline prod.

### Pendências
- `php-cs-fixer` **NÃO disponível** neste checkout (`vendor/bin/` vazio; é dev-dep nunca instalada).
  Rodar exige `composer install`. Checado por inspeção: PSR-12 conforme (4 espaços, visibilidade,
  docblocks). Instalar com aval do dono.
- Deploy PROD: backup → migration `20261020000000` verificada por `information_schema` → código →
  **restart do Apache** (opcache `validate_timestamps=0` exige restart, `reload` não pega view PHP).
- Hotkey-13 é pré-existente em ~21 botões de 12 views — fix foi narrow (só `#extra_code_input`);
  decisão de tratar os outros modais é do dono (`qa-evidence/hotkey13-pre-existing-20261020/FINDING.md`).
- Prova funnel-level via `_proof_funnel.php` bloqueada por bootstrap CLI (CI4 4.5+).
  `Config\Paths not found`; resolver com `Boot::bootConsole`/comando `spark`.
- Validar com usuário antes de subir para produção em /var/www/html/pos
---

## Session Context (2026-10-21)

### `php-cs-fixer` roda, mas NÃO é seguro aplicar em views com PHP misturado a HTML

O bloqueador histórico ("`vendor/bin/` vazio, dev-dep nunca instalada") foi resolvido: os 8
pacotes `symfony/*` do `composer.lock` exigiam **php >= 8.4** e o ambiente roda **PHP 8.3.6**,
então `composer install` falhava. Downgrade para a linha **v7.4 (LTS)** (`composer update
--no-scripts --no-install`) materializou `vendor/bin/php-cs-fixer 3.95.1`, que roda em 8.3.6.
Nenhuma outra dependência mudou; `codeigniter4/framework` permanece **v4.7.2** (conferido
antes/depois). Commit `45f38b99c`.

**O problema:** a ruleset carregada é `CodeIgniter\CodingStandard\CodeIgniter4` (via
`nexusphp/cs-config`, config `.php-cs-fixer.no-header.php`). Em **views** — arquivos com PHP
entremeado a HTML — ela produz reescrita **destrutiva**:

```php
// antes (válido)
<?php
// Category is always a dropdown...
echo form_dropdown('category', $categories, $selected_category, [...]);
?>
// o fixer quer:
<?=// Category is always a dropdown...
 form_dropdown('category', $categories, $selected_category, [...]);
?>
```

O comentário engole o resto da linha e os blocos de controle dentro de `<?php ?>`
(`if ($standard_item_locked)`, `match()`, etc.) são re-indentados como se fossem markup,
embaralhando o fluxo. **Nenhuma flag do fixer evita isso de forma confiável.**

**Regra:** em views (`app/Views/**`), **não** rodar o fixer de forma cega. Rodar `--dry-run` e
inspecionar; se o diff mexer em blocos PHP inline, **não aplicar** — manter o estilo das linhas
ao redor e validar com `php -l`. O fixer continua útil para `app/Controllers`, `app/Models`,
`app/Libraries`, `app/Helpers` (PHP puro).

### Enter no painel de fornecedor do modal de item (hotkey-13, mesma família do `#extra_code_input`)

Mesma raiz: o Bootstrap-Dialog escuta key 13 no nível do modal e clica no botão `hotkey:13`
(o "Enviar" do item). Ao apertar Enter dentro do painel "Novo fornecedor", o **form do item**
era submetido em vez de salvar o fornecedor. Fix espelha o `#extra_code_input`: extrai o handler
de clique para `save_supplier_panel()` e o associa aos três campos do painel com
`keydown` (`preventDefault`) + `keyup` (`stopPropagation`).

### Múltiplos códigos por produto — ordem de deploy em PROD

Ver §Seção 2026-10-20 item 1: a migration `ospos_item_codes` **primeiro**, o código depois. O
funil de resolução (`get_info_by_id_or_number`) compila subquery contra essa tabela; subir o
código antes faz toda venda/scan quebrar no caixa.
