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
- Validar com usuário antes de subir para produção em /var/www/html/pos