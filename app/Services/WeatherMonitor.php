<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/Notification.php';
require_once __DIR__ . '/MailService.php'; 

class WeatherMonitor {
    private $conn;
    private $notificationModel;
    private $mailService;
    private $apiKey;

    public function __construct() {
        date_default_timezone_set('Asia/Jakarta');

        $db = new Database();
        $this->conn = $db->getConnection();
        $this->notificationModel = new Notification();
        $this->mailService = new MailService(); 
        $this->apiKey = $_ENV['API_WEATHER_KEY'] ?? ''; 
    }
    public function checkDailyRecommendation($user_id) {
        $sql = "SELECT city, email, name FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || empty($user['city'])) return;

        $city = $user['city'];
        
        $currentHour = (int) date('H'); 
        $timeBlock = "";

        if ($currentHour >= 5 && $currentHour < 10) $timeBlock = "Pagi";
        elseif ($currentHour >= 10 && $currentHour < 15) $timeBlock = "Siang";
        elseif ($currentHour >= 15 && $currentHour < 18) $timeBlock = "Sore";
        else $timeBlock = "Malam";

        $tagUnik = "[Info $timeBlock]";

        if ($this->isNotificationExists($user_id, $tagUnik)) {
             return;
        }

        $weather = $this->getWeatherForecastFromAPI($city);
        $main = strtolower($weather['main']);
        $temp = $weather['temp'];
        
        $saran = "Nikmati waktu istirahatmu.";
        $isUrgent = false;

        if (strpos($main, 'rain') !== false) {
            $saran = "☔ <b>Hujan Turun:</b> Sedia payung jika ingin pergi keluar.";
            $isUrgent = true;
        } elseif (strpos($main, 'thunder') !== false) {
            $saran = "⚡ <b>BAHAYA:</b> Badai petir sedang terjadi.";
            $isUrgent = true;
        } else {
            if ($timeBlock == "Pagi") {
                $saran = "🌅 <b>Selamat Pagi:</b> Cuaca $main {$temp}°C.";
            } elseif ($timeBlock == "Siang") {
                if ($temp > 32 || strpos($main, 'clear') !== false) {
                    $saran = "☀️ <b>Terik Siang:</b> Panas {$temp}°C. Gunakan sunscreen.";
                    $isUrgent = true;
                } else {
                        $saran = "🏢 <b>Siang Hari:</b> Cuaca $main {$temp}°C.";
                }
            } elseif ($timeBlock == "Sore") {
                $saran = "🌆 <b>Sore Hari:</b> Langit $main {$temp}°C.";
            } elseif ($timeBlock == "Malam") {
                if ($temp < 22) {
                    $saran = "🧣 <b>Malam Dingin:</b> Suhu {$temp}°C. Pakai jaket jika keluar.";
                    $isUrgent = true;
                } else {
                    $saran = "🌙 <b>Malam Hari:</b> Istirahat yang cukup.";
                }
            }
        }

        $finalMessage = "$tagUnik $saran"; 
        
        $this->notificationModel->create($user_id, $city, strip_tags($finalMessage));

        if ($isUrgent && !empty($user['email'])) {
            $this->mailService->send($user['email'], $user['name'], "⚠️ Info $timeBlock: $city", $saran);
        }
    }

    public function checkUserActivities($user_id) {
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $sql = "SELECT * FROM activities WHERE user_id = ? AND activity_date = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id, $tomorrow]);
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($activities as $activity) {
            $this->analyzeActivityWeather($user_id, $activity);
        }
    }

    private function analyzeActivityWeather($user_id, $activity) {
        $stmtUser = $this->conn->prepare("SELECT email, name FROM users WHERE id = ?");
        $stmtUser->execute([$user_id]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        $city = $activity['city'];
        $title = $activity['title'];
        $timeString = $activity['time'];
        
        $forecast = $this->getWeatherForecastFromAPI($city); 
        $main = strtolower($forecast['main']);
        $temp = $forecast['temp'];
        $jam = (int) substr($timeString, 0, 2);

        $pesan = "";
        $isBahaya = false;

        if (strpos($main, 'rain') !== false) {
            $pesan = "🌧️ <b>Hujan:</b> Kegiatan '$title' jam $timeString besok berpotensi basah.";
            $isBahaya = true;
        } elseif (strpos($main, 'thunder') !== false) {
            $pesan = "⚡ <b>Badai:</b> Batalkan kegiatan '$title' besok!";
            $isBahaya = true;
        } elseif ($jam >= 11 && $jam <= 14 && $temp > 32) {
            $pesan = "🔥 <b>Panas:</b> Jadwal '$title' besok di jam terik {$temp}°C.";
            $isBahaya = true;
        }

        if ($isBahaya && !empty($pesan)) {
            $tagAktivitas = "[Alert: $title]";
            
            if (!$this->isNotificationExists($user_id, $tagAktivitas)) {
                $this->notificationModel->create($user_id, $city, "$tagAktivitas " . strip_tags($pesan));
                
                if (!empty($user['email'])) {
                    $this->mailService->send($user['email'], $user['name'], "⚠️ Alert Jadwal: $title", $pesan);
                }
            }
        }
    }

    private function isNotificationExists($user_id, $tagUnik) {
        $today = date('Y-m-d');

        $sql = "SELECT COUNT(*) FROM notifications 
                WHERE user_id = ? 
                AND message LIKE ? 
                AND DATE(created_at) = ?";
        
        $search = $tagUnik . "%"; 
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id, $search, $today]);
        
        return $stmt->fetchColumn() > 0;
    }

    public function getWeatherForecastFromAPI($city) {
        $cacheFile = __DIR__ . '/../../cache/weather_' . md5(strtolower($city)) . '.json';
        $cacheTime = 3600; 

        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
            $cachedData = json_decode(file_get_contents($cacheFile), true);
            if (isset($cachedData['main'])) return $cachedData;
        }

        $apiKey = $this->apiKey; 
        if(empty($apiKey)) return null;
        
        $url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city) . "&units=metric&lang=id&appid=" . $apiKey;
        $opts = ["ssl" => ["verify_peer"=>false, "verify_peer_name"=>false]];
        
        $res = @file_get_contents($url, false, stream_context_create($opts));
        
        if ($res) {
            $data = json_decode($res, true);
            
            if (isset($data['cod']) && $data['cod'] != 200) {
                return null;
            }

            $formattedData = [
                'main' => strtolower($data['weather'][0]['main']),
                'desc' => $data['weather'][0]['description'],
                'temp' => round($data['main']['temp']),
                'city_name' => $data['name']
            ];

            if (!is_dir(dirname($cacheFile))) mkdir(dirname($cacheFile), 0777, true);
            file_put_contents($cacheFile, json_encode($formattedData));

            return $formattedData;
        }

        return null; 
    }
}
?>