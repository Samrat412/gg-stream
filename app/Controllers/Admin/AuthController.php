<?php

namespace App\Controllers\Admin;

use App\Services\VisitorService;
use App\Models\AdminUser;
use App\Models\SiteSettings;
use PDO;

class AuthController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = \App\Services\Database::getConnection();
    }

    public function loginForm(): void
    {
        if (isset($_SESSION['admin_id'])) {
            header('Location: /admin');
            exit;
        }
        
        $error = $_GET['error'] ?? '';
        include __DIR__ . '/../../../views/admin/login.php';
    }

    public function login(): void
    {
        // Rate limiting
        $ip = $_SERVER['REMOTE_ADDR'];
        $cacheKey = "login_attempts_{$ip}";
        $attempts = (int) ($_SESSION[$cacheKey] ?? 0);
        
        if ($attempts >= 5) {
            $_SESSION['login_error'] = 'Too many login attempts. Please try again later.';
            header('Location: /admin/login');
            exit;
        }

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        if (empty($username) || empty($password)) {
            $_SESSION['login_error'] = 'Please enter username and password';
            header('Location: /admin/login');
            exit;
        }

        // Verify CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['login_error'] = 'Invalid security token';
            header('Location: /admin/login');
            exit;
        }

        $stmt = $this->db->prepare("SELECT * FROM admin_users WHERE username = ? AND is_active = TRUE");
        $stmt->execute([$username]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $_SESSION[$cacheKey] = $attempts + 1;
            $_SESSION['login_error'] = 'Invalid username or password';
            header('Location: /admin/login');
            exit;
        }

        // Clear attempts
        unset($_SESSION[$cacheKey]);

        // Set session
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_role'] = $user['role'];

        // Update last login
        $stmt = $this->db->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);

        // Log activity
        $stmt = $this->db->prepare("INSERT INTO admin_activity_log (admin_id, action_type, action_detail, ip_address) VALUES (?, 'login', 'Admin logged in', ?)");
        $stmt->execute([$user['id'], $ip]);

        // Remember me cookie
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie('admin_remember', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
            $stmt = $this->db->prepare("UPDATE admin_users SET remember_token = ? WHERE id = ?");
            $stmt->execute([$token, $user['id']]);
        }

        // Regenerate session ID
        session_regenerate_id(true);

        header('Location: /admin');
        exit;
    }

    public function logout(): void
    {
        $adminId = $_SESSION['admin_id'] ?? null;
        
        if ($adminId) {
            $stmt = $this->db->prepare("INSERT INTO admin_activity_log (admin_id, action_type, action_detail, ip_address) VALUES (?, 'logout', 'Admin logged out', ?)");
            $stmt->execute([$adminId, $_SERVER['REMOTE_ADDR']]);
        }

        // Clear remember token
        if (isset($_COOKIE['admin_remember'])) {
            $stmt = $this->db->prepare("UPDATE admin_users SET remember_token = NULL WHERE remember_token = ?");
            $stmt->execute([$_COOKIE['admin_remember']]);
            setcookie('admin_remember', '', time() - 3600, '/', '', true, true);
        }

        session_destroy();
        header('Location: /admin/login');
        exit;
    }

    public function users(): void
    {
        if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'superadmin') {
            header('Location: /admin');
            exit;
        }

        $stmt = $this->db->query("SELECT id, username, email, role, is_active, last_login, created_at FROM admin_users ORDER BY created_at DESC");
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        include __DIR__ . '/../../../views/admin/users.php';
    }

    public function createUser(): void
    {
        if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'superadmin') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'admin';

        if (empty($username) || empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'All fields required']);
            exit;
        }

        // Check if exists
        $stmt = $this->db->prepare("SELECT id FROM admin_users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['error' => 'Username or email already exists']);
            exit;
        }

        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);

        $stmt = $this->db->prepare("INSERT INTO admin_users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $passwordHash, $role]);

        $userId = $this->db->lastInsertId();

        // Log activity
        $stmt = $this->db->prepare("INSERT INTO admin_activity_log (admin_id, action_type, action_detail, ip_address) VALUES (?, 'create_user', 'Created user: ' || ?, ?)");
        $stmt->execute([$_SESSION['admin_id'], $username, $_SERVER['REMOTE_ADDR']]);

        echo json_encode(['success' => true, 'user_id' => $userId]);
    }
}
