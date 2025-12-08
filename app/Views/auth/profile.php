<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once "../../../config/database.php";

$db   = new Database();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $city   = trim($_POST['city']);
    $userId = $_SESSION['user']['id'];

    $stmt = $conn->prepare("UPDATE users SET city = ? WHERE id = ?");
    $stmt->bindValue(1, $city);
    $stmt->bindValue(2, $userId);
    $stmt->execute();


    $_SESSION['user']['city'] = $city;

    header("Location: ../dashboard/index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profil User</title>
    <link href="../../../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="content" style="margin-left:260px; padding:25px;">
    <h3>Profil Pengguna</h3>
    <div class="card p-4 mt-3">
        <p><strong>Nama:</strong> <?= $_SESSION['user']['name'] ?></p>
        <p><strong>Email:</strong> <?= $_SESSION['user']['email'] ?></p>

        <hr>

        <h5>Pengaturan Kota</h5>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Kota Domisili</label>
                <input type="text" name="city"
                       value="<?= $_SESSION['user']['city'] ?? 'Jakarta' ?>"
                       class="form-control" required>
            </div>
            <button class="btn btn-primary">Simpan Kota</button>
        </form>
    </div>
</div>

</body>
</html>
