<?php
/**
 * Global Helper Functions
 */

/**
 * Generate CSRF token
 */
function generateCsrfToken(): string {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken(string $token): bool {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Redirect to URL
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * JSON response
 */
function jsonResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Render view with layout
 */
function renderView(string $view, array $data = [], ?string $layout = 'main'): void {
    extract($data);
    
    if ($layout) {
        include __DIR__ . "/../../views/layouts/{$layout}.php";
    } else {
        include __DIR__ . "/../../views/pages/{$view}.php";
    }
}

/**
 * Get client IP address
 */
function getClientIp(): string {
    $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = explode(',', $_SERVER[$key])[0];
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Get user agent
 */
function getUserAgent(): string {
    return $_SERVER['HTTP_USER_AGENT'] ?? '';
}

/**
 * Detect device type
 */
function detectDeviceType(): string {
    $ua = getUserAgent();
    if (preg_match('/Mobile|Android|iPhone|iPad|iPod/i', $ua)) {
        return preg_match('/Tablet|iPad/i', $ua) ? 'tablet' : 'mobile';
    }
    return 'desktop';
}

/**
 * Get country code from IP (simplified - use MaxMind in production)
 */
function getCountryCode(): string {
    // In production, use MaxMind GeoIP2
    return 'US';
}

/**
 * Sanitize output
 */
function e(string $string): string {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Format runtime in minutes to "Xh Ym"
 */
function formatRuntime(int $minutes): string {
    if ($minutes <= 0) return '';
    $hours = intdiv($minutes, 60);
    $mins = $minutes % 60;
    return $hours > 0 ? "{$hours}h {$mins}m" : "{$mins}m";
}

/**
 * Format date
 */
function formatDate(string $date, string $format = 'M d, Y'): string {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Get year from date
 */
function getYear(string $date): string {
    if (empty($date)) return '';
    return date('Y', strtotime($date));
}

/**
 * Truncate text
 */
function truncate(string $text, int $length = 150, string $suffix = '...'): string {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . $suffix;
}

/**
 * Generate slug from title
 */
function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

/**
 * Check if request is AJAX
 */
function isAjax(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Check if bot/crawler
 */
function isBot(): bool {
    $ua = getUserAgent();
    $bots = ['Googlebot', 'Bingbot', 'Slurp', 'DuckDuckBot', 'Baiduspider', 'YandexBot', 'facebot', 'ia_archiver'];
    foreach ($bots as $bot) {
        if (stripos($ua, $bot) !== false) {
            return true;
        }
    }
    return false;
}

/**
 * Get language name from code
 */
function getLanguageName(string $code): string {
    $languages = [
        'en' => 'English',
        'de' => 'German',
        'fr' => 'French',
        'it' => 'Italian',
        'es' => 'Spanish',
        'ja' => 'Japanese',
        'ko' => 'Korean',
        'zh' => 'Chinese',
        'hi' => 'Hindi',
        'pt' => 'Portuguese',
        'ru' => 'Russian',
        'nl' => 'Dutch',
        'pl' => 'Polish',
        'sv' => 'Swedish',
        'da' => 'Danish',
        'no' => 'Norwegian',
        'fi' => 'Finnish'
    ];
    return $languages[strtolower($code)] ?? ucfirst($code);
}

/**
 * Detect quality based on vote average and popularity
 */
function detectQuality(array $media): string {
    $vote = $media['vote_average'] ?? 0;
    $popularity = $media['popularity'] ?? 0;
    
    if ($vote >= 8.0 || $popularity > 500) {
        return '4K Ultra HD';
    } elseif ($vote >= 7.0 || $popularity > 200) {
        return 'Full HD';
    }
    return 'HD';
}

/**
 * Simple 32-bit rolling hash for deterministic selection
 */
function hashCode(string $str): int {
    $hash = 0;
    $len = strlen($str);
    for ($i = 0; $i < $len; $i++) {
        $hash = (($hash << 5) - $hash) + ord($str[$i]);
        $hash &= 0xFFFFFFFF;
    }
    return $hash;
}

/**
 * Load genre maps
 */
function getGenreMaps(): array {
    static $maps = null;
    if ($maps === null) {
        $maps = [
            'movie' => require __DIR__ . '/../../data/genre-maps.php',
            'tv' => require __DIR__ . '/../../data/genre-maps.php',
        ];
    }
    return $maps;
}

/**
 * Get genre name by ID
 */
function getGenreName(int $id, string $type = 'movie'): string {
    $maps = getGenreMaps();
    $map = $type === 'tv' ? $maps['tv'] : $maps['movie'];
    return $map[$id] ?? 'Unknown';
}

/**
 * Set cache headers
 */
function setCacheHeaders(int $maxAge = 300, bool $public = true): void {
    $visibility = $public ? 'public' : 'private';
    header("Cache-Control: {$visibility}, max-age={$maxAge}, s-maxage=" . ($maxAge * 2));
    header("Vary: Accept-Encoding");
}

/**
 * Set no-cache headers
 */
function setNoCacheHeaders(): void {
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
}

/**
 * Log admin activity
 */
function logAdminActivity(int $adminId, string $actionType, string $actionDetail): void {
    $ip = getClientIp();
    Database::insert('admin_activity_log', [
        'admin_id' => $adminId,
        'action_type' => $actionType,
        'action_detail' => $actionDetail,
        'ip_address' => $ip
    ]);
}
