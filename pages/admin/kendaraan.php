<?php
/**
 * =============================================
 * CRUD KENDARAAN - ADMIN
 * =============================================
 */
require_once __DIR__ . '/../../includes/auth_check.php';
requireRole(['admin']);

$pageTitle = 'Data Kendaraan - ' . APP_NAME;

// Handle Actions
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$error = '';

// Proses Form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $plat_nomor = strtoupper(mysqli_real_escape_string($conn, $_POST['plat_nomor']));
    $jenis_kendaraan = mysqli_real_escape_string($conn, $_POST['jenis_kendaraan']);
    $warna = mysqli_real_escape_string($conn, $_POST['warna']);
    $pemilik = mysqli_real_escape_string($conn, $_POST['pemilik']);
    
    if ($_POST['form_action'] == 'add') {
        // Cek plat nomor unik
        $check = mysqli_query($conn, "SELECT id_kendaraan FROM tb_kendaraan WHERE plat_nomor = '$plat_nomor'");
        if (mysqli_num_rows($check) > 0) {
            $error = 'Plat nomor sudah terdaftar!';
        } else {
            $query = "INSERT INTO tb_kendaraan (plat_nomor, jenis_kendaraan, warna, pemilik, id_user) 
                      VALUES ('$plat_nomor', '$jenis_kendaraan', '$warna', '$pemilik', {$_SESSION['user_id']})";
            if (mysqli_query($conn, $query)) {
                $logQuery = "INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES ({$_SESSION['user_id']}, 'Menambah kendaraan: $plat_nomor')";
                mysqli_query($conn, $logQuery);
                
                setFlash('success', 'Kendaraan berhasil ditambahkan!');
                redirect('pages/admin/kendaraan.php');
            } else {
                $error = 'Gagal menambah kendaraan: ' . mysqli_error($conn);
            }
        }
    } elseif ($_POST['form_action'] == 'edit') {
        $id = $_POST['id_kendaraan'];
        
        // Cek plat nomor unik
        $check = mysqli_query($conn, "SELECT id_kendaraan FROM tb_kendaraan WHERE plat_nomor = '$plat_nomor' AND id_kendaraan != $id");
        if (mysqli_num_rows($check) > 0) {
            $error = 'Plat nomor sudah terdaftar!';
        } else {
            $query = "UPDATE tb_kendaraan SET plat_nomor = '$plat_nomor', jenis_kendaraan = '$jenis_kendaraan', 
                      warna = '$warna', pemilik = '$pemilik' WHERE id_kendaraan = $id";
            if (mysqli_query($conn, $query)) {
                $logQuery = "INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES ({$_SESSION['user_id']}, 'Mengedit kendaraan: $plat_nomor')";
                mysqli_query($conn, $logQuery);
                
                setFlash('success', 'Kendaraan berhasil diupdate!');
                redirect('pages/admin/kendaraan.php');
            } else {
                $error = 'Gagal update kendaraan: ' . mysqli_error($conn);
            }
        }
    }
}

// Proses Delete
if ($action == 'delete' && $id) {
    $kendaraan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT plat_nomor FROM tb_kendaraan WHERE id_kendaraan = $id"));
    if (mysqli_query($conn, "DELETE FROM tb_kendaraan WHERE id_kendaraan = $id")) {
        $logQuery = "INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES ({$_SESSION['user_id']}, 'Menghapus kendaraan: {$kendaraan['plat_nomor']}')";
        mysqli_query($conn, $logQuery);
        
        setFlash('success', 'Kendaraan berhasil dihapus!');
    } else {
        setFlash('danger', 'Gagal menghapus kendaraan! Mungkin masih ada transaksi terkait.');
    }
    redirect('pages/admin/kendaraan.php');
}

// Ambil data untuk edit
$kendaraanData = null;
if ($action == 'edit' && $id) {
    $kendaraanData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tb_kendaraan WHERE id_kendaraan = $id"));
}

// Ambil semua kendaraan dengan pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$totalData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_kendaraan"))['total'];
$totalPages = ceil($totalData / $limit);

$kendaraanList = mysqli_query($conn, "SELECT * FROM tb_kendaraan ORDER BY id_kendaraan DESC LIMIT $limit OFFSET $offset");

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0"><i class="bi bi-truck me-2"></i>Data Kendaraan</h4>
                <small class="text-muted">Kelola data kendaraan terdaftar (Total: <?= $totalData ?>)</small>
            </div>
            <?php if ($action == 'list'): ?>
            <a href="?action=add" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Tambah Kendaraan
            </a>
            <?php else: ?>
            <a href="<?= APP_URL ?>/pages/admin/kendaraan.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
            <?php endif; ?>
        </div>
        
        <?= getFlash() ?>
        
        <?php if ($action == 'list'): ?>
        <!-- Tabel Kendaraan -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th>Plat Nomor</th>
                            <th>Jenis</th>
                            <th>Warna</th>
                            <th>Pemilik</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = $offset + 1; while ($row = mysqli_fetch_assoc($kendaraanList)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><strong class="text-primary"><?= escape($row['plat_nomor']) ?></strong></td>
                            <td>
                                <?php
                                $icon = match($row['jenis_kendaraan']) {
                                    'motor' => 'bi-bicycle',
                                    'mobil' => 'bi-car-front',
                                    default => 'bi-truck'
                                };
                                ?>
                                <i class="bi <?= $icon ?> me-1"></i>
                                <?= ucfirst($row['jenis_kendaraan']) ?>
                            </td>
                            <td><?= escape($row['warna']) ?: '-' ?></td>
                            <td><?= escape($row['pemilik']) ?: '-' ?></td>
                            <td>
                                <a href="?action=edit&id=<?= $row['id_kendaraan'] ?>" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="?action=delete&id=<?= $row['id_kendaraan'] ?>" class="btn btn-sm btn-danger btn-delete">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if ($totalData == 0): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada data kendaraan</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
        
        <?php else: ?>
        <!-- Form Add/Edit -->
        <div class="form-container" style="max-width: 600px;">
            <h5 class="mb-4">
                <?= $action == 'add' ? 'Tambah Kendaraan Baru' : 'Edit Kendaraan' ?>
            </h5>
            
            <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="form_action" value="<?= $action ?>">
                <?php if ($action == 'edit'): ?>
                <input type="hidden" name="id_kendaraan" value="<?= $kendaraanData['id_kendaraan'] ?>">
                <?php endif; ?>
                
                <div class="mb-3">
                    <label for="plat_nomor" class="form-label">Plat Nomor <span class="text-danger">*</span></label>
                    <input type="text" class="form-control text-uppercase" id="plat_nomor" name="plat_nomor" 
                           value="<?= escape($kendaraanData['plat_nomor'] ?? '') ?>" 
                           placeholder="Contoh: AB 1234 CD" required>
                </div>
                
                <div class="mb-3">
                    <label for="jenis_kendaraan" class="form-label">Jenis Kendaraan <span class="text-danger">*</span></label>
                    <select class="form-select" id="jenis_kendaraan" name="jenis_kendaraan" required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="motor" <?= ($kendaraanData['jenis_kendaraan'] ?? '') == 'motor' ? 'selected' : '' ?>>Motor</option>
                        <option value="mobil" <?= ($kendaraanData['jenis_kendaraan'] ?? '') == 'mobil' ? 'selected' : '' ?>>Mobil</option>
                        <option value="lainnya" <?= ($kendaraanData['jenis_kendaraan'] ?? '') == 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                    </select>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="warna" class="form-label">Warna</label>
                        <input type="text" class="form-control" id="warna" name="warna" 
                               value="<?= escape($kendaraanData['warna'] ?? '') ?>" 
                               placeholder="Contoh: Hitam">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="pemilik" class="form-label">Nama Pemilik</label>
                        <input type="text" class="form-control" id="pemilik" name="pemilik" 
                               value="<?= escape($kendaraanData['pemilik'] ?? '') ?>" 
                               placeholder="Contoh: John Doe">
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Simpan
                    </button>
                    <a href="<?= APP_URL ?>/pages/admin/kendaraan.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
