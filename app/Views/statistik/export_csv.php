<?php
session_start();

require_once "../../../config/database.php";
require_once "../../Models/WeatherLog.php";

$filename = "weatherdaily_report_" . date('Y-m-d_H-i-s') . ".csv";

header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=$filename");

$output = fopen("php://output", "w");

fputcsv($output, ["Kota", "Suhu (°C)", "Kelembaban", "Angin (m/s)", "Kondisi", "Tanggal Input"]);

$weather = new WeatherLog();
$data = $weather->getTemperatureHistory($_SESSION['user']['id']);

foreach ($data as $row) {
    fputcsv($output, [
        $row['city'],
        $row['temperature'],
        $row['humidity'],
        $row['wind_speed'],
        $row['weather_main'],
        $row['created_at']
    ]);
}

fclose($output);
exit;
