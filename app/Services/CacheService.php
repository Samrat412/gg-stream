<?php
/**
 * Cache Service
 * File-based and APCu caching for TMDB responses and pages
 */

class CacheService {
    private string $cacheDir;
    private bool $useApcu;
    
    public function __construct() {
        $this->cacheDir = CACHE_DIR;
        $this->useApcu = function_exists('apcu_fetch') && function_exists('apcu_store');
        
        // Ensure cache directories exist
        @mkdir($this->cacheDir . '/tmdb', 0755, true);
        @mkdir($this->cacheDir . '/pages', 0755, true);
    }
    
    /**
     * Get item from cache
     */
    public function get(string $key): mixed {
        $cacheKey = $this->normalizeKey($key);
        
        // Try APCu first
        if ($this->useApcu) {
            $value = apcu_fetch($cacheKey, $success);
            if ($success) {
                return $value;
            }
        }
        
        // Try file cache
        $file = $this->getFilePath($cacheKey);
        if (file_exists($file)) {
            $data = unserialize(file_get_contents($file));
            if ($data && (!isset($data['expires']) || $data['expires'] > time())) {
                return $data['value'];
            }
            // Expired, delete
            @unlink($file);
        }
        
        return null;
    }
    
    /**
     * Set item in cache
     */
    public function set(string $key, mixed $value, int $ttl = 300): bool {
        $cacheKey = $this->normalizeKey($key);
        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        // Store in APCu
        if ($this->useApcu) {
            apcu_store($cacheKey, $data, $ttl);
        }
        
        // Store in file
        $file = $this->getFilePath($cacheKey);
        $dir = dirname($file);
        @mkdir($dir, 0755, true);
        
        return file_put_contents($file, serialize($data), LOCK_EX) !== false;
    }
    
    /**
     * Delete item from cache
     */
    public function delete(string $key): bool {
        $cacheKey = $this->normalizeKey($key);
        
        // Delete from APCu
        if ($this->useApcu) {
            apcu_delete($cacheKey);
        }
        
        // Delete from file
        $file = $this->getFilePath($cacheKey);
        return !file_exists($file) || unlink($file);
    }
    
    /**
     * Clear all cache
     */
    public function clearAll(): bool {
        // Clear APCu
        if ($this->useApcu) {
            apcu_clear_cache();
        }
        
        // Clear file cache
        $this->clearDirectory($this->cacheDir . '/tmdb');
        $this->clearDirectory($this->cacheDir . '/pages');
        
        return true;
    }
    
    /**
     * Clear TMDB cache only
     */
    public function clearTmdb(): bool {
        $this->clearDirectory($this->cacheDir . '/tmdb');
        return true;
    }
    
    /**
     * Clear page cache only
     */
    public function clearPages(): bool {
        $this->clearDirectory($this->cacheDir . '/pages');
        return true;
    }
    
    /**
     * Get cache statistics
     */
    public function getStats(): array {
        $stats = [
            'tmdb_count' => 0,
            'tmdb_size' => 0,
            'pages_count' => 0,
            'pages_size' => 0,
            'total_count' => 0,
            'total_size' => 0,
        ];
        
        $tmdbStats = $this->getDirectoryStats($this->cacheDir . '/tmdb');
        $pagesStats = $this->getDirectoryStats($this->cacheDir . '/pages');
        
        $stats['tmdb_count'] = $tmdbStats['count'];
        $stats['tmdb_size'] = $tmdbStats['size'];
        $stats['pages_count'] = $pagesStats['count'];
        $stats['pages_size'] = $pagesStats['size'];
        $stats['total_count'] = $tmdbStats['count'] + $pagesStats['count'];
        $stats['total_size'] = $tmdbStats['size'] + $pagesStats['size'];
        
        return $stats;
    }
    
    /**
     * Remember - get from cache or execute callback and cache result
     */
    public function remember(string $key, callable $callback, int $ttl = 300): mixed {
        $cached = $this->get($key);
        if ($cached !== null) {
            return $cached;
        }
        
        $value = $callback();
        $this->set($key, $value, $ttl);
        return $value;
    }
    
    private function normalizeKey(string $key): string {
        return md5($key);
    }
    
    private function getFilePath(string $cacheKey): string {
        // Use first 2 chars as subdirectory for better performance
        $subDir = substr($cacheKey, 0, 2);
        return $this->cacheDir . '/tmdb/' . $subDir . '/' . $cacheKey . '.cache';
    }
    
    private function clearDirectory(string $dir): void {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($files as $file) {
            if ($file->isFile()) {
                unlink($file->getPathname());
            }
        }
    }
    
    private function getDirectoryStats(string $dir): array {
        $count = 0;
        $size = 0;
        
        if (!is_dir($dir)) {
            return ['count' => 0, 'size' => 0];
        }
        
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($files as $file) {
            if ($file->isFile()) {
                $count++;
                $size += $file->getSize();
            }
        }
        
        return ['count' => $count, 'size' => $size];
    }
}
