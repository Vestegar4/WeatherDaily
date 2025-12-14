<?php
session_start();
require_once __DIR__ . "/../../../config/database.php";
require_once __DIR__ . "/../../Services/MailService.php";

if (!isset($_SESSION['pending_email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['pending_email'];
$db = new Database();
$conn = $db->getConnection();
$msg_success = "";
$msg_error = "";

if (isset($_POST['resend_otp'])) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $new_otp = rand(100000, 999999);
        $new_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        $update = $conn->prepare("UPDATE users SET otp_code = ?, otp_expiry = ? WHERE id = ?");
        $update->execute([$new_otp, $new_expiry, $user['id']]);

        $mailService = new MailService();
        $subject = "Kode Verifikasi Baru";
        $message = "Halo " . $user['name'] . ",<br>Kode OTP baru Anda: <b>$new_otp</b>";
        
        if ($mailService->send($email, $user['name'], $subject, $message)) {
            $msg_success = "Kode OTP baru berhasil dikirim ke email!";
        } else {
            $msg_error = "Gagal mengirim email. Cek koneksi internet.";
        }
    }
}

if (isset($_POST['submit_otp'])) {
    $input_otp = $_POST['otp'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND is_verified = 0");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if ($user['otp_code'] == $input_otp) {
            if (strtotime($user['otp_expiry']) >= time()) {
                $update = $conn->prepare("UPDATE users SET is_verified = 1, otp_code = NULL, otp_expiry = NULL WHERE id = ?");
                $update->execute([$user['id']]);

                $_SESSION['user'] = $user;
                unset($_SESSION['pending_email']);
                header("Location: ../dashboard/index.php");
                exit();
            } else {
                $msg_error = "Kode sudah kadaluarsa. Silakan minta kode baru.";
            }
        } else {
            $msg_error = "Kode OTP salah!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP</title>
    <link href="../../../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        body { background: linear-gradient(135deg, #0d6efd 0%, #0099ff 100%); height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Poppins', sans-serif; }
        .otp-card { background: rgba(255, 255, 255, 0.95); border-radius: 20px; padding: 40px; width: 100%; max-width: 400px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); text-align: center; }
        .form-control { letter-spacing: 5px; font-size: 24px; text-align: center; }
    </style>
</head>
<body>
    <div class="otp-card">
        <h3 class="fw-bold mb-2">Verifikasi Email</h3>
        <p class="text-muted small mb-4">Email: <strong><?= $email ?></strong></p>
        
        <?php if($msg_error): ?>
            <div class="alert alert-danger py-2 small"><?= $msg_error ?></div>
        <?php endif; ?>
        <?php if($msg_success): ?>
            <div class="alert alert-success py-2 small"><?= $msg_success ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <input type="text" name="otp" class="form-control" maxlength="6" placeholder="000000" required>
            </div>
            <button type="submit" name="submit_otp" class="btn btn-primary w-100 py-2 mb-3">Verifikasi</button>
        </form>
        
        <form method="POST">
            <p class="small text-muted mb-1">Belum menerima kode?</p>
            <button type="submit" name="resend_otp" class="btn btn-link text-decoration-none p-0 small fw-bold">Kirim Ulang Kode</button>
        </form>

        <div class="mt-4 border-top pt-3">
            <a href="login.php" class="text-secondary small text-decoration-none">Kembali ke Login</a>
        </div>
    </div>
</body>
</html>