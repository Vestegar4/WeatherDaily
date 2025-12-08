<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: ../auth/login.php"); exit(); }

require_once "../../Models/Activity.php";
$activityModel = new Activity();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];
$data = $activityModel->getById($id);

if (!$data) {
    echo "Data tidak ditemukan!";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $category_id = $_POST['category_id']; 
    $city = $_POST['city'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $status = $_POST['status'];

    $update = $activityModel->update($id, $title, $category_id, $city, $date, $time, $status);

    if ($update) {
        header("Location: index.php");
        exit();
    } else {
        $error = "Gagal mengupdate data.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Aktivitas</title>
    <link href="../../../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7fc; font-family: sans-serif; padding: 40px; }
        .card-custom { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-custom p-4">
                <h4 class="mb-4 fw-bold">Edit Aktivitas</h4>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST">
                    
                    <div class="mb-3">
                        <label class="form-label">Judul Kegiatan</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($data['title']) ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kota</label>
                            <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($data['city']) ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="category_id" class="form-select" required>
                                <option value="1" <?= $data['category_id'] == 1 ? 'selected' : '' ?>>Pekerjaan (Work)</option>
                                <option value="2" <?= $data['category_id'] == 2 ? 'selected' : '' ?>>Olahraga (Sport)</option>
                                <option value="3" <?= $data['category_id'] == 3 ? 'selected' : '' ?>>Belajar (Study)</option>
                                <option value="4" <?= $data['category_id'] == 4 ? 'selected' : '' ?>>Hiburan (Fun)</option>
                                <option value="5" <?= $data['category_id'] == 5 ? 'selected' : '' ?>>Lainnya</option>
                            </select>
                            <div class="form-text small"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="date" class="form-control" value="<?= $data['activity_date'] ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam</label>
                            <input type="time" name="time" class="form-control" value="<?= $data['time'] ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="planned" <?= $data['status'] == 'planned' ? 'selected' : '' ?>>Rencana (Planned)</option>
                            <option value="done" <?= $data['status'] == 'done' ? 'selected' : '' ?>>Selesai (Done)</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>