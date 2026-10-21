<?php
require_once __DIR__ . '/../config/koneksi.php';

function get_all_tarif() {
    global $koneksi;
    return mysqli_query($koneksi, "SELECT * FROM tarif ORDER BY id_tarif DESC");
}

function insert_tarif($jenis, $tarif_per_jam) {
    global $koneksi;
    $jenis = strtolower(trim((string)$jenis));
    $tarif_per_jam = filter_var($tarif_per_jam, FILTER_VALIDATE_INT);
    if (!in_array($jenis, ['motor', 'mobil', 'lainnya'], true) || $tarif_per_jam === false || $tarif_per_jam <= 0) return false;
    $stmt = mysqli_prepare($koneksi, "INSERT INTO tarif (jenis_kendaraan, tarif_per_jam) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, 'si', $jenis, $tarif_per_jam);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function update_tarif($id_tarif, $jenis, $tarif_per_jam) {
    global $koneksi;
    $id_tarif = (int)$id_tarif;
    $jenis = strtolower(trim((string)$jenis));
    $tarif_per_jam = filter_var($tarif_per_jam, FILTER_VALIDATE_INT);
    if ($id_tarif < 1 || !in_array($jenis, ['motor', 'mobil', 'lainnya'], true) || $tarif_per_jam === false || $tarif_per_jam <= 0) return false;
    $stmt = mysqli_prepare($koneksi, "UPDATE tarif SET jenis_kendaraan = ?, tarif_per_jam = ? WHERE id_tarif = ?");
    mysqli_stmt_bind_param($stmt, 'sii', $jenis, $tarif_per_jam, $id_tarif);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function delete_tarif($id_tarif) {
    global $koneksi;
    $id_tarif = (int)$id_tarif;
    $stmt = mysqli_prepare($koneksi, "DELETE FROM tarif WHERE id_tarif = ? AND id_tarif NOT IN (SELECT DISTINCT id_tarif FROM kendaraan WHERE id_tarif IS NOT NULL AND id_tarif <> 0)");
    mysqli_stmt_bind_param($stmt, 'i', $id_tarif);
    $ok = mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $ok;
}

function get_pendapatan_hari_ini() {
    global $koneksi;
    $result = mysqli_query($koneksi, "SELECT COALESCE(SUM(total_bayar), 0) AS total FROM kendaraan WHERE status = 'keluar' AND DATE(jam_keluar) = CURDATE()");
    $data = $result ? mysqli_fetch_assoc($result) : null;
    return (int)($data['total'] ?? 0);
}

function get_rekap_pendapatan($tgl_mulai = '', $tgl_sampai = '') {
    global $koneksi;
    $query = "SELECT k.*, COALESCE(NULLIF(k.jenis_kendaraan, ''), t.jenis_kendaraan, '-') AS nama_jenis
              FROM kendaraan k
              LEFT JOIN tarif t ON t.id_tarif = NULLIF(k.id_tarif, 0)
              WHERE k.status = 'keluar'";
    $params = [];
    $types = '';
    if ($tgl_mulai !== '' && $tgl_sampai !== '') {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$tgl_mulai) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$tgl_sampai)) return false;
        $start = DateTime::createFromFormat('!Y-m-d', (string)$tgl_mulai);
        $end = DateTime::createFromFormat('!Y-m-d', (string)$tgl_sampai);
        if (!$start || !$end || $start->format('Y-m-d') !== $tgl_mulai || $end->format('Y-m-d') !== $tgl_sampai || $start > $end) {
            return false;
        }
        $query .= " AND DATE(k.jam_keluar) BETWEEN ? AND ?";
        $params = [$tgl_mulai, $tgl_sampai];
        $types = 'ss';
    }
    $query .= " ORDER BY k.jam_keluar DESC, k.id_kendaraan DESC";
    if (!$params) return mysqli_query($koneksi, $query);
    $stmt = mysqli_prepare($koneksi, $query);
    $startDate = $params[0];
    $endDate = $params[1];
    mysqli_stmt_bind_param($stmt, 'ss', $startDate, $endDate);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

function get_all_area() {
    global $koneksi;
    return mysqli_query($koneksi, "SELECT a.*, (SELECT COUNT(*) FROM kendaraan k WHERE k.id_area = a.id_area AND k.status = 'parkir') AS terisi FROM area_parkir a ORDER BY a.id_area DESC");
}

function insert_area($nama_area, $kapasitas) {
    global $koneksi;
    $nama_area = trim((string)$nama_area);
    $kapasitas = filter_var($kapasitas, FILTER_VALIDATE_INT);
    if ($nama_area === '' || (function_exists('mb_strlen') ? mb_strlen($nama_area, 'UTF-8') : strlen($nama_area)) > 50 || $kapasitas === false || $kapasitas < 1) return false;
    $stmt = mysqli_prepare($koneksi, "INSERT INTO area_parkir (nama_area, kapasitas) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, 'si', $nama_area, $kapasitas);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function update_area($id_area, $nama_area, $kapasitas) {
    global $koneksi;
    $id_area = (int)$id_area;
    $nama_area = trim((string)$nama_area);
    $kapasitas = filter_var($kapasitas, FILTER_VALIDATE_INT);
    if ($id_area < 1 || $nama_area === '' || (function_exists('mb_strlen') ? mb_strlen($nama_area, 'UTF-8') : strlen($nama_area)) > 50 || $kapasitas === false || $kapasitas < 1) return false;
    $count_stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) AS n FROM kendaraan WHERE id_area = ? AND status = 'parkir'");
    mysqli_stmt_bind_param($count_stmt, 'i', $id_area);
    mysqli_stmt_execute($count_stmt);
    $occupied = (int)(mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt))['n'] ?? 0);
    mysqli_stmt_close($count_stmt);
    if ($kapasitas < $occupied) return false;
    $stmt = mysqli_prepare($koneksi, "UPDATE area_parkir SET nama_area = ?, kapasitas = ? WHERE id_area = ?");
    mysqli_stmt_bind_param($stmt, 'sii', $nama_area, $kapasitas, $id_area);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function delete_area($id_area) {
    global $koneksi;
    $id_area = (int)$id_area;
    $check = mysqli_prepare($koneksi, "SELECT COUNT(*) AS n FROM kendaraan WHERE id_area = ?");
    mysqli_stmt_bind_param($check, 'i', $id_area);
    mysqli_stmt_execute($check);
    $active = (int)(mysqli_fetch_assoc(mysqli_stmt_get_result($check))['n'] ?? 0);
    mysqli_stmt_close($check);
    if ($active > 0) return false;
    $stmt = mysqli_prepare($koneksi, "DELETE FROM area_parkir WHERE id_area = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id_area);
    $ok = mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $ok;
}

function get_all_logs() {
    global $koneksi;
    return mysqli_query($koneksi, "SELECT l.*, u.username FROM log_aktivitas l JOIN users u ON l.id_user = u.id_user ORDER BY l.id_log DESC");
}

if (!function_exists('catat_log')) {
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
}
?>
