<?php
/**
 * =============================================
 * CETAK STRUK PARKIR - PETUGAS
 * =============================================
 */
require_once __DIR__ . '/../../includes/auth_check.php';
requireRole(['petugas']);

$pageTitle = 'Cetak Struk - ' . APP_NAME;

// Ambil ID transaksi
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$id) {
    setFlash('danger', 'ID Transaksi tidak valid!');
    redirect('pages/petugas/transaksi.php');
}

// Ambil data transaksi lengkap
$query = "SELECT t.*, 
                 k.plat_nomor, k.jenis_kendaraan, k.warna, k.pemilik,
                 a.nama_area,
                 tf.tarif_per_jam,
                 u.nama_lengkap as petugas
          FROM tb_transaksi t 
          JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan 
          JOIN tb_area_parkir a ON t.id_area = a.id_area 
          JOIN tb_tarif tf ON t.id_tarif = tf.id_tarif 
          JOIN tb_user u ON t.id_user = u.id_user
          WHERE t.id_parkir = $id";
$transaksi = mysqli_fetch_assoc(mysqli_query($conn, $query));

if (!$transaksi) {
    setFlash('danger', 'Transaksi tidak ditemukan!');
    redirect('pages/petugas/transaksi.php');
}

// Generate nomor tiket
$nomorTiket = 'PKR-' . str_pad($transaksi['id_parkir'], 6, '0', STR_PAD_LEFT);

// Hitung durasi jika sudah keluar
$durasi = 0;
$biaya = 0;
if ($transaksi['status'] == 'keluar') {
    $durasi = $transaksi['durasi_jam'];
    $biaya = $transaksi['biaya_total'];
} else {
    $durasi = hitungDurasi($transaksi['waktu_masuk'], date('Y-m-d H:i:s'));
    $biaya = $durasi * $transaksi['tarif_per_jam'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Parkir - <?= $nomorTiket ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
            .struk-wrapper { box-shadow: none !important; }
        }
        
        body {
            background: #f0f0f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .struk-wrapper {
            background: white;
            width: 320px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            font-family: 'Courier New', monospace;
        }
        
        .struk-header {
            text-align: center;
            border-bottom: 2px dashed #333;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        
        .struk-header h4 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .struk-header p {
            margin: 5px 0 0;
            font-size: 11px;
            color: #666;
        }
        
        .struk-body {
            font-size: 12px;
        }
        
        .struk-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .struk-row .label {
            color: #666;
        }
        
        .struk-row .value {
            font-weight: bold;
            text-align: right;
        }
        
        .struk-divider {
            border-top: 1px dashed #ccc;
            margin: 10px 0;
        }
        
        .struk-total {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin: 15px 0;
            text-align: center;
        }
        
        .struk-total .amount {
            font-size: 24px;
            font-weight: bold;
            color: #198754;
        }
        
        .struk-footer {
            text-align: center;
            border-top: 2px dashed #333;
            padding-top: 15px;
            margin-top: 15px;
            font-size: 11px;
            color: #666;
        }
        
        .struk-qr {
            text-align: center;
            margin: 15px 0;
        }
        
        .struk-qr svg {
            width: 80px;
            height: 80px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-masuk {
            background: #d4edda;
            color: #155724;
        }
        
        .status-keluar {
            background: #cce5ff;
            color: #004085;
        }
        
        .btn-group-struk {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div>
        <div class="struk-wrapper">
            <!-- Header -->
            <div class="struk-header">
                <h4><i class="bi bi-car-front-fill"></i> PARKIR</h4>
                <p><?= APP_NAME ?></p>
                <p>Jl. Contoh Alamat No. 123</p>
            </div>
            
            <!-- Nomor Tiket -->
            <div class="text-center mb-3">
                <small class="text-muted">No. Tiket</small>
                <h5 class="mb-0"><?= $nomorTiket ?></h5>
                <span class="status-badge status-<?= $transaksi['status'] ?>">
                    <?= $transaksi['status'] == 'masuk' ? 'SEDANG PARKIR' : 'SELESAI' ?>
                </span>
            </div>
            
            <div class="struk-divider"></div>
            
            <!-- Info Kendaraan -->
            <div class="struk-body">
                <div class="struk-row">
                    <span class="label">Plat Nomor</span>
                    <span class="value"><?= escape($transaksi['plat_nomor']) ?></span>
                </div>
                <div class="struk-row">
                    <span class="label">Jenis</span>
                    <span class="value"><?= ucfirst($transaksi['jenis_kendaraan']) ?></span>
                </div>
                <div class="struk-row">
                    <span class="label">Warna</span>
                    <span class="value"><?= escape($transaksi['warna']) ?: '-' ?></span>
                </div>
                <div class="struk-row">
                    <span class="label">Area</span>
                    <span class="value"><?= escape($transaksi['nama_area']) ?></span>
                </div>
                
                <div class="struk-divider"></div>
                
                <div class="struk-row">
                    <span class="label">Waktu Masuk</span>
                    <span class="value"><?= date('d/m/Y H:i', strtotime($transaksi['waktu_masuk'])) ?></span>
                </div>
                
                <?php if ($transaksi['status'] == 'keluar'): ?>
                <div class="struk-row">
                    <span class="label">Waktu Keluar</span>
                    <span class="value"><?= date('d/m/Y H:i', strtotime($transaksi['waktu_keluar'])) ?></span>
                </div>
                <?php endif; ?>
                
                <div class="struk-row">
                    <span class="label">Durasi</span>
                    <span class="value"><?= $durasi ?> Jam</span>
                </div>
                
                <div class="struk-row">
                    <span class="label">Tarif</span>
                    <span class="value"><?= formatRupiah($transaksi['tarif_per_jam']) ?>/jam</span>
                </div>
            </div>
            
            <!-- Total -->
            <div class="struk-total">
                <small class="text-muted d-block">
                    <?= $transaksi['status'] == 'masuk' ? 'ESTIMASI BIAYA' : 'TOTAL BIAYA' ?>
                </small>
                <span class="amount"><?= formatRupiah($biaya) ?></span>
            </div>
            
            <!-- Footer -->
            <div class="struk-footer">
                <p class="mb-1">Petugas: <?= escape($transaksi['petugas']) ?></p>
                <p class="mb-1">Dicetak: <?= date('d/m/Y H:i:s') ?></p>
                <p class="mb-0 mt-2"><strong>Terima Kasih</strong></p>
                <p class="mb-0">Simpan struk ini sebagai bukti parkir</p>
            </div>
        </div>
        
        <!-- Tombol Aksi -->
        <div class="btn-group-struk no-print">
            <button onclick="window.print()" class="btn btn-primary flex-fill">
                <i class="bi bi-printer me-1"></i>Cetak
            </button>
            <a href="<?= APP_URL ?>/pages/petugas/transaksi.php" class="btn btn-secondary flex-fill">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
