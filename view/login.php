<?php require_once __DIR__ . '/../config/koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Parkir</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #0f172a;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .login-card {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 380px;
        }

        .login-card h2 {
            text-align: center;
            color: #1e293b;
            margin-bottom: 8px;
            font-size: 22px;
        }

        .login-card p {
            text-align: center;
            color: #64748b;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #334155;
            font-size: 14px;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-group input:focus {
            border-color: #3b82f6;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: #3b82f6;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-top: 5px;
        }

        .btn-submit:hover {
            background-color: #2563eb;
        }
    </style>
    <link rel="stylesheet" href="../assets/app.css">
</head>
<body>

    <div class="login-card">
        <h2>Sistem Parkir</h2>
        <p>Masuk untuk mengelola kendaraan dan transaksi parkir.</p>
        <?php if (($_GET['error'] ?? '') === 'csrf'): ?><div class="alert alert-error" role="alert">Sesi formulir berakhir. Muat ulang halaman, lalu coba lagi.</div><?php elseif (isset($_GET['error'])): ?><div class="alert alert-error" role="alert">Username atau password salah, atau akun sedang nonaktif.</div><?php endif; ?>

        <form action="../controller/auth.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Masukkan username" required autocomplete="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required>
            </div>

            <button type="submit" name="login" class="btn-submit">Masuk</button>
        </form>
    </div>

</body>
</html>