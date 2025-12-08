<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once "../../Models/Notification.php";

$notifModel = new Notification();
$userId = $_SESSION['user']['id'];

// 1. Handle Hapus Notifikasi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $notifModel->delete($_POST['delete_id']);
    header("Location: index.php");
    exit();
}

// 2. Ambil Data Notifikasi
$notifications = $notifModel->getByUser($userId);
$countNotif = $notifModel->countUnread($userId);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi - WeatherDaily</title>
    
    <link href="../../../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../node_modules/bootstrap-icons/font/bootstrap-icons.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        body { background:#f4f7fc; font-family:'Poppins', sans-serif; }

        /* --- SIDEBAR STYLE --- */
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
            margin:5px 10px;
        }
        .sidebar a:hover, .sidebar a.active { 
            background:#0d6efd;}

        /* --- PERBAIKAN: PAKSA WARNA LOGOUT --- */
        /* Kita gunakan ".sidebar a.link-logout" agar lebih spesifik */
        .sidebar a.link-logout {
            color: #ffaaaa !important; /* Warna Merah Muda */
            margin-top: 60px !important; 
            font-weight: 600; /* Sedikit lebih tebal biar terlihat */
        }
        
        .sidebar a.link-logout:hover {
            background: rgba(220, 53, 69, 0.2) !important; /* Background Merah Transparan */
            color: #ff6b6b !important; /* Merah Lebih Terang saat Hover */
        }

        /* --- CONTENT STYLE --- */
        .content { margin-left:260px; padding:25px; }

        /* --- NOTIF CARD STYLE --- */
        .notif-card {
            background: #fff; border: none; border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 15px;
            transition: transform 0.2s; position: relative; overflow: hidden;
            border-left: 5px solid #ffc107;
        }
        .notif-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.06); }
        
        .notif-icon {
            width: 45px; height: 45px; background: #fff8e1; color: #ffc107;
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%; font-size: 1.2rem; flex-shrink: 0;
        }
        
        .btn-delete {
            width: 30px; height: 30px; border-radius: 50%; border: none;
            background: #fff; color: #dc3545; display: flex; align-items: center; justify-content: center;
            transition: 0.2s;
        }
        .btn-delete:hover { background: #fee2e2; }
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

    <a href="../auth/logout.php" style="color:#ffdddd;">Logout</a>
</div>

<div class="p-3 bg-white shadow-sm d-flex justify-content-between align-items-center" style="margin-left:250px;">
    <h4 class="m-0 fw-bold text-dark">Pusat Notifikasi</h4>
    <a href="../auth/profile.php" class="text-decoration-none fw-bold text-dark">
        <?= $_SESSION['user']['name']; ?> 
        <i class="bi bi-person-circle text-primary" style="font-size:1.5rem; margin-left: 8px;"></i>
    </a>
</div>

<div class="content">
    
    <div class="row justify-content-center">
        <div class="col-lg-9">
            
            <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
                <div>
                    <h5 class="fw-bold mb-0">Peringatan Cuaca</h5>
                    <p class="text-muted small mb-0">Daftar rekomendasi berdasarkan kondisi cuaca terkini</p>
                </div>
            </div>

            <?php if (empty($notifications)): ?>
                <div class="text-center py-5">
                    <div class="bg-white rounded-circle d-inline-flex p-4 shadow-sm mb-3">
                        <i class="bi bi-bell-slash text-secondary fs-1"></i>
                    </div>
                    <h5 class="text-secondary">Tidak ada notifikasi baru</h5>
                    <p class="small text-muted">Semua aman! Nikmati harimu.</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $n): ?>
                    <div class="card notif-card p-3">
                        <div class="d-flex align-items-start">
                            <div class="notif-icon me-3">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($n['city']) ?></h6>
                                    <small class="text-muted" style="font-size: 0.75rem;">
                                        <?= date('d M H:i', strtotime($n['created_at'])) ?>
                                    </small>
                                </div>
                                <p class="mb-0 text-secondary small" style="line-height: 1.5;">
                                    <?= htmlspecialchars($n['message']) ?>
                                </p>
                            </div>
                            <form method="POST" action="index.php" class="ms-3">
                                <input type="hidden" name="delete_id" value="<?= $n['id']; ?>">
                                <button class="btn-delete" title="Hapus Notifikasi" onclick="return confirm('Hapus notifikasi ini?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>
</div>

<script src="../../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>