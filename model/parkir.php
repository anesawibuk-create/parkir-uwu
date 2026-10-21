<?php
require_once __DIR__ . '/../config/koneksi.php';

if (!function_exists('get_all_area')) {
    function get_all_area() {
        global $koneksi;
        // Kapasitas terpakai dihitung dari sesi parkir aktif, bukan kolom terisi yang bisa kedaluwarsa.
        return mysqli_query($koneksi, "SELECT a.*, (SELECT COUNT(*) FROM kendaraan k WHERE k.id_area = a.id_area AND k.status = 'parkir') AS terisi FROM area_parkir a ORDER BY a.nama_area ASC");
    }
}

function get_all_kendaraan() {
    global $koneksi;
    // Tarif kendaraan lama yang masih memiliki id_tarif=0 akan memakai tarif terbaru sebagai fallback.
    $query = "SELECT k.*, a.nama_area,
                     COALESCE(k.tarif_per_jam_snapshot, t.tarif_per_jam, tf.tarif_per_jam) AS tarif_per_jam,
                     COALESCE(t.id_tarif, tf.id_tarif) AS id_tarif_efektif
              FROM kendaraan k
              LEFT JOIN area_parkir a ON k.id_area = a.id_area
              LEFT JOIN tarif t ON t.id_tarif = NULLIF(k.id_tarif, 0)
              LEFT JOIN tarif tf ON tf.id_tarif = (
                  SELECT MAX(t2.id_tarif) FROM tarif t2
                  WHERE LOWER(t2.jenis_kendaraan) = LOWER(k.jenis_kendaraan)
              )
              ORDER BY k.id_kendaraan DESC";
    return mysqli_query($koneksi, $query);
}

function insert_kendaraan($plat_nomor, $jenis_kendaraan, $id_area = 0) {
    global $koneksi;
    $plat_nomor = strtoupper(trim((string)$plat_nomor));
    $plat_nomor = preg_replace('/\\s+/', ' ', $plat_nomor);
    $jenis_kendaraan = strtolower(trim((string)$jenis_kendaraan));
    $id_area = (int)$id_area;

    if ($plat_nomor === '' || strlen($plat_nomor) > 15 || !in_array($jenis_kendaraan, ['motor', 'mobil', 'lainnya'], true) || $id_area < 1) {
        return false;
    }

    mysqli_begin_transaction($koneksi);
    try {
        $area_stmt = mysqli_prepare($koneksi, "SELECT kapasitas, status FROM area_parkir WHERE id_area = ? FOR UPDATE");
        mysqli_stmt_bind_param($area_stmt, 'i', $id_area);
        mysqli_stmt_execute($area_stmt);
        $area = mysqli_fetch_assoc(mysqli_stmt_get_result($area_stmt));
        mysqli_stmt_close($area_stmt);
        if (!$area || ($area['status'] ?? 'aktif') !== 'aktif' || (int)$area['kapasitas'] < 1) {
            throw new RuntimeException('Area tidak tersedia.');
        }

        $plate_stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) AS n FROM kendaraan WHERE UPPER(plat_nomor) = ? AND status = 'parkir'");
        mysqli_stmt_bind_param($plate_stmt, 's', $plat_nomor);
        mysqli_stmt_execute($plate_stmt);
        $plate_count = (int)(mysqli_fetch_assoc(mysqli_stmt_get_result($plate_stmt))['n'] ?? 0);
        mysqli_stmt_close($plate_stmt);
        if ($plate_count > 0) {
            throw new RuntimeException('Kendaraan dengan plat tersebut masih terdaftar parkir.');
        }

        $count_stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) AS n FROM kendaraan WHERE id_area = ? AND status = 'parkir'");
        mysqli_stmt_bind_param($count_stmt, 'i', $id_area);
        mysqli_stmt_execute($count_stmt);
        $occupied = (int)(mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt))['n'] ?? 0);
        mysqli_stmt_close($count_stmt);
        if ($occupied >= (int)$area['kapasitas']) {
            throw new RuntimeException('Kapasitas area sudah penuh.');
        }

        $tarif_stmt = mysqli_prepare($koneksi, "SELECT id_tarif, tarif_per_jam FROM tarif WHERE LOWER(jenis_kendaraan) = ? ORDER BY id_tarif DESC LIMIT 1");
        mysqli_stmt_bind_param($tarif_stmt, 's', $jenis_kendaraan);
        mysqli_stmt_execute($tarif_stmt);
        $tarif = mysqli_fetch_assoc(mysqli_stmt_get_result($tarif_stmt));
        mysqli_stmt_close($tarif_stmt);
        if (!$tarif) {
            throw new RuntimeException('Tarif untuk jenis kendaraan ini belum tersedia.');
        }

        $id_tarif = (int)$tarif['id_tarif'];
        $tarif_snapshot = (int)$tarif['tarif_per_jam'];
        if ($tarif_snapshot <= 0) throw new RuntimeException('Tarif tidak valid.');
        $stmt = mysqli_prepare($koneksi, "INSERT INTO kendaraan (plat_nomor, id_tarif, tarif_per_jam_snapshot, jenis_kendaraan, id_area, status, jam_masuk, total_bayar) VALUES (?, ?, ?, ?, ?, 'parkir', NOW(), 0)");
        mysqli_stmt_bind_param($stmt, 'siisi', $plat_nomor, $id_tarif, $tarif_snapshot, $jenis_kendaraan, $id_area);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        if (!$ok) throw new RuntimeException('Gagal menyimpan kendaraan.');
        mysqli_commit($koneksi);
        return true;
    } catch (Throwable $e) {
        mysqli_rollback($koneksi);
        return false;
    }
}

function update_kendaraan($id_kendaraan, $plat_nomor, $jenis_kendaraan, $id_area = 0) {
    global $koneksi;
    $id_kendaraan = (int)$id_kendaraan;
    $plat_nomor = strtoupper(trim((string)$plat_nomor));
    $plat_nomor = preg_replace('/\\s+/', ' ', $plat_nomor);
    $jenis_kendaraan = strtolower(trim((string)$jenis_kendaraan));
    $id_area = (int)$id_area;
    if ($id_kendaraan < 1 || $plat_nomor === '' || strlen($plat_nomor) > 15 || !in_array($jenis_kendaraan, ['motor', 'mobil', 'lainnya'], true) || $id_area < 1) return false;

    $current_stmt = mysqli_prepare($koneksi, "SELECT status FROM kendaraan WHERE id_kendaraan = ?");
    mysqli_stmt_bind_param($current_stmt, 'i', $id_kendaraan);
    mysqli_stmt_execute($current_stmt);
    $current = mysqli_fetch_assoc(mysqli_stmt_get_result($current_stmt));
    mysqli_stmt_close($current_stmt);
    if (!$current || $current['status'] !== 'parkir') return false;

    $area_stmt = mysqli_prepare($koneksi, "SELECT kapasitas, status FROM area_parkir WHERE id_area = ?");
    mysqli_stmt_bind_param($area_stmt, 'i', $id_area);
    mysqli_stmt_execute($area_stmt);
    $area = mysqli_fetch_assoc(mysqli_stmt_get_result($area_stmt));
    mysqli_stmt_close($area_stmt);
    if (!$area || ($area['status'] ?? 'aktif') !== 'aktif') return false;
    $occupied_stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) AS n FROM kendaraan WHERE id_area = ? AND status = 'parkir' AND id_kendaraan <> ?");
    mysqli_stmt_bind_param($occupied_stmt, 'ii', $id_area, $id_kendaraan);
    mysqli_stmt_execute($occupied_stmt);
    $occupied = (int)(mysqli_fetch_assoc(mysqli_stmt_get_result($occupied_stmt))['n'] ?? 0);
    mysqli_stmt_close($occupied_stmt);
    if ($occupied >= (int)$area['kapasitas']) return false;

    $check_stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) AS n FROM kendaraan WHERE UPPER(plat_nomor) = ? AND status = 'parkir' AND id_kendaraan <> ?");
    mysqli_stmt_bind_param($check_stmt, 'si', $plat_nomor, $id_kendaraan);
    mysqli_stmt_execute($check_stmt);
    $duplicate = (int)(mysqli_fetch_assoc(mysqli_stmt_get_result($check_stmt))['n'] ?? 0);
    mysqli_stmt_close($check_stmt);
    if ($duplicate > 0) return false;

    $tarif_stmt = mysqli_prepare($koneksi, "SELECT id_tarif, tarif_per_jam FROM tarif WHERE LOWER(jenis_kendaraan) = ? ORDER BY id_tarif DESC LIMIT 1");
    mysqli_stmt_bind_param($tarif_stmt, 's', $jenis_kendaraan);
    mysqli_stmt_execute($tarif_stmt);
    $tarif = mysqli_fetch_assoc(mysqli_stmt_get_result($tarif_stmt));
    mysqli_stmt_close($tarif_stmt);
    if (!$tarif) return false;
    $id_tarif = (int)$tarif['id_tarif'];
    $tarif_snapshot = (int)$tarif['tarif_per_jam'];
    if ($tarif_snapshot <= 0) return false;

    $stmt = mysqli_prepare($koneksi, "UPDATE kendaraan SET plat_nomor = ?, jenis_kendaraan = ?, id_area = ?, id_tarif = ?, tarif_per_jam_snapshot = ? WHERE id_kendaraan = ? AND status = 'parkir'");
    mysqli_stmt_bind_param($stmt, 'ssiiii', $plat_nomor, $jenis_kendaraan, $id_area, $id_tarif, $tarif_snapshot, $id_kendaraan);
    $ok = mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) >= 0;
    mysqli_stmt_close($stmt);
    return $ok;
}

function delete_kendaraan($id_kendaraan) {
    // Sesi kendaraan yang sudah keluar menjadi sumber laporan pendapatan.
    // Hapus fisik akan menghilangkan jejak transaksi, sehingga penghapusan dinonaktifkan.
    return false;
}

function keluar_kendaraan($id_kendaraan) {
    global $koneksi;
    $id_kendaraan = (int)$id_kendaraan;
    if ($id_kendaraan < 1) return false;

    mysqli_begin_transaction($koneksi);
    try {
        $stmt = mysqli_prepare($koneksi, "SELECT k.id_kendaraan, k.id_tarif, k.jenis_kendaraan, k.jam_masuk, k.status, t.id_tarif AS tarif_valid, tf.id_tarif AS tarif_fallback, COALESCE(k.tarif_per_jam_snapshot, t.tarif_per_jam, tf.tarif_per_jam) AS tarif_per_jam, COALESCE(t.id_tarif, tf.id_tarif) AS id_tarif_efektif FROM kendaraan k LEFT JOIN tarif t ON t.id_tarif = NULLIF(k.id_tarif, 0) LEFT JOIN tarif tf ON tf.id_tarif = (SELECT MAX(t2.id_tarif) FROM tarif t2 WHERE LOWER(t2.jenis_kendaraan) = LOWER(k.jenis_kendaraan)) WHERE k.id_kendaraan = ? FOR UPDATE");
        mysqli_stmt_bind_param($stmt, 'i', $id_kendaraan);
        mysqli_stmt_execute($stmt);
        $data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        if (!$data || $data['status'] !== 'parkir' || empty($data['jam_masuk']) || $data['tarif_per_jam'] === null || (int)$data['tarif_per_jam'] <= 0 || empty($data['id_tarif_efektif'])) {
            throw new RuntimeException('Kendaraan atau tarif tidak valid.');
        }
        $id_tarif = (int)$data['id_tarif_efektif'];
        $tarif = (int)$data['tarif_per_jam'];
        // Hitung dengan NOW() database agar zona waktu masuk/keluar konsisten.
        $update = mysqli_prepare($koneksi, "UPDATE kendaraan SET id_tarif = ?, status = 'keluar', jam_keluar = NOW(), total_bayar = GREATEST(1, CEIL(GREATEST(0, TIMESTAMPDIFF(SECOND, jam_masuk, NOW())) / 3600)) * ? WHERE id_kendaraan = ? AND status = 'parkir'");
        mysqli_stmt_bind_param($update, 'iii', $id_tarif, $tarif, $id_kendaraan);
        $ok = mysqli_stmt_execute($update) && mysqli_stmt_affected_rows($update) === 1;
        mysqli_stmt_close($update);
        if (!$ok) throw new RuntimeException('Gagal memproses kendaraan keluar.');
        mysqli_commit($koneksi);
        return true;
    } catch (Throwable $e) {
        mysqli_rollback($koneksi);
        return false;
    }
}
?>
