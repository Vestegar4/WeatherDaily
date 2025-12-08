<?php
require_once __DIR__ . '/../Models/Activity.php';
session_start();

if (!isset($_SESSION['user'])) { exit(); }

$activityModel = new Activity();
$data = $activityModel->getAllByUser($_SESSION['user']['id']);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="laporan_aktivitas.csv"');

$output = fopen('php://output', 'w');

fputcsv($output, ['ID', 'Judul', 'Kategori', 'Kota', 'Tanggal', 'Waktu', 'Status']);

foreach ($data as $row) {
    fputcsv($output, [
        $row['id'], 
        $row['title'], 
        $row['category_name'] ?? $row['category_id'],
        $row['city'], 
        $row['activity_date'], 
        $row['time'], 
        $row['status']
    ]);
}
fclose($output);
exit();
?>