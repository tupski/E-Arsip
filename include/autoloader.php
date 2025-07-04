<?php
/**
 * Autoloader for Models and Controllers
 * Automatically loads classes when they are instantiated
 */

class Autoloader {
    private static $directories = [
        'models/',
        'controllers/',
        'include/'
    ];
    
    /**
     * Register the autoloader
     */
    public static function register() {
        spl_autoload_register([self::class, 'load']);
    }
    
    /**
     * Load class file
     */
    public static function load($className) {
        foreach (self::$directories as $directory) {
            $file = $directory . $className . '.php';
            
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Add directory to autoload path
     */
    public static function addDirectory($directory) {
        if (!in_array($directory, self::$directories)) {
            self::$directories[] = rtrim($directory, '/') . '/';
        }
    }
    
    /**
     * Get all registered directories
     */
    public static function getDirectories() {
        return self::$directories;
    }
}

// Register the autoloader
Autoloader::register();
?>
