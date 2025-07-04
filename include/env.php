<?php
/**
 * Environment Variables Loader
 * Simple .env file parser for PHP
 */

class EnvLoader {
    private static $loaded = false;
    private static $env = [];
    
    /**
     * Load environment variables from .env file
     */
    public static function load($path = null) {
        if (self::$loaded) {
            return;
        }
        
        if ($path === null) {
            $path = dirname(__DIR__) . '/.env';
        }
        
        if (!file_exists($path)) {
            // Try to copy from example if .env doesn't exist
            $examplePath = dirname(__DIR__) . '/.env.example';
            if (file_exists($examplePath)) {
                copy($examplePath, $path);
                error_log("Warning: .env file created from .env.example. Please update with your actual configuration.");
            } else {
                error_log("Warning: No .env file found and no .env.example to copy from.");
                return;
            }
        }
        
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse key=value pairs
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                    (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                    $value = substr($value, 1, -1);
                }
                
                // Set environment variable
                $_ENV[$key] = $value;
                putenv("$key=$value");
                self::$env[$key] = $value;
            }
        }
        
        self::$loaded = true;
    }
    
    /**
     * Get environment variable value
     */
    public static function get($key, $default = null) {
        self::load();
        
        // Check $_ENV first, then getenv(), then our internal array
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        if (isset(self::$env[$key])) {
            return self::$env[$key];
        }
        
        return $default;
    }
    
    /**
     * Check if environment variable exists
     */
    public static function has($key) {
        return self::get($key) !== null;
    }
    
    /**
     * Get boolean value from environment
     */
    public static function getBool($key, $default = false) {
        $value = self::get($key, $default);
        
        if (is_bool($value)) {
            return $value;
        }
        
        return in_array(strtolower($value), ['true', '1', 'yes', 'on']);
    }
    
    /**
     * Get integer value from environment
     */
    public static function getInt($key, $default = 0) {
        return (int) self::get($key, $default);
    }
}

// Helper functions for easier access
if (!function_exists('env')) {
    function env($key, $default = null) {
        return EnvLoader::get($key, $default);
    }
}

if (!function_exists('env_bool')) {
    function env_bool($key, $default = false) {
        return EnvLoader::getBool($key, $default);
    }
}

if (!function_exists('env_int')) {
    function env_int($key, $default = 0) {
        return EnvLoader::getInt($key, $default);
    }
}

// Auto-load environment variables
EnvLoader::load();
?>
