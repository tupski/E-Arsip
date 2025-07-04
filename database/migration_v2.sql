-- Migration Script v2.0
-- This script updates existing database to new schema with foreign keys and improved structure
-- Run this script on existing database to upgrade

USE `e_arsip`;

-- Backup existing data before migration
CREATE TABLE IF NOT EXISTS `backup_berita_acara` AS SELECT * FROM `tbl_berita_acara`;
CREATE TABLE IF NOT EXISTS `backup_kendaraan` AS SELECT * FROM `tbl_kendaraan`;
CREATE TABLE IF NOT EXISTS `backup_user` AS SELECT * FROM `tbl_user`;
CREATE TABLE IF NOT EXISTS `backup_instansi` AS SELECT * FROM `tbl_instansi`;

-- Step 1: Update character set and collation
ALTER DATABASE `e_arsip` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Step 2: Update tbl_user structure
ALTER TABLE `tbl_user` 
  MODIFY COLUMN `id_user` int(10) NOT NULL AUTO_INCREMENT,
  MODIFY COLUMN `username` varchar(50) NOT NULL,
  MODIFY COLUMN `password` varchar(255) NOT NULL,
  MODIFY COLUMN `nama` varchar(100) NOT NULL,
  ADD COLUMN `created_at` timestamp DEFAULT CURRENT_TIMESTAMP AFTER `admin`,
  ADD COLUMN `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
  ADD COLUMN `last_login` timestamp NULL DEFAULT NULL AFTER `updated_at`,
  ADD COLUMN `is_active` tinyint(1) NOT NULL DEFAULT 1 AFTER `last_login`,
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Add indexes to tbl_user
ALTER TABLE `tbl_user` 
  ADD UNIQUE KEY `idx_username` (`username`),
  ADD INDEX `idx_nip` (`nip`),
  ADD INDEX `idx_admin` (`admin`),
  ADD INDEX `idx_active` (`is_active`);

-- Step 3: Update tbl_instansi structure
ALTER TABLE `tbl_instansi` 
  MODIFY COLUMN `id_instansi` int(10) NOT NULL AUTO_INCREMENT,
  MODIFY COLUMN `alamat` text NOT NULL,
  MODIFY COLUMN `kepala_dinas` varchar(100) NOT NULL,
  MODIFY COLUMN `website` varchar(100) DEFAULT NULL,
  MODIFY COLUMN `email` varchar(100) DEFAULT NULL,
  MODIFY COLUMN `logo` varchar(255) DEFAULT NULL,
  MODIFY COLUMN `id_user` int(10) NOT NULL,
  ADD COLUMN `created_at` timestamp DEFAULT CURRENT_TIMESTAMP AFTER `id_user`,
  ADD COLUMN `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Add foreign key to tbl_instansi
ALTER TABLE `tbl_instansi` 
  ADD INDEX `idx_user` (`id_user`),
  ADD CONSTRAINT `fk_instansi_user` FOREIGN KEY (`id_user`) REFERENCES `tbl_user` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- Step 4: Update tbl_berita_acara structure
ALTER TABLE `tbl_berita_acara` 
  MODIFY COLUMN `status_bpkb` enum('Ada', 'Tidak Ada', 'Proses') DEFAULT NULL,
  MODIFY COLUMN `id_user` int(10) NOT NULL,
  ADD COLUMN `created_at` timestamp DEFAULT CURRENT_TIMESTAMP AFTER `id_user`,
  ADD COLUMN `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Add indexes to tbl_berita_acara
ALTER TABLE `tbl_berita_acara` 
  ADD UNIQUE KEY `idx_no_berita_acara` (`no_berita_acara`),
  ADD INDEX `idx_nama_pemakai` (`nama_pemakai`),
  ADD INDEX `idx_nip` (`nip`),
  ADD INDEX `idx_tgl_pembuatan` (`tgl_pembuatan`),
  ADD INDEX `idx_user` (`id_user`),
  ADD INDEX `idx_berita_acara_search` (`nama_pemakai`, `nip`, `tgl_pembuatan`);

-- Add foreign key to tbl_berita_acara
ALTER TABLE `tbl_berita_acara` 
  ADD CONSTRAINT `fk_berita_acara_user` FOREIGN KEY (`id_user`) REFERENCES `tbl_user` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- Step 5: Update tbl_kendaraan structure
ALTER TABLE `tbl_kendaraan` 
  MODIFY COLUMN `jenis_kendaraan` enum('Motor', 'Mobil', 'Truk', 'Bus', 'Lainnya') NOT NULL,
  MODIFY COLUMN `tahun` year NOT NULL,
  MODIFY COLUMN `id_user` int(10) NOT NULL,
  ADD COLUMN `status` enum('Aktif', 'Tidak Aktif', 'Maintenance') NOT NULL DEFAULT 'Aktif' AFTER `id_user`,
  ADD COLUMN `created_at` timestamp DEFAULT CURRENT_TIMESTAMP AFTER `status`,
  ADD COLUMN `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Add indexes to tbl_kendaraan
ALTER TABLE `tbl_kendaraan` 
  ADD UNIQUE KEY `idx_no_polisi` (`no_polisi`),
  ADD UNIQUE KEY `idx_no_mesin` (`no_mesin`),
  ADD UNIQUE KEY `idx_no_rangka` (`no_rangka`),
  ADD INDEX `idx_jenis_kendaraan` (`jenis_kendaraan`),
  ADD INDEX `idx_tahun` (`tahun`),
  ADD INDEX `idx_penanggung_jawab` (`penanggung_jawab`),
  ADD INDEX `idx_pemakai` (`pemakai`),
  ADD INDEX `idx_status` (`status`),
  ADD INDEX `idx_user` (`id_user`),
  ADD INDEX `idx_kendaraan_search` (`no_polisi`, `merk_type`, `pemakai`);

-- Add foreign key to tbl_kendaraan
ALTER TABLE `tbl_kendaraan` 
  ADD CONSTRAINT `fk_kendaraan_user` FOREIGN KEY (`id_user`) REFERENCES `tbl_user` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- Step 6: Update existing data
-- Update jenis_kendaraan values to match enum
UPDATE `tbl_kendaraan` SET `jenis_kendaraan` = 'Motor' WHERE `jenis_kendaraan` IN ('motor', 'Motor', 'MOTOR');
UPDATE `tbl_kendaraan` SET `jenis_kendaraan` = 'Mobil' WHERE `jenis_kendaraan` IN ('mobil', 'Mobil', 'MOBIL', 'car', 'Car');
UPDATE `tbl_kendaraan` SET `jenis_kendaraan` = 'Truk' WHERE `jenis_kendaraan` IN ('truk', 'Truk', 'TRUK', 'truck', 'Truck');
UPDATE `tbl_kendaraan` SET `jenis_kendaraan` = 'Bus' WHERE `jenis_kendaraan` IN ('bus', 'Bus', 'BUS');
UPDATE `tbl_kendaraan` SET `jenis_kendaraan` = 'Lainnya' WHERE `jenis_kendaraan` NOT IN ('Motor', 'Mobil', 'Truk', 'Bus');

-- Update status_bpkb values to match enum
UPDATE `tbl_berita_acara` SET `status_bpkb` = 'Ada' WHERE `status_bpkb` IN ('ada', 'Ada', 'ADA', 'tersedia', 'Tersedia');
UPDATE `tbl_berita_acara` SET `status_bpkb` = 'Tidak Ada' WHERE `status_bpkb` IN ('tidak ada', 'Tidak Ada', 'TIDAK ADA', 'tidak tersedia', 'kosong');
UPDATE `tbl_berita_acara` SET `status_bpkb` = 'Proses' WHERE `status_bpkb` IN ('proses', 'Proses', 'PROSES', 'dalam proses');

-- Step 7: Create views for easier data access
CREATE OR REPLACE VIEW `view_berita_acara_detail` AS
SELECT 
    ba.*,
    u.nama as created_by_name,
    u.username as created_by_username
FROM `tbl_berita_acara` ba
LEFT JOIN `tbl_user` u ON ba.id_user = u.id_user;

CREATE OR REPLACE VIEW `view_kendaraan_detail` AS
SELECT 
    k.*,
    u.nama as created_by_name,
    u.username as created_by_username
FROM `tbl_kendaraan` k
LEFT JOIN `tbl_user` u ON k.id_user = u.id_user;

-- Step 8: Create audit log table for tracking changes
CREATE TABLE IF NOT EXISTS `tbl_audit_log` (
  `id_log` int(10) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(50) NOT NULL,
  `record_id` int(10) NOT NULL,
  `action` enum('INSERT', 'UPDATE', 'DELETE') NOT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `user_id` int(10) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`),
  INDEX `idx_table_record` (`table_name`, `record_id`),
  INDEX `idx_action` (`action`),
  INDEX `idx_user` (`user_id`),
  INDEX `idx_created_at` (`created_at`),
  CONSTRAINT `fk_audit_log_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migration completed successfully
SELECT 'Database migration to v2.0 completed successfully!' as status;
