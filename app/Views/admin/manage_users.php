<?php
session_start();
require_once __DIR__ . "/../../../config/database.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();

if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $del = $conn->prepare("DELETE FROM users WHERE id = ?");
    $del->execute([$id]);
    echo "<script>alert('User berhasil dihapus!'); window.location='manage_users.php';</script>";
}

if (isset($_GET['verify_id'])) {
    $id = $_GET['verify_id'];
    $up = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
    $up->execute([$id]);
    echo "<script>alert('User berhasil diverifikasi manual!'); window.location='manage_users.php';</script>";
}

$stmt = $conn->query("SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kelola User - Admin</title>
    <link href="../../../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../node_modules/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        body {
             background: #f4f6f9; 
        }

        .sidebar { 
            min-height: 100vh; 
            background: #343a40; 
            color: white; 
        }
        .sidebar a {
             color: #cfd8dc; 
             text-decoration: none; 
             display: block; 
             padding: 10px 20px; 
        }
        .sidebar a:hover, .sidebar a.active { 
            background: #495057; 
            color: white; 
        }
        .card-stat { 
            border: none; 
            border-radius: 10px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); 
        }
    </style>
</head>
<body class="bg-light">

<div class="d-flex">
    <div class="sidebar p-3" style="width: 250px;">
        <h4 class="mb-4 text-center fw-bold">Admin Panel</h4>
        <a href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
        <a href="manage_users.php"><i class="bi bi-people me-2"></i> Kelola User</a>
        <a href="manage_activities.php"><i class="bi bi-list-check me-2"></i> Semua Aktivitas</a>
        <hr>
        <a href="dashboard.php" class="text-white d-block p-2 text-decoration-none"> Kembali</a>
    </div>

    <div class="flex-grow-1 p-4">
        <h3 class="fw-bold mb-4">Manajemen Pengguna</h3>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Kota</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td>
                                <span class="fw-bold"><?= htmlspecialchars($u['name']) ?></span><br>
                                <small class="text-muted">Join: <?= $u['created_at'] ?></small>
                            </td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['city']) ?></td>
                            <td>
                                <?php if($u['is_verified'] == 1): ?>
                                    <span class="badge bg-success">Verified</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Unverified</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($u['is_verified'] == 0): ?>
                                    <a href="?verify_id=<?= $u['id'] ?>" class="btn btn-sm btn-primary" title="Verifikasi Manual">
                                        <i class="bi bi-check-lg"></i>
                                    </a>
                                <?php endif; ?>

                                <a href="?delete_id=<?= $u['id'] ?>" class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Yakin hapus user ini? Semua datanya akan hilang!')" title="Hapus User">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>