-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for db_arsip
CREATE DATABASE IF NOT EXISTS `db_arsip` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `db_arsip`;

-- Dumping structure for table db_arsip.arsip_aktif
CREATE TABLE IF NOT EXISTS `arsip_aktif` (
  `id_arsip_aktif` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `uraian_informasi` text NOT NULL,
  `uraian_informasi_berkas` text,
  `asal_arsip` enum('internal','eksternal') NOT NULL,
  `id_kode` varchar(5) NOT NULL,
  `id_sub` varchar(20) NOT NULL,
  `id_subsub` varchar(30) DEFAULT NULL,
  `id_jenis` varchar(5) NOT NULL,
  `nomor_arsip` varchar(100) NOT NULL,
  `no_berkas` varchar(100) DEFAULT NULL,
  `jumlah` int DEFAULT NULL,
  `kurun_waktu` varchar(50) DEFAULT NULL,
  `retensi` int NOT NULL,
  `lokasi_simpan` varchar(255) NOT NULL,
  `id_metode` varchar(5) NOT NULL,
  `id_keamanan` varchar(5) DEFAULT NULL,
  `file_pdf` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_arsip_aktif`),
  KEY `user_id` (`user_id`),
  KEY `id_kode` (`id_kode`),
  KEY `id_sub` (`id_sub`),
  KEY `id_subsub` (`id_subsub`),
  KEY `id_jenis` (`id_jenis`),
  KEY `id_metode` (`id_metode`),
  KEY `id_keamanan` (`id_keamanan`),
  CONSTRAINT `arsip_aktif_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `arsip_aktif_ibfk_2` FOREIGN KEY (`id_kode`) REFERENCES `kode_klasifikasi` (`id_kode`),
  CONSTRAINT `arsip_aktif_ibfk_3` FOREIGN KEY (`id_sub`) REFERENCES `sub_klasifikasi` (`id_sub`),
  CONSTRAINT `arsip_aktif_ibfk_4` FOREIGN KEY (`id_subsub`) REFERENCES `sub_sub_klasifikasi` (`id_subsub`),
  CONSTRAINT `arsip_aktif_ibfk_5` FOREIGN KEY (`id_jenis`) REFERENCES `jenis_arsip` (`id_jenis`),
  CONSTRAINT `arsip_aktif_ibfk_6` FOREIGN KEY (`id_metode`) REFERENCES `metode_perlindungan` (`id_metode`),
  CONSTRAINT `arsip_aktif_ibfk_7` FOREIGN KEY (`id_keamanan`) REFERENCES `klasifikasi_keamanan` (`id_keamanan`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_arsip.arsip_aktif: ~0 rows (approximately)
INSERT INTO `arsip_aktif` (`id_arsip_aktif`, `user_id`, `uraian_informasi`, `uraian_informasi_berkas`, `asal_arsip`, `id_kode`, `id_sub`, `id_subsub`, `id_jenis`, `nomor_arsip`, `no_berkas`, `jumlah`, `kurun_waktu`, `retensi`, `lokasi_simpan`, `id_metode`, `id_keamanan`, `file_pdf`, `created_at`, `updated_at`) VALUES
	(1, 1, 'Laporan Analisis Intelijen Internal 2024', 'Informasi Rahasia Tingkat Tinggi Kemenkumham', 'internal', 'K02', 'PR.03', 'PR.03.01', 'J02', 'INT-44-2024', '1', 1, '2024', 10, 'Ruang Khusus Penyimpanan Aman (Vault)', 'MP03', 'KK01', 'intelijen_2024.pdf', '2025-12-11 03:42:34', '2025-12-11 03:42:34');

-- Dumping structure for table db_arsip.arsip_inaktif
CREATE TABLE IF NOT EXISTS `arsip_inaktif` (
  `id_arsip_inaktif` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `uraian_informasi` text NOT NULL,
  `asal_arsip` enum('internal','eksternal') NOT NULL,
  `id_kode` varchar(5) NOT NULL,
  `id_sub` varchar(20) NOT NULL,
  `id_subsub` varchar(30) DEFAULT NULL,
  `id_jenis` varchar(5) NOT NULL,
  `nomor_arsip` varchar(100) NOT NULL,
  `retensi` int NOT NULL,
  `kurun_waktu` varchar(50) DEFAULT NULL,
  `jumlah` int DEFAULT NULL,
  `id_tingkat` varchar(5) DEFAULT NULL,
  `keterangan` text,
  `lokasi_simpan` varchar(255) NOT NULL,
  `id_metode` varchar(5) NOT NULL,
  `file_pdf` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_arsip_inaktif`),
  KEY `user_id` (`user_id`),
  KEY `id_kode` (`id_kode`),
  KEY `id_sub` (`id_sub`),
  KEY `id_subsub` (`id_subsub`),
  KEY `id_jenis` (`id_jenis`),
  KEY `id_metode` (`id_metode`),
  KEY `fk_ai_tingkat` (`id_tingkat`),
  CONSTRAINT `arsip_inaktif_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `arsip_inaktif_ibfk_2` FOREIGN KEY (`id_kode`) REFERENCES `kode_klasifikasi` (`id_kode`),
  CONSTRAINT `arsip_inaktif_ibfk_3` FOREIGN KEY (`id_sub`) REFERENCES `sub_klasifikasi` (`id_sub`),
  CONSTRAINT `arsip_inaktif_ibfk_4` FOREIGN KEY (`id_subsub`) REFERENCES `sub_sub_klasifikasi` (`id_subsub`),
  CONSTRAINT `arsip_inaktif_ibfk_5` FOREIGN KEY (`id_jenis`) REFERENCES `jenis_arsip` (`id_jenis`),
  CONSTRAINT `arsip_inaktif_ibfk_6` FOREIGN KEY (`id_metode`) REFERENCES `metode_perlindungan` (`id_metode`),
  CONSTRAINT `fk_ai_tingkat` FOREIGN KEY (`id_tingkat`) REFERENCES `tingkat_perkembangan` (`id_tingkat`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_arsip.arsip_inaktif: ~0 rows (approximately)

-- Dumping structure for table db_arsip.arsip_permanen
CREATE TABLE IF NOT EXISTS `arsip_permanen` (
  `id_arsip_permanen` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `uraian_informasi` text NOT NULL,
  `asal_arsip` enum('internal','eksternal') NOT NULL,
  `id_kode` varchar(5) NOT NULL,
  `id_sub` varchar(20) NOT NULL,
  `id_subsub` varchar(30) DEFAULT NULL,
  `id_jenis` varchar(5) NOT NULL,
  `nomor_arsip` varchar(100) NOT NULL,
  `retensi` int NOT NULL,
  `lokasi_simpan` varchar(255) NOT NULL,
  `id_metode` varchar(5) NOT NULL,
  `file_pdf` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_arsip_permanen`),
  KEY `user_id` (`user_id`),
  KEY `id_kode` (`id_kode`),
  KEY `id_sub` (`id_sub`),
  KEY `id_subsub` (`id_subsub`),
  KEY `id_jenis` (`id_jenis`),
  KEY `id_metode` (`id_metode`),
  CONSTRAINT `arsip_permanen_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `arsip_permanen_ibfk_2` FOREIGN KEY (`id_kode`) REFERENCES `kode_klasifikasi` (`id_kode`),
  CONSTRAINT `arsip_permanen_ibfk_3` FOREIGN KEY (`id_sub`) REFERENCES `sub_klasifikasi` (`id_sub`),
  CONSTRAINT `arsip_permanen_ibfk_4` FOREIGN KEY (`id_subsub`) REFERENCES `sub_sub_klasifikasi` (`id_subsub`),
  CONSTRAINT `arsip_permanen_ibfk_5` FOREIGN KEY (`id_jenis`) REFERENCES `jenis_arsip` (`id_jenis`),
  CONSTRAINT `arsip_permanen_ibfk_6` FOREIGN KEY (`id_metode`) REFERENCES `metode_perlindungan` (`id_metode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_arsip.arsip_permanen: ~0 rows (approximately)

-- Dumping structure for table db_arsip.arsip_vital
CREATE TABLE IF NOT EXISTS `arsip_vital` (
  `id_arsip_vital` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `uraian_informasi` text NOT NULL,
  `asal_arsip` enum('internal','eksternal') NOT NULL,
  `id_kode` varchar(5) NOT NULL,
  `id_sub` varchar(20) NOT NULL,
  `id_subsub` varchar(30) DEFAULT NULL,
  `id_jenis` varchar(5) NOT NULL,
  `nomor_arsip` varchar(100) NOT NULL,
  `retensi` int NOT NULL,
  `lokasi_simpan` varchar(255) NOT NULL,
  `id_metode` varchar(5) NOT NULL,
  `file_pdf` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_arsip_vital`),
  KEY `user_id` (`user_id`),
  KEY `id_kode` (`id_kode`),
  KEY `id_sub` (`id_sub`),
  KEY `id_subsub` (`id_subsub`),
  KEY `id_jenis` (`id_jenis`),
  KEY `id_metode` (`id_metode`),
  CONSTRAINT `arsip_vital_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `arsip_vital_ibfk_2` FOREIGN KEY (`id_kode`) REFERENCES `kode_klasifikasi` (`id_kode`),
  CONSTRAINT `arsip_vital_ibfk_3` FOREIGN KEY (`id_sub`) REFERENCES `sub_klasifikasi` (`id_sub`),
  CONSTRAINT `arsip_vital_ibfk_4` FOREIGN KEY (`id_subsub`) REFERENCES `sub_sub_klasifikasi` (`id_subsub`),
  CONSTRAINT `arsip_vital_ibfk_5` FOREIGN KEY (`id_jenis`) REFERENCES `jenis_arsip` (`id_jenis`),
  CONSTRAINT `arsip_vital_ibfk_6` FOREIGN KEY (`id_metode`) REFERENCES `metode_perlindungan` (`id_metode`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_arsip.arsip_vital: ~0 rows (approximately)
INSERT INTO `arsip_vital` (`id_arsip_vital`, `user_id`, `uraian_informasi`, `asal_arsip`, `id_kode`, `id_sub`, `id_subsub`, `id_jenis`, `nomor_arsip`, `retensi`, `lokasi_simpan`, `id_metode`, `file_pdf`, `created_at`, `updated_at`) VALUES
	(10, 1, 'Dokumen Rencana Strategis Pembangunan Tahun 2024', 'internal', 'K01', 'PR.02', 'PR.02.02', 'J01', 'W.16.PB.05.01-1', 10, 'Brankas BMN Kantor Wilayah', 'MP01', 'renstra_2024.pdf', '2025-12-09 08:36:03', '2025-12-09 08:36:03'),
	(12, 1, 'Notulen Trilateral Meeting Kemenkumham 2023', 'internal', 'K01', 'PR.02', 'PR.02.03', 'J01', 'W.16.PB.05.02-1', 5, 'Ruang Arsip Utama', 'MP01', 'trilateral_meeting_2023.pdf', '2025-12-10 02:39:21', '2025-12-10 02:39:21');

-- Dumping structure for table db_arsip.files_pdf
CREATE TABLE IF NOT EXISTS `files_pdf` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_file` varchar(255) DEFAULT NULL,
  `file_data` longblob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_arsip.files_pdf: ~0 rows (approximately)

-- Dumping structure for table db_arsip.jenis_arsip
CREATE TABLE IF NOT EXISTS `jenis_arsip` (
  `id_jenis` varchar(5) NOT NULL,
  `nama_jenis` text NOT NULL,
  PRIMARY KEY (`id_jenis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_arsip.jenis_arsip: ~6 rows (approximately)
INSERT INTO `jenis_arsip` (`id_jenis`, `nama_jenis`) VALUES
	('J01', 'Arsip Aset (Administrasi Tanah, Sertifikat Tanah, BPKB Kendaraan Dinas)'),
	('J02', 'Gambar Teknik Bangunan - As Built Drawing (Blueprint)'),
	('J03', 'Pekerjaan Konstruksi (Bangunan)'),
	('J04', 'Personal File (SK CPNS, SK PNS, SK Pangkat dan Sejenisnya)'),
	('J05', 'Pemusnahan Arsip: Berita Acara Pemusnahan Arsip, Daftar Arsip yang Dimusnahkan, Rekomendasi / Pertimbangan Pemusnahan Arsip, Surat Keputusan Pemusnahan Arsip'),
	('J06', 'Arsip Keuangan');

-- Dumping structure for table db_arsip.klasifikasi_keamanan
CREATE TABLE IF NOT EXISTS `klasifikasi_keamanan` (
  `id_keamanan` varchar(5) NOT NULL,
  `nama_keamanan` varchar(50) NOT NULL,
  PRIMARY KEY (`id_keamanan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_arsip.klasifikasi_keamanan: ~4 rows (approximately)
INSERT INTO `klasifikasi_keamanan` (`id_keamanan`, `nama_keamanan`) VALUES
	('KK01', 'Sangat Rahasia'),
	('KK02', 'Rahasia'),
	('KK03', 'Biasa / Terbuka'),
	('KK04', 'Terbatas');

-- Dumping structure for table db_arsip.kode_klasifikasi
CREATE TABLE IF NOT EXISTS `kode_klasifikasi` (
  `id_kode` varchar(5) NOT NULL,
  `kode_klasifikasi` varchar(5) NOT NULL,
  `deskripsi` varchar(100) NOT NULL,
  PRIMARY KEY (`id_kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_arsip.kode_klasifikasi: ~18 rows (approximately)
INSERT INTO `kode_klasifikasi` (`id_kode`, `kode_klasifikasi`, `deskripsi`) VALUES
	('K01', 'PR', 'PERENCANAAN'),
	('K02', 'OT', 'ORGANISASI DAN TATA LAKSANA'),
	('K03', 'KP', 'KEPEGAWAIAN'),
	('K04', 'KU', 'KEUANGAN'),
	('K05', 'PB', 'PENGELOLAAN BARANG MILIK NEGARA'),
	('K06', 'HH', 'KEHUMASAN DAN HUKUM'),
	('K07', 'UM', 'UMUM'),
	('K08', 'PW', 'PENGAWASAN'),
	('K09', 'TI', 'TEKNOLOGI DAN INFORMASI'),
	('K10', 'PP', 'PERATURAN PERUNDANG-UNDANGAN'),
	('K11', 'AH', 'ADMINISTRASI DAN HUKUM'),
	('K12', 'PK', 'PEMASYARAKATAN'),
	('K13', 'GR', 'KEIMIGRASIAN'),
	('K14', 'KI', 'KEKAYAAN INTELEKTUAL'),
	('K15', 'HA', 'HAK ASASI MANUSIA (HAM)'),
	('K16', 'HN', 'PEMBINAAN HUKUM NASIONAL'),
	('K17', 'SM', 'SUMBER DAYA MANUSIA'),
	('K18', 'LT', 'PENELITIAN DAN PENGEMBANGAN');

-- Dumping structure for table db_arsip.metode_perlindungan
CREATE TABLE IF NOT EXISTS `metode_perlindungan` (
  `id_metode` varchar(5) NOT NULL,
  `nama_metode` varchar(100) NOT NULL,
  PRIMARY KEY (`id_metode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_arsip.metode_perlindungan: ~3 rows (approximately)
INSERT INTO `metode_perlindungan` (`id_metode`, `nama_metode`) VALUES
	('MP01', 'Duplikasi / Copy'),
	('MP02', 'Dispersal'),
	('MP03', 'Vaulting');

-- Dumping structure for table db_arsip.nasib_akhir
CREATE TABLE IF NOT EXISTS `nasib_akhir` (
  `id_nasib` varchar(5) NOT NULL,
  `nama_nasib` varchar(50) NOT NULL,
  PRIMARY KEY (`id_nasib`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_arsip.nasib_akhir: ~2 rows (approximately)
INSERT INTO `nasib_akhir` (`id_nasib`, `nama_nasib`) VALUES
	('NA01', 'Musnah'),
	('NA02', 'Permanen');

-- Dumping structure for table db_arsip.peminjaman_arsip
CREATE TABLE IF NOT EXISTS `peminjaman_arsip` (
  `id_peminjaman` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `arsip_type` enum('vital','permanen','aktif','inaktif') NOT NULL,
  `arsip_id` int NOT NULL,
  `uraian_informasi` text,
  `no_box` varchar(50) DEFAULT NULL,
  `kode_klasifikasi` varchar(50) DEFAULT NULL,
  `pemilik_arsip` varchar(255) DEFAULT NULL,
  `periode` varchar(100) DEFAULT NULL,
  `alasan_peminjaman` text,
  `nama_peminjam` varchar(255) DEFAULT NULL,
  `instansi_peminjam` varchar(255) DEFAULT NULL,
  `tanggal_pinjam` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tanggal_kembali` datetime DEFAULT NULL,
  `tanggal_expired` datetime NOT NULL,
  `status` enum('aktif','kembali','expired') DEFAULT 'aktif',
  `keterangan` text,
  PRIMARY KEY (`id_peminjaman`),
  KEY `idx_status` (`status`),
  KEY `idx_expired` (`tanggal_expired`),
  KEY `idx_arsip` (`arsip_type`,`arsip_id`),
  KEY `idx_user_arsip` (`user_id`,`arsip_type`,`arsip_id`),
  CONSTRAINT `peminjaman_arsip_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_arsip.peminjaman_arsip: ~0 rows (approximately)
INSERT INTO `peminjaman_arsip` (`id_peminjaman`, `user_id`, `arsip_type`, `arsip_id`, `uraian_informasi`, `no_box`, `kode_klasifikasi`, `pemilik_arsip`, `periode`, `alasan_peminjaman`, `nama_peminjam`, `instansi_peminjam`, `tanggal_pinjam`, `tanggal_kembali`, `tanggal_expired`, `status`, `keterangan`) VALUES
	(1, 1, 'vital', 10, 'Dokumen Rencana Strategis Pembangunan Tahun 2024', '12', 'PR', 'TUM', '3 hari', ' an', 'Gita', 'Internal', '2025-12-10 00:00:00', NULL, '2025-12-13 00:00:00', 'aktif', NULL);

-- Dumping structure for table db_arsip.sub_klasifikasi
CREATE TABLE IF NOT EXISTS `sub_klasifikasi` (
  `id_sub` varchar(20) NOT NULL,
  `nama_sub` varchar(255) NOT NULL,
  `id_kode` varchar(5) NOT NULL,
  PRIMARY KEY (`id_sub`),
  KEY `id_kode` (`id_kode`),
  CONSTRAINT `sub_klasifikasi_ibfk_1` FOREIGN KEY (`id_kode`) REFERENCES `kode_klasifikasi` (`id_kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_arsip.sub_klasifikasi: ~7 rows (approximately)
INSERT INTO `sub_klasifikasi` (`id_sub`, `nama_sub`, `id_kode`) VALUES
	('PR.01', 'Pokokpokok Kebijakan dan Stategi Pembangunan', 'K01'),
	('PR.02', 'Progam dan Anggaran', 'K01'),
	('PR.03', 'Evaluasi', 'K01'),
	('PR.04', 'Laporan Akuntabilitas Kinerja Instansi Pemerintah (LAKIP)', 'K01'),
	('PR.05', 'Pelaporan', 'K01'),
	('PR.06', 'Rapat Kerja', 'K01'),
	('PR.07', 'Sidang Kabinet', 'K01');

-- Dumping structure for table db_arsip.sub_sub_klasifikasi
CREATE TABLE IF NOT EXISTS `sub_sub_klasifikasi` (
  `id_subsub` varchar(30) NOT NULL,
  `nama_subsub` varchar(255) NOT NULL,
  `id_sub` varchar(20) NOT NULL,
  PRIMARY KEY (`id_subsub`),
  KEY `id_sub` (`id_sub`),
  CONSTRAINT `sub_sub_klasifikasi_ibfk_1` FOREIGN KEY (`id_sub`) REFERENCES `sub_klasifikasi` (`id_sub`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_arsip.sub_sub_klasifikasi: ~16 rows (approximately)
INSERT INTO `sub_sub_klasifikasi` (`id_subsub`, `nama_subsub`, `id_sub`) VALUES
	('PR.02.02', 'Rencana Strategis', 'PR.02'),
	('PR.02.03', 'Trilateral Meeting', 'PR.02'),
	('PR.02.04', 'Rencana Kerja', 'PR.02'),
	('PR.02.05', 'Rencana Kerja dan Anggaran', 'PR.02'),
	('PR.03.01', 'Unit Utama', 'PR.03'),
	('PR.03.02', 'Kantor Wilayah', 'PR.03'),
	('PR.05.01', 'Laporan Bulanan', 'PR.05'),
	('PR.05.02', 'Laporan Triwulan', 'PR.05'),
	('PR.05.03', 'Laporan Semester', 'PR.05'),
	('PR.05.04', 'Laporan Tahunan', 'PR.05'),
	('PR.05.05', 'Insidentil', 'PR.05'),
	('PR.06.01', 'Dengan DPR', 'PR.06'),
	('PR.06.02', 'Tingkat Kementerian', 'PR.06'),
	('PR.06.03', 'Tingkat Unit Utama (RAKERNIS)', 'PR.06'),
	('PR.06.04', 'Tingkat Kantor Wilayah', 'PR.06'),
	('PR.06.05', 'Rapat Pimpinan dan Rapat Staf', 'PR.06');

-- Dumping structure for table db_arsip.tingkat_perkembangan
CREATE TABLE IF NOT EXISTS `tingkat_perkembangan` (
  `id_tingkat` varchar(5) NOT NULL,
  `nama_tingkat` varchar(50) NOT NULL,
  PRIMARY KEY (`id_tingkat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_arsip.tingkat_perkembangan: ~3 rows (approximately)
INSERT INTO `tingkat_perkembangan` (`id_tingkat`, `nama_tingkat`) VALUES
	('TP01', 'Asli'),
	('TP02', 'Fotokopi'),
	('TP03', 'Asli dan Fotokopi');

-- Dumping structure for table db_arsip.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_arsip.users: ~1 rows (approximately)
INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `role`, `created_at`) VALUES
	(1, 'tutik', 'tutik', 'Gita Dwi Astuti', 'user', '2025-12-09 06:18:04');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
