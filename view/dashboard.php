<?php
require_once __DIR__ . '/../config/koneksi.php';
cek_akses(['admin', 'petugas', 'owner']);

$role = $_SESSION['role'];

// Ambil ringkasan data dari database
$total_user       = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM users"))['total'] ?? 0;
$total_area       = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM area_parkir"))['total'] ?? 0;

// FIX: Kendaraan aktif diambil dari tabel 'kendaraan' dengan status 'parkir'
$kendaraan_parkir = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM kendaraan WHERE status = 'parkir'"))['total'] ?? 0;

$tgl_today        = date('Y-m-d');

// Total pendapatan diakumulasikan dari SEMUA transaksi yang sudah keluar (bukan per hari)
$q_pendapatan_total = mysqli_query($koneksi, "SELECT SUM(total_bayar) AS total FROM kendaraan WHERE status = 'keluar'");
$pendapatan_total   = mysqli_fetch_assoc($q_pendapatan_total)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Aplikasi Parkir</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        body {
            background-color: #f4f6f9;
        }
        /* Navbar Style */
        .navbar {
            background-color: #1e293b;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            height: 60px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .navbar .brand {
            color: #ffffff;
            font-size: 20px;
            font-weight: bold;
        }
        .navbar ul {
            list-style: none;
            display: flex;
            align-items: center;
        }
        .navbar ul li {
            margin-left: 5px;
        }
        .navbar ul li a {
            color: #cbd5e1;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
            border-radius: 4px;
            transition: 0.3s;
            font-size: 14px;
        }
        .navbar ul li a:hover,
        .navbar ul li a.active {
            background-color: #3b82f6;
            color: #ffffff;
        }
        .navbar ul li a.btn-logout {
            background-color: #ef4444;
            color: #ffffff;
        }
        .navbar ul li a.btn-logout:hover {
            background-color: #dc2626;
        }

        /* Content Container */
        .container {
            padding: 30px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .welcome-box {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        /* Card Grid */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }
        .card {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-left: 5px solid #3b82f6;
        }
        .card h3 {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .card .value {
            font-size: 24px;
            font-weight: bold;
            color: #0f172a;
        }
    </style>
    <link rel="stylesheet" href="../assets/app.css">
</head>
<body>

    <nav class="navbar">
        <div class="brand">E-Parkir</div>
        <ul>
            <li><a href="dashboard.php" class="active">Dashboard</a></li>

            <?php if ($role === 'admin') : ?>
                <li><a href="adminview.php">Kelola User</a></li>
                <li><a href="admintarif.php">Kelola Tarif</a></li>
                <li><a href="adminarea.php">Kelola Area</a></li>
                <li><a href="adminlog.php">Log Aktivitas</a></li>
            <?php endif; ?>

            <?php if ($role === 'petugas') : ?>
                <li><a href="transaksi_petugas.php">Transaksi Parkir</a></li>
            <?php endif; ?>

            <?php if ($role === 'owner') : ?>
                <li><a href="rekap.php">Rekap Laporan</a></li>
            <?php endif; ?>

            <li>
                <form action="../controller/auth.php" method="POST" class="logout-form" onsubmit="return confirm('Yakin ingin keluar?')">
                    <input type="hidden" name="action" value="logout">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="btn-logout">Logout</button>
                </form>
            </li>
        </ul>
    </nav>

    <div class="container">
        <div class="welcome-box">
            <h2>
                Selamat Datang, 
<?= htmlspecialchars($_SESSION['nama_lengkap'] ?? ucfirst($role), ENT_QUOTES, 'UTF-8'); ?>!
            </h2>
            <p style="color: #64748b; margin-top: 5px;">
                Anda login sebagai <b><?= strtoupper($role); ?></b>. Silakan gunakan menu navigasi di atas untuk mengakses fitur.
            </p>
        </div>

        <div class="cards-grid">
            <?php if ($role === 'admin' || $role === 'petugas') : ?>
                <div class="card" style="border-left-color: #f59e0b;">
                    <h3>Kendaraan Parkir (Aktif)</h3>
                    <div class="value"><?= number_format($kendaraan_parkir); ?> Unit</div>
                </div>
            <?php endif; ?>

            <?php if ($role === 'admin') : ?>
                <div class="card" style="border-left-color: #3b82f6;">
                    <h3>Total Pengguna</h3>
                    <div class="value"><?= number_format($total_user); ?> User</div>
                </div>
                <div class="card" style="border-left-color: #10b981;">
                    <h3>Total Area Parkir</h3>
                    <div class="value"><?= number_format($total_area); ?> Lokasi</div>
                </div>
            <?php endif; ?>

            <?php if ($role === 'owner' || $role === 'admin') : ?>
                <div class="card" style="border-left-color: #8b5cf6;">
                    <h3>Total Pendapatan</h3>
                    <div class="value">Rp <?= number_format($pendapatan_total, 0, ',', '.'); ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>