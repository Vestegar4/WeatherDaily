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
        .bg-shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            z-index: 0;
        }
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
            max-width: 420px;
            position: relative;
            z-index: 1;
            animation: fadeIn Up 0.8s ease;
        }

        .form-control { 
            border-radius: 10px; 
            padding: 12px 15px; 
            background: #f8f9fa; 
            border: 1px solid #eee; 
        }
    </style>
</head>
<body>

<div class="bg-shape shape-1"></div>
<div class="bg-shape shape-2"></div>

<div class="register-card">
    <div class="text-center mb-4">
        <h3 class="fw-bold">Register Account</h3>
        <p class="text-muted small">Bergabunglah untuk mengatur harimu lebih baik.</p>

        <form action="../../Controllers/AuthController.php?action=register" method="POST">
            <div class="mb-3">
                <label class="form-label small text-muted fw-bold">Nama Lengkap</label>
                <input type="text" name="name" class="form-control" placeholder="Masukkan nama lengkap anda" required>
            </div>

            <div class="mb-3">
                <label class="form-label small text-muted fw-bold">Email</label>
                <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
            </div>

            <div class="row mb-4">
                <div class="col-md-6 mb-3 mb-md-0">
                    <label class="form-label small text-muted fw-bold">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="buat password" required>
                </div>
            </div>

            <button class="btn btn-primary w-100 mb-3 shadow-sm" type="submit">Register</button>

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
