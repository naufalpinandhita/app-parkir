# 🚗 Aplikasi Parkir

Aplikasi manajemen parkir berbasis web dengan 3 level pengguna (Admin, Petugas, Owner).

![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-7952B3?style=flat&logo=bootstrap&logoColor=white)

## 📋 Deskripsi

Aplikasi Parkir adalah sistem manajemen parkir yang memungkinkan pengelolaan transaksi parkir kendaraan, pencatatan tarif, area parkir, dan pelaporan pendapatan. Dibuat sebagai proyek Uji Kompetensi.

## ✨ Fitur

### 👨‍💼 Admin
- CRUD Data User
- CRUD Tarif Parkir
- CRUD Area Parkir
- CRUD Data Kendaraan
- Log Aktivitas Pengguna

### 👷 Petugas
- Transaksi Parkir Masuk
- Transaksi Parkir Keluar (dengan perhitungan otomatis)
- Cetak Struk Parkir

### 👔 Owner
- Dashboard Statistik
- Laporan Transaksi dengan Filter Periode
- Grafik Pendapatan 7 Hari Terakhir
- Rekap per Jenis Kendaraan & Area

## 🛠️ Teknologi

- **Backend:** PHP 8.x
- **Database:** MySQL / MariaDB
- **Frontend:** Bootstrap 5.3, Bootstrap Icons
- **Chart:** Chart.js

## 📁 Struktur Folder

```
app-parkir/
├── config/          # Konfigurasi database & aplikasi
├── helpers/         # Fungsi helper (format, auth, dll)
├── includes/        # Header, footer, navbar, middleware
├── assets/
│   ├── css/         # Custom stylesheet
│   └── js/          # Custom JavaScript
├── auth/            # Login & Logout
├── pages/
│   ├── admin/       # Halaman CRUD Admin
│   ├── petugas/     # Halaman Transaksi & Struk
│   └── owner/       # Halaman Laporan
├── schema.sql       # Database schema
└── index.php        # Entry point
```

## ⚙️ Instalasi

### Prasyarat
- PHP 8.0 atau lebih baru
- MySQL 5.7 / MariaDB 10.x
- Web Server (Apache/Nginx) atau Laragon/XAMPP

### Langkah Instalasi

1. **Clone repository**
   ```bash
   git clone https://github.com/username/app-parkir.git
   ```

2. **Pindahkan ke direktori web server**
   ```bash
   # Untuk Laragon
   mv app-parkir C:/laragon/www/
   
   # Untuk XAMPP
   mv app-parkir C:/xampp/htdocs/
   ```

3. **Buat database**
   - Buka phpMyAdmin
   - Buat database baru dengan nama `db_parkir`

4. **Import schema database**
   - Pilih database `db_parkir`
   - Klik tab **Import**
   - Pilih file `schema.sql`
   - Klik **Go**

5. **Konfigurasi database** (opsional)
   
   Edit file `config/database.php` jika diperlukan:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'db_parkir');
   ```

6. **Akses aplikasi**
   ```
   http://localhost/app-parkir
   ```

## 🔐 Akun Default

| Username | Password | Role |
|----------|----------|------|
| `admin` | `admin123` | Admin |
| `petugas` | `petugas123` | Petugas |
| `owner` | `owner123` | Owner |

## 📸 Screenshot

### Halaman Login
![Login](docs/screenshots/login.png)

### Dashboard
![Dashboard](docs/screenshots/dashboard.png)

### Transaksi Parkir
![Transaksi](docs/screenshots/transaksi.png)

### Struk Parkir
![Struk](docs/screenshots/struk.png)

### Laporan Owner
![Laporan](docs/screenshots/laporan.png)

## 📊 ERD (Entity Relationship Diagram)

```
tb_user (id_user, nama_lengkap, username, password, role, status_aktif)
    │
    ├── tb_kendaraan (id_kendaraan, plat_nomor, jenis_kendaraan, warna, pemilik, id_user)
    │
    ├── tb_transaksi (id_parkir, id_kendaraan, waktu_masuk, waktu_keluar, 
    │                  id_tarif, durasi_jam, biaya_total, status, id_user, id_area)
    │
    └── tb_log_aktivitas (id_log, id_user, aktivitas, waktu_aktivitas)

tb_tarif (id_tarif, jenis_kendaraan, tarif_per_jam)

tb_area_parkir (id_area, nama_area, kapasitas, terisi)
```

## 📝 Lisensi

Proyek ini dibuat untuk keperluan Uji Kompetensi.

## 👨‍💻 Developer

Dibuat dengan ❤️ menggunakan PHP, MySQL, dan Bootstrap.
