-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 11, 2026 at 03:07 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `parkir_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `area_parkir`
--

CREATE TABLE `area_parkir` (
  `id_area` int(11) NOT NULL,
  `nama_area` varchar(50) NOT NULL,
  `terisi` int(11) NOT NULL,
  `kapasitas` int(11) NOT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `area_parkir`
--

INSERT INTO `area_parkir` (`id_area`, `nama_area`, `terisi`, `kapasitas`, `status`) VALUES
(1, 'Area Motor', 0, 100, 'aktif'),
(2, 'Area Mobil', 0, 50, 'aktif');

-- --------------------------------------------------------

--
-- Table structure for table `kendaraan`
--

CREATE TABLE `kendaraan` (
  `id_kendaraan` int(11) NOT NULL,
  `plat_nomor` varchar(15) NOT NULL,
  `id_tarif` int(11) NOT NULL,
  `tarif_per_jam_snapshot` decimal(10,0) DEFAULT NULL,
  `id_area` int(11) DEFAULT NULL,
  `jenis_kendaraan` varchar(20) NOT NULL,
  `warna` varchar(20) DEFAULT NULL,
  `pemilik` varchar(100) DEFAULT NULL,
  `jam_masuk` datetime DEFAULT current_timestamp(),
  `jam_keluar` datetime DEFAULT NULL,
  `total_bayar` int(11) DEFAULT 0,
  `status` enum('parkir','keluar') DEFAULT 'parkir',
  `plat_aktif` varchar(15) GENERATED ALWAYS AS (CASE WHEN `status` = 'parkir' THEN UPPER(`plat_nomor`) ELSE NULL END) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kendaraan`
--

INSERT INTO `kendaraan` (`id_kendaraan`, `plat_nomor`, `id_tarif`, `id_area`, `jenis_kendaraan`, `warna`, `pemilik`, `jam_masuk`, `jam_keluar`, `total_bayar`, `status`) VALUES
(11, 'D 1234 YAH', 0, 1, 'motor', 'pink', 'hayu', '2026-09-11 08:03:30', NULL, 0, 'parkir'),
(12, 'B 2435 BGH', 0, 2, 'mobil', NULL, NULL, '2026-09-11 08:34:14', NULL, 0, 'parkir');

-- --------------------------------------------------------

--
-- Table structure for table `log_aktivitas`
--

CREATE TABLE `log_aktivitas` (
  `id_log` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `aktivitas` varchar(100) NOT NULL,
  `waktu_aktivitas` timestamp NOT NULL DEFAULT current_timestamp(),
  `waktu` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `log_aktivitas`
--

INSERT INTO `log_aktivitas` (`id_log`, `id_user`, `aktivitas`, `waktu_aktivitas`, `waktu`) VALUES
(1, 1, 'Login ke sistem', '0000-00-00 00:00:00', '2026-09-09 09:52:46'),
(2, 2, 'Login ke sistem', '0000-00-00 00:00:00', '2026-09-09 09:52:46'),
(3, 2, 'Mencatat kendaraan masuk', '0000-00-00 00:00:00', '2026-09-09 09:52:46'),
(4, 2, 'Mencatat kendaraan keluar', '0000-00-00 00:00:00', '2026-09-09 09:52:46'),
(5, 3, 'Mencatat transaksi parkir', '0000-00-00 00:00:00', '2026-09-09 09:52:46'),
(6, 1, 'Mengelola data tarif', '0000-00-00 00:00:00', '2026-09-09 09:52:46'),
(7, 1, 'Mengelola data kendaraan', '0000-00-00 00:00:00', '2026-09-09 09:52:46'),
(8, 2, 'Login ke sistem sebagai petugas', '2026-09-02 01:39:32', '2026-09-09 09:52:46'),
(9, 2, 'Login ke sistem sebagai petugas', '2026-09-02 02:03:46', '2026-09-09 09:52:46'),
(10, 1, 'Login ke sistem sebagai admin', '2026-09-02 02:07:48', '2026-09-09 09:52:46'),
(11, 1, 'Logout dari sistem', '2026-09-02 03:26:52', '2026-09-09 09:52:46'),
(12, 3, 'Login ke sistem sebagai owner', '2026-09-02 03:27:05', '2026-09-09 09:52:46'),
(13, 3, 'Logout dari sistem', '2026-09-03 01:16:57', '2026-09-09 09:52:46'),
(14, 1, 'Login ke sistem sebagai admin', '2026-09-03 01:17:08', '2026-09-09 09:52:46'),
(15, 1, 'Logout dari sistem', '2026-09-03 01:59:44', '2026-09-09 09:52:46'),
(16, 2, 'Login ke sistem sebagai petugas', '2026-09-03 01:59:55', '2026-09-09 09:52:46'),
(17, 2, 'Logout dari sistem', '2026-09-03 06:05:56', '2026-09-09 09:52:46'),
(18, 1, 'Login ke sistem sebagai admin', '2026-09-03 06:06:02', '2026-09-09 09:52:46'),
(19, 1, 'Logout dari sistem', '2026-09-04 00:07:00', '2026-09-09 09:52:46'),
(20, 3, 'Login ke sistem sebagai owner', '2026-09-04 00:07:06', '2026-09-09 09:52:46'),
(21, 3, 'Logout dari sistem', '2026-09-04 00:08:11', '2026-09-09 09:52:46'),
(22, 2, 'Login ke sistem sebagai petugas', '2026-09-04 00:08:16', '2026-09-09 09:52:46'),
(23, 2, 'Logout dari sistem', '2026-09-04 00:09:17', '2026-09-09 09:52:46'),
(24, 1, 'Login ke sistem sebagai admin', '2026-09-04 00:09:24', '2026-09-09 09:52:46'),
(25, 1, 'Logout dari sistem', '2026-09-04 00:09:30', '2026-09-09 09:52:46'),
(26, 3, 'Login ke sistem sebagai owner', '2026-09-04 00:09:34', '2026-09-09 09:52:46'),
(27, 3, 'Logout dari sistem', '2026-09-04 00:09:39', '2026-09-09 09:52:46'),
(28, 2, 'Login ke sistem sebagai petugas', '2026-09-04 00:09:44', '2026-09-09 09:52:46'),
(29, 2, 'Logout dari sistem', '2026-09-04 00:15:23', '2026-09-09 09:52:46'),
(30, 1, 'Login ke sistem sebagai admin', '2026-09-04 00:15:28', '2026-09-09 09:52:46'),
(31, 1, 'Logout dari sistem', '2026-09-04 03:21:54', '2026-09-09 09:52:46'),
(32, 1, 'Login ke sistem sebagai admin', '2026-09-04 04:38:45', '2026-09-09 09:52:46'),
(33, 1, 'Logout dari sistem', '2026-09-04 04:39:51', '2026-09-09 09:52:46'),
(34, 3, 'Login ke sistem sebagai owner', '2026-09-04 04:40:03', '2026-09-09 09:52:46'),
(35, 3, 'Logout dari sistem', '2026-09-08 05:31:22', '2026-09-09 09:52:46'),
(36, 1, 'Login ke sistem sebagai admin', '2026-09-08 05:31:26', '2026-09-09 09:52:46'),
(37, 1, 'Menambah tarif kendaraan motor', '2026-09-09 00:47:48', '2026-09-09 09:52:46'),
(38, 1, 'Mengubah tarif ID: 3', '2026-09-09 00:48:02', '2026-09-09 09:52:46'),
(39, 1, 'Menghapus tarif ID: 3', '2026-09-09 00:52:51', '2026-09-09 09:52:46'),
(40, 1, 'Menghapus area parkir ID: 4', '2026-09-09 01:18:32', '2026-09-09 09:52:46'),
(41, 1, 'Mengubah area parkir ID: 3', '2026-09-09 01:18:42', '2026-09-09 09:52:46'),
(42, 1, 'Menambah area parkir: area motor', '2026-09-09 01:18:58', '2026-09-09 09:52:46'),
(43, 1, 'Menghapus area parkir ID: 5', '2026-09-09 01:19:38', '2026-09-09 09:52:46'),
(44, 1, 'Menghapus area parkir ID: 3', '2026-09-09 01:21:07', '2026-09-09 09:52:46'),
(45, 1, 'Logout dari sistem', '2026-09-09 01:21:38', '2026-09-09 09:52:46'),
(46, 3, 'Login ke sistem sebagai owner', '2026-09-09 01:21:46', '2026-09-09 09:52:46'),
(47, 3, 'Logout dari sistem', '2026-09-09 01:21:50', '2026-09-09 09:52:46'),
(48, 2, 'Login ke sistem sebagai petugas', '2026-09-09 01:21:55', '2026-09-09 09:52:46'),
(49, 2, 'Logout dari sistem', '2026-09-09 01:22:04', '2026-09-09 09:52:46'),
(50, 1, 'Login ke sistem sebagai admin', '2026-09-09 01:22:11', '2026-09-09 09:52:46'),
(51, 1, 'Logout dari sistem', '2026-09-09 01:27:22', '2026-09-09 09:52:46'),
(52, 2, 'Login ke sistem sebagai petugas', '2026-09-09 01:27:27', '2026-09-09 09:52:46'),
(53, 2, 'Logout dari sistem', '2026-09-09 02:44:07', '2026-09-09 09:52:46'),
(54, 3, 'Login ke sistem sebagai owner', '2026-09-09 02:44:11', '2026-09-09 09:52:46'),
(55, 3, 'Logout dari sistem', '2026-09-09 02:44:22', '2026-09-09 09:52:46'),
(56, 1, 'Login ke sistem sebagai admin', '2026-09-09 02:44:26', '2026-09-09 09:52:46'),
(57, 1, 'Logout dari sistem', '2026-09-11 00:18:16', '2026-09-11 07:18:16'),
(58, 3, 'Login ke sistem sebagai owner', '2026-09-11 00:18:21', '2026-09-11 07:18:21'),
(59, 3, 'Logout dari sistem', '2026-09-11 00:19:14', '2026-09-11 07:19:14'),
(60, 1, 'Login ke sistem sebagai admin', '2026-09-11 00:19:18', '2026-09-11 07:19:18'),
(61, 1, 'Logout dari sistem', '2026-09-11 00:27:28', '2026-09-11 07:27:28'),
(62, 3, 'Login ke sistem sebagai owner', '2026-09-11 00:27:33', '2026-09-11 07:27:33'),
(63, 3, 'Logout dari sistem', '2026-09-11 00:27:48', '2026-09-11 07:27:48'),
(64, 1, 'Login ke sistem sebagai admin', '2026-09-11 00:27:52', '2026-09-11 07:27:52'),
(65, 1, 'Menambah tarif kendaraan motor', '2026-09-11 00:28:02', '2026-09-11 07:28:02'),
(66, 1, 'Logout dari sistem', '2026-09-11 00:35:15', '2026-09-11 07:35:15'),
(67, 3, 'Login ke sistem sebagai owner', '2026-09-11 00:35:18', '2026-09-11 07:35:18'),
(68, 3, 'Logout dari sistem', '2026-09-11 00:35:52', '2026-09-11 07:35:52'),
(69, 1, 'Login ke sistem sebagai admin', '2026-09-11 00:35:55', '2026-09-11 07:35:55'),
(70, 1, 'Menambah tarif kendaraan motor', '2026-09-11 00:36:12', '2026-09-11 07:36:12'),
(71, 1, 'Logout dari sistem', '2026-09-11 00:36:22', '2026-09-11 07:36:22'),
(72, 3, 'Login ke sistem sebagai owner', '2026-09-11 00:36:25', '2026-09-11 07:36:25'),
(73, 1, 'Login ke sistem sebagai admin', '2026-09-11 00:37:26', '2026-09-11 07:37:26'),
(74, 1, 'Menambah tarif kendaraan sepeda', '2026-09-11 00:37:45', '2026-09-11 07:37:45'),
(75, 1, 'Menghapus tarif ID: 6', '2026-09-11 00:41:13', '2026-09-11 07:41:13'),
(76, 1, 'Menambah tarif kendaraan mobil', '2026-09-11 00:41:24', '2026-09-11 07:41:24'),
(77, 1, 'Logout dari sistem', '2026-09-11 00:42:24', '2026-09-11 07:42:24'),
(78, 2, 'Login ke sistem sebagai petugas', '2026-09-11 00:42:28', '2026-09-11 07:42:28'),
(79, 1, 'Login ke sistem sebagai admin', '2026-09-11 00:48:26', '2026-09-11 07:48:26'),
(80, 1, 'Menambah tarif kendaraan mobil', '2026-09-11 00:48:48', '2026-09-11 07:48:48'),
(81, 1, 'Menghapus tarif ID: 1', '2026-09-11 01:14:26', '2026-09-11 08:14:26'),
(82, 1, 'Menghapus tarif ID: 7', '2026-09-11 01:14:33', '2026-09-11 08:14:33'),
(83, 1, 'Menambah tarif kendaraan mobil', '2026-09-11 01:14:46', '2026-09-11 08:14:46'),
(84, 1, 'Menghapus tarif ID: 9', '2026-09-11 01:37:59', '2026-09-11 08:37:59'),
(85, 1, 'Logout dari sistem', '2026-09-11 02:56:13', '2026-09-11 09:56:13'),
(86, 2, 'Login ke sistem sebagai petugas', '2026-09-11 02:56:17', '2026-09-11 09:56:17'),
(87, 2, 'Logout dari sistem', '2026-09-11 02:56:35', '2026-09-11 09:56:35'),
(88, 3, 'Login ke sistem sebagai owner', '2026-09-11 02:56:40', '2026-09-11 09:56:40'),
(89, 3, 'Logout dari sistem', '2026-09-11 02:57:41', '2026-09-11 09:57:41'),
(90, 1, 'Login ke sistem sebagai admin', '2026-09-11 02:57:46', '2026-09-11 09:57:46'),
(91, 1, 'Logout dari sistem', '2026-09-11 03:00:24', '2026-09-11 10:00:24'),
(92, 3, 'Login ke sistem sebagai owner', '2026-09-11 03:00:29', '2026-09-11 10:00:29');

-- --------------------------------------------------------

--
-- Table structure for table `tarif`
--

CREATE TABLE `tarif` (
  `id_tarif` int(11) NOT NULL,
  `jenis_kendaraan` enum('motor','mobil','lainnya') NOT NULL,
  `tarif_per_jam` decimal(10,0) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tarif`
--

INSERT INTO `tarif` (`id_tarif`, `jenis_kendaraan`, `tarif_per_jam`, `created_at`) VALUES
(2, 'mobil', 5000, '2026-08-27 02:22:05'),
(4, 'motor', 4000, '2026-09-11 00:28:02'),
(5, 'motor', 7000, '2026-09-11 00:36:12'),
(8, 'mobil', 9000, '2026-09-11 00:48:48');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_kendaraan` int(11) DEFAULT NULL,
  `id_area` int(11) NOT NULL,
  `id_tarif` int(11) DEFAULT NULL,
  `plat_nomor` varchar(15) NOT NULL,
  `jenis_kendaraan` varchar(20) NOT NULL,
  `warna` varchar(20) DEFAULT NULL,
  `pemilik` varchar(50) DEFAULT NULL,
  `waktu_masuk` datetime NOT NULL,
  `waktu_keluar` datetime DEFAULT NULL,
  `biaya` int(11) DEFAULT 0,
  `status` enum('masuk','keluar') DEFAULT 'masuk'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nama_lengkap` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` enum('admin','petugas','owner') NOT NULL,
  `status_aktif` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama_lengkap`, `username`, `password`, `role`, `status_aktif`, `created_at`) VALUES
(1, 'Administrator', 'admin', 'admin123', 'admin', 'aktif', '2026-08-27 02:09:07'),
(2, 'Petugas Parkir', 'petugas', 'petugas123', 'petugas', 'aktif', '2026-08-27 02:09:07'),
(3, 'Pemilik Parkir', 'owner', 'owner123', 'owner', 'aktif', '2026-08-27 02:09:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `area_parkir`
--
ALTER TABLE `area_parkir`
  ADD PRIMARY KEY (`id_area`);

--
-- Indexes for table `kendaraan`
--
ALTER TABLE `kendaraan`
  ADD PRIMARY KEY (`id_kendaraan`),
  ADD UNIQUE KEY `uq_kendaraan_plat_aktif` (`plat_aktif`),
  ADD KEY `idx_kendaraan_status_area` (`status`,`id_area`);

--
-- Indexes for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `tarif`
--
ALTER TABLE `tarif`
  ADD PRIMARY KEY (`id_tarif`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_kendaraan` (`id_kendaraan`),
  ADD KEY `id_area` (`id_area`),
  ADD KEY `id_tarif` (`id_tarif`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `area_parkir`
--
ALTER TABLE `area_parkir`
  MODIFY `id_area` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `kendaraan`
--
ALTER TABLE `kendaraan`
  MODIFY `id_kendaraan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `tarif`
--
ALTER TABLE `tarif`
  MODIFY `id_tarif` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD CONSTRAINT `log_aktivitas_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_kendaraan`) REFERENCES `kendaraan` (`id_kendaraan`),
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`id_area`) REFERENCES `area_parkir` (`id_area`),
  ADD CONSTRAINT `transaksi_ibfk_3` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `transaksi_ibfk_4` FOREIGN KEY (`id_tarif`) REFERENCES `tarif` (`id_tarif`);
-- Penyesuaian versi aplikasi: id_tarif dan tarif per jam disimpan per sesi parkir.
UPDATE kendaraan k
LEFT JOIN tarif old_t ON old_t.id_tarif = NULLIF(k.id_tarif, 0)
LEFT JOIN (
    SELECT jenis_kendaraan, MAX(id_tarif) AS id_tarif
    FROM tarif GROUP BY jenis_kendaraan
) latest ON LOWER(latest.jenis_kendaraan) = LOWER(k.jenis_kendaraan)
LEFT JOIN tarif new_t ON new_t.id_tarif = latest.id_tarif
SET k.id_tarif = COALESCE(old_t.id_tarif, new_t.id_tarif, k.id_tarif),
    k.tarif_per_jam_snapshot = COALESCE(old_t.tarif_per_jam, new_t.tarif_per_jam, k.tarif_per_jam_snapshot)
WHERE k.id_tarif = 0 OR k.tarif_per_jam_snapshot IS NULL OR k.tarif_per_jam_snapshot <= 0;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
