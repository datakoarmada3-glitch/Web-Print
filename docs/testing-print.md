# Testing Print - Panduan Pengujian

## Pre-requisites

1. VM sudah deploy sesuai `docs/deployment-ubuntu-24.04.md`
2. CUPS sudah setup sesuai `docs/cups-canon-ir2625.md`
3. Printer Canon iR2625 online dan bisa di-ping dari VM
4. Laravel queue worker berjalan (`supervisorctl status`)

---

## Test 1: Koneksi Printer

```bash
# Ping printer
ping -c 3 10.3.105.224

# Cek CUPS menerima printer
lpstat -p canon_ir2625 -l

# Test print langsung dari command line
echo "Test direct CUPS print" | lp -d canon_ir2625
```

✅ Expected: Printer mencetak teks "Test direct CUPS print"

---

## Test 2: Login Aplikasi

1. Buka browser: `http://<IP_VM>`
2. Login dengan `admin@webprinter.local` / `admin123`

✅ Expected: Redirect ke dashboard, tampilan Tabler UI tampil

---

## Test 3: Upload & Print PDF

1. Login sebagai user biasa (`user@webprinter.local` / `user123`)
2. Klik "Print Dokumen"
3. Upload file PDF (contoh: dokumen 2 halaman)
4. Pilih: A4, Portrait, 1 copy, Satu Sisi, Hitam Putih
5. Klik "Kirim ke Antrean Print"

✅ Expected:
- File terupload
- Job muncul di daftar dengan status Waiting → Processing → Printing → Completed
- Printer mencetak dokumen
- Detail job menampilkan page count dan CUPS job ID

---

## Test 4: Upload & Print DOCX

1. Upload file .docx
2. Pilih opsi print apapun
3. Submit

✅ Expected:
- LibreOffice mengkonversi ke PDF
- `converted_pdf_path` terisi di database
- Printer mencetak hasil konversi (layout sama dengan file asli)

Jika gagal, cek:
```bash
sudo -u www-data libreoffice --headless --convert-to pdf --outdir /tmp /path/to/test.docx
```

---

## Test 5: Upload & Print Gambar (JPG/PNG)

1. Upload file .jpg atau .png
2. Pilih A4, Portrait

✅ Expected:
- Gambar dikonversi ke PDF (img2pdf atau ImageMagick)
- Printer mencetak gambar full page

---

## Test 6: Upload Excel/PowerPoint

1. Upload .xlsx atau .pptx
2. Submit

✅ Expected: Konversi ke PDF berhasil, print normal

---

## Test 7: Opsi Print

### Duplex
1. Upload PDF multi-halaman
2. Pilih "Dua Sisi - Tepi Panjang"

✅ Expected: Printer cetak bolak-balik

### Multiple copies
1. Upload PDF, pilih 3 copy

✅ Expected: Keluar 3 set dokumen

### Landscape
1. Upload PDF, pilih Landscape

✅ Expected: Output landscape

### Page range
1. Upload PDF 10 halaman
2. Isi range: `1-3`

✅ Expected: Hanya halaman 1-3 yang dicetak

---

## Test 8: Cancel Job

1. Upload file besar atau saat printer sedang busy
2. Klik "Batalkan" sebelum status = Printing

✅ Expected: Status berubah ke "Dibatalkan", printer tidak mencetak

---

## Test 9: Admin Queue

1. Login sebagai admin
2. Buka Antrean Print
3. Cek pause/resume
4. Cek cancel dari admin
5. Upload job gagal (misal printer offline), lalu retry

✅ Expected: Semua aksi admin berfungsi

---

## Test 10: Statistik

1. Lakukan beberapa print job
2. Buka Dashboard Admin

✅ Expected:
- Counter hari ini dan bulan ini update
- Chart.js grafik menampilkan data
- Top user muncul

---

## Test 11: Cleanup

```bash
# Ubah retensi ke 0 hari di settings admin (untuk test)
# Atau jalankan manual:
sudo -u www-data php artisan print:cleanup-files
```

✅ Expected: File di storage terhapus, record database tetap ada

---

## Test 12: Keamanan

1. Akses halaman admin tanpa login → redirect login
2. Login sebagai user biasa → halaman admin return 403
3. Upload file .exe atau .php → ditolak
4. Upload file > limit → ditolak
5. User A tidak bisa lihat job User B

---

## Monitoring Commands

```bash
# Status worker
sudo supervisorctl status web-printer-worker:*

# Tail worker log
tail -f /var/www/web-printer/storage/logs/worker.log

# CUPS status
lpstat -W not-completed -o canon_ir2625
lpstat -W completed -o canon_ir2625

# Laravel log
tail -f /var/www/web-printer/storage/logs/laravel.log

# Sync printer status manual
sudo -u www-data php artisan print:sync-printer-status

# Monitor job status manual
sudo -u www-data php artisan print:monitor-status
```
