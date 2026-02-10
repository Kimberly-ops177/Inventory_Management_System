#!/bin/bash
# =====================================================
# Inventory Management System - Database Backup Script
# =====================================================
# This script creates a backup of the database
#
# Usage: ./backup.sh
# Cron: 0 2 * * * /path/to/inventory/backup.sh

set -e  # Exit on error

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Configuration
APP_DIR=$(pwd)
BACKUP_DIR="$APP_DIR/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
KEEP_DAYS=30  # Keep backups for 30 days

# Load environment variables
if [ -f "$APP_DIR/.env" ]; then
    export $(cat "$APP_DIR/.env" | grep -v '^#' | xargs)
else
    echo -e "${RED}✗ Error: .env file not found${NC}"
    exit 1
fi

echo -e "${YELLOW}╔═══════════════════════════════════════════╗${NC}"
echo -e "${YELLOW}║  Database Backup Utility                  ║${NC}"
echo -e "${YELLOW}╚═══════════════════════════════════════════╝${NC}\n"

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Backup filename
BACKUP_FILE="$BACKUP_DIR/backup_${DB_DATABASE}_${TIMESTAMP}.sql"
COMPRESSED_FILE="${BACKUP_FILE}.gz"

echo -e "${YELLOW}→ Starting database backup...${NC}"
echo -e "  Database: $DB_DATABASE"
echo -e "  Host: $DB_HOST"

# Create backup using mysqldump
if mysqldump \
    --host="$DB_HOST" \
    --port="$DB_PORT" \
    --user="$DB_USERNAME" \
    --password="$DB_PASSWORD" \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    "$DB_DATABASE" > "$BACKUP_FILE" 2>/dev/null; then

    echo -e "${GREEN}✓ Database dumped successfully${NC}"

    # Compress backup
    echo -e "${YELLOW}→ Compressing backup...${NC}"
    gzip "$BACKUP_FILE"

    # Get file size
    SIZE=$(du -h "$COMPRESSED_FILE" | cut -f1)
    echo -e "${GREEN}✓ Backup compressed: $SIZE${NC}"
    echo -e "  File: $COMPRESSED_FILE"

else
    echo -e "${RED}✗ Backup failed${NC}"
    rm -f "$BACKUP_FILE"
    exit 1
fi

# Clean old backups
echo -e "\n${YELLOW}→ Cleaning old backups (older than $KEEP_DAYS days)...${NC}"
DELETED=$(find "$BACKUP_DIR" -name "backup_*.sql.gz" -type f -mtime +$KEEP_DAYS -delete -print | wc -l)

if [ "$DELETED" -gt 0 ]; then
    echo -e "${GREEN}✓ Deleted $DELETED old backup(s)${NC}"
else
    echo -e "${GREEN}✓ No old backups to delete${NC}"
fi

# Count remaining backups
TOTAL=$(find "$BACKUP_DIR" -name "backup_*.sql.gz" -type f | wc -l)
echo -e "  Total backups: $TOTAL"

# Calculate total backup size
TOTAL_SIZE=$(du -sh "$BACKUP_DIR" | cut -f1)
echo -e "  Total size: $TOTAL_SIZE"

echo -e "\n${GREEN}╔═══════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  ✓ Backup Completed Successfully          ║${NC}"
echo -e "${GREEN}╚═══════════════════════════════════════════╝${NC}\n"

echo -e "Backup file: ${GREEN}$COMPRESSED_FILE${NC}"
echo -e "Timestamp: $TIMESTAMP"

# Restore instructions
echo -e "\n${YELLOW}To restore this backup:${NC}"
echo -e "  gunzip -c $COMPRESSED_FILE | mysql -u$DB_USERNAME -p$DB_PASSWORD $DB_DATABASE"
