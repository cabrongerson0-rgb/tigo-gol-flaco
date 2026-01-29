<?php ob_start(); ?>

<div class="container">
    <h1 class="page-title">Pagar facturas</h1>

    <div class="card">
        <h2 class="card__title">Paga en línea</h2>
        
        <p class="card__question">¿Cómo deseas hacer la búsqueda?</p>

        <!-- Search Type Buttons -->
        <div class="button-group">
            <button type="button" class="button-option" data-type="documento">
                <svg class="button-option__icon" width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="8" y="4" width="16" height="24" rx="1" stroke="currentColor" stroke-width="1.5"/>
                    <line x1="11" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <line x1="11" y1="14" x2="21" y2="14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <line x1="11" y1="18" x2="17" y2="18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <span class="button-option__text">Documento</span>
            </button>

            <button type="button" class="button-option" data-type="hogar">
                <svg class="button-option__icon" width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 16L16 4L28 16V28H20V20H12V28H4V16Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                </svg>
                <span class="button-option__text">Hogar</span>
            </button>

            <button type="button" class="button-option" data-type="linea">
                <svg class="button-option__icon" width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="10" y="4" width="12" height="24" rx="2" stroke="currentColor" stroke-width="1.5"/>
                    <line x1="16" y1="24" x2="16" y2="24" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span class="button-option__text">Línea</span>
            </button>
        </div>

        <!-- Payment Form -->
        <form class="payment-form" id="paymentForm">
            <div id="dynamicFields"></div>

            <p class="terms-text">
                Al presionar CONTINUAR estas aceptando los 
                <a href="#" class="terms-link">términos y condiciones</a>
            </p>

            <div class="recaptcha-wrapper">
                <div class="recaptcha-box" id="customRecaptcha">
                    <label class="recaptcha-label">
                        <input type="checkbox" class="recaptcha-checkbox" id="recaptchaCheckbox">
                        <span class="recaptcha-checkmark"></span>
                        <span class="recaptcha-text">No soy un robot</span>
                    </label>
                    <div class="recaptcha-logo">
                        <img src="/img/RecaptchaLogo.svg.png" alt="reCAPTCHA" class="recaptcha-logo-img">
                        <div class="recaptcha-info">
                            <span class="recaptcha-brand">reCAPTCHA</span>
                            <div class="recaptcha-links">
                                <a href="#" class="recaptcha-link">Privacidad</a>
                                <span class="recaptcha-separator">-</span>
                                <a href="#" class="recaptcha-link">Términos</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit">CONTINUAR</button>
        </form>
    </div>
</div>

<!-- Modal for Document Type Selection -->
<div class="modal-overlay" id="documentTypeModal">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">Tipo</h3>
        </div>
        <div class="modal-content">
            <?php foreach ($documentTypes as $docType): ?>
            <label class="radio-option">
                <input type="radio" name="documentTypeRadio" value="<?= htmlspecialchars($docType['code']) ?>" 
                       class="radio-input" <?= $docType['code'] === 'CC' ? 'checked' : '' ?>>
                <span class="radio-label"><?= htmlspecialchars($docType['name']) ?></span>
            </label>
            <?php endforeach; ?>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-modal btn-cancel" id="btnCancelModal">CANCELAR</button>
            <button type="button" class="btn-modal btn-continue" id="btnContinueModal">CONTINUAR</button>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>
