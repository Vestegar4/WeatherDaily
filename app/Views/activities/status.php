<?php
session_start();
require_once __DIR__ . "/../../Models/Activity.php";

if (!isset($_SESSION['user'])) { header("Location: ../auth/login.php"); exit(); }

if (isset($_GET['id']) && isset($_GET['s'])) {
    $id = $_GET['id'];
    $status = $_GET['s'];

    $activityModel = new Activity();
    $result = $activityModel->updateStatus($id, $status);

    if ($result) {
        header("Location: index.php");
    } else {
        echo "Gagal update status.";
    }
} else {
    header("Location: index.php");
}
exit();
?>