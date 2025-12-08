<?php
session_start();

require_once "../../../config/database.php";
require_once "../../Models/Activity.php";

$filename = "weatherdaily_activities_" . date('Y-m-d_H-i-s') . ".csv";

header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=$filename");

$output = fopen("php://output", "w");

fputcsv($output, ["Judul", "Kategori", "Lokasi", "Tanggal", "Waktu", "Status", "Waktu Input"]);

$activity = new Activity();
$data = $activity->getAllByUser($_SESSION['user']['id']);

foreach ($data as $row) {
    fputcsv($output, [
        $row['title'],
        $row['category_name'],
        $row['city'],
        $row['date'],
        $row['time'],
        $row['status'],
        $row['created_at']
    ]);
}

fclose($output);
exit;
