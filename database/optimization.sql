-- Database Performance Optimization
-- Additional indexes and query optimizations for E-Arsip

USE `e_arsip`;

-- Add composite indexes for common query patterns
CREATE INDEX idx_berita_acara_user_date ON tbl_berita_acara (id_user, tgl_pembuatan);
CREATE INDEX idx_berita_acara_status_date ON tbl_berita_acara (status_bpkb, tgl_pembuatan);
CREATE INDEX idx_berita_acara_nama_nip ON tbl_berita_acara (nama_pemakai, nip);

CREATE INDEX idx_kendaraan_user_status ON tbl_kendaraan (id_user, status);
CREATE INDEX idx_kendaraan_jenis_tahun ON tbl_kendaraan (jenis_kendaraan, tahun);
CREATE INDEX idx_kendaraan_pemakai_status ON tbl_kendaraan (pemakai, status);

CREATE INDEX idx_user_admin_active ON tbl_user (admin, is_active);
CREATE INDEX idx_user_created_at ON tbl_user (created_at);
CREATE INDEX idx_user_last_login ON tbl_user (last_login);

-- Add full-text search indexes for better search performance
ALTER TABLE tbl_berita_acara ADD FULLTEXT(nama_pemakai, unit_kerja, keterangan);
ALTER TABLE tbl_kendaraan ADD FULLTEXT(merk_type, penanggung_jawab, pemakai);
ALTER TABLE tbl_user ADD FULLTEXT(nama);

-- Optimize table storage
OPTIMIZE TABLE tbl_user;
OPTIMIZE TABLE tbl_berita_acara;
OPTIMIZE TABLE tbl_kendaraan;
OPTIMIZE TABLE tbl_instansi;
OPTIMIZE TABLE tbl_audit_log;

-- Add partitioning for audit log table (by month)
ALTER TABLE tbl_audit_log PARTITION BY RANGE (YEAR(created_at) * 100 + MONTH(created_at)) (
    PARTITION p202401 VALUES LESS THAN (202402),
    PARTITION p202402 VALUES LESS THAN (202403),
    PARTITION p202403 VALUES LESS THAN (202404),
    PARTITION p202404 VALUES LESS THAN (202405),
    PARTITION p202405 VALUES LESS THAN (202406),
    PARTITION p202406 VALUES LESS THAN (202407),
    PARTITION p202407 VALUES LESS THAN (202408),
    PARTITION p202408 VALUES LESS THAN (202409),
    PARTITION p202409 VALUES LESS THAN (202410),
    PARTITION p202410 VALUES LESS THAN (202411),
    PARTITION p202411 VALUES LESS THAN (202412),
    PARTITION p202412 VALUES LESS THAN (202501),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);

-- Create views for commonly used queries
CREATE OR REPLACE VIEW view_dashboard_stats AS
SELECT 
    (SELECT COUNT(*) FROM tbl_user WHERE is_active = 1) as total_users,
    (SELECT COUNT(*) FROM tbl_berita_acara) as total_berita_acara,
    (SELECT COUNT(*) FROM tbl_kendaraan WHERE status = 'Aktif') as total_kendaraan_aktif,
    (SELECT COUNT(*) FROM tbl_berita_acara WHERE MONTH(tgl_pembuatan) = MONTH(CURDATE()) AND YEAR(tgl_pembuatan) = YEAR(CURDATE())) as berita_acara_bulan_ini,
    (SELECT COUNT(*) FROM tbl_kendaraan WHERE YEAR(created_at) = YEAR(CURDATE())) as kendaraan_tahun_ini;

CREATE OR REPLACE VIEW view_recent_activities AS
SELECT 
    'berita_acara' as type,
    id_berita_acara as item_id,
    no_berita_acara as title,
    nama_pemakai as description,
    created_at,
    id_user
FROM tbl_berita_acara
UNION ALL
SELECT 
    'kendaraan' as type,
    id_kendaraan as item_id,
    no_polisi as title,
    CONCAT(merk_type, ' - ', pemakai) as description,
    created_at,
    id_user
FROM tbl_kendaraan
ORDER BY created_at DESC
LIMIT 20;

-- Performance monitoring queries
CREATE OR REPLACE VIEW view_slow_queries AS
SELECT 
    sql_text,
    exec_count,
    avg_timer_wait/1000000000 as avg_time_seconds,
    sum_timer_wait/1000000000 as total_time_seconds
FROM performance_schema.events_statements_summary_by_digest 
WHERE avg_timer_wait > 1000000000  -- queries taking more than 1 second
ORDER BY avg_timer_wait DESC
LIMIT 10;

-- Database maintenance procedures
DELIMITER //

CREATE PROCEDURE CleanupOldAuditLogs(IN days_to_keep INT)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    DELETE FROM tbl_audit_log 
    WHERE created_at < DATE_SUB(NOW(), INTERVAL days_to_keep DAY);
    
    COMMIT;
END //

CREATE PROCEDURE OptimizeAllTables()
BEGIN
    OPTIMIZE TABLE tbl_user;
    OPTIMIZE TABLE tbl_berita_acara;
    OPTIMIZE TABLE tbl_kendaraan;
    OPTIMIZE TABLE tbl_instansi;
    OPTIMIZE TABLE tbl_audit_log;
END //

CREATE PROCEDURE GetTableSizes()
BEGIN
    SELECT 
        table_name,
        ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)',
        table_rows
    FROM information_schema.tables 
    WHERE table_schema = DATABASE()
    ORDER BY (data_length + index_length) DESC;
END //

DELIMITER ;

-- Create events for automatic maintenance
CREATE EVENT IF NOT EXISTS cleanup_old_audit_logs
ON SCHEDULE EVERY 1 WEEK
STARTS CURRENT_TIMESTAMP
DO
    CALL CleanupOldAuditLogs(90);  -- Keep 90 days of audit logs

CREATE EVENT IF NOT EXISTS optimize_tables_weekly
ON SCHEDULE EVERY 1 WEEK
STARTS CURRENT_TIMESTAMP + INTERVAL 1 DAY
DO
    CALL OptimizeAllTables();

-- Enable event scheduler
SET GLOBAL event_scheduler = ON;

-- Performance tuning recommendations (comments for DBA)
/*
Recommended MySQL configuration for better performance:

[mysqld]
# InnoDB settings
innodb_buffer_pool_size = 70% of available RAM
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

# Query cache (for MySQL 5.7 and below)
query_cache_type = 1
query_cache_size = 64M
query_cache_limit = 2M

# Connection settings
max_connections = 200
thread_cache_size = 16

# Temporary tables
tmp_table_size = 64M
max_heap_table_size = 64M

# Slow query log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
*/
