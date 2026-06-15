# Impressão ESC/POS Nativa - Epson TM-T20

## Objetivo

Integrar impressão térmica ESC/POS nativa no OSPOS para a Epson TM-T20, eliminando a dependência de impressão via navegador (`window.print()`).

## Implementação

### Nova Biblioteca

**`app/Libraries/ThermalPrinter.php`**

Biblioteca central que encapsula a `mike42/escpos-php` com:

- **Conexão flexível**: CUPS (padrão), file device, ou TCP/IP direto
- **Formatação completa**: cabeçalho da loja, itens da venda, subtotal/descontos/taxas/total, barcode do recibo, corte automático, gaveta de dinheiro
- **Logo opcional**: suporta exibição do logo da empresa se configurado
- **Teste de impressão**: modo de diagnóstico que imprime um recibo de teste

### Novo Controller

**`app/Controllers/Printer.php`**

Endpoints REST para operações térmicas:

| Método | Rota | Descrição |
|--------|------|-----------|
| `GET` | `/printer/test` | Imprime recibo de teste |
| `GET` | `/printer/printReceipt/{id}` | Reimprime recibo de venda |
| `POST` | `/printer/quickPrint` | Impressão rápida via AJAX |

### Modificações

| Arquivo | O que mudou |
|---------|-------------|
| `app/Controllers/Sales.php` | `complete()` chama impressão térmica automática quando habilitado |
| `app/Controllers/Config.php` | `postSaveReceipt()` salva as novas configs ESC/POS |
| `app/Views/sales/receipt.php` | Botão "Impressora Térmica" + script AJAX para reprint |
| `app/Views/partial/print_receipt.php` | Suprime `window.print()` quando ESC/POS ativo (evita duplicidade) |
| `app/Views/configs/receipt_config.php` | Formulário completo de configuração ESC/POS |
| `app/Language/pt-BR/Config.php` | Tradução `escpos_enable` |
| `composer.json` | Dependência `mike42/escpos-php` adicionada |

## Configuração

Acesse **Config → Receipt Config** no OSPOS e configure:

| Campo | Descrição | Padrão |
|-------|-----------|--------|
| **Impressora Térmica** | Ativar/desativar impressão ESC/POS | Desligado |
| **Nome da Impressora** | Nome no CUPS ou caminho do dispositivo | TM-T20 |
| **Largura do Papel** | 80mm (48 colunas) ou 58mm (32 colunas) | 80mm |
| **Tipo de Conexão** | cups, file, network | cups |
| **Device Path** | Caminho do dispositivo (ex: `/dev/usb/lp0`) | vazio |
| **Host/Porta** | IP e porta para conexão network | vazio |
| **Mostrar Logo** | Exibe o logo da empresa no topo do recibo | Sim |
| **Imprimir Teste** | Botão para testar a impressora | — |

## Fluxo de Impressão

1. **Venda finalizada** → `Sales::complete()` verifica `escpos_enabled`
2. Se ativo → instancia `ThermalPrinter`, conecta, imprime recibo completo, corta papel
3. Se inativo → comportamento original (`window.print()`)
4. **Reprint manual** → botão no recibo envia POST AJAX para `/printer/quickPrint`

## Decisões Técnicas

- **ESC/POS nativo escolhido** sobre impressão via navegador: corte automático, sem janela de print, formatação precisa, velocidade máxima
- **Conexão via CUPS** como padrão (já configurado na produção Lubuntu), com fallback para file device e network TCP/IP
- **Papel 80mm** (48 colunas) por ser o padrão da TM-T20, com suporte a 58mm configurável
- **Print silencioso**: quando ESC/POS está habilitado, o `window.print()` é suprimido para evitar impressão duplicada

## Testes na Produção

```bash
# Instalar dependência
composer install --no-dev
```

No OSPOS:
1. **Config → Receipt Config** → ativar "Impressora Térmica"
2. Nome da impressora: `TM-T20` (ou nome exato no CUPS)
3. Tipo de conexão: `cups`
4. Clicar **"Imprimir Teste"**
5. Fazer uma venda → recibo sai automático no fechamento

## Stack Técnica

- PHP 8.5.6 / CodeIgniter 4.7.2
- `mike42/escpos-php` (última versão)
- Lubuntu + CUPS + Epson TM-T20 (80mm)
