<?php
/**
 * Database Migration Runner
 *
 * Usage:
 *   php database/migrate.php          - Run pending migrations
 *   php database/migrate.php --reset  - Drop all tables and re-run (DANGEROUS!)
 */
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/bootstrap.php';

use App\Database;

// ANSI colors for better output
const C_GREEN = "\033[0;32m";
const C_RED = "\033[0;31m";
const C_YELLOW = "\033[1;33m";
const C_BLUE = "\033[0;34m";
const C_RESET = "\033[0m";

$resetMode = in_array('--reset', $argv, true);

echo C_BLUE . "╔═══════════════════════════════════════════╗\n";
echo "║  Database Migration Runner                ║\n";
echo "║  Inventory Management System              ║\n";
echo "╚═══════════════════════════════════════════╝" . C_RESET . "\n\n";

try {
    $db = Database::connection();

    // Detect database driver
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    $isPgsql = $driver === 'pgsql';

    echo "Database driver: " . C_BLUE . strtoupper($driver) . C_RESET . "\n\n";

    // Handle reset mode
    if ($resetMode) {
        echo C_YELLOW . "⚠ WARNING: Reset mode will DROP ALL TABLES!\n";
        echo "Type 'yes' to confirm: " . C_RESET;

        $handle = fopen("php://stdin", "r");
        $line = trim(fgets($handle));
        fclose($handle);

        if ($line !== 'yes') {
            echo C_BLUE . "Reset cancelled.\n" . C_RESET;
            exit(0);
        }

        echo "\n" . C_YELLOW . "Dropping all tables...\n" . C_RESET;

        if ($isPgsql) {
            // PostgreSQL: Get all tables from current schema
            $stmt = $db->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                $db->exec("DROP TABLE IF EXISTS \"$table\" CASCADE");
                echo "  Dropped: $table\n";
            }
        } else {
            // MySQL
            $db->exec('SET FOREIGN_KEY_CHECKS = 0');
            $stmt = $db->query('SHOW TABLES');
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                $db->exec("DROP TABLE IF EXISTS `$table`");
                echo "  Dropped: $table\n";
            }
            $db->exec('SET FOREIGN_KEY_CHECKS = 1');
        }

        echo C_GREEN . "✓ All tables dropped\n\n" . C_RESET;
    }

    // Create migrations tracking table
    echo "Checking migrations table...\n";

    if ($isPgsql) {
        $db->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id SERIAL PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    } else {
        $db->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    echo C_GREEN . "✓ Migrations table ready\n\n" . C_RESET;

    // Get migration files (filter by database type)
    $migrationDir = __DIR__ . '/migrations';
    $allMigrationFiles = glob($migrationDir . '/*.sql');

    if ($allMigrationFiles === false || empty($allMigrationFiles)) {
        echo C_YELLOW . "No migration files found in $migrationDir\n" . C_RESET;
        exit(0);
    }

    // Filter migration files based on database driver
    $migrationFiles = [];
    foreach ($allMigrationFiles as $file) {
        $basename = basename($file);

        // Skip driver-specific files for other drivers
        if ($isPgsql && strpos($basename, '_mysql.sql') !== false) {
            continue; // Skip MySQL-specific files
        }
        if (!$isPgsql && strpos($basename, '_pgsql.sql') !== false) {
            continue; // Skip PostgreSQL-specific files
        }

        // For PostgreSQL, prefer _pgsql.sql files over generic ones
        if ($isPgsql) {
            $genericVersion = str_replace('_pgsql.sql', '.sql', $basename);
            if ($basename !== $genericVersion && in_array($migrationDir . '/' . $genericVersion, $allMigrationFiles)) {
                // This is a pgsql-specific file, add it and skip the generic one later
                $migrationFiles[] = $file;
                // Mark the generic one to skip
                $skipGeneric[$genericVersion] = true;
            } elseif (!isset($skipGeneric[$basename])) {
                $migrationFiles[] = $file;
            }
        } else {
            $migrationFiles[] = $file;
        }
    }

    sort($migrationFiles);

    // Get executed migrations
    $stmt = $db->query("SELECT migration FROM migrations ORDER BY id");
    $executedMigrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $totalMigrations = count($migrationFiles);
    $executed = 0;
    $skipped = 0;

    echo "Found $totalMigrations migration file(s)\n\n";

    // Execute migrations
    foreach ($migrationFiles as $file) {
        $migrationName = basename($file);

        if (in_array($migrationName, $executedMigrations, true)) {
            echo C_YELLOW . "⊘ Skipping $migrationName (already executed)\n" . C_RESET;
            $skipped++;
            continue;
        }

        echo "→ Running migration: " . C_BLUE . $migrationName . C_RESET . "\n";

        try {
            $db->beginTransaction();

            // Read and execute SQL
            $sql = file_get_contents($file);
            if ($sql === false) {
                throw new Exception("Failed to read migration file");
            }

            $db->exec($sql);

            // Record migration
            $stmt = $db->prepare("INSERT INTO migrations (migration) VALUES (?)");
            $stmt->execute([$migrationName]);

            $db->commit();
            echo C_GREEN . "  ✓ Migration completed\n" . C_RESET;
            $executed++;

        } catch (Exception $e) {
            $db->rollBack();
            echo C_RED . "  ✗ Migration failed: " . $e->getMessage() . "\n" . C_RESET;
            exit(1);
        }
    }

    // Summary
    echo "\n" . C_BLUE . "══════ Migration Summary ══════\n";
    echo "Total migrations: $totalMigrations\n";
    echo "Executed: $executed\n";
    echo "Skipped: $skipped\n" . C_RESET;

    if ($executed > 0) {
        echo "\n" . C_GREEN . "✓ All migrations completed successfully!\n" . C_RESET;
    } else {
        echo "\n" . C_GREEN . "✓ Database is up to date!\n" . C_RESET;
    }

} catch (Exception $e) {
    echo "\n" . C_RED . "✗ Migration failed:\n";
    echo "  " . $e->getMessage() . "\n" . C_RESET;
    exit(1);
}
