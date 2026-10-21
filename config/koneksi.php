<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "parkir_db_fixed"; 

$koneksi = mysqli_connect($host, $user, $pass, $db); 

if (!$koneksi) {
    error_log("Koneksi database gagal: " . mysqli_connect_error());
    http_response_code(500);
    exit("Layanan database sedang tidak tersedia. Periksa konfigurasi koneksi.");
}
mysqli_set_charset($koneksi, 'utf8mb4');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function catat_log($id_user, $aktivitas) {
    global $koneksi;
    $id_user = (int)$id_user;
    $aktivitas = trim((string)$aktivitas);
    $stmt = mysqli_prepare($koneksi, "INSERT INTO log_aktivitas (id_user, aktivitas, waktu) VALUES (?, ?, NOW())");
    if (!$stmt) return false;
    mysqli_stmt_bind_param($stmt, 'is', $id_user, $aktivitas);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

function cek_akses($allowed_roles = []) {
    if (empty($_SESSION['login'])) {
        header("Location: ../view/login.php");
        exit;
    }
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles, true)) {
        die("Akses Ditolak! Anda tidak memiliki hak akses ke halaman ini.");
    }
}
?>