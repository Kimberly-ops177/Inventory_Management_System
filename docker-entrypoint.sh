#!/bin/bash
set -e

echo "Running database migrations..."
php database/migrate.php || echo "Warning: Migrations failed or already applied"

echo "Starting PHP server on port ${PORT:-8000}..."
exec php -S 0.0.0.0:${PORT:-8000} -t public
