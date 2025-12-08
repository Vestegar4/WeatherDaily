<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/Notification.php';

class WeatherMonitor {
    private $conn;
    private $notificationModel;
    private $apiKey;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
        $this->notificationModel = new Notification();
        
        $this->apiKey = $_ENV['OPENWEATHER_API_KEY'] ?? ''; 
    }

    public function checkUserActivities($user_id) {
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        
        $sql = "SELECT * FROM activities WHERE user_id = ? AND activity_date = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id, $tomorrow]);
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($activities as $activity) {
            $this->analyzeWeather($user_id, $activity);
        }
    }

    public function checkDailyRecommendation($user_id) {
        $sql = "SELECT city FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || empty($user['city'])) {
            return;
        }

        $city = $user['city'];
        
        if ($this->isNotificationExists($user_id, "%Rekomendasi Harian%")) {
            return;
        }

        $weather = $this->getWeatherForecastFromAPI($city);
        $condition = strtolower($weather['main']);
        $desc = $weather['desc'];

        $saran = "Nikmati harimu!";

        if (strpos($condition, 'rain') !== false || strpos($condition, 'drizzle') !== false) {
            $saran = "☔ Jangan lupa bawa payung atau jas hujan.";
        } elseif (strpos($condition, 'thunder') !== false) {
            $saran = "⚡ Cuaca buruk, hindari aktivitas di luar ruangan.";
        } elseif (strpos($condition, 'clear') !== false || strpos($condition, 'sun') !== false) {
            $saran = "🧢 Cuaca panas terik, gunakan sunscreen dan topi.";
        } elseif (strpos($condition, 'snow') !== false) {
            $saran = "🧣 Sangat dingin! Pakai jaket tebal.";
        } elseif (strpos($condition, 'cloud') !== false) {
            $saran = "☁️ Cuaca berawan, nyaman untuk jalan santai.";
        }

        $message = "Rekomendasi Harian: Cuaca di $city hari ini $desc. $saran";
        $this->notificationModel->create($user_id, $city, $message);
    }
    
    private function isNotificationExists($user_id, $messagePattern) {
        $sql = "SELECT COUNT(*) FROM notifications 
                WHERE user_id = ? AND message LIKE ? AND DATE(created_at) = CURDATE()";
        $stmt = $this->conn->prepare($sql);
        if (strpos($messagePattern, '%') === false) {
            $stmt->execute([$user_id, $messagePattern]);
        } else {
            $stmt->execute([$user_id, $messagePattern]);
        }
        return $stmt->fetchColumn() > 0;
    }


    private function analyzeWeather($user_id, $activity) {
        $city = $activity['city'];
        
        $forecast = $this->getWeatherForecastFromAPI($city); 
        
        $badWeatherKeywords = ['Rain', 'Thunderstorm', 'Drizzle', 'Snow', 'Extreme'];
        
        $isBadWeather = false;
        $weatherDesc = $forecast['main'];

        foreach ($badWeatherKeywords as $keyword) {
            if (stripos($weatherDesc, $keyword) !== false) {
                $isBadWeather = true;
                break;
            }
        }

        if ($isBadWeather) {
            $message = "⚠️ Peringatan: Aktivitas '{$activity['title']}' besok di $city berpotensi hujan ($weatherDesc). Jangan lupa bawa payung atau pertimbangkan untuk membatalkan.";
            
            if (!$this->isNotificationExists($user_id, $message)) {
                $this->notificationModel->create($user_id, $city, $message);
            }
        }
    }

    private function getWeatherForecastFromAPI($city) {
        return [
            'main' => 'Thunderstorm', 
            'desc' => 'heavy thunderstorm'
        ];
    }
}
?>