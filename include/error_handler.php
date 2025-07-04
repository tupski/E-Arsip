<?php
/**
 * Error Handler and Logger Class
 * Provides comprehensive error handling and logging functionality
 */

class ErrorHandler {
    private static $log_file;
    private static $error_log_file;
    private static $debug_mode;
    private static $initialized = false;
    
    /**
     * Initialize error handler
     */
    public static function init() {
        if (self::$initialized) {
            return;
        }
        
        self::$debug_mode = env_bool('APP_DEBUG', false);
        self::$log_file = 'logs/app.log';
        self::$error_log_file = 'logs/error.log';
        
        // Create logs directory if it doesn't exist
        $log_dir = dirname(self::$log_file);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        // Set custom error and exception handlers
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
        
        // Configure error reporting
        if (self::$debug_mode) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
        } else {
            error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
            ini_set('display_errors', 0);
            ini_set('log_errors', 1);
            ini_set('error_log', self::$error_log_file);
        }
        
        self::$initialized = true;
    }
    
    /**
     * Handle PHP errors
     */
    public static function handleError($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $error_type = self::getErrorType($severity);
        $error_message = "[$error_type] $message in $file on line $line";
        
        self::logError($error_message, [
            'type' => $error_type,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'severity' => $severity
        ]);
        
        if (self::$debug_mode) {
            echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px; border-radius: 4px;'>";
            echo "<strong>Error:</strong> $error_message";
            echo "</div>";
        }
        
        return true;
    }
    
    /**
     * Handle uncaught exceptions
     */
    public static function handleException($exception) {
        $error_message = "Uncaught Exception: " . $exception->getMessage() . 
                        " in " . $exception->getFile() . 
                        " on line " . $exception->getLine();
        
        self::logError($error_message, [
            'type' => 'Exception',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
        
        if (self::$debug_mode) {
            echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px; border-radius: 4px;'>";
            echo "<strong>Exception:</strong> " . htmlspecialchars($exception->getMessage()) . "<br>";
            echo "<strong>File:</strong> " . htmlspecialchars($exception->getFile()) . "<br>";
            echo "<strong>Line:</strong> " . $exception->getLine() . "<br>";
            echo "<strong>Trace:</strong><pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
            echo "</div>";
        } else {
            self::showUserFriendlyError();
        }
    }
    
    /**
     * Handle fatal errors on shutdown
     */
    public static function handleShutdown() {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $error_message = "Fatal Error: " . $error['message'] . 
                           " in " . $error['file'] . 
                           " on line " . $error['line'];
            
            self::logError($error_message, $error);
            
            if (!self::$debug_mode) {
                self::showUserFriendlyError();
            }
        }
    }
    
    /**
     * Log application events
     */
    public static function log($level, $message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $user_id = session_user_id() ?? 'guest';
        $ip = self::getClientIP();
        
        $log_entry = [
            'timestamp' => $timestamp,
            'level' => strtoupper($level),
            'message' => $message,
            'user_id' => $user_id,
            'ip' => $ip,
            'context' => $context
        ];
        
        $log_line = json_encode($log_entry) . "\n";
        
        file_put_contents(self::$log_file, $log_line, FILE_APPEND | LOCK_EX);
        
        // Also log to database if available
        self::logToDatabase($level, $message, $context);
    }
    
    /**
     * Log error specifically
     */
    private static function logError($message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $user_id = session_user_id() ?? 'guest';
        $ip = self::getClientIP();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        
        $error_entry = [
            'timestamp' => $timestamp,
            'message' => $message,
            'user_id' => $user_id,
            'ip' => $ip,
            'user_agent' => $user_agent,
            'request_uri' => $request_uri,
            'context' => $context
        ];
        
        $error_line = json_encode($error_entry) . "\n";
        
        file_put_contents(self::$error_log_file, $error_line, FILE_APPEND | LOCK_EX);
        
        // Also log to application log
        self::log('error', $message, $context);
    }
    
    /**
     * Log to database
     */
    private static function logToDatabase($level, $message, $context) {
        try {
            global $config;
            if (!$config) return;
            
            $user_id = session_user_id();
            $ip = self::getClientIP();
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $request_uri = $_SERVER['REQUEST_URI'] ?? '';
            
            $stmt = mysqli_prepare($config, 
                "INSERT INTO tbl_audit_log (table_name, record_id, action, new_values, user_id, ip_address, user_agent) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            
            if ($stmt) {
                $table_name = 'system_log';
                $record_id = 0;
                $action = strtoupper($level);
                $log_data = json_encode([
                    'message' => $message,
                    'context' => $context,
                    'request_uri' => $request_uri
                ]);
                
                mysqli_stmt_bind_param($stmt, "sisssss", 
                    $table_name, $record_id, $action, $log_data, $user_id, $ip, $user_agent
                );
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        } catch (Exception $e) {
            // Don't throw exceptions in error handler
            error_log("Failed to log to database: " . $e->getMessage());
        }
    }
    
    /**
     * Get error type string
     */
    private static function getErrorType($type) {
        switch($type) {
            case E_ERROR: return 'Fatal Error';
            case E_WARNING: return 'Warning';
            case E_PARSE: return 'Parse Error';
            case E_NOTICE: return 'Notice';
            case E_CORE_ERROR: return 'Core Error';
            case E_CORE_WARNING: return 'Core Warning';
            case E_COMPILE_ERROR: return 'Compile Error';
            case E_COMPILE_WARNING: return 'Compile Warning';
            case E_USER_ERROR: return 'User Error';
            case E_USER_WARNING: return 'User Warning';
            case E_USER_NOTICE: return 'User Notice';
            case E_STRICT: return 'Strict Standards';
            case E_RECOVERABLE_ERROR: return 'Recoverable Error';
            case E_DEPRECATED: return 'Deprecated';
            case E_USER_DEPRECATED: return 'User Deprecated';
            default: return 'Unknown Error';
        }
    }
    
    /**
     * Show user-friendly error page
     */
    private static function showUserFriendlyError() {
        if (headers_sent()) {
            return;
        }
        
        http_response_code(500);
        include 'error_pages/500.php';
        exit();
    }
    
    /**
     * Get client IP address
     */
    private static function getClientIP() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, 
                        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Log user activity
     */
    public static function logActivity($action, $description, $data = []) {
        self::log('info', "User Activity: $action - $description", [
            'action' => $action,
            'data' => $data
        ]);
    }
    
    /**
     * Log security event
     */
    public static function logSecurity($event, $description, $data = []) {
        self::log('warning', "Security Event: $event - $description", [
            'event' => $event,
            'data' => $data
        ]);
    }
}

// Helper functions
if (!function_exists('app_log')) {
    function app_log($level, $message, $context = []) {
        ErrorHandler::log($level, $message, $context);
    }
}

if (!function_exists('log_activity')) {
    function log_activity($action, $description, $data = []) {
        ErrorHandler::logActivity($action, $description, $data);
    }
}

if (!function_exists('log_security')) {
    function log_security($event, $description, $data = []) {
        ErrorHandler::logSecurity($event, $description, $data);
    }
}

// Initialize error handler
ErrorHandler::init();
?>
