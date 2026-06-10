# Rencana Eksekusi Deployment Manual Nuanscent

## 1. Tujuan dan Batasan

Dokumen ini adalah runbook untuk men-deploy Nuanscent secara manual sebagai demo publik. Nuanscent terdiri dari:

- `frontend/`: React, TypeScript, dan Vite.
- `backend/`: Laravel REST API dan Filament.
- PostgreSQL: database katalog, panduan, dan user admin.
- Gambar produk: URL eksternal yang sudah tersimpan di katalog.

Frontend, backend, dan database boleh menggunakan layanan berbeda. Panduan ini tidak melakukan deployment otomatis, tidak menghubungkan repository ke penyedia hosting, dan tidak memuat kredensial produksi.

## 2. Jalur yang Direkomendasikan

Untuk demo pertama yang mudah dipahami:

1. Push kode terbaru ke repository Git.
2. Buat PostgreSQL hosted.
3. Deploy backend Laravel.
4. Isi environment backend dan `APP_KEY`.
5. Jalankan migration, seeding, dan audit katalog.
6. Buat user admin Filament.
7. Uji API serta `/admin`.
8. Deploy frontend Vite.
9. Isi `VITE_API_BASE_URL`.
10. Setelah URL frontend final diketahui, perbarui CORS backend.
11. Restart atau redeploy backend bila diperlukan.
12. Jalankan smoke test publik.
13. Tambahkan URL demo final ke README setelah semuanya stabil.

Urutan backend lebih dulu memudahkan frontend langsung diuji dengan API publik yang sudah aktif.

## 3. Pilihan Arsitektur Hosting

| Opsi | Frontend | Backend | Database | Cocok untuk |
|---|---|---|---|---|
| Demo terpisah | Vercel | Render, Koyeb, atau host PHP serupa | Supabase, Neon, Render PostgreSQL, atau hosted PostgreSQL lain | Demo publik sederhana dengan pengaturan layanan terpisah |
| Hosting yang sudah dimiliki | Netlify atau static hosting | Shared hosting PHP/Laravel atau VPS | Hosted PostgreSQL | Pengguna yang sudah memiliki hosting |
| Kendali penuh | VPS | VPS yang sama | VPS yang sama atau hosted PostgreSQL | Pengguna yang siap mengelola web server, SSL, process, backup, dan keamanan |

Paket gratis, batas resource, dan kebijakan sleep dapat berubah. Periksa dokumentasi penyedia saat benar-benar melakukan deployment.

## 4. Checklist Sebelum Push

Jalankan secara lokal:

```bash
cd backend
php artisan test --filter=PublicApiTest
php artisan test --filter=RecommendationApiTest
php artisan test --filter=PublicGuideApiTest
php artisan nuanscent:audit-catalog-data
```

```bash
cd frontend
npm run lint
npm run build
```

Pastikan:

- `.env` tidak masuk Git.
- Tidak ada password, token, `APP_KEY`, atau kredensial admin di repository.
- `backend/database/seeders/data/` ikut ter-push.
- Folder root `data/` tidak diperlukan dan tidak ditambahkan kembali.
- Build frontend berhasil.
- Audit katalog sesuai baseline.

## 5. Membuat PostgreSQL Hosted

1. Buat project atau database PostgreSQL di penyedia pilihan.
2. Simpan host, port, database, username, password, dan persyaratan SSL.
3. Izinkan koneksi dari backend sesuai mekanisme penyedia.
4. Jangan memasukkan kredensial database ke source code atau README.

Nuanscent dapat memakai variabel database terpisah atau `DB_URL` bila penyedia memberikan connection string. Contoh di bawah menggunakan variabel terpisah agar mudah dibaca.

## 6. Deployment Backend Laravel

### 6.1 Pengaturan layanan

- Root directory: `backend`
- Runtime: PHP 8.2 atau lebih baru; PHP 8.3 direkomendasikan.
- Document/web root: `backend/public` jika penyedia meminta pengaturan web root.
- Install command:

```bash
composer install --no-dev --optimize-autoloader
```

Pastikan `storage/` dan `bootstrap/cache/` dapat ditulis oleh proses PHP.

### 6.2 Render Web Service dengan Docker

Repository menyediakan `backend/Dockerfile` untuk deployment backend melalui Render Docker Web Service. Docker image memakai PHP 8.3 CLI, memasang ekstensi Laravel/PostgreSQL termasuk `pdo_pgsql`, menginstal dependency Composer tanpa package development, lalu menjalankan Laravel dari directory `public/` melalui:

```bash
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
```

Konfigurasi service di Render:

| Pengaturan | Nilai |
|---|---|
| Service Type | Web Service |
| Language | Docker |
| Root Directory | `backend` |
| Dockerfile repository path | `backend/Dockerfile` |
| Dockerfile path dari Root Directory | `./Dockerfile` |

Start command tidak perlu diisi ulang karena sudah ditentukan oleh `Dockerfile`. Render memberikan environment `PORT` saat runtime dan container otomatis mendengarkannya.

Isi environment melalui dashboard Render menggunakan nilai deployment sebenarnya. Contoh berikut hanya placeholder:

```env
APP_NAME=Nuanscent
APP_ENV=production
APP_KEY=base64:generated-key-here
APP_DEBUG=false
APP_URL=https://your-render-backend-url.onrender.com

FRONTEND_URL=https://your-frontend-domain.example
CORS_ALLOWED_ORIGINS=https://your-frontend-domain.example

DB_CONNECTION=pgsql
DB_HOST=your-supabase-pooler-host
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=your-supabase-db-user
DB_PASSWORD=your-supabase-db-password
DB_SSLMODE=require

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local
LOG_CHANNEL=stack
```

Jangan upload `.env` atau memasukkan secret ke Docker image. Setelah deployment pertama berhasil, jalankan command berikut melalui Render Shell atau mekanisme one-off command yang setara:

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan nuanscent:audit-catalog-data
php artisan make:filament-user
php artisan optimize
```

Migration dan seeder sengaja tidak dijalankan saat Docker build maupun setiap container start. Dengan begitu, build tidak bergantung pada database dan redeploy tidak menjalankan perubahan data tanpa keputusan eksplisit.

### 6.3 Environment backend

Gunakan placeholder berikut sebagai checklist, bukan sebagai secret nyata:

```env
APP_NAME=Nuanscent
APP_ENV=production
APP_KEY=base64:generated-key-here
APP_DEBUG=false
APP_URL=https://your-backend-domain.example

FRONTEND_URL=https://your-frontend-domain.example
CORS_ALLOWED_ORIGINS=https://your-frontend-domain.example

DB_CONNECTION=pgsql
DB_HOST=your-db-host
DB_PORT=5432
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password
DB_SSLMODE=require

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local
LOG_CHANNEL=stack
```

Catatan:

- Buat `APP_KEY` dengan `php artisan key:generate --show`.
- Simpan hasil key sebagai secret environment hosting.
- `APP_DEBUG` harus `false` pada deployment publik.
- `APP_URL` adalah origin backend, tanpa akhiran `/api`.
- `FRONTEND_URL` dan `CORS_ALLOWED_ORIGINS` adalah origin frontend, tanpa path `/api`.
- `DB_SSLMODE` mengikuti penyedia; hosted PostgreSQL sering menggunakan `require`.
- Driver `file` membutuhkan filesystem yang writable. Untuk host dengan filesystem ephemeral, periksa dukungan provider atau gunakan driver persisten yang tersedia sebelum deployment jangka panjang.
- `QUEUE_CONNECTION=sync` tidak membutuhkan worker queue terpisah.

Jika frontend belum memiliki URL final, gunakan origin lokal bawaan saat pengujian dan perbarui environment CORS setelah frontend dibuat.

### 6.4 Membuat APP_KEY

Jalankan di shell backend:

```bash
php artisan key:generate --show
```

Salin hasilnya ke environment hosting sebagai `APP_KEY`. Jangan menjalankan command yang menulis `.env` bila hosting mengelola environment melalui dashboard.

### 6.5 Command deployment backend

Urutan aman untuk database demo baru:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --force
php artisan nuanscent:audit-catalog-data
php artisan make:filament-user
php artisan optimize
```

Penjelasan:

- `migrate --force` membuat struktur database tanpa menghapus tabel secara paksa.
- `db:seed --force` menjalankan seluruh alur `DatabaseSeeder`.
- Audit memastikan katalog berhasil direproduksi.
- `make:filament-user` membuat akun admin secara interaktif.
- `optimize` membuat cache konfigurasi, route, event, dan view untuk runtime production.

Untuk database demo yang memang boleh direset sepenuhnya:

```bash
php artisan migrate:fresh --seed --force
```

`migrate:fresh` menghapus seluruh tabel. Jangan menjalankannya pada database berisi data penting tanpa backup dan keputusan reset yang jelas.

## 7. Database dan Seeding

Seluruh file data aktif berada di:

```text
backend/database/seeders/data/
```

Root `data/` tidak diperlukan untuk deployment. `DatabaseSeeder` sudah menjalankan data referensi, batch parfum, patch variant/gambar/harga, enrichment notes, panduan, deduplikasi, dan patch preservasi fresh install dalam urutan yang benar.

Untuk database PostgreSQL kosong:

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan nuanscent:audit-catalog-data
```

Baseline audit yang diharapkan:

| Metrik | Nilai |
|---|---:|
| Brand | 8 |
| Parfum published | 119 |
| Gambar hilang | 0 |
| URL gambar malformed | 0 |
| Duplikat parfum | 0 |
| Harga perlu review | 43 |
| Variant perlu review | 5 |

Harga dan variant yang belum lengkap adalah item review manual yang diketahui dan dapat diterima untuk demo saat ini.

### Alternatif import database

Database PostgreSQL lokal yang telah diaudit juga dapat diekspor dan diimpor ke database hosted. Pilihan ini berguna bila hosting membatasi waktu seeding.

Jangan commit dump database bila mengandung data sensitif atau tidak diperlukan. Setelah import, tetap jalankan:

```bash
php artisan migrate --force
php artisan nuanscent:audit-catalog-data
```

## 8. Membuat User Admin

Setelah migration dan seeding:

```bash
php artisan make:filament-user
```

Gunakan email yang dikelola sendiri dan password yang kuat. Jangan menyimpan kredensial admin di Git, dokumentasi, atau seeder publik.

Format URL admin:

```text
https://your-backend-domain.example/admin
```

Halaman `/admin` harus tetap menampilkan login dan tidak boleh membuka dashboard tanpa autentikasi.

## 9. Uji Backend Sebelum Frontend

Periksa:

```text
GET https://your-backend-domain.example/up
GET https://your-backend-domain.example/api/perfumes
GET https://your-backend-domain.example/api/brands
GET https://your-backend-domain.example/api/guides
```

Uji `POST /api/recommendations` menggunakan client API atau frontend setelah tersedia. Pastikan `/admin` membuka halaman login.

Jika endpoint belum bekerja, selesaikan backend dan database terlebih dahulu sebelum men-deploy frontend.

## 10. Deployment Frontend

### 10.1 Build settings

Gunakan konfigurasi berikut:

| Pengaturan | Nilai |
|---|---|
| Root directory | `frontend` |
| Install command | `npm install` |
| Build command | `npm run build` |
| Output directory | `dist` |

### 10.2 Environment frontend

```env
VITE_API_BASE_URL=https://your-backend-domain.example/api
```

`VITE_API_BASE_URL` wajib memiliki akhiran `/api`. Nilai Vite dimasukkan ke bundle saat build, sehingga perubahan environment memerlukan build atau redeploy frontend.

### 10.3 SPA rewrite

Static hosting harus mengarahkan route frontend yang tidak cocok dengan file fisik kembali ke `index.html`. Ini diperlukan agar direct URL berikut tidak menghasilkan 404:

- `/quiz`
- `/parfum`
- `/parfum/:slug`
- `/brands`
- `/brands/:slug`
- `/guides`
- `/guides/:slug`

Gunakan fitur rewrite/fallback SPA resmi dari penyedia hosting.

## 11. Menyelesaikan CORS

Setelah URL frontend final diketahui, ubah environment backend:

```env
FRONTEND_URL=https://your-frontend-domain.example
CORS_ALLOWED_ORIGINS=https://your-frontend-domain.example
```

Aturan penting:

- Gunakan origin frontend saja, tanpa `/api` dan tanpa path halaman.
- Jangan memakai wildcard `*` untuk deployment publik.
- Untuk beberapa origin yang benar-benar diperlukan, pisahkan dengan koma:

```env
CORS_ALLOWED_ORIGINS=https://your-frontend-domain.example,https://your-preview-domain.example
```

Setelah environment berubah:

```bash
php artisan optimize:clear
php artisan optimize
```

Restart atau redeploy backend jika provider tidak memuat ulang environment secara otomatis. Kemudian muat ulang frontend dan periksa browser console.

## 12. Smoke Test Setelah Deployment

### Backend

- [ ] `/up` merespons sukses.
- [ ] `/api/perfumes` mengembalikan katalog.
- [ ] `/api/brands` mengembalikan brand.
- [ ] `/api/guides` mengembalikan panduan.
- [ ] `POST /api/recommendations` mengembalikan rekomendasi.
- [ ] `/admin` membuka halaman login.
- [ ] `APP_DEBUG=false` dan stack trace tidak terlihat publik.

### Frontend

- [ ] Homepage dapat dibuka.
- [ ] Katalog memuat data.
- [ ] Search, filter, pagination, dan reset filter bekerja.
- [ ] Detail parfum bekerja dari klik dan direct URL.
- [ ] Quiz dapat diselesaikan.
- [ ] Modal **Kenapa cocok?** bekerja.
- [ ] Perbandingan parfum bekerja.
- [ ] Halaman brands dan detail brand bekerja.
- [ ] Halaman guides dan detail guide bekerja.
- [ ] Gambar produk tampil.
- [ ] Browser console tidak menunjukkan error CORS atau mixed content.
- [ ] Route langsung tidak menghasilkan 404.

## 13. Troubleshooting Cepat

| Masalah | Kemungkinan penyebab | Tindakan |
|---|---|---|
| Frontend blank | Build gagal, output salah, atau error JavaScript | Periksa build log, gunakan output `dist`, lalu lihat browser console |
| Frontend masih memanggil localhost | `VITE_API_BASE_URL` salah atau bundle belum dibangun ulang | Perbarui environment lalu redeploy frontend |
| CORS error | Origin frontend belum diizinkan atau memakai path `/api` | Perbaiki `FRONTEND_URL`/`CORS_ALLOWED_ORIGINS`, bersihkan cache, restart backend |
| Backend error 500 | Environment, dependency, permission, atau cache bermasalah | Periksa log hosting, `APP_KEY`, web root, `storage/`, dan `bootstrap/cache/` |
| `APP_KEY` missing | Key belum dibuat atau belum masuk environment | Jalankan `php artisan key:generate --show`, simpan sebagai secret, restart |
| Koneksi database gagal | Host, port, kredensial, firewall, atau SSL salah | Cocokkan detail provider dan `DB_SSLMODE` |
| Seeder gagal | File JSON tidak ter-deploy atau migration belum lengkap | Pastikan `backend/database/seeders/data/` tersedia dan jalankan migration lebih dulu |
| `/admin` tidak ditemukan | Web root/routing backend salah atau Filament belum termuat | Arahkan web root ke `public/`, cek install log dan route backend |
| Direct route frontend 404 | SPA rewrite belum dikonfigurasi | Tambahkan fallback semua route frontend ke `index.html` |
| Gambar lambat/rusak | Sumber gambar eksternal lambat atau URL berubah | Uji URL langsung; jangan mengganti dengan gambar yang tidak terverifikasi |
| Request awal lambat | Free hosting sedang sleep/cold start | Tunggu service aktif atau gunakan hosting tanpa sleep |

## 14. Keterbatasan Demo Publik

- Free hosting dapat sleep dan mengalami cold start.
- Paket database gratis dapat memiliki batas koneksi atau kapasitas.
- Gambar berasal dari URL eksternal dan dapat lambat atau berubah.
- Sebagian harga serta variant masih memerlukan review.
- Filesystem beberapa layanan bersifat ephemeral.
- Project belum ditujukan sebagai deployment production-grade penuh.
- Belum ada akun pengguna publik permanen.

## 15. Ringkasan Eksekusi

1. Commit dan push kode terbaru.
2. Buat PostgreSQL hosted.
3. Deploy backend dari folder `backend`.
4. Isi environment dan `APP_KEY`.
5. Jalankan `migrate`, `db:seed`, dan audit.
6. Buat user Filament.
7. Uji API, health endpoint, dan admin.
8. Deploy frontend dari folder `frontend`.
9. Isi `VITE_API_BASE_URL` dan aktifkan SPA rewrite.
10. Perbarui CORS backend memakai URL frontend final.
11. Restart/redeploy backend, lalu jalankan smoke test.
12. Tambahkan URL demo final ke README saat deployment sudah stabil.
