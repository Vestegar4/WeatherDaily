<?php
session_start();

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once __DIR__ . '/../../Services/WeatherMonitor.php';
require_once __DIR__ . '/../../Services/WeatherApiClient.php';
require_once __DIR__ . '/../../Models/WeatherLog.php';
require_once __DIR__ . '/../../Models/Notification.php';

$api = new WeatherApiClient();
$log = new WeatherLog();
$notif = new Notification();
$monitor = new WeatherMonitor();

if (isset($_SESSION['user']) && isset($_SESSION['user']['id'])) {
    $userId = $_SESSION['user']['id'];

    $monitor->checkUserActivities($userId);
    $monitor->checkDailyRecommendation($userId);
}

$homeCity = $_SESSION['user']['city'] ?? 'Jakarta';
$weatherHome = $api->getWeatherByCity($homeCity);

$forecastData = $api->getForecastByCity($homeCity);
$chartLabels = [];
$chartTemps = [];

if (isset($forecastData['list'])) {
    $processedDates = []; 
    foreach ($forecastData['list'] as $item) {
        $date = date('Y-m-d', $item['dt']);
        $today = date('Y-m-d');
        
        if ($date != $today && !in_array($date, $processedDates)) {
            if (strpos($item['dt_txt'], '12:00') !== false || strpos($item['dt_txt'], '15:00') !== false) {
                $processedDates[] = $date;
                $chartLabels[] = date('l, d M', $item['dt']); // Label Hari
                $chartTemps[] = round($item['main']['temp']); // Data Suhu
            }
        }
    }
}

$weatherSearch = null;
$errorMsg = "";
$searchCity = null;

if (isset($_GET['city']) && !empty($_GET['city'])) {
    $searchCity = htmlspecialchars($_GET['city']);
    $weatherSearch = $api->getWeatherByCity($searchCity);

    if ($weatherSearch && isset($weatherSearch['cod']) && $weatherSearch['cod'] == 200) {
        
        $log->save(
            $searchCity,
            $weatherSearch['main']['temp'],
            $weatherSearch['main']['humidity'],
            $weatherSearch['wind']['speed'],
            $weatherSearch['weather'][0]['main'],
            $weatherSearch['weather'][0]['description']
        );

        $temp = $weatherSearch['main']['temp'];
        $condition = $weatherSearch['weather'][0]['main'];
        $message = null;

        if ($temp > 34) $message = "⚠ Panas Ekstrem di $searchCity ($temp°C)!";
        elseif ($temp < 15) $message = "❄ Sangat Dingin di $searchCity ($temp°C)!";
        elseif (stripos($condition, "Thunder") !== false) $message = "⛈ Badai Petir di $searchCity!";
        elseif (stripos($condition, "Rain") !== false) $message = "🌧 Hujan Deras di $searchCity!";

        if ($message) {
            $notif->create($_SESSION['user']['id'], $searchCity, $message);
        }
    } else {
        $errorMsg = "Maaf, kota '$searchCity' tidak ditemukan. Silakan cek kembali nama kota yang dicari.";
        $weatherSearch = null;
    }
}

$countNotif = $notif->countUnread($_SESSION['user']['id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Cuaca Harian</title>
    
    <link href="../../../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../node_modules/bootstrap-icons/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        body { background: #f2f7ff; font-family:'Poppins', sans-serif; }
        .sidebar {
            height:100vh;
            width:250px; 
            position:fixed; 
            left:0; 
            top:0;
            background:#084bb8; 
            color:#fff; 
            padding-top:20px; 
            z-index: 1000;
        }
        .sidebar a {
            display:block; 
            padding:12px 20px; 
            color:#dbeafe; 
            font-size:16px;
            text-decoration:none; 
            border-radius:8px; 
            margin:5px 15px;
        }
        .sidebar a:hover, .sidebar a.active { 
            background:#0d6efd; 
        }
        .content { margin-left:250px; padding:30px; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(5px); border-radius: 12px;
        }
        .card-custom {
            border-radius: 20px; border:0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <h3 class="text-center mb-4">WeatherDaily</h3>
        <a href="../dashboard/index.php" class="active">Dashboard</a>
        <a href="../activities/index.php">Aktivitas</a>
        <a href="../statistik/index.php">Laporan Aktivitas</a>
        <a href="../notifikasi/index.php" class="d-flex justify-content-between align-items-center">
            Notifikasi
            <?php if ($countNotif > 0): ?>
                <span class="badge bg-danger rounded-pill" style="font-size: 0.7rem;"><?= $countNotif ?></span>
            <?php endif; ?>
        </a>
        <a href="../auth/logout.php" style="color:#ffdddd;">Logout</a>
    </div>

    <div class="p-3 bg-white shadow-sm d-flex justify-content-between align-items-center" style="margin-left:245px;">
        <h4 class="m-0 fw-bold text-dark"><i class="bi bi-cloud-sun-fill text-warning"></i> Cuaca Harian</h4>
        <a href="../auth/profile.php" class="text-decoration-none fw-bold"><?= $_SESSION['user']['name']; ?> 
        <i class="bi bi-person-circle" style="font-size:1.5rem; margin-left: 8px;"></i></a>
    </div>

    <div class="content text-center">
        
        <h4 class="mb-3">Cek Cuaca Kota Lain</h4>
        <form method="GET" action="index.php" class="row g-3 justify-content-center mb-4">
            <div class="col-auto">
                <input type="text" name="city" placeholder="Masukkan nama kota..." class="form-control form-control-lg shadow-sm" required>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary btn-lg shadow-sm"><i class="bi bi-search"></i> Cari</button>
            </div>
            <?php if(isset($_GET['city'])): ?>
                <div class="col-auto">
                    <a href="index.php" class="btn btn-outline-secondary btn-lg">Reset</a>
                </div>
            <?php endif; ?>
        </form>

            <?php if (!empty($errorMsg)): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm w-75 mx-auto mb-4" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i> 
                    <strong>Ups!</strong> <?= $errorMsg ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

        <div class="row g-4">
            <div class="<?= ($weatherSearch) ? 'col-lg-6' : 'col-lg-8 mx-auto'; ?>">
                <h5 class="text-start mb-2 text-secondary text-capitalize"><i class="bi bi-geo-alt-fill text-danger"></i> Lokasi Anda: <?= htmlspecialchars($homeCity); ?></h5>
                
                <?php if ($weatherHome && isset($weatherHome['cod']) && $weatherHome['cod'] == 200): ?>
                <div class="card card-custom text-white h-100" style="background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%); min-height: 260px;">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-start">
                                <h2 class="fw-bold display-4 mb-0"><?= round($weatherHome['main']['temp']); ?>°</h2>
                                <p class="fs-6 mb-0 badge glass-panel text-uppercase mt-2"><?= $weatherHome['weather'][0]['description']; ?></p>
                                <div class="mt-2 small opacity-75">Terasa <?= round($weatherHome['main']['feels_like']); ?>°C</div>
                            </div>
                            <img src="https://openweathermap.org/img/wn/<?= $weatherHome['weather'][0]['icon']; ?>@4x.png" width="110">
                        </div>
                        <div class="row mt-3 text-center glass-panel p-3 mx-1 align-items-center">
                            <div class="col-4 border-end border-light border-opacity-25">
                                <i class="bi bi-droplet mb-1 d-block fs-5"></i><span class="small"><?= $weatherHome['main']['humidity']; ?>%</span>
                            </div>
                            <div class="col-4 border-end border-light border-opacity-25">
                                <i class="bi bi-wind mb-1 d-block fs-5"></i><span class="small"><?= $weatherHome['wind']['speed']; ?> m/s</span>
                            </div>
                            <div class="col-4">
                                <i class="bi bi-speedometer2 mb-1 d-block fs-5"></i><span class="small"><?= $weatherHome['main']['pressure']; ?> hPa</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                    <div class="alert alert-warning">Gagal memuat cuaca lokasi profil.</div>
                <?php endif; ?>
            </div>

            <?php if ($weatherSearch && isset($weatherSearch['cod']) && $weatherSearch['cod'] == 200): ?>
            <div class="col-lg-6">
                <h5 class="text-start mb-2 text-secondary"><i class="bi bi-search text-primary"></i> Hasil: <?= htmlspecialchars($searchCity); ?></h5>
                <div class="card card-custom text-white h-100" style="background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%); min-height: 260px;">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-start">
                                <h2 class="fw-bold display-4 mb-0"><?= round($weatherSearch['main']['temp']); ?>°</h2>
                                <p class="fs-6 mb-0 badge glass-panel text-uppercase mt-2"><?= $weatherSearch['weather'][0]['description']; ?></p>
                                <div class="mt-2 small opacity-75">Terasa <?= round($weatherSearch['main']['feels_like']); ?>°C</div>
                            </div>
                            <img src="https://openweathermap.org/img/wn/<?= $weatherSearch['weather'][0]['icon']; ?>@4x.png" width="110">
                        </div>
                        <div class="row mt-3 text-center glass-panel p-3 mx-1 align-items-center">
                            <div class="col-4 border-end border-light border-opacity-25">
                                <i class="bi bi-droplet-fill mb-1 d-block fs-5"></i><span class="small"><?= $weatherSearch['main']['humidity']; ?>%</span>
                            </div>
                            <div class="col-4 border-end border-light border-opacity-25">
                                <i class="bi bi-wind mb-1 d-block fs-5"></i><span class="small"><?= $weatherSearch['wind']['speed']; ?> m/s</span>
                            </div>
                            <div class="col-4">
                                <i class="bi bi-eye mb-1 d-block fs-5"></i>
                                <span class="small">
                                    <?= isset($weatherSearch['visibility']) ? round($weatherSearch['visibility']/1000, 1) . ' km' : '-'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="row mt-5">
            <div class="col-12">
                <div class="card card-custom bg-white p-4">
                    <h5 class="text-start mb-4 fw-bold text-dark">
                        <i class="bi bi-thermometer-half text-danger"></i> 
                        Statistik Suhu 5 Hari Kedepan Daerah <?= htmlspecialchars($homeCity); ?>
                    </h5>
                    
                    <?php if (!empty($chartLabels)): ?>
                        <div style="height: 300px; width: 100%;">
                            <canvas id="weatherChart"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light text-muted">Data statistik belum tersedia. Pastikan API Key valid dan koneksi internet lancar.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3" id="notifArea" style="z-index: 9999;"></div>
    
    <script src="../../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const labels = <?= json_encode($chartLabels); ?>;
        const temps = <?= json_encode($chartTemps); ?>;

        if (labels.length > 0) {
            const ctx = document.getElementById('weatherChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Suhu (°C)',
                        data: temps,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderWidth: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#0d6efd',
                        pointRadius: 6,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: false, grid: { borderDash: [5, 5] } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }
    </script>

    <script>
    function showBootstrapToast(message) {
        let notifArea = document.getElementById("notifArea");
        let toastElement = document.createElement("div");
        toastElement.className = "toast align-items-center text-white bg-warning border-0 shadow-lg";
        toastElement.innerHTML = `
            <div class="d-flex">
                <div class="toast-body fs-6 fw-bold text-dark"> <i class="bi bi-exclamation-triangle-fill"></i> ${message} </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>`;
        notifArea.appendChild(toastElement);
        const toast = new bootstrap.Toast(toastElement, { delay: 10000 });
        toast.show();
        toastElement.addEventListener("hidden.bs.toast", () => toastElement.remove());
    }

    setInterval(() => {
        fetch("../../Ajax/check_notification.php") 
            .then(res => res.ok ? res.json() : [])
            .then(data => {
                if (data.length > 0) {
                    data.forEach(notif => showBootstrapToast(notif.message));
                }
            })
            .catch(err => console.error("Gagal cek notifikasi"));
    }, 5000);
    </script>

</body>
</html>