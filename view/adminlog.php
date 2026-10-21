<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/koneksi.php';
cek_akses(['admin']);
require_once __DIR__ . '/../model/admin.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ambil data riwayat log dari Model
$log_list = get_all_logs();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Log Aktivitas Sistem</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; color: #333; padding: 30px; }
        .container { max-width: 950px; margin: 0 auto; background: #ffffff; padding: 25px 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); }
        .btn-back { display: inline-block; color: #4a5568; text-decoration: none; font-weight: 600; font-size: 14px; margin-bottom: 20px; }
        .btn-back:hover { color: #3182ce; }
        h2 { color: #1a202c; font-size: 24px; font-weight: 700; margin-bottom: 25px; border-bottom: 2px solid #edf2f7; padding-bottom: 10px; }
        
        table { width: 100%; border-collapse: collapse; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        table th, table td { padding: 14px 16px; text-align: left; font-size: 14px; }
        table th { background-color: #edf2f7; color: #4a5568; font-weight: 700; font-size: 12px; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
        table tr { border-bottom: 1px solid #edf2f7; }
        table tbody tr:hover { background-color: #f7fafc; }
        
        .badge-user { display: inline-block; padding: 4px 10px; background-color: #e2e8f0; color: #2d3748; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .time-text { color: #718096; font-size: 13px; }
    </style>
    <link rel="stylesheet" href="../assets/app.css">
</head>
<body>

<div class="container">
    <a href="dashboard.php" class="btn-back">← Kembali ke Dashboard</a>
    <h2>Log Aktivitas Pengguna</h2>

    <!-- TABEL DATA LOG -->
    <table>
        <thead>
            <tr>
                <th style="width: 80px;">ID Log</th>
                <th style="width: 150px;">User</th>
                <th>Aktivitas</th>
                <th style="width: 180px;">Waktu</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($log_list && mysqli_num_rows($log_list) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($log_list)): ?>
                    <tr>
                        <td>#<?= htmlspecialchars($row['id_log']); ?></td>
                        <td>
                            <span class="badge-user">
                                <?= htmlspecialchars($row['username']); ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($row['aktivitas']); ?></td>
<td class="time-text">
    <?= !empty($row['waktu']) ? date('d-m-Y H:i:s', strtotime($row['waktu'])) : '-'; ?>
</td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align: center; color: #a0aec0; padding: 30px;">
                        Belum ada catatan aktivitas sistem.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>