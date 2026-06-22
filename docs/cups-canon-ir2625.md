# Setup CUPS + Canon iR2625

## Informasi Printer

- Model: Canon iR2625 / iR2630
- IP: 10.3.105.224
- Koneksi utama: IPP
- Printer monochrome (hitam putih)
- Mendukung duplex
- Paper: A4, Legal, F4/Folio

---

## Metode Koneksi

### 1. IPP (Direkomendasikan)

```bash
sudo lpadmin -p canon_ir2625 -E \
    -v ipp://10.3.105.224/ipp/print \
    -m everywhere \
    -L "Ruang Kerja Utama" \
    -D "Canon iR2625"
```

IPP adalah protokol paling modern. Fitur `everywhere` otomatis mendeteksi kapabilitas printer via IPP.

### 2. Socket/AppSocket (Fallback)

```bash
sudo lpadmin -p canon_ir2625 -E \
    -v socket://10.3.105.224:9100 \
    -m everywhere \
    -L "Ruang Kerja Utama" \
    -D "Canon iR2625"
```

Gunakan jika IPP bermasalah. Port 9100 langsung kirim data raw ke printer.

### 3. LPD (Legacy)

```bash
sudo lpadmin -p canon_ir2625 -E \
    -v lpd://10.3.105.224/queue \
    -m everywhere \
    -L "Ruang Kerja Utama" \
    -D "Canon iR2625"
```

Hanya jika opsi lain gagal.

---

## Set Default Printer

```bash
sudo lpadmin -d canon_ir2625
```

## Verifikasi

```bash
# Cek printer terdaftar
lpstat -v

# Detail status
lpstat -p canon_ir2625 -l

# Lihat opsi yang tersedia
lpoptions -p canon_ir2625 -l
```

## Test Print

```bash
# Print teks sederhana
echo "Test Print Web Printer System" | lp -d canon_ir2625

# Print file PDF
lp -d canon_ir2625 -o media=A4 -o sides=one-sided /path/to/test.pdf

# Print duplex
lp -d canon_ir2625 -o sides=two-sided-long-edge /path/to/test.pdf
```

## Opsi Print yang Digunakan Aplikasi

| Parameter | CUPS Option | Nilai |
|-----------|-------------|-------|
| Paper A4 | `media=A4` | - |
| Paper Legal/F4 | `media=Legal` | - |
| Portrait | `orientation-requested=3` | - |
| Landscape | `orientation-requested=4` | - |
| Single side | `sides=one-sided` | - |
| Duplex long | `sides=two-sided-long-edge` | - |
| Duplex short | `sides=two-sided-short-edge` | - |
| Grayscale | `ColorModel=Gray` | - |
| Color | `ColorModel=RGB` | Mungkin tidak tersedia (mono) |
| Copies | `-n 3` | - |
| Page range | `-P 1-5` | - |

## Catatan Canon iR2625

1. **Monochrome**: Printer ini hitam putih. Opsi `ColorModel=RGB` diabaikan oleh printer, tapi tidak error.
2. **F4/Folio**: CUPS mungkin tidak punya media size `F4`. Gunakan `Legal` sebagai approximation terdekat.
3. **Driver `everywhere`**: Menggunakan IPP Everywhere (driverless). Jika duplex/paper size tidak terdeteksi, install driver Canon UFR II:
   ```bash
   # Download dari Canon website jika diperlukan
   # https://www.canon.co.id/support/
   ```
4. **Firewall**: Pastikan port 631 (IPP) atau 9100 (socket) terbuka dari VM ke printer.

## Troubleshooting

### Printer tidak terdeteksi
```bash
# Cek koneksi
ping 10.3.105.224

# Cek port IPP
nc -zv 10.3.105.224 631

# Cek port socket
nc -zv 10.3.105.224 9100
```

### Job stuck di queue
```bash
# Lihat job aktif
lpstat -W not-completed -o canon_ir2625

# Cancel semua job
cancel -a canon_ir2625

# Reset printer
sudo cupsdisable canon_ir2625
sudo cupsenable canon_ir2625
```

### Error "client-error-not-possible"
Printer mungkin sedang busy atau paper jam. Cek panel fisik printer.
