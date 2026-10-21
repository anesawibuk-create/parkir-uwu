<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../model/parkir.php';
cek_akses(['petugas', 'admin']);

$aksi = $_GET['aksi'] ?? '';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Gunakan form untuk melakukan perubahan data.'); }
if (!verify_csrf_token($_POST['csrf_token'] ?? null)) { http_response_code(403); exit('Token keamanan tidak valid. Muat ulang halaman dan coba lagi.'); }
$ok = false;
$message = 'Permintaan tidak valid.';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $aksi === 'tambah_kendaraan') {
    $plat = $_POST['plat_nomor'] ?? '';
    $jenis = $_POST['jenis_kendaraan'] ?? '';
    $id_area = $_POST['id_area'] ?? 0;
    $ok = insert_kendaraan($plat, $jenis, $id_area);
    $message = $ok ? 'Kendaraan berhasil dicatat masuk.' : 'Gagal mencatat kendaraan. Periksa plat, tarif, area, dan kapasitas.';
    if ($ok) catat_log($_SESSION['id_user'], 'Mencatat kendaraan masuk: ' . strtoupper(trim($plat)));
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $aksi === 'keluar_kendaraan') {
    $id = (int)($_POST['id'] ?? 0);
    $ok = $id > 0 && keluar_kendaraan($id);
    $message = $ok ? 'Kendaraan berhasil diproses keluar.' : 'Gagal memproses keluar. Kendaraan mungkin sudah keluar atau tarif belum tersedia.';
    if ($ok) catat_log($_SESSION['id_user'], 'Mencatat kendaraan keluar ID: ' . $id);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $aksi === 'hapus_kendaraan') {
    $id = (int)($_POST['id'] ?? 0);
    $ok = $id > 0 && delete_kendaraan($id);
    $message = 'Riwayat parkir disimpan untuk menjaga akurasi laporan pendapatan dan tidak dapat dihapus melalui halaman ini.';
    if ($ok) catat_log($_SESSION['id_user'], 'Menghapus riwayat kendaraan ID: ' . $id);
}

$redirect = '../view/transaksi_petugas.php?info=' . ($ok ? 'success' : 'error') . '&msg=' . rawurlencode($message);
header('Location: ' . $redirect);
exit;
?>