<?php
/**
 * =============================================
 * APLIKASI PARKIR - INDEX
 * =============================================
 * Redirect ke halaman login atau dashboard
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/helpers/functions.php';

// Redirect berdasarkan status login
if (isLoggedIn()) {
    redirect('pages/dashboard.php');
} else {
    redirect('auth/login.php');
}
