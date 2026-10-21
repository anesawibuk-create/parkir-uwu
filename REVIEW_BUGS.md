# Catatan code review E-Parkir

## Temuan dan perbaikan

| Tingkat | Temuan awal | Perbaikan pada versi ini |
|---|---|---|
| Kritis | `controller/petugas.php` bisa dipanggil tanpa memvalidasi role login. | Endpoint sekarang memeriksa role `petugas`/`admin` dan memakai token CSRF pada permintaan tulis. |
| Kritis | Proses tambah kendaraan tidak mengisi `id_tarif`, padahal kolom database `NOT NULL`. | Tarif dipilih saat kendaraan masuk dan disimpan bersama kendaraan. |
| Tinggi | Join tarif berdasarkan `jenis_kendaraan` dapat menggandakan baris kendaraan jika ada beberapa tarif per jenis. | Daftar dan struk menggunakan `id_tarif` per sesi, dengan fallback untuk data lama. |
| Tinggi | Tarif yang dipakai saat keluar bisa tidak konsisten jika tarif suatu jenis kendaraan lebih dari satu. | Tarif per jam disimpan sebagai snapshot saat masuk; biaya keluar dihitung memakai snapshot. |
| Tinggi | Indeks unik `plat_nomor` melarang kendaraan yang sama masuk kembali walau sesi sebelumnya sudah selesai. | Dump database baru menggantinya dengan unique index pada kolom generated `plat_aktif` yang hanya terisi saat status parkir; sesi historis tetap boleh memakai plat yang sama. Untuk DB lama, jalankan migrasi. |
| Tinggi | Halaman struk memakai tabel `transaksi_parkir` dan kolom `durasi_jam`/`biaya_total`, yang tidak ada pada SQL yang dikirim. | Struk membaca tabel `kendaraan` dan menghitung durasi dari waktu masuk/keluar. |
| Tinggi | `get_pendapatan_hari_ini()` memiliki kondisi `OR status = 'keluar'`, sehingga bisa menghitung pemasukan hari sebelumnya. | Query dibatasi pada `DATE(jam_keluar) = CURDATE()`. |
| Tinggi | Filter tanggal rekap dirangkai ke SQL dan mencocokkan tanggal masuk **atau** keluar, yang berisiko injeksi dan membingungkan total. | Tanggal divalidasi dan dipakai sebagai parameter query; rekap pendapatan difilter berdasarkan tanggal keluar. |
| Tinggi | Login akun tidak ditemukan tidak selalu memberi respons; pengalihan sesi memakai path `views` yang salah. | Login menampilkan pesan yang jelas, akses memakai path `view`, dan ID sesi diregenerasi setelah login. Password teks biasa lama di-upgrade ke hash saat login sukses. |
| Tinggi | Penghapusan user berpotensi gagal karena `log_aktivitas` memiliki foreign key ke user. | UI mengubah tindakan menjadi aktif/nonaktif agar riwayat aktivitas tidak terputus. Akun sendiri tidak dapat dinonaktifkan melalui UI. |
| Tinggi | Hapus riwayat kendaraan bisa menghilangkan sumber data pendapatan. | Tombol hapus riwayat dihilangkan dan penghapusan fisik sesi parkir dinonaktifkan. |
| Sedang | Kolom tabel transaksi kendaraan tidak cocok dengan isi baris: tarif ditampilkan di bawah judul jam masuk dan ada sel berlebih. | Kolom Tarif/Jam ditambahkan dan jumlah sel dibetulkan; ditambahkan pencarian cepat plat/area. |
| Sedang | Penghitung kapasitas `area_parkir.terisi` bisa stale karena tidak selalu diperbarui oleh transaksi. | Tampilan dan validasi kapasitas menghitung kendaraan dengan status `parkir` secara langsung. |
| Sedang | Tabel log aktivitas kehilangan tag penutup `</tr>`. | Markup baris diperbaiki. |
| Sedang | Halaman tertentu tidak memeriksa role; aksi mutasi bergantung pada GET dan tidak punya perlindungan CSRF. | Pemeriksaan akses ditambahkan ke transaksi, rekap, log, dan struk; mutasi admin memakai POST dan token CSRF. |
| Sedang | Halaman punya style yang berbeda-beda dan tidak nyaman di HP. | CSS bersama dengan layout responsif, warna konsisten, komponen form/tabel/modal, dan mode cetak struk. |

## Catatan arsitektur dan sisa pekerjaan

- Tabel `transaksi` memang ada pada skema database awal, tetapi alur aplikasi yang ada menyimpan sesi parkir dan pendapatan di tabel `kendaraan`. Belum ada migrasi data atau konsolidasi ke tabel `transaksi`; untuk aplikasi produksi, sebaiknya dipilih satu sumber transaksi dan dibuat aturan relasi yang konsisten.
- Query dan halaman belum diuji live pada server database, jadi lint PHP bukan bukti bahwa semua proses berjalan sukses pada konfigurasi lokal. Jalankan skenario uji masuk-keluar, kapasitas penuh, tarif kosong, plat masuk ulang, filter rekap, dan cetak struk.
- Koneksi default (`root` tanpa password) hanya cocok untuk lingkungan development lokal, bukan server produksi.
- Proteksi CSRF ditambahkan pada aksi perubahan melalui form. Pengamanan produksi lebih lanjut tetap disarankan: HTTPS, pembatasan percobaan login/rate limiting, pengelolaan rahasia konfigurasi, backup rutin, dan pengujian otorisasi per role.

## Uji manual yang disarankan

1. Login setiap role dan pastikan halaman yang tidak sesuai role ditolak.
2. Tambah kendaraan ketika tarif belum tersedia, area penuh, atau plat masih aktif; operasi harus gagal dengan pesan yang jelas.
3. Masukkan kendaraan, ubah tarif admin, lalu proses keluar; tarif sesi sebelumnya harus tetap sama.
4. Proses keluar dua kali; proses kedua tidak boleh mengubah nominal lagi. Riwayat sesi yang sudah selesai tidak dapat dihapus melalui UI agar laporan pendapatan tetap utuh.
5. Keluar lalu masuk kembali dengan plat yang sama setelah migrasi; harus membuat sesi baru.
6. Filter rekap dengan tanggal valid, terbalik, dan input yang tidak valid.
7. Cetak struk untuk sesi keluar dan buka ID yang tidak ada / sesi yang belum keluar.
