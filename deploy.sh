#!/bin/bash
# =====================================================
# Inventory Management System - Deployment Script
# =====================================================
# This script automates the deployment process
#
# Usage: ./deploy.sh [environment]
# Example: ./deploy.sh production

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
ENVIRONMENT=${1:-production}
APP_DIR=$(pwd)
BACKUP_DIR="$APP_DIR/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

echo -e "${BLUE}╔═══════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  Inventory System Deployment             ║${NC}"
echo -e "${BLUE}║  Environment: ${ENVIRONMENT}                    ║${NC}"
echo -e "${BLUE}╚═══════════════════════════════════════════╝${NC}\n"

# Pre-deployment checks
echo -e "${YELLOW}→ Running pre-deployment checks...${NC}"

# Check if .env exists
if [ ! -f "$APP_DIR/.env" ]; then
    echo -e "${RED}✗ Error: .env file not found${NC}"
    echo "  Copy .env.example to .env and configure it first"
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r 'echo PHP_VERSION;')
echo -e "${GREEN}✓ PHP version: $PHP_VERSION${NC}"

# Check required PHP extensions
REQUIRED_EXTENSIONS=("pdo" "pdo_mysql" "mbstring" "json")
for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -m | grep -q "^$ext$"; then
        echo -e "${GREEN}✓ Extension $ext: loaded${NC}"
    else
        echo -e "${RED}✗ Extension $ext: missing${NC}"
        exit 1
    fi
done

# Check database connectivity
echo -e "\n${YELLOW}→ Testing database connection...${NC}"
if php -r "
require 'vendor/autoload.php';
require 'src/bootstrap.php';
try {
    \$db = App\Database::connection();
    \$db->query('SELECT 1');
    exit(0);
} catch (Exception \$e) {
    echo \$e->getMessage();
    exit(1);
}
"; then
    echo -e "${GREEN}✓ Database connection successful${NC}"
else
    echo -e "${RED}✗ Database connection failed${NC}"
    exit 1
fi

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Backup database
if [ "$ENVIRONMENT" = "production" ]; then
    echo -e "\n${YELLOW}→ Creating database backup...${NC}"
    ./backup.sh
    echo -e "${GREEN}✓ Database backup created${NC}"
fi

# Install/Update dependencies
echo -e "\n${YELLOW}→ Installing dependencies...${NC}"
if [ "$ENVIRONMENT" = "production" ]; then
    composer install --no-dev --optimize-autoloader
else
    composer install
fi
echo -e "${GREEN}✓ Dependencies installed${NC}"

# Run database migrations
echo -e "\n${YELLOW}→ Running database migrations...${NC}"
php database/migrate.php
echo -e "${GREEN}✓ Migrations completed${NC}"

# Clear cache (if implemented)
echo -e "\n${YELLOW}→ Clearing cache...${NC}"
rm -rf storage/cache/* 2>/dev/null || true
echo -e "${GREEN}✓ Cache cleared${NC}"

# Set permissions
echo -e "\n${YELLOW}→ Setting file permissions...${NC}"
chmod -R 755 storage
chmod -R 755 database
chmod 644 .env
echo -e "${GREEN}✓ Permissions set${NC}"

# Restart services (if using PHP-FPM)
if [ "$ENVIRONMENT" = "production" ]; then
    echo -e "\n${YELLOW}→ Restarting PHP-FPM...${NC}"
    if command -v systemctl &> /dev/null; then
        sudo systemctl reload php-fpm || sudo systemctl reload php8.2-fpm || true
    fi
    echo -e "${GREEN}✓ Services restarted${NC}"
fi

# Health check
echo -e "\n${YELLOW}→ Running health check...${NC}"
HEALTH_RESPONSE=$(php -r "
require 'vendor/autoload.php';
require 'src/bootstrap.php';
\$controller = new App\Controllers\HealthController();
\$response = \$controller->check();
echo \$response->getStatusCode();
")

if [ "$HEALTH_RESPONSE" = "200" ]; then
    echo -e "${GREEN}✓ Health check passed${NC}"
else
    echo -e "${RED}✗ Health check failed (Status: $HEALTH_RESPONSE)${NC}"
    exit 1
fi

# Deployment summary
echo -e "\n${GREEN}╔═══════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  ✓ Deployment Completed Successfully      ║${NC}"
echo -e "${GREEN}╚═══════════════════════════════════════════╝${NC}\n"

echo -e "Environment: ${BLUE}$ENVIRONMENT${NC}"
echo -e "Timestamp: ${BLUE}$TIMESTAMP${NC}"
echo -e "PHP Version: ${BLUE}$PHP_VERSION${NC}"

if [ "$ENVIRONMENT" = "production" ]; then
    echo -e "\n${YELLOW}⚠ Remember to:${NC}"
    echo "  1. Clear browser cache"
    echo "  2. Notify users if downtime occurred"
    echo "  3. Monitor error logs: storage/logs/error.log"
fi

echo -e "\n${GREEN}System is ready!${NC}"
