#!/usr/bin/env bash
set -euo pipefail

# Web Printer - Initial Server Deployment Script
# Usage:
#   sudo bash scripts/deploy-server.sh
# Optional env vars:
#   APP_DIR=/var/www/web-printer
#   REPO_URL=https://github.com/datakoarmada3-glitch/Web-Print.git
#   APP_URL=http://192.168.1.50
#   DB_NAME=web_printer
#   DB_USER=web_printer
#   DB_PASSWORD='StrongPassword123!'
#   PRINTER_IP=10.3.105.224
#   CUPS_PRINTER_NAME=canon_ir2625

APP_DIR="${APP_DIR:-/var/www/web-printer}"
REPO_URL="${REPO_URL:-https://github.com/datakoarmada3-glitch/Web-Print.git}"
APP_URL="${APP_URL:-http://localhost}"
DB_NAME="${DB_NAME:-web_printer}"
DB_USER="${DB_USER:-web_printer}"
DB_PASSWORD="${DB_PASSWORD:-ChangeMe123!}"
PRINTER_IP="${PRINTER_IP:-10.3.105.224}"
CUPS_PRINTER_NAME="${CUPS_PRINTER_NAME:-canon_ir2625}"
PHP_VERSION="${PHP_VERSION:-8.3}"

if [[ "$EUID" -ne 0 ]]; then
  echo "Run as root: sudo bash scripts/deploy-server.sh"
  exit 1
fi

export DEBIAN_FRONTEND=noninteractive

echo "[1/12] Install system packages"
apt update
apt install -y software-properties-common curl wget unzip git ca-certificates gnupg lsb-release \
  nginx mariadb-server mariadb-client redis-server cups cups-client cups-bsd supervisor \
  php${PHP_VERSION}-fpm php${PHP_VERSION}-cli php${PHP_VERSION}-common php${PHP_VERSION}-mysql \
  php${PHP_VERSION}-redis php${PHP_VERSION}-zip php${PHP_VERSION}-gd php${PHP_VERSION}-mbstring \
  php${PHP_VERSION}-curl php${PHP_VERSION}-xml php${PHP_VERSION}-bcmath php${PHP_VERSION}-intl \
  php${PHP_VERSION}-imagick php${PHP_VERSION}-readline \
  libreoffice-core libreoffice-writer libreoffice-calc libreoffice-impress \
  poppler-utils img2pdf imagemagick

systemctl enable --now nginx mariadb redis-server cups supervisor php${PHP_VERSION}-fpm

echo "[2/12] Install Composer if missing"
if ! command -v composer >/dev/null 2>&1; then
  cd /tmp
  curl -sS https://getcomposer.org/installer -o composer-setup.php
  php composer-setup.php
  mv composer.phar /usr/local/bin/composer
fi

echo "[3/12] Prepare database"
mysql -u root <<SQL
CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

echo "[4/12] Clone or refresh repository"
mkdir -p /var/www
if [[ -d "${APP_DIR}/.git" ]]; then
  git -C "${APP_DIR}" fetch origin
  git -C "${APP_DIR}" reset --hard origin/main
else
  rm -rf "${APP_DIR}"
  git clone "${REPO_URL}" "${APP_DIR}"
fi
chown -R www-data:www-data "${APP_DIR}"

echo "[5/12] Install PHP dependencies"
sudo -u www-data composer install --working-dir="${APP_DIR}" --no-dev --optimize-autoloader

echo "[6/12] Create Laravel runtime directories"
mkdir -p "${APP_DIR}/bootstrap/cache"
mkdir -p "${APP_DIR}/storage/framework/sessions"
mkdir -p "${APP_DIR}/storage/framework/views"
mkdir -p "${APP_DIR}/storage/framework/cache"
mkdir -p "${APP_DIR}/storage/logs"
mkdir -p "${APP_DIR}/storage/app/print-jobs"
chown -R www-data:www-data "${APP_DIR}/bootstrap" "${APP_DIR}/storage"
chmod -R 775 "${APP_DIR}/bootstrap" "${APP_DIR}/storage"

echo "[7/12] Create .env if missing"
if [[ ! -f "${APP_DIR}/.env" ]]; then
  cp "${APP_DIR}/.env.example" "${APP_DIR}/.env"
fi

python3 - <<PY
from pathlib import Path
path = Path("${APP_DIR}/.env")
text = path.read_text()
replacements = {
    'APP_ENV=local': 'APP_ENV=production',
    'APP_DEBUG=true': 'APP_DEBUG=false',
    'APP_URL=http://localhost': 'APP_URL=${APP_URL}',
    'DB_HOST=127.0.0.1': 'DB_HOST=127.0.0.1',
    'DB_PORT=3306': 'DB_PORT=3306',
    'DB_DATABASE=web_printer': 'DB_DATABASE=${DB_NAME}',
    'DB_USERNAME=web_printer': 'DB_USERNAME=${DB_USER}',
    'DB_PASSWORD=secret': 'DB_PASSWORD=${DB_PASSWORD}',
    'REDIS_HOST=127.0.0.1': 'REDIS_HOST=127.0.0.1',
    'QUEUE_CONNECTION=redis': 'QUEUE_CONNECTION=redis',
    'CACHE_STORE=redis': 'CACHE_STORE=redis',
    'SESSION_DRIVER=redis': 'SESSION_DRIVER=redis',
    'CUPS_PRINTER_NAME=canon_ir2625': 'CUPS_PRINTER_NAME=${CUPS_PRINTER_NAME}',
    'CUPS_PRINTER_URI=ipp://10.3.105.224/ipp/print': 'CUPS_PRINTER_URI=ipp://${PRINTER_IP}/ipp/print',
    'PRINTER_IP=10.3.105.224': 'PRINTER_IP=${PRINTER_IP}',
}
for old, new in replacements.items():
    text = text.replace(old, new)
path.write_text(text)
PY

if ! grep -q '^APP_KEY=base64:' "${APP_DIR}/.env"; then
  sudo -u www-data php "${APP_DIR}/artisan" key:generate
fi

echo "[8/12] Run migrations and cache"
sudo -u www-data php "${APP_DIR}/artisan" migrate --seed --force
sudo -u www-data php "${APP_DIR}/artisan" optimize:clear
sudo -u www-data php "${APP_DIR}/artisan" config:cache
sudo -u www-data php "${APP_DIR}/artisan" route:cache
sudo -u www-data php "${APP_DIR}/artisan" view:cache

echo "[9/12] Configure CUPS printer"
usermod -aG lpadmin www-data || true
if ! lpstat -p "${CUPS_PRINTER_NAME}" >/dev/null 2>&1; then
  lpadmin -p "${CUPS_PRINTER_NAME}" -E -v "ipp://${PRINTER_IP}/ipp/print" -m everywhere -D "Canon iR2625" -L "Office Printer" || true
fi
lpadmin -d "${CUPS_PRINTER_NAME}" || true
cupsaccept "${CUPS_PRINTER_NAME}" || true
cupsenable "${CUPS_PRINTER_NAME}" || true

echo "[10/12] Configure Nginx"
cat >/etc/nginx/sites-available/web-printer <<NGINX
server {
    listen 80;
    server_name _;
    root ${APP_DIR}/public;
    index index.php index.html;

    client_max_body_size 60M;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_read_timeout 300;
    }

    location ~ /\.ht {
        deny all;
    }
}
NGINX
ln -sf /etc/nginx/sites-available/web-printer /etc/nginx/sites-enabled/web-printer
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx

echo "[11/12] Configure Supervisor worker"
cat >/etc/supervisor/conf.d/web-printer-worker.conf <<SUPERVISOR
[program:web-printer-worker]
process_name=%(program_name)s_%(process_num)02d
command=php ${APP_DIR}/artisan queue:work redis --queue=prints --sleep=3 --tries=3 --timeout=180
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=${APP_DIR}/storage/logs/worker.log
stopwaitsecs=3600
SUPERVISOR
supervisorctl reread
supervisorctl update
supervisorctl restart web-printer-worker:* || supervisorctl start web-printer-worker:*

echo "[12/12] Configure scheduler"
CRON_LINE="* * * * * cd ${APP_DIR} && php artisan schedule:run >> /dev/null 2>&1"
( crontab -u www-data -l 2>/dev/null | grep -v 'schedule:run' ; echo "$CRON_LINE" ) | crontab -u www-data -

echo "Done. Open: ${APP_URL}"
echo "Login admin -> username: admin | password: admin123"
echo "Change passwords after first login."
