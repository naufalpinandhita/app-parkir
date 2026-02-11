<?php
/**
 * =============================================
 * API: LOOKUP KENDARAAN BY PLAT NOMOR
 * =============================================
 * AJAX endpoint untuk mencari data kendaraan berdasarkan plat nomor
 * Return JSON
 */
require_once __DIR__ . '/../../includes/auth_check.php';
requireRole(['petugas']);

header('Content-Type: application/json');

$plat_nomor = strtoupper(trim($_GET['plat'] ?? ''));

if (empty($plat_nomor)) {
    echo json_encode(['found' => false, 'message' => 'Plat nomor tidak boleh kosong']);
    exit;
}

$plat_nomor = mysqli_real_escape_string($conn, $plat_nomor);

$query = "SELECT k.*, t.tarif_per_jam, t.id_tarif 
          FROM tb_kendaraan k
          LEFT JOIN tb_tarif t ON k.jenis_kendaraan = t.jenis_kendaraan
          WHERE k.plat_nomor = '$plat_nomor'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    $kendaraan = mysqli_fetch_assoc($result);
    echo json_encode([
        'found' => true,
        'id_kendaraan' => $kendaraan['id_kendaraan'],
        'plat_nomor' => $kendaraan['plat_nomor'],
        'jenis_kendaraan' => $kendaraan['jenis_kendaraan'],
        'warna' => $kendaraan['warna'],
        'pemilik' => $kendaraan['pemilik'],
        'status_parkir' => $kendaraan['status_parkir'],
        'id_tarif' => $kendaraan['id_tarif'],
        'tarif_per_jam' => $kendaraan['tarif_per_jam']
    ]);
} else {
    echo json_encode([
        'found' => false, 
        'message' => 'Kendaraan belum terdaftar! Hubungi admin untuk mendaftarkan kendaraan terlebih dahulu.'
    ]);
}
