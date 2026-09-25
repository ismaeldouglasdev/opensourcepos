# DESIGN.md — Quase Tudo (OSPOS)

Documento de design do fork em produção. Descreve **o que o sistema é hoje**, os
princípios que valem, e o backlog de correções priorizado por evidência.

Base: auditoria visual com browser real em 3 telas principais (registro de venda,
grid de itens, home) em 1440×900, + medições de performance.

---

## 1. O que este sistema é

Um PDV de balcão para um comércio pequeno. O caixa é a tela de partida — `/` redireciona
para o registro de venda, não para um dashboard. Isso é intencional e correto: quem
abre o sistema quer **vender**, não consultar.

Consequência de design: a tela de venda é a mais importante e não pode ter atrito.
Grids e relatórios são ferramentas de apoio, acessadas com menos frequência.

### Público e ambiente de uso

- Um operador, em pé, de balcão, possivelmente com as mãos ocupadas.
- Ações são repetidas milhares de vezes por dia — o custo de um clique extra se
  multiplica. Velocidade e previsibilidade valem mais que densidade de informação.
- Telas de toque e mouse. A mira do toque é imprecisa: alvos precisam de área.
- A máquina é fraca (i3-3220T, 3.7 GiB, HDD). Animação e blur custam caro.

---

## 2. Princípios

1. **O caminho da venda não pode ter atrito.** Busca de produto, ajuste de
   quantidade e finalização precisam ser atingíveis sem tirar a mão do teclado.
2. **Ação destrutiva nunca divide o mesmo peso visual de uma ação benigna.**
   "Apagar" e "Editar Múltiplos" não podem ser o mesmo botão.
3. **Nada flutuante sobrepõe dado.** Overlay, tooltip e botão flutuante que
   cobrem coluna ou linha são bugs, não estilo.
4. **Número é dado, não decoração.** Valores monetários e quantidades têm
   alinhamento e formatação estáveis para comparação vertical rápida.
5. **O espaço vazio é custo.** Em 1440×900 o conteúdo ocupa ~60% da altura.
   Espaço morto em PDV é espaço que deveria ser informação ou controle.

---

## 3. Estado atual — problemas observados

Todos abaixo foram vistos nas capturas, não inferidos.

### 3.1 Ícones

A navegação usa **emoji como ícone** (🏠 RESUMO, 💰 VENDAS, 📦 ITENS, 👥 CLIENTES)
e o botão de ajuda é um círculo verde com "?".

Problemas: o desenho varia por SO e por fonte; o emoji colored conflita com a
paleta; não há estado de foco/hover real; alinhamento e o peso visual não são
controlados. Nenhum outro elemento do sistema usa emoji — a navegação é a única
exceção, o que a torna mais visível como inconsistência.

**Direção:** a base já tem `glyphicon` (Bootstrap 3) carregado e usado nos botões
secundários. Trocar os cinco emoji por `glyphicon` resolve sem trazer dependência
nova e mantém coerência com o resto.

### 3.2 Espaço vertical

Nas três telas o conteúdo termina por volta de 60% da altura útil, e o rodapé
sobe junto, deixando uma faixa vazia grande abaixo dele. O layout não ocupa a
janela.

### 3.3 Botão de ajuda sobrepõe a tabela

Na grid de itens, o círculo verde de ajuda fica sobre a coluna **ÚLTIMA
MODIFICAÇÃO** e cobre parcialmente os valores das linhas. Isso é um defeito
visual concreto, não uma questão de gosto.

### 3.4 Peso visual não diferencia ação destrutiva

Na barra da grid, "Apagar" tem exatamente o mesmo estilo de "Editar Múltiplos
Itens" e "Gerar Códigos de Barras". Apagar é irreversível na prática (soft delete)
e precisa de sinal próprio.

### 3.5 Colunas com baixo aproveitamento

- **IMAGEM** está vazia (`-`) na imensa maioria das linhas. O banco tem 155 itens
  com foto em 10.335 (1,5%). A coluna consome ~90px para mostrar "-" quase sempre.
- **COD. BARRA** também mostra "-" com frequência.

A coluna de imagem é a que mais custa e a que menos entrega.

### 3.6 Rótulos

- "Importar do CSV" — construção atravessa preposição; o natural é "Importar CSV".
- "Quantidade de 0 itens" — em estado vazio o esperado é "Nenhum item na cesta"
  (que a mesa já mostra) ou "0 itens", não as duas coisas.
- "SELECIONAR CLIENTE (OBRIGATÓRIO PARA PAGAMENTOS VENCIDOS)" — o parêntese é
  ruído em caixa alta; a condição é rara e não precisa competir por atenção.
- Dois controles com o rótulo "Vendas" lado a lado na barra do registro: o seletor
  de modo e um botão. Nomes duplicados em ações diferentes causam erro de clique.

### 3.7 Formatação numérica inconsistente

Na mesma tabela, `QTD` aparece em negrito preto, preço de custo em cinza e preço de
venda em verde — sem regra aparente que explique quando cada cor entra. Datas
aparecem como "23/09/2026 10:51" na tabela e em outro formato em outros lugares.

Sem regra explícita, o operador não sabe o que a cor significa e para de usá-la
como sinal.

---

## 4. Fundamentos (tokens)

Consolidar aqui para que a correção seja aditiva, não mais uma exceção.

### Cor

| Papel | Valor | Uso |
|---|---|---|
| Tinta | `#2c3e50` | texto padrão, títulos |
| Tinta suave | `#7f8c8d` | secundário, colunas vazias |
| Superfície | `#ffffff` | cards, tabela |
| Fundo | `#f4f6f7` | área de trabalho |
| Marca | `#2f6fed` | ação primária |
| Marca ativa | `#1e4fc7` | hover/pressed |
| Sucesso | `#1e8e5a` | preço de venda, confirmação |
| Alerta | `#c0392b` | ação destrutiva, estoque zerado |
| Aviso | `#b8860b` | estoque irregular |
| Borda | `#e2e6e9` | divisores, cards |

Regra: **cor só é usada quando carrega significado**. Verde = preço/confirmado.
Vermelho = destrutivo/zerado. Azul = ação primária. Sem cor decorativa.

### Tipografia

Escalas, com `rem` relativo à raiz (o sistema usa `zoom: 1.25` global — nunca
fixar `px` em fonte):

| Token | Tamanho | Peso | Uso |
|---|---|---|---|
| `display` | 2rem | 700 | valor total do dia |
| `h1` | 1.5rem | 600 | título de página |
| `h2` | 1.25rem | 600 | título de seção |
| `body` | 1rem | 400 | texto padrão |
| `label` | 0.8125rem | 600, caixa alta | cabeçalho de coluna |
| `mono` | — | 400, tabular-nums | código, quantidade, moeda |

Números monetários e quantidade usam `font-variant-numeric: tabular-nums` para
alinhar verticalmente na coluna.

### Espaço

Escala de 4px: `4 / 8 / 12 / 16 / 24 / 32`. Padding de card `16`, gap entre
seções `24`, gutter de coluna `12`.

### Alvos de toque

Mínimo `44×44px` para controle interativo. Botão pequeno de coluna de tabela
exceção aceita, desde que tenha `padding` vertical suficiente.

---

## 5. Backlog priorizado

### P0 — defeito visual

1. **Botão de ajuda sobrepõe a tabela.** Dar `z-index` abaixo da tabela ou
   reposicionar. Entrega: nenhuma coluna da grid coberta.

### P1 — hierarquia e segurança de clique

2. **Diferenciar ação destrutiva.** "Apagar" com contorno/tom `Alerta`; manter
   "Editar Múltiplos" e "Gerar Códigos" neutros. Entrega: a distinção é legível
   sem passar o mouse.
3. **Emoticons da navegação → `glyphicon`.** Entrega: navegação com ícone
   consistente com o resto do sistema e sem variação por SO.
4. **Renomear rótulos divergentes.** "Importar CSV"; remover a informação
   redundante no estado vazio; separar os dois "Vendas". Entrega: nenhum par de
   rótulos iguais para ações diferentes.

### P2 — aproveitamento de tela

5. **Layout ocupa a altura da janela.** O card de conteúdo cresce; o rodapé
   desce para o fim. Entrega: sem faixa morta abaixo do conteúdo.
6. **Coluna IMAGEM.** Ocultar por padrão quando o filtro não é de imagens, ou
   reduzir a largura ao mínimo. Entrega: ~90px devolvidos para nome/categoria.

### P3 consistência

7. **Regra explícita de formatação numérica**, aplicada a todas as tabelas:
   moeda em `mono` tabular; `QTD` sem negrito para competir com o preço; uma
   única máscara de data.
8. **Microcopy.** Retirar parênteses de bloqueio da caixa alta; manter a
   condição como texto de apoio em tom suave.

---

## 6. Como validar

Toda alteração precisa ser verificada com browser real, não por leitura de CSS:

1. `php -l` no arquivo alterado.
2. Captura em 1440×900 da tela afetada.
3. Medir sobreposição: nenhum controle flutuante sobre coluna de dado.
4. Conferir que a ação destrutiva continua exigindo confirmação.
5. Testar com o conteúdo real (o banco tem 10.335 itens), não com a lista vazia —
   a maioria dos problemas de grid só aparece com dados densos.

Referência de ferramenta: `/home/ismael/.opencode-tools/penv/bin/python` com
Playwright + Chromium headless.
