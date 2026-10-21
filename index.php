<?php
// Titik masuk aplikasi: arahkan ke dashboard jika sudah login, selain itu ke halaman login.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Location: ' . (!empty($_SESSION['login']) ? 'view/dashboard.php' : 'view/login.php'));
exit;
