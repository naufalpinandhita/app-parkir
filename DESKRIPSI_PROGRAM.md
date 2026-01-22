# DESKRIPSI PROGRAM APLIKASI PARKIR

## 📋 INFORMASI UMUM

**Nama Aplikasi:** Aplikasi Parkir  
**Versi:** 1.0.0  
**Tanggal Pembuatan:** 2026  
**Developer:** Tim Developer  
**Jenis Aplikasi:** Web-Based Application  
**Bahasa Pemrograman:** PHP 8.x  
**Database:** MySQL / MariaDB  
**Framework Frontend:** Bootstrap 5.3  

---

## 📖 DESKRIPSI SINGKAT

Aplikasi Parkir adalah sistem manajemen parkir berbasis web yang dirancang untuk mengelola operasional parkir kendaraan secara terkomputerisasi. Aplikasi ini memiliki 3 (tiga) level pengguna dengan hak akses berbeda: **Admin**, **Petugas**, dan **Owner**. Sistem ini mencakup fitur pengelolaan transaksi parkir masuk/keluar, perhitungan biaya otomatis, manajemen tarif dan area parkir, serta pelaporan pendapatan dengan visualisasi grafik.

---

## 🎯 TUJUAN APLIKASI

1. **Efisiensi Operasional**: Mempercepat proses pencatatan transaksi parkir masuk dan keluar
2. **Akurasi Perhitungan**: Menghitung biaya parkir secara otomatis berdasarkan durasi dan tarif
3. **Manajemen Data**: Mengelola data kendaraan, tarif, area parkir, dan pengguna secara terpusat
4. **Monitoring Real-time**: Memantau status parkir (terisi/tersedia) secara real-time
5. **Pelaporan**: Menyediakan laporan transaksi dan pendapatan dengan filter periode
6. **Audit Trail**: Mencatat setiap aktivitas pengguna untuk keperluan audit

---

## 👥 LEVEL PENGGUNA & HAK AKSES

### 1. Admin (Administrator)
**Deskripsi:** Pengelola sistem dengan akses penuh untuk konfigurasi dan manajemen master data.

**Hak Akses:**
- ✅ CRUD (Create, Read, Update, Delete) Data User
- ✅ CRUD Tarif Parkir (per jenis kendaraan)
- ✅ CRUD Area Parkir (kapasitas & status)
- ✅ CRUD Data Kendaraan
- ✅ Melihat Log Aktivitas Pengguna
- ✅ Akses Dashboard Statistik

**Menu:**
- Dashboard
- Data User
- Tarif Parkir
- Area Parkir
- Data Kendaraan
- Log Aktivitas

---

### 2. Petugas (Parking Attendant)
**Deskripsi:** Operator lapangan yang menangani transaksi parkir harian.

**Hak Akses:**
- ✅ Transaksi Parkir Masuk (registrasi kendaraan)
- ✅ Transaksi Parkir Keluar (perhitungan biaya otomatis)
- ✅ Cetak Struk Parkir (masuk & keluar)
- ✅ Melihat Daftar Kendaraan Sedang Parkir
- ✅ Akses Dashboard Statistik

**Menu:**
- Dashboard
- Transaksi Parkir
- Cetak Struk

---

### 3. Owner (Pemilik)
**Deskripsi:** Pemilik usaha yang fokus pada monitoring dan pelaporan bisnis.

**Hak Akses:**
- ✅ Dashboard Statistik Lengkap
- ✅ Laporan Transaksi dengan Filter Periode (harian, bulanan, tahunan)
- ✅ Grafik Pendapatan 7 Hari Terakhir
- ✅ Rekap Transaksi per Jenis Kendaraan
- ✅ Rekap Transaksi per Area Parkir
- ✅ Export/Print Laporan

**Menu:**
- Dashboard
- Laporan Transaksi

---

## 🗄️ STRUKTUR DATABASE

### Tabel-Tabel Utama

#### 1. tb_user
Menyimpan data pengguna sistem dengan role-based access.
- **Primary Key:** id_user
- **Kolom:** nama_lengkap, username, password, role (admin/petugas/owner), status_aktif
- **Fungsi:** Autentikasi dan otorisasi

#### 2. tb_kendaraan
Menyimpan data kendaraan yang pernah parkir.
- **Primary Key:** id_kendaraan
- **Kolom:** plat_nomor, jenis_kendaraan, warna, pemilik, id_user
- **Fungsi:** Master data kendaraan
- **Relasi:** Foreign key ke tb_user

#### 3. tb_tarif
Menyimpan tarif parkir per jenis kendaraan.
- **Primary Key:** id_tarif
- **Kolom:** jenis_kendaraan (motor/mobil/lainnya), tarif_per_jam
- **Fungsi:** Referensi perhitungan biaya

#### 4. tb_area_parkir
Menyimpan data area parkir beserta kapasitas.
- **Primary Key:** id_area
- **Kolom:** nama_area, kapasitas, terisi
- **Fungsi:** Manajemen slot parkir
- **Mekanisme:** Auto-update slot terisi saat transaksi

#### 5. tb_transaksi
Menyimpan seluruh transaksi parkir (inti sistem).
- **Primary Key:** id_parkir
- **Kolom:** id_kendaraan, waktu_masuk, waktu_keluar, id_tarif, durasi_jam, biaya_total, status (masuk/keluar), id_user, id_area
- **Fungsi:** Pencatatan transaksi parkir
- **Relasi:** 
  - Foreign key ke tb_kendaraan
  - Foreign key ke tb_tarif
  - Foreign key ke tb_user
  - Foreign key ke tb_area_parkir

#### 6. tb_log_aktivitas
Menyimpan log aktivitas pengguna untuk audit trail.
- **Primary Key:** id_log
- **Kolom:** id_user, aktivitas, waktu_aktivitas
- **Fungsi:** Tracking aktivitas sistem
- **Relasi:** Foreign key ke tb_user

### Indexing
Untuk optimasi performa query, dibuat index pada:
- `idx_kendaraan_plat` (plat_nomor)
- `idx_transaksi_waktu` (waktu_masuk, waktu_keluar)
- `idx_transaksi_status` (status)
- `idx_log_waktu` (waktu_aktivitas)
- `idx_user_username` (username)

---

## 📁 STRUKTUR FOLDER APLIKASI

```
app-parkir/
│
├── index.php                     # Entry point (redirect ke login/dashboard)
├── README.md                     # Dokumentasi singkat
├── schema.sql                    # Database schema & seeder
├── ujikompetensi.md             # Dokumentasi tugas
│
├── config/                       # Konfigurasi aplikasi
│   ├── app.php                   # Konfigurasi umum (APP_NAME, APP_URL, timezone, session)
│   └── database.php              # Koneksi database (mysqli)
│
├── helpers/                      # Fungsi-fungsi pembantu
│   └── functions.php             # Helper functions (format, auth, dll)
│
├── includes/                     # File yang sering di-include
│   ├── auth_check.php            # Middleware authentication
│   ├── header.php                # HTML header & CSS
│   ├── navbar.php                # Navigation bar (dinamis per role)
│   └── footer.php                # HTML footer & JavaScript
│
├── auth/                         # Modul autentikasi
│   ├── login.php                 # Halaman & proses login
│   └── logout.php                # Proses logout & destroy session
│
├── assets/                       # Asset statis
│   ├── css/
│   │   └── style.css             # Custom stylesheet
│   └── js/
│       └── script.js             # Custom JavaScript
│
└── pages/                        # Halaman-halaman aplikasi
    ├── dashboard.php             # Dashboard (accessible untuk semua role)
    │
    ├── admin/                    # Halaman khusus Admin
    │   ├── user.php              # CRUD User
    │   ├── tarif.php             # CRUD Tarif Parkir
    │   ├── area.php              # CRUD Area Parkir
    │   ├── kendaraan.php         # CRUD Kendaraan
    │   └── log.php               # Log Aktivitas
    │
    ├── petugas/                  # Halaman khusus Petugas
    │   ├── transaksi.php         # Transaksi Parkir Masuk/Keluar
    │   └── struk.php             # Cetak Struk Parkir
    │
    └── owner/                    # Halaman khusus Owner
        └── laporan.php           # Laporan & Rekap Transaksi
```

---

## ⚙️ ALUR KERJA APLIKASI

### 1. Proses Login
**INPUT:**
- Username
- Password

**PROSES:**
1. User mengakses halaman login (`auth/login.php`)
2. Sistem menerima input username dan password
3. Sistem melakukan query ke `tb_user` dengan kondisi:
   - Username sesuai
   - Status aktif = 1 (aktif)
4. Jika user ditemukan, verifikasi password
5. Jika password cocok:
   - Set session variabel (user_id, nama_lengkap, username, role)
   - Catat aktivitas login ke `tb_log_aktivitas`
   - Redirect ke dashboard
6. Jika gagal, tampilkan pesan error

**OUTPUT:**
- Session aktif dengan data user
- Redirect ke dashboard
- Log aktivitas tercatat

**FLOWCHART KONSEP:**
```
START → Input Username & Password → Query User → User Ditemukan? 
    → NO → Error "Username tidak ditemukan"
    → YES → Verifikasi Password → Password Cocok?
        → NO → Error "Password salah"
        → YES → Set Session → Log Aktivitas → Redirect Dashboard → END
```

---

### 2. Proses Transaksi Parkir Masuk
**INPUT:**
- Plat Nomor Kendaraan
- Jenis Kendaraan (motor/mobil/lainnya)
- Warna Kendaraan
- Area Parkir
- Tarif (berdasarkan jenis kendaraan)

**PROSES:**
1. Petugas mengakses menu Transaksi (`pages/petugas/transaksi.php`)
2. Petugas memilih "Parkir Masuk" dan input data kendaraan
3. Sistem mengecek apakah kendaraan sudah ada di `tb_kendaraan`:
   - Jika ada: Gunakan id_kendaraan yang sudah ada, update data jika perlu
   - Jika tidak ada: Insert kendaraan baru ke `tb_kendaraan`
4. Sistem mengecek apakah kendaraan sedang parkir (status = 'masuk')
   - Jika ya: Tampilkan error "Kendaraan masih parkir"
   - Jika tidak: Lanjutkan proses
5. Sistem insert data ke `tb_transaksi` dengan:
   - waktu_masuk = timestamp saat ini
   - status = 'masuk'
   - id_user = petugas yang input
6. Sistem update slot terisi di `tb_area_parkir` (terisi = terisi + 1)
7. Sistem catat log aktivitas
8. Generate nomor tiket parkir
9. Redirect ke halaman cetak struk

**OUTPUT:**
- Record baru di `tb_transaksi` dengan status 'masuk'
- Slot terisi area bertambah 1
- Struk parkir masuk dapat dicetak
- Log aktivitas tercatat

---

### 3. Proses Transaksi Parkir Keluar
**INPUT:**
- ID Parkir (dipilih dari daftar kendaraan sedang parkir)

**PROSES:**
1. Petugas memilih kendaraan dari daftar yang sedang parkir
2. Sistem query data transaksi berdasarkan id_parkir
3. Sistem mengambil waktu keluar = timestamp saat ini
4. Sistem menghitung durasi parkir:
   ```php
   durasi = ceil((waktu_keluar - waktu_masuk) / 3600)
   // Minimal 1 jam, dibulatkan ke atas
   ```
5. Sistem menghitung biaya total:
   ```php
   biaya_total = durasi × tarif_per_jam
   ```
6. Sistem update record `tb_transaksi`:
   - waktu_keluar = timestamp saat ini
   - durasi_jam = hasil perhitungan
   - biaya_total = hasil perhitungan
   - status = 'keluar'
7. Sistem update slot terisi di `tb_area_parkir` (terisi = terisi - 1)
8. Sistem catat log aktivitas dengan nominal
9. Tampilkan pesan sukses dengan total biaya
10. Link ke halaman cetak struk keluar

**OUTPUT:**
- Record di `tb_transaksi` status berubah jadi 'keluar'
- Durasi dan biaya terisi
- Slot terisi area berkurang 1
- Struk parkir keluar dapat dicetak
- Log aktivitas tercatat

**RUMUS PERHITUNGAN:**
```
Durasi (jam) = CEIL((Waktu Keluar - Waktu Masuk) / 3600)
Biaya Total = Durasi × Tarif Per Jam
Minimal: 1 jam
Pembulatan: Ke atas (ceiling)
```

---

### 4. Proses Cetak Struk
**INPUT:**
- ID Parkir (dari URL parameter)

**PROSES:**
1. Sistem query data lengkap transaksi dengan JOIN:
   - Data kendaraan (plat, jenis, warna)
   - Data area parkir
   - Data tarif
   - Data petugas
2. Generate nomor tiket: `PKR-{id_parkir padded 6 digit}`
3. Untuk struk masuk (status = 'masuk'):
   - Tampilkan waktu masuk
   - Tarif per jam
   - Estimasi biaya (optional)
4. Untuk struk keluar (status = 'keluar'):
   - Tampilkan waktu masuk & keluar
   - Durasi parkir
   - Biaya total (highlighted)
5. Render halaman struk dengan styling khusus print
6. Tombol print trigger `window.print()`

**OUTPUT:**
- Struk parkir dalam format printable
- Untuk masuk: Nomor tiket, waktu masuk, tarif
- Untuk keluar: Detail lengkap + total biaya

**FORMAT STRUK:**
```
============================================
       APLIKASI PARKIR
============================================
No. Tiket    : PKR-000001
Tanggal      : 22 Januari 2026 14:30
--------------------------------------------
Plat Nomor   : B 1234 XYZ
Jenis        : Motor
Warna        : Hitam
Area         : Area A - Motor
--------------------------------------------
Waktu Masuk  : 22 Jan 2026 14:30
Waktu Keluar : 22 Jan 2026 17:45
Durasi       : 4 Jam
--------------------------------------------
Tarif        : Rp 2.000/jam
TOTAL BAYAR  : Rp 8.000
============================================
         Terima Kasih
============================================
```

---

## 🔐 SISTEM KEAMANAN

### 1. Autentikasi
- **Session-based authentication**: Menggunakan PHP session
- **Login validation**: Cek username + password + status_aktif
- **Session variables**: user_id, nama_lengkap, username, role
- **Auto redirect**: User belum login → ke halaman login

### 2. Otorisasi (Role-Based Access Control)
- **Middleware**: `auth_check.php` di setiap halaman protected
- **Function**: `requireRole(['role1', 'role2'])` untuk restrict akses
- **Validation**: Cek role user sesuai dengan halaman yang diakses
- **Action**: Jika unauthorized → redirect ke dashboard dengan flash message

### 3. Input Validation & Sanitization
- **mysqli_real_escape_string()**: Untuk semua input dari user
- **Type casting**: `(int)` untuk ID, `strtoupper()` untuk plat nomor
- **XSS Prevention**: Function `escape()` = `htmlspecialchars()` di output

### 4. SQL Injection Prevention
- **Prepared statement approach**: Escape semua input sebelum query
- **Parameterized queries**: Hindari direct concatenation string di query
- **Validation**: Validasi tipe data sebelum query

### 5. Audit Trail
- **Log semua aktivitas**: Insert ke `tb_log_aktivitas`
- **Informasi log**: user_id, aktivitas, timestamp
- **Akses log**: Hanya admin yang bisa melihat

---

## 🧰 FUNGSI-FUNGSI HELPER UTAMA

### File: `helpers/functions.php`

#### 1. redirect($url)
**Deskripsi:** Redirect ke halaman tertentu  
**Parameter:** `$url` - URL tujuan (relative)  
**Return:** void  
**Contoh:**
```php
redirect('pages/dashboard.php');
```

#### 2. escape($string)
**Deskripsi:** Escape output untuk mencegah XSS  
**Parameter:** `$string` - String yang akan di-escape  
**Return:** String yang sudah aman  
**Contoh:**
```php
echo escape($user['nama_lengkap']);
```

#### 3. formatRupiah($amount)
**Deskripsi:** Format angka ke mata uang Rupiah  
**Parameter:** `$amount` - Jumlah uang (int/float)  
**Return:** String format "Rp 1.000.000"  
**Contoh:**
```php
echo formatRupiah(50000); // Output: Rp 50.000
```

#### 4. formatTanggal($date, $withTime = false)
**Deskripsi:** Format tanggal ke bahasa Indonesia  
**Parameter:** 
- `$date` - Tanggal (Y-m-d atau datetime)
- `$withTime` - Boolean, tampilkan jam atau tidak
**Return:** String "22 Januari 2026" atau "22 Januari 2026 14:30"  
**Contoh:**
```php
echo formatTanggal('2026-01-22', true); // 22 Januari 2026 14:30
```

#### 5. hitungDurasi($waktuMasuk, $waktuKeluar)
**Deskripsi:** Hitung durasi parkir dalam jam (pembulatan ke atas)  
**Parameter:** 
- `$waktuMasuk` - Datetime masuk
- `$waktuKeluar` - Datetime keluar
**Return:** Integer (minimal 1 jam)  
**Rumus:** `CEIL((keluar - masuk) / 3600)`  
**Contoh:**
```php
$durasi = hitungDurasi('2026-01-22 14:00:00', '2026-01-22 16:30:00');
// Return: 3 (jam)
```

#### 6. isLoggedIn()
**Deskripsi:** Cek apakah user sudah login  
**Return:** Boolean  
**Contoh:**
```php
if (!isLoggedIn()) {
    redirect('auth/login.php');
}
```

#### 7. currentUser($key = null)
**Deskripsi:** Ambil data user yang sedang login dari session  
**Parameter:** `$key` - Kolom yang ingin diambil (opsional)  
**Return:** String (jika $key diisi) atau array (jika $key kosong)  
**Contoh:**
```php
echo currentUser('nama_lengkap'); // Output: Administrator
$role = currentUser('role'); // Output: admin
```

#### 8. hasRole($roles)
**Deskripsi:** Cek apakah user memiliki salah satu role tertentu  
**Parameter:** `$roles` - Array role atau string single role  
**Return:** Boolean  
**Contoh:**
```php
if (hasRole(['admin', 'owner'])) {
    // Allow access
}
```

#### 9. setFlash($type, $message)
**Deskripsi:** Set flash message untuk notifikasi  
**Parameter:** 
- `$type` - Tipe alert (success, danger, warning, info)
- `$message` - Pesan yang ditampilkan
**Return:** void  
**Contoh:**
```php
setFlash('success', 'Data berhasil disimpan!');
```

#### 10. getFlash()
**Deskripsi:** Ambil dan tampilkan flash message (auto-clear)  
**Return:** HTML alert Bootstrap  
**Contoh:**
```php
<?= getFlash() ?>
```

---

## 📊 FITUR-FITUR UTAMA

### 1. Dashboard Statistik
**Untuk:** Semua role  
**Fitur:**
- Card statistik: Total Kendaraan, Sedang Parkir, Transaksi Hari Ini, Pendapatan Hari Ini
- Tabel transaksi terbaru (5 terakhir)
- Welcome message dengan nama user
- Quick stats dengan icon dan warna berbeda

### 2. Manajemen User (Admin)
**Fitur:**
- Tambah user baru dengan role selection
- Edit data user (nama, username, password, role, status)
- Hapus user (tidak bisa hapus diri sendiri)
- Toggle status aktif/nonaktif
- Validasi username unik
- Password optional saat edit (jika kosong tidak diupdate)

### 3. Manajemen Tarif (Admin)
**Fitur:**
- Tambah tarif per jenis kendaraan
- Edit tarif existing
- Hapus tarif (jika tidak ada transaksi terkait)
- Validasi jenis kendaraan unik
- Format input rupiah dengan thousand separator

### 4. Manajemen Area Parkir (Admin)
**Fitur:**
- Tambah area parkir dengan kapasitas
- Edit nama area dan kapasitas
- Hapus area (jika tidak ada transaksi terkait)
- Monitor slot terisi secara real-time
- Progress bar visual untuk occupancy rate
- Alert jika area penuh

### 5. Manajemen Kendaraan (Admin)
**Fitur:**
- Tambah kendaraan baru
- Edit data kendaraan
- Hapus kendaraan (jika tidak ada transaksi terkait)
- Pencarian kendaraan berdasarkan plat nomor
- Data pemilik (opsional)

### 6. Transaksi Parkir (Petugas)
**Fitur Parkir Masuk:**
- Input plat nomor (auto uppercase)
- Pilih jenis kendaraan (motor/mobil/lainnya)
- Input warna kendaraan
- Pilih area parkir (hanya area available)
- Auto-select tarif berdasarkan jenis kendaraan
- Cek duplikasi kendaraan sedang parkir
- Auto-update slot terisi

**Fitur Parkir Keluar:**
- List kendaraan sedang parkir
- Button "Proses Keluar" per kendaraan
- Auto-calculate durasi dan biaya
- Konfirmasi total bayar
- Print struk keluar

**Tampilan List:**
- Kendaraan aktif parkir dengan detail
- Estimasi durasi parkir saat ini
- Estimasi biaya terkini
- Quick action button

### 7. Cetak Struk (Petugas)
**Fitur:**
- Struk masuk: Nomor tiket, info kendaraan, waktu masuk, tarif
- Struk keluar: Semua info + waktu keluar, durasi, total biaya
- Format print-friendly (auto hide navbar & footer)
- Button print dengan JavaScript `window.print()`
- Nomor tiket format: PKR-000001

### 8. Laporan Transaksi (Owner)
**Fitur Filter:**
- Filter periode: Harian, Bulanan, Tahunan
- Date picker untuk pilih tanggal/bulan/tahun
- Button "Tampilkan Laporan"

**Statistik Ringkasan:**
- Total transaksi periode
- Total pendapatan (hanya yang sudah keluar)
- Kendaraan sedang parkir
- Total durasi parkir

**Rekap Per Kategori:**
- Rekap per jenis kendaraan (jumlah & pendapatan)
- Rekap per area parkir (jumlah & pendapatan)
- Tampilan table dengan subtotal

**Grafik Pendapatan:**
- Chart.js Line Chart
- 7 hari terakhir
- Format rupiah di tooltip
- Responsive design

**Tabel Detail Transaksi:**
- Pagination (15 data per halaman)
- Kolom: No, Plat Nomor, Jenis, Area, Waktu Masuk, Waktu Keluar, Durasi, Biaya, Status, Petugas
- Search & filter
- Export options (print)

### 9. Log Aktivitas (Admin)
**Fitur:**
- Tabel log semua aktivitas user
- Filter berdasarkan user
- Filter berdasarkan tanggal
- Pagination (20 data per halaman)
- Info: User, Role, Aktivitas, Timestamp
- Sorting by waktu descending (terbaru di atas)

---

## 🛠️ TEKNOLOGI & LIBRARY

### Backend
- **PHP:** 8.x (procedural style)
- **Database:** MySQL / MariaDB
- **Web Server:** Apache (Laragon recommended)
- **Session:** Native PHP session

### Frontend
- **HTML5 & CSS3**
- **Bootstrap 5.3:** Framework CSS untuk UI responsive
- **Bootstrap Icons 1.11:** Icon library
- **JavaScript Vanilla:** Untuk interaktivitas
- **Chart.js:** Library untuk grafik (di laporan owner)

### Konvensi Kode
- **Naming:** Snake_case untuk database, camelCase untuk PHP variables
- **Indentation:** 4 spaces
- **Comment:** PHPDoc style untuk dokumentasi function
- **Query:** Optimized dengan index, gunakan LIMIT untuk data besar

---

## 🚀 CARA INSTALASI & MENJALANKAN

### 1. Persiapan Environment
- Install Laragon (atau XAMPP/WAMP)
- PHP versi minimal 8.0
- MySQL/MariaDB
- Web browser modern (Chrome, Firefox, Edge)

### 2. Setup Database
```sql
1. Buat database baru: db_parkir
2. Import file schema.sql
3. Seeder otomatis termasuk:
   - 3 user (admin/petugas/owner, password: [role]123)
   - 3 tarif default
   - 3 area parkir
```

### 3. Konfigurasi Aplikasi
- Edit `config/database.php`:
  ```php
  define('DB_HOST', 'localhost');
  define('DB_USER', 'root');
  define('DB_PASS', '');
  define('DB_NAME', 'db_parkir');
  ```
- Edit `config/app.php`:
  ```php
  define('APP_URL', 'http://localhost/app-parkir');
  ```

### 4. Jalankan Aplikasi
- Start Laragon (Apache & MySQL)
- Akses browser: `http://localhost/app-parkir`
- Login default:
  - **Admin:** admin / admin123
  - **Petugas:** petugas / petugas123
  - **Owner:** owner / owner123

---

## 🔄 ALUR DATA LENGKAP

```
┌─────────────┐
│   LOGIN     │ ─── Set Session ─────┐
└─────────────┘                       │
                                      ▼
                            ┌──────────────────┐
                            │    DASHBOARD     │
                            └──────────────────┘
                                      │
                    ┌─────────────────┼─────────────────┐
                    ▼                 ▼                 ▼
              ┌─────────┐       ┌──────────┐     ┌──────────┐
              │  ADMIN  │       │ PETUGAS  │     │  OWNER   │
              └─────────┘       └──────────┘     └──────────┘
                    │                 │                 │
        ┌───────────┼─────────┐       │                 │
        ▼           ▼         ▼       ▼                 ▼
    [User]      [Tarif]   [Area]  [Transaksi]      [Laporan]
                                        │
                            ┌───────────┼───────────┐
                            ▼                       ▼
                      [Parkir Masuk]        [Parkir Keluar]
                            │                       │
                            ▼                       ▼
                      [Cetak Struk]          [Cetak Struk]
```

---

## 📝 KESIMPULAN

Aplikasi Parkir adalah solusi manajemen parkir yang komprehensif dan user-friendly. Dengan pemisahan hak akses yang jelas, sistem ini memastikan setiap pengguna hanya dapat mengakses fitur sesuai dengan perannya. 

### Keunggulan:
✅ **User Interface Intuitif:** Menggunakan Bootstrap untuk tampilan modern dan responsive  
✅ **Perhitungan Otomatis:** Durasi dan biaya dihitung otomatis oleh sistem  
✅ **Real-time Monitoring:** Status area parkir terupdate secara real-time  
✅ **Audit Trail Lengkap:** Semua aktivitas tercatat untuk keperluan audit  
✅ **Laporan Komprehensif:** Filter fleksibel dengan visualisasi grafik  
✅ **Keamanan:** Role-based access control dan input sanitization  
✅ **Performa Optimal:** Query dioptimasi dengan indexing database  

### Best Practices yang Diterapkan:
✅ Separation of Concerns (config, helpers, includes terpisah)  
✅ DRY Principle (fungsi helper reusable)  
✅ Consistent naming convention  
✅ Proper error handling  
✅ Flash message untuk user feedback  
✅ Responsive design untuk berbagai device  

---

## 📞 INFORMASI TAMBAHAN

**Dokumentasi Terkait:**
- [README.md](README.md) - Panduan singkat
- [schema.sql](schema.sql) - Database structure
- [ujikompetensi.md](ujikompetensi.md) - Dokumen tugas

**Dukungan:**
Untuk pertanyaan atau issue, silakan hubungi tim developer.

---

**Dibuat dengan ❤️ untuk Uji Kompetensi**  
**© 2026 Aplikasi Parkir Pinan - All Rights Reserved**
