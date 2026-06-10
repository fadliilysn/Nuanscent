# Panduan Deployment Manual Nuanscent

## Gambaran Umum

Nuanscent terdiri dari dua aplikasi terpisah:

- `frontend/`: React, TypeScript, dan Vite untuk antarmuka publik.
- `backend/`: Laravel, REST API, dan Filament untuk API serta panel admin.

Frontend dan backend dapat ditempatkan di layanan yang berbeda. Demo publik membutuhkan PostgreSQL daring, sedangkan frontend diarahkan ke API backend melalui environment variable.

Panduan ini mencakup persiapan dan langkah deployment manual. Panduan ini tidak melakukan deployment otomatis dan tidak memuat kredensial produksi.

## Arsitektur Demo yang Disarankan

- **Frontend statis:** Vercel, Netlify, atau static hosting serupa.
- **Backend Laravel:** Render, Koyeb, VPS, shared hosting Laravel, atau host lain yang mendukung PHP.
- **Database:** Supabase PostgreSQL, Render PostgreSQL, Neon, atau penyedia PostgreSQL lain.
- **Gambar produk:** tetap memakai URL publik resmi, retailer, atau CDN yang sudah tersimpan di katalog.

Pilihan layanan tidak dikunci di source code. Pertimbangkan dukungan PHP, PostgreSQL, biaya, lokasi server, serta kebijakan sleep dan cold start.

## Cakupan Panduan

Panduan ini menjelaskan konfigurasi environment, build, migration, seeding, CORS, panel admin, dan pemeriksaan setelah deployment. Panduan ini tidak:

- menghubungkan repository ke penyedia hosting;
- membuat database daring;
- menyediakan URL demo;
- menyimpan password, token, `APP_KEY`, atau kredensial admin.

## Deployment Frontend

Gunakan folder `frontend/` sebagai root aplikasi frontend:

```bash
cd frontend
npm install
npm run build
```

Konfigurasi hosting:

- Install command: `npm install`
- Build command: `npm run build`
- Output directory: `dist`

Karena frontend merupakan SPA, static hosting perlu mengarahkan route seperti `/parfum/nama-parfum` kembali ke `index.html`. Gunakan konfigurasi rewrite SPA dari penyedia hosting.

### Environment frontend

Nilai lokal:

```env
VITE_API_BASE_URL=http://127.0.0.1:8000/api
```

Contoh nilai deployment:

```env
VITE_API_BASE_URL=https://your-backend-domain.example/api
```

Environment Vite dimasukkan saat build. Setelah mengubah `VITE_API_BASE_URL`, lakukan build atau deployment frontend ulang.

## Deployment Backend

Gunakan folder `backend/` sebagai root aplikasi Laravel:

```bash
cd backend
composer install --no-dev --optimize-autoloader
```

Urutan umum:

1. Salin variabel yang diperlukan dari `.env.example` ke environment hosting.
2. Atur `APP_ENV=production` dan `APP_DEBUG=false`.
3. Atur `APP_URL` ke domain backend.
4. Atur koneksi PostgreSQL.
5. Atur `FRONTEND_URL` dan `CORS_ALLOWED_ORIGINS`.
6. Buat `APP_KEY` bila belum tersedia.
7. Jalankan migration dan alur data yang dipilih.
8. Arahkan document root ke folder Laravel `public/`.

Membuat nilai `APP_KEY` tanpa menulis `.env`:

```bash
php artisan key:generate --show
```

Simpan hasilnya sebagai secret `APP_KEY` di hosting. Jangan menaruhnya di Git atau dokumentasi.

Setelah environment final tersedia:

```bash
php artisan migrate --force
php artisan optimize
```

`php artisan optimize` membuat cache production. Jalankan ulang setelah konfigurasi berubah dan jangan commit file cache yang dihasilkan.

Health endpoint Laravel dapat diperiksa pada:

```text
https://your-backend-domain.example/up
```

## Environment Variables

### Frontend

| Variabel | Lokal | Deployment |
|---|---|---|
| `VITE_API_BASE_URL` | `http://127.0.0.1:8000/api` | URL backend dengan akhiran `/api` |

### Backend

Variabel utama:

- `APP_NAME=Nuanscent`
- `APP_ENV=production`
- `APP_KEY`: secret dari `php artisan key:generate --show`
- `APP_DEBUG=false`
- `APP_URL`: domain backend
- `FRONTEND_URL`: domain frontend utama
- `CORS_ALLOWED_ORIGINS`: daftar origin frontend yang dipisahkan koma
- `LOG_CHANNEL`
- `DB_CONNECTION=pgsql`
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `DB_URL`: opsional bila penyedia memberi connection URL
- `DB_SSLMODE`: ikuti persyaratan penyedia, umumnya `require` untuk database daring
- `CACHE_STORE`
- `SESSION_DRIVER`
- `QUEUE_CONNECTION`
- `FILESYSTEM_DISK`

Nilai lokal yang aman tersedia di `backend/.env.example`. Untuk demo sederhana, `QUEUE_CONNECTION=sync` tidak membutuhkan worker terpisah. Jika kemudian memakai queue asynchronous, siapkan driver dan worker yang sesuai.

## Database, Migration, dan Seeding

### Strategi fresh database

Seluruh dataset aktif berada di `backend/database/seeders/data/`. Folder root `data/` tidak diperlukan dan tidak boleh ditambahkan kembali ke Git.

Untuk database PostgreSQL baru yang masih boleh dihapus seluruh isinya:

```bash
cd backend
php artisan migrate:fresh --seed --force
```

> `migrate:fresh` menghapus seluruh tabel. Jangan jalankan command ini pada database yang berisi data penting tanpa backup.

`DatabaseSeeder` menjalankan data referensi, seluruh batch parfum, patch kurasi, panduan, note enrichment, dan patch preservasi fresh install dalam urutan yang diperlukan. Hasil yang diharapkan setelah seed:

- 119 parfum published;
- 0 parfum tanpa gambar;
- 0 URL gambar malformed;
- 0 duplikat parfum;
- 43 parfum masih memerlukan review harga;
- 5 parfum masih memerlukan review variant.

Untuk database yang sudah memiliki data dan hanya membutuhkan migration baru:

```bash
php artisan migrate --force
```

Seeder juga dapat dijalankan ulang secara idempotent:

```bash
php artisan db:seed --force
```

### Sumber data seeder

Semua seeder pembaca JSON menggunakan `database_path('seeders/data/...')`.

| Seeder | File data | Dipanggil `DatabaseSeeder` | Fungsi |
|---|---|---:|---|
| `NuanscentPerfumeBatch01Seeder` | `nuanscent_perfumes_batch_01.json` | Ya | Brand dan parfum Batch 01 |
| `NuanscentPerfumeBatch01VariantsPatchSeeder` | `nuanscent_perfumes_batch_01_variants_patch.json` | Ya | Variant Batch 01 |
| `NuanscentPerfumeBatch02Seeder` | `nuanscent_perfumes_batch_02.json` | Ya | Brand dan parfum Batch 02 |
| `NuanscentPerfumeBatch03Seeder` | `nuanscent_perfumes_batch_03.json` | Ya | Merged Batch 03 |
| `NuanscentPerfumeCleanBatch04Seeder` | `nuanscent_perfumes_clean_batch_04.json` | Ya | Kurasi kategori Clean dan deduplikasi slug kanonis |
| `NuanscentUnderrepresentedAromaBatch01Seeder` | `nuanscent_perfumes_underrepresented_aroma_batch_01.json` | Ya | Parfum kategori yang kurang terwakili |
| `NuanscentNoteEnrichmentSeeder` | `nuanscent_note_enrichment.json` | Ya | Enrichment master notes |
| `NuanscentProductImageUrlPatchSeeder` | `nuanscent_product_image_url_patch.json` | Ya | Patch gambar awal |
| `NuanscentNonHmnsProductImageUrlPatchSeeder` | `nuanscent_non_hmns_product_image_url_patch.json` | Ya | Patch gambar non-HMNS |
| `NuanscentProductImageUrlPatchBatch02Seeder` | `nuanscent_product_image_url_patch_batch_02.json` | Ya | Patch gambar lanjutan |
| `NuanscentPerfumePriceVariantPatch01Seeder` | `nuanscent_perfume_price_variant_patch_01.json` | Ya | Patch harga dan variant |
| `NuanscentFreshInstallCatalogStatePatchSeeder` | `nuanscent_fresh_install_catalog_state_patch.json` | Ya | Mempertahankan delta database audited saat fresh install |

Tidak ada seeder JSON legacy yang masih bergantung pada root `data/` atau berada di luar alur fresh seed.

### Alternatif import database

Sebagai alternatif, database PostgreSQL lokal yang sudah diaudit dapat diekspor lalu diimpor ke database deployment. Cara ini berguna bila penyedia hosting membatasi waktu eksekusi seeder.

Jangan commit:

- `.env` atau kredensial database;
- dump database yang mengandung data sensitif atau tidak diperlukan;
- folder root `data/`.

Pastikan `backend/database/seeders/data/` tetap terlacak karena diperlukan oleh fresh seed.

### Audit setelah seed atau import

```bash
php artisan nuanscent:audit-catalog-data
```

Audit memeriksa jumlah data, status published, kelengkapan gambar, harga, variant, tag, occasion, notes, kategori, URL gambar, dan kemungkinan duplikasi. Sebanyak 43 parfum yang belum memiliki harga dan 5 parfum tanpa variant merupakan kondisi kurasi yang diketahui, bukan kegagalan seed.

## CORS

Browser hanya mengizinkan frontend memanggil backend lintas domain jika origin frontend diizinkan oleh CORS.

Origin lokal bawaan:

- `http://localhost:5173`
- `http://127.0.0.1:5173`

Contoh deployment:

```env
FRONTEND_URL=https://your-frontend-domain.example
CORS_ALLOWED_ORIGINS=https://your-frontend-domain.example
```

Beberapa origin dapat dipisahkan dengan koma:

```env
CORS_ALLOWED_ORIGINS=https://your-frontend-domain.example,https://preview.example
```

Gunakan origin lengkap tanpa path `/api` dan hindari wildcard `*` pada deployment publik. Setelah mengubah environment:

```bash
php artisan optimize
```

## Panel Admin

Panel admin menjadi bagian dari backend. Jika domain backend adalah:

```text
https://your-backend-domain.example
```

panel admin tersedia di:

```text
https://your-backend-domain.example/admin
```

Database deployment harus memiliki user admin. Project menyediakan command interaktif Filament:

```bash
php artisan make:filament-user
```

Jalankan melalui shell hosting dengan email yang dikelola sendiri dan password kuat. Jangan menaruh kredensial admin di seeder publik, dokumentasi, atau Git. Pastikan `APP_ENV=production` dan `APP_DEBUG=false`.

## Gambar Eksternal

Gambar produk menggunakan URL publik dari sumber brand, retailer, atau CDN dan tidak disimpan secara lokal. Demo tidak membutuhkan proses download atau normalisasi gambar.

Konsekuensinya:

- kecepatan gambar bergantung pada server eksternal;
- URL dapat berubah atau dibatasi oleh sumber;
- sebagian gambar dapat lebih lambat daripada aset lokal.

## Checklist Lokal Sebelum Deployment

Backend:

```bash
cd backend
php artisan test --filter=PublicApiTest
php artisan test --filter=RecommendationApiTest
php artisan test --filter=PublicGuideApiTest
php artisan nuanscent:audit-catalog-data
```

Frontend:

```bash
cd frontend
npm run lint
npm run build
```

Periksa manual:

- homepage dan navigasi;
- katalog, search, filter, dan pagination;
- detail parfum;
- quiz dan alasan rekomendasi;
- perbandingan parfum;
- halaman brands dan panduan;
- panel admin lokal;
- tidak ada secret dalam file yang akan di-commit.

## Checklist Setelah Deployment

1. Homepage dapat dibuka.
2. Katalog memuat data dari API.
3. Search, filter, dan pagination bekerja.
4. Detail parfum dan gambar tampil.
5. Quiz menghasilkan rekomendasi.
6. Modal alasan rekomendasi bekerja.
7. Perbandingan parfum bekerja.
8. Halaman brands dan guides dapat dibuka.
9. `/admin` dapat dijangkau dan tetap membutuhkan login.
10. Request API tidak terkena error CORS.
11. Route langsung seperti `/parfum/:slug` tidak menghasilkan 404 dari static hosting.
12. Detail stack trace tidak terlihat publik.

## Troubleshooting

### CORS error

- Pastikan `CORS_ALLOWED_ORIGINS` berisi origin frontend lengkap.
- Jangan menambahkan `/api` ke origin.
- Jalankan ulang `php artisan optimize`.
- Pastikan frontend memakai domain yang didaftarkan.

### Frontend tidak dapat menghubungi API

- Periksa `VITE_API_BASE_URL` dan akhiran `/api`.
- Build ulang frontend setelah environment berubah.
- Buka endpoint API secara langsung.
- Frontend HTTPS sebaiknya tidak memanggil backend HTTP.

### `APP_KEY` belum tersedia

Jalankan `php artisan key:generate --show`, simpan hasilnya sebagai secret hosting, lalu buat ulang cache konfigurasi.

### Error 500 setelah deployment

- Periksa log hosting atau `storage/logs`.
- Pastikan dependency Composer terpasang.
- Pastikan web root menunjuk ke `backend/public/`.
- Periksa izin tulis `storage/` dan `bootstrap/cache/`.
- Jalankan migration dan cache ulang konfigurasi.

### Koneksi database gagal

- Periksa host, port, nama database, username, password, dan SSL mode.
- Bila memakai `DB_URL`, gunakan nilai dari penyedia.
- Pastikan firewall mengizinkan koneksi dari backend.

### Migration gagal

- Pastikan database kosong atau berada pada versi migration yang sesuai.
- Periksa permission user database.
- Jangan reset/drop database berisi data tanpa backup.

### Seeder gagal

- Baca slug atau referensi yang dilaporkan hilang.
- Jalankan reference seeder lebih dulu.
- Pastikan `backend/database/seeders/data/` tersedia.
- Pastikan seluruh file JSON dalam folder tersebut ikut ter-deploy.

### Gambar lambat atau rusak

Gambar berasal dari layanan eksternal. Uji URL secara langsung dan jangan menggantinya dengan gambar yang belum terverifikasi.

### Permission storage/cache

Pastikan proses PHP dapat menulis ke `storage/` dan `bootstrap/cache/`. Ikuti mekanisme permission resmi penyedia hosting.

### Cold start

Free hosting dapat menidurkan service. Request pertama setelah idle dapat lebih lambat.

### Build Vite memakai API yang salah

Perbarui `VITE_API_BASE_URL`, lalu build/deploy ulang. Nilai environment tertanam dalam bundle saat build.

## Keterbatasan Demo Publik

- Free hosting dapat sleep dan mengalami cold start.
- Database gratis dapat memiliki batas koneksi atau kapasitas.
- CDN gambar eksternal dapat lambat atau berubah.
- Sebagian harga dan variant masih membutuhkan review manual.
- Project belum ditujukan sebagai sistem production-grade.
- Belum ada akun pengguna publik permanen.

## Ringkasan Deployment Manual

1. Siapkan PostgreSQL daring.
2. Pilih metode reproduksi katalog: `migrate:fresh --seed` pada database kosong atau import database audited.
3. Deploy backend, atur environment, dan arahkan web root ke `public/`.
4. Jalankan migration/seeding atau import, audit katalog, dan buat user admin.
5. Pastikan API dan CORS bekerja.
6. Deploy frontend dengan `VITE_API_BASE_URL` menuju backend.
7. Jalankan smoke test seluruh route dan fitur utama.
8. Tambahkan URL demo ke README setelah URL publik final tersedia.
