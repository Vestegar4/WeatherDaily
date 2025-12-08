<?php
require_once __DIR__ . '/../Models/Activity.php';
session_start();

// Cek Login
if (!isset($_SESSION['user'])) { exit(); }

$activityModel = new Activity();
$data = $activityModel->getAllByUser($_SESSION['user']['id']);

// Set Header untuk Download CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="laporan_aktivitas.csv"');

$output = fopen('php://output', 'w');

// Header Kolom
fputcsv($output, ['ID', 'Judul', 'Kategori', 'Kota', 'Tanggal', 'Waktu', 'Status']);

// Isi Data
foreach ($data as $row) {
    fputcsv($output, [
        $row['id'], 
        $row['title'], 
        $row['category_name'] ?? $row['category_id'], // Sesuaikan
        $row['city'], 
        $row['activity_date'], 
        $row['time'], 
        $row['status']
    ]);
}
fclose($output);
exit();
?>