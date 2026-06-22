# Deploy Web Printer dari GitHub ke VM Ubuntu

Panduan ini untuk workflow rapi: develop di PC → `git push` ke GitHub → server VM tinggal `git pull`.

Repository:

```text
https://github.com/datakoarmada3-glitch/Web-Print.git
```

---

## 1. Persiapan VM

Gunakan Ubuntu Server 24.04 LTS.

Rekomendasi VM:

- CPU: 2 vCPU
- RAM: 4 GB
- Disk: 40 GB
- Network: satu LAN dengan printer `10.3.105.224`

Update server:

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y git curl unzip wget ca-certificates
```

---

## 2. Install stack server

```bash
sudo apt install -y nginx mariadb-server redis-server cups cups-client cups-bsd supervisor \
php8.3-fpm php8.3-cli php8.3-common php8.3-mysql php8.3-redis php8.3-zip \
php8.3-gd php8.3-mbstring php8.3-curl php8.3-xml php8.3-bcmath php8.3-intl \
php8.3-imagick php8.3-readline \
libreoffice-core libreoffice-writer libreoffice-calc libreoffice-impress \
poppler-utils img2pdf imagemagick
```

Enable services:

```bash
sudo systemctl enable --now nginx php8.3-fpm mariadb redis-server cups supervisor
```

---

## 3. Install Composer

```bash
cd /tmp
curl -sS https://getcomposer.org/installer -o composer-setup.php
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

---

## 4. Setup database

```bash
sudo mysql -u root -p
```

Jalankan SQL:

```sql
CREATE DATABASE web_printer CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'web_printer'@'localhost' IDENTIFIED BY 'GantiPasswordKuat123!';
GRANT ALL PRIVILEGES ON web_printer.* TO 'web_printer'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 5. Setup CUPS printer Canon

Cek printer bisa dijangkau:

```bash
ping 10.3.105.224
```

Tambah printer via IPP:

```bash
sudo lpadmin -p canon_ir2625 -E \
  -v ipp://10.3.105.224/ipp/print \
  -m everywhere \
  -D "Canon iR2625" \
  -L "Office Printer"

sudo lpadmin -d canon_ir2625
sudo cupsaccept canon_ir2625
sudo cupsenable canon_ir2625
```

Test print langsung:

```bash
echo "Test print dari Ubuntu VM" | lp -d canon_ir2625
```

Kalau gagal, coba socket:

```bash
sudo lpadmin -x canon_ir2625
sudo lpadmin -p canon_ir2625 -E \
  -v socket://10.3.105.224:9100 \
  -m everywhere \
  -D "Canon iR2625" \
  -L "Office Printer"

sudo lpadmin -d canon_ir2625
echo "Test print socket" | lp -d canon_ir2625
```

---

## 6. Clone project dari GitHub

```bash
cd /var/www
sudo git clone https://github.com/datakoarmada3-glitch/Web-Print.git web-printer
sudo chown -R www-data:www-data /var/www/web-printer
cd /var/www/web-printer
```

Kalau repository private, pakai Personal Access Token saat diminta password GitHub.

---

## 7. Install dependency Laravel

```bash
cd /var/www/web-printer
sudo -u www-data composer install --no-dev --optimize-autoloader
```

---

## 8. Setup `.env`

```bash
sudo -u www-data cp .env.example .env
sudo nano .env
```

Isi contoh:

```env
APP_NAME="Web Printer"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://IP_VM

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=web_printer
DB_USERNAME=web_printer
DB_PASSWORD=GantiPasswordKuat123!

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

CUPS_PRINTER_NAME=canon_ir2625
CUPS_PRINTER_URI=ipp://10.3.105.224/ipp/print
PRINTER_IP=10.3.105.224

UPLOAD_MAX_SIZE_MB=50
FILE_RETENTION_DAYS=30

LIBREOFFICE_BIN=/usr/bin/libreoffice
PDFINFO_BIN=/usr/bin/pdfinfo
IMG2PDF_BIN=/usr/bin/img2pdf
```

Ganti:

- `APP_URL=http://IP_VM`
- `DB_PASSWORD=...`

---

## 9. Setup folder permission

```bash
cd /var/www/web-printer
sudo mkdir -p bootstrap/cache
sudo mkdir -p storage/framework/sessions
sudo mkdir -p storage/framework/views
sudo mkdir -p storage/framework/cache
sudo mkdir -p storage/logs
sudo mkdir -p storage/app/print-jobs
sudo chown -R www-data:www-data bootstrap storage
sudo chmod -R 775 bootstrap storage
```

---

## 10. Generate key, migrate, seed

```bash
cd /var/www/web-printer
sudo -u www-data php artisan key:generate
sudo -u www-data php artisan migrate:fresh --seed --force
sudo -u www-data php artisan package:discover --ansi
```

Login default:

```text
username: admin
password: admin123
```

User demo:

```text
username: user
password: user123
```

Ganti password setelah login pertama.

---

## 11. Cache production

```bash
cd /var/www/web-printer
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

---

## 12. Setup Nginx

```bash
sudo nano /etc/nginx/sites-available/web-printer
```

Isi:

```nginx
server {
    listen 80;
    server_name _;
    root /var/www/web-printer/public;
    index index.php index.html;

    client_max_body_size 60M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_read_timeout 300;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Aktifkan:

```bash
sudo ln -s /etc/nginx/sites-available/web-printer /etc/nginx/sites-enabled/web-printer
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl reload nginx
```

---

## 13. Naikkan limit upload PHP

```bash
sudo nano /etc/php/8.3/fpm/php.ini
```

Ubah:

```ini
upload_max_filesize = 60M
post_max_size = 60M
memory_limit = 512M
max_execution_time = 300
```

Restart:

```bash
sudo systemctl restart php8.3-fpm
```

---

## 14. Setup Supervisor worker

```bash
sudo nano /etc/supervisor/conf.d/web-printer-worker.conf
```

Isi:

```ini
[program:web-printer-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/web-printer/artisan queue:work redis --queue=prints --sleep=3 --tries=3 --timeout=180
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

Aktifkan:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start web-printer-worker:*
sudo supervisorctl status
```

---

## 15. Setup Laravel scheduler

```bash
sudo crontab -u www-data -e
```

Tambahkan:

```cron
* * * * * cd /var/www/web-printer && php artisan schedule:run >> /dev/null 2>&1
```

---

## 16. Buka aplikasi

Buka browser:

```text
http://IP_VM
```

Login:

```text
username: admin
password: admin123
```

---

## 17. Test print dari web

1. Login sebagai `admin`
2. Buka **Print Dokumen**
3. Upload PDF kecil
4. Pilih opsi print
5. Klik **Kirim ke Antrean Print**
6. Cek printer fisik

Cek worker log:

```bash
tail -f /var/www/web-printer/storage/logs/worker.log
```

Cek CUPS job:

```bash
lpstat -W not-completed -o canon_ir2625
lpstat -W completed -o canon_ir2625
```

---

## 18. Workflow update ke server nanti

Kalau ada perubahan dari PC, push:

```bash
git add .
git commit -m "fix: update fitur"
git push origin main
```

Di server VM, pull update:

```bash
cd /var/www/web-printer
sudo -u www-data git pull origin main
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo supervisorctl restart web-printer-worker:*
sudo systemctl reload nginx
```

Kalau hanya ubah view Blade:

```bash
cd /var/www/web-printer
sudo -u www-data git pull origin main
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan view:cache
```

---

## 19. Troubleshooting cepat

### Web 500 error

```bash
tail -f /var/www/web-printer/storage/logs/laravel.log
```

### Worker tidak jalan

```bash
sudo supervisorctl status
sudo supervisorctl restart web-printer-worker:*
```

### Printer tidak jalan

```bash
lpstat -r
lpstat -v
lpstat -p canon_ir2625 -l
```

### Upload file besar gagal

```bash
grep -n "upload_max_filesize\|post_max_size" /etc/php/8.3/fpm/php.ini
sudo systemctl restart php8.3-fpm
sudo systemctl reload nginx
```

### CUPS queue macet

```bash
cancel -a canon_ir2625
sudo cupsenable canon_ir2625
sudo cupsaccept canon_ir2625
```

---

## 20. Command update paling sering dipakai

```bash
cd /var/www/web-printer
sudo -u www-data git pull origin main
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo supervisorctl restart web-printer-worker:*
```
