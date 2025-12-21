<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once "../../Models/ActivityCategory.php";
require_once "../../Models/Notification.php";

$categoryModel = new ActivityCategory();
$categories    = $categoryModel->getAll();

$notifBadge = new Notification();
$countNotif = $notifBadge->countUnread($_SESSION['user']['id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Aktivitas - WeatherDaily</title>
    <link href="../../../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../node_modules/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        body {
            background:#f2f7ff;
            font-family:'Poppins',sans-serif;
        }

        .sidebar {
            height:100vh;
            width:250px;
            position:fixed;
            left:0;
            top:0;
            background:#084bb8;
            color:#fff;
            padding-top:20px;
        }
        .sidebar a {
            display:block;
            padding:12px 20px;
            color:#dbeafe;
            text-decoration:none;
            border-radius:8px;
            margin:5px 15px;
        }
        .sidebar a:hover,
        .sidebar a.active {
            background:#0d6efd;
        }

        .content {
            margin-left:250px;
            padding:30px;
        }

        .card {
            border:none;
            border-radius:12px;
            box-shadow:0 4px 10px rgba(0,0,0,0.08);
            border-left: 5px solid #0d6efd;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h3 class="text-center mb-4">WeatherDaily</h3>
        <a href="../dashboard/index.php">Dashboard</a>
        <a href="../activities/index.php" class="active">Aktivitas</a>
        <a href="../statistik/index.php">Laporan Aktivitas</a>
        <a href="../notifikasi/index.php" class="d-flex justify-content-between align-items-center">
            Notifikasi
            <?php if ($countNotif > 0): ?>
                <span class="badge bg-danger rounded-pill" style="font-size: 0.7rem;"><?= $countNotif ?></span>
            <?php endif; ?>
        </a>
        <a href="../auth/logout.php" style="color:#ffdddd;">Logout</a>
    </div>

    <div class="p-3 bg-white shadow-sm d-flex justify-content-between align-items-center"
         style="margin-left:245px;">
        <h4 class="m-0 fw-bold text-dark">Tambah Aktivitas Baru</h4>
        <a href="../auth/profile.php" class="text-decoration-none fw-bold"><?= $_SESSION['user']['name']; ?>
        <i class="bi bi-person-circle" style="font-size:1.5rem; margin-left: 8px;"></i>
        </a>
    </div>

    <div class="content">
        <div class="card p-4">
            <h5 class="mb-3">Isi form berikut untuk menambahkan aktivitas.</h5>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i> 
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="../../Controllers/ActivityController.php?action=create">

                <div class="mb-3">
                    <label class="form-label">Judul Aktivitas</label>
                    <input type="text" name="title" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id']; ?>">
                                <?= htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kota</label>
                    <input type="text" name="city" class="form-control" required>
                </div>

                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="date" class="form-control" required>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Waktu</label>
                        <input type="time" name="time" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="planned">Belum selesai</option>
                        <option value="done">Selesai</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="index.php" class="btn btn-secondary ms-2">Batal</a>
            </form>
        </div>
    </div>

</body>
</html>