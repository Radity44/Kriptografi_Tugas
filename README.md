# Sistem Pengajuan Magang Mahasiswa
### Implementasi Riset Kriptografi: Integritas Data pada Penyimpanan Cloud Menggunakan SHA-256

Aplikasi berbasis web responsif yang memudahkan mahasiswa mengajukan permohonan magang, mengunggah Curriculum Vitae (CV) dalam format PDF, serta memantau status pengajuan. Dokumen CV disimpan secara aman pada **Supabase Cloud Storage (Private Bucket)** dan dilindungi integritas datanya menggunakan algoritma **SHA-256** untuk mendeteksi setiap modifikasi atau manipulasi data di sisi cloud.

---

## 🛠️ Arsitektur Teknologi

- **Backend:** Laravel 12 / PHP 8.3
- **Frontend:** Blade Templating, Bootstrap 5.3, Bootstrap Icons, JavaScript ES6
- **Database:** Supabase PostgreSQL (Managed Shared Pooler dengan koneksi SSL `require`)
- **Cloud Storage:** Supabase Storage Private Bucket (`cv-mahasiswa`, batas ukuran 5 MB, MIME `application/pdf`)
- **Kriptografi:** Algoritma Hash SHA-256 (`hash_file('sha256')`, `hash_equals()`)
- **Pengujian:** PHPUnit (Unit & Feature Tests)

---

## 🔐 Mekanisme Integritas Dokumen SHA-256

```
[1. Mahasiswa Upload CV PDF]
             │
             ▼
[2. Validasi File (PDF, ≤ 5MB)]
             │
             ▼
[3. Hitung SHA-256 Dokumen Asli] ───► Simpan 64-Hex Hash ke Supabase PostgreSQL
             │
             ▼
[4. Upload Dokumen ke Supabase Cloud Storage (Private Bucket)]

────────────────────────────────────────────────────────────────────────────

[5. Admin Verifikasi Integritas Dokumen]
             │
             ▼
[6. Download Real-time Dokumen dari Cloud Storage via Stream Backend]
             │
             ▼
[7. Hitung Ulang SHA-256 Byte Dokumen Cloud]
             │
             ▼
[8. Komparasi Aman: hash_equals(Hash Asli, Hash Cloud)]
      │
      ├── Sama (True)   ──► Status: VALID (File Murni / Asli)
      └── Beda (False)  ──► Status: TAMPERED (Terdeteksi Manipulasi)
```

---

## 👥 Hak Akses (Role-Based Access Control)

### 1. Mahasiswa
- Registrasi dan login akun mahasiswa.
- Mengisi formulir pengajuan magang (nama perusahaan, posisi magang, deskripsi motivasi).
- Mengunggah CV berformat PDF (maksimal 5 MB).
- Memantau status pengajuan: **Menunggu Verifikasi**, **Diterima**, atau **Ditolak**.
- Melihat metadata dokumen dan sidik jari digital (SHA-256) dokumen miliknya.

### 2. Admin
- Login akun administrator.
- Dashboard statistik pengajuan magang.
- Melihat daftar dan riwayat seluruh pengajuan dari semua mahasiswa.
- Mengunduh/membuka file CV secara aman melalui stream backend.
- Menjalankan **Verifikasi Integritas Dokumen SHA-256** secara real-time.
- Menggunakan panel **Simulasi Manipulasi File** untuk keperluan pengujian dan demonstrasi riset.
- Menetapkan keputusan akhir pengajuan: **Diterima** / **Ditolak** disertai catatan admin.

---

## 🚀 Panduan Menjalankan Aplikasi Secara Lokal

### Prasyarat
- PHP 8.2 atau PHP 8.3 dengan ekstensi `pdo_pgsql`, `pgsql`, `openssl`, `mbstring`, `fileinfo`, `curl`, `zip`.
- Composer 2.x
- Koneksi internet aktif untuk sinkronisasi Supabase.

### 1. Clone & Setup Dependensi
```bash
git clone https://github.com/USERNAME/REPO_NAME.git
cd Tugas_SHA
composer install
```

### 2. Konfigurasi Environment (`.env`)
Salin file `.env.example` menjadi `.env`:
```bash
cp .env.example .env
php artisan key:generate
```

Pastikan variabel konfigurasi Supabase terisi:
```env
DB_CONNECTION=pgsql
DB_HOST=aws-1-ap-southeast-2.pooler.supabase.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.grpxzkvtinwsjtezgfqh
DB_PASSWORD=YOUR_SUPABASE_DB_PASSWORD
DB_SSLMODE=require

SUPABASE_URL=https://grpxzkvtinwsjtezgfqh.supabase.co
SUPABASE_KEY=YOUR_SUPABASE_SECRET_KEY
SUPABASE_BUCKET=cv-mahasiswa
```

### 3. Jalankan Migrasi & Database Seeder
```bash
php artisan migrate --seed
```

### 4. Jalankan Web Server
```bash
php artisan serve
```
Buka browser pada alamat: **`http://127.0.0.1:8000`**

---

## 🔑 Akun Uji Coba Default

| Role | Email | Password | Dashboard URL |
|---|---|---|---|
| **Admin** | `admin@magang.ac.id` | `Admin123!` | `/admin/dashboard` |
| **Mahasiswa** | `mahasiswa@magang.ac.id` | `Mahasiswa123!` | `/mahasiswa/dashboard` |

---

## 🧪 Pengujian Otomatis

Jalankan rangkaian unit & feature test menggunakan PHPUnit:
```bash
php artisan test
```
*Hasil pengujian mencakup verifikasi hash SHA-256, deteksi valid vs tampered, proteksi role middleware, dan respon autentikasi.*

---

## ☁️ Rekomendasi Deployment Hosting Gratis

1. **Render.com / Railway.app / Fly.io**:
   - Platform mendukung deployment Laravel/PHP secara gratis untuk demo.
   - Database tetap menggunakan **Supabase PostgreSQL** dan file CV tetap tersimpan di **Supabase Storage**.
   - Tambahkan environment variables yang ada pada `.env` ke dashboard deployment.
