<?php
session_start();
require_once __DIR__ . "/../../../config/database.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
$totalUser = $stmt->fetchColumn();

$stmt = $conn->query("SELECT COUNT(*) FROM activities");
$totalActivity = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - WeatherDaily</title>
    <link href="../../../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../node_modules/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        body {
             background: #f4f6f9; 
        }

        .sidebar { 
            min-height: 100vh; 
            background: #343a40; 
            color: white; 
        }
        .sidebar a {
             color: #cfd8dc; 
             text-decoration: none; 
             display: block; 
             padding: 10px 20px; 
        }
        .sidebar a:hover, .sidebar a.active { 
            background: #495057; 
            color: white; 
        }
        .card-stat { 
            border: none; 
            border-radius: 10px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); 
        }
    </style>
</head>
<body>

<div class="d-flex">
    <div class="sidebar p-3" style="width: 250px;">
        <h4 class="mb-4 text-center fw-bold">Admin Panel</h4>
        <a href="dashboard.php" class="active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
        <a href="manage_users.php"><i class="bi bi-people me-2"></i> Kelola User</a>
        <a href="manage_activities.php"><i class="bi bi-list-check me-2"></i> Semua Aktivitas</a>
        <hr>
        <a href="../auth/logout.php" class="text-danger"><i class="bi bi-box-arrow-left me-2"></i> Logout</a>
    </div>

    <div class="flex-grow-1 p-4">
        <h2 class="fw-bold mb-4">Selamat Datang, <?= htmlspecialchars($_SESSION['user']['name']) ?>!</h2>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card card-stat bg-primary text-white p-3">
                    <h3><?= $totalUser ?></h3>
                    <p class="mb-0">Total Pengguna</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stat bg-success text-white p-3">
                    <h3><?= $totalActivity ?></h3>
                    <p class="mb-0">Total Aktivitas Tersimpan</p>
                </div>
            </div>
        </div>

        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i> 
            Halo Admin! Di sini Anda memiliki kontrol penuh. Harap berhati-hati saat menghapus data user.
        </div>
    </div>
</div>

</body>
</html>