<?php
require_once __DIR__ . '/../config/koneksi.php';
cek_akses(['petugas', 'admin']);

$id = (int)($_GET['id'] ?? 0);
$stmt = mysqli_prepare($koneksi, "SELECT k.*, a.nama_area, COALESCE(k.tarif_per_jam_snapshot, t.tarif_per_jam, tf.tarif_per_jam) AS tarif_per_jam FROM kendaraan k LEFT JOIN area_parkir a ON a.id_area = k.id_area LEFT JOIN tarif t ON t.id_tarif = NULLIF(k.id_tarif, 0) LEFT JOIN tarif tf ON tf.id_tarif = (SELECT MAX(t2.id_tarif) FROM tarif t2 WHERE LOWER(t2.jenis_kendaraan) = LOWER(k.jenis_kendaraan)) WHERE k.id_kendaraan = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$data) {
    http_response_code(404);
    exit('Data parkir tidak ditemukan.');
}
if ($data['status'] !== 'keluar') {
    exit('Struk belum tersedia karena kendaraan belum diproses keluar.');
}
$durasi = 0;
if (!empty($data['jam_masuk']) && !empty($data['jam_keluar'])) {
    $seconds = max(0, strtotime($data['jam_keluar']) - strtotime($data['jam_masuk']));
    $durasi = max(1, (int)ceil($seconds / 3600));
}
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Parkir - <?= e($data['plat_nomor']); ?></title>
    <link rel="stylesheet" href="../assets/app.css">
    <style>
      body { min-height: 100vh; display: grid; place-items: start center; padding: 24px; }
      .receipt { width: min(100%, 360px); padding: 24px; background: #fff; border: 1px dashed #94a3b8; border-radius: 12px; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }
      .receipt h1 { margin: 0 0 4px; text-align: center; font-size: 20px; }
      .receipt .sub { margin: 0 0 18px; text-align: center; color: #64748b; font-size: 12px; }
      .receipt-line { display: flex; justify-content: space-between; gap: 12px; margin: 8px 0; font-size: 13px; }
      .receipt-line span:last-child { text-align: right; overflow-wrap: anywhere; }
      .receipt-total { margin-top: 16px; padding-top: 14px; border-top: 1px dashed #94a3b8; font-size: 16px; font-weight: 800; }
      .receipt-actions { display: flex; gap: 8px; margin-top: 18px; }
      .receipt-actions a, .receipt-actions button { flex: 1; text-align: center; text-decoration: none; }
      @media print { body { display: block; padding: 0; } .receipt { width: 100%; border: 0; padding: 0; } .receipt-actions { display: none; } }
    </style>
</head>
<body>
  <main class="receipt">
    <h1>STRUK PARKIR</h1>
    <p class="sub">Bukti pembayaran parkir</p>
    <div class="receipt-line"><span>No. Transaksi</span><span>PKR-<?= (int)$data['id_kendaraan']; ?></span></div>
    <div class="receipt-line"><span>Plat nomor</span><strong><?= e($data['plat_nomor']); ?></strong></div>
    <div class="receipt-line"><span>Kendaraan</span><span><?= e(ucfirst($data['jenis_kendaraan'])); ?></span></div>
    <div class="receipt-line"><span>Area</span><span><?= e($data['nama_area'] ?? '-'); ?></span></div>
    <div class="receipt-line"><span>Masuk</span><span><?= e($data['jam_masuk']); ?></span></div>
    <div class="receipt-line"><span>Keluar</span><span><?= e($data['jam_keluar']); ?></span></div>
    <div class="receipt-line"><span>Durasi tagih</span><span><?= $durasi; ?> jam</span></div>
    <div class="receipt-line"><span>Tarif / jam</span><span>Rp <?= number_format((int)($data['tarif_per_jam'] ?? 0), 0, ',', '.'); ?></span></div>
    <div class="receipt-line receipt-total"><span>TOTAL</span><span>Rp <?= number_format((int)$data['total_bayar'], 0, ',', '.'); ?></span></div>
    <p class="sub" style="margin:18px 0 0">Terima kasih, hati-hati di jalan.</p>
    <div class="receipt-actions no-print"><button type="button" onclick="window.print()">Cetak struk</button><a class="btn-back" href="transaksi_petugas.php">Kembali</a></div>
  </main>
</body>
</html>
