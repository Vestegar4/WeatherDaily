<?php
require_once __DIR__ . "/../../Models/Activity.php";

// Pastikan ada ID yang dikirim
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $status = 'done'; // Atau ambil dari parameter jika dinamis

    $activityModel = new Activity();
    
    // GUNAKAN FUNGSI BARU INI:
    $activityModel->updateStatus($id, $status);
}

// Kembalikan ke halaman index
header("Location: index.php");
exit();
?>