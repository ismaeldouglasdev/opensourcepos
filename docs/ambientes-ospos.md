# Ambientes OSPOS

## Estrutura

| Ambiente | URL | Pasta | Banco | Porta |
|----------|-----|-------|-------|-------|
| **Produção** | http://localhost/pos | `/var/www/html/pos` | `ospos` | 80 |
| **Teste** | http://localhost:8080 | `/var/www/html/pos-test` | `ospos_test` | 8080 |

Código fonte: `/home/ismael/opensourcepos-fork/` (git repo)

---

## Configurações de Isolamento

### Cookie de sessão
- **Produção:** `ospos_session` (`app/Config/Session.php`)
- **Teste:** `ospos_test_session` (`app/Config/Session.php`)

### Cookie CSRF
- **Produção:** `csrf_cookie_ospos_v4` (`app/Config/Security.php`)
- **Teste:** `csrf_cookie_ospos_test_v4` (`app/Config/Security.php`)

### Chave de encriptação (`.env`)
- **Produção:** manter a original
- **Teste:** `39375001af541e8f9645a2fade5d491f`

### Diretório de sessões PHP
- **Produção:** `/var/lib/php/sessions`
- **Teste:** `/var/lib/php/sessions-test`

Configurado no Apache em `/etc/apache2/sites-available/pos-test.conf`:
```
php_value session.save_path /var/lib/php/sessions-test
```

---

## Fluxo de Trabalho

### 1. Desenvolver
Sempre edite os arquivos no git repo:
```bash
cd /home/ismael/opensourcepos-fork
# faça as alterações necessárias
```

### 2. Deploy para teste
```bash
./deploy-test.sh
```
Isso sincroniza o git repo com `/var/www/html/pos-test` (preservando o `.env`).

### 3. Testar
Acesse http://localhost:8080 e valide as alterações.

### 4. Deploy para produção
Quando tudo estiver funcionando no teste, copie manualmente ou use git:
```bash
cd /home/ismael/opensourcepos-fork
git add .
git commit -m "descricao das alteracoes"
# depois copie para /var/www/html/pos (manual ou script similar)
```

---

## Scripts

### `/home/ismael/deploy-test.sh`
```bash
#!/bin/bash
SRC="/home/ismael/opensourcepos-fork"
DST="/var/www/html/pos-test"

sudo rsync -av --delete \
  --exclude='.env' \
  --exclude='.git' \
  "$SRC/" "$DST/"

sudo chown -R www-data:www-data "$DST/writable"

echo "Teste atualizado em http://localhost:8080"
```

### `/home/ismael/backup_ospos.sh`
Backup do banco `ospos` (produção) via mysqldump.

### `/home/ismael/pos-backups/backup.sh`
Backup completo do código + banco de produção.

### `/home/ismael/pos-backups/restore.sh`
Restaura um backup para `/var/www/html/pos`.

### `/home/ismael/pos-backups/branch.sh`
Gerencia branches manuais (salva/restaura snapshots de produção).

---

## Arquivos .env

### Produção (`/var/www/html/pos/.env`)
```
CI_ENVIRONMENT = production
app.baseURL = 'http://localhost/pos/'
encryption.key = <original>
database.default.username = admin
database.default.password = Arroz123@
```

### Teste (`/var/www/html/pos-test/.env`)
```
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080/'
database.default.hostname = 'localhost'
database.default.database = 'ospos_test'
database.default.username = 'admin'
database.default.password = 'Arroz123@'
database.default.DBDriver = 'MySQLi'
database.default.DBPrefix = 'ospos_'
encryption.key = 39375001af541e8f9645a2fade5d491f
app.allowedHostnames = 'localhost'
```

---

## Apache

### Produção (`/etc/apache2/sites-available/000-default.conf`)
- Porta 80
- DocumentRoot: `/var/www/html/pos/public`

### Teste (`/etc/apache2/sites-available/pos-test.conf`)
```apache
<VirtualHost *:8080>
    DocumentRoot /var/www/html/pos-test/public
    <Directory /var/www/html/pos-test/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    php_value session.save_path /var/lib/php/sessions-test
    ErrorLog ${APACHE_LOG_DIR}/pos-test-error.log
    CustomLog ${APACHE_LOG_DIR}/pos-test-access.log combined
</VirtualHost>
```

---

## Deploy para Produção

Para deploy manual dos arquivos de impressão sem sobrescrever customizações visuais:

```bash
# Backup .env
sudo cp /var/www/html/pos/.env /tmp/env-prod.backup

# Arquivos novos da impressão térmica
sudo cp /home/ismael/opensourcepos-fork/app/Controllers/Printer.php /var/www/html/pos/app/Controllers/
sudo cp /home/ismael/opensourcepos-fork/app/Libraries/ThermalPrinter.php /var/www/html/pos/app/Libraries/
sudo cp /home/ismael/opensourcepos-fork/app/Views/configs/receipt_config.php /var/www/html/pos/app/Views/configs/receipt_config.php
sudo cp /home/ismael/opensourcepos-fork/app/Views/sales/receipt.php /var/www/html/pos/app/Views/sales/receipt.php
sudo cp /home/ismael/opensourcepos-fork/app/Language/pt-BR/Config.php /var/www/html/pos/app/Language/pt-BR/Config.php

# Atualizar rotas (adicionar ao final de Routes.php)
# $routes->add('printer/test', 'Printer::getTest');
# $routes->add('printer/printReceipt/(:num)', 'Printer::getPrintReceipt/$1');
# $routes->add('printer/quickPrint', 'Printer::postQuickPrint');

# Instalar dependência ESC/POS
sudo composer require mike42/escpos-php --update-no-dev

# Copiar assets compilados
sudo cp -r /home/ismael/opensourcepos-fork/public/resources/* /var/www/html/pos/public/resources/
sudo chown -R www-data:www-data /var/www/html/pos/public/resources/

# Configs no banco de produção
mysql -u admin -pArroz123@ ospos -e "INSERT INTO ospos_app_config (\`key\`, value) VALUES ('escpos_enabled', '0') ON DUPLICATE KEY UPDATE value = '0';"
mysql -u admin -pArroz123@ ospos -e "INSERT INTO ospos_app_config (\`key\`, value) VALUES ('escpos_printer', 'EPSON-TM20') ON DUPLICATE KEY UPDATE value = 'EPSON-TM20';"
# ... e demais configs escpos_*
```

---

## Histórico de Progresso

### 15/06/2026 — Deploy da impressão ESC/POS Epson TM-T20

#### Ambiente de Teste (localhost:8080)
- [x] Isolamento completo dos ambientes (cookie, CSRF, chave, sessões PHP)
- [x] Deploy do branch `test-bugfixes` com impressão térmica
- [x] Correção de assets faltantes (CSS, imagens, fontes)
- [x] Compilação de assets (npm install + npm run build / gulp)
- [x] Barra visual "AMBIENTE DE TESTE" verde no topo
- [x] Teste de impressão bem-sucedido com Epson TM-T20 via CUPS
- [x] Venda completa com impressão automática no fechamento
- [x] Botão de reprint térmico no recibo

#### Ambiente de Produção (localhost)
- [x] Deploy cirúrgico dos arquivos de impressão (sem sobrescrever customizações visuais)
- [x] Instalação da dependência `mike42/escpos-php`
- [x] Atualização de dados da loja (empresa, endereço, telefone)
- [x] Rotas do Printer controller adicionadas
- [x] Configurações ESC/POS no banco de produção
- [x] Botão "Reimprimir Térmica" na listagem de vendas (sales/manage)

#### Pendências
- [ ] Corrigir rota `printer/quickPrint` na produção (testar chamada)
- [ ] Testar impressão automática pós-venda na produção

---

## Dicas

- **Copiar ao selecionar:** `autocutsel -s PRIMARY -fork` (adicione ao `~/.bashrc` para iniciar automático)
- **Limpar cache do teste:** `sudo rm -rf /var/www/html/pos-test/writable/cache/*`
- **Resetar banco de teste:** `mysql -u admin -pArroz123@ ospos_test < backup.sql`
- **Ver rota no navegador:** http://localhost/sales/receipt/N (trocar N pelo ID da venda)
