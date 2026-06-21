# Setup OSPOS — Pendências

## Reativar ambiente de teste (amanhã)

```bash
sudo a2ensite pos-test.conf && sudo systemctl reload apache2
```

## 1. Corrigir `header.php`

Faltam fechar duas tags `<li>` em `/home/ismael/opensourcepos-fork/app/Views/partial/header.php`:

- **Linha 169**: adicionar `</li>` após `</a>`
- **Linha 173**: adicionar `</li>` após `</a>`

```diff
- <li class="<?=$is_resumo ? 'active' : ' ' ?>">
-     <a href="<?=base_url('sales/manage') ?>" style="font-size: 16px;">
-     📊 RESUMO
-     </a>
- <li class="<?=$is_vendas ? 'active' : ' ' ?>">
+ <li class="<?=$is_resumo ? 'active' : ' ' ?>">
+     <a href="<?=base_url('sales/manage') ?>" style="font-size: 16px;">
+     📊 RESUMO
+     </a>
+ </li>
+ <li class="<?=$is_vendas ? 'active' : ' ' ?>">
```

(Fazer o mesmo para VENDAS, adicionar `</li>` na linha 173)

---

## 2. Adicionar 5 rotas em `Routes.php`

Arquivo: `/home/ismael/opensourcepos-fork/app/Config/Routes.php`

Adicionar após a linha que contém a rota de receber (ou no final do bloco de rotas relacionadas a sales/printer):

```php
$routes->get('sales/getPaymentSummary/(:num)', 'Sales::getPaymentSummary/$1');
$routes->post('sales/addDiversos', 'Sales::addDiversos');
$routes->get('printer/test', 'Printer::test');
$routes->get('printer/printReceipt/(:num)', 'Printer::printReceipt/$1');
$routes->get('printer/quickPrint/(:num)', 'Printer::quickPrint/$1');
```

---

## 3. Adicionar método `getPaymentSummary()` no `Sales.php`

Arquivo: `/home/ismael/opensourcepos-fork/app/Controllers/Sales.php`

Copiar o método da produção (`/var/www/html/pos/app/Controllers/Sales.php`, ~linha 1699-1741) para o git repo, no mesmo arquivo, antes do `}` final da classe.

Assinatura: `public function getPaymentSummary($sale_id)`

---

## 4. Adicionar campos ESC/POS no `Config.php`

Arquivo: `/home/ismael/opensourcepos-fork/app/Controllers/Config.php`

Adicionar antes do `];` final (após linha 910 aproximadamente):

```php
$config_session['escpos_enabled'] = $request->getPost('escpos_enabled') ?? '';
$config_session['escpos_printer'] = $request->getPost('escpos_printer') ?? '';
$config_session['escpos_printer_print_receipt'] = $request->getPost('escpos_printer_print_receipt') ?? '';
```

---

## 5. Copiar `manage.php` da produção

Arquivo: `/var/www/html/pos/app/Views/sales/manage.php` → `/home/ismael/opensourcepos-fork/app/Views/sales/manage.php`

Tem botão "Reimprimir Térmica" e resumo de pagamentos dinâmico.

---

## 6. Rodar deploy

```bash
./deploy-test.sh
```

---

## 7. Commit e push

```bash
git add -A
git commit -m "fix: correcao header.php, rotas, getPaymentSummary, config escpos, manage.php"
git push
```
