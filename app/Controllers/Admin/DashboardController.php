<?php

namespace App\Controllers\Admin;

use PDO;

class DashboardController
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

        // Get total visitors
        $stmt = $this->db->query("SELECT total_count FROM visitor_counter WHERE id = 1");
        $totalVisitors = (int) ($stmt->fetchColumn() ?: 0);

        // Get today's visitors
        $today = date('Y-m-d');
        $stmt = $this->db->prepare("SELECT visit_count FROM daily_visitors WHERE visit_date = ?");
        $stmt->execute([$today]);
        $todayVisitors = (int) ($stmt->fetchColumn() ?: 0);

        // Get yesterday's visitors
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $stmt = $this->db->prepare("SELECT visit_count FROM daily_visitors WHERE visit_date = ?");
        $stmt->execute([$yesterday]);
        $yesterdayVisitors = (int) ($stmt->fetchColumn() ?: 0);

        // Calculate change
        $changePercent = $yesterdayVisitors > 0 
            ? round((($todayVisitors - $yesterdayVisitors) / $yesterdayVisitors) * 100, 1)
            : 0;

        // Get active sessions (last 5 minutes)
        $stmt = $this->db->query("SELECT COUNT(*) FROM active_sessions WHERE last_seen > NOW() - INTERVAL '5 minutes'");
        $onlineNow = (int) ($stmt->fetchColumn() ?: 0);

        // Get today's page views
        $stmt = $this->db->prepare("SELECT SUM(page_views_count) FROM hourly_stats WHERE stat_date = ?");
        $stmt->execute([$today]);
        $todayPageViews = (int) ($stmt->fetchColumn() ?: 0);

        // Get this week's total
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $stmt = $this->db->prepare("SELECT SUM(visit_count) FROM daily_visitors WHERE visit_date >= ?");
        $stmt->execute([$weekStart]);
        $weekTotal = (int) ($stmt->fetchColumn() ?: 0);

        // Get this month's total
        $monthStart = date('Y-m-01');
        $stmt = $this->db->prepare("SELECT SUM(visit_count) FROM daily_visitors WHERE visit_date >= ?");
        $stmt->execute([$monthStart]);
        $monthTotal = (int) ($stmt->fetchColumn() ?: 0);

        // Top 10 pages today
        $stmt = $this->db->prepare("
            SELECT page_path, COUNT(*) as views 
            FROM page_views 
            WHERE DATE(created_at) = ? 
            GROUP BY page_path 
            ORDER BY views DESC 
            LIMIT 10
        ");
        $stmt->execute([$today]);
        $topPages = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Device breakdown
        $stmt = $this->db->prepare("
            SELECT device_type, COUNT(*) as count 
            FROM page_views 
            WHERE DATE(created_at) = ? 
            GROUP BY device_type
        ");
        $stmt->execute([$today]);
        $deviceBreakdown = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Country breakdown (top 20)
        $stmt = $this->db->prepare("
            SELECT country_code, COUNT(*) as count 
            FROM page_views 
            WHERE DATE(created_at) = ? AND country_code IS NOT NULL
            GROUP BY country_code 
            ORDER BY count DESC 
            LIMIT 20
        ");
        $stmt->execute([$today]);
        $countryBreakdown = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Hourly stats for today
        $stmt = $this->db->prepare("
            SELECT stat_hour, page_views_count, unique_visitors 
            FROM hourly_stats 
            WHERE stat_date = ? 
            ORDER BY stat_hour
        ");
        $stmt->execute([$today]);
        $hourlyStats = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Last 7 days trend
        $stmt = $this->db->query("
            SELECT visit_date, visit_count 
            FROM daily_visitors 
            WHERE visit_date >= CURRENT_DATE - INTERVAL '7 days' 
            ORDER BY visit_date
        ");
        $last7Days = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Last 30 days trend
        $stmt = $this->db->query("
            SELECT visit_date, visit_count 
            FROM daily_visitors 
            WHERE visit_date >= CURRENT_DATE - INTERVAL '30 days' 
            ORDER BY visit_date
        ");
        $last30Days = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Recent activity (last 20 page views)
        $stmt = $this->db->query("
            SELECT page_path, device_type, country_code, created_at 
            FROM page_views 
            ORDER BY created_at DESC 
            LIMIT 20
        ");
        $recentActivity = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Active sessions list
        $stmt = $this->db->query("
            SELECT session_id, page_path, device_type, last_seen 
            FROM active_sessions 
            WHERE last_seen > NOW() - INTERVAL '5 minutes' 
            ORDER BY last_seen DESC 
            LIMIT 50
        ");
        $activeSessions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get maintenance mode setting
        $stmt = $this->db->query("SELECT setting_value FROM site_settings WHERE setting_key = 'maintenance_mode'");
        $maintenanceMode = $stmt->fetchColumn() === 'true';

        include __DIR__ . '/../../../views/admin/dashboard.php';
    }
}
