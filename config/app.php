<?php
/**
 * =============================================
 * KONFIGURASI APLIKASI
 * =============================================
 * File ini berisi konfigurasi umum aplikasi
 * 
 * @package    Aplikasi Parkir
 * @author     Developer
 * @version    1.0.0
 */

// Konfigurasi Aplikasi
define('APP_NAME', 'Aplikasi Parkir');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/app-parkir');

// Konfigurasi Timezone
date_default_timezone_set('Asia/Jakarta');

// Konfigurasi Session
session_start();

// Error Reporting (matikan di production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
