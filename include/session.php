<?php
/**
 * Secure Session Management Class
 * Provides enhanced session security and management
 */

class SessionManager {
    private static $initialized = false;
    private static $session_lifetime;
    private static $session_name;
    
    /**
     * Initialize secure session
     */
    public static function init() {
        if (self::$initialized) {
            return;
        }
        
        self::$session_lifetime = env_int('SESSION_LIFETIME', 3600);
        self::$session_name = env('SESSION_NAME', 'EARSIP_SESSION');
        
        // Configure session security settings
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_only_cookies', 1);
        ini_set('session.entropy_length', 32);
        ini_set('session.hash_function', 'sha256');
        
        // Set session name and cookie parameters
        session_name(self::$session_name);
        session_set_cookie_params([
            'lifetime' => self::$session_lifetime,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        
        // Start session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Initialize session security
        self::initSessionSecurity();
        
        self::$initialized = true;
    }
    
    /**
     * Initialize session security measures
     */
    private static function initSessionSecurity() {
        // Check if session is new
        if (!isset($_SESSION['initiated'])) {
            session_regenerate_id(true);
            $_SESSION['initiated'] = true;
            $_SESSION['created'] = time();
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $_SESSION['ip_address'] = self::getClientIP();
        }
        
        // Validate session
        self::validateSession();
        
        // Check session timeout
        self::checkTimeout();
        
        // Regenerate session ID periodically
        self::regenerateSessionId();
    }
    
    /**
     * Validate session integrity
     */
    private static function validateSession() {
        // Check user agent
        if (isset($_SESSION['user_agent'])) {
            $current_user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            if ($_SESSION['user_agent'] !== $current_user_agent) {
                self::destroy();
                return false;
            }
        }
        
        // Check IP address (optional, can be disabled for mobile users)
        if (env_bool('SESSION_CHECK_IP', false) && isset($_SESSION['ip_address'])) {
            $current_ip = self::getClientIP();
            if ($_SESSION['ip_address'] !== $current_ip) {
                self::destroy();
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check session timeout
     */
    private static function checkTimeout() {
        if (isset($_SESSION['login_time'])) {
            $inactive_time = time() - $_SESSION['login_time'];
            if ($inactive_time > self::$session_lifetime) {
                self::destroy();
                header("Location: index.php?timeout=1");
                exit();
            }
        }
        
        // Update last activity time
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Regenerate session ID periodically
     */
    private static function regenerateSessionId() {
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        }
        
        // Regenerate every 30 minutes
        if (time() - $_SESSION['last_regeneration'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
    
    /**
     * Start user session after login
     */
    public static function startUserSession($user_data) {
        self::init();
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Set user session data
        $_SESSION['admin'] = $user_data['admin'];
        $_SESSION['id_user'] = $user_data['id_user'];
        $_SESSION['nama'] = $user_data['nama'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['last_regeneration'] = time();
        
        // Set session fingerprint
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $_SESSION['ip_address'] = self::getClientIP();
    }
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        self::init();
        return isset($_SESSION['admin']) && isset($_SESSION['id_user']);
    }
    
    /**
     * Check if user is admin
     */
    public static function isAdmin() {
        return self::isLoggedIn() && ($_SESSION['admin'] == 1 || $_SESSION['admin'] == 2);
    }
    
    /**
     * Check if user is regular user
     */
    public static function isRegularUser() {
        return self::isLoggedIn() && $_SESSION['admin'] == 0;
    }
    
    /**
     * Get current user ID
     */
    public static function getUserId() {
        return self::isLoggedIn() ? $_SESSION['id_user'] : null;
    }
    
    /**
     * Get current user name
     */
    public static function getUserName() {
        return self::isLoggedIn() ? $_SESSION['nama'] : null;
    }
    
    /**
     * Destroy session
     */
    public static function destroy() {
        if (session_status() == PHP_SESSION_ACTIVE) {
            $_SESSION = array();
            
            // Delete session cookie
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            
            session_destroy();
        }
    }
    
    /**
     * Logout user
     */
    public static function logout() {
        self::destroy();
        header("Location: index.php");
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
     * Set flash message
     */
    public static function setFlash($type, $message) {
        self::init();
        $_SESSION['flash'][$type] = $message;
    }
    
    /**
     * Get and clear flash message
     */
    public static function getFlash($type) {
        self::init();
        if (isset($_SESSION['flash'][$type])) {
            $message = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $message;
        }
        return null;
    }
    
    /**
     * Check if flash message exists
     */
    public static function hasFlash($type) {
        self::init();
        return isset($_SESSION['flash'][$type]);
    }
}

// Helper functions
if (!function_exists('session_user_id')) {
    function session_user_id() {
        return SessionManager::getUserId();
    }
}

if (!function_exists('session_user_name')) {
    function session_user_name() {
        return SessionManager::getUserName();
    }
}

if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return SessionManager::isLoggedIn();
    }
}

if (!function_exists('is_admin')) {
    function is_admin() {
        return SessionManager::isAdmin();
    }
}

if (!function_exists('flash')) {
    function flash($type, $message = null) {
        if ($message === null) {
            return SessionManager::getFlash($type);
        }
        SessionManager::setFlash($type, $message);
    }
}

// Initialize session manager
SessionManager::init();
?>
