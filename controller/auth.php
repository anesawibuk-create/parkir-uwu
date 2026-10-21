<?php
require_once __DIR__ . '/../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) { header('Location: ../view/login.php?error=csrf'); exit; }
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $stmt = mysqli_prepare($koneksi, "SELECT id_user, nama_lengkap, username, password, role FROM users WHERE username = ? AND status_aktif = 'aktif' LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    $valid = $row && (password_verify($password, $row['password']) || hash_equals((string)$row['password'], $password));
    if ($valid) {
        // Kompatibilitas: hash password akun lama yang masih tersimpan dalam teks biasa saat login berhasil.
        if (!password_get_info($row['password'])['algo']) {
            $new_hash = password_hash($password, PASSWORD_DEFAULT);
            $upgrade = mysqli_prepare($koneksi, "UPDATE users SET password = ? WHERE id_user = ?");
            mysqli_stmt_bind_param($upgrade, 'si', $new_hash, $row['id_user']);
            mysqli_stmt_execute($upgrade);
            mysqli_stmt_close($upgrade);
        }
        session_regenerate_id(true);
        $_SESSION['login'] = true;
        $_SESSION['id_user'] = (int)$row['id_user'];
        $_SESSION['nama_lengkap'] = $row['nama_lengkap'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];
        catat_log($row['id_user'], "Login ke sistem sebagai " . $row['role']);
        header('Location: ../view/dashboard.php');
        exit;
    }
    header('Location: ../view/login.php?error=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'logout') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) { http_response_code(403); exit('Permintaan tidak valid. Muat ulang halaman lalu coba lagi.'); }
    if (isset($_SESSION['id_user'])) catat_log($_SESSION['id_user'], 'Logout dari sistem');
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    header('Location: ../view/login.php');
    exit;
}

header('Location: ../view/login.php');
exit;
?>