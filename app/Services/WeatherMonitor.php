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
        
        $this->apiKey = $_ENV['OPENWEATHER_API_KEY'] ?? $_ENV['API_WEATHER_KEY'] ?? ''; 
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
        
        if (!$weather) return;

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
                $saran = "🌅 <b>Selamat Pagi:</b> Cuaca $main {$temp}°C. Waktu tepat untuk aktivitas luar.";
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
    
    public function checkActivityDirectly($user_id, $city, $title, $time, $category_id) {
        $activityMock = [
            'city' => $city,
            'title' => $title,
            'time' => $time,
            'activity_date' => date('Y-m-d'),
            'category_id' => $category_id
        ];
        
        if (isset($_POST['date'])) {
            $activityMock['activity_date'] = $_POST['date'];
        }

        $this->analyzeActivityWeather($user_id, $activityMock);
    }

    private function analyzeActivityWeather($user_id, $activity) {
        $stmtUser = $this->conn->prepare("SELECT email, name FROM users WHERE id = ?");
        $stmtUser->execute([$user_id]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        $city = $activity['city'];
        $title = $activity['title'];
        $timeString = $activity['time'];
        $dateString = $activity['activity_date'] ?? date('Y-m-d');
        $catId = $activity['category_id'];
        
        $forecast = $this->getWeatherForecastFromAPI($city, $dateString, $timeString); 
        
        if (!$forecast) return;

        $main = strtolower($forecast['main']);
        $temp = $forecast['temp'];
        $jam = (int) substr($timeString, 0, 2);

        $pesan = "";
        $judulEmail = "";
        $isBahaya = false;

        if (strpos($main, 'rain') !== false || strpos($main, 'drizzle') !== false) {
            
            if ($catId == 2 || $catId == 4) { // Kategori Outdoor
                $judulEmail = "⛔ REKOMENDASI PEMBATALAN: $title";
                $pesan = "🌧️ <b>Hujan Turun!</b> Kegiatan '$title' di $city pada jam $timeString diprediksi hujan ($main). <br>
                          Rekomendasi: <b>BATALKAN</b> atau ganti Indoor.";
            } 
            else {
                $judulEmail = "☔ Siapkan Payung: $title";
                $pesan = "🌧️ <b>Sedia Payung!</b> Pada jam $timeString diprediksi hujan ($main). <br>
                          Jangan lupa bawa payung.";
            }
            $isBahaya = true;

        } 
        elseif (strpos($main, 'thunder') !== false) {
            $judulEmail = "⚡ BAHAYA BADAI: $title";
            $pesan = "⚡ <b>PERINGATAN BADAI!</b> Cuaca ekstrem ($main) terdeteksi di $city pada jam $timeString. <br>
                      Demi keselamatan, harap <b>BATALKAN</b> kegiatan '$title'.";
            $isBahaya = true;
        } 
        elseif ($jam >= 11 && $jam <= 14 && $temp > 33) {
            if ($catId == 2) {
                $judulEmail = "🔥 Bahaya Heatstroke: $title";
                $pesan = "🔥 <b>Panas Ekstrem ({$temp}°C)!</b> Olahraga '$title' di jam segini berbahaya. <br>
                          Saran: Geser ke sore hari.";
                $isBahaya = true;
            }
        }

        if ($isBahaya && !empty($pesan)) {
            $tagAktivitas = "[Alert: $title]";
            
            if (!$this->isNotificationExists($user_id, $tagAktivitas)) {
                $this->notificationModel->create($user_id, $city, "$tagAktivitas " . strip_tags($pesan));
                
                if (!empty($user['email'])) {
                    $this->mailService->send($user['email'], $user['name'], $judulEmail, $pesan);
                }
            }
        }
    }

    private function isNotificationExists($user_id, $tagUnik) {
        $today = date('Y-m-d');
        $sql = "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND message LIKE ? AND DATE(created_at) = ?";
        $search = $tagUnik . "%"; 
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id, $search, $today]);
        return $stmt->fetchColumn() > 0;
    }

    public function getWeatherForecastFromAPI($city, $targetDate = null, $targetTime = null) {
        $apiKey = $this->apiKey; 
        if(empty($apiKey)) return null;
        
        $url = "https://api.openweathermap.org/data/2.5/forecast?q=" . urlencode($city) . "&units=metric&lang=id&appid=" . $apiKey;
        $opts = ["ssl" => ["verify_peer"=>false, "verify_peer_name"=>false]];
        
        $cacheFile = __DIR__ . '/../../cache/forecast_' . md5(strtolower($city)) . '.json';
        
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 3600)) {
            $jsonRes = file_get_contents($cacheFile);
        } else {
            $jsonRes = @file_get_contents($url, false, stream_context_create($opts));
            if ($jsonRes) {
                if (!is_dir(dirname($cacheFile))) mkdir(dirname($cacheFile), 0777, true);
                file_put_contents($cacheFile, $jsonRes);
            }
        }

        if (!$jsonRes) return null;
        
        $data = json_decode($jsonRes, true);
        if (isset($data['cod']) && $data['cod'] != "200") return null;

        $selectedItem = $data['list'][0]; 

        if ($targetDate && $targetTime) {
            $targetTimestamp = strtotime("$targetDate $targetTime");
            $minDiff = PHP_INT_MAX;
            
            foreach ($data['list'] as $item) {
                $itemTime = $item['dt'];
                $diff = abs($itemTime - $targetTimestamp);
                
                if ($diff < $minDiff) {
                    $minDiff = $diff;
                    $selectedItem = $item;
                }
            }
        }

        return [
            'main' => strtolower($selectedItem['weather'][0]['main']),
            'desc' => $selectedItem['weather'][0]['description'],
            'temp' => round($selectedItem['main']['temp']),
            'city_name' => $data['city']['name']
        ];
    }

    public function getSmartRecommendation($temp, $main, $humidity) {
        $recommendations = [];

        if ($temp >= 30) {
            $recommendations['outfit'] = "👕 <b>Panas!</b> Pakai kaos tipis, kacamata hitam, & sunscreen.";
        } elseif ($temp <= 24) {
            $recommendations['outfit'] = "🧥 <b>Agak Dingin.</b> Gunakan jaket ringan atau hoodie.";
        } else {
            $recommendations['outfit'] = "👕 <b>Nyaman.</b> Gunakan pakaian kasual biasa.";
        }
        
        if (strpos(strtolower($main), 'rain') !== false || strpos(strtolower($main), 'drizzle') !== false) {
            $recommendations['gear'] = "☂️ <b>Hujan Turun.</b> Wajib bawa payung atau jas hujan.";
        }

        if ($humidity < 40) {
            $recommendations['health'] = "💧 <b>Udara Kering!</b> Minum lebih banyak air agar tidak dehidrasi.";
        } elseif ($humidity > 85) {
            $recommendations['health'] = "😓 <b>Lembab Tinggi.</b> Hindari aktivitas fisik berat.";
        } else {
            $recommendations['health'] = "✅ <b>Udara Ideal.</b> Bagus untuk beraktivitas fisik.";
        }

        return $recommendations;
    }
}
?>