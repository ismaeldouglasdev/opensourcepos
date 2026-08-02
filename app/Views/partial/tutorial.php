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

.tutorial-fab {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9997;
    width: 56px;
    height: 56px;
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
