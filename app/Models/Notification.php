<?php
require_once __DIR__ . "/../../config/database.php";

class Notification {
    private $conn;
    private $table = "notifications";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();

        $this->conn->exec("SET NAMES utf8mb4");
    }

    public function create($user_id, $city, $message) {
        $sql = "INSERT INTO {$this->table} (user_id, city, message, is_read) VALUES (?, ?, ?, 0)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$user_id, $city, $message]);
    }

    public function getByUser($user_id) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUnread($user_id) {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE user_id = ? AND is_read = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }

    public function markAllAsRead($user_id) {
        $sql = "UPDATE {$this->table} SET is_read = 1 WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$user_id]);
    }

    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function countExtreme($user_id) {
        $sql = "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND (message LIKE '%⚠%' OR message LIKE '%⛈%'
         OR message LIKE '%❄%' OR message LIKE '%🌧%')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }

}
