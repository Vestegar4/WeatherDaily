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
            margin:5px 10px;
        }
        .sidebar a:hover,
        .sidebar a.active {
            background:#0d6efd;
        }

        .content {
            margin-left:260px;
            padding:25px;
        }

        .card {
            border:none;
            border-radius:12px;
            box-shadow:0 4px 10px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h3 class="text-center mb-4">WeatherDaily</h3>
        <a href="../dashboard/index.php">Dashboard</a>
        <a href="../activities/index.php" class="active">Aktivitas</a>
        <a href="../statistik/index.php">Laporan Aktivitas</a>
        <a href="../notifikasi/index.php">
            Notifikasi
            <?php if ($countNotif > 0): ?>
                <span class="badge bg-danger ms-2"><?= $countNotif ?></span>
            <?php endif; ?>
        </a>
        <a href="../auth/logout.php" style="color:#ffdddd;">Logout</a>
    </div>

    <div class="p-3 bg-white shadow-sm d-flex justify-content-between align-items-center"
         style="margin-left:260px;">
        <h4 class="m-0">Tambah Aktivitas Baru</h4>
        <span>👤 <?= htmlspecialchars($_SESSION['user']['name']); ?></span>
    </div>

    <div class="content">
        <div class="card p-4">
            <h5 class="mb-3">Isi form berikut untuk menambahkan aktivitas.</h5>

            <form method="POST" action="store.php">
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
