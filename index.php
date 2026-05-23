<?php
/**
 * Front Controller
 * All requests route through this file
 * Prime Video Style Streaming Discovery Site
 */

// Start session
session_name(SESSION_NAME);
session_start();

// Load configuration
require_once __DIR__ . '/config/app.php';

// Initialize database connection (lazy load)
// Database::getInstance();

// Load routes
$routes = require __DIR__ . '/config/routes.php';

// Get request method and URI
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove trailing slash except for root
if ($requestUri !== '/' && substr($requestUri, -1) === '/') {
    $requestUri = rtrim($requestUri, '/');
}

// Find matching route
$matchedRoute = null;
$routeParams = [];

foreach ($routes as $route => $handler) {
    // Parse route pattern
    [$method, $pattern] = explode(' ', $route, 2);
    
    if ($method !== $requestMethod) {
        continue;
    }
    
    // Convert pattern to regex
    $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
    $regex = '#^' . $regex . '$#';
    
    if (preg_match($regex, $requestUri, $matches)) {
        $matchedRoute = $handler;
        $routeParams = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        break;
    }
}

// Handle 404
if (!$matchedRoute) {
    http_response_code(404);
    $controller = new App\Controllers\ErrorController();
    $controller->notFound();
    exit;
}

// Parse handler
[$controllerClass, $method] = $matchedRoute;

// Add namespace
if (strpos($controllerClass, '\\') === false) {
    $controllerClass = "App\\Controllers\\{$controllerClass}";
}

// Instantiate controller and call method
try {
    $controller = new $controllerClass();
    
    if (!method_exists($controller, $method)) {
        throw new Exception("Method {$method} not found in {$controllerClass}");
    }
    
    // Call controller method with route params
    call_user_func_array([$controller, $method], $routeParams);
    
} catch (Exception $e) {
    error_log("Controller error: " . $e->getMessage());
    http_response_code(500);
    
    if (getenv('APP_ENV') === 'development') {
        echo "<h1>Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
    } else {
        $controller = new App\Controllers\ErrorController();
        $controller->serverError();
    }
}
