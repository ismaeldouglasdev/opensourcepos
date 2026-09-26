<style>
.tutorial-active {
    pointer-events: none;
}
.tutorial-active .tutorial-step *,
.tutorial-active .tutorial-nav *,
.tutorial-highlight {
    pointer-events: auto;
}

.tutorial-overlay {
    position: fixed;
    inset: 0;
    z-index: 9998;
    background: rgba(0,0,0,0.5);
    display: none;
}
.tutorial-overlay.active {
    display: block;
}

.tutorial-step {
    position: fixed;
    z-index: 10000;
    background: #fff;
    border-radius: 16px;
    padding: 28px 30px 24px;
    max-width: 440px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.25);
    display: none;
    border: 3px solid #1a9c6a;
}
.tutorial-step.active {
    display: block;
}

.tutorial-step .step-number {
    display: inline-block;
    background: #1a9c6a;
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    padding: 2px 12px;
    border-radius: 20px;
    margin-bottom: 12px;
}

.tutorial-step .step-title {
    font-size: 22px;
    font-weight: 700;
    color: #1a1d21;
    margin-bottom: 10px;
    line-height: 1.3;
}

.tutorial-step .step-text {
    font-size: 18px;
    color: #374151;
    line-height: 1.6;
    margin-bottom: 8px;
}

.tutorial-step .step-text strong {
    color: #1a9c6a;
}

.tutorial-nav {
    display: flex;
    gap: 8px;
    margin-top: 18px;
    align-items: center;
}

.tutorial-nav button {
    font-size: 17px;
    font-weight: 600;
    padding: 8px 18px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.15s;
}

.tutorial-nav .btn-prev {
    background: #e5e7eb;
    color: #374151;
}
.tutorial-nav .btn-prev:hover {
    background: #d1d5db;
}

.tutorial-nav .btn-next {
    background: #1a9c6a;
    color: #fff;
}
.tutorial-nav .btn-next:hover {
    background: #14805a;
}

.tutorial-nav .btn-close {
    background: transparent;
    color: #9ca3af;
    margin-left: auto;
    font-size: 15px;
    padding: 8px 12px;
}
.tutorial-nav .btn-close:hover {
    color: #6b7280;
}

.tutorial-nav .step-indicator {
    font-size: 15px;
    color: #9ca3af;
    margin-left: 12px;
}

.tutorial-highlight {
    position: relative;
    z-index: 9999;
    outline: 3px solid #1a9c6a;
    outline-offset: 4px;
    border-radius: 4px;
    transition: outline 0.2s;
}

/* O botao e position:fixed e cobre a ultima coluna da tabela (ver DESIGN.md
   P0). A correcao definitiva e mover o botao para dentro da barra do topo,
   que nao disputa espaco com dado. Enquanto isso ele recua visualmente e
   fica menor. Nao se reserva padding na tabela: isso estreita a coluna de
   nome e faz o produto quebrar em varias linhas, que e pior que a
   sobreposicao. */
.tutorial-fab {
    position: fixed;
    bottom: 12px;
    right: 12px;
    z-index: 1040;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #1a9c6a;
    color: #fff;
    font-size: 26px;
    font-weight: 700;
    border: none;
    box-shadow: 0 4px 14px rgba(26,156,106,0.35);
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.tutorial-fab:hover {
    transform: scale(1.08);
    background: #14805a;
}
.tutorial-fab:active {
    transform: scale(0.95);
}

.tutorial-arrow {
    position: fixed;
    z-index: 10001;
    pointer-events: none;
}
/* ===================================================================
   Anotacoes fixas do Modo Simples (tarefa 12 do plano
   simplificar-fluxo-venda-pdv)
   Selos circulares numerados ancorados ao alvo + uma legenda recolhivel.
   Vive em uma camada propria sobre o body, e nao dentro de #cart_contents
   nem de #sale_totals: e exatamente o que o AJAX substitui a cada item
   escaneado, e um selo morando ali sumiria junto (ou, pior, duplicaria).
   =================================================================== */

.simple-guide-layer {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 1040;
}

.simple-guide-badge {
    position: fixed;
    width: 30px;
    height: 30px;
    margin: -15px 0 0 -15px;
    border-radius: 50%;
    background: #e65100;
    color: #fff;
    font: 700 16px/30px system-ui, sans-serif;
    text-align: center;
    box-shadow: 0 2px 6px rgba(0, 0, 0, .35);
    display: none;
}

/* o vetor: triângulo apontando para o alvo, ancorado à direita do selo */
.simple-guide-badge::after {
    content: '';
    position: absolute;
    left: 100%;
    top: 50%;
    margin: -6px 0 0 -2px;
    border: 6px solid transparent;
    border-left-color: #e65100;
}

.simple-guide-legend {
    position: fixed;
    right: 10px;
    bottom: 10px;
    width: 320px;
    max-width: calc(100vw - 20px);
    background: #fff;
    border: 1px solid #e65100;
    border-radius: 8px;
    box-shadow: 0 4px 14px rgba(0, 0, 0, .25);
    z-index: 1041;
    font-size: 14px;
    line-height: 1.35;
    overflow: hidden;
}

.simple-guide-legend-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 8px 10px;
    background: #e65100;
    color: #fff;
    font-weight: 600;
    cursor: pointer;
    user-select: none;
}

.simple-guide-legend-body {
    max-height: 46vh;
    overflow-y: auto;
    padding: 6px 10px 8px;
}

.simple-guide-legend.collapsed .simple-guide-legend-body {
    display: none;
}

.simple-guide-item {
    display: flex;
    gap: 8px;
    padding: 4px 0;
    border-bottom: 1px solid #eee;
}

.simple-guide-item:last-child {
    border-bottom: 0;
}

/* passo cujo alvo nao existe: o texto continua legível, sem o número —
   é o comportamento pedido no plano para nunca quebrar a venda */
.simple-guide-item.is-orphan .simple-guide-num {
    display: none;
}

.simple-guide-num {
    flex: 0 0 22px;
    height: 22px;
    margin-top: 1px;
    border-radius: 50%;
    background: #e65100;
    color: #fff;
    font: 700 13px/22px system-ui, sans-serif;
    text-align: center;
}

.simple-guide-item.is-orphan .simple-guide-num {
    background: #bbb;
}

.simple-guide-item.is-orphan {
    opacity: .65;
}
</style>

<div class="tutorial-fab" id="tutorialFab" title="Ajuda">?</div>

<div class="tutorial-overlay" id="tutorialOverlay"></div>

<div class="tutorial-step" id="tutorialStep">
    <div class="step-number" id="stepNumber">1/5</div>
    <div class="step-title" id="stepTitle"></div>
    <div class="step-text" id="stepText"></div>
    <div class="tutorial-nav">
        <button class="btn-prev" id="tutorialPrev">← Anterior</button>
        <button class="btn-next" id="tutorialNext">Próximo →</button>
        <button class="btn-close" id="tutorialClose">✕ Fechar</button>
        <span class="step-indicator" id="tutorialIndicator"></span>
    </div>
</div>

<script>
(function() {
    var tutorialSteps = [];
    var current = 0;
    var active = false;

    function getEl(sel) {
        if (!sel) return null;
        if (sel.charAt(0) === '#') return document.getElementById(sel.slice(1));
        return document.querySelector(sel);
    }

    function clearHighlight() {
        document.querySelectorAll('.tutorial-highlight').forEach(function(el) {
            el.classList.remove('tutorial-highlight');
        });
    }

    function showStep(index) {
        if (!tutorialSteps[index]) return;
        var step = tutorialSteps[index];

        clearHighlight();

        $('#stepNumber').text('Passo ' + (index + 1) + ' de ' + tutorialSteps.length);
        $('#stepTitle').text(step.title);
        $('#stepText').html(step.text);
        $('#tutorialIndicator').text((index + 1) + '/' + tutorialSteps.length);

        var target = getEl(step.target);
        var left = 20;
        var top = 20;

        if (target) {
            target.classList.add('tutorial-highlight');
            var rect = target.getBoundingClientRect();
            left = rect.right + 16;
            top = rect.top;

            if (left + 460 > window.innerWidth) {
                left = Math.max(16, rect.left - 460);
            }
            if (top + 300 > window.innerHeight) {
                top = Math.max(16, window.innerHeight - 340);
            }
            if (top < 10) top = 10;
        }

        var stepEl = $('#tutorialStep');
        stepEl.css({ left: left + 'px', top: top + 'px' });

        $('#tutorialPrev').toggle(index > 0);
        $('#tutorialNext').text(index < tutorialSteps.length - 1 ? 'Próximo →' : 'Concluir ✓');
    }

    window.startTutorial = function() {
        tutorialSteps = window._tutorialSteps || [];
        if (!tutorialSteps.length) {
            $.notify({message: 'Nenhum tutorial disponível para esta página ainda.'}, {type: 'info'});
            return;
        }
        current = 0;
        active = true;
        $('body').addClass('tutorial-active');
        $('#tutorialOverlay').addClass('active');
        $('#tutorialStep').addClass('active');
        $('#tutorialFab').hide();
        showStep(0);
    };

    window.endTutorial = function() {
        active = false;
        $('body').removeClass('tutorial-active');
        $('#tutorialOverlay').removeClass('active');
        $('#tutorialStep').removeClass('active');
        $('#tutorialFab').show();
        clearHighlight();
    };

    $(document).ready(function() {
        $('#tutorialFab').on('click', window.startTutorial);

        // FAB (z-index 9997) fica acima do modal Bootstrap (1050) e cobre
        // botões do rodapé (ex.: "Novo" no modal de item em /sales);
        // esse BootstrapDialog só emite shown/hidden.bs.modal
        $(document)
            .on('shown.bs.modal', function() { $('#tutorialFab').hide(); })
            .on('hidden.bs.modal', function() {
                if (!$('.modal.in').length) $('#tutorialFab').fadeIn(150);
            });

        $('#tutorialNext').on('click', function() {
            if (current < tutorialSteps.length - 1) {
                current++;
                showStep(current);
            } else {
                window.endTutorial();
            }
        });

        $('#tutorialPrev').on('click', function() {
            if (current > 0) {
                current--;
                showStep(current);
            }
        });

        $('#tutorialClose, #tutorialOverlay').on('click', window.endTutorial);

        $(document).on('keydown', function(e) {
            if (!active) return;
            if (e.key === 'Escape') window.endTutorial();
            if (e.key === 'ArrowRight') $('#tutorialNext').click();
            if (e.key === 'ArrowLeft') $('#tutorialPrev').click();
        });
    });
})();
    </script>

<!-- Anotacoes fixas (tarefa 12 do plano simplificar-fluxo-venda-pdv).
     A legenda e sempre presente no Modo Simples; os selos sao gerados por JS. -->
<div class="simple-guide-legend collapsed" id="simpleGuideLegend" style="display: none;">
    <div class="simple-guide-legend-head" id="simpleGuideLegendHead">
        <span>Como fazer uma venda</span>
        <span class="simple-guide-caret" id="simpleGuideCaret">&#9650;</span>
    </div>
    <div class="simple-guide-legend-body" id="simpleGuideLegendBody"></div>
</div>

<script>
/* Motor das anotacoes fixas — tarefa 12 do plano simplificar-fluxo-venda-pdv.
   IIFE separado do walkthrough de proposito: showStep/startTutorial/endTutorial
   servem as outras telas (itens, vendas, clientes) e nao podem ser arriscados
   por causa de um recurso so do Modo Simples.

   Silencia por construcao em qualquer tela que nao defina
   window._guideAnnotations — nos outros lugares do sistema este arquivo
   simplesmente nao faz nada. */
(function() {
    var $layer = null;
    var badges = [];          // { el, sel } guardado por indice, nunca recriado
    var pending = null;       // debounce do MutationObserver

    function getEl(sel) {
        if (!sel) return null;
        if (sel.charAt(0) === '#') return document.getElementById(sel.slice(1));
        return document.querySelector(sel);
    }

    function annotations() {
        return Array.isArray(window._guideAnnotations) ? window._guideAnnotations : [];
    }

    function ensureLayer() {
        if ($layer && $layer.length) return $layer;
        $layer = $('<div class="simple-guide-layer"></div>').appendTo('body');
        return $layer;
    }

    /* Reconcilia por indice em vez de recriar tudo. E o que garante duas
       coisas de uma vez: nunca duplica (um indice, um selo) e um alvo que so
       aparece depois — a linha do carrinho, que so existe com item — ganha
       o selo sozinho quando chega. */
    function render() {
        var steps = annotations();
        var layer = ensureLayer();

        for (var i = 0; i < steps.length; i++) {
            if (!badges[i]) {
                var el = $('<div class="simple-guide-badge"></div>').text(i + 1);
                badges[i] = { el: el, sel: steps[i].target };
                layer.append(el);
            }
            badges[i].sel = steps[i].target;
        }
        // sobrou selo de anotacao que nao existe mais
        for (var j = badges.length; j > steps.length; j--) {
            if (badges[j - 1]) { badges[j - 1].el.remove(); badges[j - 1] = null; }
        }
        badges.length = steps.length;

        position();
        renderLegend();
    }

    /* getBoundingClientRect ja e relativo a viewport, entao com position:fixed
       os selos acompanham a rolagem sem trabalho extra. O clamp existe para o
       caso de alvo na borda da tela, onde o selo sairia dela. */
    function position() {
        var steps = annotations();
        for (var i = 0; i < steps.length; i++) {
            var b = badges[i];
            if (!b) continue;
            var target = getEl(steps[i].target);
            if (!target) { b.el.hide(); continue; }   // alvo ausente: sem selo
            var r = target.getBoundingClientRect();
            if (!r.width && !r.height) { b.el.hide(); continue; }
            var x = r.left - 18;
            var y = r.top + 16;
            x = Math.max(16, Math.min(x, window.innerWidth - 16));
            y = Math.max(16, Math.min(y, window.innerHeight - 16));
            b.el.css({ left: x + 'px', top: y + 'px' }).show();
        }
    }

    function renderLegend() {
        var steps = annotations();
        var $body = $('#simpleGuideLegendBody');
        if (!$body.length) return;
        $body.empty();
        steps.forEach(function(step, i) {
            var orfa = !getEl(step.target);
            $body.append(
                $('<div class="simple-guide-item"></div>')
                    .toggleClass('is-orphan', orfa)
                    .append($('<span class="simple-guide-num"></span>').text(i + 1))
                    .append($('<span class="simple-guide-text"></span>').html(step.text || ''))
            );
        });
    }

    function visible() {
        return annotations().length > 0 && window.simpleMode === true && window.showGuide !== false;
    }

    function apply() {
        var on = visible();
        var $legend = $('#simpleGuideLegend');
        if (!on) {
            badges.forEach(function(b) { if (b) b.el.hide(); });
            $legend.hide();
            return;
        }
        $legend.show();
        render();
    }

    window.refreshGuide = function() { if (visible()) position(); };
    window.renderSimpleGuide = apply;

    $(document).ready(function() {
        var $legend = $('#simpleGuideLegend');
        var $head = $('#simpleGuideLegendHead');

        $head.on('click', function() {
            $legend.toggleClass('collapsed');
            $('#simpleGuideCaret').html($legend.hasClass('collapsed') ? '&#9650;' : '&#9660;');
        });

        $(window).on('resize scroll', function() {
            if (visible()) position();
        });

        /* Reancora depois de qualquer troca de HTML por AJAX. E o motivo de o
           plano pedir isto: e exatamente assim que os botoes do carrinho
             (sumiram) antes. Observa o body inteiro e reconcilia por indice, o
           que torna o resultado idempotente. */
        if (window.MutationObserver) {
            new MutationObserver(function() {
                if (!visible()) return;
                if (pending) clearTimeout(pending);
                pending = setTimeout(function() { pending = null; render(); }, 60);
            }).observe(document.body, { childList: true, subtree: true });
        }

        apply();
    });
})();
</script>
