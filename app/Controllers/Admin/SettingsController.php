<?php

namespace App\Controllers\Admin;

use PDO;

class SettingsController
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

        $stmt = $this->db->query("SELECT * FROM site_settings ORDER BY category, setting_key");
        $settings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Group by category
        $groupedSettings = [];
        foreach ($settings as $setting) {
            $groupedSettings[$setting['category']][] = $setting;
        }

        include __DIR__ . '/../../../views/admin/settings.php';
    }

    public function update(): void
    {
        if (!isset($_SESSION['admin_id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $updates = $_POST['settings'] ?? [];
        
        foreach ($updates as $key => $value) {
            $stmt = $this->db->prepare("
                INSERT INTO site_settings (setting_key, setting_value, updated_at) 
                VALUES (?, ?, NOW())
                ON CONFLICT (setting_key) DO UPDATE SET setting_value = EXCLUDED.setting_value, updated_at = NOW()
            ");
            $stmt->execute([$key, $value]);
        }

        // Log activity
        $stmt = $this->db->prepare("INSERT INTO admin_activity_log (admin_id, action_type, action_detail, ip_address) VALUES (?, 'update_settings', 'Updated site settings', ?)");
        $stmt->execute([$_SESSION['admin_id'], $_SERVER['REMOTE_ADDR']]);

        echo json_encode(['success' => true]);
    }
}
