<?php
/**
 * =============================================
 * HALAMAN LOGIN - Modern Black & White
 * =============================================
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/functions.php';

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    redirect('pages/dashboard.php');
}

$error = '';

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    // Query user berdasarkan username
    $query = "SELECT * FROM tb_user WHERE username = '$username' AND status_aktif = 1";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verifikasi password
        if ($password == $user['password']) {
            // Set session
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Simpan area assignment untuk petugas
            if ($user['role'] == 'petugas' && $user['id_area']) {
                $_SESSION['id_area'] = $user['id_area'];
                $areaResult = mysqli_query($conn, "SELECT nama_area FROM tb_area_parkir WHERE id_area = {$user['id_area']}");
                $areaData = mysqli_fetch_assoc($areaResult);
                $_SESSION['nama_area'] = $areaData['nama_area'] ?? '';
            } else {
                $_SESSION['id_area'] = null;
                $_SESSION['nama_area'] = '';
            }
            
            // Log aktivitas
            $logQuery = "INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES ({$user['id_user']}, 'Login')";
            mysqli_query($conn, $logQuery);
            
            setFlash('success', 'Selamat datang, ' . $user['nama_lengkap'] . '!');
            redirect('pages/dashboard.php');
        } else {
            $error = 'Password salah!';
        }
    } else {
        $error = 'Username tidak ditemukan atau tidak aktif!';
    }
}

$pageTitle = 'Login - ' . APP_NAME;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="card login-card">
            <div class="card-header">
                <i class="bi bi-car-front-fill" style="font-size: 48px; color: var(--color-black);"></i>
                <h4 class="mt-3 mb-1 fw-bold"><?= APP_NAME ?></h4>
                <p class="text-muted mb-0" style="font-size: 14px;">Silakan login untuk melanjutkan</p>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i><?= $error ?>
                </div>
                <?php endif; ?>
                
                <?= getFlash() ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white" style="border-right: 0;">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="text" class="form-control" id="username" name="username" 
                                   placeholder="Masukkan username" required autofocus style="border-left: 0;">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white" style="border-right: 0;">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Masukkan password" required style="border-left: 0;">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login
                    </button>
                </form>
            </div>
            <div class="card-footer">
                <small>&copy; <?= date('Y') ?> <?= APP_NAME ?></small>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
