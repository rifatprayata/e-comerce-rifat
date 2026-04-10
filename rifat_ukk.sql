-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 10, 2026 at 04:54 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rifat_ukk`
--

-- --------------------------------------------------------

--
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id` int(11) NOT NULL,
  `id_pesanan` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id`, `id_pesanan`, `id_produk`, `jumlah`, `subtotal`) VALUES
(16, 17, 4, 1, 200.00),
(17, 18, 3, 1, 200.00),
(18, 19, 4, 1, 200.00),
(19, 20, 4, 1, 200.00),
(20, 21, 4, 1, 200.00),
(21, 22, 4, 1, 200.00),
(22, 23, 4, 1, 200.00),
(23, 24, 4, 1, 200.00),
(24, 25, 4, 1, 200.00),
(25, 26, 4, 1, 200.00),
(26, 27, 2, 1, 200.00),
(27, 28, 3, 1, 200.00),
(28, 29, 2, 1, 200.00);

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id`, `nama_kategori`) VALUES
(1, 'Makanan'),
(2, 'Minuman'),
(3, 'Elektronik'),
(4, 'Tanaman');

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pelanggan`
--

INSERT INTO `pelanggan` (`id`, `nama`, `email`, `telepon`, `alamat`) VALUES
(1, 'Customer 1', 'c1@email.com', '08123456789', 'Jl. Contoh No.1');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int(11) NOT NULL,
  `nomor_pesanan` varchar(50) NOT NULL,
  `id_pelanggan` int(11) DEFAULT NULL,
  `tanggal_pesanan` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_harga` decimal(10,2) NOT NULL,
  `status_pesanan` enum('pending','diproses','dikirim','selesai','batal') DEFAULT 'pending',
  `status_pembayaran` enum('belum_bayar','lunas') DEFAULT 'belum_bayar',
  `metode_pembayaran` varchar(50) DEFAULT NULL,
  `metode_pembayaran_detail` varchar(50) DEFAULT NULL,
  `kurir` varchar(50) DEFAULT NULL,
  `paket_pengiriman` varchar(50) DEFAULT NULL,
  `resi_pengiriman` varchar(100) DEFAULT NULL,
  `alamat_pengiriman` text DEFAULT NULL,
  `nama_penerima` varchar(100) DEFAULT NULL,
  `telepon_penerima` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id`, `nomor_pesanan`, `id_pelanggan`, `tanggal_pesanan`, `total_harga`, `status_pesanan`, `status_pembayaran`, `metode_pembayaran`, `metode_pembayaran_detail`, `kurir`, `paket_pengiriman`, `resi_pengiriman`, `alamat_pengiriman`, `nama_penerima`, `telepon_penerima`) VALUES
(17, 'INV-20260408-8475', 6, '2026-04-07 19:22:16', 200.00, 'diproses', 'belum_bayar', 'Transfer Bank', NULL, NULL, NULL, NULL, 'jl sudirman', 'robi', '085899092202'),
(18, 'INV-20260408-3565', 6, '2026-04-07 19:23:11', 200.00, 'diproses', 'lunas', 'E-Wallet', NULL, NULL, NULL, NULL, 'jl hahu', 'rifat', '085899092202'),
(19, 'INV-20260408-5124', 6, '2026-04-07 21:54:13', 200.00, 'pending', 'lunas', 'E-Wallet', NULL, NULL, NULL, NULL, 'jl grogol', 'fattah', '085899092202'),
(20, 'INV-20260408-8572', 6, '2026-04-07 23:29:24', 200.00, 'pending', 'belum_bayar', 'E-Wallet', NULL, NULL, NULL, NULL, 'jl hdgdfs', 'rifat', '085899092202'),
(21, 'INV-20260408-9546', 6, '2026-04-07 23:54:20', 200.00, 'pending', 'lunas', 'Transfer Bank', NULL, NULL, NULL, NULL, 'gbddhjhm', 'testUser', '085899092202'),
(22, 'INV-20260408-1648', 6, '2026-04-07 23:56:10', 200.00, 'pending', 'belum_bayar', 'E-Wallet', NULL, NULL, NULL, NULL, 'hdnggfsd', 'testUser', '085899092202'),
(23, 'INV-20260409-1318', 6, '2026-04-08 22:59:59', 200.00, 'dikirim', 'lunas', 'Transfer Bank', NULL, NULL, NULL, NULL, 'jl grogol', 'rifat', '085899092202'),
(24, 'INV-20260409-9211', 6, '2026-04-08 23:06:47', 200.00, 'pending', 'belum_bayar', 'Transfer Bank', NULL, NULL, NULL, NULL, 'gfgfgfgfgfg', 'hfhfh', '085899092202'),
(25, 'INV-20260409-1637', 6, '2026-04-08 23:29:24', 200.00, 'pending', 'belum_bayar', 'Transfer Bank', NULL, NULL, NULL, NULL, 'Jl. Jalan', 'testUser', '085899092202'),
(26, 'INV-20260409-4077', 6, '2026-04-09 05:11:39', 200.00, 'diproses', 'lunas', 'Transfer Bank', 'BCA', 'J&T', 'Kilat (1 Hari)', 'RESI-D3C20AF3', 'Jl. Jalan', 'testUser', '085899092202'),
(27, 'INV-20260409-6694', 6, '2026-04-09 05:23:44', 200.00, 'selesai', 'lunas', 'E-Wallet', 'ShopeePay', 'J&T', 'Same Day (4 Jam)', 'RESI-E4795356', 'Jl. Kutilang IV', 'Rifat', '87875565546'),
(28, 'INV-20260409-8373', 6, '2026-04-09 05:27:31', 200.00, 'pending', 'belum_bayar', 'Transfer Bank', 'BCA', 'JNE', 'Reguler (2-4 Hari)', 'RESI-24276EFE', 'hkhgggg', 'rifat', '085899092202'),
(29, 'INV-20260409-6388', 6, '2026-04-09 05:45:53', 200.00, 'pending', 'belum_bayar', 'Transfer Bank', 'BRI', 'J&T', 'Kilat (1 Hari)', 'RESI-6A7982BE', 'jl grogol', 'bayu', '085899092202');

-- --------------------------------------------------------

--
-- Table structure for table `petugas`
--

CREATE TABLE `petugas` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `level` enum('admin','kasir','user') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `petugas`
--

INSERT INTO `petugas` (`id`, `username`, `password`, `nama_lengkap`, `level`, `created_at`) VALUES
(1, 'admin', '$2a$12$T/raz7XCPDG1XRSLU3aAHeqIONugs3rY2h/cgJAzpMIDdJOknYIaS', 'Administrator', 'admin', '2026-02-26 03:30:38'),
(6, 'user', '$2y$10$9rbWcYRNESjV7S.66PioKu2fMmH7FgoBUJKMYj6vH2a5JoS6T7CHq', 'testUser', 'user', '2026-04-02 00:28:05'),
(7, 'petugas', '$2y$10$ehrVGFXBV1cyRH6VysWW4uMxjo/49guKZQ72gFr/V8tumRMhZn4XW', 'Petugas', 'kasir', '2026-04-02 05:27:07');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id` int(11) NOT NULL,
  `kode_produk` varchar(50) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `id_kategori` int(11) DEFAULT NULL,
  `harga` decimal(10,2) NOT NULL,
  `stok` int(11) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `status` enum('tersedia','habis','diskon') DEFAULT 'tersedia',
  `gambar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id`, `kode_produk`, `nama_produk`, `id_kategori`, `harga`, `stok`, `deskripsi`, `status`, `gambar`, `created_at`) VALUES
(1, '123321', 'Bonsai', 4, 200.00, 15, 'Tanaman Bonsai\r\n', 'tersedia', '69ce028687f7b.jpg', '2026-02-26 04:36:53'),
(2, '123454', 'Ficus Banjamina', 4, 200.00, 15, '', 'tersedia', '69ce01ec38162.webp', '2026-02-26 04:37:32'),
(3, '1234321', 'Lidah Buaya', 4, 200.00, 15, '', 'tersedia', '69ce018335c13.jpg', '2026-02-26 04:37:56'),
(4, '123243', 'Janda Bolong', 4, 200.00, 15, '', 'tersedia', '69ce015ad2d4a.jpg', '2026-02-26 04:38:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_detail_pesanan` (`id_pesanan`),
  ADD KEY `fk_detail_produk` (`id_produk`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_pesanan` (`nomor_pesanan`),
  ADD KEY `fk_pesanan_petugas` (`id_pelanggan`);

--
-- Indexes for table `petugas`
--
ALTER TABLE `petugas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_produk` (`kode_produk`),
  ADD KEY `fk_produk_kategori` (`id_kategori`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `petugas`
--
ALTER TABLE `petugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `fk_detail_pesanan` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `fk_pesanan_petugas` FOREIGN KEY (`id_pelanggan`) REFERENCES `petugas` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
