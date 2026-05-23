<?php
/**
 * Base Controller
 * All controllers extend this class
 */

namespace App\Controllers;

class Controller {
    protected array $data = [];
    
    /**
     * Set data for view
     */
    protected function set(string $key, mixed $value): void {
        $this->data[$key] = $value;
    }
    
    /**
     * Get data from view
     */
    protected function get(string $key, mixed $default = null): mixed {
        return $this->data[$key] ?? $default;
    }
    
    /**
     * Render view with layout
     */
    protected function render(string $view, ?string $layout = 'main'): void {
        extract($this->data);
        
        if ($layout) {
            include __DIR__ . "/../../views/layouts/{$layout}.php";
        } else {
            include __DIR__ . "/../../views/pages/{$view}.php";
        }
    }
    
    /**
     * Render admin view
     */
    protected function renderAdmin(string $view): void {
        extract($this->data);
        include __DIR__ . "/../../views/layouts/admin.php";
    }
    
    /**
     * Return JSON response
     */
    protected function json(array $data, int $statusCode = 200): void {
        jsonResponse($data, $statusCode);
    }
    
    /**
     * Redirect to URL
     */
    protected function redirect(string $url): void {
        redirect($url);
    }
    
    /**
     * Check if request is AJAX
     */
    protected function isAjax(): bool {
        return isAjax();
    }
    
    /**
     * Verify CSRF token
     */
    protected function verifyCsrf(): bool {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return verifyCsrfToken($token);
    }
    
    /**
     * Require admin authentication
     */
    protected function requireAdmin(): void {
        if (!isset($_SESSION['admin_id'])) {
            $this->redirect('/admin/login');
        }
    }
    
    /**
     * Get current admin user
     */
    protected function getCurrentAdmin(): ?array {
        if (!isset($_SESSION['admin_id'])) {
            return null;
        }
        
        return Database::fetchOne(
            'SELECT * FROM admin_users WHERE id = ?',
            [$_SESSION['admin_id']]
        );
    }
}
