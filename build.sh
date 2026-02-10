#!/bin/bash
# Render Build Script for Inventory Management System

set -e  # Exit on error

echo "=========================================="
echo "  Building Inventory Management System"
echo "=========================================="
echo ""

# 1. Install Composer dependencies
echo "→ Installing dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction
echo "✓ Dependencies installed"
echo ""

# 2. Create required directories
echo "→ Creating required directories..."
mkdir -p storage/logs
mkdir -p database
chmod -R 755 storage
chmod -R 755 database
echo "✓ Directories created"
echo ""

# 3. Run database migrations
echo "→ Running database migrations..."
if [ -f "database/migrate.php" ]; then
    php database/migrate.php
    echo "✓ Migrations completed"
else
    echo "⚠ Migration file not found, skipping..."
fi
echo ""

# 4. Seed initial data (optional, only on first deploy)
if [ "$SEED_DATABASE" = "true" ]; then
    echo "→ Seeding database..."
    if [ -f "database/seed.php" ]; then
        php database/seed.php
        echo "✓ Database seeded"
    fi
    echo ""
fi

echo "=========================================="
echo "  ✓ Build Complete!"
echo "=========================================="
