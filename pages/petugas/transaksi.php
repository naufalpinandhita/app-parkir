<?php
/**
 * =============================================
 * TRANSAKSI PARKIR - PETUGAS
 * =============================================
 */
require_once __DIR__ . '/../../includes/auth_check.php';
requireRole(['petugas']);

$pageTitle = 'Transaksi Parkir - ' . APP_NAME;

// Handle Actions
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$error = '';

// Ambil data untuk dropdown
$tarifList = mysqli_query($conn, "SELECT * FROM tb_tarif ORDER BY jenis_kendaraan");
$areaList = mysqli_query($conn, "SELECT * FROM tb_area_parkir WHERE kapasitas > terisi ORDER BY nama_area");

// === PROSES PARKIR MASUK ===
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action_masuk'])) {
    $plat_nomor = strtoupper(mysqli_real_escape_string($conn, $_POST['plat_nomor']));
    $jenis_kendaraan = mysqli_real_escape_string($conn, $_POST['jenis_kendaraan']);
    $warna = mysqli_real_escape_string($conn, $_POST['warna']);
    $id_area = (int) $_POST['id_area'];
    $id_tarif = (int) $_POST['id_tarif'];
    
    // Cek apakah kendaraan sudah ada di database
    $checkKendaraan = mysqli_query($conn, "SELECT id_kendaraan FROM tb_kendaraan WHERE plat_nomor = '$plat_nomor'");
    
    if (mysqli_num_rows($checkKendaraan) > 0) {
        $kendaraan = mysqli_fetch_assoc($checkKendaraan);
        $id_kendaraan = $kendaraan['id_kendaraan'];
        
        // Update jenis & warna jika berbeda
        mysqli_query($conn, "UPDATE tb_kendaraan SET jenis_kendaraan = '$jenis_kendaraan', warna = '$warna' WHERE id_kendaraan = $id_kendaraan");
    } else {
        // Insert kendaraan baru
        $insertKendaraan = "INSERT INTO tb_kendaraan (plat_nomor, jenis_kendaraan, warna, id_user) 
                            VALUES ('$plat_nomor', '$jenis_kendaraan', '$warna', {$_SESSION['user_id']})";
        mysqli_query($conn, $insertKendaraan);
        $id_kendaraan = mysqli_insert_id($conn);
    }
    
    // Cek apakah kendaraan sedang parkir
    $checkParkir = mysqli_query($conn, "SELECT id_parkir FROM tb_transaksi WHERE id_kendaraan = $id_kendaraan AND status = 'masuk'");
    if (mysqli_num_rows($checkParkir) > 0) {
        $error = 'Kendaraan dengan plat nomor ini masih dalam status parkir!';
    } else {
        // Insert transaksi
        $waktu_masuk = date('Y-m-d H:i:s');
        $insertTransaksi = "INSERT INTO tb_transaksi (id_kendaraan, waktu_masuk, id_tarif, id_user, id_area, status) 
                            VALUES ($id_kendaraan, '$waktu_masuk', $id_tarif, {$_SESSION['user_id']}, $id_area, 'masuk')";
        
        if (mysqli_query($conn, $insertTransaksi)) {
            $id_parkir = mysqli_insert_id($conn);
            
            // Update slot terisi di area
            mysqli_query($conn, "UPDATE tb_area_parkir SET terisi = terisi + 1 WHERE id_area = $id_area");
            
            // Log aktivitas
            $logQuery = "INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES ({$_SESSION['user_id']}, 'Parkir masuk: $plat_nomor')";
            mysqli_query($conn, $logQuery);
            
            setFlash('success', 'Kendaraan berhasil masuk parkir! <a href="' . APP_URL . '/pages/petugas/struk.php?id=' . $id_parkir . '" class="alert-link">Cetak Struk</a>');
            redirect('pages/petugas/transaksi.php');
        } else {
            $error = 'Gagal menyimpan transaksi: ' . mysqli_error($conn);
        }
    }
}

// === PROSES PARKIR KELUAR ===
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action_keluar'])) {
    $id_parkir = (int) $_POST['id_parkir'];
    
    // Ambil data transaksi
    $transaksi = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT t.*, k.plat_nomor, tf.tarif_per_jam 
         FROM tb_transaksi t 
         JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan 
         JOIN tb_tarif tf ON t.id_tarif = tf.id_tarif 
         WHERE t.id_parkir = $id_parkir AND t.status = 'masuk'"
    ));
    
    if ($transaksi) {
        $waktu_keluar = date('Y-m-d H:i:s');
        $durasi = hitungDurasi($transaksi['waktu_masuk'], $waktu_keluar);
        $biaya_total = $durasi * $transaksi['tarif_per_jam'];
        
        // Update transaksi
        $updateQuery = "UPDATE tb_transaksi SET 
                        waktu_keluar = '$waktu_keluar', 
                        durasi_jam = $durasi, 
                        biaya_total = $biaya_total, 
                        status = 'keluar' 
                        WHERE id_parkir = $id_parkir";
        
        if (mysqli_query($conn, $updateQuery)) {
            // Update slot terisi di area
            mysqli_query($conn, "UPDATE tb_area_parkir SET terisi = terisi - 1 WHERE id_area = {$transaksi['id_area']}");
            
            // Log aktivitas
            $logQuery = "INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES ({$_SESSION['user_id']}, 'Parkir keluar: {$transaksi['plat_nomor']} - " . formatRupiah($biaya_total) . "')";
            mysqli_query($conn, $logQuery);
            
            setFlash('success', 'Kendaraan keluar! Biaya: ' . formatRupiah($biaya_total) . ' <a href="' . APP_URL . '/pages/petugas/struk.php?id=' . $id_parkir . '" class="alert-link">Cetak Struk</a>');
            redirect('pages/petugas/transaksi.php');
        } else {
            $error = 'Gagal update transaksi: ' . mysqli_error($conn);
        }
    } else {
        $error = 'Transaksi tidak ditemukan!';
    }
}

// Ambil daftar kendaraan yang sedang parkir
$parkirAktif = mysqli_query($conn, 
    "SELECT t.*, k.plat_nomor, k.jenis_kendaraan, k.warna, a.nama_area, tf.tarif_per_jam
     FROM tb_transaksi t 
     JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan 
     JOIN tb_area_parkir a ON t.id_area = a.id_area 
     JOIN tb_tarif tf ON t.id_tarif = tf.id_tarif 
     WHERE t.status = 'masuk' 
     ORDER BY t.waktu_masuk DESC"
);

// Statistik
$totalParkir = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_transaksi WHERE status = 'masuk'"))['total'];
$transaksiHariIni = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_transaksi WHERE DATE(waktu_masuk) = CURDATE()"))['total'];

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0"><i class="bi bi-receipt me-2"></i>Transaksi Parkir</h4>
                <small class="text-muted">Kelola parkir masuk dan keluar</small>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-success fs-6 py-2 px-3">
                    <i class="bi bi-car-front me-1"></i>Parkir: <?= $totalParkir ?>
                </span>
                <span class="badge bg-info fs-6 py-2 px-3">
                    <i class="bi bi-calendar me-1"></i>Hari Ini: <?= $transaksiHariIni ?>
                </span>
            </div>
        </div>
        
        <?= getFlash() ?>
        <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="row g-4">
            <!-- Form Parkir Masuk -->
            <div class="col-lg-5">
                <div class="form-container h-100">
                    <h5 class="mb-4 text-success">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Parkir Masuk
                    </h5>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="action_masuk" value="1">
                        
                        <div class="mb-3">
                            <label for="plat_nomor" class="form-label">Plat Nomor <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg text-uppercase" id="plat_nomor" 
                                   name="plat_nomor" placeholder="AB 1234 CD" required autofocus>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="jenis_kendaraan" class="form-label">Jenis <span class="text-danger">*</span></label>
                                <select class="form-select" id="jenis_kendaraan" name="jenis_kendaraan" required onchange="updateTarif()">
                                    <option value="">-- Pilih --</option>
                                    <option value="motor">Motor</option>
                                    <option value="mobil">Mobil</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="warna" class="form-label">Warna</label>
                                <input type="text" class="form-control" id="warna" name="warna" placeholder="Hitam">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="id_area" class="form-label">Area Parkir <span class="text-danger">*</span></label>
                            <select class="form-select" id="id_area" name="id_area" required>
                                <option value="">-- Pilih Area --</option>
                                <?php 
                                mysqli_data_seek($areaList, 0);
                                while ($area = mysqli_fetch_assoc($areaList)): 
                                    $sisa = $area['kapasitas'] - $area['terisi'];
                                ?>
                                <option value="<?= $area['id_area'] ?>">
                                    <?= escape($area['nama_area']) ?> (Tersedia: <?= $sisa ?>)
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label for="id_tarif" class="form-label">Tarif <span class="text-danger">*</span></label>
                            <select class="form-select" id="id_tarif" name="id_tarif" required>
                                <option value="">-- Pilih Tarif --</option>
                                <?php 
                                mysqli_data_seek($tarifList, 0);
                                while ($tarif = mysqli_fetch_assoc($tarifList)): 
                                ?>
                                <option value="<?= $tarif['id_tarif'] ?>" data-jenis="<?= $tarif['jenis_kendaraan'] ?>">
                                    <?= ucfirst($tarif['jenis_kendaraan']) ?> - <?= formatRupiah($tarif['tarif_per_jam']) ?>/jam
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-lg w-100">
                            <i class="bi bi-check-lg me-2"></i>Masuk Parkir
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Daftar Parkir Aktif -->
            <div class="col-lg-7">
                <div class="table-container h-100">
                    <h5 class="mb-4 text-primary">
                        <i class="bi bi-car-front me-2"></i>Kendaraan Sedang Parkir (<?= mysqli_num_rows($parkirAktif) ?>)
                    </h5>
                    
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-hover align-middle">
                            <thead class="sticky-top bg-light">
                                <tr>
                                    <th>Plat Nomor</th>
                                    <th>Jenis</th>
                                    <th>Waktu Masuk</th>
                                    <th>Durasi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($parkirAktif)): 
                                    $durasiSekarang = hitungDurasi($row['waktu_masuk'], date('Y-m-d H:i:s'));
                                    $estimasiBiaya = $durasiSekarang * $row['tarif_per_jam'];
                                ?>
                                <tr>
                                    <td>
                                        <strong class="text-primary"><?= escape($row['plat_nomor']) ?></strong>
                                        <br><small class="text-muted"><?= escape($row['warna']) ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $icon = match($row['jenis_kendaraan']) {
                                            'motor' => 'bi-bicycle',
                                            'mobil' => 'bi-car-front',
                                            default => 'bi-truck'
                                        };
                                        ?>
                                        <i class="bi <?= $icon ?>"></i> <?= ucfirst($row['jenis_kendaraan']) ?>
                                        <br><small class="text-muted"><?= escape($row['nama_area']) ?></small>
                                    </td>
                                    <td>
                                        <?= date('H:i', strtotime($row['waktu_masuk'])) ?>
                                        <br><small class="text-muted"><?= date('d/m/Y', strtotime($row['waktu_masuk'])) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark"><?= $durasiSekarang ?> jam</span>
                                        <br><small class="text-success fw-bold"><?= formatRupiah($estimasiBiaya) ?></small>
                                    </td>
                                    <td>
                                        <form method="POST" action="" class="d-inline" onsubmit="return confirm('Proses parkir keluar?')">
                                            <input type="hidden" name="action_keluar" value="1">
                                            <input type="hidden" name="id_parkir" value="<?= $row['id_parkir'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="bi bi-box-arrow-right"></i> Keluar
                                            </button>
                                        </form>
                                        <a href="<?= APP_URL ?>/pages/petugas/struk.php?id=<?= $row['id_parkir'] ?>" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php if (mysqli_num_rows($parkirAktif) == 0): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                        Tidak ada kendaraan yang sedang parkir
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateTarif() {
    const jenis = document.getElementById('jenis_kendaraan').value;
    const tarifSelect = document.getElementById('id_tarif');
    const options = tarifSelect.querySelectorAll('option');
    
    options.forEach(opt => {
        if (opt.dataset.jenis === jenis) {
            opt.selected = true;
        }
    });
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
