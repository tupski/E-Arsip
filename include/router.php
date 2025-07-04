<?php
/**
 * Simple Router Class
 * Handles routing for the application
 */

class Router {
    private static $routes = [];
    private static $db;
    
    /**
     * Initialize router with database connection
     */
    public static function init($database) {
        self::$db = $database;
    }
    
    /**
     * Add GET route
     */
    public static function get($path, $controller, $method) {
        self::addRoute('GET', $path, $controller, $method);
    }
    
    /**
     * Add POST route
     */
    public static function post($path, $controller, $method) {
        self::addRoute('POST', $path, $controller, $method);
    }
    
    /**
     * Add route for any HTTP method
     */
    public static function any($path, $controller, $method) {
        self::addRoute('ANY', $path, $controller, $method);
    }
    
    /**
     * Add route to routes array
     */
    private static function addRoute($httpMethod, $path, $controller, $method) {
        self::$routes[] = [
            'method' => $httpMethod,
            'path' => $path,
            'controller' => $controller,
            'action' => $method
        ];
    }
    
    /**
     * Dispatch request to appropriate controller
     */
    public static function dispatch($requestUri = null, $requestMethod = null) {
        if ($requestUri === null) {
            $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        }
        
        if ($requestMethod === null) {
            $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        }
        
        // Remove query string from URI
        $path = parse_url($requestUri, PHP_URL_PATH);
        
        // Find matching route
        foreach (self::$routes as $route) {
            if (self::matchRoute($route, $path, $requestMethod)) {
                return self::executeRoute($route, $path);
            }
        }
        
        // No route found
        return self::handleNotFound();
    }
    
    /**
     * Check if route matches current request
     */
    private static function matchRoute($route, $path, $method) {
        // Check HTTP method
        if ($route['method'] !== 'ANY' && $route['method'] !== $method) {
            return false;
        }
        
        // Convert route path to regex
        $pattern = self::convertToRegex($route['path']);
        
        return preg_match($pattern, $path);
    }
    
    /**
     * Convert route path to regex pattern
     */
    private static function convertToRegex($path) {
        // Escape special regex characters
        $pattern = preg_quote($path, '/');
        
        // Replace parameter placeholders with regex
        $pattern = preg_replace('/\\\{([^}]+)\\\}/', '([^/]+)', $pattern);
        
        return '/^' . $pattern . '$/';
    }
    
    /**
     * Execute matched route
     */
    private static function executeRoute($route, $path) {
        try {
            // Extract parameters from path
            $params = self::extractParameters($route['path'], $path);
            
            // Instantiate controller
            $controllerClass = $route['controller'];
            if (!class_exists($controllerClass)) {
                throw new Exception("Controller $controllerClass not found");
            }
            
            $controller = new $controllerClass(self::$db);
            
            // Check if method exists
            $method = $route['action'];
            if (!method_exists($controller, $method)) {
                throw new Exception("Method $method not found in $controllerClass");
            }
            
            // Call controller method with parameters
            return call_user_func_array([$controller, $method], $params);
            
        } catch (Exception $e) {
            app_log('error', 'Router error: ' . $e->getMessage(), [
                'route' => $route,
                'path' => $path
            ]);
            
            return self::handleError($e);
        }
    }
    
    /**
     * Extract parameters from URL path
     */
    private static function extractParameters($routePath, $actualPath) {
        $routeParts = explode('/', trim($routePath, '/'));
        $actualParts = explode('/', trim($actualPath, '/'));
        
        $params = [];
        
        for ($i = 0; $i < count($routeParts); $i++) {
            if (isset($routeParts[$i]) && preg_match('/^{([^}]+)}$/', $routeParts[$i], $matches)) {
                $paramName = $matches[1];
                $params[$paramName] = $actualParts[$i] ?? null;
            }
        }
        
        return array_values($params);
    }
    
    /**
     * Handle 404 Not Found
     */
    private static function handleNotFound() {
        http_response_code(404);
        
        if (file_exists('error_pages/404.php')) {
            include 'error_pages/404.php';
        } else {
            echo '<h1>404 - Page Not Found</h1>';
        }
        
        exit();
    }
    
    /**
     * Handle errors
     */
    private static function handleError($exception) {
        http_response_code(500);
        
        if (env_bool('APP_DEBUG', false)) {
            echo '<h1>Error</h1>';
            echo '<p>' . htmlspecialchars($exception->getMessage()) . '</p>';
            echo '<pre>' . htmlspecialchars($exception->getTraceAsString()) . '</pre>';
        } else {
            if (file_exists('error_pages/500.php')) {
                include 'error_pages/500.php';
            } else {
                echo '<h1>500 - Internal Server Error</h1>';
            }
        }
        
        exit();
    }
    
    /**
     * Generate URL for named route
     */
    public static function url($name, $params = []) {
        // This is a simple implementation
        // In a more complex router, you would store named routes
        return '#'; // Placeholder
    }
    
    /**
     * Redirect to URL
     */
    public static function redirect($url, $statusCode = 302) {
        http_response_code($statusCode);
        header("Location: $url");
        exit();
    }
    
    /**
     * Get all registered routes
     */
    public static function getRoutes() {
        return self::$routes;
    }
    
    /**
     * Clear all routes
     */
    public static function clearRoutes() {
        self::$routes = [];
    }
}

/**
 * Helper functions for routing
 */
if (!function_exists('route')) {
    function route($name, $params = []) {
        return Router::url($name, $params);
    }
}

if (!function_exists('redirect')) {
    function redirect($url, $statusCode = 302) {
        Router::redirect($url, $statusCode);
    }
}
?>
