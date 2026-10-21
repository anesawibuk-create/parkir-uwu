# E-Parkir — versi UI dan perbaikan logika

Versi ini merapikan tampilan halaman login, dashboard, transaksi, tarif, area, pengguna, rekap, log, dan struk. Perubahan dibuat dengan PHP + MySQLi sesuai struktur proyek awal, tanpa framework tambahan.

## Menjalankan proyek

1. Gunakan PHP 8+ dengan ekstensi `mysqli` dan `mysqlnd`, serta MySQL/MariaDB (XAMPP/WAMP dapat digunakan).
2. Salin folder `parkir2_fixed` ke direktori web server, misalnya `htdocs/parkir2_fixed`.
3. Pilih **salah satu** cara menyiapkan database berikut:
   - **Instalasi baru:** buat database `parkir_db`, lalu import `database/parkir_db_fixed.sql` melalui phpMyAdmin.
   - **Database lama:** buat backup terlebih dahulu, lalu jalankan `migrations/001_fix_vehicle_history_and_rates.sql` satu kali pada database lama. Jangan jalankan migrasi ini setelah mengimpor dump fixed karena kolom dan indeksnya sudah disesuaikan.
4. Periksa `config/koneksi.php`. Konfigurasi default memakai host `localhost`, user `root`, password kosong, dan database `parkir_db`; sesuaikan dengan konfigurasi lokal.
5. Buka `http://localhost/parkir2_fixed/` (otomatis diarahkan ke login atau dashboard).

## Akun awal dari dump database

- Admin: `admin` / `admin123`
- Petugas: `petugas` / `petugas123`
- Owner: `owner` / `owner123`

Password akun bawaan di dump lama awalnya masih berupa teks biasa. Saat akun lama berhasil login, aplikasi mencoba menaikkan password tersebut menjadi hash. Tetap ganti kredensial demo sebelum aplikasi dipakai sungguhan dan gunakan HTTPS serta password database yang kuat.

## Perilaku tarif

Saat kendaraan masuk, aplikasi memilih tarif terbaru (ID tarif terbesar) sesuai jenis kendaraan dan menyimpannya sebagai snapshot pada sesi parkir. Mengubah tarif sesudah itu hanya dimaksudkan untuk sesi baru; biaya dan struk sesi lama tetap memakai snapshot harga saat masuk. Jika ada beberapa tarif aktif untuk jenis yang sama, tarif dengan ID terbesar yang dipakai.

Durasi ditagih dibulatkan ke atas per jam, minimal satu jam. Contoh: 1 jam 5 menit ditagih 2 jam. Ubah aturan ini di `model/parkir.php` jika kebijakan parkir yang sebenarnya berbeda.

## Pemeriksaan yang dilakukan

Semua file PHP sudah diperiksa dengan `php -l` (syntax lint). Pemeriksaan runtime terhadap query dan tampilan akhir tetap harus dilakukan di server dengan database yang sebenarnya, karena lingkungan penyuntingan ini tidak menjalankan database proyek.
