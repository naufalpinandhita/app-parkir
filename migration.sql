-- =============================================
-- MIGRATION: Sistem Parkir VIP + Area-Based Petugas
-- =============================================
-- Jalankan migration ini pada database db_parkir yang sudah ada
-- =============================================

-- 1. Tambah kolom id_area di tb_user (untuk assign petugas ke area)
ALTER TABLE tb_user ADD COLUMN id_area INT(11) NULL AFTER status_aktif;
ALTER TABLE tb_user ADD CONSTRAINT fk_user_area 
    FOREIGN KEY (id_area) REFERENCES tb_area_parkir(id_area)
    ON DELETE SET NULL ON UPDATE CASCADE;

-- 2. Tambah kolom status_parkir di tb_kendaraan
ALTER TABLE tb_kendaraan ADD COLUMN status_parkir ENUM('tidak_parkir', 'parkir') 
    NOT NULL DEFAULT 'tidak_parkir' AFTER pemilik;

-- 3. Sinkronisasi status_parkir untuk kendaraan yang saat ini sedang parkir
UPDATE tb_kendaraan k
    JOIN tb_transaksi t ON k.id_kendaraan = t.id_kendaraan AND t.status = 'masuk'
    SET k.status_parkir = 'parkir';

-- 4. Tambah index untuk optimasi lookup
CREATE INDEX idx_kendaraan_status ON tb_kendaraan(status_parkir);
