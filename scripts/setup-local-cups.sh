#!/bin/bash
# Setup local Docker CUPS printer queue for Canon iR2625
# Run from project root: bash scripts/setup-local-cups.sh

set -e

PRINTER_NAME="canon_ir2625"
PRINTER_URI="ipp://10.3.105.224/ipp/print"
PRINTER_LOCATION="Ruang Kerja Utama"
PRINTER_DESC="Canon iR2625"

echo "Checking printer network..."
if ! ping -c 2 10.3.105.224 >/dev/null 2>&1; then
  echo "WARNING: Printer 10.3.105.224 tidak bisa di-ping dari host/container."
  echo "Lanjut setup CUPS queue, tapi test print mungkin gagal."
fi

echo "Adding printer queue to CUPS container..."
docker compose exec cups lpadmin -p "$PRINTER_NAME" -E \
  -v "$PRINTER_URI" \
  -m everywhere \
  -L "$PRINTER_LOCATION" \
  -D "$PRINTER_DESC"

docker compose exec cups lpadmin -d "$PRINTER_NAME"
docker compose exec cups cupsaccept "$PRINTER_NAME"
docker compose exec cups cupsenable "$PRINTER_NAME"

echo "Printer status:"
docker compose exec cups lpstat -v
docker compose exec cups lpstat -p "$PRINTER_NAME" -l

echo "Testing direct print..."
echo "Test print dari Web Printer Docker CUPS" | docker compose exec -T cups lp -d "$PRINTER_NAME"

echo "Done. CUPS Web UI: http://localhost:6631"
echo "Laravel app: http://localhost:8000"
