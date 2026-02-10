<?php
/**
 * Top Header Bar (Minimal)
 * Used with Sidebar Layout
 */
$userRole = currentUser('role');
$userName = currentUser('nama_lengkap');
?>

<div class="main-wrapper">
    <div class="top-header">
        <div>
            <h5 class="mb-0"><?= $pageTitle ?? 'Dashboard' ?></h5>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted"><?= escape($userName) ?></span>
            <a href="<?= APP_URL ?>/auth/logout.php" class="btn btn-sm btn-outline" title="Logout">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>
