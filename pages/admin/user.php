<?php
/**
 * =============================================
 * CRUD USER - ADMIN
 * =============================================
 */
require_once __DIR__ . '/../../includes/auth_check.php';
requireRole(['admin']);

$pageTitle = 'Data User';

// Handle Actions
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$error = '';
$success = '';

// Proses Form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $status_aktif = isset($_POST['status_aktif']) ? 1 : 0;
    
    if ($_POST['form_action'] == 'add') {
        // Cek username unik
        $check = mysqli_query($conn, "SELECT id_user FROM tb_user WHERE username = '$username'");
        if (mysqli_num_rows($check) > 0) {
            $error = 'Username sudah digunakan!';
        } else {
            $query = "INSERT INTO tb_user (nama_lengkap, username, password, role, status_aktif) 
                      VALUES ('$nama_lengkap', '$username', '$password', '$role', $status_aktif)";
            if (mysqli_query($conn, $query)) {
                // Log aktivitas
                $logQuery = "INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES ({$_SESSION['user_id']}, 'Menambah user: $username')";
                mysqli_query($conn, $logQuery);
                
                setFlash('success', 'User berhasil ditambahkan!');
                redirect('pages/admin/user.php');
            } else {
                $error = 'Gagal menambah user: ' . mysqli_error($conn);
            }
        }
    } elseif ($_POST['form_action'] == 'edit') {
        $id = $_POST['id_user'];
        
        // Cek username unik (kecuali untuk user ini sendiri)
        $check = mysqli_query($conn, "SELECT id_user FROM tb_user WHERE username = '$username' AND id_user != $id");
        if (mysqli_num_rows($check) > 0) {
            $error = 'Username sudah digunakan!';
        } else {
            // Jika password kosong, jangan update password
            if (empty($password)) {
                $query = "UPDATE tb_user SET nama_lengkap = '$nama_lengkap', username = '$username', 
                          role = '$role', status_aktif = $status_aktif WHERE id_user = $id";
            } else {
                $query = "UPDATE tb_user SET nama_lengkap = '$nama_lengkap', username = '$username', 
                          password = '$password', role = '$role', status_aktif = $status_aktif WHERE id_user = $id";
            }
            
            if (mysqli_query($conn, $query)) {
                // Log aktivitas
                $logQuery = "INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES ({$_SESSION['user_id']}, 'Mengedit user: $username')";
                mysqli_query($conn, $logQuery);
                
                setFlash('success', 'User berhasil diupdate!');
                redirect('pages/admin/user.php');
            } else {
                $error = 'Gagal update user: ' . mysqli_error($conn);
            }
        }
    }
}

// Proses Delete
if ($action == 'delete' && $id) {
    // Cek apakah bukan user sendiri
    if ($id == $_SESSION['user_id']) {
        setFlash('danger', 'Tidak dapat menghapus akun sendiri!');
    } else {
        $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT username FROM tb_user WHERE id_user = $id"));
        if (mysqli_query($conn, "DELETE FROM tb_user WHERE id_user = $id")) {
            // Log aktivitas
            $logQuery = "INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES ({$_SESSION['user_id']}, 'Menghapus user: {$user['username']}')";
            mysqli_query($conn, $logQuery);
            
            setFlash('success', 'User berhasil dihapus!');
        } else {
            setFlash('danger', 'Gagal menghapus user!');
        }
    }
    redirect('pages/admin/user.php');
}

// Ambil data untuk edit
$userData = null;
if ($action == 'edit' && $id) {
    $userData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tb_user WHERE id_user = $id"));
}

// Ambil semua user (untuk list)
$users = mysqli_query($conn, "SELECT * FROM tb_user ORDER BY id_user DESC");

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0"><i class="bi bi-people me-2"></i>Data User</h4>
                <small class="text-muted">Kelola data pengguna sistem</small>
            </div>
            <?php if ($action == 'list'): ?>
            <a href="?action=add" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Tambah User
            </a>
            <?php else: ?>
            <a href="<?= APP_URL ?>/pages/admin/user.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
            <?php endif; ?>
        </div>
        
        <?= getFlash() ?>
        
        <?php if ($action == 'list'): ?>
        <!-- Tabel User -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($users)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><strong><?= escape($row['nama_lengkap']) ?></strong></td>
                            <td><?= escape($row['username']) ?></td>
                            <td>
                                <?php
                                $badgeClass = match($row['role']) {
                                    'admin' => 'bg-danger',
                                    'petugas' => 'bg-primary',
                                    'owner' => 'bg-success',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= ucfirst($row['role']) ?></span>
                            </td>
                            <td>
                                <?php if ($row['status_aktif']): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?action=edit&id=<?= $row['id_user'] ?>" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ($row['id_user'] != $_SESSION['user_id']): ?>
                                <a href="?action=delete&id=<?= $row['id_user'] ?>" class="btn btn-sm btn-danger btn-delete">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php else: ?>
        <!-- Form Add/Edit -->
        <div class="form-container" style="max-width: 600px;">
            <h5 class="mb-4">
                <?= $action == 'add' ? 'Tambah User Baru' : 'Edit User' ?>
            </h5>
            
            <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="form_action" value="<?= $action ?>">
                <?php if ($action == 'edit'): ?>
                <input type="hidden" name="id_user" value="<?= $userData['id_user'] ?>">
                <?php endif; ?>
                
                <div class="mb-3">
                    <label for="nama_lengkap" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                           value="<?= escape($userData['nama_lengkap'] ?? '') ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?= escape($userData['username'] ?? '') ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">
                        Password <?= $action == 'add' ? '<span class="text-danger">*</span>' : '<small class="text-muted">(kosongkan jika tidak diubah)</small>' ?>
                    </label>
                    <input type="password" class="form-control" id="password" name="password" 
                           <?= $action == 'add' ? 'required' : '' ?>>
                </div>
                
                <div class="mb-3">
                    <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="">-- Pilih Role --</option>
                        <option value="admin" <?= ($userData['role'] ?? '') == 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="petugas" <?= ($userData['role'] ?? '') == 'petugas' ? 'selected' : '' ?>>Petugas</option>
                        <option value="owner" <?= ($userData['role'] ?? '') == 'owner' ? 'selected' : '' ?>>Owner</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="status_aktif" name="status_aktif" 
                               <?= ($userData['status_aktif'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="status_aktif">Status Aktif</label>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Simpan
                    </button>
                    <a href="<?= APP_URL ?>/pages/admin/user.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
