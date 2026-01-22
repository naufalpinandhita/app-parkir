<?php
/**
 * =============================================
 * PROSES LOGOUT
 * =============================================
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/functions.php';

// Log aktivitas sebelum logout
if (isLoggedIn()) {
    $userId = $_SESSION['user_id'];
    $logQuery = "INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES ($userId, 'Logout')";
    mysqli_query($conn, $logQuery);
}

// Hapus semua session
session_unset();
session_destroy();

// Redirect ke login
header("Location: " . APP_URL . "/auth/login.php");
exit();
