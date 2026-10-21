<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../model/admin.php';

if (function_exists('cek_akses')) {
    cek_akses(['admin']);
}

// Ambil data tarif dari Model
$tarif_list = get_all_tarif();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Kelola Tarif Parkir</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; color: #333; padding: 30px; }
        .container { max-width: 900px; margin: 0 auto; background: #ffffff; padding: 25px 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); }
        .btn-back { display: inline-block; color: #4a5568; text-decoration: none; font-weight: 600; font-size: 14px; margin-bottom: 20px; }
        .btn-back:hover { color: #3182ce; }
        h2 { color: #1a202c; font-size: 24px; font-weight: 700; margin-bottom: 25px; border-bottom: 2px solid #edf2f7; padding-bottom: 10px; }
        
        .form-tarif { display: flex; gap: 12px; background-color: #f8fafc; padding: 18px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 30px; align-items: center; }
        .form-tarif input { flex: 1; padding: 10px 14px; border: 1px solid #cbd5e0; border-radius: 6px; font-size: 14px; outline: none; background: #fff; }
        .form-tarif input:focus { border-color: #3182ce; box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.15); }
        .btn-submit { padding: 10px 20px; background-color: #3182ce; color: #ffffff; border: none; border-radius: 6px; font-weight: 600; font-size: 14px; cursor: pointer; }
        .btn-submit:hover { background-color: #2b6cb0; }
        
        table { width: 100%; border-collapse: collapse; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        table th, table td { padding: 14px 16px; text-align: left; font-size: 14px; }
        table th { background-color: #edf2f7; color: #4a5568; font-weight: 700; font-size: 12px; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
        table tr { border-bottom: 1px solid #edf2f7; }
        table tbody tr:hover { background-color: #f7fafc; }
        
        .btn-edit { color: #d69e2e; text-decoration: none; font-weight: 600; padding: 6px 12px; background-color: #fefcbf; border: 1px solid #faf089; border-radius: 4px; font-size: 13px; margin-right: 5px; cursor: pointer; }
        .btn-edit:hover { background-color: #d69e2e; color: #fff; }
        .btn-delete { color: #e53e3e; text-decoration: none; font-weight: 600; padding: 6px 12px; background-color: #fff5f5; border: 1px solid #fed7d7; border-radius: 4px; font-size: 13px; }
        .btn-delete:hover { background-color: #e53e3e; color: #ffffff; }

        /* Modal Styles */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4); }
        .modal-content { background-color: #fff; margin: 15% auto; padding: 20px; border-radius: 8px; width: 400px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        .modal-content h3 { margin-bottom: 15px; }
        .modal-content input { width: 100%; padding: 10px; margin-bottom: 12px; border: 1px solid #cbd5e0; border-radius: 6px; }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; }
        .btn-cancel { padding: 8px 16px; background-color: #e2e8f0; border: none; border-radius: 6px; cursor: pointer; }
    </style>
    <link rel="stylesheet" href="../assets/app.css">
</head>
<body>

<div class="container">
    <a href="dashboard.php" class="btn-back">← Kembali ke Dashboard</a>
    <h2>Kelola Tarif Parkir</h2>

    <!-- FORM TAMBAH TARIF -->
    <form action="../controller/admincontroller.php?aksi=tambah_tarif" method="POST" class="form-tarif">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
        <select name="jenis_kendaraan" required aria-label="Jenis kendaraan"><option value="motor">Motor</option><option value="mobil">Mobil</option><option value="lainnya">Lainnya</option></select>
        <input type="number" name="tarif_per_jam" placeholder="Tarif per Jam (Rp)" min="1" step="1" required>
        <button type="submit" class="btn-submit">Tambah Tarif</button>
    </form>

    <!-- TABEL DATA TARIF -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Jenis Kendaraan</th>
                <th>Tarif / Jam</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($tarif_list && mysqli_num_rows($tarif_list) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($tarif_list)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id_tarif']); ?></td>
                        <td><?= htmlspecialchars($row['jenis_kendaraan']); ?></td>
                        <td>Rp <?= number_format($row['tarif_per_jam'], 0, ',', '.'); ?></td>
                        <td>
                            <button type="button" class="btn-edit" 
                                    onclick="openEditModal(<?= (int)$row['id_tarif']; ?>, <?= htmlspecialchars(json_encode($row['jenis_kendaraan']), ENT_QUOTES, 'UTF-8'); ?>, <?= (int)$row['tarif_per_jam']; ?>)">
                                Edit
                            </button>
                            <form action="../controller/admincontroller.php?aksi=hapus_tarif&id=<?= (int)$row['id_tarif']; ?>" method="POST" style="display:inline" onsubmit="return confirm('Yakin ingin menghapus tarif ini? Tarif yang sudah dipakai tidak dapat dihapus.')">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="btn-delete">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align: center;">Belum ada data tarif.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- MODAL EDIT TARIF -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>Edit Tarif Parkir</h3>
        <form action="../controller/admincontroller.php?aksi=edit_tarif" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="id_tarif" id="edit_id_tarif">
            <label style="font-size: 12px; font-weight: 600;">Jenis Kendaraan</label>
            <select name="jenis_kendaraan" id="edit_jenis_kendaraan" required><option value="motor">Motor</option><option value="mobil">Mobil</option><option value="lainnya">Lainnya</option></select>
            <label style="font-size: 12px; font-weight: 600;">Tarif per Jam (Rp)</label>
            <input type="number" name="tarif_per_jam" id="edit_tarif_per_jam" min="1" step="1" required>
            
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Batal</button>
                <button type="submit" class="btn-submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, jenis, tarif) {
    document.getElementById('edit_id_tarif').value = id;
    document.getElementById('edit_jenis_kendaraan').value = jenis;
    document.getElementById('edit_tarif_per_jam').value = tarif;
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

window.onclick = function(event) {
    var modal = document.getElementById('editModal');
    if (event.target == modal) {
        closeEditModal();
    }
}
</script>

</body>
</html>