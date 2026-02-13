#!/bin/bash

# Start PHP server in background immediately (so Render detects port)
echo "Starting PHP server on port ${PORT:-8000}..."
php -S 0.0.0.0:${PORT:-8000} -t public 2>&1 &
SERVER_PID=$!

# Give server a moment to bind to port
sleep 3
echo "✓ Server started with PID $SERVER_PID"

# Wait for database in background (don't block server)
(
  echo "Waiting for database to be ready..."
  for i in {1..30}; do
      if php -r "
          try {
              \$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s',
                  getenv('DB_HOST'),
                  getenv('DB_PORT') ?: '3306',
                  getenv('DB_DATABASE')
              );
              \$pdo = new PDO(\$dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
              exit(0);
          } catch (Exception \$e) {
              exit(1);
          }
      " 2>/dev/null; then
          echo "✓ Database is ready!"
          break
      fi
      echo "Waiting for database... ($i/30)"
      sleep 2
  done

  echo "Running database migrations..."
  php database/migrate.php || echo "Warning: Migrations failed or already applied"
  echo "✓ Migrations complete"
) &

# Wait for server process
wait $SERVER_PID
