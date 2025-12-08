<?php
require_once __DIR__ . "/../../Models/Activity.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $status = 'done';

    $activityModel = new Activity();
    
    $activityModel->updateStatus($id, $status);
}

header("Location: index.php");
exit();
?>