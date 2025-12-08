<?php
require_once __DIR__ . "/../../config/database.php";

class Activity {
    private $conn;
    private $table = "activities";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }
    public function create($user_id, $category_id, $title, $city, $date, $time, $status) {
        $sql = "INSERT INTO " . $this->table . " (user_id, category_id, title, city, activity_date, time, status)
                VALUES (:user_id, :category_id, :title, :city, :activity_date, :time, :status)";
        
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":city", $city);
        $stmt->bindParam(":activity_date", $date);
        $stmt->bindParam(":time", $time);
        $stmt->bindParam(":status", $status);

        return $stmt->execute();
    }

    public function delete($id) {
        $sql = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function countByCategory($user_id) {
        $sql = "SELECT c.name AS category, COUNT(a.id) AS total
                FROM activities a
                JOIN activity_categories c ON a.category_id = c.id
                WHERE a.user_id = :user_id
                GROUP BY c.name";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countByDay($user_id) {
        $sql = "SELECT DAYNAME(activity_date) as day_name, COUNT(*) as total 
                FROM {$this->table} 
                WHERE user_id = ? 
                GROUP BY DAYNAME(activity_date)
                ORDER BY FIELD(day_name, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll($user_id) {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }

    public function countFinished($user_id) {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE user_id = ? AND status = 'done'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }

    public function getAllByUser($user_id) {
        $sql = "SELECT a.*, c.name AS category_name
                FROM activities a
                JOIN activity_categories c ON a.category_id = c.id
                WHERE a.user_id = ?
                ORDER BY a.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $title, $category_id, $city, $date, $time, $status) {
        $sql = "UPDATE {$this->table} 
                SET title = ?, 
                    category_id = ?, 
                    city = ?, 
                    activity_date = ?, 
                    time = ?, 
                    status = ? 
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $title, 
            $category_id, 
            $city, 
            $date, 
            $time, 
            $status, 
            $id
        ]);
    }
    public function updateStatus($id, $status) {
        $sql = "UPDATE {$this->table} SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$status, $id]);
    }
}
?>