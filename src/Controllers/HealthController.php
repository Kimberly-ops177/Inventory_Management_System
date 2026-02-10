<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Http\JsonResponse;
use PDO;

class HealthController
{
    /**
     * Health check endpoint
     * Returns system status and checks critical components
     */
    public function check(): JsonResponse
    {
        $checks = [
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'environment' => $_ENV['APP_ENV'] ?? 'unknown',
            'checks' => []
        ];

        // 1. Database connectivity
        try {
            $db = Database::connection();
            $stmt = $db->query('SELECT 1');
            $checks['checks']['database'] = [
                'status' => 'ok',
                'message' => 'Database connection successful'
            ];
        } catch (\Exception $e) {
            $checks['status'] = 'unhealthy';
            $checks['checks']['database'] = [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }

        // 2. Storage directory writable
        $logPath = __DIR__ . '/../../storage/logs';
        if (is_dir($logPath) && is_writable($logPath)) {
            $checks['checks']['storage'] = [
                'status' => 'ok',
                'message' => 'Storage directory is writable'
            ];
        } else {
            $checks['status'] = 'unhealthy';
            $checks['checks']['storage'] = [
                'status' => 'error',
                'message' => 'Storage directory is not writable'
            ];
        }

        // 3. Session status
        if (session_status() === PHP_SESSION_ACTIVE) {
            $checks['checks']['session'] = [
                'status' => 'ok',
                'message' => 'Session is active'
            ];
        } else {
            $checks['checks']['session'] = [
                'status' => 'warning',
                'message' => 'Session not started'
            ];
        }

        // 4. PHP version
        $checks['checks']['php'] = [
            'status' => 'ok',
            'version' => PHP_VERSION,
            'message' => 'PHP version: ' . PHP_VERSION
        ];

        // 5. Required extensions
        $requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json'];
        $missingExtensions = [];

        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $missingExtensions[] = $ext;
            }
        }

        if (empty($missingExtensions)) {
            $checks['checks']['extensions'] = [
                'status' => 'ok',
                'message' => 'All required PHP extensions loaded'
            ];
        } else {
            $checks['status'] = 'unhealthy';
            $checks['checks']['extensions'] = [
                'status' => 'error',
                'message' => 'Missing extensions: ' . implode(', ', $missingExtensions)
            ];
        }

        // 6. Database tables check
        try {
            $db = Database::connection();
            $stmt = $db->query("SHOW TABLES LIKE 'users'");
            $tablesExist = $stmt->rowCount() > 0;

            if ($tablesExist) {
                $checks['checks']['migrations'] = [
                    'status' => 'ok',
                    'message' => 'Database tables exist'
                ];
            } else {
                $checks['checks']['migrations'] = [
                    'status' => 'warning',
                    'message' => 'Database tables not found - run migrations'
                ];
            }
        } catch (\Exception $e) {
            $checks['checks']['migrations'] = [
                'status' => 'error',
                'message' => 'Cannot check tables: ' . $e->getMessage()
            ];
        }

        // Set HTTP status code
        $statusCode = $checks['status'] === 'healthy' ? 200 : 503;

        return new JsonResponse([
            'success' => $checks['status'] === 'healthy',
            'data' => $checks
        ], $statusCode);
    }
}
