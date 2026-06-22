# Web Printer - Sistem Print Terpusat Kantor

Aplikasi web internal untuk upload dokumen dan mengirim print job ke printer kantor melalui CUPS.

## Fitur

- **User**: Upload dokumen (PDF, Word, Excel, PowerPoint, JPG/PNG), pilih opsi print, lihat status job
- **Admin**: Dashboard statistik, kelola antrean, histori print, manajemen user & printer
- **Print**: Otomatis konversi ke PDF, kirim ke CUPS, tracking status real-time
- **Keamanan**: Login wajib, role-based access, validasi file ketat, CSRF protection

## Tech Stack

| Komponen | Teknologi |
|----------|-----------|
| OS | Ubuntu Server 24.04 LTS |
| Web Server | Nginx |
| Backend | Laravel 11+ (PHP 8.3) |
| Database | MariaDB |
| Cache/Queue | Redis |
| Print Server | CUPS |
| Konversi | LibreOffice headless, img2pdf |
| UI | Tabler (Bootstrap 5) + Chart.js |
| Worker | Supervisor |

## Printer

- Canon iR2625 @ `10.3.105.224`
- Koneksi: `ipp://10.3.105.224/ipp/print`

## Quick Start (Development)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
# Di terminal lain:
php artisan queue:work --queue=prints
```

## Deployment

Lihat: [docs/deployment-ubuntu-24.04.md](docs/deployment-ubuntu-24.04.md)

## Dokumentasi

- [Deployment Guide](docs/deployment-ubuntu-24.04.md)
- [CUPS + Canon iR2625 Setup](docs/cups-canon-ir2625.md)
- [Testing Guide](docs/testing-print.md)

## Login Default

| Role  | Email                  | Password |
|-------|------------------------|----------|
| Admin | admin@webprinter.local | admin123 |
| User  | user@webprinter.local  | user123  |

**⚠️ Ganti password setelah login pertama!**

## Format File Didukung

- PDF
- DOC, DOCX (Microsoft Word)
- XLS, XLSX (Microsoft Excel)
- PPT, PPTX (Microsoft PowerPoint)
- JPG, JPEG, PNG (Gambar)

## Opsi Print

- Jumlah copy (1-99)
- Ukuran kertas: A4, Legal, F4/Folio
- Orientasi: Portrait, Landscape
- Duplex: Satu sisi, Dua sisi (tepi panjang/pendek)
- Mode warna: Hitam putih, Berwarna
- Range halaman (opsional)

## Backup

```bash
sudo /var/www/web-printer/scripts/backup.sh
```

## License

Proprietary - Internal use only.
