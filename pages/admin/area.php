<?php
/**
 * =============================================
 * CRUD AREA PARKIR - ADMIN
 * =============================================
 */
require_once __DIR__ . '/../../includes/auth_check.php';
requireRole(['admin']);

$pageTitle = 'Area Parkir - ' . APP_NAME;

// Handle Actions
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$error = '';

// Proses Form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_area = mysqli_real_escape_string($conn, $_POST['nama_area']);
    $kapasitas = (int) $_POST['kapasitas'];
    
    if ($_POST['form_action'] == 'add') {
        $query = "INSERT INTO tb_area_parkir (nama_area, kapasitas, terisi) VALUES ('$nama_area', $kapasitas, 0)";
        if (mysqli_query($conn, $query)) {
            $logQuery = "INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES ({$_SESSION['user_id']}, 'Menambah area: $nama_area')";
            mysqli_query($conn, $logQuery);
            
            setFlash('success', 'Area parkir berhasil ditambahkan!');
            redirect('pages/admin/area.php');
        } else {
            $error = 'Gagal menambah area: ' . mysqli_error($conn);
        }
    } elseif ($_POST['form_action'] == 'edit') {
        $id = $_POST['id_area'];
        
        $query = "UPDATE tb_area_parkir SET nama_area = '$nama_area', kapasitas = $kapasitas WHERE id_area = $id";
        if (mysqli_query($conn, $query)) {
            $logQuery = "INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES ({$_SESSION['user_id']}, 'Mengedit area: $nama_area')";
            mysqli_query($conn, $logQuery);
            
            setFlash('success', 'Area parkir berhasil diupdate!');
            redirect('pages/admin/area.php');
        } else {
            $error = 'Gagal update area: ' . mysqli_error($conn);
        }
    }
}

// Proses Delete
if ($action == 'delete' && $id) {
    $area = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_area FROM tb_area_parkir WHERE id_area = $id"));
    if (mysqli_query($conn, "DELETE FROM tb_area_parkir WHERE id_area = $id")) {
        $logQuery = "INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES ({$_SESSION['user_id']}, 'Menghapus area: {$area['nama_area']}')";
        mysqli_query($conn, $logQuery);
        
        setFlash('success', 'Area parkir berhasil dihapus!');
    } else {
        setFlash('danger', 'Gagal menghapus area! Mungkin masih digunakan di transaksi.');
    }
    redirect('pages/admin/area.php');
}

// Ambil data untuk edit
$areaData = null;
if ($action == 'edit' && $id) {
    $areaData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tb_area_parkir WHERE id_area = $id"));
}

// Ambil semua area
$areaList = mysqli_query($conn, "SELECT * FROM tb_area_parkir ORDER BY id_area ASC");

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Area Parkir</h4>
                <small class="text-muted">Kelola area dan kapasitas parkir</small>
            </div>
            <?php if ($action == 'list'): ?>
            <a href="?action=add" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Tambah Area
            </a>
            <?php else: ?>
            <a href="<?= APP_URL ?>/pages/admin/area.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
            <?php endif; ?>
        </div>
        
        <?= getFlash() ?>
        
        <?php if ($action == 'list'): ?>
        <!-- Cards Area -->
        <div class="row g-4 mb-4">
            <?php while ($row = mysqli_fetch_assoc($areaList)): ?>
            <?php 
                $persentase = $row['kapasitas'] > 0 ? round(($row['terisi'] / $row['kapasitas']) * 100) : 0;
                $colorClass = $persentase < 50 ? 'success' : ($persentase < 80 ? 'warning' : 'danger');
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title mb-1"><?= escape($row['nama_area']) ?></h5>
                                <small class="text-muted">ID: <?= $row['id_area'] ?></small>
                            </div>
                            <div>
                                <a href="?action=edit&id=<?= $row['id_area'] ?>" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="?action=delete&id=<?= $row['id_area'] ?>" class="btn btn-sm btn-danger btn-delete">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Kapasitas</span>
                                <span class="fw-bold"><?= $row['terisi'] ?> / <?= $row['kapasitas'] ?></span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-<?= $colorClass ?>" style="width: <?= $persentase ?>%"></div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <span class="badge bg-<?= $colorClass ?>"><?= $persentase ?>% Terisi</span>
                            <span class="text-muted">Tersedia: <?= $row['kapasitas'] - $row['terisi'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        
        <?php else: ?>
        <!-- Form Add/Edit -->
        <div class="form-container" style="max-width: 500px;">
            <h5 class="mb-4">
                <?= $action == 'add' ? 'Tambah Area Baru' : 'Edit Area' ?>
            </h5>
            
            <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="form_action" value="<?= $action ?>">
                <?php if ($action == 'edit'): ?>
                <input type="hidden" name="id_area" value="<?= $areaData['id_area'] ?>">
                <?php endif; ?>
                
                <div class="mb-3">
                    <label for="nama_area" class="form-label">Nama Area <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nama_area" name="nama_area" 
                           value="<?= escape($areaData['nama_area'] ?? '') ?>" 
                           placeholder="Contoh: Area A - Motor" required>
                </div>
                
                <div class="mb-4">
                    <label for="kapasitas" class="form-label">Kapasitas <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="kapasitas" name="kapasitas" 
                           value="<?= $areaData['kapasitas'] ?? '' ?>" min="1" required>
                    <div class="form-text">Jumlah maksimal kendaraan yang bisa parkir</div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Simpan
                    </button>
                    <a href="<?= APP_URL ?>/pages/admin/area.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
