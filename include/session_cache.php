<?php
/**
 * Session Cache Manager
 * Enhanced session management with caching capabilities
 */

class SessionCache {
    private $cache;
    private $sessionPrefix = 'session:';
    private $userPrefix = 'user:';
    private $defaultTtl = 3600; // 1 hour
    
    public function __construct($cache = null) {
        $this->cache = $cache ?: cache();
    }
    
    /**
     * Store session data in cache
     */
    public function setSessionData($sessionId, $data, $ttl = null) {
        $key = $this->sessionPrefix . $sessionId;
        $ttl = $ttl ?: $this->defaultTtl;
        
        return $this->cache->set($key, $data, $ttl, 'sessions');
    }
    
    /**
     * Get session data from cache
     */
    public function getSessionData($sessionId) {
        $key = $this->sessionPrefix . $sessionId;
        return $this->cache->get($key, 'sessions');
    }
    
    /**
     * Delete session data from cache
     */
    public function deleteSessionData($sessionId) {
        $key = $this->sessionPrefix . $sessionId;
        return $this->cache->delete($key, 'sessions');
    }
    
    /**
     * Store user data in cache
     */
    public function setUserData($userId, $data, $ttl = null) {
        $key = $this->userPrefix . $userId;
        $ttl = $ttl ?: $this->defaultTtl;
        
        return $this->cache->set($key, $data, $ttl, 'sessions');
    }
    
    /**
     * Get user data from cache
     */
    public function getUserData($userId) {
        $key = $this->userPrefix . $userId;
        return $this->cache->get($key, 'sessions');
    }
    
    /**
     * Delete user data from cache
     */
    public function deleteUserData($userId) {
        $key = $this->userPrefix . $userId;
        return $this->cache->delete($key, 'sessions');
    }
    
    /**
     * Cache user permissions
     */
    public function cacheUserPermissions($userId, $permissions, $ttl = 1800) {
        $key = 'permissions:' . $userId;
        return $this->cache->set($key, $permissions, $ttl, 'sessions');
    }
    
    /**
     * Get cached user permissions
     */
    public function getUserPermissions($userId) {
        $key = 'permissions:' . $userId;
        return $this->cache->get($key, 'sessions');
    }
    
    /**
     * Cache user preferences
     */
    public function cacheUserPreferences($userId, $preferences, $ttl = 86400) {
        $key = 'preferences:' . $userId;
        return $this->cache->set($key, $preferences, $ttl, 'sessions');
    }
    
    /**
     * Get cached user preferences
     */
    public function getUserPreferences($userId) {
        $key = 'preferences:' . $userId;
        return $this->cache->get($key, 'sessions');
    }
    
    /**
     * Track active sessions
     */
    public function trackActiveSession($userId, $sessionId, $data = []) {
        $key = 'active_sessions:' . $userId;
        $sessions = $this->cache->get($key, 'sessions') ?: [];
        
        $sessions[$sessionId] = array_merge($data, [
            'last_activity' => time(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
        return $this->cache->set($key, $sessions, $this->defaultTtl, 'sessions');
    }
    
    /**
     * Get active sessions for user
     */
    public function getActiveSessions($userId) {
        $key = 'active_sessions:' . $userId;
        return $this->cache->get($key, 'sessions') ?: [];
    }
    
    /**
     * Remove session from active sessions
     */
    public function removeActiveSession($userId, $sessionId) {
        $key = 'active_sessions:' . $userId;
        $sessions = $this->cache->get($key, 'sessions') ?: [];
        
        if (isset($sessions[$sessionId])) {
            unset($sessions[$sessionId]);
            return $this->cache->set($key, $sessions, $this->defaultTtl, 'sessions');
        }
        
        return false;
    }
    
    /**
     * Cache login attempts for rate limiting
     */
    public function trackLoginAttempt($identifier, $success = false) {
        $key = 'login_attempts:' . md5($identifier);
        $attempts = $this->cache->get($key, 'sessions') ?: [];
        
        $attempts[] = [
            'timestamp' => time(),
            'success' => $success,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        // Keep only last 10 attempts
        $attempts = array_slice($attempts, -10);
        
        return $this->cache->set($key, $attempts, 3600, 'sessions'); // 1 hour
    }
    
    /**
     * Get login attempts
     */
    public function getLoginAttempts($identifier) {
        $key = 'login_attempts:' . md5($identifier);
        return $this->cache->get($key, 'sessions') ?: [];
    }
    
    /**
     * Check if user is rate limited
     */
    public function isRateLimited($identifier, $maxAttempts = 5, $timeWindow = 900) {
        $attempts = $this->getLoginAttempts($identifier);
        $recentAttempts = array_filter($attempts, function($attempt) use ($timeWindow) {
            return (time() - $attempt['timestamp']) < $timeWindow && !$attempt['success'];
        });
        
        return count($recentAttempts) >= $maxAttempts;
    }
    
    /**
     * Cache CSRF tokens
     */
    public function setCsrfToken($formName, $token, $ttl = 3600) {
        $sessionId = session_id();
        $key = 'csrf:' . $sessionId . ':' . $formName;
        return $this->cache->set($key, $token, $ttl, 'sessions');
    }
    
    /**
     * Get CSRF token
     */
    public function getCsrfToken($formName) {
        $sessionId = session_id();
        $key = 'csrf:' . $sessionId . ':' . $formName;
        return $this->cache->get($key, 'sessions');
    }
    
    /**
     * Validate CSRF token
     */
    public function validateCsrfToken($formName, $token) {
        $cachedToken = $this->getCsrfToken($formName);
        return $cachedToken && hash_equals($cachedToken, $token);
    }
    
    /**
     * Clear user-related cache
     */
    public function clearUserCache($userId) {
        $this->deleteUserData($userId);
        $this->cache->delete('permissions:' . $userId, 'sessions');
        $this->cache->delete('preferences:' . $userId, 'sessions');
        $this->cache->delete('active_sessions:' . $userId, 'sessions');
    }
    
    /**
     * Clear session cache
     */
    public function clearSessionCache($sessionId) {
        $this->deleteSessionData($sessionId);
        
        // Clear CSRF tokens for this session
        $pattern = 'csrf:' . $sessionId . ':*';
        // Note: This would need implementation in cache manager for pattern deletion
    }
    
    /**
     * Get session statistics
     */
    public function getSessionStats() {
        $stats = $this->cache->getStats();
        
        return [
            'active_sessions' => $stats['categories']['sessions']['files'] ?? 0,
            'cache_size' => $stats['categories']['sessions']['size'] ?? 0,
            'cache_hits' => $stats['categories']['sessions']['hits'] ?? 0
        ];
    }
    
    /**
     * Cleanup expired sessions
     */
    public function cleanupExpiredSessions() {
        return $this->cache->cleanup();
    }
}

/**
 * Enhanced Session Manager with caching
 */
class EnhancedSessionManager {
    private static $sessionCache;
    private static $sessionStarted = false;
    private static $sessionTimeout = 3600; // 1 hour
    private static $regenerateInterval = 300; // 5 minutes
    
    /**
     * Initialize session with caching
     */
    public static function init() {
        if (self::$sessionStarted) {
            return;
        }
        
        self::$sessionCache = new SessionCache();
        
        // Configure session settings
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        session_start();
        self::$sessionStarted = true;
        
        // Validate and regenerate session
        self::validateSession();
        self::regenerateIfNeeded();
    }
    
    /**
     * Start user session with caching
     */
    public static function startUserSession($userData) {
        self::init();
        
        $sessionId = session_id();
        $userId = $userData['id_user'];
        
        // Store session data
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $userData['username'];
        $_SESSION['nama'] = $userData['nama'];
        $_SESSION['admin'] = $userData['admin'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['fingerprint'] = self::generateFingerprint();
        
        // Cache user data
        self::$sessionCache->setUserData($userId, $userData);
        
        // Cache session data
        self::$sessionCache->setSessionData($sessionId, $_SESSION);
        
        // Track active session
        self::$sessionCache->trackActiveSession($userId, $sessionId, [
            'login_time' => time(),
            'username' => $userData['username']
        ]);
        
        // Cache user permissions
        $permissions = self::getUserPermissions($userData);
        self::$sessionCache->cacheUserPermissions($userId, $permissions);
        
        session_regenerate_id(true);
    }
    
    /**
     * Get user permissions
     */
    private static function getUserPermissions($userData) {
        $permissions = ['read' => true];
        
        if ($userData['admin'] >= 1) {
            $permissions['write'] = true;
            $permissions['delete'] = true;
        }
        
        if ($userData['admin'] >= 2) {
            $permissions['admin'] = true;
            $permissions['user_management'] = true;
        }
        
        return $permissions;
    }
    
    /**
     * Check if user has permission
     */
    public static function hasPermission($permission) {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        $userId = $_SESSION['user_id'];
        $permissions = self::$sessionCache->getUserPermissions($userId);
        
        if (!$permissions) {
            // Fallback to session data
            $permissions = self::getUserPermissions($_SESSION);
            self::$sessionCache->cacheUserPermissions($userId, $permissions);
        }
        
        return isset($permissions[$permission]) && $permissions[$permission];
    }
    
    /**
     * Logout user
     */
    public static function logout() {
        if (!self::$sessionStarted) {
            return;
        }
        
        $sessionId = session_id();
        $userId = $_SESSION['user_id'] ?? null;
        
        if ($userId) {
            // Remove from active sessions
            self::$sessionCache->removeActiveSession($userId, $sessionId);
            
            // Clear user cache
            self::$sessionCache->clearUserCache($userId);
        }
        
        // Clear session cache
        self::$sessionCache->clearSessionCache($sessionId);
        
        // Destroy session
        session_unset();
        session_destroy();
        
        // Clear session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        self::$sessionStarted = false;
    }
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        self::init();
        return isset($_SESSION['user_id']) && self::validateSession();
    }
    
    /**
     * Validate session
     */
    private static function validateSession() {
        if (!isset($_SESSION['fingerprint'])) {
            return false;
        }
        
        // Check fingerprint
        if ($_SESSION['fingerprint'] !== self::generateFingerprint()) {
            self::logout();
            return false;
        }
        
        // Check timeout
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity']) > self::$sessionTimeout) {
            self::logout();
            return false;
        }
        
        // Update last activity
        $_SESSION['last_activity'] = time();
        
        // Update cached session data
        if (self::$sessionCache) {
            self::$sessionCache->setSessionData(session_id(), $_SESSION);
        }
        
        return true;
    }
    
    /**
     * Regenerate session ID if needed
     */
    private static function regenerateIfNeeded() {
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
            return;
        }
        
        if ((time() - $_SESSION['last_regeneration']) > self::$regenerateInterval) {
            $oldSessionId = session_id();
            session_regenerate_id(true);
            $newSessionId = session_id();
            
            $_SESSION['last_regeneration'] = time();
            
            // Update cached data with new session ID
            if (self::$sessionCache && isset($_SESSION['user_id'])) {
                self::$sessionCache->deleteSessionData($oldSessionId);
                self::$sessionCache->setSessionData($newSessionId, $_SESSION);
                
                // Update active sessions tracking
                $userId = $_SESSION['user_id'];
                self::$sessionCache->removeActiveSession($userId, $oldSessionId);
                self::$sessionCache->trackActiveSession($userId, $newSessionId);
            }
        }
    }
    
    /**
     * Generate session fingerprint
     */
    private static function generateFingerprint() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        $acceptEncoding = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';
        
        return hash('sha256', $userAgent . $acceptLanguage . $acceptEncoding);
    }
    
    /**
     * Get session cache instance
     */
    public static function getSessionCache() {
        if (!self::$sessionCache) {
            self::$sessionCache = new SessionCache();
        }
        return self::$sessionCache;
    }
}

// Global session cache instance
$sessionCache = null;

if (!function_exists('session_cache')) {
    function session_cache() {
        global $sessionCache;
        if (!$sessionCache) {
            $sessionCache = new SessionCache();
        }
        return $sessionCache;
    }
}
?>
