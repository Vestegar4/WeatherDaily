<?php

if (empty($_ENV['API_WEATHER_KEY']) && file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
    if (class_exists('Dotenv\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->safeLoad();
    }
}

class WeatherApiClient {

    private $apiKey;

    public function __construct() {
        $this->apiKey = $_ENV['API_WEATHER_KEY'] ?? ''; 

        if (empty($this->apiKey)) {
            error_log("PERINGATAN: API_WEATHER_KEY belum diset di file .env!");
        }
    }

    public function getWeatherByCity($city) {
        if (empty($this->apiKey)) return null;

        $city = urlencode($city);
        $apiUrl = "https://api.openweathermap.org/data/2.5/weather?q={$city}&units=metric&lang=id&appid={$this->apiKey}";

        $options = [
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ],
        ];
        
        $response = @file_get_contents($apiUrl, false, stream_context_create($options));
        
        if ($response === FALSE) {
            return null;
        }

        return json_decode($response, true);
    }

    public function getForecastByCity($city) {
        if (empty($this->apiKey)) return null;

        $city = urlencode($city);
        $apiUrl = "https://api.openweathermap.org/data/2.5/forecast?q={$city}&units=metric&lang=id&appid={$this->apiKey}";

        $options = [
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ],
        ];

        $response = @file_get_contents($apiUrl, false, stream_context_create($options));

        if ($response === FALSE) {
            return null;
        }

        return json_decode($response, true);
    }
}