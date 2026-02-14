<?php
declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
            $port = (int)($_ENV['DB_PORT'] ?? 5432);
            $dbName = $_ENV['DB_DATABASE'] ?? 'inventory_db';
            $user = $_ENV['DB_USERNAME'] ?? 'inventory_user';
            $pass = $_ENV['DB_PASSWORD'] ?? 'inventory_pass';

            // Support both PostgreSQL and MySQL
            $driver = $_ENV['DB_DRIVER'] ?? 'pgsql';
            if ($driver === 'mysql') {
                $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $dbName);
            } else {
                $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $host, $port, $dbName);
            }

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$connection = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                throw new \RuntimeException('Database connection failed: ' . $e->getMessage(), 0, $e);
            }
        }

        return self::$connection;
    }
}
