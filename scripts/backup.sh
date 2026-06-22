#!/bin/bash
# Web Printer - Backup Script
# Jalankan: sudo /var/www/web-printer/scripts/backup.sh
# Rekomendasi: cron harian jam 03:00

set -e

# === KONFIGURASI ===
APP_DIR="/var/www/web-printer"
BACKUP_DIR="/var/backups/web-printer"
RETENTION_DAYS=14
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_NAME="web-printer-${DATE}"

# DB credentials (dari .env)
DB_HOST=$(grep ^DB_HOST ${APP_DIR}/.env | cut -d= -f2)
DB_DATABASE=$(grep ^DB_DATABASE ${APP_DIR}/.env | cut -d= -f2)
DB_USERNAME=$(grep ^DB_USERNAME ${APP_DIR}/.env | cut -d= -f2)
DB_PASSWORD=$(grep ^DB_PASSWORD ${APP_DIR}/.env | cut -d= -f2)

# === MULAI BACKUP ===
echo "[$(date)] Starting backup: ${BACKUP_NAME}"
mkdir -p "${BACKUP_DIR}/${BACKUP_NAME}"

# 1. Backup database
echo "[$(date)] Backing up database..."
mysqldump -h "${DB_HOST}" -u "${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" \
    --single-transaction --routines --triggers \
    > "${BACKUP_DIR}/${BACKUP_NAME}/database.sql"

# 2. Backup uploaded files
echo "[$(date)] Backing up uploaded files..."
if [ -d "${APP_DIR}/storage/app/print-jobs" ]; then
    tar -czf "${BACKUP_DIR}/${BACKUP_NAME}/print-files.tar.gz" \
        -C "${APP_DIR}/storage/app" print-jobs/
fi

# 3. Backup .env
echo "[$(date)] Backing up .env..."
cp "${APP_DIR}/.env" "${BACKUP_DIR}/${BACKUP_NAME}/env.bak"

# 4. Backup CUPS config
echo "[$(date)] Backing up CUPS config..."
tar -czf "${BACKUP_DIR}/${BACKUP_NAME}/cups-config.tar.gz" \
    -C /etc cups/

# 5. Compress all
echo "[$(date)] Compressing backup..."
tar -czf "${BACKUP_DIR}/${BACKUP_NAME}.tar.gz" \
    -C "${BACKUP_DIR}" "${BACKUP_NAME}/"
rm -rf "${BACKUP_DIR}/${BACKUP_NAME}"

# 6. Cleanup old backups
echo "[$(date)] Cleaning up backups older than ${RETENTION_DAYS} days..."
find "${BACKUP_DIR}" -name "web-printer-*.tar.gz" -mtime +${RETENTION_DAYS} -delete

echo "[$(date)] Backup completed: ${BACKUP_DIR}/${BACKUP_NAME}.tar.gz"
echo "[$(date)] Size: $(du -sh ${BACKUP_DIR}/${BACKUP_NAME}.tar.gz | cut -f1)"
