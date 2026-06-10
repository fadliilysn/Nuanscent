# Nuanscent — Web Rekomendasi Parfum Lokal

Nuanscent adalah aplikasi web katalog dan rekomendasi parfum lokal. Pengguna dapat menjelajahi parfum berdasarkan brand, kategori aroma, notes, occasion, harga, dan preferensi pribadi.

Proyek ini dibuat sebagai sarana belajar dan portfolio pengembangan aplikasi full-stack.

## Screenshot

> Screenshot akan ditambahkan pada folder `docs/screenshots/`.

Path yang disiapkan:

- [docs/screenshots/homepage.png](docs/screenshots/homepage.png)
- [docs/screenshots/catalog.png](docs/screenshots/catalog.png)
- [docs/screenshots/quiz.png](docs/screenshots/quiz.png)
- [docs/screenshots/detailparfume.png](docs/screenshots/detailparfume.png)
- [docs/screenshots/listbrands.png](docs/screenshots/listbrands.png)
- [docs/screenshots/panduan.png](docs/screenshots/panduan.png)
- [docs/screenshots/comparison.png](docs/screenshots/comparison.png)
- [docs/screenshots/recomendation.png](docs/screenshots/recomendation.png)

## Fitur

- Katalog parfum lokal
- Pencarian dan filter katalog
- Halaman detail parfum
- Quiz rekomendasi parfum
- Alasan rekomendasi **Kenapa cocok?**
- Perbandingan parfum
- Halaman brand
- Halaman panduan
- Admin panel Filament

## Tech Stack

**Frontend**

- React
- TypeScript
- Vite

**Backend**

- Laravel 12
- PHP 8.3
- PostgreSQL
- Filament

**Database**

- PostgreSQL
- Siap menggunakan Supabase

## Status Deployment

- Frontend dapat di-deploy ke Vercel sebagai preview UI.
- Database Supabase telah disiapkan untuk deployment berikutnya.
- Backend Laravel belum tersedia secara online dan saat ini dijalankan secara lokal dengan `php artisan serve`.
- Katalog, quiz, API, dan admin membutuhkan backend Laravel yang aktif, baik secara lokal maupun melalui hosting publik.
- Setelah backend di-deploy, `VITE_API_BASE_URL` perlu diarahkan ke URL API backend online.

Karena backend masih lokal, link Vercel saat ini terutama ditujukan sebagai preview tampilan dan belum menjamin seluruh fitur publik dapat digunakan.

## Menjalankan Secara Lokal

### Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Konfigurasikan koneksi PostgreSQL pada `backend/.env` sebelum menjalankan migration.

### Frontend

```bash
cd frontend
npm install
npm run dev
```

API lokal frontend harus diarahkan ke:

```env
VITE_API_BASE_URL=http://127.0.0.1:8000/api
```

## Deployment Frontend

Konfigurasi Vercel:

| Pengaturan | Nilai |
|---|---|
| Platform | Vercel |
| Root Directory | `frontend` |
| Build Command | `npm run build` |
| Output Directory | `dist` |

Environment sementara:

```env
VITE_API_BASE_URL=http://127.0.0.1:8000/api
```

Alamat lokal tersebut hanya dapat mengakses backend pada komputer developer sendiri. Pengguna publik memerlukan backend online yang dapat dijangkau melalui internet.

## Catatan Backend

Backend Laravel memerlukan hosting yang mendukung PHP/Laravel, VPS, atau container hosting. Deployment backend dapat ditambahkan kemudian dengan seluruh konfigurasi sensitif disimpan melalui environment hosting.

Setelah backend di-deploy, panel admin tersedia pada:

```text
https://domain-backend.example/admin
```

Panduan deployment lebih lengkap tersedia di [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md).

## Disclaimer

Nuanscent adalah proyek portfolio dan pembelajaran. Informasi produk dapat berubah mengikuti sumber brand atau toko resmi.

Nuanscent tidak berafiliasi secara resmi dengan brand parfum yang tercantum, kecuali dinyatakan secara khusus.
