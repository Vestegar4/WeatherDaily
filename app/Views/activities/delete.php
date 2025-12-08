<?php
session_start();
require_once "../../Models/Activity.php";

$activity = new Activity();
$activity->delete($_GET['id']);

header("Location: index.php?success=deleted");
exit();
