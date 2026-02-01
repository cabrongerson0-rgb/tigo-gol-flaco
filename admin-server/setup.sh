#!/bin/bash

# Admin Panel Setup Script for Railway Deployment

echo "🚀 Configurando Panel de Control Tigo PSE..."

# Check if we're in the admin-server directory
if [ ! -f "package.json" ]; then
    echo "❌ Este script debe ejecutarse desde la carpeta admin-server/"
    exit 1
fi

# Install dependencies
echo "📦 Instalando dependencias..."
npm install

# Create .env file if it doesn't exist
if [ ! -f ".env" ]; then
    echo "⚙️  Creando archivo .env..."
    cp .env.example .env
    echo "✅ Por favor configura las variables en .env antes de continuar"
fi

# Railway-specific setup
if [ "$RAILWAY_ENVIRONMENT" ]; then
    echo "🚂 Configurando para Railway..."
    export NODE_ENV=production
fi

# Start the server
echo "🌟 Iniciando servidor admin..."
npm start