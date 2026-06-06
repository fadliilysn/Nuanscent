# Nuanscent — Web Rekomendasi Parfum Lokal

Nuanscent adalah aplikasi web katalog dan rekomendasi parfum lokal Indonesia. Aplikasi ini membantu pengguna menjelajahi parfum berdasarkan brand, kategori aroma, tag aroma, notes, occasion, rentang harga, serta preferensi pribadi melalui quiz rekomendasi.

Nuanscent dirancang agar mudah digunakan oleh pemula, tetapi tetap menyediakan informasi terstruktur bagi pengguna yang ingin mempelajari dan membandingkan parfum dengan lebih rinci.

## Fitur Utama

- Homepage untuk mengenalkan katalog, kategori aroma, dan brand lokal.
- Katalog parfum dengan pencarian, filter, dan pagination.
- Filter berdasarkan brand, kategori aroma, tag aroma, occasion, dan harga.
- Halaman detail parfum dengan deskripsi, varian, harga, notes pyramid, tag aroma, dan sumber data.
- Quiz rekomendasi dengan dukungan beberapa preferensi aroma.
- Hasil rekomendasi yang menampilkan persentase kecocokan dan pertimbangan blind buy.
- Modal **Kenapa cocok?** untuk menjelaskan alasan rekomendasi.
- Fitur perbandingan hingga tiga parfum dari halaman katalog.
- Halaman daftar dan detail brand.
- Halaman panduan parfum yang ramah bagi pemula.
- Panel admin Filament untuk mengelola data katalog dan panduan.

## Tech Stack

### Backend

- Laravel 12
- PHP 8.2 atau lebih baru; PHP 8.3 direkomendasikan
- PostgreSQL
- REST API
- Filament 5
- PHPUnit

### Frontend

- React
- TypeScript
- Vite
- Custom CSS

## Struktur Proyek

```text
Nuanscent/
├── backend/     # Laravel REST API dan Filament admin
├── frontend/    # React, TypeScript, dan Vite
└── README.md
```

Backend dan frontend dijalankan sebagai aplikasi terpisah. Frontend mengakses data melalui REST API Laravel.

## Gambaran Model Data

Data utama Nuanscent disimpan dalam tabel berikut:

- `brands`
- `perfumes`
- `perfume_variants`
- `aroma_categories`
- `aroma_tags`
- `notes`
- `occasions`
- `guides`
- `perfume_aroma_tag`
- `perfume_occasion`
- `perfume_notes`

Data katalog dikelola melalui migration, seeder, serta dataset JSON yang dapat diimpor secara idempotent.

## Public Routes

| Route | Keterangan |
| --- | --- |
| `/` | Homepage |
| `/quiz` | Quiz rekomendasi parfum |
| `/parfum` | Katalog parfum |
| `/parfum/:slug` | Detail parfum |
| `/brands` | Daftar brand |
| `/brands/:slug` | Detail brand |
| `/guides` | Daftar panduan |
| `/guides/:slug` | Detail panduan |

## Backend API

Endpoint publik utama:

```text
GET  /api/perfumes
GET  /api/perfumes/{slug}
GET  /api/brands
GET  /api/brands/{slug}
GET  /api/aroma-categories
GET  /api/aroma-tags
GET  /api/occasions
GET  /api/guides
GET  /api/guides/{slug}
POST /api/recommendations
```

Endpoint katalog mendukung filter melalui query string. Endpoint rekomendasi menerima preferensi pengguna dan mengembalikan hasil beserta skor, alasan kecocokan, serta pertimbangan blind buy.

## Menjalankan Secara Lokal

### Prasyarat

- PHP 8.2 atau lebih baru
- Composer
- PostgreSQL
- Node.js dan npm

### Backend

```bash
cd backend
composer install
```

Salin file environment:

```bash
cp .env.example .env
```

Pada Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Buat application key:

```bash
php artisan key:generate
```

Atur koneksi PostgreSQL pada `.env`:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nuanscent
DB_USERNAME=postgres
DB_PASSWORD=
```

Jalankan migration dan seeder utama:

```bash
php artisan migrate
php artisan db:seed
```

Beberapa batch data parfum dan patch kurasi tersedia sebagai seeder terpisah di `backend/database/seeders`. Jalankan hanya seeder dataset yang memang diperlukan untuk environment tersebut.

Mulai backend:

```bash
php artisan serve
```

Secara default, API lokal dapat diakses melalui `http://127.0.0.1:8000/api`.

### Frontend

Buka terminal baru:

```bash
cd frontend
npm install
```

Salin file environment:

```bash
cp .env.example .env
```

Pada Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Pastikan frontend mengarah ke backend:

```dotenv
VITE_API_BASE_URL=http://127.0.0.1:8000/api
```

Mulai frontend:

```bash
npm run dev
```

Vite biasanya menjalankan frontend pada `http://localhost:5173`.

## Testing

### Backend

Jalankan dari folder `backend`:

```bash
php artisan test --filter=PublicApiTest
php artisan test --filter=RecommendationApiTest
php artisan test --filter=PublicGuideApiTest
```

### Frontend

Jalankan dari folder `frontend`:

```bash
npm run lint
npm run build
```

Proyek frontend tidak memiliki script `typecheck` terpisah. Pemeriksaan TypeScript dijalankan sebagai bagian dari `npm run build`.

## Audit Data Katalog

Nuanscent menyediakan command audit read-only:

```bash
cd backend
php artisan nuanscent:audit-catalog-data
```

Command ini tidak mengubah data. Laporannya mencakup:

- jumlah brand dan parfum;
- jumlah parfum berstatus published;
- kelengkapan gambar, harga, varian, kategori, tag, occasion, dan notes;
- URL gambar yang tampak tidak valid;
- kategori aroma yang masih kurang terwakili;
- brand tanpa parfum published;
- notes yang belum lengkap;
- kemungkinan duplikat note dan parfum.

Ambang kategori yang dianggap memiliki sangat sedikit parfum dapat disesuaikan:

```bash
php artisan nuanscent:audit-catalog-data --few-threshold=3
```

## Status Data Katalog

Katalog saat ini memuat sekitar 119 parfum published. Berdasarkan audit terakhir:

- seluruh parfum published telah memiliki `image_url`;
- tidak ditemukan URL gambar yang tampak malformed;
- tidak ditemukan duplikat parfum;
- sebagian data harga dan varian masih memerlukan review manual.

Jumlah data dapat berubah setelah seeder, patch, atau kurasi berikutnya dijalankan. Gunakan command audit untuk mendapatkan kondisi terbaru dari database aktif.

## Admin Panel

Panel admin tersedia melalui backend:

```text
/admin
```

Pada deployment, alamatnya mengikuti domain backend:

```text
<backend-domain>/admin
```

Akses admin memerlukan user yang valid. Gunakan password yang kuat dan jangan menyimpan kredensial di repository.

Panel admin digunakan untuk mengelola:

- parfum dan varian;
- brand;
- kategori dan tag aroma;
- notes;
- occasion;
- panduan.

## Deployment

Nuanscent belum mencantumkan URL deployment publik di repository ini. Arsitekturnya dapat dipasang secara terpisah:

- frontend pada Vercel, Netlify, atau layanan static hosting sejenis;
- backend Laravel pada hosting yang mendukung PHP;
- PostgreSQL pada penyedia managed database.

Konfigurasi penting:

### Frontend

```dotenv
VITE_API_BASE_URL=<backend-api-url>/api
```

### Backend

Atur `APP_URL`, konfigurasi database, dan origin frontend sesuai environment deployment. Jika konfigurasi deployment menggunakan `FRONTEND_URL`, nilainya harus menunjuk ke domain frontend yang sebenarnya.

```dotenv
APP_URL=<backend-url>
FRONTEND_URL=<frontend-url>

DB_CONNECTION=pgsql
DB_HOST=
DB_PORT=5432
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

Jangan menyimpan `.env`, password, token, atau kredensial deployment ke Git.

## Catatan Gambar Eksternal

Gambar produk menggunakan URL publik dari sumber brand resmi, retailer, atau CDN produk yang telah dikurasi. File gambar produk tidak disimpan dan dinormalisasi secara lokal oleh aplikasi.

Konsekuensinya:

- kecepatan gambar bergantung pada server sumber;
- URL eksternal dapat berubah atau berhenti tersedia;
- kebijakan hotlink dari sumber gambar dapat memengaruhi tampilan.

## Keterbatasan Demo Publik

- Free hosting dapat mengalami cold start.
- Gambar eksternal dapat dimuat lebih lambat.
- Sebagian data harga dan varian masih perlu ditinjau manual.
- Ketersediaan dan informasi produk dapat berubah mengikuti sumber resmi.
- Konfigurasi saat ini belum ditujukan sebagai deployment production-grade tanpa hardening dan observability tambahan.

## Roadmap

- Deployment frontend dan backend publik.
- Dokumentasi deployment yang lebih rinci.
- Peningkatan kelengkapan dan akurasi harga serta varian.
- Optimasi pengiriman dan fallback gambar.
- Fitur parfum favorit untuk pengguna.
- Riwayat hasil quiz.
- Penyesuaian rekomendasi berdasarkan feedback pengguna.

## Disclaimer

Nuanscent dibuat untuk eksplorasi, pembelajaran, dan portfolio pengembangan web rekomendasi parfum lokal. Informasi produk, harga, varian, notes, dan ketersediaan dapat berubah mengikuti brand atau toko resmi.

Rekomendasi bersifat sebagai bahan pertimbangan dan tidak menjamin sebuah parfum pasti cocok dengan setiap pengguna. Nuanscent tidak berafiliasi secara resmi dengan brand yang tercantum, kecuali dinyatakan secara eksplisit.

## Lisensi dan Penggunaan

Repository root belum menyediakan file lisensi khusus. Proyek ini dibuat untuk pembelajaran, portfolio, dan pengembangan aplikasi rekomendasi parfum lokal. Hubungi pemilik repository sebelum menggunakan atau mendistribusikan kode untuk tujuan lain.
