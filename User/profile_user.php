<?php
// profile_user.php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['login_petugas']) || $_SESSION['level'] != 'user') {
    header("Location: ../login.php");
    exit();
}
$koneksi = getKoneksi();
$id_user = $_SESSION['id_petugas'];
$user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM petugas WHERE id = '$id_user'"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - E Commerce</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
    <!-- Copy CSS dari dashboard atau buat file CSS terpisah -->
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f5f5f5; color: #333; }
        .profile-container { max-width: 600px; margin: 40px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .profile-header { text-align: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 2px solid #9CAF88; }
        .profile-avatar { width: 80px; height: 80px; background: #9CAF88; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 28px; font-weight: 700; margin: 0 auto 15px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; background: #f9f9f9; }
        .btn-back { display: inline-block; margin-top: 20px; padding: 10px 25px; background: #9CAF88; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; }
        .btn-back:hover { background: #8A9B76; }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar"><?= strtoupper(substr($user['nama_petugas'] ?? 'U', 0, 1)) ?></div>
            <h2><?= htmlspecialchars($user['nama_petugas'] ?? 'User') ?></h2>
        </div>
        <div class="form-group"><label>ID User</label><input type="text" value="<?= htmlspecialchars($user['id'] ?? '-') ?>" readonly></div>
        <div class="form-group"><label>Username</label><input type="text" value="<?= htmlspecialchars($user['username'] ?? '-') ?>" readonly></div>
        <div class="form-group"><label>Level</label><input type="text" value="<?= htmlspecialchars($user['level'] ?? '-') ?>" readonly></div>
        <a href="dashboard_user.php" class="btn-back">← Kembali ke Dashboard</a>
    </div>
</body>
</html>