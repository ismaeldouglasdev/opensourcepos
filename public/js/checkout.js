var payment_type_selected = '';

function openCheckoutModal() {
    var span = document.getElementById('sale_total');
    if (!span) {
        alert('Nao encontrou sale_total');
        return;
    }
    
    var totalText = span.textContent || '0';
    var total = parseFloat(totalText.replace(/[^0-9.,]/g, '').replace(',', '.')) || 0;
    
    if (total <= 0) {
        alert('Carrinho vazio');
        return;
    }
    
    document.getElementById('checkout_total').textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
    document.getElementById('checkout_amount').value = '';
    document.getElementById('checkout_amount').placeholder = total.toFixed(2).replace('.', ',');
    document.getElementById('troco_display').textContent = ' ';
    document.getElementById('amount_group').style.display = 'none';
    payment_type_selected = '';
    
    jQuery('#checkoutModal').modal('show');
}

function selectPayment(type, lang_key) {
    payment_type_selected = lang_key;
    
    document.querySelectorAll('.payment-btn').forEach(function(btn) {
        btn.classList.remove('active');
    });
    var btn = document.getElementById('payment_btn_' + type);
    if (btn) btn.classList.add('active');
    
    if (type === 'cash') {
        document.getElementById('amount_group').style.display = 'block';
    } else {
        document.getElementById('amount_group').style.display = 'none';
        document.getElementById('troco_display').textContent = ' ';
    }
}

function calculateTroco() {
    var span = document.getElementById('sale_total');
    var totalText = span ? span.textContent : '0';
    var total = parseFloat(totalText.replace(/[^0-9.,]/g, '').replace(',', '.')) || 0;
    
    var amountInput = document.getElementById('checkout_amount');
    var amount = parseFloat((amountInput ? amountInput.value : '0').replace(',', '.')) || 0;
    var troco = amount - total;
    
    var trocoDisplay = document.getElementById('troco_display');
    if (troco >= 0) {
        trocoDisplay.textContent = 'Troco: R$ ' + troco.toFixed(2).replace('.', ',');
    } else {
        trocoDisplay.textContent = 'Falta: R$ ' + Math.abs(troco).toFixed(2).replace('.', ',');
    }
}

function finishCheckout() {
    if (!payment_type_selected) {
        alert('Selecione pagamento');
        return;
    }
    
    var amountInput = document.getElementById('checkout_amount');
    var amount = parseFloat((amountInput ? amountInput.value : '0').replace(',', '.')) || 0;
    
    if (payment_type_selected === 'Dinheiro' && amount <= 0) {
        alert('Informe o valor');
        return;
    }
    
    jQuery('#checkoutModal').modal('hide');
    
    // Preencher campos originais
    var paymentSelect = document.getElementById('payment_types');
    if (paymentSelect) {
        for (var i = 0; i < paymentSelect.options.length; i++) {
            if (paymentSelect.options[i].text === payment_type_selected || 
                paymentSelect.options[i].value === payment_type_selected) {
                paymentSelect.selectedIndex = i;
                break;
            }
        }
    }
    
    var amountTendered = document.getElementById('amount_tendered');
    if (amountTendered) {
        amountTendered.value = amount > 0 ? amount : '';
    }
    
    // Ir para a página de adicionar pagamento via AJAX
    var form = document.getElementById('add_payment_form');
    if (form) {
        var formData = new FormData(form);
        
        jQuery.ajax({
            url: form.action,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Após adicionar pagamento, redirecionar para completar venda
                window.location.href = '<?= site_url("sales/complete") ?>';
            },
            error: function() {
                // Se der erro, tentar redirecionar mesmo assim
                window.location.href = '<?= site_url("sales/complete") ?>';
            }
        });
    } else {
        // Fallback: redirecionar diretamente
        window.location.href = '<?= site_url("sales/complete") ?>';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var amountInput = document.getElementById('checkout_amount');
    if (amountInput) amountInput.addEventListener('input', calculateTroco);
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F2') {
            e.preventDefault();
            openCheckoutModal();
        }
    });
});
