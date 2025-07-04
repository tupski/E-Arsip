<?php
/**
 * Database Performance Monitor
 * Monitors and optimizes database performance
 */

class DatabaseMonitor {
    private $db;
    private $queryLog = [];
    private $slowQueryThreshold = 1.0; // seconds
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Execute query with performance monitoring
     */
    public function executeQuery($sql, $params = [], $logQuery = true) {
        $startTime = microtime(true);
        
        if (empty($params)) {
            $result = mysqli_query($this->db, $sql);
        } else {
            $stmt = mysqli_prepare($this->db, $sql);
            if ($stmt) {
                $types = str_repeat('s', count($params));
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                mysqli_stmt_close($stmt);
            } else {
                $result = false;
            }
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        if ($logQuery) {
            $this->logQuery($sql, $params, $executionTime);
        }
        
        return $result;
    }
    
    /**
     * Log query execution
     */
    private function logQuery($sql, $params, $executionTime) {
        $queryInfo = [
            'sql' => $sql,
            'params' => $params,
            'execution_time' => $executionTime,
            'timestamp' => microtime(true),
            'memory_usage' => memory_get_usage(true)
        ];
        
        $this->queryLog[] = $queryInfo;
        
        // Log slow queries
        if ($executionTime > $this->slowQueryThreshold) {
            $this->logSlowQuery($queryInfo);
        }
    }
    
    /**
     * Log slow queries
     */
    private function logSlowQuery($queryInfo) {
        app_log('warning', 'Slow query detected', [
            'sql' => $queryInfo['sql'],
            'execution_time' => $queryInfo['execution_time'],
            'params' => $queryInfo['params']
        ]);
    }
    
    /**
     * Get query statistics
     */
    public function getQueryStats() {
        if (empty($this->queryLog)) {
            return null;
        }
        
        $totalQueries = count($this->queryLog);
        $totalTime = array_sum(array_column($this->queryLog, 'execution_time'));
        $avgTime = $totalTime / $totalQueries;
        $slowQueries = array_filter($this->queryLog, function($query) {
            return $query['execution_time'] > $this->slowQueryThreshold;
        });
        
        return [
            'total_queries' => $totalQueries,
            'total_time' => $totalTime,
            'average_time' => $avgTime,
            'slow_queries' => count($slowQueries),
            'memory_peak' => memory_get_peak_usage(true)
        ];
    }
    
    /**
     * Get database table sizes
     */
    public function getTableSizes() {
        $sql = "SELECT 
                    table_name,
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'size_mb',
                    table_rows
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()
                ORDER BY (data_length + index_length) DESC";
        
        $result = $this->executeQuery($sql, [], false);
        $tables = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $tables[] = $row;
        }
        
        return $tables;
    }
    
    /**
     * Get database status
     */
    public function getDatabaseStatus() {
        $status = [];
        
        // Get connection info
        $result = mysqli_query($this->db, "SHOW STATUS LIKE 'Connections'");
        $row = mysqli_fetch_assoc($result);
        $status['total_connections'] = $row['Value'];
        
        // Get thread info
        $result = mysqli_query($this->db, "SHOW STATUS LIKE 'Threads_connected'");
        $row = mysqli_fetch_assoc($result);
        $status['active_connections'] = $row['Value'];
        
        // Get query cache info (if available)
        $result = mysqli_query($this->db, "SHOW STATUS LIKE 'Qcache_hits'");
        if ($row = mysqli_fetch_assoc($result)) {
            $status['query_cache_hits'] = $row['Value'];
        }
        
        // Get slow query count
        $result = mysqli_query($this->db, "SHOW STATUS LIKE 'Slow_queries'");
        $row = mysqli_fetch_assoc($result);
        $status['slow_queries'] = $row['Value'];
        
        return $status;
    }
    
    /**
     * Analyze table performance
     */
    public function analyzeTablePerformance($tableName) {
        $analysis = [];
        
        // Check table status
        $result = $this->executeQuery("SHOW TABLE STATUS LIKE ?", [$tableName], false);
        $tableStatus = mysqli_fetch_assoc($result);
        $analysis['table_status'] = $tableStatus;
        
        // Check indexes
        $result = $this->executeQuery("SHOW INDEX FROM $tableName", [], false);
        $indexes = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $indexes[] = $row;
        }
        $analysis['indexes'] = $indexes;
        
        // Check for unused indexes
        $analysis['index_recommendations'] = $this->getIndexRecommendations($tableName);
        
        return $analysis;
    }
    
    /**
     * Get index recommendations
     */
    private function getIndexRecommendations($tableName) {
        $recommendations = [];
        
        // This is a simplified version - in production, you'd analyze query patterns
        switch ($tableName) {
            case 'tbl_berita_acara':
                $recommendations[] = 'Consider adding composite index on (id_user, tgl_pembuatan) for user-specific date queries';
                $recommendations[] = 'Consider adding full-text index on (nama_pemakai, keterangan) for search functionality';
                break;
            case 'tbl_kendaraan':
                $recommendations[] = 'Consider adding composite index on (status, jenis_kendaraan) for filtered listings';
                $recommendations[] = 'Consider adding index on (pemakai) for user-based queries';
                break;
            case 'tbl_user':
                $recommendations[] = 'Consider adding composite index on (admin, is_active) for role-based queries';
                break;
        }
        
        return $recommendations;
    }
    
    /**
     * Optimize table
     */
    public function optimizeTable($tableName) {
        $result = $this->executeQuery("OPTIMIZE TABLE $tableName", [], false);
        return mysqli_fetch_assoc($result);
    }
    
    /**
     * Analyze table
     */
    public function analyzeTable($tableName) {
        $result = $this->executeQuery("ANALYZE TABLE $tableName", [], false);
        return mysqli_fetch_assoc($result);
    }
    
    /**
     * Get explain plan for query
     */
    public function explainQuery($sql, $params = []) {
        $explainSql = "EXPLAIN " . $sql;
        $result = $this->executeQuery($explainSql, $params, false);
        
        $plan = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $plan[] = $row;
        }
        
        return $plan;
    }
    
    /**
     * Clear query log
     */
    public function clearQueryLog() {
        $this->queryLog = [];
    }
    
    /**
     * Set slow query threshold
     */
    public function setSlowQueryThreshold($seconds) {
        $this->slowQueryThreshold = $seconds;
    }
    
    /**
     * Get recent slow queries
     */
    public function getRecentSlowQueries($limit = 10) {
        $slowQueries = array_filter($this->queryLog, function($query) {
            return $query['execution_time'] > $this->slowQueryThreshold;
        });
        
        // Sort by execution time descending
        usort($slowQueries, function($a, $b) {
            return $b['execution_time'] <=> $a['execution_time'];
        });
        
        return array_slice($slowQueries, 0, $limit);
    }
    
    /**
     * Generate performance report
     */
    public function generatePerformanceReport() {
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'query_stats' => $this->getQueryStats(),
            'database_status' => $this->getDatabaseStatus(),
            'table_sizes' => $this->getTableSizes(),
            'slow_queries' => $this->getRecentSlowQueries(5)
        ];
        
        return $report;
    }
}

/**
 * Query Cache Class
 */
class QueryCache {
    private $cacheDir;
    private $defaultTtl = 3600; // 1 hour
    
    public function __construct($cacheDir = 'cache/queries/') {
        $this->cacheDir = $cacheDir;
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * Get cached query result
     */
    public function get($key) {
        $cacheFile = $this->getCacheFile($key);
        
        if (!file_exists($cacheFile)) {
            return null;
        }
        
        $data = unserialize(file_get_contents($cacheFile));
        
        if ($data['expires'] < time()) {
            unlink($cacheFile);
            return null;
        }
        
        return $data['result'];
    }
    
    /**
     * Cache query result
     */
    public function set($key, $result, $ttl = null) {
        $ttl = $ttl ?? $this->defaultTtl;
        $cacheFile = $this->getCacheFile($key);
        
        $data = [
            'result' => $result,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        file_put_contents($cacheFile, serialize($data));
    }
    
    /**
     * Generate cache key from SQL and parameters
     */
    public function generateKey($sql, $params = []) {
        return md5($sql . serialize($params));
    }
    
    /**
     * Get cache file path
     */
    private function getCacheFile($key) {
        return $this->cacheDir . $key . '.cache';
    }
    
    /**
     * Clear all cache
     */
    public function clear() {
        $files = glob($this->cacheDir . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
    
    /**
     * Clear expired cache
     */
    public function clearExpired() {
        $files = glob($this->cacheDir . '*.cache');
        $cleared = 0;
        
        foreach ($files as $file) {
            $data = unserialize(file_get_contents($file));
            if ($data['expires'] < time()) {
                unlink($file);
                $cleared++;
            }
        }
        
        return $cleared;
    }
}

// Global database monitor instance
$dbMonitor = null;
$queryCache = null;

if (!function_exists('get_db_monitor')) {
    function get_db_monitor() {
        global $dbMonitor, $config;
        if (!$dbMonitor && $config) {
            $dbMonitor = new DatabaseMonitor($config);
        }
        return $dbMonitor;
    }
}

if (!function_exists('get_query_cache')) {
    function get_query_cache() {
        global $queryCache;
        if (!$queryCache) {
            $queryCache = new QueryCache();
        }
        return $queryCache;
    }
}
?>
