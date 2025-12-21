<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once "../../Models/Notification.php";
require_once "../../Services/WeatherApiClient.php";
require_once "../../Services/WeatherMonitor.php";

$notifModel = new Notification();
$userId = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = $_POST['delete_id'];
    $notifModel->delete($deleteId);
    header("Location: index.php");
    exit();
}

$notifications = $notifModel->getByUser($userId);
$countNotif = $notifModel->countUnread($userId);

$api = new WeatherApiClient();
$monitor = new WeatherMonitor();

$homeCity = $_SESSION['user']['city'] ?? 'Jakarta';
$weather = $api->getWeatherByCity($homeCity);
$rekomendasi = [];

if ($weather && isset($weather['cod']) && $weather['cod'] == 200) {
    $rekomendasi = $monitor->getSmartRecommendation(
        $weather['main']['temp'],
        $weather['weather'][0]['main'],
        $weather['main']['humidity']
    );
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusat Notifikasi - WeatherDaily</title>
    
    <link href="../../../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../node_modules/bootstrap-icons/font/bootstrap-icons.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        body { background:#f4f7fc; font-family:'Poppins', sans-serif; }

        .sidebar {
            height: 100vh; width: 250px; position: fixed; left: 0; top: 0;
            background: #084bb8; color: #fff; padding-top: 20px; z-index: 1000;
        }
        .sidebar a {
            display: block; padding: 12px 20px; color: #dbeafe; text-decoration: none;
            font-size: 16px; margin: 5px 15px; border-radius: 8px; transition: 0.3s;
        }
        .sidebar a:hover, .sidebar a.active { background: #0d6efd; color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        
        .content { margin-left:240px; padding:10px; }

        .notif-card {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white; border-radius: 20px; border: none;
            box-shadow: 0 10px 30px rgba(13, 110, 253, 0.2);
            margin-bottom: 30px; position: relative; overflow: hidden;
        }

        .notif-card::after {
            content: ''; position: absolute; top: -50px; right: -50px;
            width: 200px; height: 200px; background: rgba(255,255,255,0.08);
            border-radius: 50%; pointer-events: none;
        }

        .notif-list li { margin-bottom: 10px; display: flex; align-items: start; font-size: 0.95rem; }
        
        .history-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px; padding: 12px; margin-bottom: 10px;
            border-left: 4px solid #ffc107;
            display: flex; align-items: center; justify-content: space-between;
            transition: 0.2s;
        }
        .history-item:hover { background: rgba(255, 255, 255, 0.2); }
        .history-text { font-size: 0.85rem; color: #e0e0e0; margin-bottom: 0; }
        .history-time { font-size: 0.7rem; opacity: 0.7; display: block; margin-top: 2px;}

        .btn-delete-sm {
            background: transparent; border: none; color: #ffadad; 
            padding: 5px; border-radius: 50%; transition: 0.2s;
        }
        .btn-delete-sm:hover { color: #ff5f5f; background: rgba(255,0,0,0.1); }
    </style>
</head>

<body>

<div class="sidebar">
    <h3 class="text-center mb-4">WeatherDaily</h3>
    <a href="../dashboard/index.php">Dashboard</a>
    <a href="../activities/index.php">Aktivitas</a>
    <a href="../statistik/index.php">Laporan Aktivitas</a>
    
    <a href="index.php" class="active d-flex justify-content-between align-items-center">
        Notifikasi
        <?php if ($countNotif > 0): ?>
            <span class="badge bg-danger rounded-pill" style="font-size: 0.7rem;"><?= $countNotif ?></span>
        <?php endif; ?>
    </a>

    <a href="../auth/logout.php" style="color: #ffdddd">Logout</a>
</div>

<div class="p-3 bg-white shadow-sm d-flex justify-content-between align-items-center" style="margin-left:245px;">
    <h4 class="m-0 fw-bold text-dark"><i class="bi bi-bell-fill text-primary"></i> Pusat Informasi</h4>
    <a href="../auth/profile.php" class="text-decoration-none fw-bold">
        <?= $_SESSION['user']['name']; ?> 
        <i class="bi bi-person-circle text-primary" style="font-size:1.5rem; margin-left: 8px;"></i>
    </a>
</div>

<div class="content">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <div class="card notif-card p-4">
                
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-white text-primary rounded-circle p-2 me-3 shadow-sm" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-stars fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0">Rekomendasi</h5>
                                <small class="opacity-75">Saran aktivitas & outfit hari ini di <?= htmlspecialchars($homeCity) ?></small>
                            </div>
                        </div>

                        <?php if (!empty($rekomendasi)): ?>
                            <div class="bg-white bg-opacity-10 p-3 rounded-3">
                                <ul class="list-unstyled mb-0 notif-list">
                                    <li>
                                        <i class="bi bi-person-standing-dress text-warning me-2 fs-5"></i> 
                                        <span><?= $rekomendasi['outfit'] ?></span>
                                    </li>
                                    <?php if(isset($rekomendasi['gear'])): ?>
                                        <li class="fw-bold text-warning">
                                            <i class="bi bi-umbrella-fill me-2 fs-5"></i> 
                                            <span><?= $rekomendasi['gear'] ?></span>
                                        </li>
                                    <?php endif; ?>
                                    <li>
                                        <i class="bi bi-heart-pulse-fill text-danger me-2 fs-5"></i> 
                                        <span><?= $rekomendasi['health'] ?></span>
                                    </li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <p class="small text-white opacity-50">Data cuaca tidak tersedia.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <hr class="border-white opacity-25 my-2">

                <div class="mt-3">
                    <h6 class="fw-bold mb-3 opacity-75">
                        <i class="bi bi-clock-history me-2"></i>Riwayat Peringatan (Suhu & Cuaca)
                    </h6>

                    <?php if (empty($notifications)): ?>
                        <div class="text-center py-4 bg-white bg-opacity-10 rounded-3">
                            <i class="bi bi-inbox fs-3 opacity-50"></i>
                            <p class="small mb-0 mt-2 opacity-75">Belum ada riwayat peringatan.</p>
                        </div>
                    <?php else: ?>
                        <div class="history-list" style="max-height: 400px; overflow-y: auto; padding-right: 5px;">
                            <?php foreach ($notifications as $n): ?>
                                <div class="history-item">
                                    <div class="d-flex align-items-center overflow-hidden">
                                        <div class="me-3 fs-4 text-warning">
                                            <?php if (stripos($n['message'], 'hujan') !== false): ?>
                                                <i class="bi bi-cloud-drizzle"></i>
                                            <?php elseif (stripos($n['message'], 'panas') !== false): ?>
                                                <i class="bi bi-brightness-high"></i>
                                            <?php else: ?>
                                                <i class="bi bi-exclamation-circle"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow-1" style="min-width: 0;">
                                            <p class="history-text text-truncate">
                                                <?= $n['message'] ?>
                                            </p>
                                            <span class="history-time">
                                                <?= htmlspecialchars($n['city']) ?> • <?= date('d M Y, H:i', strtotime($n['created_at'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <form method="POST" action="index.php" class="ms-2">
                                        <input type="hidden" name="delete_id" value="<?= $n['id']; ?>">
                                        <button type="submit" class="btn-delete-sm" title="Hapus" onclick="return confirm('Hapus item ini?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                </div>
            
        </div>
    </div>
</div>

<script src="../../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>