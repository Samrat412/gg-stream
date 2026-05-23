<?php

namespace App\Controllers\Admin;

use PDO;

class SEOController
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

        include __DIR__ . '/../../../views/admin/seo-tools.php';
    }

    public function audit(): void
    {
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /admin/login');
            exit;
        }

        // Get recent audit results
        $stmt = $this->db->query("
            SELECT * FROM seo_audit_log 
            ORDER BY created_at DESC 
            LIMIT 50
        ");
        $audits = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Calculate aggregate stats
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_audits,
                AVG(score) as avg_score,
                SUM(CASE WHEN has_schema THEN 1 ELSE 0 END) as with_schema,
                SUM(CASE WHEN has_canonical THEN 1 ELSE 0 END) as with_canonical,
                SUM(CASE WHEN has_hreflang THEN 1 ELSE 0 END) as with_hreflang
            FROM seo_audit_log
        ");
        $stats = $stmt->fetch(\PDO::FETCH_ASSOC);

        include __DIR__ . '/../../../views/admin/seo-audit.php';
    }

    public function runAudit(): void
    {
        if (!isset($_SESSION['admin_id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        // Sample URLs to audit
        $urls = [
            '/',
            '/movie/278',
            '/tv/1396',
            '/browse/movies',
            '/browse/tv',
        ];

        $results = [];
        foreach ($urls as $url) {
            $result = $this->auditPage($url);
            $results[] = $result;

            // Save to DB
            $stmt = $this->db->prepare("
                INSERT INTO seo_audit_log (
                    audit_type, page_url, title_length, desc_length, 
                    content_words, internal_links, has_schema, 
                    has_canonical, has_hreflang, issues, score
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                'manual',
                $url,
                $result['title_length'],
                $result['desc_length'],
                $result['content_words'],
                $result['internal_links'],
                $result['has_schema'] ? true : false,
                $result['has_canonical'] ? true : false,
                $result['has_hreflang'] ? true : false,
                implode(', ', $result['issues']),
                $result['score']
            ]);
        }

        echo json_encode(['success' => true, 'results' => $results]);
    }

    private function auditPage(string $url): array
    {
        // Simulate audit (in production, would fetch actual page)
        $baseUrl = rtrim(SITE_DOMAIN, '/') . $url;
        
        $ch = curl_init($baseUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $html = curl_exec($ch);
        curl_close($ch);

        $issues = [];
        $score = 100;

        // Check title
        preg_match('/<title>(.*?)<\/title>/i', $html, $titleMatch);
        $title = $titleMatch[1] ?? '';
        $titleLength = strlen($title);
        if ($titleLength < 40 || $titleLength > 60) {
            $issues[] = 'Title length not optimal (40-60 chars)';
            $score -= 10;
        }

        // Check description
        preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\'](.*?)["\']/i', $html, $descMatch);
        $description = $descMatch[1] ?? '';
        $descLength = strlen($description);
        if ($descLength < 120 || $descLength > 160) {
            $issues[] = 'Description length not optimal (120-160 chars)';
            $score -= 10;
        }

        // Check canonical
        $hasCanonical = (bool) preg_match('/<link[^>]*rel=["\']canonical["\']/i', $html);
        if (!$hasCanonical) {
            $issues[] = 'Missing canonical tag';
            $score -= 15;
        }

        // Check hreflang
        $hasHreflang = (bool) preg_match('/<link[^>]*rel=["\']alternate["\'][^>]*hreflang/i', $html);
        if (!$hasHreflang) {
            $issues[] = 'Missing hreflang tags';
            $score -= 10;
        }

        // Check schema
        $hasSchema = (bool) preg_match('/<script[^>]*type=["\']application\/ld\+json["\']/i', $html);
        if (!$hasSchema) {
            $issues[] = 'Missing structured data';
            $score -= 15;
        }

        // Count words (simplified)
        $textContent = strip_tags($html);
        $contentWords = str_word_count($textContent);
        if ($contentWords < 500 && strpos($url, '/movie/') !== false) {
            $issues[] = 'Low content word count (<500)';
            $score -= 10;
        }

        // Count internal links
        preg_match_all('/<a[^>]*href=["\']\/(?!http|\/\/|#)/i', $html, $linksMatch);
        $internalLinks = count($linksMatch[0] ?? []);
        if ($internalLinks < 15 && strpos($url, '/movie/') !== false) {
            $issues[] = 'Low internal link count (<15)';
            $score -= 5;
        }

        return [
            'url' => $url,
            'title_length' => $titleLength,
            'desc_length' => $descLength,
            'content_words' => $contentWords,
            'internal_links' => $internalLinks,
            'has_schema' => $hasSchema,
            'has_canonical' => $hasCanonical,
            'has_hreflang' => $hasHreflang,
            'issues' => $issues,
            'score' => max(0, $score)
        ];
    }

    public function testTitles(): void
    {
        if (!isset($_SESSION['admin_id'])) {
            http_response_code(403);
            exit;
        }

        $tmdbId = (int) ($_GET['id'] ?? 0);
        if (!$tmdbId) {
            echo json_encode(['error' => 'Invalid ID']);
            exit;
        }

        $tmdb = new \App\Services\TMDBService();
        $movie = $tmdb->fetchMovieDetails($tmdbId);
        
        if (!$movie) {
            echo json_encode(['error' => 'Movie not found']);
            exit;
        }

        $titleGenerator = new \App\Helpers\SEOTitleGenerator();
        $descGenerator = new \App\Helpers\SEODescriptionGenerator();

        $title = $titleGenerator->generateUniqueTitle($movie, false);
        $description = $descGenerator->generateUniqueDescription($movie, false);

        echo json_encode([
            'success' => true,
            'title' => $title,
            'title_length' => strlen($title),
            'description' => $description,
            'desc_length' => strlen($description)
        ]);
    }

    public function triggerIndexNow(): void
    {
        if (!isset($_SESSION['admin_id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        // Get IndexNow key from settings
        $stmt = $this->db->query("SELECT setting_value FROM site_settings WHERE setting_key = 'indexnow_key'");
        $key = $stmt->fetchColumn();

        if (!$key) {
            echo json_encode(['error' => 'IndexNow key not configured']);
            exit;
        }

        $domain = parse_url(SITE_DOMAIN, PHP_URL_HOST);
        $url = "https://www.indexnow.org/IndexNow?url={$domain}&key={$key}";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Log activity
        $stmt = $this->db->prepare("INSERT INTO admin_activity_log (admin_id, action_type, action_detail, ip_address) VALUES (?, 'indexnow', 'Triggered IndexNow submission', ?)");
        $stmt->execute([$_SESSION['admin_id'], $_SERVER['REMOTE_ADDR']]);

        echo json_encode([
            'success' => $httpCode === 200,
            'http_code' => $httpCode,
            'message' => $httpCode === 200 ? 'IndexNow submission successful' : 'IndexNow submission failed'
        ]);
    }
}
