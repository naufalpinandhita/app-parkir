-- =============================================
-- APLIKASI PARKIR - DATABASE SCHEMA
-- =============================================
-- Dibuat berdasarkan ERD untuk uji kompetensi
-- 3 Level User: Admin, Petugas, Owner
-- =============================================

-- Membuat database (opsional, uncomment jika diperlukan)
-- CREATE DATABASE IF NOT EXISTS db_parkir;
-- USE db_parkir;

-- =============================================
-- TABEL: tb_user
-- Deskripsi: Menyimpan data pengguna sistem
-- Level: admin, petugas, owner
-- =============================================
CREATE TABLE tb_user (
    id_user INT(11) NOT NULL AUTO_INCREMENT,
    nama_lengkap VARCHAR(50) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    role ENUM('admin', 'petugas', 'owner') NOT NULL,
    status_aktif TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- TABEL: tb_tarif
-- Deskripsi: Menyimpan tarif parkir per jenis kendaraan
-- =============================================
CREATE TABLE tb_tarif (
    id_tarif INT(11) NOT NULL AUTO_INCREMENT,
    jenis_kendaraan ENUM('motor', 'mobil', 'lainnya') NOT NULL,
    tarif_per_jam DECIMAL(10, 0) NOT NULL,
    PRIMARY KEY (id_tarif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- TABEL: tb_area_parkir
-- Deskripsi: Menyimpan data area parkir beserta kapasitas
-- =============================================
CREATE TABLE tb_area_parkir (
    id_area INT(11) NOT NULL AUTO_INCREMENT,
    nama_area VARCHAR(50) NOT NULL,
    kapasitas INT(5) NOT NULL,
    terisi INT(5) NOT NULL DEFAULT 0,
    PRIMARY KEY (id_area)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- TABEL: tb_kendaraan
-- Deskripsi: Menyimpan data kendaraan yang terdaftar
-- =============================================
CREATE TABLE tb_kendaraan (
    id_kendaraan INT(11) NOT NULL AUTO_INCREMENT,
    plat_nomor VARCHAR(15) NOT NULL,
    jenis_kendaraan VARCHAR(20) NOT NULL,
    warna VARCHAR(20),
    pemilik VARCHAR(100),
    id_user INT(11),
    PRIMARY KEY (id_kendaraan),
    CONSTRAINT fk_kendaraan_user 
        FOREIGN KEY (id_user) REFERENCES tb_user(id_user)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- TABEL: tb_transaksi
-- Deskripsi: Menyimpan data transaksi parkir
-- =============================================
CREATE TABLE tb_transaksi (
    id_parkir INT(11) NOT NULL AUTO_INCREMENT,
    id_kendaraan INT(11) NOT NULL,
    waktu_masuk DATETIME NOT NULL,
    waktu_keluar DATETIME,
    id_tarif INT(11) NOT NULL,
    durasi_jam INT(5) DEFAULT 0,
    biaya_total DECIMAL(10, 0) DEFAULT 0,
    status ENUM('masuk', 'keluar') NOT NULL DEFAULT 'masuk',
    id_user INT(11) NOT NULL,
    id_area INT(11) NOT NULL,
    PRIMARY KEY (id_parkir),
    CONSTRAINT fk_transaksi_kendaraan 
        FOREIGN KEY (id_kendaraan) REFERENCES tb_kendaraan(id_kendaraan)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_transaksi_tarif 
        FOREIGN KEY (id_tarif) REFERENCES tb_tarif(id_tarif)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_transaksi_user 
        FOREIGN KEY (id_user) REFERENCES tb_user(id_user)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_transaksi_area 
        FOREIGN KEY (id_area) REFERENCES tb_area_parkir(id_area)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- TABEL: tb_log_aktivitas
-- Deskripsi: Menyimpan log aktivitas pengguna
-- =============================================
CREATE TABLE tb_log_aktivitas (
    id_log INT(11) NOT NULL AUTO_INCREMENT,
    id_user INT(11) NOT NULL,
    aktivitas VARCHAR(100) NOT NULL,
    waktu_aktivitas DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_log),
    CONSTRAINT fk_log_user 
        FOREIGN KEY (id_user) REFERENCES tb_user(id_user)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- INDEX UNTUK OPTIMASI QUERY
-- =============================================
CREATE INDEX idx_kendaraan_plat ON tb_kendaraan(plat_nomor);
CREATE INDEX idx_transaksi_waktu ON tb_transaksi(waktu_masuk, waktu_keluar);
CREATE INDEX idx_transaksi_status ON tb_transaksi(status);
CREATE INDEX idx_log_waktu ON tb_log_aktivitas(waktu_aktivitas);
CREATE INDEX idx_user_username ON tb_user(username);

-- =============================================
-- DATA AWAL (SEEDER)
-- =============================================

-- Insert data tarif default
INSERT INTO tb_tarif (jenis_kendaraan, tarif_per_jam) VALUES
('motor', 2000),
('mobil', 5000),
('lainnya', 3000);

-- Insert user admin default (password: admin123 - HARUS di-hash di aplikasi)
INSERT INTO tb_user (nama_lengkap, username, password, role, status_aktif) VALUES
('Administrator', 'admin', '$2y$10$grbT0JAC1aNu4nNRDO8Okuy1ObLYdTx06kXCTLJcA83Q7HZ37tuaa', 'admin', 1),
('Petugas Parkir', 'petugas', '$2y$10$qm16Xy7TnQhLPzPPk36OcevtNd6FJI54XE/lr/jH5FBhSZaCR2p32', 'petugas', 1),
('Owner Parkir', 'owner', '$2y$10$GzA3yvrV/9HqC3qC9yNozu4RbyeoRhvnpjAI0ZsDDAmrRCAJGOFU6', 'owner', 1);

-- Insert area parkir default
INSERT INTO tb_area_parkir (nama_area, kapasitas, terisi) VALUES
('Area A - Motor', 50, 0),
('Area B - Mobil', 30, 0),
('Area C - VIP', 10, 0);
