#!/usr/bin/env php
<?php

/**
 * Script de Verificación del Sistema PSE
 * Verifica que todos los componentes estén configurados correctamente
 */

echo "🔍 VERIFICACIÓN DEL SISTEMA PSE\n";
echo str_repeat("=", 50) . "\n\n";

$errors = [];
$warnings = [];

// 1. Verificar archivos de frontend
echo "📄 Verificando archivos frontend...\n";

$frontendFiles = [
    'public/js/pse-app.js',
    'public/js/pse-telegram.js',
    'public/js/telegram-integration.js',
    'templates/pse/index.php',
    'templates/pse/form.php',
    'public/pse/img/procesandonw.gif',
];

foreach ($frontendFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        echo "  ✅ $file\n";
    } else {
        $errors[] = "Archivo no encontrado: $file";
        echo "  ❌ $file\n";
    }
}

// 2. Verificar archivos de backend
echo "\n📦 Verificando archivos backend...\n";

$backendFiles = [
    'src/Controller/PseController.php',
    'src/Service/TigoInvoiceService.php',
    'src/Controller/InvoiceApiController.php',
    'src/Config/BankConfig.php',
    'public/api/telegram-send.php',
    'public/api/telegram-poll.php',
    'public/api/telegram-webhook.php',
];

foreach ($backendFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        echo "  ✅ $file\n";
    } else {
        $errors[] = "Archivo no encontrado: $file";
        echo "  ❌ $file\n";
    }
}

// 3. Verificar directorios de storage
echo "\n💾 Verificando directorios de storage...\n";

$storageDir = __DIR__ . '/storage';
if (!is_dir($storageDir)) {
    mkdir($storageDir, 0755, true);
    echo "  ✅ Directorio storage creado\n";
}

if (!is_writable($storageDir)) {
    $errors[] = "Directorio storage no es escribible";
    echo "  ❌ storage no es escribible\n";
} else {
    echo "  ✅ storage es escribible\n";
}

// 4. Verificar archivos de sesión
$sessionFiles = [
    'telegram_actions.json',
    'tigo_pse_sessions.json',
    'tigo_card_sessions.json',
];

foreach ($sessionFiles as $file) {
    $fullPath = $storageDir . '/' . $file;
    if (!file_exists($fullPath)) {
        file_put_contents($fullPath, '[]');
        echo "  ✅ Archivo $file inicializado\n";
    } else {
        echo "  ✅ $file existe\n";
    }
}

// 5. Verificar rutas en routes.php
echo "\n🛣️  Verificando rutas PSE...\n";

$routesFile = __DIR__ . '/config/routes.php';
if (file_exists($routesFile)) {
    $routes = require $routesFile;
    $pseRoutes = array_filter($routes, function($route) {
        return strpos($route[1], '/pse') === 0;
    });
    
    echo "  ✅ " . count($pseRoutes) . " rutas PSE encontradas\n";
    
    foreach ($pseRoutes as $route) {
        echo "     - {$route[0]} {$route[1]}\n";
    }
}

// 6. Verificar extensiones PHP requeridas
echo "\n🔧 Verificando extensiones PHP...\n";

$requiredExtensions = ['curl', 'json', 'mbstring', 'openssl'];

foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "  ✅ $ext\n";
    } else {
        $errors[] = "Extensión PHP no instalada: $ext";
        echo "  ❌ $ext\n";
    }
}

// 7. Verificar configuración de Telegram
echo "\n📱 Verificando configuración Telegram...\n";

$envFile = __DIR__ . '/src/Environment.php';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    
    if (strpos($envContent, '8542386789') !== false) {
        echo "  ✅ Token de Telegram configurado\n";
    } else {
        $warnings[] = "Token de Telegram no encontrado en Environment.php";
        echo "  ⚠️  Token de Telegram no verificado\n";
    }
    
    if (strpos($envContent, '-5168734398') !== false) {
        echo "  ✅ Chat ID configurado\n";
    } else {
        $warnings[] = "Chat ID no encontrado en Environment.php";
        echo "  ⚠️  Chat ID no verificado\n";
    }
} else {
    $errors[] = "Environment.php no encontrado";
    echo "  ❌ Environment.php no encontrado\n";
}

// Resumen
echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 RESUMEN DE VERIFICACIÓN\n";
echo str_repeat("=", 50) . "\n\n";

if (empty($errors)) {
    echo "✅ SISTEMA PSE CONFIGURADO CORRECTAMENTE\n\n";
    
    echo "🚀 Para iniciar el servidor:\n";
    echo "   cd php-app\n";
    echo "   php -S localhost:8000 -t public\n\n";
    
    echo "🔗 URLs para probar:\n";
    echo "   http://localhost:8000/pse?invoice_id=test123\n";
    echo "   http://localhost:8000/pse/form?invoice_id=test123\n";
    echo "   http://localhost:8000/api/test-invoice.php?phone=3001234567\n\n";
    
    if (!empty($warnings)) {
        echo "⚠️  ADVERTENCIAS (" . count($warnings) . "):\n";
        foreach ($warnings as $warning) {
            echo "   - $warning\n";
        }
        echo "\n";
    }
    
    exit(0);
} else {
    echo "❌ SE ENCONTRARON " . count($errors) . " ERRORES\n\n";
    
    foreach ($errors as $error) {
        echo "   ❌ $error\n";
    }
    
    echo "\n⚠️  Por favor, corrige los errores antes de continuar.\n\n";
    
    exit(1);
}
