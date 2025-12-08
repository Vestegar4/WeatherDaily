<?php
session_start();

require_once '../../../config/google_init.php';

$login_url = $client->createAuthUrl();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - WeatherDaily</title>
    <link href="../../../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5 col-md-5">
    <div class="card shadow p-4">
        <h3 class="text-center mb-4">Login</h3>

        <form action="../../Controllers/AuthController.php?action=login" method="POST">
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button class="btn btn-primary w-100" type="submit">Login</button>

            <hr class="my-4">
            <a href="<?= $login_url; ?>" class="btn btn-outline-danger w-100 py-2">
                <i class="bi bi-google me-2"></i> Masuk dengan Google
            </a>

            <p class="text-center mt-3">Belum punya akun? <a href="register.php">Register</a></p>
        </form>
    </div>
</div>

</body>
</html>
