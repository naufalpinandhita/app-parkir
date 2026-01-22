<?php
/**
 * =============================================
 * AUTH CHECK
 * =============================================
 * File ini memastikan user sudah login sebelum
 * mengakses halaman tertentu
 */

// Load konfigurasi
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/functions.php';

// Cek apakah user sudah login
if (!isLoggedIn()) {
    setFlash('warning', 'Silakan login terlebih dahulu!');
    redirect('auth/login.php');
}

/**
 * Fungsi untuk membatasi akses berdasarkan role
 * 
 * @param array $allowedRoles Role yang diizinkan
 * @return void
 */
function requireRole($allowedRoles) {
    if (!hasRole($allowedRoles)) {
        setFlash('danger', 'Anda tidak memiliki akses ke halaman ini!');
        redirect('pages/dashboard.php');
    }
}
