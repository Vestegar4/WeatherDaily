<?php
session_start();

require_once "../Models/Activity.php";

$activity = new Activity();

$action = $_GET['action'] ?? '';

if ($action == "create") {
    $activity->create(
        $_SESSION['user']['id'],
        $_POST['category_id'],
        $_POST['title'],
        $_POST['city'],
        $_POST['date'],
        $_POST['time'],
        $_POST['status'],
    );

    header("Location: ../Views/activities/index.php");
    exit();

} elseif ($action == "delete") {
    $activity->delete($_GET['id']);
    header("Location: ../Views/activities/index.php");
    exit();
}
