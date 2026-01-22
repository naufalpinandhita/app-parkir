<?php
/**
 * Navbar/Sidebar berdasarkan role user
 */
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$userRole = currentUser('role');
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= APP_URL ?>/pages/dashboard.php">
            <i class="bi bi-car-front-fill me-2"></i><?= APP_NAME ?>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <!-- Dashboard - Semua Role -->
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage == 'dashboard' ? 'active' : '' ?>" href="<?= APP_URL ?>/pages/dashboard.php">
                        <i class="bi bi-speedometer2 me-1"></i> Dashboard
                    </a>
                </li>
                
                <?php if ($userRole == 'admin'): ?>
                <!-- Menu Admin -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-gear me-1"></i> Master Data
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/pages/admin/user.php">
                            <i class="bi bi-people me-2"></i>Data User</a></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/pages/admin/tarif.php">
                            <i class="bi bi-cash me-2"></i>Tarif Parkir</a></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/pages/admin/area.php">
                            <i class="bi bi-geo-alt me-2"></i>Area Parkir</a></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/pages/admin/kendaraan.php">
                            <i class="bi bi-truck me-2"></i>Kendaraan</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage == 'log' ? 'active' : '' ?>" href="<?= APP_URL ?>/pages/admin/log.php">
                        <i class="bi bi-journal-text me-1"></i> Log Aktivitas
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if ($userRole == 'petugas'): ?>
                <!-- Menu Petugas -->
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage == 'transaksi' ? 'active' : '' ?>" href="<?= APP_URL ?>/pages/petugas/transaksi.php">
                        <i class="bi bi-receipt me-1"></i> Transaksi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage == 'struk' ? 'active' : '' ?>" href="<?= APP_URL ?>/pages/petugas/struk.php">
                        <i class="bi bi-printer me-1"></i> Cetak Struk
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if ($userRole == 'owner'): ?>
                <!-- Menu Owner -->
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage == 'laporan' ? 'active' : '' ?>" href="<?= APP_URL ?>/pages/owner/laporan.php">
                        <i class="bi bi-bar-chart me-1"></i> Laporan
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            
            <!-- User Info & Logout -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        <?= escape(currentUser('nama_lengkap')) ?>
                        <span class="badge bg-light text-primary ms-1"><?= ucfirst($userRole) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/pages/profil.php">
                            <i class="bi bi-person me-2"></i>Profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= APP_URL ?>/auth/logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
