<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/koneksi.php';
cek_akses(['owner', 'admin']);
require_once __DIR__ . '/../model/admin.php';

$today = date('Y-m-d');
$tgl_mulai = (string)($_GET['tgl_mulai'] ?? $today);
$tgl_sampai = (string)($_GET['tgl_sampai'] ?? $today);
$validDate = static function ($value) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) return false;
    $date = DateTime::createFromFormat('!Y-m-d', $value);
    return $date && $date->format('Y-m-d') === $value;
};
if (!$validDate($tgl_mulai)) $tgl_mulai = $today;
if (!$validDate($tgl_sampai)) $tgl_sampai = $today;
if ($tgl_mulai > $tgl_sampai) { $temp = $tgl_mulai; $tgl_mulai = $tgl_sampai; $tgl_sampai = $temp; }
$rekap_data = get_rekap_pendapatan($tgl_mulai, $tgl_sampai);
$grand_total = 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Rekap Pendapatan Parkir</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; color: #333; padding: 30px; }
        .container { max-width: 1000px; margin: 0 auto; background: #ffffff; padding: 25px 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); }
        .btn-back { display: inline-block; color: #4a5568; text-decoration: none; font-weight: 600; font-size: 14px; margin-bottom: 20px; }
        h2 { color: #1a202c; font-size: 24px; font-weight: 700; margin-bottom: 25px; border-bottom: 2px solid #edf2f7; padding-bottom: 10px; }
        
        .filter-form { display: flex; gap: 15px; background-color: #f8fafc; padding: 18px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 30px; align-items: center; }
        .filter-form input[type="date"] { padding: 8px 12px; border: 1px solid #cbd5e0; border-radius: 6px; }
        .btn-filter { padding: 9px 18px; background-color: #3182ce; color: #fff; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
        
        table { width: 100%; border-collapse: collapse; background-color: #ffffff; border-radius: 8px; overflow: hidden; }
        table th, table td { padding: 12px 16px; text-align: left; font-size: 14px; }
        table th { background-color: #edf2f7; color: #4a5568; font-weight: 700; font-size: 12px; text-transform: uppercase; }
        table tr { border-bottom: 1px solid #edf2f7; }
        
        .grand-total-row { background-color: #ebf8ff; font-weight: bold; color: #2b6cb0; font-size: 16px; }
    </style>
    <link rel="stylesheet" href="../assets/app.css">
</head>
<body>

<div class="container">
    <a href="dashboard.php" class="btn-back">← Kembali ke Dashboard</a>
    <h2>Rekap Pendapatan Parkir</h2>

    <form method="GET" action="" class="filter-form">
        <label>Mulai:</label>
        <input type="date" name="tgl_mulai" value="<?= htmlspecialchars($tgl_mulai, ENT_QUOTES, 'UTF-8'); ?>">
        <label>Sampai:</label>
        <input type="date" name="tgl_sampai" value="<?= htmlspecialchars($tgl_sampai, ENT_QUOTES, 'UTF-8'); ?>">
        <button type="submit" class="btn-filter">Filter Tanggal</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Plat Nomor</th>
                <th>Jenis Kendaraan (Tarif Admin)</th>
                <th>Masuk</th>
                <th>Keluar</th>
                <th>Biaya</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rekap_data && mysqli_num_rows($rekap_data) > 0): ?>
                <?php $no = 1; while ($row = mysqli_fetch_assoc($rekap_data)): ?>
                    <?php $grand_total += $row['total_bayar']; ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><strong><?= htmlspecialchars($row['plat_nomor']); ?></strong></td>
                        <td><?= ucfirst(htmlspecialchars($row['nama_jenis'])); ?></td>
                        <td><?= !empty($row['jam_masuk']) ? date('d/m/Y H:i', strtotime($row['jam_masuk'])) : '-'; ?></td>
                        <td><?= !empty($row['jam_keluar']) ? date('d/m/Y H:i', strtotime($row['jam_keluar'])) : '-'; ?></td>
                        <td>Rp <?= number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endwhile; ?>
                <tr class="grand-total-row">
                    <td colspan="5" style="text-align: right;">GRAND TOTAL PENDAPATAN</td>
                    <td>Rp <?= number_format($grand_total, 0, ',', '.'); ?></td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: #718096; padding: 20px;">Data transaksi tidak ditemukan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>