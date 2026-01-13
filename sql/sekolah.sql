-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 26, 2025 at 07:40 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sekolah`
--

-- --------------------------------------------------------

--
-- Table structure for table `jadwal`
--

CREATE TABLE `jadwal` (
  `id_jadwal` int NOT NULL,
  `id_kelas` int NOT NULL,
  `id_mapel` int NOT NULL,
  `id_petugas` int NOT NULL,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') COLLATE utf8mb4_general_ci NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kehadiran`
--

CREATE TABLE `kehadiran` (
  `id_kehadiran` int NOT NULL,
  `id_siswa` int NOT NULL,
  `id_jadwal` int NOT NULL,
  `tanggal` date NOT NULL,
  `status` enum('Hadir','Izin','Sakit','Alpha') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id_kelas` int NOT NULL,
  `nama_kelas` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `tingkat` varchar(20) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kelas`
--

INSERT INTO `kelas` (`id_kelas`, `nama_kelas`, `tingkat`) VALUES
(7, 'X PPLG', 'X'),
(8, 'XI PPLG', 'XI'),
(9, 'XII PPLG', 'XII');

-- --------------------------------------------------------

--
-- Table structure for table `mata_pelajaran`
--

CREATE TABLE `mata_pelajaran` (
  `id_mapel` int NOT NULL,
  `nama_mapel` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `kode_mapel` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nilai`
--

CREATE TABLE `nilai` (
  `id_nilai` int NOT NULL,
  `id_siswa` int NOT NULL,
  `id_mapel` int NOT NULL,
  `nilai` decimal(5,2) NOT NULL,
  `semester` enum('1','2') COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id_reset` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expired_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id_reset`, `username`, `token`, `created_at`, `expired_at`) VALUES
(1, 'w', '95f78b71df74f7d3fe9f37a7b172fb1bc0475c8f993c819e42796d5fd0ac4d0a', '2025-09-02 09:47:17', '2025-09-02 17:47:17'),
(5, 'w', '32090f6aab38c27d6be8c48a0d52b8f5e1de0173695dd79806f00504dfe724a9', '2025-09-02 09:55:09', '2025-09-02 17:55:09'),
(8, 'refal_3434', 'a355a87293da6101a82ac96b4b4f837071d8d29f3617254e2b87bb87893a3b9e', '2025-09-06 12:25:21', '2025-09-06 20:25:21'),
(9, 'r', 'b122e8759b005fff38681d904ccedfd2275a010cd6bcecfeb776390d98f1ef4b', '2025-09-06 12:29:16', '2025-09-06 20:29:16'),
(10, 'udin', '93b26c093f40cee9027c2c61095bfa4e5d20d4c8eedc4a87cf623639ea506128', '2025-11-04 07:02:13', '2025-11-04 15:02:13'),
(11, '123', 'f93cd779a168b531f67a6396c8458e4cbbfbdf4df28cab53b87a18f976ed1b8e', '2025-11-04 07:02:30', '2025-11-04 15:02:30'),
(12, '123', '2ca2e54b5d4c18c62e87fa354b74f2dce75340ae01f695246c79dd5581556d82', '2025-11-04 07:04:00', '2025-11-04 15:04:00'),
(16, '123', 'd2d6032fb556f4733be5a06c5dd0be4bfbaa0491743a9e6b6f07b3749e7c8d81', '2025-11-04 07:48:11', '2025-11-04 15:48:11'),
(17, '123', 'aada695c12fe1f64775ea1827f8872124743548441227b34ffcf153e7b63ffd8', '2025-11-04 07:53:00', '2025-11-04 15:53:00'),
(18, '123', 'c8679fbc8b861b5472f2831d052eaf49a139f63c8052b30b5db2080a26cb8f5b', '2025-11-04 07:56:25', '2025-11-04 15:56:25'),
(19, '123', 'cace728ec7d3a2efa272c4c5f03db6befca312534c1247c9c50af272f0fdbcb4', '2025-11-04 07:57:57', '2025-11-04 15:57:57'),
(20, 'udin', 'b34337cec08c2e418af6a5664756ef9fe08733d34db397aff954d8955645257e', '2025-11-04 08:02:19', '2025-11-04 16:02:19'),
(21, '123', '9337a4bee89baadc6e6069a730067f9db4457eed971a27feae293c09b3865cb8', '2025-11-04 08:03:29', '2025-11-04 16:03:29'),
(22, 'refal_3434', '5e48cc18d78c36bc080db95c25b9232e05ab840d1e97456e7e6f23103c75cd7f', '2025-11-25 00:53:33', '2025-11-25 08:53:33'),
(23, 'refal_3434', '42e1a5304f8e16dfc40eed784c3ab2515ad5ef4d5b6e43afb0b99320a8f2b632', '2025-11-25 01:00:45', '2025-11-25 03:00:44'),
(24, 'refal_3434', '7798ab2125d69d6f1919a7fd3f5a921e18ed4ad39b8868d5f2ccf80cf47130a3', '2025-11-25 01:18:23', '2025-11-25 09:18:23'),
(25, 'refal_3434', '2e26521f3f65277d658ef3872d0b58ebb3b945c8bb1f58fe09433a107873d2c7', '2025-11-25 04:06:02', '2025-11-25 06:06:02'),
(26, 'refal_3434', '7b97640e109fe1122dbf3a77dd675714ccbe9b65f4622f9a6a55efa5ec49b4b7', '2025-11-25 04:22:21', '2025-11-25 06:22:21'),
(27, 'refal_3434', '7f40b6f198ce7abc3f29b7afd3b98d4871e3baa6be9fd499e1408eae4ca0b626', '2025-11-25 04:51:43', '2025-11-25 06:51:43');

-- --------------------------------------------------------

--
-- Table structure for table `pending_registrations`
--

CREATE TABLE `pending_registrations` (
  `id_pending` int NOT NULL,
  `nis` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_siswa` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `jenis_kelamin` enum('L','P') COLLATE utf8mb4_general_ci NOT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_general_ci DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pending_registrations`
--

INSERT INTO `pending_registrations` (`id_pending`, `nis`, `nama_siswa`, `username`, `password`, `jenis_kelamin`, `no_hp`, `created_at`, `status`) VALUES
(1, '2888', 'pisang coklat', '2888', '$2y$10$MyooWORLkYZAZN8KnLs63uHnx4v7GTDCUORjINFU.CtYkpt3yStlW', 'L', '544678', '2025-11-25 04:19:37', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `petugas`
--

CREATE TABLE `petugas` (
  `id_petugas` int NOT NULL,
  `nama_petugas` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','guru') COLLATE utf8mb4_general_ci NOT NULL,
  `nip` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `petugas`
--

INSERT INTO `petugas` (`id_petugas`, `nama_petugas`, `username`, `password`, `role`, `nip`, `no_hp`) VALUES
(1, 'udin', 'donto', '$2y$10$eJbsDmsycoe3A4T6p.lvi.cKf.5uc3LURkN12Q6/YHcflryZ9WYmC', 'guru', NULL, NULL),
(2, 'Naufal Hanan', 'r', '$2y$10$xpOaLnkU7PyKS1FVyIJxIu3RWzVx4KAOVbHetq4G2b8nXhdyh4OKW', 'guru', NULL, NULL),
(3, 'dnd', 'w', '$2y$10$2utcVhx8PSCl9pyVGtD0C.FCZ6ZEh7uG90SEEMRaephp0HLq07UX6', 'guru', NULL, ''),
(4, 'refal', 'refal', '$2y$10$k2uOZrDRskhj4Jiy9qLDJO/zVMYOZZmAcQRLSpwJ9ugq9TOUhVWr.', 'guru', '3123', '2131'),
(5, 'refal', 'refal_3434', '$2y$10$jiXU5b6MMQ6nLvjTdVT4nO8MMrPQgA7Srfw.2VbW7QL0A688emINe', 'admin', NULL, ''),
(6, 'refal', '123', '$2y$10$/Hg2DYZbA1f6ouxQiNFCEuOZmrsKh0SfeMB1ftlQ8QtkRNF12PSre', 'admin', '231', '321123'),
(7, 'Raifal Fadhal Zahran', 'jamal', '$2y$10$tSVYjpKMzgxuS.7oStltG.JWHzKAgF/16SGBnNx.4lZPaM2aAvLLO', 'admin', '123', '34324'),
(9, 'asep', 'asep', '$2y$10$wRRM2GhetsISxKxdMiKl8eSvWBF4EMtgGu3dZ6BY/pqs1oLlapn2q', 'guru', NULL, ''),
(10, 'Naufal Hanan', 'Naufal', '$2y$10$WT8km0oFtJ56lvvfEdYq5uVFr.Mfa8ilBahagZM1oEb6FdJvilK0q', 'guru', NULL, ''),
(11, 'udin', 'udin', '$2y$10$IkPP0tzJwBvtV0BsmtWjmuqcv0qzh/KqJjv5zMo7os3z8VzPj3Eii', 'guru', NULL, ''),
(13, 'jamal', 'jamal123', '$2y$10$HhJQS/5stNjbrJTCDYkRneqcwRG0Rz2AEm.JE7YU3W481dblocxsa', 'admin', '122345', NULL),
(15, 'Panci', 'Hyugaaaa', '$2y$10$SZD/Gk77cGreLSZe8ecRiOTMEiNuyyRBOxMDELd3xL/ryuK4CRHmm', 'admin', NULL, ''),
(16, 'A', 'Hyuga', '$2y$10$6UBK.OaxkSnXeiAFsHH48.AIjHtLw2.WKKItLnr8lQmciPg2wXglq', 'guru', NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id_siswa` int NOT NULL,
  `nama_siswa` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `nis` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `id_kelas` int NOT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jenis_kelamin` enum('L','P') COLLATE utf8mb4_general_ci NOT NULL,
  `face_data` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD KEY `id_kelas` (`id_kelas`),
  ADD KEY `id_mapel` (`id_mapel`),
  ADD KEY `id_petugas` (`id_petugas`);

--
-- Indexes for table `kehadiran`
--
ALTER TABLE `kehadiran`
  ADD PRIMARY KEY (`id_kehadiran`),
  ADD KEY `id_siswa` (`id_siswa`),
  ADD KEY `id_jadwal` (`id_jadwal`);

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id_kelas`);

--
-- Indexes for table `mata_pelajaran`
--
ALTER TABLE `mata_pelajaran`
  ADD PRIMARY KEY (`id_mapel`),
  ADD UNIQUE KEY `kode_mapel` (`kode_mapel`);

--
-- Indexes for table `nilai`
--
ALTER TABLE `nilai`
  ADD PRIMARY KEY (`id_nilai`),
  ADD KEY `id_siswa` (`id_siswa`),
  ADD KEY `id_mapel` (`id_mapel`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id_reset`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `pending_registrations`
--
ALTER TABLE `pending_registrations`
  ADD PRIMARY KEY (`id_pending`),
  ADD UNIQUE KEY `nis` (`nis`);

--
-- Indexes for table `petugas`
--
ALTER TABLE `petugas`
  ADD PRIMARY KEY (`id_petugas`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `nip` (`nip`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id_siswa`),
  ADD UNIQUE KEY `nis` (`nis`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `id_kelas` (`id_kelas`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jadwal`
--
ALTER TABLE `jadwal`
  MODIFY `id_jadwal` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `kehadiran`
--
ALTER TABLE `kehadiran`
  MODIFY `id_kehadiran` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id_kelas` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `mata_pelajaran`
--
ALTER TABLE `mata_pelajaran`
  MODIFY `id_mapel` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `nilai`
--
ALTER TABLE `nilai`
  MODIFY `id_nilai` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id_reset` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `pending_registrations`
--
ALTER TABLE `pending_registrations`
  MODIFY `id_pending` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `petugas`
--
ALTER TABLE `petugas`
  MODIFY `id_petugas` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id_siswa` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD CONSTRAINT `jadwal_ibfk_1` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE CASCADE,
  ADD CONSTRAINT `jadwal_ibfk_2` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id_mapel`) ON DELETE CASCADE,
  ADD CONSTRAINT `jadwal_ibfk_3` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON DELETE CASCADE;

--
-- Constraints for table `kehadiran`
--
ALTER TABLE `kehadiran`
  ADD CONSTRAINT `kehadiran_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE,
  ADD CONSTRAINT `kehadiran_ibfk_2` FOREIGN KEY (`id_jadwal`) REFERENCES `jadwal` (`id_jadwal`) ON DELETE CASCADE;

--
-- Constraints for table `nilai`
--
ALTER TABLE `nilai`
  ADD CONSTRAINT `nilai_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE,
  ADD CONSTRAINT `nilai_ibfk_2` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id_mapel`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`username`) REFERENCES `petugas` (`username`) ON DELETE CASCADE;

--
-- Constraints for table `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
