<?php

namespace App\Controllers\Admin;

use PDO;

class CacheController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = \App\Services\Database::getConnection();
    }

    public function index(): void
    {
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /admin/login');
            exit;
        }

        // Get cache statistics
        $stmt = $this->db->query("SELECT COUNT(*) FROM cache_entries");
        $totalCached = (int) $stmt->fetchColumn();

        $stmt = $this->db->query("SELECT SUM(pg_column_size(cache_value)) FROM cache_entries");
        $cacheSize = (int) ($stmt->fetchColumn() ?: 0);

        $stmt = $this->db->query("SELECT MIN(expires_at) FROM cache_entries WHERE expires_at IS NOT NULL");
        $oldestExpiry = $stmt->fetchColumn();

        $stmt = $this->db->query("SELECT MAX(created_at) FROM cache_entries");
        $newestEntry = $stmt->fetchColumn();

        // Get cache breakdown by type
        $stmt = $this->db->query("
            SELECT 
                CASE 
                    WHEN cache_key LIKE 'tmdb_%' THEN 'TMDB API'
                    WHEN cache_key LIKE 'page_%' THEN 'Page Cache'
                    ELSE 'Other'
                END as type,
                COUNT(*) as count
            FROM cache_entries
            GROUP BY type
        ");
        $breakdown = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        include __DIR__ . '/../../../views/admin/cache-manager.php';
    }

    public function clear(): void
    {
        if (!isset($_SESSION['admin_id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $type = $_POST['type'] ?? 'all';

        if ($type === 'all') {
            $this->db->query("TRUNCATE TABLE cache_entries");
        } elseif ($type === 'tmdb') {
            $this->db->query("DELETE FROM cache_entries WHERE cache_key LIKE 'tmdb_%'");
        } elseif ($type === 'pages') {
            $this->db->query("DELETE FROM cache_entries WHERE cache_key LIKE 'page_%'");
        } elseif ($type === 'key') {
            $key = $_POST['cache_key'] ?? '';
            if ($key) {
                $stmt = $this->db->prepare("DELETE FROM cache_entries WHERE cache_key = ?");
                $stmt->execute([$key]);
            }
        }

        // Log activity
        $stmt = $this->db->prepare("INSERT INTO admin_activity_log (admin_id, action_type, action_detail, ip_address) VALUES (?, 'clear_cache', 'Cleared cache: ' || ?, ?)");
        $stmt->execute([$_SESSION['admin_id'], $type, $_SERVER['REMOTE_ADDR']]);

        echo json_encode(['success' => true]);
    }

    public function warm(): void
    {
        if (!isset($_SESSION['admin_id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        // Get TMDB service and warm popular endpoints
        $tmdb = new \App\Services\TMDBService();
        
        // Warm trending movies and TV
        $tmdb->fetchTrending('movie', 'week');
        $tmdb->fetchTrending('tv', 'week');
        $tmdb->fetchPopularMovies(1);
        $tmdb->fetchPopularTV(1);

        // Log activity
        $stmt = $this->db->prepare("INSERT INTO admin_activity_log (admin_id, action_type, action_detail, ip_address) VALUES (?, 'warm_cache', 'Warmed cache for popular content', ?)");
        $stmt->execute([$_SESSION['admin_id'], $_SERVER['REMOTE_ADDR']]);

        echo json_encode(['success' => true, 'message' => 'Cache warmed successfully']);
    }
}
