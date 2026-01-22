-- =============================================
-- SEEDER: Mengisi Area Parkir Sampai Penuh
-- =============================================
-- Script ini akan mengisi semua area parkir dengan
-- kendaraan random untuk keperluan testing.
-- Jalankan di phpMyAdmin atau MySQL CLI
-- =============================================

-- Hapus transaksi & kendaraan test yang mungkin sudah ada (opsional)
-- DELETE FROM tb_transaksi WHERE id_kendaraan > 0;
-- DELETE FROM tb_kendaraan WHERE id_kendaraan > 0;
-- UPDATE tb_area_parkir SET terisi = 0;

-- =============================================
-- VARIABEL HELPER
-- =============================================
-- Kode wilayah: AB, AD, B, D, N, L, W, H, K, AA, AG, Z, F, E, T
-- Warna: Hitam, Putih, Merah, Biru, Silver, Abu-abu, Hijau, Kuning, Orange, Coklat

-- =============================================
-- AREA A - MOTOR (50 slot)
-- id_area = 1, id_tarif = 1 (motor)
-- =============================================

-- Insert 50 motor ke tb_kendaraan dan tb_transaksi
INSERT INTO tb_kendaraan (plat_nomor, jenis_kendaraan, warna, pemilik) VALUES
('AB 1001 AA', 'motor', 'Hitam', NULL),
('AD 1002 BB', 'motor', 'Putih', NULL),
('B 1003 CC', 'motor', 'Merah', NULL),
('D 1004 DD', 'motor', 'Biru', NULL),
('N 1005 EE', 'motor', 'Silver', NULL),
('L 1006 FF', 'motor', 'Abu-abu', NULL),
('W 1007 GG', 'motor', 'Hijau', NULL),
('H 1008 HH', 'motor', 'Kuning', NULL),
('K 1009 II', 'motor', 'Orange', NULL),
('AA 1010 JJ', 'motor', 'Coklat', NULL),
('AG 1011 KK', 'motor', 'Hitam', NULL),
('Z 1012 LL', 'motor', 'Putih', NULL),
('F 1013 MM', 'motor', 'Merah', NULL),
('E 1014 NN', 'motor', 'Biru', NULL),
('T 1015 OO', 'motor', 'Silver', NULL),
('AB 1016 PP', 'motor', 'Abu-abu', NULL),
('AD 1017 QQ', 'motor', 'Hijau', NULL),
('B 1018 RR', 'motor', 'Kuning', NULL),
('D 1019 SS', 'motor', 'Orange', NULL),
('N 1020 TT', 'motor', 'Coklat', NULL),
('L 1021 UU', 'motor', 'Hitam', NULL),
('W 1022 VV', 'motor', 'Putih', NULL),
('H 1023 WW', 'motor', 'Merah', NULL),
('K 1024 XX', 'motor', 'Biru', NULL),
('AA 1025 YY', 'motor', 'Silver', NULL),
('AG 1026 ZZ', 'motor', 'Abu-abu', NULL),
('Z 1027 AB', 'motor', 'Hijau', NULL),
('F 1028 CD', 'motor', 'Kuning', NULL),
('E 1029 EF', 'motor', 'Orange', NULL),
('T 1030 GH', 'motor', 'Coklat', NULL),
('AB 1031 IJ', 'motor', 'Hitam', NULL),
('AD 1032 KL', 'motor', 'Putih', NULL),
('B 1033 MN', 'motor', 'Merah', NULL),
('D 1034 OP', 'motor', 'Biru', NULL),
('N 1035 QR', 'motor', 'Silver', NULL),
('L 1036 ST', 'motor', 'Abu-abu', NULL),
('W 1037 UV', 'motor', 'Hijau', NULL),
('H 1038 WX', 'motor', 'Kuning', NULL),
('K 1039 YZ', 'motor', 'Orange', NULL),
('AA 1040 AC', 'motor', 'Coklat', NULL),
('AG 1041 BD', 'motor', 'Hitam', NULL),
('Z 1042 CE', 'motor', 'Putih', NULL),
('F 1043 DF', 'motor', 'Merah', NULL),
('E 1044 EG', 'motor', 'Biru', NULL),
('T 1045 FH', 'motor', 'Silver', NULL),
('AB 1046 GI', 'motor', 'Abu-abu', NULL),
('AD 1047 HJ', 'motor', 'Hijau', NULL),
('B 1048 IK', 'motor', 'Kuning', NULL),
('D 1049 JL', 'motor', 'Orange', NULL),
('N 1050 KM', 'motor', 'Coklat', NULL);

-- Simpan ID awal motor (asumsi auto increment dari 1)
SET @motor_start_id = (SELECT MIN(id_kendaraan) FROM tb_kendaraan WHERE jenis_kendaraan = 'motor' AND plat_nomor LIKE '%1001%');

-- Insert transaksi untuk 50 motor (waktu masuk random dalam 3 jam terakhir)
INSERT INTO tb_transaksi (id_kendaraan, waktu_masuk, id_tarif, status, id_user, id_area)
SELECT id_kendaraan, 
       DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 180) MINUTE),
       1, -- id_tarif motor
       'masuk',
       2, -- id_user petugas
       1  -- id_area Area A - Motor
FROM tb_kendaraan 
WHERE jenis_kendaraan = 'motor' 
  AND plat_nomor REGEXP '^(AB|AD|B|D|N|L|W|H|K|AA|AG|Z|F|E|T) 10[0-5][0-9]'
LIMIT 50;

-- Update slot terisi Area A
UPDATE tb_area_parkir SET terisi = 50 WHERE id_area = 1;

-- =============================================
-- AREA B - MOBIL (30 slot)
-- id_area = 2, id_tarif = 2 (mobil)
-- =============================================

INSERT INTO tb_kendaraan (plat_nomor, jenis_kendaraan, warna, pemilik) VALUES
('AB 2001 AA', 'mobil', 'Hitam', NULL),
('AD 2002 BB', 'mobil', 'Putih', NULL),
('B 2003 CC', 'mobil', 'Merah', NULL),
('D 2004 DD', 'mobil', 'Biru', NULL),
('N 2005 EE', 'mobil', 'Silver', NULL),
('L 2006 FF', 'mobil', 'Abu-abu', NULL),
('W 2007 GG', 'mobil', 'Hijau', NULL),
('H 2008 HH', 'mobil', 'Kuning', NULL),
('K 2009 II', 'mobil', 'Orange', NULL),
('AA 2010 JJ', 'mobil', 'Coklat', NULL),
('AG 2011 KK', 'mobil', 'Hitam', NULL),
('Z 2012 LL', 'mobil', 'Putih', NULL),
('F 2013 MM', 'mobil', 'Merah', NULL),
('E 2014 NN', 'mobil', 'Biru', NULL),
('T 2015 OO', 'mobil', 'Silver', NULL),
('AB 2016 PP', 'mobil', 'Abu-abu', NULL),
('AD 2017 QQ', 'mobil', 'Hijau', NULL),
('B 2018 RR', 'mobil', 'Kuning', NULL),
('D 2019 SS', 'mobil', 'Orange', NULL),
('N 2020 TT', 'mobil', 'Coklat', NULL),
('L 2021 UU', 'mobil', 'Hitam', NULL),
('W 2022 VV', 'mobil', 'Putih', NULL),
('H 2023 WW', 'mobil', 'Merah', NULL),
('K 2024 XX', 'mobil', 'Biru', NULL),
('AA 2025 YY', 'mobil', 'Silver', NULL),
('AG 2026 ZZ', 'mobil', 'Abu-abu', NULL),
('Z 2027 AB', 'mobil', 'Hijau', NULL),
('F 2028 CD', 'mobil', 'Kuning', NULL),
('E 2029 EF', 'mobil', 'Orange', NULL),
('T 2030 GH', 'mobil', 'Coklat', NULL);

-- Insert transaksi untuk 30 mobil
INSERT INTO tb_transaksi (id_kendaraan, waktu_masuk, id_tarif, status, id_user, id_area)
SELECT id_kendaraan, 
       DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 180) MINUTE),
       2, -- id_tarif mobil
       'masuk',
       2, -- id_user petugas
       2  -- id_area Area B - Mobil
FROM tb_kendaraan 
WHERE jenis_kendaraan = 'mobil' 
  AND plat_nomor REGEXP '^(AB|AD|B|D|N|L|W|H|K|AA|AG|Z|F|E|T) 20[0-3][0-9]'
LIMIT 30;

-- Update slot terisi Area B
UPDATE tb_area_parkir SET terisi = 30 WHERE id_area = 2;

-- =============================================
-- AREA C - VIP (10 slot)
-- id_area = 3, id_tarif = 2 (mobil - VIP biasanya mobil)
-- =============================================

INSERT INTO tb_kendaraan (plat_nomor, jenis_kendaraan, warna, pemilik) VALUES
('B 3001 VIP', 'mobil', 'Hitam', 'VIP Guest 1'),
('D 3002 VIP', 'mobil', 'Putih', 'VIP Guest 2'),
('AB 3003 VIP', 'mobil', 'Merah', 'VIP Guest 3'),
('AD 3004 VIP', 'mobil', 'Silver', 'VIP Guest 4'),
('N 3005 VIP', 'mobil', 'Biru', 'VIP Guest 5'),
('L 3006 VIP', 'mobil', 'Abu-abu', 'VIP Guest 6'),
('W 3007 VIP', 'mobil', 'Hitam', 'VIP Guest 7'),
('H 3008 VIP', 'mobil', 'Putih', 'VIP Guest 8'),
('K 3009 VIP', 'mobil', 'Silver', 'VIP Guest 9'),
('AA 3010 VIP', 'mobil', 'Hitam', 'VIP Guest 10');

-- Insert transaksi untuk 10 VIP
INSERT INTO tb_transaksi (id_kendaraan, waktu_masuk, id_tarif, status, id_user, id_area)
SELECT id_kendaraan, 
       DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 180) MINUTE),
       2, -- id_tarif mobil
       'masuk',
       2, -- id_user petugas
       3  -- id_area Area C - VIP
FROM tb_kendaraan 
WHERE plat_nomor LIKE '%VIP'
LIMIT 10;

-- Update slot terisi Area C
UPDATE tb_area_parkir SET terisi = 10 WHERE id_area = 3;

-- =============================================
-- VERIFIKASI HASIL
-- =============================================
SELECT 'Area Parkir Status:' AS Info;
SELECT nama_area, kapasitas, terisi, 
       CONCAT(ROUND(terisi/kapasitas*100), '%') AS occupancy 
FROM tb_area_parkir;

SELECT 'Total Kendaraan Terparkir:' AS Info;
SELECT COUNT(*) AS total_terparkir FROM tb_transaksi WHERE status = 'masuk';

SELECT 'Per Jenis Kendaraan:' AS Info;
SELECT k.jenis_kendaraan, COUNT(*) AS jumlah 
FROM tb_transaksi t 
JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan 
WHERE t.status = 'masuk' 
GROUP BY k.jenis_kendaraan;
