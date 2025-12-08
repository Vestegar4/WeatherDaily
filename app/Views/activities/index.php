<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: ../auth/login.php"); exit(); }

require_once "../../Models/Activity.php";
require_once "../../Models/Notification.php";

$activityModel = new Activity();
$notifBadge = new Notification();

// Ambil data aktivitas
$activities = $activityModel->getAllByUser($_SESSION['user']['id']);
$countNotif = $notifBadge->countUnread($_SESSION['user']['id']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivitas - WeatherDaily</title>
    
    <link href="../../../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../node_modules/bootstrap-icons/font/bootstrap-icons.css">
    
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
            border-radius: 8px; 
            transition: 0.3s; 
        }
        .sidebar a:hover, .sidebar a.active { background: #0d6efd;}

        /* --- CONTENT --- */
        .main-content { margin-left:250px; padding:30px; }
        
        /* --- PERBAIKAN LIST ACTIVITY --- */
        .activity-item {
            background: #fff;
            border-radius: 12px;
            padding: 15px 20px; /* Padding lebih ramping */
            margin-bottom: 12px;
            border-left: 5px solid #0d6efd;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02); /* Bayangan lebih tipis */
            transition: all 0.2s ease-in-out;
            
            /* Flexbox Alignment */
            display: flex;
            align-items: center;
        }
        
        /* Efek Hover Diperhalus (Tidak terlalu lompat) */
        .activity-item:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); 
        }
        
        /* KOLOM TANGGAL (FIXED WIDTH - AGAR SEJAJAR) */
        .date-box {
            width: 80px;      /* Lebar Tetap */
            min-width: 80px;  /* Tidak boleh mengecil */
            text-align: center;
            padding-right: 15px;
            border-right: 1px solid #eee;
            margin-right: 20px;
            flex-shrink: 0;   /* Mencegah kolom ini tergencet */
        }
        .date-day { font-size: 1.4rem; font-weight: 700; color: #333; line-height: 1; display: block; }
        .date-month { font-size: 0.75rem; text-transform: uppercase; color: #888; font-weight: 600; letter-spacing: 1px; display: block; }
        .date-time { 
            font-size: 0.75rem; color: #0d6efd; font-weight: 600; margin-top: 6px; 
            background: #e7f1ff; padding: 2px 8px; border-radius: 10px; display: inline-block;
        }
        
        .content-box {
            flex-grow: 1; /* Mengisi ruang sisa */
            min-width: 0; /* Mencegah teks panjang merusak layout */
        }

        .action-box {
            margin-left: auto; /* Memaksa tombol ke paling kanan */
            display: flex;
            gap: 10px;
            flex-shrink: 0;
        }

        .badge-category { background: #f8f9fa; border: 1px solid #eee; color: #666; font-size: 0.75rem; padding: 4px 10px; border-radius: 6px; font-weight: 500;}
        .btn-action { width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; transition: 0.2s; border: 1px solid transparent; background: #fff; }
        .btn-action:hover { background: #f1f3f5; border-color: #dee2e6; }
        
        /* Status Styles */
        .item-done { border-left-color: #198754; opacity: 0.8; background: #fafffc; }
        .item-done .date-time { background: #d1e7dd; color: #0f5132; }
    </style>
</head>

<body>

<div class="sidebar">
    <h3 class="text-center mb-4">WeatherDaily</h3>
    <a href="../dashboard/index.php">Dashboard</a>
    <a href="index.php" class="active">Aktivitas</a>
    <a href="../statistik/index.php">Laporan Aktivitas</a>
    <a href="../notifikasi/index.php" class="d-flex justify-content-between align-items-center">
        Notifikasi <?php if($countNotif>0):?><span class="badge bg-danger rounded-pill" style="font-size:0.7rem"><?=$countNotif?></span><?php endif;?>
    </a>
    <a href="../auth/logout.php" style="color:#ffdddd;">Logout</a>
</div>

<div class="p-3 bg-white shadow-sm d-flex justify-content-between align-items-center" style="margin-left:250px;">
    <h4 class="m-0 fw-bold text-dark">Aktivitas Harian</h4>
    <a href="../auth/profile.php" class="text-decoration-none fw-bold text-dark pe-3">
        <?= $_SESSION['user']['name']; ?> 
        <i class="bi bi-person-circle text-primary" style="font-size:1.5rem; margin-left: 8px;"></i>
    </a>
</div>

<div class="main-content">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Agenda Anda</h3>
            <p class="text-secondary mb-0">Kelola jadwal kegiatan produktifmu</p>
        </div>
        <a href="create.php" class="btn btn-primary shadow-sm rounded-pill px-4 py-2 fw-bold">
            <i class="bi bi-plus-lg me-2"></i>Tambah Baru
        </a>
    </div>

    <div class="row">
        <div class="col-lg-12">
            
            <?php if (count($activities) > 0): ?>
                <?php foreach ($activities as $row): ?>
                    <?php 
                        $dateObj = date_create($row['activity_date']); 
                        $day = date_format($dateObj, "d");
                        $month = date_format($dateObj, "M");
                        $isDone = ($row['status'] == 'done');
                    ?>

                    <div class="activity-item <?= $isDone ? 'item-done' : ''; ?>">
                        
                        <div class="date-box">
                            <span class="date-day"><?= $day; ?></span>
                            <span class="date-month"><?= $month; ?></span>
                            <span class="date-time"><?= substr($row['time'], 0, 5); ?></span>
                        </div>

                        <div class="content-box">
                            <div class="d-flex align-items-center mb-1">
                                <h5 class="fw-bold mb-0 text-dark me-2 <?= $isDone ? 'text-decoration-line-through text-muted' : ''; ?>">
                                    <?= htmlspecialchars($row['title']); ?>
                                </h5>
                                <?php if($isDone): ?>
                                    <span class="badge bg-success rounded-pill" style="font-size: 0.6rem;">Selesai</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="text-secondary small mb-2 d-flex align-items-center">
                                <span class="me-3"><i class="bi bi-geo-alt-fill text-danger me-1"></i> <?= htmlspecialchars($row['city']); ?></span>
                                <span class="badge-category">
                                    <i class="bi bi-tag-fill me-1 opacity-50"></i> 
                                    <?= htmlspecialchars($row['category_name'] ?? $row['category'] ?? 'Umum'); ?>
                                </span>
                            </div>
                        </div>

                        <div class="action-box">
                            <?php if (!$isDone): ?>
                                <a href="status.php?id=<?= $row['id']; ?>&s=done" class="btn-action text-success" title="Tandai Selesai">
                                    <i class="bi bi-check-lg fs-5"></i>
                                </a>
                            <?php else: ?>
                                <a href="status.php?id=<?= $row['id']; ?>&s=planned" class="btn-action text-secondary" title="Batalkan Selesai">
                                    <i class="bi bi-arrow-counterclockwise fs-5"></i>
                                </a>
                            <?php endif; ?>

                            <a href="edit.php?id=<?= $row['id']; ?>" class="btn-action text-primary" title="Edit">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            
                            <a href="../../Controllers/ActivityController.php?action=delete&id=<?= $row['id']; ?>" 
                               class="btn-action text-danger" 
                               onclick="return confirm('Yakin ingin menghapus?');" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>

                    </div>
                <?php endforeach; ?>

            <?php else: ?>
                <div class="text-center py-5">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="120" class="mb-3 opacity-25">
                    <h5 class="text-secondary">Belum ada aktivitas</h5>
                    <p class="small text-muted">Yuk mulai atur jadwal produktifmu hari ini!</p>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script src="../../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>