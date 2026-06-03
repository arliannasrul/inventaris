# Arlian6A1 Inventory

Web app sistem inventaris barang berbasis Laravel, Prisma, Neon PostgreSQL, Docker, dan Traefik.

## Arsitektur

- `app`: Laravel web app untuk UI, autentikasi sederhana, dashboard, laporan, notifikasi, dan komunikasi internal.
- `prisma-api`: Node.js API service yang menjadi satu-satunya service yang berkomunikasi langsung ke database Neon melalui Prisma.
- `traefik`: reverse proxy untuk routing container.
- `neon`: PostgreSQL managed database dari Neon.tech.

## Menjalankan

1. Salin `.env.example` ke `.env`.
2. Isi `DATABASE_URL` dengan Neon connection string.
3. Jalankan:

```bash
docker compose up --build
```

4. Jalankan migrasi Prisma:

```bash
docker compose exec prisma-api npx prisma migrate deploy
```

5. Buka:

- Laravel app: `http://inventaris.localhost`
- Prisma API health: `http://api.inventaris.localhost/health`
- Traefik dashboard: `http://traefik.localhost`

## Fitur Utama

- Pencatatan barang masuk, keluar, rusak, dan penyesuaian stok.
- Master data kategori, lokasi, pemasok, dan pengguna.
- Dashboard stok, nilai inventaris, aktivitas terbaru, dan barang stok rendah.
- Cetak laporan inventaris dan pergerakan stok.
- Notifikasi stok rendah, barang habis, dan aktivitas penting.
- Komunikasi internal per barang/laporan melalui thread komentar.
- Audit log untuk perubahan data.

