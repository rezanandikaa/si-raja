# SI-RAJA — Sistem Informasi Analisa P3KE Kabupaten Lebak

> **Sistem Informasi Analisa P3KE** (*Percepatan Penghapusan Kemiskinan Ekstrem*) adalah platform berbasis web yang dirancang untuk mendukung pengelolaan, analisis, dan pemantauan data kemiskinan ekstrem di Kabupaten Lebak, Provinsi Banten — Indonesia.

---

## 📋 Daftar Isi

- [Tentang Proyek](#tentang-proyek)
- [Tech Stack](#tech-stack)
- [Arsitektur Aplikasi](#arsitektur-aplikasi)
- [Persyaratan Sistem](#persyaratan-sistem)
- [Getting Started](#getting-started)
  - [Cara 1: Instalasi Manual (Lokal)](#cara-1-instalasi-manual-lokal)
  - [Cara 2: Menggunakan Docker / Laravel Sail](#cara-2-menggunakan-docker--laravel-sail)
- [Konfigurasi Environment](#konfigurasi-environment)
- [Struktur Direktori](#struktur-direktori)
- [Artisan Commands](#artisan-commands)
- [Konfigurasi Server Produksi](#konfigurasi-server-produksi)
- [Menjalankan Test](#menjalankan-test)
- [Lisensi](#lisensi)

---

## Tentang Proyek

SI-RAJA dibangun sebagai alat bantu pemerintah daerah dalam:

- **Memvisualisasikan** data kemiskinan ekstrem berbasis wilayah (kabupaten, kecamatan, desa)
- **Mengelola program** penanggulangan kemiskinan beserta anggaran dan realisasinya
- **Mengimpor dan menyinkronisasi** data P3KE dari sumber data eksternal (format CSV)
- **Menghasilkan statistik** dan dashboard analitik secara otomatis
- **Mengelola akses pengguna** dengan kontrol berbasis peran (*role-based access control*)

---

## Tech Stack

### Backend

| Teknologi | Versi | Keterangan |
|---|---|---|
| **PHP** | `^8.1` | Bahasa pemrograman utama |
| **Laravel** | `^10.10` | Full-stack MVC framework |
| **Laravel Sanctum** | `^3.2` | Autentikasi berbasis token (API) |
| **Laravel UI** | `^4.2` | Scaffolding UI dan autentikasi |
| **Laravel Tinker** | `^2.8` | REPL interaktif untuk debugging |
| **Laravel Sail** | `^1.18` | Docker development environment |

### Database

| Teknologi | Versi | Keterangan |
|---|---|---|
| **MySQL** | `8.0` | Database relasional utama |
| **phpMyAdmin** | Latest | GUI manajemen database (via Docker) |

### Package dan Library Utama

| Package | Versi | Fungsi |
|---|---|---|
| **Maatwebsite Excel** | `^3.1` | Ekspor data ke format Excel |
| **League CSV** | `^9.11` | Pembacaan dan pemrosesan file CSV besar |
| **Intervention Image** | `^2.7` | Manipulasi dan kompresi gambar |
| **Yajra DataTables** | `^10.1` | Tabel dinamis server-side (AJAX) |
| **Mews Captcha** | `^3.3` | Proteksi form login dengan CAPTCHA |
| **Spatie Laravel Backup** | `^8.3` | Backup otomatis database dan storage |
| **GuzzleHTTP** | `^7.2` | HTTP client untuk integrasi API eksternal |

### Frontend

| Teknologi | Versi | Keterangan |
|---|---|---|
| **Vite** | `^4.0.0` | Build tool dan dev server modern |
| **Bootstrap** | `^5.2.3` | CSS framework responsive |
| **Sass** | `^1.56.1` | CSS preprocessor |
| **Axios** | `^1.1.2` | HTTP client berbasis promise |
| **Popper.js** | `^2.11.6` | Positioning engine untuk komponen Bootstrap |

### DevOps dan Tools

| Teknologi | Keterangan |
|---|---|
| **Docker & Docker Compose** | Containerisasi untuk pengembangan dan deployment |
| **Laravel Pint** | Code style fixer (PSR-12) |
| **PHPUnit** | `^10.1` — Framework testing |
| **Faker PHP** | Library pembuatan data dummy |
| **Spatie Ignition** | Error page yang informatif saat development |

---

## Arsitektur Aplikasi

Aplikasi mengikuti pola **MVC (Model-View-Controller)** yang diperkuat dengan **Repository Pattern** untuk memisahkan logika bisnis dari lapisan data.

```
app/
├── Console/Commands/       # Custom Artisan Commands (Baduy Engine)
├── Exports/                # Kelas ekspor Excel (Maatwebsite)
├── Helpers/                # Global helper functions
├── Http/
│   ├── Controllers/
│   │   ├── Auth/           # Autentikasi (Login, Register, Password)
│   │   ├── Master/         # CRUD data master
│   │   ├── Transaction/    # Fitur transaksi dan program
│   │   ├── System/         # Manajemen sistem (file, log, preferensi)
│   │   └── Website/        # Halaman publik
│   └── Middleware/         # Middleware kustom
├── Jobs/                   # Background Jobs (Queue)
├── Libraries/              # Library internal
├── Mail/                   # Kelas email
├── Models/
│   ├── Master/             # Model data master (mt_*)
│   ├── System/             # Model sistem (sy_*)
│   └── Transaction/        # Model transaksi (tr_*)
├── Observers/              # Model observers
├── Providers/              # Service providers
└── Repositories/           # Repository pattern (abstraksi query)
    ├── Master/
    ├── System/
    └── Transaction/
```

### Modul Utama

| Modul | Deskripsi |
|---|---|
| **Dashboard** | Statistik ringkasan data kemiskinan ekstrem per wilayah |
| **Data Kemiskinan (NIK/KK)** | Pengelolaan data individu dan kepala keluarga miskin ekstrem |
| **Program** | Manajemen program penanggulangan kemiskinan beserta anggaran |
| **Realisasi Program** | Pencatatan realisasi kegiatan dan penerima manfaat (BNBA) |
| **Galeri** | Dokumentasi foto kegiatan program |
| **Unduhan** | Ekspor data ke Excel/CSV |
| **Master Data** | Wilayah, organisasi, sumber anggaran, tahun anggaran |
| **Manajemen Pengguna** | Pengelolaan akun dan hak akses pengguna |
| **Preferensi Sistem** | Konfigurasi aplikasi dinamis |
| **Log Aktivitas** | Audit trail seluruh aktivitas pengguna |
| **Impor Data** | Unggah dan proses file CSV P3KE |

---

## Persyaratan Sistem

### Instalasi Manual

| Kebutuhan | Versi Minimum |
|---|---|
| PHP | `>= 8.1` (disarankan `8.2`) |
| Composer | `>= 2.x` |
| Node.js | `>= 18.x` |
| npm | `>= 9.x` |
| MySQL / MariaDB | `>= 8.0` |

### Menggunakan Docker

| Kebutuhan | Keterangan |
|---|---|
| Docker Desktop | Versi terbaru |
| Docker Compose | `>= 2.x` |

---

## Getting Started

### Cara 1: Instalasi Manual (Lokal)

#### Langkah 1 — Clone Repository

```bash
git clone https://github.com/<username>/si-raja.git
cd si-raja
```

#### Langkah 2 — Install Dependensi PHP

```bash
composer install
```

#### Langkah 3 — Install Dependensi Frontend

```bash
npm install
```

#### Langkah 4 — Konfigurasi Environment

```bash
# Salin file environment
cp .env.example .env

# Generate application key
php artisan key:generate
```

Kemudian sesuaikan konfigurasi database dan aplikasi pada file `.env`:

```env
APP_NAME="SI-RAJA P3KE Lebak"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=si_raja
DB_USERNAME=root
DB_PASSWORD=

# Konfigurasi tambahan wajib
ADMIN_LOGIN=administrator
```

#### Langkah 5 — Migrasi dan Seeding Database

```bash
php artisan migrate --seed
```

#### Langkah 6 — Publish Vendor Assets

```bash
# Publish seluruh asset vendor
php artisan vendor:publish

# Atau publish spesifik untuk CAPTCHA
php artisan vendor:publish --provider="Mews\Captcha\CaptchaServiceProvider"
```

#### Langkah 7 — Buat Symlink Storage

```bash
php artisan storage:link
```

#### Langkah 8 — Jalankan Aplikasi

Buka **dua terminal** secara bersamaan:

```bash
# Terminal 1 — Jalankan Laravel development server
php artisan serve

# Terminal 2 — Jalankan Vite (build frontend)
npm run dev
```

Aplikasi dapat diakses di: **http://localhost:8000**

---

### Cara 2: Menggunakan Docker / Laravel Sail

Pendekatan ini menggunakan **Laravel Sail** yang menyediakan environment Docker lengkap beserta MySQL dan phpMyAdmin secara otomatis.

#### Langkah 1 — Clone dan Konfigurasi

```bash
git clone https://github.com/<username>/si-raja.git
cd si-raja

# Salin file environment
cp .env.example .env
```

#### Langkah 2 — Install Dependensi via Docker (tanpa PHP lokal)

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
```

#### Langkah 3 — Jalankan Container

```bash
./vendor/bin/sail up -d
```

#### Langkah 4 — Setup Aplikasi

```bash
# Generate application key
./vendor/bin/sail artisan key:generate

# Jalankan migrasi dan seeding
./vendor/bin/sail artisan migrate --seed

# Publish vendor assets
./vendor/bin/sail artisan vendor:publish

# Install dependensi frontend
./vendor/bin/sail npm install

# Build frontend (development)
./vendor/bin/sail npm run dev
```

#### Layanan yang Tersedia

| Layanan | URL / Port |
|---|---|
| Aplikasi Web | `http://localhost:80` |
| phpMyAdmin | `http://localhost:8080` |
| MySQL | `localhost:3306` |
| Vite Dev Server | `http://localhost:5173` |

---

## Konfigurasi Environment

Berikut adalah variabel environment penting yang perlu dikonfigurasi pada file `.env`:

```env
# ─── Aplikasi ─────────────────────────────────────────────────────────────────
APP_NAME="SI-RAJA P3KE Lebak"
APP_ENV=production          # local | production | testing
APP_KEY=                    # Di-generate dengan: php artisan key:generate
APP_DEBUG=false
APP_URL=https://domain-anda.com

# ─── Database ─────────────────────────────────────────────────────────────────
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=si_raja
DB_USERNAME=your_username
DB_PASSWORD=your_password

# ─── Queue dan Cache ──────────────────────────────────────────────────────────
QUEUE_CONNECTION=database   # Wajib 'database' untuk import CSV via queue
CACHE_DRIVER=file
SESSION_DRIVER=file

# ─── Konfigurasi Khusus Aplikasi ─────────────────────────────────────────────
ADMIN_LOGIN=administrator   # Prefix path panel admin

# ─── Storage ──────────────────────────────────────────────────────────────────
FILESYSTEM_DISK=local
```

> **Penting:** Pastikan nilai `ADMIN_LOGIN` sesuai dengan `HOME` constant pada `RouteServiceProvider.php` (`public const HOME = '/administrator';`).

---

## Struktur Direktori

```
si-raja/
├── app/                    # Kode inti aplikasi Laravel
│   ├── Console/Commands/   # Custom artisan commands
│   ├── Exports/            # Kelas ekspor Excel
│   ├── Helpers/            # Global helper functions (helpers.php)
│   ├── Http/               # Controllers, Middleware, Requests
│   ├── Jobs/               # Background queue jobs
│   ├── Models/             # Eloquent ORM models
│   ├── Observers/          # Model event observers
│   ├── Providers/          # Service providers
│   └── Repositories/       # Repository pattern (abstraksi data)
├── bootstrap/              # Bootstrap dan cache aplikasi
├── config/                 # File konfigurasi Laravel
├── database/
│   ├── factories/          # Model factories untuk testing
│   ├── migrations/         # Skema migrasi database (52 file)
│   └── seeders/            # Database seeders
├── docker/                 # File konfigurasi Docker tambahan
├── lang/                   # File lokalisasi
├── public/                 # Document root (assets publik)
├── resources/
│   ├── js/                 # JavaScript frontend
│   ├── sass/               # File SCSS/Sass
│   └── views/              # Blade templates
├── routes/
│   ├── api.php             # Definisi route API
│   ├── web.php             # Definisi route web (utama)
│   └── console.php         # Definisi route console
├── storage/                # File upload, log, cache
├── stubs/                  # Template generator Laravel
├── tests/                  # Unit dan Feature tests
├── docker-compose.yml      # Konfigurasi Docker Compose
├── vite.config.js          # Konfigurasi Vite bundler
├── composer.json           # Dependensi PHP
└── package.json            # Dependensi Node.js
```

---

## Artisan Commands

Aplikasi ini memiliki custom commands yang dikumpulkan dalam namespace `baduyengine`:

### `baduyengine:import`

Memproses file CSV P3KE yang telah diunggah dan belum disinkronisasi, lalu mendistribusikannya ke queue untuk diimpor ke database secara batch (10.000 baris per chunk).

```bash
php artisan baduyengine:import
```

> **Catatan:** Perintah ini bergantung pada queue worker. Pastikan queue worker aktif sebelum menjalankannya.

---

### `baduyengine:sync`

Menyinkronisasi data dari database lokal ke database master P3KE pusat.

```bash
php artisan baduyengine:sync
```

---

### `baduyengine:statistic`

Menghasilkan dan memperbarui data statistik ringkasan untuk halaman dashboard secara otomatis. Disarankan dijalankan via cron job setiap 1 hingga 6 jam.

```bash
php artisan baduyengine:statistic
```

---

### `baduyengine:clear`

Membersihkan data tertentu dari database.

```bash
php artisan baduyengine:clear
```

---

### Menjalankan Queue Worker

Proses impor data CSV berjalan secara asinkron menggunakan Laravel Queue. Queue worker harus selalu aktif di server:

```bash
php artisan queue:work
```

> Untuk production, gunakan **Supervisor** agar queue worker berjalan secara persisten dan otomatis restart ketika terjadi kegagalan.

Contoh konfigurasi Supervisor (`/etc/supervisor/conf.d/si-raja-worker.conf`):

```ini
[program:si-raja-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/ke/proyek/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/ke/proyek/storage/logs/worker.log
stopwaitsecs=3600
```

---

### Menjalankan Scheduler (CronJob)

Tambahkan satu entri cron pada server untuk mengaktifkan Laravel Scheduler:

```cron
* * * * * cd /path/ke/proyek && php artisan schedule:run >> /dev/null 2>&1
```

Jadwal yang disarankan untuk commands utama:

```
# Jalankan import setiap 1 jam
0 * * * *   php artisan baduyengine:import

# Jalankan sinkronisasi setiap 1 jam (5 menit setelah import)
5 * * * *   php artisan baduyengine:sync

# Generate statistik setiap 6 jam
0 */6 * * * php artisan baduyengine:statistic
```

---

## Konfigurasi Server Produksi

### PHP (php.ini / php-fpm)

```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

Tambahkan atau sesuaikan konfigurasi berikut:

```ini
memory_limit = 512M
post_max_size = 512M
upload_max_filesize = 512M
max_execution_time = 1800
```

Restart PHP-FPM:

```bash
sudo service php8.2-fpm restart
```

---

### Nginx

```bash
sudo nano /etc/nginx/nginx.conf
```

Tambahkan dalam blok `http {}`:

```nginx
client_max_body_size 500M;
```

Restart Nginx:

```bash
sudo service nginx restart
```

---

### MariaDB / MySQL

```bash
sudo nano /etc/mysql/mariadb.conf.d/50-server.cnf
```

Tambahkan dalam blok `[mysqld]`:

```ini
max_allowed_packet = 1G
```

Restart MariaDB:

```bash
sudo service mariadb restart
```

---

### Optimasi Laravel untuk Production

```bash
# Optimasi konfigurasi
php artisan config:cache

# Optimasi routes
php artisan route:cache

# Optimasi views (Blade templates)
php artisan view:cache

# Optimasi autoloader
composer install --optimize-autoloader --no-dev
```

---

## Menjalankan Test

Aplikasi menggunakan **PHPUnit** untuk unit test dan feature test.

```bash
# Jalankan seluruh test suite
php artisan test

# Jalankan hanya Unit Tests
php artisan test --testsuite=Unit

# Jalankan hanya Feature Tests
php artisan test --testsuite=Feature

# Jalankan dengan output verbose
php artisan test --verbose

# Atau langsung menggunakan PHPUnit binary
./vendor/bin/phpunit
```

---

## Lisensi

Proyek ini dilisensikan di bawah **MIT License**. Lihat file [LICENSE](./LICENSE) untuk informasi lebih lanjut.

---

<div align="center">
  <sub>Dibuat dengan untuk Kabupaten Lebak, Provinsi Banten — Indonesia</sub>
</div>
