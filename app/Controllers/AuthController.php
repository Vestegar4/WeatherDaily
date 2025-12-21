<?php
session_start();
require_once "../Models/User.php";

$user = new User();
$action = $_GET['action'] ?? '';

if ($action == "register") {
    header("Location: ../Views/auth/login.php");
    exit();
}

if ($action == "login") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = $user->login($email, $password);

    if ($result) {
        if ($result['is_verified'] == 0) {
            $_SESSION['pending_email'] = $email;
            $_SESSION['error'] = "Akun belum verifikasi. Silakan minta kode baru di bawah.";
            
            header("Location: ../Views/auth/verify_otp.php");
            exit();
        }

        $_SESSION['user'] = $result;

        if (isset($result['role']) && $result['role'] === 'admin') {
            header("Location: ../Views/admin/dashboard.php");
        } else {
            header("Location: ../Views/dashboard/index.php");
        }
        exit();

    } else {
        $_SESSION['error'] = "Email atau password salah!";
        header("Location: ../Views/auth/login.php");
        exit();
    }
}

elseif ($action == "change_password") {
    if (!isset($_SESSION['user'])) {
        header("Location: ../Views/auth/login.php");
        exit();
    }

    $id = $_SESSION['user']['id'];
    $oldPass = $_POST['old_password'];
    $newPass = $_POST['new_password'];
    $confirmPass = $_POST['confirm_password'];

    if ($newPass !== $confirmPass) {
        $_SESSION['error'] = "Password baru dan konfirmasi tidak cocok!";
        header("Location: ../Views/auth/profile.php");
        exit();
    }

    $currentHash = $user->getPasswordById($id);
    if (!password_verify($oldPass, $currentHash)) {
        $_SESSION['error'] = "Password lama salah!";
        header("Location: ../Views/auth/profile.php");
        exit();
    }

    $newHash = password_hash($newPass, PASSWORD_DEFAULT);
    if ($user->updatePassword($id, $newHash)) {
        $_SESSION['success'] = "Password berhasil diubah!";
    } else {
        $_SESSION['error'] = "Gagal mengubah password (Database Error).";
    }
    
    header("Location: ../Views/auth/profile.php");
    exit();
}
?>