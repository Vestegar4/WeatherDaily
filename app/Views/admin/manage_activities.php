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
    
    $del = $conn->prepare("DELETE FROM activities WHERE id = ?");
    $del->execute([$id]);
    
    echo "<script>alert('Aktivitas berhasil dihapus oleh Admin!'); window.location='manage_activities.php';</script>";
}

$sql = "SELECT activities.*, users.name as user_name, users.email 
        FROM activities 
        JOIN users ON activities.user_id = users.id 
        ORDER BY activities.created_at DESC";

$stmt = $conn->query($sql);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Semua Aktivitas - Admin</title>
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
        <a href="dashboard.php" class="text-white d-block p-2 text-decoration-none">Kembali</a>
    </div>

    <div class="p-4 flex-grow-1">
        <h3 class="fw-bold mb-4">Monitoring Aktivitas User</h3>
        <p class="text-muted">Pantau semua kegiatan yang dibuat pengguna. Hapus jika melanggar aturan.</p>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Tanggal</th>
                                <th>User (Pemilik)</th>
                                <th>Detail Aktivitas</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($activities) > 0): ?>
                                <?php foreach($activities as $row): ?>
                                <tr>
                                    <td style="min-width: 100px;">
                                        <small class="fw-bold"><?= date('d M Y', strtotime($row['activity_date'])) ?></small><br>
                                        <small class="text-muted"><?= $row['time'] ?></small>
                                    </td>

                                    <td>
                                        <span class="fw-bold text-primary"><?= htmlspecialchars($row['user_name']) ?></span><br>
                                        <small class="text-muted" style="font-size: 0.8rem;"><?= htmlspecialchars($row['email']) ?></small>
                                    </td>

                                    <td>
                                        <span class="fw-bold"><?= htmlspecialchars($row['title']) ?></span><br>
                                        <small class="text-muted"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($row['city']) ?></small>
                                    </td>

                                    <td>
                                        <?php if($row['status'] == 'done'): ?>
                                            <span class="badge bg-success rounded-pill">Selesai</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary rounded-pill">Rencana</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <a href="?delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-danger shadow-sm"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus aktivitas milik user ini?');"
                                           title="Hapus Aktivitas (Moderasi)">
                                            <i class="bi bi-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada aktivitas apapun di database.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>