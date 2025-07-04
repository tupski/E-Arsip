<?php
/**
 * Health Check Endpoint
 * Provides system health status and monitoring information
 */

// Prevent direct access in non-production environments
if (!defined('HEALTH_CHECK_ENABLED')) {
    define('HEALTH_CHECK_ENABLED', true);
}

if (!HEALTH_CHECK_ENABLED) {
    http_response_code(404);
    exit('Not Found');
}

// Set content type
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Include configuration
require_once 'include/config.php';

/**
 * Health Check Manager
 */
class HealthChecker {
    private $checks = [];
    private $status = 'healthy';
    private $details = [];
    
    public function __construct() {
        $this->registerChecks();
    }
    
    /**
     * Register all health checks
     */
    private function registerChecks() {
        $this->checks = [
            'database' => [$this, 'checkDatabase'],
            'cache' => [$this, 'checkCache'],
            'disk_space' => [$this, 'checkDiskSpace'],
            'memory' => [$this, 'checkMemory'],
            'php_version' => [$this, 'checkPhpVersion'],
            'extensions' => [$this, 'checkExtensions'],
            'permissions' => [$this, 'checkPermissions'],
            'logs' => [$this, 'checkLogs']
        ];
    }
    
    /**
     * Run all health checks
     */
    public function runChecks() {
        foreach ($this->checks as $name => $callback) {
            try {
                $result = call_user_func($callback);
                $this->details[$name] = $result;
                
                if ($result['status'] !== 'ok') {
                    $this->status = 'unhealthy';
                }
            } catch (Exception $e) {
                $this->details[$name] = [
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
                $this->status = 'unhealthy';
            }
        }
        
        return [
            'status' => $this->status,
            'timestamp' => date('c'),
            'version' => $this->getVersion(),
            'checks' => $this->details,
            'summary' => $this->getSummary()
        ];
    }
    
    /**
     * Check database connectivity
     */
    private function checkDatabase() {
        global $config;
        
        if (!$config) {
            return [
                'status' => 'error',
                'message' => 'Database connection not available'
            ];
        }
        
        // Test basic connectivity
        $result = mysqli_query($config, "SELECT 1 as test");
        if (!$result) {
            return [
                'status' => 'error',
                'message' => 'Database query failed: ' . mysqli_error($config)
            ];
        }
        
        // Check table existence
        $tables = ['tbl_user', 'tbl_berita_acara', 'tbl_kendaraan', 'tbl_instansi'];
        $missingTables = [];
        
        foreach ($tables as $table) {
            $result = mysqli_query($config, "SHOW TABLES LIKE '$table'");
            if (mysqli_num_rows($result) === 0) {
                $missingTables[] = $table;
            }
        }
        
        if (!empty($missingTables)) {
            return [
                'status' => 'warning',
                'message' => 'Missing tables: ' . implode(', ', $missingTables)
            ];
        }
        
        // Get database stats
        $stats = $this->getDatabaseStats();
        
        return [
            'status' => 'ok',
            'message' => 'Database connected',
            'details' => $stats
        ];
    }
    
    /**
     * Check cache system
     */
    private function checkCache() {
        try {
            $cache = cache();
            
            // Test cache write/read
            $testKey = 'health_check_' . time();
            $testValue = 'test_data';
            
            $cache->set($testKey, $testValue, 60);
            $retrieved = $cache->get($testKey);
            $cache->delete($testKey);
            
            if ($retrieved !== $testValue) {
                return [
                    'status' => 'error',
                    'message' => 'Cache read/write test failed'
                ];
            }
            
            $stats = $cache->getStats();
            
            return [
                'status' => 'ok',
                'message' => 'Cache operational',
                'details' => [
                    'total_files' => $stats['total_files'],
                    'total_size' => $this->formatBytes($stats['total_size']),
                    'expired_files' => $stats['expired_files']
                ]
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Cache error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Check disk space
     */
    private function checkDiskSpace() {
        $path = __DIR__;
        $freeBytes = disk_free_space($path);
        $totalBytes = disk_total_space($path);
        
        if ($freeBytes === false || $totalBytes === false) {
            return [
                'status' => 'error',
                'message' => 'Unable to check disk space'
            ];
        }
        
        $usedBytes = $totalBytes - $freeBytes;
        $usedPercent = ($usedBytes / $totalBytes) * 100;
        
        $status = 'ok';
        $message = 'Sufficient disk space';
        
        if ($usedPercent > 90) {
            $status = 'error';
            $message = 'Disk space critically low';
        } elseif ($usedPercent > 80) {
            $status = 'warning';
            $message = 'Disk space running low';
        }
        
        return [
            'status' => $status,
            'message' => $message,
            'details' => [
                'total' => $this->formatBytes($totalBytes),
                'used' => $this->formatBytes($usedBytes),
                'free' => $this->formatBytes($freeBytes),
                'used_percent' => round($usedPercent, 2)
            ]
        ];
    }
    
    /**
     * Check memory usage
     */
    private function checkMemory() {
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryLimit = $this->parseBytes(ini_get('memory_limit'));
        
        $usagePercent = ($memoryUsage / $memoryLimit) * 100;
        
        $status = 'ok';
        $message = 'Memory usage normal';
        
        if ($usagePercent > 90) {
            $status = 'warning';
            $message = 'High memory usage';
        }
        
        return [
            'status' => $status,
            'message' => $message,
            'details' => [
                'current' => $this->formatBytes($memoryUsage),
                'peak' => $this->formatBytes($memoryPeak),
                'limit' => $this->formatBytes($memoryLimit),
                'usage_percent' => round($usagePercent, 2)
            ]
        ];
    }
    
    /**
     * Check PHP version
     */
    private function checkPhpVersion() {
        $version = PHP_VERSION;
        $minVersion = '7.4.0';
        
        if (version_compare($version, $minVersion, '<')) {
            return [
                'status' => 'error',
                'message' => "PHP version $version is below minimum required $minVersion"
            ];
        }
        
        return [
            'status' => 'ok',
            'message' => "PHP version $version",
            'details' => [
                'version' => $version,
                'sapi' => php_sapi_name(),
                'os' => PHP_OS
            ]
        ];
    }
    
    /**
     * Check required PHP extensions
     */
    private function checkExtensions() {
        $required = ['mysqli', 'mbstring', 'json', 'session', 'filter'];
        $missing = [];
        
        foreach ($required as $ext) {
            if (!extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }
        
        if (!empty($missing)) {
            return [
                'status' => 'error',
                'message' => 'Missing extensions: ' . implode(', ', $missing)
            ];
        }
        
        return [
            'status' => 'ok',
            'message' => 'All required extensions loaded',
            'details' => [
                'loaded' => $required
            ]
        ];
    }
    
    /**
     * Check file permissions
     */
    private function checkPermissions() {
        $paths = [
            'cache' => 'cache/',
            'logs' => 'logs/',
            'uploads' => 'uploads/'
        ];
        
        $issues = [];
        
        foreach ($paths as $name => $path) {
            if (!is_dir($path)) {
                $issues[] = "$name directory missing";
                continue;
            }
            
            if (!is_writable($path)) {
                $issues[] = "$name directory not writable";
            }
        }
        
        if (!empty($issues)) {
            return [
                'status' => 'error',
                'message' => 'Permission issues: ' . implode(', ', $issues)
            ];
        }
        
        return [
            'status' => 'ok',
            'message' => 'File permissions correct'
        ];
    }
    
    /**
     * Check log files
     */
    private function checkLogs() {
        $logFile = 'logs/app.log';
        
        if (!file_exists($logFile)) {
            return [
                'status' => 'warning',
                'message' => 'Log file does not exist'
            ];
        }
        
        $size = filesize($logFile);
        $maxSize = 50 * 1024 * 1024; // 50MB
        
        if ($size > $maxSize) {
            return [
                'status' => 'warning',
                'message' => 'Log file is large, consider rotation',
                'details' => [
                    'size' => $this->formatBytes($size)
                ]
            ];
        }
        
        return [
            'status' => 'ok',
            'message' => 'Log file healthy',
            'details' => [
                'size' => $this->formatBytes($size)
            ]
        ];
    }
    
    /**
     * Get database statistics
     */
    private function getDatabaseStats() {
        global $config;
        
        $stats = [];
        
        // Get table sizes
        $result = mysqli_query($config, "
            SELECT 
                table_name,
                table_rows,
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
            FROM information_schema.tables 
            WHERE table_schema = DATABASE()
            ORDER BY (data_length + index_length) DESC
        ");
        
        $tables = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $tables[] = $row;
        }
        
        $stats['tables'] = $tables;
        
        // Get connection count
        $result = mysqli_query($config, "SHOW STATUS LIKE 'Threads_connected'");
        $row = mysqli_fetch_assoc($result);
        $stats['connections'] = $row['Value'];
        
        return $stats;
    }
    
    /**
     * Get application version
     */
    private function getVersion() {
        $versionFile = 'VERSION';
        if (file_exists($versionFile)) {
            return trim(file_get_contents($versionFile));
        }
        return '1.0.0';
    }
    
    /**
     * Get summary statistics
     */
    private function getSummary() {
        $total = count($this->details);
        $healthy = 0;
        $warnings = 0;
        $errors = 0;
        
        foreach ($this->details as $check) {
            switch ($check['status']) {
                case 'ok':
                    $healthy++;
                    break;
                case 'warning':
                    $warnings++;
                    break;
                case 'error':
                    $errors++;
                    break;
            }
        }
        
        return [
            'total_checks' => $total,
            'healthy' => $healthy,
            'warnings' => $warnings,
            'errors' => $errors
        ];
    }
    
    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Parse bytes from string (e.g., "128M" -> 134217728)
     */
    private function parseBytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        $val = (int) $val;
        
        switch($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        
        return $val;
    }
}

// Run health checks
try {
    $healthChecker = new HealthChecker();
    $result = $healthChecker->runChecks();
    
    // Set appropriate HTTP status code
    if ($result['status'] === 'unhealthy') {
        http_response_code(503); // Service Unavailable
    } else {
        http_response_code(200); // OK
    }
    
    echo json_encode($result, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Health check failed: ' . $e->getMessage(),
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
}
?>
