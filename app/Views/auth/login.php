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
    <link rel="stylesheet" href="../../../node_modules/bootstrap-icons/font/bootstrap-icons.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body{
            background: linear-gradient(135deg, #0d6efd 0%, #0099ff 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: poppins, sans-serif;
            overflow: hidden;
        }

        .bg-shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            z-index: 0;
        }
        .shape-1 { width: 300px; height: 300px; top: -50px; left: -50px; }
        .shape-2 { width: 400px; height: 400px; bottom: -100px; right: -100px; }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.5);
            padding: 40px;
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
            animation: fadeIn Up 0.8s ease;
        }

        .brand-icon {
            font-size: 3rem;
            color: #0d6efd;
            margin-bottom: 10px;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            background: #f8f9fa;
            border: 1px solid #eee;
        }
        .form-control:focus {
            background: #fff;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
            border-color: #0d6efd;
        }

        .btn-primary {
            background: #0d6efd;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: 0.3s;
        }
        .btn-primary:hover {
            background: #0b5ed7;
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }

        .btn-google {
            background: #fff;
            border: 1px solid #ddd;
            color: #555;
            font-weight: 500;
            border-radius: 10px;
            padding: 10px;
            transition: 0.3s;
            position: relative;
        }
        .btn-google:hover {
            background: #f8f9fa;
            border-color: #ccc;
        }
    </style>
</head>
<body>

<div class="bg-shape shape-1"></div>
<div class="bg-shape shape-2"></div>

<div class="login-card">
    <div class="text-center mb-4">
        <div class="brand-icon">
            <i class="bi bi-cloud-sun-fill"></i>
        </div>
        
        <h3 class="fw-bold"> Selamat Datang </h3>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger py-2 text-center shadow-sm border-0 mb-4" role="alert" style="font-size: 0.9rem;">
                <i class="bi bi-exclamation-circle-fill me-1"></i> 
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <p class="text-muted small">Masuk untuk mengelola aktivitas harianmu.</p>
        
        <form action="../../Controllers/AuthController.php?action=login" method="POST">
            <div class="mb-3">
                <label class="form-label small text-muted fw-bold">Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted ps-3"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control border-start-0 ps-2" placeholder="nama@email.com" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label small text-muted fw-bold">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted ps-3"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control border-start-0 ps-2" placeholder="••••••••" required>
                </div>
            </div>

            <button class="btn btn-primary w-100 shadow-sm" type="submit">Login</button>
            
            <div class="text-center position-relative mb-3">
                <hr class="text-muted opacity-25">
                <span class="position-absolute top-50 start-50 translate-middle text-muted small" style="z-index: 2;">atau</span>
            </div>
            
            <hr class="my-4">
            <a href="<?= $login_url; ?>" class="btn btn-google w-100 shadow-sm d-flex align-items-center justify-content-center">
                <i class="bi bi-google me-2"></i> Masuk dengan Google
            </a>
        </form>
        <div class="text-center mb-4">
        <p class="small text-muted mb-0">Belum punya akun? <a href="register.php" class="text-primary fw-bold text-decoration-none">Register</a></p>
        </div>
    </div>
</div>

</body>
</html>