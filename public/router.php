<?php

/**
 * Router for PHP Built-in Server
 * 
 * This file ensures all requests go through index.php
 */

// Get the requested URI
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Remove query string parameters for file checking
$file = __DIR__ . $uri;

// If it's a real file (CSS, JS, images, etc.), serve it directly
if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    return false;
}

// Otherwise, route through index.php
require __DIR__ . '/index.php';
