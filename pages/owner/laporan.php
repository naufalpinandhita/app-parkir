<?php
/**
 * =============================================
 * LAPORAN & REKAP TRANSAKSI - OWNER
 * =============================================
 */
require_once __DIR__ . '/../../includes/auth_check.php';
requireRole(['owner']);

$pageTitle = 'Laporan Transaksi - ' . APP_NAME;

// Filter periode
$filterPeriode = $_GET['periode'] ?? 'hari';
$filterTanggal = $_GET['tanggal'] ?? date('Y-m-d');
$filterBulan = $_GET['bulan'] ?? date('Y-m');
$filterTahun = $_GET['tahun'] ?? date('Y');

// Build where clause berdasarkan periode
switch ($filterPeriode) {
    case 'hari':
        $whereDate = "DATE(t.waktu_masuk) = '$filterTanggal'";
        $labelPeriode = formatTanggal($filterTanggal);
        break;
    case 'bulan':
        $whereDate = "DATE_FORMAT(t.waktu_masuk, '%Y-%m') = '$filterBulan'";
        $labelPeriode = date('F Y', strtotime($filterBulan . '-01'));
        break;
    case 'tahun':
        $whereDate = "YEAR(t.waktu_masuk) = '$filterTahun'";
        $labelPeriode = "Tahun $filterTahun";
        break;
    default:
        $whereDate = "1=1";
        $labelPeriode = "Semua Waktu";
}

// ===== STATISTIK RINGKASAN =====
$statsQuery = "SELECT 
    COUNT(*) as total_transaksi,
    SUM(CASE WHEN status = 'keluar' THEN biaya_total ELSE 0 END) as total_pendapatan,
    SUM(CASE WHEN status = 'masuk' THEN 1 ELSE 0 END) as sedang_parkir,
    SUM(durasi_jam) as total_durasi
    FROM tb_transaksi t WHERE $whereDate";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $statsQuery));

// Transaksi per jenis kendaraan
$perJenisQuery = "SELECT k.jenis_kendaraan, 
    COUNT(*) as jumlah,
    SUM(CASE WHEN t.status = 'keluar' THEN t.biaya_total ELSE 0 END) as pendapatan
    FROM tb_transaksi t 
    JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
    WHERE $whereDate
    GROUP BY k.jenis_kendaraan";
$perJenis = mysqli_query($conn, $perJenisQuery);

// Transaksi per area
$perAreaQuery = "SELECT a.nama_area, 
    COUNT(*) as jumlah,
    SUM(CASE WHEN t.status = 'keluar' THEN t.biaya_total ELSE 0 END) as pendapatan
    FROM tb_transaksi t 
    JOIN tb_area_parkir a ON t.id_area = a.id_area
    WHERE $whereDate
    GROUP BY a.id_area, a.nama_area";
$perArea = mysqli_query($conn, $perAreaQuery);

// ===== DATA TRANSAKSI DETAIL =====
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

$totalData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_transaksi t WHERE $whereDate"))['total'];
$totalPages = ceil($totalData / $limit);

$transaksiQuery = "SELECT t.*, k.plat_nomor, k.jenis_kendaraan, a.nama_area, u.nama_lengkap as petugas
    FROM tb_transaksi t 
    JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan 
    JOIN tb_area_parkir a ON t.id_area = a.id_area
    JOIN tb_user u ON t.id_user = u.id_user
    WHERE $whereDate
    ORDER BY t.waktu_masuk DESC
    LIMIT $limit OFFSET $offset";
$transaksiList = mysqli_query($conn, $transaksiQuery);

// Data untuk chart (7 hari terakhir)
$chartData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dayQuery = "SELECT COALESCE(SUM(biaya_total), 0) as pendapatan FROM tb_transaksi WHERE DATE(waktu_keluar) = '$date' AND status = 'keluar'";
    $dayResult = mysqli_fetch_assoc(mysqli_query($conn, $dayQuery));
    $chartData[] = [
        'tanggal' => date('d/m', strtotime($date)),
        'pendapatan' => (int) $dayResult['pendapatan']
    ];
}

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Laporan Transaksi</h4>
                <small class="text-muted">Rekap dan analisis pendapatan parkir</small>
            </div>
            <button class="btn btn-outline-primary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Cetak Laporan
            </button>
        </div>
        
        <?= getFlash() ?>
        
        <!-- Filter Periode -->
        <div class="table-container mb-4 no-print">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">Periode</label>
                    <select name="periode" class="form-select" onchange="togglePeriodeInput(this.value)">
                        <option value="hari" <?= $filterPeriode == 'hari' ? 'selected' : '' ?>>Harian</option>
                        <option value="bulan" <?= $filterPeriode == 'bulan' ? 'selected' : '' ?>>Bulanan</option>
                        <option value="tahun" <?= $filterPeriode == 'tahun' ? 'selected' : '' ?>>Tahunan</option>
                    </select>
                </div>
                <div class="col-md-3" id="input-hari" style="<?= $filterPeriode != 'hari' ? 'display:none' : '' ?>">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= $filterTanggal ?>">
                </div>
                <div class="col-md-3" id="input-bulan" style="<?= $filterPeriode != 'bulan' ? 'display:none' : '' ?>">
                    <label class="form-label">Bulan</label>
                    <input type="month" name="bulan" class="form-control" value="<?= $filterBulan ?>">
                </div>
                <div class="col-md-3" id="input-tahun" style="<?= $filterPeriode != 'tahun' ? 'display:none' : '' ?>">
                    <label class="form-label">Tahun</label>
                    <select name="tahun" class="form-select">
                        <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                        <option value="<?= $y ?>" <?= $filterTahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Label Periode -->
        <div class="alert alert-info mb-4">
            <i class="bi bi-calendar3 me-2"></i>
            Menampilkan data untuk: <strong><?= $labelPeriode ?></strong>
        </div>
        
        <!-- Statistik Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card stat-card border-0 bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stat-label opacity-75">Total Transaksi</div>
                                <div class="stat-value"><?= number_format($stats['total_transaksi']) ?></div>
                            </div>
                            <div class="stat-icon bg-white bg-opacity-25">
                                <i class="bi bi-receipt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card stat-card border-0 bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stat-label opacity-75">Total Pendapatan</div>
                                <div class="stat-value" style="font-size: 1.5rem;"><?= formatRupiah($stats['total_pendapatan'] ?? 0) ?></div>
                            </div>
                            <div class="stat-icon bg-white bg-opacity-25">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card stat-card border-0 bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stat-label opacity-75">Sedang Parkir</div>
                                <div class="stat-value"><?= number_format($stats['sedang_parkir'] ?? 0) ?></div>
                            </div>
                            <div class="stat-icon bg-white bg-opacity-25">
                                <i class="bi bi-car-front"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card stat-card border-0 bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stat-label opacity-75">Total Durasi</div>
                                <div class="stat-value"><?= number_format($stats['total_durasi'] ?? 0) ?> <small>jam</small></div>
                            </div>
                            <div class="stat-icon bg-white bg-opacity-25">
                                <i class="bi bi-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Grafik & Breakdown -->
        <div class="row g-4 mb-4">
            <!-- Chart Pendapatan 7 Hari -->
            <div class="col-lg-8">
                <div class="table-container">
                    <h6 class="mb-3"><i class="bi bi-graph-up me-2"></i>Pendapatan 7 Hari Terakhir</h6>
                    <div style="height: 250px; position: relative;">
                        <canvas id="chartPendapatan"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Breakdown per Jenis -->
            <div class="col-lg-4">
                <div class="table-container h-100">
                    <h6 class="mb-3"><i class="bi bi-pie-chart me-2"></i>Per Jenis Kendaraan</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Jenis</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalJumlah = 0;
                            $totalPendapatan = 0;
                            while ($row = mysqli_fetch_assoc($perJenis)): 
                                $totalJumlah += $row['jumlah'];
                                $totalPendapatan += $row['pendapatan'];
                            ?>
                            <tr>
                                <td>
                                    <?php
                                    $icon = match($row['jenis_kendaraan']) {
                                        'motor' => 'bi-bicycle',
                                        'mobil' => 'bi-car-front',
                                        default => 'bi-truck'
                                    };
                                    ?>
                                    <i class="bi <?= $icon ?> me-1"></i><?= ucfirst($row['jenis_kendaraan']) ?>
                                </td>
                                <td class="text-center"><?= $row['jumlah'] ?></td>
                                <td class="text-end text-success"><?= formatRupiah($row['pendapatan']) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td>Total</td>
                                <td class="text-center"><?= $totalJumlah ?></td>
                                <td class="text-end text-success"><?= formatRupiah($totalPendapatan) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <h6 class="mb-3 mt-4"><i class="bi bi-geo-alt me-2"></i>Per Area Parkir</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Area</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($perArea)): ?>
                            <tr>
                                <td><?= escape($row['nama_area']) ?></td>
                                <td class="text-center"><?= $row['jumlah'] ?></td>
                                <td class="text-end text-success"><?= formatRupiah($row['pendapatan']) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Tabel Detail Transaksi -->
        <div class="table-container">
            <h6 class="mb-3"><i class="bi bi-list-ul me-2"></i>Detail Transaksi (<?= number_format($totalData) ?>)</h6>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Plat Nomor</th>
                            <th>Jenis</th>
                            <th>Area</th>
                            <th>Waktu Masuk</th>
                            <th>Waktu Keluar</th>
                            <th>Durasi</th>
                            <th>Biaya</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($transaksiList)): ?>
                        <tr>
                            <td><small class="text-muted">#<?= $row['id_parkir'] ?></small></td>
                            <td><strong><?= escape($row['plat_nomor']) ?></strong></td>
                            <td><?= ucfirst($row['jenis_kendaraan']) ?></td>
                            <td><small><?= escape($row['nama_area']) ?></small></td>
                            <td><small><?= date('d/m H:i', strtotime($row['waktu_masuk'])) ?></small></td>
                            <td><small><?= $row['waktu_keluar'] ? date('d/m H:i', strtotime($row['waktu_keluar'])) : '-' ?></small></td>
                            <td><?= $row['durasi_jam'] ?: '-' ?> jam</td>
                            <td class="text-success fw-bold"><?= $row['biaya_total'] ? formatRupiah($row['biaya_total']) : '-' ?></td>
                            <td>
                                <?php if ($row['status'] == 'masuk'): ?>
                                    <span class="badge bg-success">Parkir</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Selesai</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if ($totalData == 0): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Tidak ada data transaksi</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-3 no-print">
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <?php
                    $queryParams = $_GET;
                    unset($queryParams['page']);
                    $queryString = http_build_query($queryParams);
                    ?>
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&<?= $queryString ?>">«</a>
                    </li>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&<?= $queryString ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&<?= $queryString ?>">»</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Toggle input periode
function togglePeriodeInput(value) {
    document.getElementById('input-hari').style.display = value === 'hari' ? 'block' : 'none';
    document.getElementById('input-bulan').style.display = value === 'bulan' ? 'block' : 'none';
    document.getElementById('input-tahun').style.display = value === 'tahun' ? 'block' : 'none';
}

// Chart Pendapatan
const ctx = document.getElementById('chartPendapatan').getContext('2d');
const chartData = <?= json_encode($chartData) ?>;

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: chartData.map(d => d.tanggal),
        datasets: [{
            label: 'Pendapatan (Rp)',
            data: chartData.map(d => d.pendapatan),
            backgroundColor: 'rgba(25, 135, 84, 0.7)',
            borderColor: 'rgba(25, 135, 84, 1)',
            borderWidth: 1,
            borderRadius: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                    }
                }
            }
        }
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
