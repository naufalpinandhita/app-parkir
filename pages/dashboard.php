<?php
/**
 * =============================================
 * HALAMAN DASHBOARD
 * =============================================
 */
require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'Dashboard - ' . APP_NAME;
$userRole = currentUser('role');

// Query statistik
$totalKendaraan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_kendaraan"))['total'];
$kendaraanParkir = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_transaksi WHERE status = 'masuk'"))['total'];
$transaksiHariIni = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_transaksi WHERE DATE(waktu_masuk) = CURDATE()"))['total'];
$pendapatanHariIni = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(biaya_total), 0) as total FROM tb_transaksi WHERE DATE(waktu_keluar) = CURDATE() AND status = 'keluar'"))['total'];

// Query transaksi terbaru
$recentQuery = "SELECT t.*, k.plat_nomor, k.jenis_kendaraan, a.nama_area 
                FROM tb_transaksi t 
                JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan 
                JOIN tb_area_parkir a ON t.id_area = a.id_area 
                ORDER BY t.waktu_masuk DESC LIMIT 5";
$recentTransaksi = mysqli_query($conn, $recentQuery);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Dashboard</h4>
                <small class="text-muted">Selamat datang, <?= escape(currentUser('nama_lengkap')) ?>!</small>
            </div>
            <span class="badge bg-primary fs-6"><?= date('l, d F Y') ?></span>
        </div>
        
        <?= getFlash() ?>
        
        <!-- Statistik Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                            <i class="bi bi-car-front"></i>
                        </div>
                        <div>
                            <div class="stat-value text-primary"><?= $totalKendaraan ?></div>
                            <div class="stat-label">Total Kendaraan</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                            <i class="bi bi-p-circle"></i>
                        </div>
                        <div>
                            <div class="stat-value text-success"><?= $kendaraanParkir ?></div>
                            <div class="stat-label">Sedang Parkir</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <div>
                            <div class="stat-value text-info"><?= $transaksiHariIni ?></div>
                            <div class="stat-label">Transaksi Hari Ini</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                            <i class="bi bi-cash"></i>
                        </div>
                        <div>
                            <div class="stat-value text-warning"><?= formatRupiah($pendapatanHariIni) ?></div>
                            <div class="stat-label">Pendapatan Hari Ini</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Transaksi Terbaru -->
        <div class="table-container">
            <h5 class="mb-3"><i class="bi bi-clock-history me-2"></i>Transaksi Terbaru</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Plat Nomor</th>
                            <th>Jenis</th>
                            <th>Area</th>
                            <th>Waktu Masuk</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($recentTransaksi) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($recentTransaksi)): ?>
                            <tr>
                                <td><strong><?= escape($row['plat_nomor']) ?></strong></td>
                                <td><?= escape($row['jenis_kendaraan']) ?></td>
                                <td><?= escape($row['nama_area']) ?></td>
                                <td><?= formatTanggal($row['waktu_masuk'], true) ?></td>
                                <td>
                                    <?php if ($row['status'] == 'masuk'): ?>
                                        <span class="badge bg-success">Parkir</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Keluar</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada transaksi</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
