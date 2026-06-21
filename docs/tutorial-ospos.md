# Tutorial OSPOS — Setup do Ambiente de Teste

## Tudo que ainda falta fazer

---

## TAREFA 1 — Corrigir `header.php`

**Arquivo:** `app/Views/partial/header.php`

Faltam fechar duas tags `<li>` em:

- **Linha ~169**: adicionar `</li>` depois do `</a>` do "RESUMO"
- **Linha ~173**: adicionar `</li>` depois do `</a>` do "VENDAS"

**Antes (errado):**
```php
<li class="<?=$is_resumo ? 'active' : ' ' ?>">
    <a href="<?=base_url('sales/manage') ?>" style="font-size: 16px;">
    📊 RESUMO
    </a>
<li class="<?=$is_vendas ? 'active' : ' ' ?>">
```

**Depois (correto):**
```php
<li class="<?=$is_resumo ? 'active' : ' ' ?>">
    <a href="<?=base_url('sales/manage') ?>" style="font-size: 16px;">
    📊 RESUMO
    </a>
</li>
<li class="<?=$is_vendas ? 'active' : ' ' ?>">
```

---

## TAREFA 2 — Adicionar 5 rotas em `Routes.php`

**Arquivo:** `app/Config/Routes.php`

Adicionar no **final do arquivo** (depois da última rota `$routes->add('sales', 'Sales::getIndex');`):

```php
$routes->get('sales/getPaymentSummary/(:num)', 'Sales::getPaymentSummary/$1');
$routes->post('sales/addDiversos', 'Sales::addDiversos');
$routes->get('printer/test', 'Printer::test');
$routes->get('printer/printReceipt/(:num)', 'Printer::printReceipt/$1');
$routes->get('printer/quickPrint/(:num)', 'Printer::quickPrint/$1');
```

---

## TAREFA 3 — Adicionar método `getPaymentSummary()` no `Sales.php`

**Arquivo:** `app/Controllers/Sales.php`

1. Abra o arquivo
2. Vá até a **última linha** (deve ser `}` fechando a classe)
3. Adicione **antes desse `}`** o seguinte código:

```php
public function getPaymentSummary(): void
{
    $start_date = $this->request->getGet('start_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $end_date = $this->request->getGet('end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $filters = $this->request->getGet('filters', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? [];

    $inputs = [
        'start_date' => $start_date,
        'end_date' => $end_date,
        'sale_type' => 'sales',
        'only_cash' => false,
        'only_creditcard' => false,
        'only_pix' => false,
        'only_account_receivable' => false,
        'only_invoices' => false,
        'selected_customer' => false
    ];

    if (!empty($filters)) {
        $filter_array = is_array($filters) ? $filters : [$filters];
        foreach ($filter_array as $filter) {
            switch ($filter) {
                case 'only_cash':
                    $inputs['only_cash'] = true;
                    break;
                case 'only_creditcard':
                    $inputs['only_creditcard'] = true;
                    break;
                case 'only_pix':
                    $inputs['only_pix'] = true;
                    break;
                case 'only_account_receivable':
                    $inputs['only_account_receivable'] = true;
                    break;
            }
        }
    }

    $payments = $this->sale->get_payments_summary('', $inputs);
    $payment_summary = get_sales_manage_payments_summary($payments);

    echo json_encode(['payment_summary' => $payment_summary]);
}
```

---

## TAREFA 4 — Adicionar campos ESC/POS no `Config.php`

**Arquivo:** `app/Controllers/Config.php`

1. Abra o arquivo
2. Vá até o **final** da função que monta `$config_session`
3. Antes do `];` final do array, adicione:

```php
$config_session['escpos_enabled'] = $request->getPost('escpos_enabled') ?? '';
$config_session['escpos_printer'] = $request->getPost('escpos_printer') ?? '';
$config_session['escpos_printer_print_receipt'] = $request->getPost('escpos_printer_print_receipt') ?? '';
```

---

## TAREFA 5 — Copiar `manage.php` da produção

**Arquivo de origem (produção):** `/var/www/html/pos/app/Views/sales/manage.php`

**Arquivo de destino:** `app/Views/sales/manage.php`

Copie o arquivo da produção para o repositório git:

```bash
cp /var/www/html/pos/app/Views/sales/manage.php /home/ismael/opensourcepos-fork/app/Views/sales/manage.php
```

Esse arquivo já contém o botão "Reimprimir Térmica" e o resumo de pagamentos dinâmico.

---

## TAREFA 6 — Rodar deploy para o ambiente de teste

Depois de fazer todas as alterações acima:

```bash
./deploy-test.sh
```

Isso vai sincronizar tudo do git repo (`/home/ismael/opensourcepos-fork/`) para o ambiente de teste (`/var/www/html/pos-test/`).

Acesse http://localhost:8080 para testar.

---

## TAREFA 7 — Commit e push no git

```bash
cd /home/ismael/opensourcepos-fork
git add -A
git commit -m "fix: correcao header.php, rotas, getPaymentSummary, config escpos, manage.php"
git push
```

---

## Ordem recomendada

| Passo | O que fazer | Arquivo |
|-------|-------------|---------|
| 1 | Fechar tags `<li>` | `header.php` |
| 2 | Adicionar 5 rotas | `Routes.php` |
| 3 | Adicionar método | `Sales.php` |
| 4 | Adicionar campos ESC/POS | `Config.php` |
| 5 | Copiar manage.php | `manage.php` |
| 6 | Deploy | `./deploy-test.sh` |
| 7 | Commit + push | `git` |

Bom aprendizado! 🚀
