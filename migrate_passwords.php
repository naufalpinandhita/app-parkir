<?php
/**
 * Script Migrasi Password ke Hash
 * Jalankan via CLI: php migrate_passwords.php
 */
require_once __DIR__ . '/config/database.php';

echo "=============================================\n";
echo "MIGRASI PASSWORD KE HASH\n";
echo "=============================================\n";

$query = "SELECT id_user, username, password FROM tb_user";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Gagal mengambil data user: " . mysqli_error($conn) . "\n");
}

$count = 0;
$skipped = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $id = $row['id_user'];
    $username = $row['username'];
    $password = $row['password'];
    
    // Cek apakah password belum di-hash (bcrypt dimulai dengan $2y$)
    // Asumsi: Password plain text tidak dimulai dengan $2y$ (sangat kecil kemungkinannya)
    if (substr($password, 0, 4) !== '$2y$') {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $update = "UPDATE tb_user SET password = '$hashed' WHERE id_user = $id";
        
        if (mysqli_query($conn, $update)) {
            echo "[OK] User '$username' (ID: $id) berhasil di-hash.\n";
            $count++;
        } else {
            echo "[ERROR] Gagal update user '$username': " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "[PY] User '$username' sudah menggunakan hash. Skip.\n";
        $skipped++;
    }
}

echo "=============================================\n";
echo "SELESAI.\n";
echo "Dikonversi: $count\n";
echo "Dilewati  : $skipped\n";
echo "=============================================\n";
