<?php
session_start();
require_once "../../Models/Notification.php";

$notif = new Notification();

if (isset($_POST['id'])) {
    $notif->delete($_POST['id']);
}

header("Location: index.php");
exit();