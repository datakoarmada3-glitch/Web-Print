# Web Printer - Deployment Guide (Ubuntu Server 24.04 LTS)

Panduan deploy Web Print Management System di 1 VM Proxmox.

## Spesifikasi Minimum VM

- OS: Ubuntu Server 24.04 LTS
- CPU: 2 vCPU
- RAM: 2 GB (4 GB direkomendasikan)
- Disk: 40 GB
- Network: Bridge ke LAN kantor (akses ke printer 10.3.105.224)

---

## 1. Update Sistem

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y software-properties-common curl wget unzip git
```

## 2. Install Nginx

```bash
sudo apt install -y nginx
sudo systemctl enable nginx
```

## 3. Install PHP 8.3

```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-common \
    php8.3-mysql php8.3-pgsql php8.3-zip php8.3-gd \
    php8.3-mbstring php8.3-curl php8.3-xml php8.3-bcmath \
    php8.3-intl php8.3-readline php8.3-redis php8.3-imagick

sudo systemctl enable php8.3-fpm
```

## 4. Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

## 5. Install MariaDB

```bash
sudo apt install -y mariadb-server
sudo systemctl enable mariadb
sudo mysql_secure_installation
```

Buat database dan user:

```bash
sudo mysql -u root -p <<EOF
CREATE DATABASE web_printer CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'web_printer'@'localhost' IDENTIFIED BY 'GANTI_PASSWORD_KUAT';
GRANT ALL PRIVILEGES ON web_printer.* TO 'web_printer'@'localhost';
FLUSH PRIVILEGES;
EOF
```

## 6. Install Redis

```bash
sudo apt install -y redis-server
sudo systemctl enable redis-server
```

## 7. Install CUPS

```bash
sudo apt install -y cups cups-client
sudo systemctl enable cups
```

Konfigurasi CUPS agar bisa dikelola dari web:

```bash
sudo usermod -a -G lpadmin www-data
sudo cupsctl --remote-admin --share-printers
sudo systemctl restart cups
```

### Tambahkan Printer Canon iR2625

```bash
# Opsi 1: IPP (REKOMENDASI)
sudo lpadmin -p canon_ir2625 -E \
    -v ipp://10.3.105.224/ipp/print \
    -m everywhere \
    -L "Ruang Kerja Utama" \
    -D "Canon iR2625"

# Opsi 2: Socket (fallback jika IPP bermasalah)
# sudo lpadmin -p canon_ir2625 -E \
#     -v socket://10.3.105.224:9100 \
#     -m everywhere \
#     -L "Ruang Kerja Utama" \
#     -D "Canon iR2625"

# Set sebagai default
sudo lpadmin -d canon_ir2625
```

Verifikasi:

```bash
lpstat -v
lpstat -p canon_ir2625 -l
lpoptions -p canon_ir2625 -l
```

Test print:

```bash
echo "Test Print dari Web Printer" | lp -d canon_ir2625
# Atau dengan file PDF:
# lp -d canon_ir2625 /path/to/test.pdf
```

## 8. Install LibreOffice Headless

```bash
sudo apt install -y libreoffice-core libreoffice-writer libreoffice-calc libreoffice-impress --no-install-recommends
```

## 9. Install Utility Konversi

```bash
sudo apt install -y poppler-utils img2pdf imagemagick
```

Edit policy ImageMagick agar bisa konversi PDF:

```bash
sudo sed -i 's/rights="none" pattern="PDF"/rights="read|write" pattern="PDF"/' /etc/ImageMagick-6/policy.xml
```

## 10. Clone & Setup Project Laravel

```bash
cd /var/www
sudo git clone <URL_REPO> web-printer
sudo chown -R www-data:www-data web-printer
cd web-printer

sudo -u www-data composer install --optimize-autoloader --no-dev
sudo -u www-data cp .env.example .env
```

Edit `.env`:

```bash
sudo -u www-data nano .env
```

Isi yang perlu diubah:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://printer.kantor.local

DB_HOST=127.0.0.1
DB_DATABASE=web_printer
DB_USERNAME=web_printer
DB_PASSWORD=GANTI_PASSWORD_KUAT

QUEUE_CONNECTION=redis
```

Jalankan setup:

```bash
sudo -u www-data php artisan key:generate
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan db:seed --force
sudo -u www-data php artisan storage:link
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

Set permissions:

```bash
sudo chown -R www-data:www-data /var/www/web-printer/storage
sudo chown -R www-data:www-data /var/www/web-printer/bootstrap/cache
sudo chmod -R 775 /var/www/web-printer/storage
sudo chmod -R 775 /var/www/web-printer/bootstrap/cache
```

## 11. Nginx Virtual Host

```bash
sudo nano /etc/nginx/sites-available/web-printer
```

Isi:

```nginx
server {
    listen 80;
    server_name printer.kantor.local _;
    root /var/www/web-printer/public;
    index index.php;

    client_max_body_size 60M;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Aktifkan:

```bash
sudo ln -s /etc/nginx/sites-available/web-printer /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl reload nginx
```

## 12. Queue Worker (Supervisor)

```bash
sudo apt install -y supervisor
```

```bash
sudo nano /etc/supervisor/conf.d/web-printer-worker.conf
```

Isi:

```ini
[program:web-printer-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/web-printer/artisan queue:work redis --queue=prints --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/web-printer/storage/logs/worker.log
stopwaitsecs=3600
```

Mulai:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start web-printer-worker:*
```

## 13. Laravel Scheduler (Cron)

```bash
sudo crontab -u www-data -e
```

Tambahkan:

```cron
* * * * * cd /var/www/web-printer && php artisan schedule:run >> /dev/null 2>&1
```

## 14. PHP-FPM Tuning

```bash
sudo nano /etc/php/8.3/fpm/pool.d/www.conf
```

Sesuaikan:

```ini
upload_max_filesize = 60M
post_max_size = 60M
max_execution_time = 300
memory_limit = 256M
```

```bash
sudo systemctl restart php8.3-fpm
```

## 15. Firewall

```bash
sudo ufw allow 80/tcp
sudo ufw allow 22/tcp
sudo ufw enable
```

---

## Backup

Gunakan script `scripts/backup.sh`:

```bash
sudo chmod +x /var/www/web-printer/scripts/backup.sh
# Jalankan manual:
sudo /var/www/web-printer/scripts/backup.sh
# Atau cron harian jam 3 pagi:
echo "0 3 * * * root /var/www/web-printer/scripts/backup.sh" | sudo tee /etc/cron.d/web-printer-backup
```

---

## Login Awal

| Role  | Email                  | Password |
|-------|------------------------|----------|
| Admin | admin@webprinter.local | admin123 |
| User  | user@webprinter.local  | user123  |

**⚠️ GANTI PASSWORD SEGERA SETELAH LOGIN PERTAMA.**

---

## Troubleshooting

### CUPS tidak bisa print
```bash
lpstat -p canon_ir2625 -l
# Cek apakah printer enabled dan accepting
sudo cupsenable canon_ir2625
sudo cupsaccept canon_ir2625
```

### Queue tidak jalan
```bash
sudo supervisorctl status web-printer-worker:*
# Restart jika perlu:
sudo supervisorctl restart web-printer-worker:*
```

### LibreOffice gagal konversi
```bash
# Test manual:
sudo -u www-data libreoffice --headless --convert-to pdf --outdir /tmp /path/to/file.docx
```

### Permission error
```bash
sudo chown -R www-data:www-data /var/www/web-printer/storage
sudo chmod -R 775 /var/www/web-printer/storage
```
