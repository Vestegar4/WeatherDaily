<?php
session_start();

require_once __DIR__ . "/../../../config/database.php"; 
require_once __DIR__ . "/../../Services/MailService.php"; 

$db = new Database();
$conn = $db->getConnection();
$conn->query("DELETE FROM users WHERE is_verified = 0 AND created_at < (NOW() - INTERVAL 1 HOUR)");

if (isset($_SESSION['user'])) {
    header("Location: ../dashboard/index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $city = htmlspecialchars($_POST['city']); 
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Password konfirmasi tidak cocok!";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Email sudah terdaftar! Silakan login.";
        } else {
            $otp = rand(100000, 999999);
            $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            $sql = "INSERT INTO users (name, email, city, password, otp_code, otp_expiry, is_verified, created_at) VALUES (?, ?, ?, ?, ?, ?, 0, NOW())";
            $stmt = $conn->prepare($sql);
            
            if ($stmt->execute([$name, $email, $city, $hashed_password, $otp, $expiry])) {
                
                $mailService = new MailService();
                $subject = "Kode Verifikasi Akun - WeatherDaily";
                $message = "Halo $name,<br><br>Kode verifikasi (OTP) Anda adalah: <b>$otp</b>";
                
                if ($mailService->send($email, $name, $subject, $message)) {
                    $_SESSION['pending_email'] = $email;
                    header("Location: verify_otp.php");
                    exit();
                } else {
                    $conn->prepare("DELETE FROM users WHERE email = ?")->execute([$email]);
                    $_SESSION['error'] = "Gagal mengirim email verifikasi. Cek koneksi internet.";
                }

            } else {
                $_SESSION['error'] = "Terjadi kesalahan sistem database.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - WeatherDaily</title>
    <link href="../../../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../node_modules/bootstrap-icons/font/bootstrap-icons.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body {
            background: linear-gradient(135deg, #0d6efd 0%, #0099ff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: Poppins,sans-serif;
            overflow: hidden;
            padding: 20px;
        }
        .bg-shape { position: absolute; border-radius: 50%; background: rgba(255, 255, 255, 0.1); z-index: 0; }
        .shape-1 { width: 300px; height: 300px; top: -50px; left: -50px; }
        .shape-2 { width: 400px; height: 400px; bottom: -100px; right: -100px; }

        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.5);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            position: relative;
            z-index: 1;
            animation: fadeIn Up 0.8s ease;
        }

        .form-control { border-radius: 10px; padding: 12px 15px; background: #f8f9fa; border: 1px solid #eee; }
    </style>
</head>
<body>

<div class="bg-shape shape-1"></div>
<div class="bg-shape shape-2"></div>

<div class="register-card">
    <div class="text-center mb-4">
        <h3 class="fw-bold">Register Account</h3>
        <p class="text-muted small">Bergabunglah untuk mengatur harimu lebih baik.</p>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger py-2 text-center shadow-sm border-0 mb-4" style="font-size: 0.9rem;">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-3 text-start">
                <label class="form-label small text-muted fw-bold">Nama Lengkap</label>
                <input type="text" name="name" class="form-control" placeholder="Masukkan nama lengkap" required>
            </div>

            <div class="mb-3 text-start">
                <label class="form-label small text-muted fw-bold">Email</label>
                <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
            </div>

            <div class="mb-3 text-start">
                <label class="form-label small text-muted fw-bold">Kota Domisili</label>
                <input type="text" name="city" class="form-control" placeholder="Contoh: Jakarta" required>
            </div>

            <div class="row mb-4 text-start">
                <div class="col-md-6 mb-3 mb-md-0">
                    <label class="form-label small text-muted fw-bold">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Buat password" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small text-muted fw-bold">Konfirmasi</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi Pass" required>
                </div>
            </div>

            <button class="btn btn-primary w-100 mb-3 shadow-sm py-2 fw-bold" type="submit">
                Daftar & Kirim OTP <i class="bi bi-arrow-right ms-1"></i>
            </button>

            <div class="text-center mt-3">
                <p class="small text-muted mb-0"> Sudah punya akun?
                    <a href="login.php" class="text-primary fw-bold text-decoration-none">Masuk</a>
                </p>
            </div>
        </form>
    </div>
</div>

</body>
</html>