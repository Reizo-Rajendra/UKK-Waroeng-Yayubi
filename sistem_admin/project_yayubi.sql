-- phpMyAdmin SQL Dump
-- Database Catering: project_yayubi
-- Project: UKK Rekayasa Perangkat Lunak - Dapoer Yayubi
-- Host: 127.0.0.1
-- Versi Server: MariaDB / MySQL

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;
SET time_zone = "+07:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project_yayubi`
--
CREATE DATABASE IF NOT EXISTS `project_yayubi` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `project_yayubi`;

-- --------------------------------------------------------

--
-- Struktur dari tabel `penjual` (Akun Admin Catering)
--

DROP TABLE IF EXISTS `penjual`;
CREATE TABLE `penjual` (
  `id_penjual` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id_penjual`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data default tabel `penjual` (Akun Admin)
-- Password: admin123 (ter-enkripsi bcrypt)
--

INSERT INTO `penjual` (`id_penjual`, `nama`, `email`, `password`, `no_hp`, `alamat`) VALUES
(1, 'Admin Dapoer Yayubi', 'admin@yayubi.com', '$2y$10$z4MURsHk2g6cyqYdAsFjD.VFL4pT/5FLGcd5AUgCXF.N5VbOThDuG', '081234567890', 'Jl. Sukarno Hatta No. 88, Malang');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembeli` (Data Pelanggan Catering)
--

DROP TABLE IF EXISTS `pembeli`;
CREATE TABLE `pembeli` (
  `id_pembeli` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pembeli`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data default tabel `pembeli`
-- Password: pembeli123
--

INSERT INTO `pembeli` (`id_pembeli`, `nama`, `email`, `password`, `no_hp`, `alamat`) VALUES
(1, 'Siti Rahmawati', 'siti@gmail.com', '$2y$10$dMtO.1IzDl0RXoZ22hjGYu4177Ajs7H/82xlOzOzcemBoDFC9c05.', '081298765432', 'Jl. Mawar Indah Blok B4, Malang'),
(2, 'Budi Santoso', 'budi@gmail.com', '$2y$10$dMtO.1IzDl0RXoZ22hjGYu4177Ajs7H/82xlOzOzcemBoDFC9c05.', '085712348899', 'Perum Permata Jingga No. 12, Malang');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk` (Katalog Menu Catering - Master CRUD)
--

DROP TABLE IF EXISTS `produk`;
CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL AUTO_INCREMENT,
  `id_penjual` int(11) DEFAULT 1,
  `nama_produk` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL DEFAULT 'Nasi Box',
  `harga` decimal(10,2) NOT NULL DEFAULT 0.00,
  `deskripsi` text DEFAULT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `gambar` varchar(255) DEFAULT 'default.png',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=aktif/tampil, 0=nonaktif/terhapus (soft delete)',
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id_produk`),
  KEY `id_penjual` (`id_penjual`),
  CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`id_penjual`) REFERENCES `penjual` (`id_penjual`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data default tabel `produk`
--

INSERT INTO `produk` (`id_produk`, `id_penjual`, `nama_produk`, `kategori`, `harga`, `deskripsi`, `stok`, `gambar`) VALUES
-- MENU TUMPENG
(1, 1, 'Tumpeng Besar', 'Tumpeng', 600000.00, 'Nikmati sajian lengkap dalam satu paket praktis dan lezat! Nasi pulen disajikan dengan lauk pilihan seperti ayam goreng berbumbu, telur, sayur segar, dan sambal nikmat 🌶️', 15, 'tumpeng besar.jpeg'),
(2, 1, 'Tumpeng Kecil', 'Tumpeng', 350000.00, 'Sederhana tapi tetap istimewa! Tumpeng kecil dengan nasi pulen, dilengkapi lauk pilihan seperti ayam goreng, telur, sayur segar, dan sambal yang menggugah selera 🌶️', 20, 'tumpeng kecil.jpeg'),

-- MENU NASI BOX
(4, 1, 'Nasi Dus Ayam Goreng', 'Nasi Box', 20000.00, 'Nasi pulen + Ayam Goreng renyah gurih + Sambal khas + Lalapan segar 🍗', 50, 'nasiayamgorengsambellalap.jpeg'),
(5, 1, 'Nasi Dus Lengkap', 'Nasi Box', 25000.00, 'Nasi + Ayam Goreng + Cap Cay lezat + Bakmi goreng gurih + Sambal + Telur 🍜', 40, 'nasi capcay.jpeg'),
(6, 1, 'Nasi Dus Gudangan', 'Nasi Box', 20000.00, 'Nasi + Gudangan Sayur segar bumbu kelapa + Tahu/Tempe bacem + Telur Bulat 🥗', 35, 'nasi gudangan.jpeg'),
(7, 1, 'Nasi Dus Kering Tempe', 'Nasi Box', 25000.00, 'Nasi + Kering Tempe manis gurih + Telur Dadar + Sambal Goreng + Kerupuk renyah 🥚', 30, 'nasi kering tempe.jpeg'),

-- MENU SNACK BOX / JAJAN TRADISIONAL
(8, 1, 'Kroket', 'Snack Box', 3000.00, 'Kroket goreng keemasan dengan isian kentang lembut berbumbu. Luar renyah, dalam gurih!', 60, 'kroket.jpeg'),
(9, 1, 'Risole Mayo', 'Snack Box', 3500.00, 'Kulit dadar tipis nan lembut diisi mayo creamy, sayuran segar, dan telur. Gurih legit!', 55, 'risol mayo.jpeg'),
(10, 1, 'Putu Ayu', 'Snack Box', 3000.00, 'Kue tradisional lembut berwarna hijau pandan wangi dengan taburan kelapa parut gurih.', 50, 'putu ayu.jpeg'),
(11, 1, 'Lemper', 'Snack Box', 3000.00, 'Ketan pulen gurih dibalut daun pisang, berisi ayam suwir bumbu rempah aromatik.', 65, 'lemper.jpeg'),
(12, 1, 'Klepon', 'Snack Box', 3500.00, 'Bola-bola ketan kenyal isi gula merah aren cair yang meledak di mulut dengan kelapa parut segar.', 45, 'klepon.jpeg'),
(13, 1, 'Nagasari', 'Snack Box', 3500.00, 'Kue tepung beras lembut berisi irisan pisang manis legit, dikukus dengan daun pisang harum.', 40, 'nagasari.jpeg'),
(14, 1, 'Dadar Gulung', 'Snack Box', 3000.00, 'Kulit pandan tipis lembut berpori dengan isian kelapa parut manis gula kelapa legit.', 50, 'dadar gulung.jpeg'),
(15, 1, 'Onde Onde', 'Snack Box', 3500.00, 'Bola kenyal bertabur wijen renyah dengan isian kacang hijau lumer lembut nan manis.', 45, 'onde onde.jpeg'),
(16, 1, 'Pukis', 'Snack Box', 3000.00, 'Kue setengah bulan empuk dan manis, dipanggang sempurna dengan aroma santan kelapa.', 50, 'pukis.jpeg'),
(17, 1, 'Macaroni Schootel', 'Snack Box', 4000.00, 'Macaroni panggang creamy dengan saus gurih dan taburan keju cheddar meleleh.', 40, 'macroni scothel.jpeg'),
(18, 1, 'Brownies', 'Snack Box', 5000.00, 'Brownies coklat moist dan fudgy, manis legit dengan aroma coklat pekat yang memikat.', 45, 'brownies.jpeg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesanan` (Transaksi Pesanan Catering)
--

DROP TABLE IF EXISTS `pesanan`;
CREATE TABLE `pesanan` (
  `id_pesanan` int(11) NOT NULL AUTO_INCREMENT,
  `id_penjual` int(11) DEFAULT 1,
  `id_pembeli` int(11) DEFAULT NULL,
  `id_produk` int(11) DEFAULT NULL,
  `jumlah` int(11) NOT NULL DEFAULT 1,
  `tanggal_pesanan` datetime DEFAULT current_timestamp(),
  `total_harga` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `catatan` text DEFAULT NULL,
  PRIMARY KEY (`id_pesanan`),
  KEY `id_penjual` (`id_penjual`),
  KEY `id_pembeli` (`id_pembeli`),
  KEY `id_produk` (`id_produk`),
  CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`id_penjual`) REFERENCES `penjual` (`id_penjual`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `pesanan_ibfk_2` FOREIGN KEY (`id_pembeli`) REFERENCES `pembeli` (`id_pembeli`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `pesanan_ibfk_3` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data default tabel `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `id_penjual`, `id_pembeli`, `id_produk`, `jumlah`, `tanggal_pesanan`, `total_harga`, `status`, `catatan`) VALUES
(1, 1, 1, 1, 1, '2026-09-20 10:15:00', 600000.00, 'Selesai', 'Syukuran keluarga jam 10 pagi, sambal dipisah'),
(2, 1, 2, 3, 25, '2026-09-21 11:30:00', 500000.00, 'Diproses', 'Rapat kantor di lantai 2, butuh sendok plastik'),
(3, 1, 1, 9, 30, '2026-09-22 08:45:00', 105000.00, 'Pending', 'Snack arisan komplek jam 3 sore');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembayaran`
--

DROP TABLE IF EXISTS `pembayaran`;
CREATE TABLE `pembayaran` (
  `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT,
  `id_pesanan` int(11) DEFAULT NULL,
  `metode_bayar` varchar(50) DEFAULT 'Transfer Bank',
  `total_bayar` decimal(10,2) DEFAULT NULL,
  `tanggal_bayar` datetime DEFAULT current_timestamp(),
  `status_pembayaran` varchar(50) DEFAULT 'Lunas',
  PRIMARY KEY (`id_pembayaran`),
  KEY `id_pesanan` (`id_pesanan`),
  CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `pembayaran` (`id_pembayaran`, `id_pesanan`, `metode_bayar`, `total_bayar`, `tanggal_bayar`, `status_pembayaran`) VALUES
(1, 1, 'BCA Transfer', 150000.00, '2026-09-20 10:20:00', 'Lunas'),
(2, 2, 'QRIS / GoPay', 560000.00, '2026-09-21 13:35:00', 'Lunas');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ulasan`
--

DROP TABLE IF EXISTS `ulasan`;
CREATE TABLE `ulasan` (
  `id_ulasan` int(11) NOT NULL AUTO_INCREMENT,
  `id_pesanan` int(11) DEFAULT NULL,
  `id_produk` int(11) DEFAULT NULL,
  `id_pembeli` int(11) DEFAULT NULL,
  `komentar` text DEFAULT NULL,
  `rating` int(11) DEFAULT 5,
  `waktu_ulasan` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_ulasan`),
  KEY `id_pesanan` (`id_pesanan`),
  KEY `id_produk` (`id_produk`),
  KEY `id_pembeli` (`id_pembeli`),
  CONSTRAINT `ulasan_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ulasan_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ulasan_ibfk_3` FOREIGN KEY (`id_pembeli`) REFERENCES `pembeli` (`id_pembeli`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `ulasan` (`id_ulasan`, `id_pesanan`, `id_produk`, `id_pembeli`, `komentar`, `rating`) VALUES
(1, 1, 1, 1, 'Tumpengnya sangat enak, nasinya pulen dan lauknya berlimpah! Keluarga sangat puas.', 5);

-- --------------------------------------------------------

--
-- Struktur dari tabel `chat`
--

DROP TABLE IF EXISTS `chat`;
CREATE TABLE `chat` (
  `id_chat` int(11) NOT NULL AUTO_INCREMENT,
  `id_pembeli` int(11) DEFAULT NULL,
  `id_penjual` int(11) DEFAULT NULL,
  `waktu_chat` datetime DEFAULT current_timestamp(),
  `isi_chat` text DEFAULT NULL,
  PRIMARY KEY (`id_chat`),
  KEY `id_pembeli` (`id_pembeli`),
  KEY `id_penjual` (`id_penjual`),
  CONSTRAINT `chat_ibfk_1` FOREIGN KEY (`id_pembeli`) REFERENCES `pembeli` (`id_pembeli`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `chat_ibfk_2` FOREIGN KEY (`id_penjual`) REFERENCES `penjual` (`id_penjual`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
