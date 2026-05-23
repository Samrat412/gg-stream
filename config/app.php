<?php
/**
 * Application Configuration
 * Prime Video Style Streaming Discovery Site
 */

// Site Information
define('SITE_NAME', 'StreamHub');
define('SITE_DOMAIN', 'https://www.yourdomain.com');
define('SITE_TAGLINE', 'Watch Free Movies & TV Shows Online');

// TMDB Configuration
define('TMDB_API_KEY', getenv('TMDB_API_KEY') ?: '');
define('TMDB_BASE_URL', 'https://api.themoviedb.org/3');
define('TMDB_IMAGE_BASE', 'https://image.tmdb.org/t/p');

// Cache Configuration
define('CACHE_DIR', __DIR__ . '/../cache');
define('CACHE_TTL_DETAILS', 300);      // 5 minutes
define('CACHE_TTL_DISCOVER', 600);     // 10 minutes
define('CACHE_TTL_SEARCH', 300);       // 5 minutes
define('CACHE_TTL_SITEMAP', 43200);    // 12 hours
define('CACHE_TTL_PERSON', 1800);      // 30 minutes

// Session Configuration
define('SESSION_LIFETIME', 3600);
define('SESSION_NAME', 'streamhub_session');

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 300);

// Player Configuration
define('PRIMARY_EMBED_DOMAIN', 'vidsrc.cc');
define('SECONDARY_EMBED_DOMAIN', 'videasy.net');

// Geo Targets
define('GEO_TARGETS', ['US', 'GB', 'DE', 'FR', 'IT', 'ES', 'NL', 'PL', 'SE', 'DK', 'NO', 'FI']);
define('HREFLANG_LOCALES', [
    'en-US', 'en-GB', 'de-DE', 'fr-FR', 'it-IT', 'es-ES',
    'nl-NL', 'pl-PL', 'sv-SE', 'da-DK', 'nb-NO', 'fi-FI'
]);

// Timezone
date_default_timezone_set('UTC');

// Error Reporting (disable in production)
if (getenv('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Output buffering with gzip
ob_start('ob_gzhandler');

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Load helper functions
require_once __DIR__ . '/../app/Helpers/functions.php';
