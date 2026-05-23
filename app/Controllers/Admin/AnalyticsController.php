<?php

namespace App\Controllers\Admin;

use PDO;

class AnalyticsController
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

        // Date range from query params
        $range = $_GET['range'] ?? '7d';
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;

        // Calculate date range
        if ($startDate && $endDate) {
            $start = $startDate;
            $end = $endDate;
        } else {
            $end = date('Y-m-d');
            switch ($range) {
                case 'today':
                    $start = $end;
                    break;
                case '7d':
                    $start = date('Y-m-d', strtotime('-6 days'));
                    break;
                case '30d':
                    $start = date('Y-m-d', strtotime('-29 days'));
                    break;
                case '90d':
                    $start = date('Y-m-d', strtotime('-89 days'));
                    break;
                default:
                    $start = date('Y-m-d', strtotime('-6 days'));
            }
        }

        // Visitor trend data
        $stmt = $this->db->prepare("
            SELECT visit_date, visit_count 
            FROM daily_visitors 
            WHERE visit_date BETWEEN ? AND ? 
            ORDER BY visit_date
        ");
        $stmt->execute([$start, $end]);
        $visitorTrend = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Geographic breakdown
        $stmt = $this->db->prepare("
            SELECT country_code, COUNT(*) as count 
            FROM page_views 
            WHERE DATE(created_at) BETWEEN ? AND ? AND country_code IS NOT NULL
            GROUP BY country_code 
            ORDER BY count DESC 
            LIMIT 50
        ");
        $stmt->execute([$start, $end]);
        $geoBreakdown = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Referrer sources
        $stmt = $this->db->prepare("
            SELECT referrer_source, COUNT(*) as count 
            FROM page_views 
            WHERE DATE(created_at) BETWEEN ? AND ? AND referrer_source IS NOT NULL AND referrer_source != ''
            GROUP BY referrer_source 
            ORDER BY count DESC 
            LIMIT 20
        ");
        $stmt->execute([$start, $end]);
        $referrerSources = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Top pages
        $stmt = $this->db->prepare("
            SELECT page_path, COUNT(*) as views, COUNT(DISTINCT session_id) as unique_visitors
            FROM page_views 
            WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY page_path 
            ORDER BY views DESC 
            LIMIT 50
        ");
        $stmt->execute([$start, $end]);
        $topPages = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Device breakdown
        $stmt = $this->db->prepare("
            SELECT device_type, COUNT(*) as count 
            FROM page_views 
            WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY device_type
        ");
        $stmt->execute([$start, $end]);
        $deviceBreakdown = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Peak hours analysis
        $stmt = $this->db->prepare("
            SELECT stat_hour, SUM(page_views_count) as total_views, SUM(unique_visitors) as total_unique
            FROM hourly_stats 
            WHERE stat_date BETWEEN ? AND ?
            GROUP BY stat_hour 
            ORDER BY stat_hour
        ");
        $stmt->execute([$start, $end]);
        $peakHours = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Calculate totals
        $totalVisitors = array_sum(array_column($visitorTrend, 'visit_count'));
        $totalPageViews = array_sum(array_column($topPages, 'views'));
        $avgPagesPerSession = $totalPageViews > 0 ? round($totalPageViews / max(1, $totalVisitors), 2) : 0;

        include __DIR__ . '/../../../views/admin/analytics.php';
    }

    public function live(): void
    {
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /admin/login');
            exit;
        }

        // Current online visitors (last 5 minutes)
        $stmt = $this->db->query("SELECT COUNT(*) FROM active_sessions WHERE last_seen > NOW() - INTERVAL '5 minutes'");
        $onlineNow = (int) ($stmt->fetchColumn() ?: 0);

        // Active sessions list
        $stmt = $this->db->query("
            SELECT session_id, page_path, device_type, country_code, last_seen 
            FROM active_sessions 
            WHERE last_seen > NOW() - INTERVAL '5 minutes' 
            ORDER BY last_seen DESC 
            LIMIT 100
        ");
        $activeSessions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Live event feed (last 50 page views)
        $stmt = $this->db->query("
            SELECT page_path, device_type, country_code, created_at 
            FROM page_views 
            ORDER BY created_at DESC 
            LIMIT 50
        ");
        $liveFeed = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Trending pages (last 30 minutes)
        $stmt = $this->db->query("
            SELECT page_path, COUNT(*) as views 
            FROM page_views 
            WHERE created_at > NOW() - INTERVAL '30 minutes' 
            GROUP BY page_path 
            ORDER BY views DESC 
            LIMIT 20
        ");
        $trendingPages = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        include __DIR__ . '/../../../views/admin/analytics-live.php';
    }

    public function trafficData(): void
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['admin_id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $range = $_GET['range'] ?? '7d';
        $end = date('Y-m-d');
        
        switch ($range) {
            case 'today':
                $start = $end;
                break;
            case '7d':
                $start = date('Y-m-d', strtotime('-6 days'));
                break;
            case '30d':
                $start = date('Y-m-d', strtotime('-29 days'));
                break;
            case '90d':
                $start = date('Y-m-d', strtotime('-89 days'));
                break;
            default:
                $start = date('Y-m-d', strtotime('-6 days'));
        }

        $stmt = $this->db->prepare("
            SELECT visit_date, visit_count 
            FROM daily_visitors 
            WHERE visit_date BETWEEN ? AND ? 
            ORDER BY visit_date
        ");
        $stmt->execute([$start, $end]);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $data]);
    }

    public function trafficLive(): void
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['admin_id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        // Current online visitors
        $stmt = $this->db->query("SELECT COUNT(*) FROM active_sessions WHERE last_seen > NOW() - INTERVAL '5 minutes'");
        $onlineNow = (int) ($stmt->fetchColumn() ?: 0);

        // Recent page views
        $stmt = $this->db->query("
            SELECT page_path, device_type, country_code, created_at 
            FROM page_views 
            ORDER BY created_at DESC 
            LIMIT 20
        ");
        $recentViews = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'online_now' => $onlineNow,
            'recent_views' => $recentViews
        ]);
    }

    public function trafficStream(): void
    {
        if (!isset($_SESSION['admin_id'])) {
            http_response_code(403);
            exit;
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');

        while (true) {
            // Get current online count
            $stmt = $this->db->query("SELECT COUNT(*) FROM active_sessions WHERE last_seen > NOW() - INTERVAL '5 minutes'");
            $onlineNow = (int) ($stmt->fetchColumn() ?: 0);

            // Get latest page view
            $stmt = $this->db->query("
                SELECT page_path, device_type, country_code, created_at 
                FROM page_views 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $latestView = $stmt->fetch(\PDO::FETCH_ASSOC);

            $data = [
                'online_now' => $onlineNow,
                'latest_view' => $latestView,
                'timestamp' => time()
            ];

            echo "data: " . json_encode($data) . "\n\n";
            ob_flush();
            flush();

            sleep(3);
        }
    }
}
