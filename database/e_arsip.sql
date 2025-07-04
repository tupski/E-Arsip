-- Pastikan database ada dan digunakan
CREATE DATABASE IF NOT EXISTS `e_arsip` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `e_arsip`;

-- Hapus tabel lama jika ada (untuk menghindari konflik)
-- Hapus dalam urutan yang benar karena foreign key constraints
DROP TABLE IF EXISTS `tbl_berita_acara`;
DROP TABLE IF EXISTS `tbl_kendaraan`;
DROP TABLE IF EXISTS `tbl_instansi`;
DROP TABLE IF EXISTS `tbl_user`;

-- Buat tabel user terlebih dahulu (parent table)
CREATE TABLE `tbl_user` (
  `id_user` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nip` varchar(25) NOT NULL,
  `admin` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `idx_username` (`username`),
  INDEX `idx_nip` (`nip`),
  INDEX `idx_admin` (`admin`),
  INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Buat tabel instansi
CREATE TABLE `tbl_instansi` (
  `id_instansi` int(10) NOT NULL AUTO_INCREMENT,
  `nama` varchar(150) NOT NULL,
  `alamat` text NOT NULL,
  `kepala_dinas` varchar(100) NOT NULL,
  `nip` varchar(25) NOT NULL,
  `website` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `id_user` int(10) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_instansi`),
  INDEX `idx_user` (`id_user`),
  CONSTRAINT `fk_instansi_user` FOREIGN KEY (`id_user`) REFERENCES `tbl_user` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Buat tabel berita acara dengan foreign key constraints
CREATE TABLE `tbl_berita_acara` (
  `id_berita_acara` int(10) NOT NULL AUTO_INCREMENT,
  `no_berita_acara` varchar(50) NOT NULL,
  `nama_pemakai` varchar(100) NOT NULL,
  `nip` varchar(25) NOT NULL,
  `unit_kerja` varchar(100) NOT NULL,
  `jabatan_pembina` varchar(100) NOT NULL,
  `no_pakta_integritas` varchar(50) NOT NULL,
  `nama_kendaraan` varchar(100) DEFAULT NULL,
  `status_bpkb` enum('Ada', 'Tidak Ada', 'Proses') DEFAULT NULL,
  `no_bpkb` varchar(50) DEFAULT NULL,
  `barang1_qty` int(5) DEFAULT NULL,
  `barang1_nama` varchar(100) DEFAULT NULL,
  `barang2_qty` int(5) DEFAULT NULL,
  `barang2_nama` varchar(100) DEFAULT NULL,
  `barang3_qty` int(5) DEFAULT NULL,
  `barang3_nama` varchar(100) DEFAULT NULL,
  `barang4_qty` int(5) DEFAULT NULL,
  `barang4_nama` varchar(100) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `tgl_pembuatan` date NOT NULL,
  `id_user` int(10) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_berita_acara`),
  UNIQUE KEY `idx_no_berita_acara` (`no_berita_acara`),
  INDEX `idx_nama_pemakai` (`nama_pemakai`),
  INDEX `idx_nip` (`nip`),
  INDEX `idx_tgl_pembuatan` (`tgl_pembuatan`),
  INDEX `idx_user` (`id_user`),
  CONSTRAINT `fk_berita_acara_user` FOREIGN KEY (`id_user`) REFERENCES `tbl_user` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Buat tabel kendaraan dengan foreign key constraints
CREATE TABLE `tbl_kendaraan` (
  `id_kendaraan` int(10) NOT NULL AUTO_INCREMENT,
  `jenis_kendaraan` enum('Motor', 'Mobil', 'Truk', 'Bus', 'Lainnya') NOT NULL,
  `merk_type` varchar(100) NOT NULL,
  `tahun` year NOT NULL,
  `no_polisi` varchar(15) NOT NULL,
  `warna` varchar(30) NOT NULL,
  `no_mesin` varchar(50) NOT NULL,
  `no_rangka` varchar(50) NOT NULL,
  `penanggung_jawab` varchar(100) NOT NULL,
  `pemakai` varchar(100) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `id_user` int(10) NOT NULL,
  `status` enum('Aktif', 'Tidak Aktif', 'Maintenance') NOT NULL DEFAULT 'Aktif',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_kendaraan`),
  UNIQUE KEY `idx_no_polisi` (`no_polisi`),
  UNIQUE KEY `idx_no_mesin` (`no_mesin`),
  UNIQUE KEY `idx_no_rangka` (`no_rangka`),
  INDEX `idx_jenis_kendaraan` (`jenis_kendaraan`),
  INDEX `idx_tahun` (`tahun`),
  INDEX `idx_penanggung_jawab` (`penanggung_jawab`),
  INDEX `idx_pemakai` (`pemakai`),
  INDEX `idx_status` (`status`),
  INDEX `idx_user` (`id_user`),
  CONSTRAINT `fk_kendaraan_user` FOREIGN KEY (`id_user`) REFERENCES `tbl_user` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data user dengan password yang aman
-- Default passwords: admin = 'password', user = 'password'
-- PENTING: Ganti password ini setelah instalasi!
INSERT INTO `tbl_user` (`id_user`, `username`, `password`, `nama`, `nip`, `admin`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', '12345678901234567890', 1),
(2, 'user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'User Biasa', '09876543210987654321', 0);

-- Insert data instansi
INSERT INTO `tbl_instansi` (`id_instansi`, `nama`, `alamat`, `kepala_dinas`, `nip`, `website`, `email`, `logo`, `id_user`) VALUES
(1, 'Dinas Tenaga Kerja', 'Jl. Raya Puspitek – Serpong No.1 RT.018 RW.005 Gedung Depo Arsip Lt.4&5, Kelurahan Setu Kecamatan Setu Kota Tangerang Selatan', 'KHATRUDIN, SE', '19760802200901005', 'https://disnaker.tangerangselatankota.go.id', 'info@disnaker.tangerangselatankota.go.id', 'assets/img/logo2.png', 1);

-- Create additional indexes for better performance
CREATE INDEX idx_berita_acara_search ON tbl_berita_acara (nama_pemakai, nip, tgl_pembuatan);
CREATE INDEX idx_kendaraan_search ON tbl_kendaraan (no_polisi, merk_type, pemakai);

-- Note: Default password for both users is 'password'
-- Please change these passwords immediately after installation for security