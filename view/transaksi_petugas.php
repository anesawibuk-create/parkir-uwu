<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/koneksi.php';
cek_akses(['petugas', 'admin']);
require_once __DIR__ . '/../model/parkir.php';

$list_kendaraan = get_all_kendaraan();
$list_area      = get_all_area();
$notice = (string)($_GET['msg'] ?? '');
$notice_type = (($_GET['info'] ?? '') === 'success') ? 'alert-success' : 'alert-error';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Kelola Kendaraan Parkir</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background-color: #f4f6f9; padding: 30px; }
        .container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .btn-back { display: inline-block; color: #4a5568; text-decoration: none; font-weight: 600; font-size: 14px; margin-bottom: 20px; }
        h2 { color: #1a202c; font-size: 24px; font-weight: 700; margin-bottom: 25px; }
        
        .form-kendaraan { display: flex; gap: 10px; background: #f8fafc; padding: 18px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 30px; align-items: center; }
        .form-kendaraan input, .form-kendaraan select { flex: 1; padding: 10px; border: 1px solid #cbd5e0; border-radius: 6px; font-size: 14px; }
        .btn-submit { padding: 10px 24px; background: #3182ce; color: #fff; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
        
        table { width: 100%; border-collapse: collapse; background: #fff; }
        table th, table td { padding: 12px 14px; text-align: left; font-size: 14px; }
        table th { background: #edf2f7; color: #4a5568; font-size: 12px; text-transform: uppercase; }
        table tr { border-bottom: 1px solid #edf2f7; }
        
        .badge-parkir { color: #2b6cb0; background: #ebf8ff; padding: 4px 10px; border-radius: 4px; font-weight: 600; font-size: 12px; }
        .badge-keluar { color: #c53030; background: #fff5f5; padding: 4px 10px; border-radius: 4px; font-weight: 600; font-size: 12px; }
        .btn-delete { color: #e53e3e; padding: 5px 12px; background: #fff5f5; border: 1px solid #fed7d7; border-radius: 4px; text-decoration: none; font-size: 13px; }
        .btn-keluar { color: #2f855a; padding: 5px 12px; background: #f0fff4; border: 1px solid #c6f6d5; border-radius: 4px; text-decoration: none; font-size: 13px; margin-right: 6px; }
    </style>
    <link rel="stylesheet" href="../assets/app.css">
</head>
<body>

<div class="container">
    <a href="dashboard.php" class="btn-back">← Kembali ke Dashboard</a>
    <h2>Kelola Kendaraan Parkir</h2>
    <?php if ($notice !== ''): ?><div class="alert <?= $notice_type; ?>" role="status"><?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
    <div class="toolbar" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:16px;align-items:center">
      <input type="search" id="searchKendaraan" placeholder="Cari plat atau area..." aria-label="Cari kendaraan" style="flex:1;min-width:220px">
      <span style="color:#64748b;font-size:13px">Tarif kendaraan mengikuti tarif yang tercatat saat masuk.</span>
    </div>

    <!-- FORM HANYA PLAT, JENIS, DAN AREA -->
    <form action="../controller/petugas.php?aksi=tambah_kendaraan" method="POST" class="form-kendaraan">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
        <input type="text" name="plat_nomor" placeholder="PLAT NOMOR" maxlength="15" required autocomplete="off" style="text-transform: uppercase;">
        
        <select name="jenis_kendaraan" required>
            <option value="motor">Motor</option>
            <option value="mobil">Mobil</option>
        </select>

        <select name="id_area" required>
            <option value="">-- Pilih Area --</option>
            <?php 
            mysqli_data_seek($list_area, 0);
            while($a = mysqli_fetch_assoc($list_area)): 
            ?>
                <option value="<?= (int)$a['id_area']; ?>" <?= ((int)$a['terisi'] >= (int)$a['kapasitas'] || ($a['status'] ?? 'aktif') !== 'aktif') ? 'disabled' : ''; ?>><?= htmlspecialchars($a['nama_area']); ?> (<?= (int)$a['terisi']; ?>/<?= (int)$a['kapasitas']; ?>)<?= (($a['status'] ?? 'aktif') !== 'aktif') ? ' — nonaktif' : (((int)$a['terisi'] >= (int)$a['kapasitas']) ? ' — penuh' : ''); ?></option>
            <?php endwhile; ?>
        </select>

        <button type="submit" class="btn-submit">Tambah</button>
    </form>

    <!-- TABEL KENDARAAN PARKIR -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>PLAT NOMOR</th>
                <th>JENIS</th>
                <th>AREA</th>
                <th>TARIF / JAM</th>
                <th>JAM MASUK</th>
                <th>STATUS</th>
                <th>AKSI</th>
            </tr>
        </thead>
        <tbody>
            
          <?php if ($list_kendaraan && mysqli_num_rows($list_kendaraan) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($list_kendaraan)): ?>
            <tr>
                <td><?= $row['id_kendaraan']; ?></td>
                <td><strong><?= htmlspecialchars($row['plat_nomor'] ?? ''); ?></strong></td>
                <td><?= ucfirst(htmlspecialchars($row['jenis_kendaraan'] ?? '')); ?></td>
                <td><strong><?= htmlspecialchars($row['nama_area'] ?? '-'); ?></strong></td>
                <td><?= !empty($row['tarif_per_jam']) ? 'Rp ' . number_format((int)$row['tarif_per_jam'], 0, ',', '.') : '-'; ?></td>
                <td><?= !empty($row['jam_masuk']) ? date('d/m/Y H:i', strtotime($row['jam_masuk'])) : '-'; ?></td>
                <td>
                    <span class="<?= ($row['status'] ?? 'parkir') == 'parkir' ? 'badge-parkir' : 'badge-keluar'; ?>">
                        <?= ucfirst(htmlspecialchars($row['status'] ?? 'parkir')); ?>
                    </span>
                </td>
                <td>
                    <?php if (($row['status'] ?? 'parkir') == 'parkir'): ?>
                        <form action="../controller/petugas.php?aksi=keluar_kendaraan" method="POST" style="display:inline" onsubmit="return confirm('Proses kendaraan ini keluar dan hitung biayanya?')">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="id" value="<?= (int)$row['id_kendaraan']; ?>">
                            <button type="submit" class="btn-keluar">Keluar</button>
                        </form>
                    <?php endif; ?>
                    <?php if (($row['status'] ?? 'parkir') === 'keluar'): ?>
                        <a class="btn-edit" href="struk_petugas.php?id=<?= (int)$row['id_kendaraan']; ?>" target="_blank" rel="noopener">Struk</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="8" style="text-align: center;">Belum ada data kendaraan.</td>
        </tr>
    <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
(function () {
  const search = document.getElementById('searchKendaraan');
  if (!search) return;
  search.addEventListener('input', function () {
    const needle = this.value.toLocaleLowerCase('id-ID').trim();
    document.querySelectorAll('table tbody tr').forEach(function (row) {
      row.hidden = !row.innerText.toLocaleLowerCase('id-ID').includes(needle);
    });
  });
})();
</script>
</body>
</html>