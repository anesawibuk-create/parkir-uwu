<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../model/admin.php';
require_once __DIR__ . '/../model/parkir.php';

// Pastikan session aktif sebelum membaca $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

cek_akses(['admin']);
$aksi = $_GET['aksi'] ?? '';
$aksi_tulis = [
    'tambah_user', 'hapus_user', 'tambah_tarif', 'edit_tarif', 'hapus_tarif',
    'tambah_area', 'edit_area', 'hapus_area', 'tambah_kendaraan', 'edit_kendaraan', 'hapus_kendaraan'
];
if (in_array($aksi, $aksi_tulis, true)) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Gunakan form untuk melakukan perubahan data.'); }
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) { http_response_code(403); exit('Token keamanan tidak valid. Muat ulang halaman dan coba lagi.'); }
}


// --- KELOLA USER ---
if ($aksi == 'tambah_user') {
    $nama   = $_POST['nama_lengkap'];
    $user   = $_POST['username'];
    $pass   = $_POST['password'];
    $role   = $_POST['role'];
    $status = $_POST['status_aktif'];

    tambah_user_data($nama, $user, $pass, $role, $status);
    
    if (function_exists('catat_log') && isset($_SESSION['id_user'])) {
        catat_log($_SESSION['id_user'], "Menambah user baru: $user");
    }

    header("Location: ../view/adminview.php");
    exit;
}

if ($aksi == 'hapus_user') {
    $id = (int)($_GET['id'] ?? 0);
    
    hapus_user_data($id);
    
    if (function_exists('catat_log') && isset($_SESSION['id_user'])) {
        catat_log($_SESSION['id_user'], "Menonaktifkan user ID: $id");
    }

    header("Location: ../view/adminview.php");
    exit;
}


// --- KELOLA TARIF ---
if ($aksi == 'tambah_tarif') {
    $jenis = $_POST['jenis_kendaraan'];
    $tarif = $_POST['tarif_per_jam'];

    if (insert_tarif($jenis, $tarif)) {
        if (function_exists('catat_log') && isset($_SESSION['id_user'])) {
            catat_log($_SESSION['id_user'], "Menambah tarif kendaraan $jenis");
        }
        echo "<script>alert('Tarif berhasil ditambahkan!'); window.location.href='../view/admintarif.php';</script>";
    } else {
        echo "<script>alert('Gagal menambah tarif!'); window.location.href='../view/admintarif.php';</script>";
    }
    exit;
}

if ($aksi == 'edit_tarif') {
    $id    = $_POST['id_tarif'];
    $jenis = $_POST['jenis_kendaraan'];
    $tarif = $_POST['tarif_per_jam'];

    if (update_tarif($id, $jenis, $tarif)) {
        if (function_exists('catat_log') && isset($_SESSION['id_user'])) {
            catat_log($_SESSION['id_user'], "Mengubah tarif ID: $id");
        }
        echo "<script>alert('Tarif berhasil diperbarui!'); window.location.href='../view/admintarif.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui tarif!'); window.location.href='../view/admintarif.php';</script>";
    }
    exit;
}

if ($aksi == 'hapus_tarif') {
    $id = (int)($_GET['id'] ?? 0);

    if (delete_tarif($id)) {
        if (function_exists('catat_log') && isset($_SESSION['id_user'])) {
            catat_log($_SESSION['id_user'], "Menghapus tarif ID: $id");
        }
        echo "<script>alert('Tarif berhasil dihapus!'); window.location.href='../view/admintarif.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus tarif!'); window.location.href='../view/admintarif.php';</script>";
    }
    exit;
}


// --- KELOLA AREA PARKIR ---
if ($aksi == 'tambah_area') {
    $nama_area = $_POST['nama_area'];
    $kapasitas = $_POST['kapasitas'];

    if (insert_area($nama_area, $kapasitas)) {
        if (function_exists('catat_log') && isset($_SESSION['id_user'])) {
            catat_log($_SESSION['id_user'], "Menambah area parkir: $nama_area");
        }
        echo "<script>alert('Area parkir berhasil ditambahkan!'); window.location.href='../view/adminarea.php';</script>";
    } else {
        echo "<script>alert('Gagal menambah area parkir!'); window.location.href='../view/adminarea.php';</script>";
    }
    exit;
}

if ($aksi == 'edit_area') {
    $id_area   = $_POST['id_area'];
    $nama_area = $_POST['nama_area'];
    $kapasitas = $_POST['kapasitas'];

    if (update_area($id_area, $nama_area, $kapasitas)) {
        if (function_exists('catat_log') && isset($_SESSION['id_user'])) {
            catat_log($_SESSION['id_user'], "Mengubah area parkir ID: $id_area");
        }
        echo "<script>alert('Area parkir berhasil diperbarui!'); window.location.href='../view/adminarea.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui area parkir!'); window.location.href='../view/adminarea.php';</script>";
    }
    exit;
}

if ($aksi == 'hapus_area') {
    $id_area = (int)($_GET['id'] ?? 0);

    if (delete_area($id_area)) {
        if (function_exists('catat_log') && isset($_SESSION['id_user'])) {
            catat_log($_SESSION['id_user'], "Menghapus area parkir ID: $id_area");
        }
        echo "<script>alert('Area parkir berhasil dihapus!'); window.location.href='../view/adminarea.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus area parkir!'); window.location.href='../view/adminarea.php';</script>";
    }
    exit;
}


// --- KELOLA KENDARAAN PARKIR (DENGAN AREA PARKIR, TANPA WARNA/PEMILIK) ---
if ($aksi == 'tambah_kendaraan') {
    $plat    = $_POST['plat_nomor'] ?? '';
    $jenis   = $_POST['jenis_kendaraan'] ?? '';
    $id_area = $_POST['id_area'] ?? 0;

    if (insert_kendaraan($plat, $jenis, $id_area)) {
        echo "<script>alert('Data kendaraan berhasil ditambahkan!'); window.location.href='../view/transaksi_petugas.php';</script>";
    } else {
        echo "<script>alert('Gagal menambah kendaraan!'); window.location.href='../view/transaksi_petugas.php';</script>";
    }
    exit;
}

if ($aksi == 'edit_kendaraan') {
    $id      = $_POST['id_kendaraan'] ?? 0;
    $plat    = $_POST['plat_nomor'] ?? '';
    $jenis   = $_POST['jenis_kendaraan'] ?? '';
    $id_area = $_POST['id_area'] ?? 0;

    if (update_kendaraan($id, $plat, $jenis, $id_area)) {
        echo "<script>alert('Data kendaraan berhasil diperbarui!'); window.location.href='../view/transaksi_petugas.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui kendaraan!'); window.location.href='../view/transaksi_petugas.php';</script>";
    }
    exit;
}

if ($aksi == 'hapus_kendaraan') {
    $id = $_GET['id'] ?? 0;

    if (delete_kendaraan($id)) {
        echo "<script>alert('Data kendaraan berhasil dihapus!'); window.location.href='../view/transaksi_petugas.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus kendaraan!'); window.location.href='../view/transaksi_petugas.php';</script>";
    }
    exit;
}
?>