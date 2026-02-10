<?php
/**
 * =============================================
 * CRUD TARIF PARKIR - ADMIN
 * =============================================
 */
require_once __DIR__ . '/../../includes/auth_check.php';
requireRole(['admin']);

$pageTitle = 'Tarif Parkir';

// Handle Actions
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$error = '';

// Proses Form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenis_kendaraan = mysqli_real_escape_string($conn, $_POST['jenis_kendaraan']);
    $tarif_per_jam = (int) str_replace(['.', ','], '', $_POST['tarif_per_jam']);
    
    if ($_POST['form_action'] == 'add') {
        // Cek jenis kendaraan unik
        $check = mysqli_query($conn, "SELECT id_tarif FROM tb_tarif WHERE jenis_kendaraan = '$jenis_kendaraan'");
        if (mysqli_num_rows($check) > 0) {
            $error = 'Tarif untuk jenis kendaraan ini sudah ada!';
        } else {
            $query = "INSERT INTO tb_tarif (jenis_kendaraan, tarif_per_jam) VALUES ('$jenis_kendaraan', $tarif_per_jam)";
            if (mysqli_query($conn, $query)) {
                $logQuery = "INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES ({$_SESSION['user_id']}, 'Menambah tarif: $jenis_kendaraan')";
                mysqli_query($conn, $logQuery);
                
                setFlash('success', 'Tarif berhasil ditambahkan!');
                redirect('pages/admin/tarif.php');
            } else {
                $error = 'Gagal menambah tarif: ' . mysqli_error($conn);
            }
        }
    } elseif ($_POST['form_action'] == 'edit') {
        $id = $_POST['id_tarif'];
        
        $query = "UPDATE tb_tarif SET jenis_kendaraan = '$jenis_kendaraan', tarif_per_jam = $tarif_per_jam WHERE id_tarif = $id";
        if (mysqli_query($conn, $query)) {
            $logQuery = "INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES ({$_SESSION['user_id']}, 'Mengedit tarif: $jenis_kendaraan')";
            mysqli_query($conn, $logQuery);
            
            setFlash('success', 'Tarif berhasil diupdate!');
            redirect('pages/admin/tarif.php');
        } else {
            $error = 'Gagal update tarif: ' . mysqli_error($conn);
        }
    }
}

// Proses Delete
if ($action == 'delete' && $id) {
    $tarif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT jenis_kendaraan FROM tb_tarif WHERE id_tarif = $id"));
    if (mysqli_query($conn, "DELETE FROM tb_tarif WHERE id_tarif = $id")) {
        $logQuery = "INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES ({$_SESSION['user_id']}, 'Menghapus tarif: {$tarif['jenis_kendaraan']}')";
        mysqli_query($conn, $logQuery);
        
        setFlash('success', 'Tarif berhasil dihapus!');
    } else {
        setFlash('danger', 'Gagal menghapus tarif! Mungkin masih digunakan di transaksi.');
    }
    redirect('pages/admin/tarif.php');
}

// Ambil data untuk edit
$tarifData = null;
if ($action == 'edit' && $id) {
    $tarifData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tb_tarif WHERE id_tarif = $id"));
}

// Ambil semua tarif
$tarifList = mysqli_query($conn, "SELECT * FROM tb_tarif ORDER BY id_tarif ASC");

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0"><i class="bi bi-cash me-2"></i>Tarif Parkir</h4>
                <small class="text-muted">Kelola tarif parkir per jenis kendaraan</small>
            </div>
            <?php if ($action == 'list'): ?>
            <a href="?action=add" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Tambah Tarif
            </a>
            <?php else: ?>
            <a href="<?= APP_URL ?>/pages/admin/tarif.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
            <?php endif; ?>
        </div>
        
        <?= getFlash() ?>
        
        <?php if ($action == 'list'): ?>
        <!-- Tabel Tarif -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th>Jenis Kendaraan</th>
                            <th>Tarif per Jam</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($tarifList)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <?php
                                $icon = match($row['jenis_kendaraan']) {
                                    'motor' => 'bi-bicycle',
                                    'mobil' => 'bi-car-front',
                                    default => 'bi-truck'
                                };
                                ?>
                                <i class="bi <?= $icon ?> me-2"></i>
                                <strong><?= ucfirst($row['jenis_kendaraan']) ?></strong>
                            </td>
                            <td class="text-success fw-bold"><?= formatRupiah($row['tarif_per_jam']) ?></td>
                            <td>
                                <a href="?action=edit&id=<?= $row['id_tarif'] ?>" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="?action=delete&id=<?= $row['id_tarif'] ?>" class="btn btn-sm btn-danger btn-delete">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php else: ?>
        <!-- Form Add/Edit -->
        <div class="form-container" style="max-width: 500px;">
            <h5 class="mb-4">
                <?= $action == 'add' ? 'Tambah Tarif Baru' : 'Edit Tarif' ?>
            </h5>
            
            <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="form_action" value="<?= $action ?>">
                <?php if ($action == 'edit'): ?>
                <input type="hidden" name="id_tarif" value="<?= $tarifData['id_tarif'] ?>">
                <?php endif; ?>
                
                <div class="mb-3">
                    <label for="jenis_kendaraan" class="form-label">Jenis Kendaraan <span class="text-danger">*</span></label>
                    <select class="form-select" id="jenis_kendaraan" name="jenis_kendaraan" required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="motor" <?= ($tarifData['jenis_kendaraan'] ?? '') == 'motor' ? 'selected' : '' ?>>Motor</option>
                        <option value="mobil" <?= ($tarifData['jenis_kendaraan'] ?? '') == 'mobil' ? 'selected' : '' ?>>Mobil</option>
                        <option value="lainnya" <?= ($tarifData['jenis_kendaraan'] ?? '') == 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="tarif_per_jam" class="form-label">Tarif per Jam (Rp) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" class="form-control" id="tarif_per_jam" name="tarif_per_jam" 
                               value="<?= number_format($tarifData['tarif_per_jam'] ?? 0, 0, ',', '.') ?>" 
                               onkeyup="formatRupiahInput(this)" required>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Simpan
                    </button>
                    <a href="<?= APP_URL ?>/pages/admin/tarif.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
