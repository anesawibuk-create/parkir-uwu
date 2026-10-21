<?php
// 1. Matikan/Aktifkan error reporting jika perlu
error_reporting(E_ALL);
ini_set('display_errors', 0);

// 2. Load file koneksi dan model
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../model/user.php';

$user_error = false;

// 3. Cek akses login
if (function_exists('cek_akses')) {
    cek_akses(['admin']);
}

// -------------------------------------------------------------
// LE TAK KODE TAMBAH USER DI SINI (SEBELUM QUERY GET DATA)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verify_csrf_token($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Token keamanan tidak valid. Muat ulang halaman dan coba lagi.');
}

if (isset($_POST['tambah'])) {
    $nama_lengkap = $_POST['nama_lengkap'];
    $username     = $_POST['username'];
    $password     = $_POST['password'];
    $role         = $_POST['role'];
    $status_aktif = $_POST['status_aktif'];

    if (tambah_user_data($nama_lengkap, $username, $password, $role, $status_aktif)) {
        catat_log($_SESSION['id_user'], 'Menambah user baru: ' . $username);
        header('Location: adminview.php?info=created');
        exit;
    } else {
        $user_error = true;
    }
}

// -------------------------------------------------------------
// LETAK KODE HAPUS USER JUGA DI SINI
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ubah_status_user'])) {
    $id_user = (int)($_POST['id_user'] ?? 0);
    $status_baru = (string)($_POST['status_baru'] ?? '');
    if (ubah_status_user_data($id_user, $status_baru)) {
        catat_log($_SESSION['id_user'], 'Mengubah status user ID: ' . $id_user . ' menjadi ' . $status_baru);
        header('Location: adminview.php?info=status');
        exit;
    }
    $user_error = true;
}

// 4. Ambil data terbaru untuk tabel (harus setelah proses tambah/hapus)
$users = get_all_users();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Kelola Data User</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; color: #333; padding: 30px; }
        .container { max-width: 1000px; margin: 0 auto; background: #ffffff; padding: 25px 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); }
        .btn-back { display: inline-block; color: #4a5568; text-decoration: none; font-weight: 600; font-size: 14px; margin-bottom: 20px; }
        .btn-back:hover { color: #3182ce; }
        h2 { color: #1a202c; font-size: 24px; font-weight: 700; margin-bottom: 25px; border-bottom: 2px solid #edf2f7; padding-bottom: 10px; }
        
        .form-user { display: flex; flex-wrap: wrap; gap: 12px; background-color: #f8fafc; padding: 18px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 30px; align-items: center; }
        .form-user input, .form-user select { flex: 1; min-width: 130px; padding: 10px 14px; border: 1px solid #cbd5e0; border-radius: 6px; font-size: 14px; outline: none; background: #fff; }
        .form-user input:focus, .form-user select:focus { border-color: #3182ce; box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.15); }
        .btn-submit { padding: 10px 20px; background-color: #3182ce; color: #ffffff; border: none; border-radius: 6px; font-weight: 600; font-size: 14px; cursor: pointer; }
        .btn-submit:hover { background-color: #2b6cb0; }
        
        table { width: 100%; border-collapse: collapse; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        table th, table td { padding: 14px 16px; text-align: left; font-size: 14px; }
        table th { background-color: #edf2f7; color: #4a5568; font-weight: 700; font-size: 12px; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
        table tr { border-bottom: 1px solid #edf2f7; }
        table tbody tr:hover { background-color: #f7fafc; }
        
        .badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; text-transform: capitalize; }
        .badge-aktif { background-color: #c6f6d5; color: #22543d; }
        .badge-nonaktif { background-color: #fed7d7; color: #742a2a; }
        
        .btn-delete { color: #e53e3e; text-decoration: none; font-weight: 600; padding: 6px 12px; background-color: #fff5f5; border: 1px solid #fed7d7; border-radius: 4px; font-size: 13px; }
        .btn-delete:hover { background-color: #e53e3e; color: #ffffff; }
    </style>
    <link rel="stylesheet" href="../assets/app.css">
</head>
<body>

<div class="container">
    <a href="dashboard.php" class="btn-back">← Kembali ke Dashboard</a>
    <h2>Kelola Data User</h2>
    <?php if (($_GET['info'] ?? '') === 'created'): ?><div class="alert alert-success" role="status">User berhasil ditambahkan.</div><?php endif; ?>
    <?php if (($_GET['info'] ?? '') === 'status'): ?><div class="alert alert-success" role="status">Status akun berhasil diperbarui.</div><?php endif; ?>
    <?php if ($user_error): ?><div class="alert alert-error" role="alert">Operasi gagal. Periksa username, password minimal 8 karakter, dan pastikan akun yang sedang digunakan tidak dinonaktifkan.</div><?php endif; ?>

    <!-- FORM TAMBAH USER -->
    <form action="" method="POST" class="form-user">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
        <input type="text" name="nama_lengkap" placeholder="Nama Lengkap" maxlength="50" required autocomplete="off">
        <input type="text" name="username" placeholder="Username" maxlength="50" required autocomplete="off">
        <input type="password" name="password" placeholder="Password (min. 8 karakter)" minlength="8" required>
        <select name="role" required>
            <option value="admin">Admin</option>
            <option value="petugas">Petugas</option>
            <option value="owner">Owner</option>
        </select>
        <select name="status_aktif" required>
            <option value="aktif">Aktif</option>
            <option value="nonaktif">Nonaktif</option>
        </select>
        <button type="submit" name="tambah" class="btn-submit">Tambah User</button>
    </form>

    <!-- TABEL DATA USER -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Lengkap</th>
                <th>Username</th>
                <th>Role</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($users && mysqli_num_rows($users) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($users)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id_user']); ?></td>
                        <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                        <td><?= htmlspecialchars($row['username']); ?></td>
                        <td><?= ucfirst(htmlspecialchars($row['role'])); ?></td>
                        <td>
                            <span class="badge <?= $row['status_aktif'] === 'aktif' ? 'badge-aktif' : 'badge-nonaktif'; ?>">
                                <?= htmlspecialchars($row['status_aktif']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ((int)$row['id_user'] !== (int)($_SESSION['id_user'] ?? 0)): ?>
                                <form action="adminview.php" method="POST" style="display:inline" onsubmit="return confirm('Ubah status akun ini? Log aktivitas tetap akan disimpan.')">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="id_user" value="<?= (int)$row['id_user']; ?>">
                                    <input type="hidden" name="status_baru" value="<?= $row['status_aktif'] === 'aktif' ? 'nonaktif' : 'aktif'; ?>">
                                    <button type="submit" name="ubah_status_user" class="<?= $row['status_aktif'] === 'aktif' ? 'btn-delete' : 'btn-edit'; ?>"><?= $row['status_aktif'] === 'aktif' ? 'Nonaktifkan' : 'Aktifkan'; ?></button>
                                </form>
                            <?php else: ?>
                                <span class="time-text">Akun Anda</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center;">Belum ada data user.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>