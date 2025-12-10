<?php
require_once __DIR__ . '/../../config/database.php';

class Notification {
    private $conn;
    private $table = 'notifications';

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getByUser($user_id) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUnread($user_id) {
        // Anggap semua notif dalam 24 jam terakhir sebagai "Baru/Unread"
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }

    public function create($user_id, $city, $message) {
        $sql = "INSERT INTO {$this->table} (user_id, city, message, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$user_id, $city, $message]);
    }

    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function countExtreme($user_id) {
        // Menghitung pesan yang mengandung kata kunci cuaca buruk
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE user_id = ? 
                AND (
                    message LIKE '%Hujan%' OR 
                    message LIKE '%Badai%' OR 
                    message LIKE '%Panas%' OR 
                    message LIKE '%Bahaya%' OR 
                    message LIKE '%Waspada%' OR 
                    message LIKE '%Alert%'
                )";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }
}
?>