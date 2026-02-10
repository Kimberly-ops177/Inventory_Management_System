<?php
declare(strict_types=1);

use Dotenv\Dotenv;
use App\ErrorHandler;

$rootDir = dirname(__DIR__);

// Load environment variables
if (class_exists(Dotenv::class) && file_exists($rootDir . '/.env')) {
    $dotenv = Dotenv::createImmutable($rootDir);
    $dotenv->safeLoad();
}

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

// Configure error reporting based on environment
$isProduction = ($_ENV['APP_ENV'] ?? 'local') === 'production';
$debug = ($_ENV['APP_DEBUG'] ?? 'true') === 'true';

if ($isProduction) {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', $debug ? '1' : '0');
}

// Register global error handler
ErrorHandler::register();

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => filter_var($_ENV['SESSION_HTTP_ONLY'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'cookie_secure' => filter_var($_ENV['SESSION_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'cookie_samesite' => $_ENV['SESSION_SAME_SITE'] ?? 'Lax',
    ]);
}
