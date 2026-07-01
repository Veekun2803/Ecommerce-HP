-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 27 Jun 2026 pada 04.51
-- Versi server: 10.4.27-MariaDB
-- Versi PHP: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_hp`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `nama_lengkap`) VALUES
(2, 'faisal', '$2y$10$J3flKQYOlnAN.9bv68KmCetrvJ9995BkYEsER3ByIyif6mk4Rvnrq', 'FAISAL DWIKI'),
(3, 'admin', '$2y$10$ej7it9ED.FceOhjjw9oqAOoxsLBRf6q0LXqk01rPFURckz6/x.4bm', 'admin');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `phone_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `cart`
--

INSERT INTO `cart` (`id`, `customer_id`, `phone_id`, `qty`, `created_at`) VALUES
(38, 2, 15, 1, '2026-04-26 12:56:33'),
(39, 2, 14, 1, '2026-04-26 12:56:33'),
(46, 4, 14, 1, '2026-05-14 12:13:14');

-- --------------------------------------------------------

--
-- Struktur dari tabel `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `no_wa` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `customers`
--

INSERT INTO `customers` (`id`, `nama_lengkap`, `email`, `password`, `created_at`, `no_wa`, `alamat`) VALUES
(1, 'FAISAL DWIKI NURDIANSYAH', 'faisal@gmail.com', '$2y$10$J3flKQYOlnAN.9bv68KmCetrvJ9995BkYEsER3ByIyif6mk4Rvnrq', '2026-04-25 14:14:51', '0899998876', 'jalan sehat'),
(2, 'jaka kentir', 'jaka123@gmail.com', '$2y$10$RxFsuL3olVB1QDF1zI1Gdeex5V47lnF0IO5FKHFqV9NFdoyIWOQrK', '2026-04-25 14:33:40', '085555555555555', 'jalan duluuuuuuuuuuuuuuuu'),
(4, 'ABDUL ROHMAN ', 'abdul@gmail.com', '$2y$10$3P9zNxepH3kbtr7SYyFbb.vdJjtA7YuDRwrgcytgNAwGiD6lLeUfm', '2026-05-14 11:48:33', '0859999999', 'jalannnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnn'),
(5, 'rahmat', 'rahmat@gmail.com', '$2y$10$lFiwXp7yWZygsEQ18xytTerMIaNtAkluuGxVtLNJ/Zp7afVzcdBte', '2026-06-23 14:21:32', NULL, NULL),
(6, 'admin', 'admin@gmail.com', '$2y$10$ej7it9ED.FceOhjjw9oqAOoxsLBRf6q0LXqk01rPFURckz6/x.4bm', '2026-06-27 02:35:04', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `total` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `metode` varchar(20) DEFAULT NULL,
  `bukti` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `no_resi` varchar(50) DEFAULT NULL,
  `no_wa` varchar(20) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`id`, `nama`, `alamat`, `total`, `created_at`, `metode`, `bukti`, `status`, `no_resi`, `no_wa`, `customer_id`) VALUES
(25, 'FAISAL DWIKI NURDIANSYAH', 'jalan oiiiiiii', 2400000, '2026-06-23 13:54:36', 'bank', '6a3a901c31247.png', 'ditolak', NULL, '0899999999999', 1),
(26, 'FAISAL DWIKI NURDIANSYAH', 'jalan sehat', 9799000, '2026-06-25 16:57:18', 'bank', '6a3d5dee581a8.png', 'lunas', 'SPX12345678', '089999999997877', 1),
(27, 'FAISAL DWIKI NURDIANSYAH', 'jalan sehat', 5599000, '2026-06-27 01:06:24', 'bank', '6a3f2210931eb.jpg', 'pending', NULL, '089999999997877', 1),
(28, 'FAISAL DWIKI NURDIANSYAH', 'jalan sehat', 9799000, '2026-06-27 02:44:12', 'bank', '6a3f38fc1b6d6.jpg', 'lunas', 'JNE123456', '0899998876', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `phone_id` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `phone_id`, `qty`, `harga`) VALUES
(40, 25, 15, 1, 2400000),
(41, 26, 27, 1, 4200000),
(42, 26, 26, 1, 5599000),
(43, 27, 26, 1, 5599000),
(44, 28, 26, 1, 5599000),
(45, 28, 27, 1, 4200000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `phones`
--

CREATE TABLE `phones` (
  `id` int(11) NOT NULL,
  `nama_hp` varchar(255) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `harga` decimal(10,2) DEFAULT NULL,
  `stok` int(11) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `spesifikasi` text DEFAULT NULL,
  `kategori` enum('Android','iPhone') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `phones`
--

INSERT INTO `phones` (`id`, `nama_hp`, `brand`, `harga`, `stok`, `deskripsi`, `spesifikasi`, `kategori`, `created_at`) VALUES
(13, 'Iphone 12 basic', 'Apple', '3200000.00', 99, 'second inter', 'ram 6 / 128', 'iPhone', '2026-04-09 07:11:07'),
(14, 'Motorola Edge 60 Fussion', 'Motorola', '3700000.00', 99, 'Bekas - Seperti Baru', '12/256 HP pemakaian pribadi. No minus. Fullset Original. Masih garansi sampai Desember 2026', 'Android', '2026-04-09 13:55:09'),
(15, 'Poco F4 ', 'Xiaomi', '2400000.00', 99, 'Poco F4 \r\nCas + Dos ada lengkap\r\nLecet pemakaian', '8/256', 'Android', '2026-04-09 14:10:25'),
(26, 'iPhone 14 Second Original iPhone 14', 'Apple', '5599000.00', 97, 'Kondisi: Bekas\r\nEtalase: Semua Etalase\r\nKondisi:Pernah Dipakai', 'Merek:Apple\r\nKapasitas Penyimpanan:512GB\r\nResolusi Kamera Utama:12MP\r\nJenis Garansi:Garansi Supplier\r\nRAM:6GB\r\nJumlah Slot Kartu SIM:1\r\nJumlah Kamera Utama:2\r\nFitur Handphone:GPS, NFC, Wi-Fi\r\nTipe Handphone:Smartphone\r\nSistem Operasi yang Didukung:iOS\r\nTipe Kabel Seluler:Type C\r\nTipe SIM:Nano\r\nTipe Pengaman Layar:Lainnya\r\nTipe Case:Lainnya\r\nROM:6GB\r\nModel Handphone:iPhone 14\r\nTipe Prosesor:Apple A15 Bionic\r\nJaringan:5G/LTE\r\nKondisi:Pernah Dipakai\r\nKapasitas Baterai:3279mAh\r\nUkuran Layar:6.1inches', 'iPhone', '2026-06-17 13:10:46'),
(27, 'Pixel 8a', 'Pixel', '4200000.00', 98, 'Pixel 8A \r\nPemakaian hampir setahun sinyal dan fungsinya semua aman.\r\nKelengkapan Fullset + Case\r\nMulus sesuai gambar.', 'Ram:8\r\nInternal:128', 'Android', '2026-06-17 13:29:49'),
(31, 'Iphone', 'Iphone', '3000000.00', 99, 'test', 'test:9gb', 'iPhone', '2026-06-27 02:01:37');

-- --------------------------------------------------------

--
-- Struktur dari tabel `phone_images`
--

CREATE TABLE `phone_images` (
  `id` int(11) NOT NULL,
  `phone_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `phone_images`
--

INSERT INTO `phone_images` (`id`, `phone_id`, `image`) VALUES
(48, 13, '69d7510b043e9.jpg'),
(49, 13, '69d7510b04bb2.jpg'),
(50, 13, '69d7510b052f0.jpg'),
(52, 14, '69d7afbd82a89.jpg'),
(53, 14, '69d7afbd83229.jpg'),
(54, 14, '69d7afbd8374e.jpg'),
(55, 14, '69d7afbd83d8f.jpg'),
(56, 15, '69d7b3515aec5.jpg'),
(57, 15, '69d7b3515b74f.jpg'),
(70, 26, '6a329cd6ce8aa.jpeg'),
(71, 26, '6a329cd6cebf4.jpeg'),
(74, 27, '6a32a17ed5c90.jpeg'),
(76, 27, '6a32a18438503.jpeg'),
(80, 31, '6a3f2f017f71e.jpg');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indeks untuk tabel `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `phones`
--
ALTER TABLE `phones`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `phone_images`
--
ALTER TABLE `phone_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `phone_id` (`phone_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT untuk tabel `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT untuk tabel `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT untuk tabel `phones`
--
ALTER TABLE `phones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT untuk tabel `phone_images`
--
ALTER TABLE `phone_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `phone_images`
--
ALTER TABLE `phone_images`
  ADD CONSTRAINT `phone_images_ibfk_1` FOREIGN KEY (`phone_id`) REFERENCES `phones` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
