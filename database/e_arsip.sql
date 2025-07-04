-- Pastikan database ada dan digunakan
CREATE DATABASE IF NOT EXISTS `e_arsip` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `e_arsip`;

-- Hapus tabel lama jika ada (untuk menghindari konflik)
DROP TABLE IF EXISTS `tbl_berita_acara`;
DROP TABLE IF EXISTS `tbl_kendaraan`;
DROP TABLE IF EXISTS `tbl_instansi`;
DROP TABLE IF EXISTS `tbl_user`;

-- Buat tabel berita acara dengan AUTO_INCREMENT
CREATE TABLE `tbl_berita_acara` (
  `id_berita_acara` int(10) NOT NULL AUTO_INCREMENT,
  `no_berita_acara` varchar(50) NOT NULL,
  `nama_pemakai` varchar(100) NOT NULL,
  `nip` varchar(25) NOT NULL,
  `unit_kerja` varchar(100) NOT NULL,
  `jabatan_pembina` varchar(100) NOT NULL,
  `no_pakta_integritas` varchar(50) NOT NULL,
  `nama_kendaraan` varchar(100) DEFAULT NULL,
  `status_bpkb` varchar(50) DEFAULT NULL,
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
  PRIMARY KEY (`id_berita_acara`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Buat tabel kendaraan dengan AUTO_INCREMENT
CREATE TABLE `tbl_kendaraan` (
  `id_kendaraan` int(10) NOT NULL AUTO_INCREMENT,
  `jenis_kendaraan` varchar(50) NOT NULL,
  `merk_type` varchar(100) NOT NULL,
  `tahun` varchar(4) NOT NULL,
  `no_polisi` varchar(15) NOT NULL,
  `warna` varchar(30) NOT NULL,
  `no_mesin` varchar(50) NOT NULL,
  `no_rangka` varchar(50) NOT NULL,
  `penanggung_jawab` varchar(100) NOT NULL,
  `pemakai` varchar(100) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `id_user` int(10) NOT NULL,
  PRIMARY KEY (`id_kendaraan`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Buat tabel instansi
CREATE TABLE `tbl_instansi` (
  `id_instansi` tinyint(1) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `alamat` varchar(150) NOT NULL,
  `kepala_dinas` varchar(50) NOT NULL,
  `nip` varchar(25) NOT NULL,
  `website` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `logo` varchar(250) NOT NULL,
  `id_user` tinyint(2) NOT NULL,
  PRIMARY KEY (`id_instansi`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Insert data instansi
INSERT INTO `tbl_instansi` (`id_instansi`, `nama`, `alamat`, `kepala_dinas`, `nip`, `website`, `email`, `logo`, `id_user`) VALUES
(1, 'Dinas Tenaga Kerja', 'Jl. Contoh No. 123, Kota Contoh', 'John Doe', '12345678901234567890', 'https://disnaker.contoh.go.id', 'info@disnaker.contoh.go.id', 'asset/img/logo2.png', 1);

-- Buat tabel user dengan AUTO_INCREMENT
CREATE TABLE `tbl_user` (
  `id_user` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nip` varchar(25) NOT NULL,
  `admin` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  INDEX `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data user with secure password hashing
-- Default passwords: admin = 'admin123', user = 'user123'
-- These should be changed immediately after installation
INSERT INTO `tbl_user` (`id_user`, `username`, `password`, `nama`, `nip`, `admin`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', '12345678901234567890', 1),
(2, 'user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'User Biasa', '09876543210987654321', 0);

-- Note: Default password for both users is 'password'
-- Please change these passwords immediately after installation for security