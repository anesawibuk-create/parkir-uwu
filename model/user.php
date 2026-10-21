<?php
require_once __DIR__ . '/../config/koneksi.php';

function get_all_users() {
    global $koneksi;
    return mysqli_query($koneksi, "SELECT id_user, nama_lengkap, username, role, status_aktif, created_at FROM users ORDER BY id_user DESC");
}

function tambah_user_data($nama, $username, $password, $role, $status) {
    global $koneksi;
    $nama = trim((string)$nama);
    $username = trim((string)$username);
    $password = (string)$password;
    $role = (string)$role;
    $status = (string)$status;
    if ($nama === '' || (function_exists('mb_strlen') ? mb_strlen($nama, 'UTF-8') : strlen($nama)) > 50 || $username === '' || (function_exists('mb_strlen') ? mb_strlen($username, 'UTF-8') : strlen($username)) > 50 || strlen($password) < 8 || !in_array($role, ['admin', 'petugas', 'owner'], true) || !in_array($status, ['aktif', 'nonaktif'], true)) return false;
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($koneksi, "INSERT INTO users (nama_lengkap, username, password, role, status_aktif) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sssss', $nama, $username, $hash, $role, $status);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function ubah_status_user_data($id, $status) {
    global $koneksi;
    $id = (int)$id;
    $status = (string)$status;
    if ($id < 1 || !in_array($status, ['aktif', 'nonaktif'], true)) return false;
    if (isset($_SESSION['id_user']) && $id === (int)$_SESSION['id_user'] && $status !== 'aktif') return false;
    $stmt = mysqli_prepare($koneksi, "UPDATE users SET status_aktif = ? WHERE id_user = ?");
    mysqli_stmt_bind_param($stmt, 'si', $status, $id);
    $ok = mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $ok;
}

// Kompatibilitas untuk controller lama: "hapus" sekarang menonaktifkan akun,
// karena penghapusan fisik akan memutus relasi dengan log aktivitas.
function hapus_user_data($id) {
    return ubah_status_user_data($id, 'nonaktif');
}
?>