<?php
/**
 * Sidebar Navigation Component
 * Black & White Modern Design
 */
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$userRole = currentUser('role');
$userName = currentUser('nama_lengkap');
?>

<div class="sidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <a href="<?= APP_URL ?>/pages/dashboard.php" class="sidebar-logo">
            <i class="bi bi-car-front-fill"></i>
            <span class="sidebar-logo-text"><?= APP_NAME ?></span>
        </a>
    </div>
    
    <!-- Sidebar Navigation -->
    <nav class="sidebar-nav">
        <ul class="sidebar-menu">
            <!-- Dashboard - All Roles -->
            <li class="sidebar-menu-item">
                <a href="<?= APP_URL ?>/pages/dashboard.php" 
                   class="sidebar-menu-link <?= $currentPage == 'dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                    <i class="bi bi-info-circle info-icon" 
                       data-bs-toggle="popover" 
                       data-bs-placement="right"
                       data-bs-trigger="click"
                       data-bs-title="Dashboard"
                       data-bs-content="Halaman utama dengan ringkasan data: total kendaraan, transaksi hari ini, dan statistik parkir."></i>
                </a>
            </li>
            
            <?php if ($userRole == 'admin'): ?>
            <!-- Admin Menu -->
            <div class="sidebar-divider"></div>
            
            <li class="sidebar-menu-item">
                <a href="#" class="sidebar-menu-link" style="pointer-events: none; opacity: 0.6; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                    <span>Master Data</span>
                </a>
            </li>
            
            <ul class="sidebar-submenu">
                <li class="sidebar-menu-item">
                    <a href="<?= APP_URL ?>/pages/admin/user.php" 
                       class="sidebar-menu-link <?= $currentPage == 'user' ? 'active' : '' ?>">
                        <i class="bi bi-people"></i>
                        <span>User</span>
                        <i class="bi bi-info-circle info-icon" 
                           data-bs-toggle="popover" 
                           data-bs-placement="right"
                           data-bs-trigger="click"
                           data-bs-title="Manajemen User"
                           data-bs-content="Kelola data pengguna sistem: tambah user baru, edit informasi, hapus user, dan atur role (Admin/Petugas/Owner)."></i>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="<?= APP_URL ?>/pages/admin/tarif.php" 
                       class="sidebar-menu-link <?= $currentPage == 'tarif' ? 'active' : '' ?>">
                        <i class="bi bi-cash"></i>
                        <span>Tarif</span>
                        <i class="bi bi-info-circle info-icon" 
                           data-bs-toggle="popover" 
                           data-bs-placement="right"
                           data-bs-trigger="click"
                           data-bs-title="Tarif Parkir"
                           data-bs-content="Atur tarif parkir per jam untuk setiap jenis kendaraan (motor, mobil, dll)."></i>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="<?= APP_URL ?>/pages/admin/area.php" 
                       class="sidebar-menu-link <?= $currentPage == 'area' ? 'active' : '' ?>">
                        <i class="bi bi-geo-alt"></i>
                        <span>Area Parkir</span>
                        <i class="bi bi-info-circle info-icon" 
                           data-bs-toggle="popover" 
                           data-bs-placement="right"
                           data-bs-trigger="click"
                           data-bs-title="Area Parkir"
                           data-bs-content="Kelola area parkir: tambah area baru, set kapasitas maksimal, lihat slot terisi."></i>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="<?= APP_URL ?>/pages/admin/kendaraan.php" 
                       class="sidebar-menu-link <?= $currentPage == 'kendaraan' ? 'active' : '' ?>">
                        <i class="bi bi-truck"></i>
                        <span>Kendaraan</span>
                        <i class="bi bi-info-circle info-icon" 
                           data-bs-toggle="popover" 
                           data-bs-placement="right"
                           data-bs-trigger="click"
                           data-bs-title="Data Kendaraan"
                           data-bs-content="Lihat semua kendaraan yang terdaftar di sistem dengan detail plat nomor dan jenis."></i>
                    </a>
                </li>
            </ul>
            
            <li class="sidebar-menu-item">
                <a href="<?= APP_URL ?>/pages/admin/log.php" 
                   class="sidebar-menu-link <?= $currentPage == 'log' ? 'active' : '' ?>">
                    <i class="bi bi-journal-text"></i>
                    <span>Log Aktivitas</span>
                    <i class="bi bi-info-circle info-icon" 
                       data-bs-toggle="popover" 
                       data-bs-placement="right"
                       data-bs-trigger="click"
                       data-bs-title="Log Aktivitas"
                       data-bs-content="Lihat riwayat semua aktivitas user di sistem untuk monitoring dan audit."></i>
                </a>
            </li>
            <?php endif; ?>
            
            <?php if ($userRole == 'petugas'): ?>
            <!-- Petugas Menu -->
            <div class="sidebar-divider"></div>
            
            <li class="sidebar-menu-item">
                <a href="<?= APP_URL ?>/pages/petugas/transaksi.php" 
                   class="sidebar-menu-link <?= $currentPage == 'transaksi' ? 'active' : '' ?>">
                    <i class="bi bi-receipt"></i>
                    <span>Transaksi</span>
                    <i class="bi bi-info-circle info-icon" 
                       data-bs-toggle="popover" 
                       data-bs-placement="right"
                       data-bs-trigger="click"
                       data-bs-title="Transaksi Parkir"
                       data-bs-content="Proses parkir masuk dan keluar: input data kendaraan, pilih area, hitung biaya otomatis."></i>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="<?= APP_URL ?>/pages/petugas/struk.php" 
                   class="sidebar-menu-link <?= $currentPage == 'struk' ? 'active' : '' ?>">
                    <i class="bi bi-printer"></i>
                    <span>Cetak Struk</span>
                    <i class="bi bi-info-circle info-icon" 
                       data-bs-toggle="popover" 
                       data-bs-placement="right"
                       data-bs-trigger="click"
                       data-bs-title="Cetak Struk"
                       data-bs-content="Cetak struk parkir untuk diserahkan kepada pelanggan."></i>
                </a>
            </li>
            <?php endif; ?>
            
            <?php if ($userRole == 'owner'): ?>
            <!-- Owner Menu -->
            <div class="sidebar-divider"></div>
            
            <li class="sidebar-menu-item">
                <a href="<?= APP_URL ?>/pages/owner/laporan.php" 
                   class="sidebar-menu-link <?= $currentPage == 'laporan' ? 'active' : '' ?>">
                    <i class="bi bi-bar-chart"></i>
                    <span>Laporan</span>
                    <i class="bi bi-info-circle info-icon" 
                       data-bs-toggle="popover" 
                       data-bs-placement="right"
                       data-bs-trigger="click"
                       data-bs-title="Laporan & Statistik"
                       data-bs-content="Lihat laporan pendapatan, grafik transaksi, dan statistik detail dengan filter periode."></i>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
    
    <!-- Sidebar Footer (User Info) -->
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar">
                <i class="bi bi-person"></i>
            </div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?= escape($userName) ?></div>
                <span class="sidebar-user-role"><?= ucfirst($userRole) ?></span>
            </div>
        </div>
    </div>
</div>
