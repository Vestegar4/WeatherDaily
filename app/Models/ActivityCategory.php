<?php
require_once dirname(__DIR__) . '/../config/database.php';

class ActivityCategory {
    private $conn;
    private $table = "activity_categories";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAll() {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($name) {
        $stmt = $this->conn->prepare("INSERT INTO " . $this->table . " (name) VALUES (?)");
        return $stmt->execute([$name]);
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table . " WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>