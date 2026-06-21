# OSPOS - Bugs Encontrados

> Data: 06/05/2026
> Projeto: Open Source Point of Sale (master)
> PHP: 8.5.6 — CodeIgniter: 4.7.2 — MariaDB: 12.2.2

---

## 🔴 Corrigidos (já resolvidos)

### 1. `Customers.php` — Código duplicado no CSV Import
- **Arquivo:** `app/Controllers/Customers.php` (linhas 516–543)
- **Problema:** Bloco de código duplicado após fechamento correto da função `csvImport()`, gerando syntax error (`unexpected token "else"`).
- **Fix:** Removido bloco duplicado.
- **Status:** ✅ Corrigido

### 2. `Services.php` — Faltando import da classe `Locale`
- **Arquivo:** `app/Config/Services.php` (linha 51)
- **Problema:** `Locale::getDefault()` chamado sem import da classe `Locale` (ext-intl), causando `Class "Config\Locale" not found`.
- **Fix:** Adicionado `use Locale;` no topo do arquivo.
- **Status:** ✅ Corrigido

### 3. `MissingConfigKeys.php` — Função inexistente
- **Arquivo:** `app/Database/Migrations/20250716170000_MissingConfigKeys.php` (linha 15)
- **Problema:** Chamada a `executeScriptWithTransaction()` que não existe. O helper `migration_helper.php` define `execute_script()`.
- **Fix:** Trocado para `execute_script()`.
- **Status:** ✅ Corrigido

### 4. `.env` — baseURL e allowedHostnames não configurados
- **Arquivo:** `.env`
- **Problema:** `app.allowedHostnames = ''` vazio e `app.baseURL` ausente, causando falhas no carregamento de CSS/JS estáticos.
- **Fix:** Definido `app.baseURL = 'http://localhost:8080/'` e `app.allowedHostnames = 'localhost'`.
- **Status:** ✅ Corrigido

---

## 🟡 Pendentes (precisam conserto)

### 5. `Sales.php.bak` — Arquivo leftover em controllers
- **Arquivo:** `app/Controllers/Sales.php.bak` (72KB)
- **Problema:** Arquivo de backup não deveria estar no repositório. Pode causar problemas de autoload se o autoloader escanear `.bak`.
- **Impacto:** Baixo, mas é risco desnecessário.
- **Ação recomendada:** Deletar o arquivo ou mover para fora da `app/`.

### 6. 53 erros nos testes PHPUnit — Compatibilidade PHP 8.5
- **Total:** 103 testes, 53 erros, 12 falhas
- **Principais falhas:**
  - `Token_libTest` — Formatação de data inconsistente (`date()` retorna `Wed May  6 20:26:56 2026` em vez de `2026-05-06 20:26:56`). PHP 8.5 mudou o comportamento do `date()`.
  - `SalesControllerTest` — Teste espera `header('Location: ...)` mas a resposta usa redirect do CI4.
  - `ReportsControllerTest` — Mesmo problema de header location.
- **Ação recomendada:** Atualizar os testes para compatibilidade com PHP 8.5.

### 7. `Token_lib` — Bug de formatação de data em PHP 8.5
- **Arquivo:** `app/Libraries/Token_lib.php`
- **Problema:** `%c` e `%x` retornam formato antigo do `date()` que não bate com os regex dos testes.
- **Impacto:** Relatórios, recibos e impressões com formato de data errado.
- **Ação recomendada:** Revisar `Token_lib::render()` para usar `DateTime` em vez de `date()`.

### 8. `execute_script()` — Sem tratamento de erro SQL
- **Arquivo:** `app/Helpers/migration_helper.php`
- **Problema:** A função `execute_script()` loga erros SQL mas **não lança exceção** nem faz rollback. Migrações com erros continuam silenciosamente.
- **Impacto:** Migrações podem falhar parcialmente sem notificar.
- **Ação recomendada:** Adicionar transação e throw exception on error.

---

## 🟢 Débito Técnico (não urgente)

### 9. Notação húngara em `Mailchimp_lib`
- **Arquivo:** `app/Libraries/Mailchimp_lib.php`
- **Problema:** Variáveis como `$_api_key`, `$_connector`, `$_list_id`.
- **Ação recomendada:** Renomear para `apiKey`, `connector`, `listId`.

### 10. Duas classes no mesmo arquivo
- **Arquivo:** `app/Libraries/Mailchimp_lib.php`
- **Problema:** `MailchimpConnector` (linha 20) e `Mailchimp_lib` (linha 137) no mesmo arquivo.
- **Ação recomendada:** Separar em `MailchimpConnector.php`.

### 11. Uso direto de `$_FILES`, `$_POST`, `$_SERVER`
- **Arquivos:** `app/Controllers/Customers.php`, `Items.php`, `Events/Method.php`
- **Problema:** Ignora abstração de request do CodeIgniter 4.
- **Ação recomendada:** Usar `$this->request->getFile()`, `$this->request->getPost()`, etc.

### 12. `require_once` em vez de autoload
- **Arquivos:** `app/Controllers/Attributes.php`, `Items.php`
- **Problema:** `require_once('Secure_Controller.php')` — deveria usar autoload PSR-4 do CI4.
- **Ação recomendada:** Remover require, usar `use App\Controllers\Secure_Controller;`.

### 13. ~40+ TODOs espalhados pelo código
- **Localização:** `Receiving_lib.php`, `Mailchimp_lib.php`, `Barcode_lib.php`, `Item_lib.php`
- **Categorias:** naming conventions, código morto, funções que sempre retornam false.
- **Ação recomendada:** Revisar em sprint de limpeza técnica.
