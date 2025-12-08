<?php
require_once "../Models/User.php";
session_start();

$user = new User();

$action = $_GET['action'] ?? '';

if ($action == "register") {
    $user->register($_POST['name'], $_POST['email'], $_POST['password']);
    header("Location: ../Views/auth/login.php");
    exit();
}

if ($action == "login") {
    $result = $user->login($_POST['email'], $_POST['password']);
    if ($result) {
        $_SESSION['user'] = $result;
        header("Location: ../Views/dashboard/index.php");
        exit();
    } else {
        echo "<script>alert('Email atau password salah!'); window.location.href='../Views/auth/login.php'</script>";
    }
}
