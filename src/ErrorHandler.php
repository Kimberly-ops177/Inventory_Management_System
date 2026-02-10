<?php
declare(strict_types=1);

namespace App;

use Throwable;

class ErrorHandler
{
    private static bool $registered = false;
    private static string $logPath = '';
    private static bool $debug = false;

    /**
     * Register error and exception handlers
     */
    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        self::$debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
        self::$logPath = $_ENV['LOG_PATH'] ?? 'storage/logs';

        // Set error handler
        set_error_handler([self::class, 'handleError']);

        // Set exception handler
        set_exception_handler([self::class, 'handleException']);

        // Set shutdown handler for fatal errors
        register_shutdown_function([self::class, 'handleShutdown']);

        self::$registered = true;
    }

    /**
     * Handle PHP errors
     */
    public static function handleError(
        int $severity,
        string $message,
        string $file = '',
        int $line = 0
    ): bool {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        self::logError($severity, $message, $file, $line);

        if (self::$debug) {
            // In debug mode, convert to exception
            throw new \ErrorException($message, 0, $severity, $file, $line);
        }

        return true;
    }

    /**
     * Handle uncaught exceptions
     */
    public static function handleException(Throwable $exception): void
    {
        self::logException($exception);

        // Clear any output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Determine if this is an API request
        $isApi = strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') === 0;

        if ($isApi) {
            self::renderApiError($exception);
        } else {
            self::renderWebError($exception);
        }

        exit(1);
    }

    /**
     * Handle fatal errors on shutdown
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            self::logError($error['type'], $error['message'], $error['file'], $error['line']);

            if (!headers_sent()) {
                http_response_code(500);
            }

            if (self::$debug) {
                echo "<h1>Fatal Error</h1>";
                echo "<p><strong>Message:</strong> {$error['message']}</p>";
                echo "<p><strong>File:</strong> {$error['file']} on line {$error['line']}</p>";
            } else {
                echo "<h1>500 Internal Server Error</h1>";
                echo "<p>An error occurred. Please try again later.</p>";
            }
        }
    }

    /**
     * Log an error to file
     */
    private static function logError(int $severity, string $message, string $file, int $line): void
    {
        $severityNames = [
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE_ERROR',
            E_CORE_WARNING => 'CORE_WARNING',
            E_COMPILE_ERROR => 'COMPILE_ERROR',
            E_COMPILE_WARNING => 'COMPILE_WARNING',
            E_USER_ERROR => 'USER_ERROR',
            E_USER_WARNING => 'USER_WARNING',
            E_USER_NOTICE => 'USER_NOTICE',
            E_STRICT => 'STRICT',
            E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER_DEPRECATED',
        ];

        $severityName = $severityNames[$severity] ?? 'UNKNOWN';
        $logMessage = sprintf(
            "[%s] %s: %s in %s on line %d\n",
            date('Y-m-d H:i:s'),
            $severityName,
            $message,
            $file,
            $line
        );

        self::writeLog($logMessage);
    }

    /**
     * Log an exception to file
     */
    private static function logException(Throwable $exception): void
    {
        $logMessage = sprintf(
            "[%s] EXCEPTION: %s\nMessage: %s\nFile: %s on line %d\nStack trace:\n%s\n\n",
            date('Y-m-d H:i:s'),
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        self::writeLog($logMessage);
    }

    /**
     * Write log message to file
     */
    private static function writeLog(string $message): void
    {
        $logFile = self::$logPath . '/error.log';

        // Create directory if it doesn't exist
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        // Write to log file
        @file_put_contents($logFile, $message, FILE_APPEND);
    }

    /**
     * Render error response for API requests
     */
    private static function renderApiError(Throwable $exception): void
    {
        header('Content-Type: application/json');
        http_response_code(500);

        if (self::$debug) {
            echo json_encode([
                'success' => false,
                'error' => 'Internal Server Error',
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => explode("\n", $exception->getTraceAsString())
            ], JSON_PRETTY_PRINT);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Internal Server Error',
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }

    /**
     * Render error page for web requests
     */
    private static function renderWebError(Throwable $exception): void
    {
        http_response_code(500);

        if (self::$debug) {
            // Detailed error page for development
            echo "<!DOCTYPE html><html><head><title>Error</title>";
            echo "<style>body{font-family:Arial,sans-serif;margin:40px;}h1{color:#d32f2f;}";
            echo ".error{background:#ffebee;padding:20px;border-left:4px solid #d32f2f;}";
            echo "pre{background:#263238;color:#aed581;padding:15px;overflow:auto;}</style></head><body>";
            echo "<h1>Error Occurred</h1>";
            echo "<div class='error'>";
            echo "<h2>" . htmlspecialchars(get_class($exception)) . "</h2>";
            echo "<p><strong>Message:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
            echo "<p><strong>File:</strong> " . htmlspecialchars($exception->getFile()) . " on line " . $exception->getLine() . "</p>";
            echo "</div>";
            echo "<h3>Stack Trace:</h3>";
            echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
            echo "</body></html>";
        } else {
            // User-friendly error page for production
            echo "<!DOCTYPE html><html><head><title>Error</title>";
            echo "<style>body{font-family:Arial,sans-serif;text-align:center;margin-top:100px;}";
            echo "h1{color:#555;font-size:48px;}p{color:#777;font-size:18px;}</style></head><body>";
            echo "<h1>500</h1>";
            echo "<p>Oops! Something went wrong.</p>";
            echo "<p><a href='/'>Go back to homepage</a></p>";
            echo "</body></html>";
        }
    }
}
