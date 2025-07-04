<?php
/**
 * CSRF Protection Class
 * Provides Cross-Site Request Forgery protection for forms
 */

class CSRFProtection {
    private static $token_name;
    private static $token_lifetime = 3600; // 1 hour
    
    /**
     * Initialize CSRF protection
     */
    public static function init() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        self::$token_name = env('CSRF_TOKEN_NAME', 'csrf_token');
        self::$token_lifetime = env_int('SESSION_LIFETIME', 3600);
        
        // Clean up expired tokens
        self::cleanupExpiredTokens();
    }
    
    /**
     * Generate a new CSRF token
     */
    public static function generateToken($form_name = 'default') {
        self::init();
        
        $token = bin2hex(random_bytes(32));
        $timestamp = time();
        
        if (!isset($_SESSION['csrf_tokens'])) {
            $_SESSION['csrf_tokens'] = [];
        }
        
        $_SESSION['csrf_tokens'][$form_name] = [
            'token' => $token,
            'timestamp' => $timestamp
        ];
        
        return $token;
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateToken($token, $form_name = 'default') {
        self::init();
        
        if (!isset($_SESSION['csrf_tokens'][$form_name])) {
            return false;
        }
        
        $stored_data = $_SESSION['csrf_tokens'][$form_name];
        
        // Check if token has expired
        if ((time() - $stored_data['timestamp']) > self::$token_lifetime) {
            unset($_SESSION['csrf_tokens'][$form_name]);
            return false;
        }
        
        // Validate token using hash_equals to prevent timing attacks
        $is_valid = hash_equals($stored_data['token'], $token);
        
        // Remove token after validation (one-time use)
        if ($is_valid) {
            unset($_SESSION['csrf_tokens'][$form_name]);
        }
        
        return $is_valid;
    }
    
    /**
     * Get CSRF token for a form
     */
    public static function getToken($form_name = 'default') {
        self::init();
        
        if (!isset($_SESSION['csrf_tokens'][$form_name])) {
            return self::generateToken($form_name);
        }
        
        $stored_data = $_SESSION['csrf_tokens'][$form_name];
        
        // Check if token has expired
        if ((time() - $stored_data['timestamp']) > self::$token_lifetime) {
            return self::generateToken($form_name);
        }
        
        return $stored_data['token'];
    }
    
    /**
     * Generate HTML input field for CSRF token
     */
    public static function getTokenField($form_name = 'default') {
        $token = self::getToken($form_name);
        $token_name = htmlspecialchars(self::$token_name, ENT_QUOTES, 'UTF-8');
        $token_value = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
        
        return '<input type="hidden" name="' . $token_name . '" value="' . $token_value . '">';
    }
    
    /**
     * Validate CSRF token from POST data
     */
    public static function validateFromPost($form_name = 'default') {
        if (!isset($_POST[self::$token_name])) {
            return false;
        }
        
        return self::validateToken($_POST[self::$token_name], $form_name);
    }
    
    /**
     * Clean up expired tokens
     */
    private static function cleanupExpiredTokens() {
        if (!isset($_SESSION['csrf_tokens'])) {
            return;
        }
        
        $current_time = time();
        foreach ($_SESSION['csrf_tokens'] as $form_name => $data) {
            if (($current_time - $data['timestamp']) > self::$token_lifetime) {
                unset($_SESSION['csrf_tokens'][$form_name]);
            }
        }
    }
    
    /**
     * Handle CSRF validation failure
     */
    public static function handleValidationFailure($redirect_url = null) {
        $_SESSION['err'] = 'Token keamanan tidak valid. Silakan coba lagi.';
        
        if ($redirect_url) {
            header("Location: $redirect_url");
        } else {
            header("Location: " . $_SERVER['HTTP_REFERER'] ?? 'index.php');
        }
        exit();
    }
}

// Helper functions for easier access
if (!function_exists('csrf_token')) {
    function csrf_token($form_name = 'default') {
        return CSRFProtection::getToken($form_name);
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field($form_name = 'default') {
        return CSRFProtection::getTokenField($form_name);
    }
}

if (!function_exists('csrf_validate')) {
    function csrf_validate($form_name = 'default') {
        return CSRFProtection::validateFromPost($form_name);
    }
}

// Initialize CSRF protection
CSRFProtection::init();
?>
