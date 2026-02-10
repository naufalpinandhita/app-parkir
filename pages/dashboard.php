<?php
/**
 * =============================================
 * HALAMAN DASHBOARD
 * =============================================
 */
require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'Dashboard';
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
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/../includes/navbar.php';
?>

    <div class="main-content">
        <!-- Welcome Section -->
        <div class="mb-4">
            <h2 class="fw-bold mb-2" style="font-size: 28px;">Selamat Datang, <?= escape(currentUser('nama_lengkap')) ?>!</h2>
            <p class="text-muted mb-0"><?= date('l, d F Y') ?></p>
        </div>
        
        <?= getFlash() ?>
        
        <!-- Statistik Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="stat-content">
                            <div class="stat-value"><?= $totalKendaraan ?></div>
                            <div class="stat-label">TOTAL KENDARAAN</div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-car-front"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-xl-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="stat-content">
                            <div class="stat-value"><?= $kendaraanParkir ?></div>
                            <div class="stat-label">SEDANG PARKIR</div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-p-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-xl-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="stat-content">
                            <div class="stat-value"><?= $transaksiHariIni ?></div>
                            <div class="stat-label">TRANSAKSI HARI INI</div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-receipt"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-xl-3">
                <div class="card stat-card stat-card-emphasized">
                    <div class="card-body">
                        <div class="stat-content">
                            <div class="stat-value" style="font-size: 24px;"><?= formatRupiah($pendapatanHariIni) ?></div>
                            <div class="stat-label">PENDAPATAN HARI INI</div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-cash"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Transaksi Terbaru -->
        <div class="table-container">
            <h5 class="mb-3 fw-semibold">
                <i class="bi bi-clock-history me-2"></i>Transaksi Terbaru
            </h5>
            <div class="table-responsive">
                <table class="table">
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
                                        <span class="badge badge-dark">Parkir</span>
                                    <?php else: ?>
                                        <span class="badge badge-light">Selesai</span>
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
