<?php
session_start();

require_once "../Models/Activity.php";
require_once "../Services/WeatherApiClient.php";
require_once "../Services/WeatherMonitor.php";

$activity = new Activity();
$api = new WeatherApiClient();
$monitor = new WeatherMonitor();

$action = $_GET['action'] ?? '';

if ($action == "create") {
    $city = $_POST['city'];

    $checkWeather = $api->getWeatherByCity($city);
    if (!$checkWeather || (isset($checkWeather['cod']) && $checkWeather['cod'] != 200)) {
        $_SESSION['error'] = "Kota '$city' tidak ditemukan! Pastikan ejaan benar.";
        header("Location: ../Views/activities/create.php");
        exit();
    }

    $isCreated = $activity->create(
        $_SESSION['user']['id'],
        $_POST['category_id'],
        $_POST['title'],
        $city,
        $_POST['date'],
        $_POST['time'],
        $_POST['status']
    );

    if ($isCreated) {
        $monitor->checkActivityDirectly(
            $_SESSION['user']['id'],
            $city,
            $_POST['title'],
            $_POST['time'],
            $_POST['category_id']
        );
    }

    header("Location: ../Views/activities/index.php");
    exit();

} 
elseif ($action == "delete") {
    if (isset($_GET['id'])) {
        $activity->delete($_GET['id']);
    }
    header("Location: ../Views/activities/index.php");
    exit();
}
?>