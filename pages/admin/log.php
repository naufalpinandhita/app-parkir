<?php
/**
 * =============================================
 * LOG AKTIVITAS - ADMIN
 * =============================================
 */
require_once __DIR__ . '/../../includes/auth_check.php';
requireRole(['admin']);

$pageTitle = 'Log Aktivitas - ' . APP_NAME;

// Filter
$filterUser = isset($_GET['user']) ? (int)$_GET['user'] : '';
$filterDate = $_GET['date'] ?? '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$where = "1=1";
if ($filterUser) {
    $where .= " AND l.id_user = $filterUser";
}
if ($filterDate) {
    $where .= " AND DATE(l.waktu_aktivitas) = '$filterDate'";
}

$totalData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_log_aktivitas l WHERE $where"))['total'];
$totalPages = ceil($totalData / $limit);

$logQuery = "SELECT l.*, u.nama_lengkap, u.username, u.role 
             FROM tb_log_aktivitas l 
             JOIN tb_user u ON l.id_user = u.id_user 
             WHERE $where 
             ORDER BY l.waktu_aktivitas DESC 
             LIMIT $limit OFFSET $offset";
$logList = mysqli_query($conn, $logQuery);

// Get all users for filter
$users = mysqli_query($conn, "SELECT id_user, nama_lengkap FROM tb_user ORDER BY nama_lengkap");

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0"><i class="bi bi-journal-text me-2"></i>Log Aktivitas</h4>
                <small class="text-muted">Riwayat aktivitas pengguna sistem (Total: <?= $totalData ?>)</small>
            </div>
        </div>
        
        <?= getFlash() ?>
        
        <!-- Filter -->
        <div class="table-container mb-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Filter User</label>
                    <select name="user" class="form-select">
                        <option value="">-- Semua User --</option>
                        <?php while ($u = mysqli_fetch_assoc($users)): ?>
                        <option value="<?= $u['id_user'] ?>" <?= $filterUser == $u['id_user'] ? 'selected' : '' ?>>
                            <?= escape($u['nama_lengkap']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter Tanggal</label>
                    <input type="date" name="date" class="form-control" value="<?= $filterDate ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    <a href="<?= APP_URL ?>/pages/admin/log.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
        
        <!-- Tabel Log -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th width="180">Waktu</th>
                            <th>User</th>
                            <th>Aktivitas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = $offset + 1; while ($row = mysqli_fetch_assoc($logList)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <small class="text-muted"><?= date('d/m/Y', strtotime($row['waktu_aktivitas'])) ?></small><br>
                                <strong><?= date('H:i:s', strtotime($row['waktu_aktivitas'])) ?></strong>
                            </td>
                            <td>
                                <strong><?= escape($row['nama_lengkap']) ?></strong><br>
                                <small class="text-muted">@<?= escape($row['username']) ?></small>
                                <?php
                                $badgeClass = match($row['role']) {
                                    'admin' => 'bg-danger',
                                    'petugas' => 'bg-primary',
                                    'owner' => 'bg-success',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?= $badgeClass ?> ms-1"><?= ucfirst($row['role']) ?></span>
                            </td>
                            <td>
                                <?php
                                $icon = 'bi-activity';
                                $activity = $row['aktivitas'];
                                if (str_contains($activity, 'Login')) $icon = 'bi-box-arrow-in-right text-success';
                                elseif (str_contains($activity, 'Logout')) $icon = 'bi-box-arrow-right text-danger';
                                elseif (str_contains($activity, 'Menambah')) $icon = 'bi-plus-circle text-primary';
                                elseif (str_contains($activity, 'Mengedit')) $icon = 'bi-pencil text-warning';
                                elseif (str_contains($activity, 'Menghapus')) $icon = 'bi-trash text-danger';
                                ?>
                                <i class="bi <?= $icon ?> me-2"></i>
                                <?= escape($row['aktivitas']) ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if ($totalData == 0): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Tidak ada log aktivitas</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center mb-0">
                    <?php
                    $queryParams = $_GET;
                    unset($queryParams['page']);
                    $queryString = http_build_query($queryParams);
                    ?>
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&<?= $queryString ?>">Previous</a>
                    </li>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&<?= $queryString ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&<?= $queryString ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
