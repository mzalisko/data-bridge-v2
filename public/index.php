<?php

declare(strict_types=1);

/**
 * DataBridge CRM — Single Entry Point
 * All HTTP requests are routed through this file via .htaccess / nginx
 */

define('BASE_PATH', dirname(__DIR__));
define('START_TIME', microtime(true));

// Autoloader (PSR-4 via Composer)
require_once BASE_PATH . '/vendor/autoload.php';

// Load environment
if (file_exists(BASE_PATH . '/.env')) {
    $lines = file(BASE_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$key, $val] = array_pad(explode('=', $line, 2), 2, '');
        $_ENV[trim($key)] = trim($val);
    }
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Session configuration
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Strict');
if (!empty($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production') {
    ini_set('session.cookie_secure', '1');
}
session_start();

// Error handling
if (!empty($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

use DataBridge\Core\Router;

$router = new Router();
require_once BASE_PATH . '/src/routes.php';
$router->dispatch();
