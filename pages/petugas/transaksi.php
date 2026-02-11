<?php
/**
 * =============================================
 * TRANSAKSI PARKIR - PETUGAS (VIP MODE)
 * =============================================
 * Sistem toggle: input plat → auto masuk/keluar
 * Petugas hanya bisa mengelola area sendiri
 */
require_once __DIR__ . '/../../includes/auth_check.php';
requireRole(['petugas']);

$pageTitle = 'Transaksi Parkir';
$error = '';

// Cek apakah petugas sudah di-assign area
$id_area_petugas = $_SESSION['id_area'] ?? null;
$nama_area_petugas = $_SESSION['nama_area'] ?? '';

if (!$id_area_petugas) {
    $error = 'Anda belum di-assign ke area parkir manapun. Hubungi admin untuk assign area.';
}

// Ambil data area petugas
$areaPetugas = null;
if ($id_area_petugas) {
    $areaPetugas = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tb_area_parkir WHERE id_area = $id_area_petugas"));
}

// === PROSES TOGGLE PARKIR ===
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action_toggle']) && $id_area_petugas) {
    $plat_nomor = strtoupper(mysqli_real_escape_string($conn, trim($_POST['plat_nomor'])));
    
    // Cari kendaraan
    $checkKendaraan = mysqli_query($conn, "SELECT * FROM tb_kendaraan WHERE plat_nomor = '$plat_nomor'");
    
    if (mysqli_num_rows($checkKendaraan) == 0) {
        $error = 'Kendaraan dengan plat nomor "' . htmlspecialchars($plat_nomor) . '" belum terdaftar! Hubungi admin untuk mendaftarkan kendaraan terlebih dahulu.';
    } else {
        $kendaraan = mysqli_fetch_assoc($checkKendaraan);
        $id_kendaraan = $kendaraan['id_kendaraan'];
        
        if ($kendaraan['status_parkir'] == 'tidak_parkir') {
            // === PROSES MASUK ===
            // Cek kapasitas area
            if ($areaPetugas && $areaPetugas['terisi'] >= $areaPetugas['kapasitas']) {
                $error = 'Area parkir ' . $nama_area_petugas . ' sudah penuh!';
            } else {
                // Cari tarif berdasarkan jenis kendaraan
                $tarifQuery = mysqli_query($conn, "SELECT id_tarif, tarif_per_jam FROM tb_tarif WHERE jenis_kendaraan = '{$kendaraan['jenis_kendaraan']}'");
                $tarif = mysqli_fetch_assoc($tarifQuery);
                
                if (!$tarif) {
                    $error = 'Tarif untuk jenis kendaraan "' . $kendaraan['jenis_kendaraan'] . '" belum diatur!';
                } else {
                    $waktu_masuk = date('Y-m-d H:i:s');
                    $id_tarif = $tarif['id_tarif'];
                    
                    // Insert transaksi
                    $insertQuery = "INSERT INTO tb_transaksi (id_kendaraan, waktu_masuk, id_tarif, id_user, id_area, status) 
                                    VALUES ($id_kendaraan, '$waktu_masuk', $id_tarif, {$_SESSION['user_id']}, $id_area_petugas, 'masuk')";
                    
                    if (mysqli_query($conn, $insertQuery)) {
                        $id_parkir = mysqli_insert_id($conn);
                        
                        // Update status kendaraan
                        mysqli_query($conn, "UPDATE tb_kendaraan SET status_parkir = 'parkir' WHERE id_kendaraan = $id_kendaraan");
                        
                        // Update slot terisi
                        mysqli_query($conn, "UPDATE tb_area_parkir SET terisi = terisi + 1 WHERE id_area = $id_area_petugas");
                        
                        // Log
                        mysqli_query($conn, "INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES ({$_SESSION['user_id']}, 'Parkir masuk: $plat_nomor di $nama_area_petugas')");
                        
                        setFlash('success', '<i class="bi bi-box-arrow-in-right me-1"></i> Kendaraan <strong>' . $plat_nomor . '</strong> berhasil masuk parkir! <a href="' . APP_URL . '/pages/petugas/struk.php?id=' . $id_parkir . '" class="alert-link">Cetak Struk</a>');
                        redirect('pages/petugas/transaksi.php');
                    } else {
                        $error = 'Gagal menyimpan transaksi: ' . mysqli_error($conn);
                    }
                }
            }
        } else {
            // === PROSES KELUAR ===
            // Cari transaksi aktif untuk kendaraan ini
            $transaksiQuery = mysqli_query($conn, 
                "SELECT t.*, tf.tarif_per_jam 
                 FROM tb_transaksi t 
                 JOIN tb_tarif tf ON t.id_tarif = tf.id_tarif 
                 WHERE t.id_kendaraan = $id_kendaraan AND t.status = 'masuk' 
                 ORDER BY t.waktu_masuk DESC LIMIT 1"
            );
            $transaksi = mysqli_fetch_assoc($transaksiQuery);
            
            if (!$transaksi) {
                $error = 'Data transaksi aktif tidak ditemukan!';
            } else {
                $waktu_keluar = date('Y-m-d H:i:s');
                $durasi = hitungDurasi($transaksi['waktu_masuk'], $waktu_keluar);
                $biaya_total = $durasi * $transaksi['tarif_per_jam'];
                $id_parkir = $transaksi['id_parkir'];
                $id_area_transaksi = $transaksi['id_area'];
                
                // Update transaksi
                $updateQuery = "UPDATE tb_transaksi SET 
                                waktu_keluar = '$waktu_keluar', 
                                durasi_jam = $durasi, 
                                biaya_total = $biaya_total, 
                                status = 'keluar' 
                                WHERE id_parkir = $id_parkir";
                
                if (mysqli_query($conn, $updateQuery)) {
                    // Update status kendaraan
                    mysqli_query($conn, "UPDATE tb_kendaraan SET status_parkir = 'tidak_parkir' WHERE id_kendaraan = $id_kendaraan");
                    
                    // Update slot terisi
                    mysqli_query($conn, "UPDATE tb_area_parkir SET terisi = terisi - 1 WHERE id_area = $id_area_transaksi");
                    
                    // Log
                    mysqli_query($conn, "INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES ({$_SESSION['user_id']}, 'Parkir keluar: $plat_nomor - " . formatRupiah($biaya_total) . "')");
                    
                    setFlash('success', '<i class="bi bi-box-arrow-right me-1"></i> Kendaraan <strong>' . $plat_nomor . '</strong> keluar! Durasi: ' . $durasi . ' jam | Biaya: ' . formatRupiah($biaya_total) . ' <a href="' . APP_URL . '/pages/petugas/struk.php?id=' . $id_parkir . '" class="alert-link">Cetak Struk</a>');
                    redirect('pages/petugas/transaksi.php');
                } else {
                    $error = 'Gagal update transaksi: ' . mysqli_error($conn);
                }
            }
        }
    }
}

// Ambil daftar kendaraan yang sedang parkir di area petugas
$parkirAktif = null;
$totalParkirArea = 0;
if ($id_area_petugas) {
    $parkirAktif = mysqli_query($conn, 
        "SELECT t.*, k.plat_nomor, k.jenis_kendaraan, k.warna, k.pemilik, a.nama_area, tf.tarif_per_jam
         FROM tb_transaksi t 
         JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan 
         JOIN tb_area_parkir a ON t.id_area = a.id_area 
         JOIN tb_tarif tf ON t.id_tarif = tf.id_tarif 
         WHERE t.status = 'masuk' AND t.id_area = $id_area_petugas
         ORDER BY t.waktu_masuk DESC"
    );
    $totalParkirArea = mysqli_num_rows($parkirAktif);
}

// Statistik area petugas
$transaksiHariIni = 0;
if ($id_area_petugas) {
    $transaksiHariIni = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT COUNT(*) as total FROM tb_transaksi WHERE DATE(waktu_masuk) = CURDATE() AND id_area = $id_area_petugas"
    ))['total'];
}

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0"><i class="bi bi-receipt me-2"></i>Transaksi Parkir</h4>
                <small class="text-muted">
                    <?php if ($nama_area_petugas): ?>
                        <i class="bi bi-geo-alt me-1"></i><?= escape($nama_area_petugas) ?>
                    <?php else: ?>
                        Area belum di-assign
                    <?php endif; ?>
                </small>
            </div>
            <?php if ($id_area_petugas && $areaPetugas): ?>
            <div class="d-flex gap-2">
                <span class="badge bg-success fs-6 py-2 px-3">
                    <i class="bi bi-car-front me-1"></i>Parkir: <?= $totalParkirArea ?>/<?= $areaPetugas['kapasitas'] ?>
                </span>
                <span class="badge bg-info fs-6 py-2 px-3">
                    <i class="bi bi-calendar me-1"></i>Hari Ini: <?= $transaksiHariIni ?>
                </span>
            </div>
            <?php endif; ?>
        </div>
        
        <?= getFlash() ?>
        <?php if ($error): ?>
        <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($id_area_petugas): ?>
        <div class="row g-4">
            <!-- Form Input Plat Nomor -->
            <div class="col-lg-5">
                <div class="form-container h-100">
                    <h5 class="mb-4 text-primary">
                        <i class="bi bi-search me-2"></i>Input Plat Nomor
                    </h5>
                    
                    <form method="POST" action="" id="formToggle">
                        <input type="hidden" name="action_toggle" value="1">
                        
                        <div class="mb-3">
                            <label for="plat_nomor" class="form-label">Plat Nomor Kendaraan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg text-uppercase" id="plat_nomor" 
                                   name="plat_nomor" placeholder="Contoh: AB 1234 CD" required autofocus
                                   autocomplete="off">
                        </div>
                        
                        <!-- Preview Data Kendaraan -->
                        <div id="preview-kendaraan" class="d-none mb-3">
                            <div class="card border-0 bg-light">
                                <div class="card-body py-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1" id="preview-plat"></h6>
                                            <small class="text-muted">
                                                <span id="preview-jenis"></span> • <span id="preview-warna"></span>
                                            </small>
                                            <br>
                                            <small class="text-muted">Pemilik: <span id="preview-pemilik"></span></small>
                                        </div>
                                        <div id="preview-status-badge"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="preview-error" class="d-none mb-3">
                            <div class="alert alert-warning mb-0 py-2">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                <span id="preview-error-text"></span>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-lg w-100" id="btn-submit" disabled>
                            <i class="bi bi-arrow-right-circle me-2"></i>
                            <span id="btn-text">Masukkan Plat Nomor</span>
                        </button>
                    </form>
                    
                    <div class="mt-4 pt-3 border-top">
                        <small class="text-muted d-block mb-2">
                            <i class="bi bi-info-circle me-1"></i>Cara penggunaan:
                        </small>
                        <ol class="small text-muted mb-0 ps-3">
                            <li>Masukkan plat nomor kendaraan terdaftar</li>
                            <li>Sistem akan otomatis mendeteksi status</li>
                            <li>Jika belum parkir → <strong>Masuk</strong></li>
                            <li>Jika sedang parkir → <strong>Keluar</strong> (biaya dihitung otomatis)</li>
                        </ol>
                    </div>
                </div>
            </div>
            
            <!-- Daftar Parkir Aktif -->
            <div class="col-lg-7">
                <div class="table-container h-100">
                    <h5 class="mb-4 text-primary">
                        <i class="bi bi-car-front me-2"></i>Kendaraan Parkir di <?= escape($nama_area_petugas) ?> (<?= $totalParkirArea ?>)
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
                                <?php if ($parkirAktif && mysqli_num_rows($parkirAktif) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($parkirAktif)): 
                                    $durasiSekarang = hitungDurasi($row['waktu_masuk'], date('Y-m-d H:i:s'));
                                    $estimasiBiaya = $durasiSekarang * $row['tarif_per_jam'];
                                ?>
                                <tr>
                                    <td>
                                        <strong class="text-primary"><?= escape($row['plat_nomor']) ?></strong>
                                        <br><small class="text-muted"><?= escape($row['pemilik'] ?? $row['warna']) ?></small>
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
                                        <form method="POST" action="" class="d-inline" onsubmit="return confirm('Proses parkir keluar untuk <?= escape($row['plat_nomor']) ?>?')">
                                            <input type="hidden" name="action_toggle" value="1">
                                            <input type="hidden" name="plat_nomor" value="<?= escape($row['plat_nomor']) ?>">
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
                                <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                        Tidak ada kendaraan yang sedang parkir di area ini
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// AJAX Lookup saat mengetik plat nomor
let lookupTimer = null;
const platInput = document.getElementById('plat_nomor');
const previewCard = document.getElementById('preview-kendaraan');
const previewError = document.getElementById('preview-error');
const btnSubmit = document.getElementById('btn-submit');
const btnText = document.getElementById('btn-text');

if (platInput) {
    platInput.addEventListener('input', function() {
        clearTimeout(lookupTimer);
        const plat = this.value.trim();
        
        if (plat.length < 2) {
            resetPreview();
            return;
        }
        
        lookupTimer = setTimeout(() => {
            lookupKendaraan(plat);
        }, 500);
    });
}

function lookupKendaraan(plat) {
    fetch('<?= APP_URL ?>/pages/petugas/api_kendaraan.php?plat=' + encodeURIComponent(plat))
        .then(res => res.json())
        .then(data => {
            if (data.found) {
                showPreview(data);
            } else {
                showError(data.message);
            }
        })
        .catch(err => {
            showError('Gagal mencari data kendaraan');
        });
}

function showPreview(data) {
    previewCard.classList.remove('d-none');
    previewError.classList.add('d-none');
    
    document.getElementById('preview-plat').textContent = data.plat_nomor;
    document.getElementById('preview-jenis').textContent = data.jenis_kendaraan.charAt(0).toUpperCase() + data.jenis_kendaraan.slice(1);
    document.getElementById('preview-warna').textContent = data.warna || '-';
    document.getElementById('preview-pemilik').textContent = data.pemilik || '-';
    
    const statusBadge = document.getElementById('preview-status-badge');
    btnSubmit.disabled = false;
    
    if (data.status_parkir === 'parkir') {
        statusBadge.innerHTML = '<span class="badge bg-danger fs-6">Sedang Parkir</span>';
        btnSubmit.className = 'btn btn-danger btn-lg w-100';
        btnText.textContent = 'Proses Keluar';
    } else {
        statusBadge.innerHTML = '<span class="badge bg-success fs-6">Tidak Parkir</span>';
        btnSubmit.className = 'btn btn-success btn-lg w-100';
        btnText.textContent = 'Proses Masuk';
    }
}

function showError(message) {
    previewCard.classList.add('d-none');
    previewError.classList.remove('d-none');
    document.getElementById('preview-error-text').textContent = message;
    btnSubmit.disabled = true;
    btnSubmit.className = 'btn btn-secondary btn-lg w-100';
    btnText.textContent = 'Kendaraan Tidak Ditemukan';
}

function resetPreview() {
    previewCard.classList.add('d-none');
    previewError.classList.add('d-none');
    btnSubmit.disabled = true;
    btnSubmit.className = 'btn btn-secondary btn-lg w-100';
    btnText.textContent = 'Masukkan Plat Nomor';
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
