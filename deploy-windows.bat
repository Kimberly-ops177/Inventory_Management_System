@echo off
REM =====================================================
REM Inventory System - Windows Deployment Script
REM =====================================================
REM Usage: deploy-windows.bat

echo ╔═══════════════════════════════════════════╗
echo ║  Inventory System - Windows Setup         ║
echo ╚═══════════════════════════════════════════╝
echo.

echo → Checking PHP version...
php -v | findstr "PHP"
if %ERRORLEVEL% NEQ 0 (
    echo ✗ PHP not found
    exit /b 1
)

echo ✓ PHP installed
echo.

echo → Checking PHP extensions...
php -m | findstr /C:"pdo" >nul
if %ERRORLEVEL% NEQ 0 (
    echo ✗ PDO extension missing
    echo Install PDO: Enable extension=pdo in php.ini
    exit /b 1
)
echo ✓ PDO extension loaded

php -m | findstr /C:"pdo_mysql" >nul
if %ERRORLEVEL% NEQ 0 (
    echo ✗ PDO MySQL extension missing
    echo Install: Enable extension=pdo_mysql in php.ini
    exit /b 1
)
echo ✓ PDO MySQL extension loaded

php -m | findstr /C:"mbstring" >nul
if %ERRORLEVEL% NEQ 0 (
    echo ✗ mbstring extension missing
    exit /b 1
)
echo ✓ mbstring extension loaded
echo.

echo → Checking .env file...
if not exist ".env" (
    echo ✗ .env file not found
    echo Copy .env.example to .env and configure it
    exit /b 1
)
echo ✓ .env file exists
echo.

echo → Testing database connection...
php -r "require 'vendor/autoload.php'; require 'src/bootstrap.php'; try { $db = App\Database::connection(); $db->query('SELECT 1'); echo '✓ Database connection successful' . PHP_EOL; } catch (Exception $e) { echo '✗ Database connection failed: ' . $e->getMessage() . PHP_EOL; exit(1); }"
if %ERRORLEVEL% NEQ 0 (
    exit /b 1
)
echo.

echo → Installing dependencies...
call composer install --no-dev --optimize-autoloader
if %ERRORLEVEL% NEQ 0 (
    echo ✗ Composer install failed
    exit /b 1
)
echo ✓ Dependencies installed
echo.

echo → Running database migrations...
php database/migrate.php
if %ERRORLEVEL% NEQ 0 (
    echo ✗ Migration failed
    exit /b 1
)
echo.

echo → Starting PHP development server...
echo ✓ Server starting on http://localhost:8000
echo.
echo ╔═══════════════════════════════════════════╗
echo ║  ✓ Setup Complete!                        ║
echo ╚═══════════════════════════════════════════╝
echo.
echo Access the application at: http://localhost:8000
echo Login: admin@example.com / password
echo.
echo Press Ctrl+C to stop the server when done
echo.

php -S localhost:8000 -t public
