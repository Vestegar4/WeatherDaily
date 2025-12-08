<!DOCTYPE html>
<html>
<head>
    <title>Register - WeatherDaily</title>
    <link href="../../../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-gray">

<div class="container mt-5 col-md-5">
    <div class="card shadow p-4">
        <h3 class="text-center mb-4">Register Account</h3>

        <form action="../../Controllers/AuthController.php?action=register" method="POST">
            <div class="mb-3">
                <label>Nama Lengkap</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button class="btn btn-primary w-100" type="submit">Register</button>

            <p class="text-center mt-3">Sudah punya akun? <a href="login.php">Login</a></p>
        </form>
    </div>
</div>

</body>
</html>
