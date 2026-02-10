<?php
/**
 * System Requirements Checker
 * Run: php check-requirements.php
 */

echo "╔═══════════════════════════════════════════╗\n";
echo "║  System Requirements Check                ║\n";
echo "╚═══════════════════════════════════════════╝\n\n";

$passed = true;

// Check PHP version
echo "→ PHP Version:\n";
$phpVersion = PHP_VERSION;
$minVersion = '8.1.0';

if (version_compare($phpVersion, $minVersion, '>=')) {
    echo "  ✓ PHP $phpVersion (minimum: $minVersion)\n";
} else {
    echo "  ✗ PHP $phpVersion (minimum required: $minVersion)\n";
    $passed = false;
}
echo "\n";

// Check required extensions
echo "→ Required PHP Extensions:\n";
$required = ['pdo', 'pdo_mysql', 'mbstring', 'json'];

foreach ($required as $ext) {
    if (extension_loaded($ext)) {
        echo "  ✓ $ext\n";
    } else {
        echo "  ✗ $ext (MISSING)\n";
        $passed = false;
    }
}
echo "\n";

// Check optional extensions
echo "→ Optional PHP Extensions:\n";
$optional = ['curl', 'openssl', 'zip'];

foreach ($optional as $ext) {
    if (extension_loaded($ext)) {
        echo "  ✓ $ext\n";
    } else {
        echo "  ○ $ext (recommended but not required)\n";
    }
}
echo "\n";

// Check Composer
echo "→ Composer:\n";
$composerCheck = shell_exec('composer --version 2>&1');
if ($composerCheck && strpos($composerCheck, 'Composer') !== false) {
    echo "  ✓ Composer installed\n";
} else {
    echo "  ✗ Composer not found\n";
    echo "    Install from: https://getcomposer.org/\n";
    $passed = false;
}
echo "\n";

// Check .env file
echo "→ Configuration:\n";
if (file_exists(__DIR__ . '/.env')) {
    echo "  ✓ .env file exists\n";
} else {
    echo "  ✗ .env file missing\n";
    echo "    Copy .env.example to .env\n";
    $passed = false;
}
echo "\n";

// Check vendor directory
echo "→ Dependencies:\n";
if (is_dir(__DIR__ . '/vendor')) {
    echo "  ✓ Dependencies installed\n";
} else {
    echo "  ✗ Dependencies not installed\n";
    echo "    Run: composer install\n";
    $passed = false;
}
echo "\n";

// Check writable directories
echo "→ Permissions:\n";
$writableDirs = ['storage', 'storage/logs', 'database'];

foreach ($writableDirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (is_dir($path) && is_writable($path)) {
        echo "  ✓ $dir is writable\n";
    } else {
        echo "  ✗ $dir is not writable\n";
        $passed = false;
    }
}
echo "\n";

// Database connection test
echo "→ Database Connection:\n";
if (file_exists(__DIR__ . '/.env') && is_dir(__DIR__ . '/vendor')) {
    try {
        // Parse .env file manually to avoid session_start() in bootstrap.php
        $envFile = file_get_contents(__DIR__ . '/.env');
        $envLines = explode("\n", $envFile);
        $env = [];
        foreach ($envLines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $env[trim($key)] = trim($value);
            }
        }

        // Create direct PDO connection
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $env['DB_HOST'] ?? 'localhost',
            $env['DB_PORT'] ?? '3306',
            $env['DB_DATABASE'] ?? ''
        );

        $db = new PDO(
            $dsn,
            $env['DB_USERNAME'] ?? 'root',
            $env['DB_PASSWORD'] ?? '',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );

        $stmt = $db->query('SELECT 1');
        echo "  ✓ Database connection successful\n";

        // Check if migrations ran
        try {
            $stmt = $db->query("SELECT COUNT(*) FROM users");
            echo "  ✓ Database tables exist\n";
        } catch (PDOException $e) {
            echo "  ○ Database tables not found\n";
            echo "    Run: php database/migrate.php\n";
        }
    } catch (Exception $e) {
        echo "  ✗ Database connection failed\n";
        echo "    Error: " . $e->getMessage() . "\n";
        $passed = false;
    }
} else {
    echo "  ○ Skipped (dependencies not installed)\n";
}
echo "\n";

// Summary
echo "╔═══════════════════════════════════════════╗\n";
if ($passed) {
    echo "║  ✓ All Requirements Met!                  ║\n";
    echo "╚═══════════════════════════════════════════╝\n\n";
    echo "🚀 You can start the development server:\n";
    echo "   php -S localhost:8000 -t public\n\n";
    echo "📝 Or run the Windows deployment script:\n";
    echo "   deploy-windows.bat\n";
} else {
    echo "║  ✗ Some Requirements Missing              ║\n";
    echo "╚═══════════════════════════════════════════╝\n\n";
    echo "⚠️  Fix the issues above before proceeding.\n";
}
echo "\n";

exit($passed ? 0 : 1);
