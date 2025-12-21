<?php
require_once __DIR__ . "/../Models/Activity.php";
require_once __DIR__ . "/../Models/WeatherLog.php";
require_once __DIR__ . "/../Models/Notification.php";

class AnalyticsService {

    public function getSummary($user_id) {
        $activity = new Activity();
        $weather = new WeatherLog();
        $notif = new Notification();

        return [
            "total_activity" => $activity->countAll($user_id),
            "finished_activity" => $activity->countFinished($user_id),
            "weather_check" => $weather->countAll(),
            "extreme_alert" => $notif->countExtreme($user_id)
        ];
    }
}