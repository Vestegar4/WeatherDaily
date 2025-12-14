<?php
require_once dirname(__DIR__, 2) . '/config/database.php';
class WeatherLog {
    private $conn;
    private $table = "weather_logs";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getHistoryByCity($city, $limit = 10) {
        $sql = "SELECT * FROM " . $this->table . " 
                WHERE city = ? 
                ORDER BY created_at DESC LIMIT " . (int)$limit;
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$city]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_reverse($result);
    }

    public function getTemperatureHistory($user_id) {
        $sql = "SELECT city, temperature, created_at FROM " . $this->table . " ORDER BY created_at ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function save($city, $temperature, $humidity, $wind_speed, $weather_main, $weather_description) {
        $sql = "INSERT INTO weather_logs (city, temperature, humidity, wind_speed, weather_main, weather_description)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$city, $temperature, $humidity, $wind_speed, $weather_main, $weather_description]);
    }

    public function countAll() {
        $sql = "SELECT COUNT(*) FROM weather_logs";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getAll() {
            $sql = "SELECT city, temperature, humidity, wind_speed, weather_main, weather_description, created_at 
            FROM {$this->table} ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}
