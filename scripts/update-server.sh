#!/usr/bin/env bash
set -euo pipefail

# Web Printer - Update existing server from GitHub
# Usage:
#   sudo bash scripts/update-server.sh
# Optional env vars:
#   APP_DIR=/var/www/web-printer

APP_DIR="${APP_DIR:-/var/www/web-printer}"

if [[ "$EUID" -ne 0 ]]; then
  echo "Run as root: sudo bash scripts/update-server.sh"
  exit 1
fi

if [[ ! -d "${APP_DIR}/.git" ]]; then
  echo "Project not found at ${APP_DIR}"
  exit 1
fi

echo "[1/7] Pull latest code"
sudo -u www-data git -C "${APP_DIR}" pull origin main

echo "[2/7] Install PHP dependencies"
sudo -u www-data composer install --working-dir="${APP_DIR}" --no-dev --optimize-autoloader

echo "[3/7] Ensure runtime directories"
mkdir -p "${APP_DIR}/bootstrap/cache"
mkdir -p "${APP_DIR}/storage/framework/sessions"
mkdir -p "${APP_DIR}/storage/framework/views"
mkdir -p "${APP_DIR}/storage/framework/cache"
mkdir -p "${APP_DIR}/storage/logs"
mkdir -p "${APP_DIR}/storage/app/print-jobs"
chown -R www-data:www-data "${APP_DIR}/bootstrap" "${APP_DIR}/storage"
chmod -R 775 "${APP_DIR}/bootstrap" "${APP_DIR}/storage"

echo "[4/7] Run migrations"
sudo -u www-data php "${APP_DIR}/artisan" migrate --force

echo "[5/7] Refresh caches"
sudo -u www-data php "${APP_DIR}/artisan" optimize:clear
sudo -u www-data php "${APP_DIR}/artisan" config:cache
sudo -u www-data php "${APP_DIR}/artisan" route:cache
sudo -u www-data php "${APP_DIR}/artisan" view:cache

echo "[6/7] Restart worker"
supervisorctl restart web-printer-worker:*

echo "[7/7] Reload nginx and php-fpm"
systemctl reload nginx
systemctl restart php8.3-fpm

echo "Update done."
