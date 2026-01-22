<?php
/**
 * =============================================
 * HELPER FUNCTIONS
 * =============================================
 * File ini berisi fungsi-fungsi helper yang digunakan
 * di seluruh aplikasi
 * 
 * @package    Aplikasi Parkir
 * @author     Developer
 * @version    1.0.0
 */

/**
 * Redirect ke halaman tertentu
 * 
 * @param string $url URL tujuan
 * @return void
 */
function redirect($url) {
    header("Location: " . APP_URL . "/" . $url);
    exit();
}

/**
 * Escape output untuk mencegah XSS
 * 
 * @param string $string String yang akan di-escape
 * @return string String yang sudah di-escape
 */
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Format mata uang Rupiah
 * 
 * @param int|float $amount Jumlah uang
 * @return string Format rupiah
 */
function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * Format tanggal Indonesia
 * 
 * @param string $date Tanggal dalam format Y-m-d atau datetime
 * @param bool $withTime Tampilkan waktu atau tidak
 * @return string Tanggal dalam format Indonesia
 */
function formatTanggal($date, $withTime = false) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $timestamp = strtotime($date);
    $hari = date('d', $timestamp);
    $bln = $bulan[(int)date('m', $timestamp)];
    $tahun = date('Y', $timestamp);
    
    $hasil = "$hari $bln $tahun";
    
    if ($withTime) {
        $hasil .= ' ' . date('H:i', $timestamp);
    }
    
    return $hasil;
}

/**
 * Hitung durasi parkir dalam jam
 * 
 * @param string $waktuMasuk Waktu masuk
 * @param string $waktuKeluar Waktu keluar
 * @return int Durasi dalam jam (minimal 1 jam)
 */
function hitungDurasi($waktuMasuk, $waktuKeluar) {
    $masuk = strtotime($waktuMasuk);
    $keluar = strtotime($waktuKeluar);
    $selisih = $keluar - $masuk;
    $jam = ceil($selisih / 3600); // Bulatkan ke atas
    return max(1, $jam); // Minimal 1 jam
}

/**
 * Generate nomor tiket parkir
 * 
 * @return string Nomor tiket unik
 */
function generateNomorTiket() {
    return 'PKR-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

/**
 * Cek apakah user sudah login
 * 
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Ambil data user yang sedang login
 * 
 * @param string $key Key data yang ingin diambil (optional)
 * @return mixed
 */
function currentUser($key = null) {
    if (!isLoggedIn()) {
        return null;
    }
    
    if ($key) {
        return $_SESSION[$key] ?? null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'nama' => $_SESSION['nama_lengkap'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role']
    ];
}

/**
 * Cek role user
 * 
 * @param string|array $roles Role yang diizinkan
 * @return bool
 */
function hasRole($roles) {
    if (!isLoggedIn()) {
        return false;
    }
    
    if (is_string($roles)) {
        $roles = [$roles];
    }
    
    return in_array($_SESSION['role'], $roles);
}

/**
 * Set flash message
 * 
 * @param string $type Tipe pesan (success, danger, warning, info)
 * @param string $message Isi pesan
 * @return void
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Tampilkan flash message
 * 
 * @return string HTML alert
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        
        return '<div class="alert alert-' . $flash['type'] . ' alert-dismissible fade show" role="alert">
                    ' . $flash['message'] . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
    }
    return '';
}
