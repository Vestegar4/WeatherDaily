<?php
session_start();
if (!isset($_SESSION['user'])) { exit(); }

require_once __DIR__ . '/../Models/Activity.php';
require_once __DIR__ . '/../Models/WeatherLog.php';

$type = $_GET['type'] ?? 'activity';
$userId = $_SESSION['user']['id'];
$userCity = $_SESSION['user']['city'] ?? 'Jakarta';

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

if ($type == 'weather') {
    $weatherModel = new WeatherLog();
    
    $data = $weatherModel->getHistoryByCity($userCity, 100);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="Riwayat_Cuaca_' . date('Y-m-d') . '.csv"');

    fputcsv($output, ['No', 'Tanggal', 'Jam', 'Kota', 'Suhu (C)', 'Kelembaban (%)', 'Kec. Angin (m/s)', 'Kondisi'], ';');

    $no = 1;
    foreach ($data as $row) {
        $date = date('d-m-Y', strtotime($row['created_at']));
        $time = date('H:i', strtotime($row['created_at']));
        
        fputcsv($output, [
            $no++, 
            $date,
            $time,
            $userCity,
            $row['temperature'],
            $row['humidity'] ?? '-',   
            $row['wind_speed'] ?? '-',  
            $row['weather_desc'] ?? '-'
        ], ';');
    }

} 
else {
    $activityModel = new Activity();
    $data = $activityModel->getAllByUser($userId);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="Laporan_Aktivitas_' . date('Y-m-d') . '.csv"');

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
}

fclose($output);
exit();
?>