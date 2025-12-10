<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: ../auth/login.php"); exit(); }

require_once "../../Models/Activity.php";
require_once "../../Models/Notification.php";
require_once "../../Services/AnalyticsService.php";

$activityModel = new Activity();
$notifBadge = new Notification();
$analytics = new AnalyticsService();

$countNotif = $notifBadge->countUnread($_SESSION['user']['id']);
$summary = $analytics->getSummary($_SESSION['user']['id']);

$catStats = $activityModel->countByCategory($_SESSION['user']['id']);
$catLabels = [];
$catValues = [];
foreach($catStats as $row) {
    $catLabels[] = ucfirst($row['category']); 
    $catValues[] = $row['total'];
}

$dayStats = $activityModel->countByDay($_SESSION['user']['id']);
$dayLabels = [];
$dayValues = [];
foreach($dayStats as $row) {
    $dayLabels[] = $row['day_name'];
    $dayValues[] = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Aktivitas - WeatherDaily</title>
    
    <link href="../../../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../node_modules/bootstrap-icons/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        body { background: #f4f7fc; font-family:'Poppins', sans-serif; }
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            left: 0;
            top: 0;
            background: #084bb8;
            color: #fff;
            padding-top: 20px; 
            z-index: 1000;
        }

        .sidebar a {
            display: block;
            padding: 12px 20px; 
            color: #dbeafe;
            text-decoration: none;
            font-size: 16px;
            margin: 5px 15px;
        }

        .sidebar a:hover, .sidebar a.active {
            background: #0d6efd; 
        }

        .content { margin-left:260px; padding:25px; }
        
        .card-stat { border:none; border-radius:15px; box-shadow:0 5px 20px rgba(0,0,0,0.05); transition:0.3s; background:#fff; }
        .card-stat:hover { transform:translateY(-2px); }
        .icon-box { width:50px; height:50px; display:flex; align-items:center; justify-content:center; border-radius:12px; font-size:1.5rem; }
    </style>
</head>

<body>

<div class="sidebar">
    <h3 class="text-center mb-4">WeatherDaily</h3>
    
    <a href="../dashboard/index.php">Dashboard</a>
    <a href="../activities/index.php">Aktivitas</a>
    <a href="index.php" class="active">Laporan Aktivitas</a>
    
    <a href="../notifikasi/index.php" class="d-flex justify-content-between align-items-center">
        Notifikasi
        <?php if ($countNotif > 0): ?>
            <span class="badge bg-danger rounded-pill" style="font-size: 0.7rem;"><?= $countNotif ?></span>
        <?php endif; ?>
    </a>
    
    <a href="../auth/logout.php" style="ffdddd">Logout</a>
</div>

<div class="p-3 bg-white shadow-sm d-flex justify-content-between align-items-center" style="margin-left:250px;">
    <h4 class="m-0 fw-bold text-dark">Analisa Data</h4>
    <a href="../auth/profile.php" class="text-decoration-none fw-bold">
        <?= $_SESSION['user']['name']; ?> 
        <i class="bi bi-person-circle text-primary" style="font-size:1.5rem; margin-left: 8px;"></i>
    </a>
</div>

<div class="content">

    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Laporan Aktivitas</h3>
            <p class="text-secondary mb-0">Ringkasan produktivitas & pola kegiatan Anda</p>
        </div>
        
        <a href="../../Controllers/ExportController.php" class="btn btn-success shadow-sm px-4 py-2" style="border-radius: 50px;">
            <i class="bi bi-file-earmark-spreadsheet me-2"></i>Export CSV
        </a>
    </div>
    
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card card-stat p-3 d-flex flex-row align-items-center">
                <div class="icon-box bg-primary bg-opacity-10 text-primary me-3">
                    <i class="bi bi-journal-check"></i>
                </div>
                <div>
                    <h6 class="text-secondary mb-0">Total Aktivitas</h6>
                    <h2 class="fw-bold mb-0"><?= $summary['total_activity']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stat p-3 d-flex flex-row align-items-center">
                <div class="icon-box bg-success bg-opacity-10 text-success me-3">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <h6 class="text-secondary mb-0">Sudah Selesai</h6>
                    <h2 class="fw-bold mb-0"><?= $summary['finished_activity']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stat p-3 d-flex flex-row align-items-center">
                <div class="icon-box bg-warning bg-opacity-10 text-warning me-3">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div>
                    <h6 class="text-secondary mb-0">Peringatan Cuaca</h6>
                    <h2 class="fw-bold mb-0"><?= $summary['extreme_alert']; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        
        <div class="col-lg-5">
            <div class="card card-stat p-4 h-100">
                <h5 class="fw-bold mb-3 text-dark">Jenis Kegiatan</h5>
                <?php if(!empty($catLabels)): ?>
                    <div style="height: 300px; position: relative;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light text-center py-5 text-muted">
                        <i class="bi bi-bar-chart fs-1 opacity-25"></i><br>
                        Belum ada data aktivitas.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card card-stat p-4 h-100">
                <h5 class="fw-bold mb-3 text-dark">Hari Tersibuk Anda</h5>
                <?php if(!empty($dayLabels)): ?>
                    <div style="height: 300px;">
                        <canvas id="dayChart"></canvas>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light text-center py-5 text-muted">
                        <i class="bi bi-calendar-x fs-1 opacity-25"></i><br>
                        Belum ada data aktivitas.
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<script src="../../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const catLabels = <?= json_encode($catLabels); ?>;
    const catValues = <?= json_encode($catValues); ?>;
    
    if (catLabels.length > 0) {
        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{
                    data: catValues,
                    backgroundColor: [
                        '#0d6efd', '#198754', '#ffc107', '#dc3545', '#6610f2', '#0dcaf0'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    const dayLabels = <?= json_encode($dayLabels); ?>;
    const dayValues = <?= json_encode($dayValues); ?>;

    if (dayLabels.length > 0) {
        new Chart(document.getElementById('dayChart'), {
            type: 'bar',
            data: {
                labels: dayLabels,
                datasets: [{
                    label: 'Jumlah Kegiatan',
                    data: dayValues,
                    backgroundColor: '#0d6efd',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
</script>

</body>
</html>