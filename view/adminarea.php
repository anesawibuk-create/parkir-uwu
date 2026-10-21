<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../model/admin.php';

if (function_exists('cek_akses')) {
    cek_akses(['admin']);
}

// Ambil data area dari Model
$area_list = get_all_area();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Kelola Area Parkir</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; color: #333; padding: 30px; }
        .container { max-width: 900px; margin: 0 auto; background: #ffffff; padding: 25px 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); }
        .btn-back { display: inline-block; color: #4a5568; text-decoration: none; font-weight: 600; font-size: 14px; margin-bottom: 20px; }
        .btn-back:hover { color: #3182ce; }
        h2 { color: #1a202c; font-size: 24px; font-weight: 700; margin-bottom: 25px; border-bottom: 2px solid #edf2f7; padding-bottom: 10px; }
        
        .form-area { display: flex; gap: 12px; background-color: #f8fafc; padding: 18px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 30px; align-items: center; }
        .form-area input { flex: 1; padding: 10px 14px; border: 1px solid #cbd5e0; border-radius: 6px; font-size: 14px; outline: none; background: #fff; }
        .form-area input:focus { border-color: #3182ce; box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.15); }
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
    <h2>Kelola Area Parkir</h2>

    <!-- FORM TAMBAH AREA -->
    <form action="../controller/admincontroller.php?aksi=tambah_area" method="POST" class="form-area">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
        <input type="text" name="nama_area" placeholder="Nama Area (misal: Lantai 1, Blok A)" maxlength="50" required autocomplete="off">
        <input type="number" name="kapasitas" placeholder="Kapasitas Kendaraan" min="1" step="1" required>
        <button type="submit" class="btn-submit">Tambah Area</button>
    </form>

    <!-- TABEL DATA AREA -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Area</th>
                <th>Terisi / Kapasitas</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($area_list && mysqli_num_rows($area_list) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($area_list)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id_area']); ?></td>
                        <td><?= htmlspecialchars($row['nama_area']); ?></td>
                        <td><strong><?= (int)$row['terisi']; ?> / <?= (int)$row['kapasitas']; ?></strong> kendaraan <span class="badge <?= ($row['status'] ?? 'aktif') === 'aktif' ? 'badge-aktif' : 'badge-nonaktif'; ?>"><?= htmlspecialchars($row['status'] ?? 'aktif'); ?></span></td>
                        <td>
                            <button type="button" class="btn-edit" 
                                    onclick="openEditModal(<?= (int)$row['id_area']; ?>, <?= htmlspecialchars(json_encode($row['nama_area']), ENT_QUOTES, 'UTF-8'); ?>, <?= (int)$row['kapasitas']; ?>)">
                                Edit
                            </button>
                            <form action="../controller/admincontroller.php?aksi=hapus_area&id=<?= (int)$row['id_area']; ?>" method="POST" style="display:inline" onsubmit="return confirm('Yakin ingin menghapus area parkir ini? Area yang masih digunakan tidak dapat dihapus.')">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="btn-delete">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align: center;">Belum ada data area parkir.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- MODAL EDIT AREA -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>Edit Area Parkir</h3>
        <form action="../controller/admincontroller.php?aksi=edit_area" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="id_area" id="edit_id_area">
            <label style="font-size: 12px; font-weight: 600;">Nama Area</label>
            <input type="text" name="nama_area" id="edit_nama_area" maxlength="50" required>
            <label style="font-size: 12px; font-weight: 600;">Kapasitas</label>
            <input type="number" name="kapasitas" id="edit_kapasitas" min="1" step="1" required>
            
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Batal</button>
                <button type="submit" class="btn-submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, nama, kapasitas) {
    document.getElementById('edit_id_area').value = id;
    document.getElementById('edit_nama_area').value = nama;
    document.getElementById('edit_kapasitas').value = kapasitas;
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