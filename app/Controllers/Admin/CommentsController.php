<?php

namespace App\Controllers\Admin;

use PDO;

class CommentsController
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

        $filter = $_GET['filter'] ?? 'all';
        $mediaType = $_GET['type'] ?? '';
        $search = $_GET['search'] ?? '';
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $where = ['1=1'];
        $params = [];

        if ($filter === 'approved') {
            $where[] = 'is_approved = TRUE';
        } elseif ($filter === 'flagged') {
            $where[] = 'is_flagged = TRUE';
        } elseif ($filter === 'pending') {
            $where[] = 'is_approved = FALSE';
        }

        if ($mediaType) {
            $where[] = 'media_type = ?';
            $params[] = $mediaType;
        }

        if ($search) {
            $where[] = '(username ILIKE ? OR comment_text ILIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $whereClause = implode(' AND ', $where);

        // Get total count
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM comments WHERE {$whereClause}");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();
        $totalPages = ceil($total / $perPage);

        // Get comments
        $stmt = $this->db->prepare("
            SELECT * FROM comments 
            WHERE {$whereClause} 
            ORDER BY created_at DESC 
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        $comments = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        include __DIR__ . '/../../../views/admin/comments-manage.php';
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

        $stmt = $this->db->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$id]);

        // Log activity
        $stmt = $this->db->prepare("INSERT INTO admin_activity_log (admin_id, action_type, action_detail, ip_address) VALUES (?, 'delete_comment', 'Deleted comment ID: ' || ?, ?)");
        $stmt->execute([$_SESSION['admin_id'], $id, $_SERVER['REMOTE_ADDR']]);

        echo json_encode(['success' => true]);
    }

    public function bulkDelete(): void
    {
        if (!isset($_SESSION['admin_id'])) {
            http_response_code(403);
            exit;
        }

        $ids = $_POST['ids'] ?? [];
        if (empty($ids)) {
            http_response_code(400);
            echo json_encode(['error' => 'No IDs provided']);
            exit;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("DELETE FROM comments WHERE id IN ({$placeholders})");
        $stmt->execute($ids);

        echo json_encode(['success' => true]);
    }
}
