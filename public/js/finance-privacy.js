/**
 * public/js/finance-privacy.js
 *
 * Botão de olho no topo do OSPOS: censura/desoculta os valores monetários
 * (R$ ...) de qualquer página. O estado fica no localStorage do aparelho.
 *
 * Como funciona:
 *  - Todo texto que casa com o formato de dinheiro (R$ 1.234,56 / R$5.00) é
 *    embrulhado em <span class="fin-censored">. O layout não muda; só o filtro
 *    CSS (blur) é ligado/desligado pela classe fin-privacy no <html>.
 *  - MutationObserver cobre conteúdo carregado depois (bootstrap-table AJAX,
 *    fragmentos do carrinho, tooltips de gráfico).
 *  - O <title> de elementos com valor (barras do gráfico) é mascarado
 *    enquanto a censura está ligada, para o hover não vazar o número.
 */
(function () {
    'use strict';

    var STORAGE_KEY = 'ospos_fin_privacy';
    var ROOT_CLASS = 'fin-privacy';
    var PENDING_CLASS = 'fin-privacy-pending';
    var CENSORED_CLASS = 'fin-censored';
    var SKIP_ATTR = 'data-fin-privacy-skip';

    // Páginas em que a censura não se aplica:
    //  - /sales/receipt: é o documento do cliente, precisa sair legível;
    //  - /sales e /sales/add: é o caixa, o operador precisa ver os valores.
    function isExcludedPage() {
        var path = window.location.pathname;
        var censoredSalesPage = /\/sales\/manage(\/|$)/.test(path) || /\/sales\/invoice(\/|$)/.test(path);
        return (!censoredSalesPage && /\/sales(\/|$)/.test(path)) || /\/items(\/|$)/.test(path);
    }

    if (isExcludedPage()) {
        document.documentElement.classList.remove(PENDING_CLASS);
        var excludedButton = document.getElementById('fin_privacy_toggle');
        if (excludedButton) {
            excludedButton.style.display = 'none';
        }
        return;
    }

    // R$ + separador opcional + dígitos/separadores. \s cobre o NBSP (U+00A0)
    // que o NumberFormatter coloca entre o símbolo e o número.
    var MONEY_RE = /-?R\$\s?[0-9][0-9.,]*/g;
    var SKIP_TAGS = {
        SCRIPT: 1, STYLE: 1, NOSCRIPT: 1, TEXTAREA: 1,
        SELECT: 1, OPTION: 1, TITLE: 1
    };

    var isOn = false;
    var button = null;
    var icon = null;

    function readStored() {
        try {
            return window.localStorage.getItem(STORAGE_KEY) !== '0';
        } catch (e) {
            return true;
        }
    }

    function store(on) {
        try {
            window.localStorage.setItem(STORAGE_KEY, on ? '1' : '0');
        } catch (e) {
            // modo privado / storage bloqueado: só não persiste
        }
    }

    function shouldSkip(parent) {
        if (!parent || parent.nodeType !== 1) {
            return true;
        }
        if (SKIP_TAGS[parent.tagName]) {
            return true;
        }
        if (parent.closest('.' + CENSORED_CLASS)) {
            return true;
        }
        if (parent.closest('[' + SKIP_ATTR + ']')) {
            return true;
        }
        // SVG não aceita <span> no meio: embrulhar quebraria o gráfico.
        if (parent.closest('svg')) {
            return true;
        }
        return false;
    }

    function wrapTextNode(node) {
        if (!node.parentNode) {
            return;
        }
        var text = node.nodeValue || '';
        if (text.indexOf('R$') === -1) {
            return;
        }

        MONEY_RE.lastIndex = 0;
        var match = MONEY_RE.exec(text);
        if (!match) {
            return;
        }

        var fragment = document.createDocumentFragment();
        var last = 0;

        while (match) {
            if (match.index > last) {
                fragment.appendChild(document.createTextNode(text.slice(last, match.index)));
            }
            var span = document.createElement('span');
            span.className = CENSORED_CLASS;
            span.textContent = match[0];
            fragment.appendChild(span);
            last = match.index + match[0].length;
            match = MONEY_RE.exec(text);
        }

        if (last < text.length) {
            fragment.appendChild(document.createTextNode(text.slice(last)));
        }
        node.parentNode.replaceChild(fragment, node);
    }

    function scan(root) {
        if (!root || root.nodeType !== 1 || !root.isConnected || shouldSkip(root)) {
            return;
        }

        var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
            acceptNode: function (node) {
                if (shouldSkip(node.parentNode)) {
                    return NodeFilter.FILTER_REJECT;
                }
                if (node.nodeValue && node.nodeValue.indexOf('R$') !== -1) {
                    return NodeFilter.FILTER_ACCEPT;
                }
                return NodeFilter.FILTER_REJECT;
            }
        });

        // Coleta antes de mexer no DOM: embrulhar durante o walk invalidaria o walker.
        var nodes = [];
        while (walker.nextNode()) {
            nodes.push(walker.currentNode);
        }
        for (var i = 0; i < nodes.length; i++) {
            wrapTextNode(nodes[i]);
        }
    }

    function maskTitles(root) {
        var scope = root || document;
        var elements = [];
        if (root && root.nodeType === 1 && root.hasAttribute('title')) {
            elements.push(root);
        }
        var found = scope.querySelectorAll('[title]');
        for (var i = 0; i < found.length; i++) {
            elements.push(found[i]);
        }
        for (var j = 0; j < elements.length; j++) {
            var element = elements[j];
            if (element.closest('[' + SKIP_ATTR + ']')) {
                continue;
            }
            var title = element.getAttribute('title');
            if (!title || title.indexOf('R$') === -1 || element.hasAttribute('data-fin-title')) {
                continue;
            }
            element.setAttribute('data-fin-title', title);
            element.setAttribute('title', 'Valor oculto');
        }
    }

    function unmaskTitles() {
        var elements = document.querySelectorAll('[data-fin-title]');
        for (var i = 0; i < elements.length; i++) {
            var element = elements[i];
            element.setAttribute('title', element.getAttribute('data-fin-title'));
            element.removeAttribute('data-fin-title');
        }
    }

    function applyState(on, persist) {
        isOn = on;
        document.documentElement.classList.toggle(ROOT_CLASS, on);

        if (button) {
            button.setAttribute('aria-pressed', on ? 'true' : 'false');
            button.setAttribute('title', on
                ? (button.getAttribute('data-label-show') || 'Mostrar valores')
                : (button.getAttribute('data-label-hide') || 'Ocultar valores'));
            button.setAttribute('aria-label', button.getAttribute('title'));
            button.classList.toggle('is-on', on);
        }
        if (icon) {
            icon.classList.toggle('glyphicon-eye-close', on);
            icon.classList.toggle('glyphicon-eye-open', !on);
        }

        if (on) {
            maskTitles();
        } else {
            unmaskTitles();
        }

        if (persist) {
            store(on);
        }
    }

    function startObserver() {
        if (!window.MutationObserver || !document.body) {
            return;
        }

        var observer = new MutationObserver(function (mutations) {
            var nodes = [];
            for (var i = 0; i < mutations.length; i++) {
                var mutation = mutations[i];
                if (mutation.type === 'childList') {
                    for (var j = 0; j < mutation.addedNodes.length; j++) {
                        var added = mutation.addedNodes[j];
                        if (added.nodeType === 1) {
                            nodes.push(added);
                        } else if (added.nodeType === 3 && added.parentNode) {
                            nodes.push(added.parentNode);
                        }
                    }
                } else if (mutation.type === 'characterData' && mutation.target.parentNode) {
                    nodes.push(mutation.target.parentNode);
                }
            }
            // Embrulha já no callback: ele roda antes da pintura do frame, então
            // o valor nunca chega a aparecer sem blur (o setTimeout de 60ms deixava).
            for (var k = 0; k < nodes.length; k++) {
                scan(nodes[k]);
            }
            if (isOn) {
                for (var m = 0; m < nodes.length; m++) {
                    maskTitles(nodes[m]);
                }
            }
        });

        observer.observe(document.body, { childList: true, subtree: true, characterData: true });
    }

    function init() {
        button = document.getElementById('fin_privacy_toggle');

        // Sem botão não há como desfazer a censura: não liga a classe.
        if (!button) {
            document.documentElement.classList.remove(ROOT_CLASS, PENDING_CLASS);
            return;
        }

        icon = button.querySelector('.glyphicon');

        button.addEventListener('click', function () {
            applyState(!isOn, true);
        });

        applyState(readStored(), false);
        scan(document.body);
        startObserver();
        document.documentElement.classList.remove(PENDING_CLASS);

        window.osposFinancePrivacy = {
            isOn: function () {
                return isOn;
            },
            set: function (on) {
                applyState(!!on, true);
            }
        };
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
