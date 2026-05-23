<?php

namespace App\Controllers\Admin;

use PDO;

class RequestsController
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

        $status = $_GET['status'] ?? 'all';
        $type = $_GET['type'] ?? '';
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $where = ['1=1'];
        $params = [];

        if ($status !== 'all') {
            $where[] = 'status = ?';
            $params[] = $status;
        }

        if ($type) {
            $where[] = 'request_type = ?';
            $params[] = $type;
        }

        $whereClause = implode(' AND ', $where);

        // Get total count
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM content_requests WHERE {$whereClause}");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();
        $totalPages = ceil($total / $perPage);

        // Get requests
        $stmt = $this->db->prepare("
            SELECT * FROM content_requests 
            WHERE {$whereClause} 
            ORDER BY votes DESC, created_at DESC 
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        $requests = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        include __DIR__ . '/../../../views/admin/requests-manage.php';
    }

    public function updateStatus(): void
    {
        if (!isset($_SESSION['admin_id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $notes = $_POST['admin_notes'] ?? '';

        if (!$id || !in_array($status, ['pending', 'reviewing', 'approved', 'completed', 'rejected'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data']);
            exit;
        }

        $stmt = $this->db->prepare("UPDATE content_requests SET status = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $notes, $id]);

        // Log activity
        $stmt = $this->db->prepare("INSERT INTO admin_activity_log (admin_id, action_type, action_detail, ip_address) VALUES (?, 'update_request', 'Updated request ID ' || ' to ' || ?, ?)");
        $stmt->execute([$_SESSION['admin_id'], $id . ' status ' . $status, $_SERVER['REMOTE_ADDR']]);

        echo json_encode(['success' => true]);
    }

    public function delete(): void
    {
        if (!isset($_SESSION['admin_id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid ID']);
            exit;
        }

        $stmt = $this->db->prepare("DELETE FROM content_requests WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true]);
    }
}
