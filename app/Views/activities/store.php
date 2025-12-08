<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once "../../Models/Activity.php";

$activity = new Activity();

$user_id     = $_SESSION['user']['id'];
$category_id = $_POST['category_id'];
$title       = $_POST['title'];
$city        = $_POST['city'];
$date        = $_POST['date'];
$time        = $_POST['time'];
$status      = $_POST['status'];

$activity->create($user_id, $category_id, $title, $city, $date, $time, $status);

header("Location: index.php?success=1");
exit();
