<?php
/**
 * Advanced Cache Manager
 * Comprehensive caching system for E-Arsip application
 */

class CacheManager {
    private $cacheDir;
    private $defaultTtl = 3600; // 1 hour
    private $maxCacheSize = 104857600; // 100MB
    private $compressionEnabled = true;
    
    public function __construct($cacheDir = 'cache/') {
        $this->cacheDir = rtrim($cacheDir, '/') . '/';
        $this->ensureCacheDirectory();
    }
    
    /**
     * Ensure cache directory exists
     */
    private function ensureCacheDirectory() {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
        
        // Create subdirectories
        $subdirs = ['queries/', 'views/', 'sessions/', 'files/', 'api/'];
        foreach ($subdirs as $subdir) {
            $path = $this->cacheDir . $subdir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }
    
    /**
     * Store data in cache
     */
    public function set($key, $data, $ttl = null, $category = 'default') {
        $ttl = $ttl ?? $this->defaultTtl;
        $cacheFile = $this->getCacheFilePath($key, $category);
        
        $cacheData = [
            'data' => $data,
            'expires' => time() + $ttl,
            'created' => time(),
            'hits' => 0,
            'size' => 0
        ];
        
        // Serialize and optionally compress data
        $serialized = serialize($cacheData);
        if ($this->compressionEnabled && function_exists('gzcompress')) {
            $serialized = gzcompress($serialized, 6);
            $cacheData['compressed'] = true;
        }
        
        $cacheData['size'] = strlen($serialized);
        
        // Check cache size limits
        if ($this->getCacheSize() + $cacheData['size'] > $this->maxCacheSize) {
            $this->cleanup();
        }
        
        $result = file_put_contents($cacheFile, $serialized, LOCK_EX);
        
        if ($result !== false) {
            $this->updateCacheIndex($key, $category, $cacheData);
            return true;
        }
        
        return false;
    }
    
    /**
     * Retrieve data from cache
     */
    public function get($key, $category = 'default') {
        $cacheFile = $this->getCacheFilePath($key, $category);
        
        if (!file_exists($cacheFile)) {
            return null;
        }
        
        $content = file_get_contents($cacheFile);
        if ($content === false) {
            return null;
        }
        
        // Decompress if needed
        if ($this->compressionEnabled && function_exists('gzuncompress')) {
            $decompressed = @gzuncompress($content);
            if ($decompressed !== false) {
                $content = $decompressed;
            }
        }
        
        $cacheData = @unserialize($content);
        if ($cacheData === false) {
            $this->delete($key, $category);
            return null;
        }
        
        // Check expiration
        if ($cacheData['expires'] < time()) {
            $this->delete($key, $category);
            return null;
        }
        
        // Update hit count
        $cacheData['hits']++;
        $this->updateCacheStats($key, $category, $cacheData);
        
        return $cacheData['data'];
    }
    
    /**
     * Delete cache entry
     */
    public function delete($key, $category = 'default') {
        $cacheFile = $this->getCacheFilePath($key, $category);
        
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
            $this->removeCacheIndex($key, $category);
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if cache entry exists and is valid
     */
    public function has($key, $category = 'default') {
        return $this->get($key, $category) !== null;
    }
    
    /**
     * Clear all cache or specific category
     */
    public function clear($category = null) {
        if ($category) {
            $pattern = $this->cacheDir . $category . '/*.cache';
        } else {
            $pattern = $this->cacheDir . '*/*.cache';
        }
        
        $files = glob($pattern);
        $cleared = 0;
        
        foreach ($files as $file) {
            if (unlink($file)) {
                $cleared++;
            }
        }
        
        if (!$category) {
            $this->clearCacheIndex();
        }
        
        return $cleared;
    }
    
    /**
     * Get cache statistics
     */
    public function getStats() {
        $stats = [
            'total_files' => 0,
            'total_size' => 0,
            'categories' => [],
            'expired_files' => 0,
            'hit_ratio' => 0
        ];
        
        $categories = ['queries', 'views', 'sessions', 'files', 'api', 'default'];
        
        foreach ($categories as $category) {
            $categoryPath = $this->cacheDir . $category . '/';
            if (!is_dir($categoryPath)) continue;
            
            $files = glob($categoryPath . '*.cache');
            $categoryStats = [
                'files' => count($files),
                'size' => 0,
                'expired' => 0,
                'hits' => 0
            ];
            
            foreach ($files as $file) {
                $size = filesize($file);
                $categoryStats['size'] += $size;
                $stats['total_size'] += $size;
                
                // Check if expired
                $content = file_get_contents($file);
                if ($this->compressionEnabled && function_exists('gzuncompress')) {
                    $decompressed = @gzuncompress($content);
                    if ($decompressed !== false) {
                        $content = $decompressed;
                    }
                }
                
                $cacheData = @unserialize($content);
                if ($cacheData && $cacheData['expires'] < time()) {
                    $categoryStats['expired']++;
                    $stats['expired_files']++;
                }
                
                if ($cacheData && isset($cacheData['hits'])) {
                    $categoryStats['hits'] += $cacheData['hits'];
                }
            }
            
            $stats['categories'][$category] = $categoryStats;
            $stats['total_files'] += $categoryStats['files'];
        }
        
        return $stats;
    }
    
    /**
     * Cleanup expired cache entries
     */
    public function cleanup() {
        $cleaned = 0;
        $categories = ['queries', 'views', 'sessions', 'files', 'api', 'default'];
        
        foreach ($categories as $category) {
            $categoryPath = $this->cacheDir . $category . '/';
            if (!is_dir($categoryPath)) continue;
            
            $files = glob($categoryPath . '*.cache');
            
            foreach ($files as $file) {
                $content = file_get_contents($file);
                if ($this->compressionEnabled && function_exists('gzuncompress')) {
                    $decompressed = @gzuncompress($content);
                    if ($decompressed !== false) {
                        $content = $decompressed;
                    }
                }
                
                $cacheData = @unserialize($content);
                if (!$cacheData || $cacheData['expires'] < time()) {
                    unlink($file);
                    $cleaned++;
                }
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Get cache file path
     */
    private function getCacheFilePath($key, $category) {
        $hashedKey = md5($key);
        return $this->cacheDir . $category . '/' . $hashedKey . '.cache';
    }
    
    /**
     * Get total cache size
     */
    private function getCacheSize() {
        $size = 0;
        $files = glob($this->cacheDir . '*/*.cache');
        
        foreach ($files as $file) {
            $size += filesize($file);
        }
        
        return $size;
    }
    
    /**
     * Update cache index
     */
    private function updateCacheIndex($key, $category, $cacheData) {
        $indexFile = $this->cacheDir . 'index.json';
        $index = [];
        
        if (file_exists($indexFile)) {
            $index = json_decode(file_get_contents($indexFile), true) ?: [];
        }
        
        $index[$category][$key] = [
            'created' => $cacheData['created'],
            'expires' => $cacheData['expires'],
            'size' => $cacheData['size'],
            'hits' => $cacheData['hits']
        ];
        
        file_put_contents($indexFile, json_encode($index), LOCK_EX);
    }
    
    /**
     * Remove from cache index
     */
    private function removeCacheIndex($key, $category) {
        $indexFile = $this->cacheDir . 'index.json';
        
        if (file_exists($indexFile)) {
            $index = json_decode(file_get_contents($indexFile), true) ?: [];
            
            if (isset($index[$category][$key])) {
                unset($index[$category][$key]);
                file_put_contents($indexFile, json_encode($index), LOCK_EX);
            }
        }
    }
    
    /**
     * Clear cache index
     */
    private function clearCacheIndex() {
        $indexFile = $this->cacheDir . 'index.json';
        if (file_exists($indexFile)) {
            unlink($indexFile);
        }
    }
    
    /**
     * Update cache statistics
     */
    private function updateCacheStats($key, $category, $cacheData) {
        // Update hit count in the actual cache file
        $cacheFile = $this->getCacheFilePath($key, $category);
        $serialized = serialize($cacheData);
        
        if ($this->compressionEnabled && function_exists('gzcompress')) {
            $serialized = gzcompress($serialized, 6);
        }
        
        file_put_contents($cacheFile, $serialized, LOCK_EX);
        $this->updateCacheIndex($key, $category, $cacheData);
    }
    
    /**
     * Remember function - cache with automatic key generation
     */
    public function remember($key, $callback, $ttl = null, $category = 'default') {
        $data = $this->get($key, $category);
        
        if ($data === null) {
            $data = call_user_func($callback);
            $this->set($key, $data, $ttl, $category);
        }
        
        return $data;
    }
    
    /**
     * Cache tags for group invalidation
     */
    public function tags($tags) {
        return new CacheTagManager($this, $tags);
    }
    
    /**
     * Set cache configuration
     */
    public function setConfig($config) {
        if (isset($config['default_ttl'])) {
            $this->defaultTtl = $config['default_ttl'];
        }
        
        if (isset($config['max_cache_size'])) {
            $this->maxCacheSize = $config['max_cache_size'];
        }
        
        if (isset($config['compression'])) {
            $this->compressionEnabled = $config['compression'];
        }
    }
}

/**
 * Cache Tag Manager for group invalidation
 */
class CacheTagManager {
    private $cache;
    private $tags;
    
    public function __construct($cache, $tags) {
        $this->cache = $cache;
        $this->tags = is_array($tags) ? $tags : [$tags];
    }
    
    public function set($key, $data, $ttl = null) {
        // Store the actual data
        $result = $this->cache->set($key, $data, $ttl);
        
        // Store tag associations
        foreach ($this->tags as $tag) {
            $tagKey = 'tag:' . $tag;
            $taggedKeys = $this->cache->get($tagKey, 'tags') ?: [];
            $taggedKeys[] = $key;
            $this->cache->set($tagKey, array_unique($taggedKeys), null, 'tags');
        }
        
        return $result;
    }
    
    public function flush() {
        foreach ($this->tags as $tag) {
            $tagKey = 'tag:' . $tag;
            $taggedKeys = $this->cache->get($tagKey, 'tags') ?: [];
            
            foreach ($taggedKeys as $key) {
                $this->cache->delete($key);
            }
            
            $this->cache->delete($tagKey, 'tags');
        }
    }
}

// Global cache instance
$cacheManager = null;

if (!function_exists('cache')) {
    function cache() {
        global $cacheManager;
        if (!$cacheManager) {
            $cacheManager = new CacheManager();
        }
        return $cacheManager;
    }
}

if (!function_exists('cache_remember')) {
    function cache_remember($key, $callback, $ttl = null, $category = 'default') {
        return cache()->remember($key, $callback, $ttl, $category);
    }
}
?>
