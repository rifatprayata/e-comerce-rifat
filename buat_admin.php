<?php
require_once 'config.php'; // sesuaikan jika beda folder

$koneksi = getKoneksi();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $username = trim($_POST['username']);
    $nama     = trim($_POST['nama']);
    $password = trim($_POST['password']);
    
    if ($username && $nama && $password) {
        
        // Cek apakah username sudah ada
        $cek = mysqli_query($koneksi, 
            "SELECT id FROM petugas WHERE username='".escape($username)."'"
        );
        
        if (mysqli_num_rows($cek) > 0) {
            $error = "Username sudah digunakan!";
        } else {
            
            // Hash password
            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert admin
            mysqli_query($koneksi, "
                INSERT INTO petugas (username, password, nama_lengkap, level)
                VALUES (
                    '".escape($username)."',
                    '$hash',
                    '".escape($nama)."',
                    'admin'
                )
            ");
            
            $success = "Admin berhasil dibuat!";
        }
    } else {
        $error = "Semua field wajib diisi!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Buat Admin Sementara</title>
</head>
<body>

<h2>Buat Admin</h2>

<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
<?php if (!empty($success)) echo "<p style='color:green;'>$success</p>"; ?>

<form method="POST">
    <label>Username</label><br>
    <input type="text" name="username" required><br><br>
    
    <label>Nama Lengkap</label><br>
    <input type="text" name="nama" required><br><br>
    
    <label>Password</label><br>
    <input type="password" name="password" required><br><br>
    
    <button type="submit">Buat Admin</button>
</form>

<hr>
<p><strong>⚠️ HAPUS FILE INI SETELAH SELESAI!</strong></p>

</body>
</html>