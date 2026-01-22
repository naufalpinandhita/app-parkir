<?php
/**
 * =============================================
 * KONFIGURASI DATABASE
 * =============================================
 * File ini berisi konfigurasi koneksi database MySQL
 * 
 * @package    Aplikasi Parkir
 * @author     Developer
 * @version    1.0.0
 */

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_parkir');

// Membuat koneksi database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset ke utf8mb4
mysqli_set_charset($conn, "utf8mb4");
