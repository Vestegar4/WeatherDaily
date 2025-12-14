<?php
require_once __DIR__ . '/../Models/Activity.php';
session_start();

if (!isset($_SESSION['user'])) { exit(); }

$activityModel = new Activity();
$data = $activityModel->getAllByUser($_SESSION['user']['id']);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="Laporan_Aktivitas_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

fputcsv($output, ['No', 'Judul Aktivitas', 'Kategori', 'Kota', 'Tanggal', 'Waktu', 'Status'], ';');

$no = 1;
foreach ($data as $row) {
    $statusIndo = ($row['status'] == 'done') ? 'Selesai' : 'Rencana';
    
    $tanggalIndo = date('d-m-Y', strtotime($row['activity_date']));

    fputcsv($output, [
        $no++, 
        $row['title'], 
        $row['category_name'] ?? $row['category_id'] ?? '-',
        $row['city'], 
        $tanggalIndo, 
        $row['time'], 
        $statusIndo
    ], ';');
}

fclose($output);
exit();
?>