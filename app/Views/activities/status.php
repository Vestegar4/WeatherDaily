<?php
session_start();
require_once __DIR__ . "/../../Models/Activity.php";

// 1. Cek Login
if (!isset($_SESSION['user'])) { header("Location: ../auth/login.php"); exit(); }

// 2. Cek apakah ada ID dan Status di URL
if (isset($_GET['id']) && isset($_GET['s'])) {
    $id = $_GET['id'];
    $status = $_GET['s']; // 'done' atau 'planned'

    // 3. Panggil fungsi updateStatus yang baru
    $activityModel = new Activity();
    $result = $activityModel->updateStatus($id, $status);

    if ($result) {
        // Berhasil
        header("Location: index.php");
    } else {
        echo "Gagal update status.";
    }
} else {
    // Jika data tidak lengkap
    header("Location: index.php");
}
exit();
?>