<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once "../../../config/database.php";
require_once "../../Models/Notification.php"; 

$db = new Database();
$conn = $db->getConnection();
$notif = new Notification();
$countNotif = $notif->countUnread($_SESSION['user']['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['city'])) {
    $city = trim($_POST['city']);
    $userId = $_SESSION['user']['id'];

    $stmt = $conn->prepare("UPDATE users SET city = ? WHERE id = ?");
    $stmt->execute([$city, $userId]);

    $_SESSION['user']['city'] = $city;
    $_SESSION['success_city'] = "Kota domisili berhasil diperbarui!";
    
    header("Location: profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - WeatherDaily</title>
    
    <link href="../../../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../node_modules/bootstrap-icons/font/bootstrap-icons.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        body { background: #f2f7ff; font-family:'Poppins', sans-serif; }
        
        .sidebar {
            height:100vh; width:250px; position:fixed; left:0; top:0;
            background:#084bb8; color:#fff; padding-top:20px; z-index: 1000;
        }
        .sidebar a {
            display:block; padding:12px 20px; color:#dbeafe; font-size:16px;
            text-decoration:none; border-radius:8px; margin:5px 15px;
            transition: 0.3s;
        }
        .sidebar a:hover { background:#0d6efd; color: #fff; }
        
        .content { margin-left:250px; padding:30px; }
        
        .card-profile {
            border: none; border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            background: #fff; overflow: hidden;
        }
        .profile-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            padding: 30px; text-align: center; color: white;
        }
        .profile-img {
            width: 100px; height: 100px; object-fit: cover;
            border: 4px solid rgba(255,255,255,0.3);
            background: #fff; color: #0d6efd;
            font-size: 3rem; display: flex; align-items: center; justify-content: center;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <h3 class="text-center mb-4">WeatherDaily</h3>
        <a href="../dashboard/index.php">Dashboard</a>
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
        <h4 class="m-0 fw-bold text-dark">
            <i class="bi bi-person-circle text-primary"></i> Pengaturan Akun
        </h4>
        <div class="fw-bold text-secondary"><?= $_SESSION['user']['name']; ?></div>
    </div>

    <div class="content">
        <div class="row g-4">
            
            <div class="col-lg-5">
                <div class="card card-profile h-100">
                    <div class="profile-header">
                        <div class="d-flex justify-content-center mb-3">
                            <div class="profile-img rounded-circle shadow-sm">
                                <i class="bi bi-person-fill"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold mb-0"><?= $_SESSION['user']['name'] ?></h4>
                        <p class="small opacity-75 mb-0"><?= $_SESSION['user']['email'] ?></p>
                    </div>
                    
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-geo-alt-fill"></i> Lokasi Default</h6>
                        
                        <?php if (isset($_SESSION['success_city'])): ?>
                            <div class="alert alert-success alert-dismissible fade show small" role="alert">
                                <i class="bi bi-check-circle me-1"></i> <?= $_SESSION['success_city']; unset($_SESSION['success_city']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="profile.php">
                            <div class="mb-3">
                                <label class="form-label text-secondary small">Kota Domisili</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-building"></i></span>
                                    <input type="text" name="city" 
                                           value="<?= htmlspecialchars($_SESSION['user']['city'] ?? 'Jakarta') ?>" 
                                           class="form-control border-start-0 ps-0" required>
                                </div>
                                <div class="form-text small">Digunakan untuk cuaca default di Dashboard.</div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 shadow-sm">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card card-profile h-100">
                    <div class="card-header bg-white p-4 border-bottom-0 pb-0">
                        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-shield-lock text-warning me-2"></i> Keamanan</h5>
                        <p class="text-muted small">Update password akun Anda secara berkala.</p>
                    </div>
                    
                    <div class="card-body p-4 pt-2">
                        <hr class="mb-4">
                        
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger small">
                                <i class="bi bi-exclamation-circle me-1"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success small">
                                <i class="bi bi-check-circle me-1"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                            </div>
                        <?php endif; ?>

                        <form action="../../Controllers/AuthController.php?action=change_password" method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Password Lama</label>
                                <input type="password" name="old_password" class="form-control" placeholder="••••••••" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">Password Baru</label>
                                    <input type="password" name="new_password" class="form-control" placeholder="Min. 6 karakter" minlength="6" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">Konfirmasi Password</label>
                                    <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password baru" minlength="6" required>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-2">
                                <button type="submit" class="btn btn-warning text-white px-4 shadow-sm">
                                    Ubah Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    <script src="../../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>