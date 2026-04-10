<?php
// Include konfigurasi database
require_once '../config.php';
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Auto setup database (jika belum ada)
autoSetupDatabase();

$message = "";
$error = "";

// === PROSES REGISTRASI ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $level = $_POST['level'];
    
    // Validasi input
    if (empty($username) || empty($password) || empty($confirm_password) || empty($nama_lengkap)) {
        $error = "Semua field wajib diisi!";
    } elseif (strlen($username) < 3) {
        $error = "Username minimal 3 karakter!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } elseif ($password !== $confirm_password) {
        $error = "Password tidak sama!";
    } else {
        $koneksi = getKoneksi();
        // Cek username sudah ada atau belum
        $cek = mysqli_query($koneksi, "SELECT id FROM petugas WHERE username = '".escape($username)."'");
        if (mysqli_num_rows($cek) > 0) {
            $error = "Username sudah terdaftar!";
        } else {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            // Insert ke database
            $query = "INSERT INTO petugas (username, password, nama_lengkap, level) 
                      VALUES ('".escape($username)."', '$password_hash', '".escape($nama_lengkap)."', '".escape($level)."')";
            if (mysqli_query($koneksi, $query)) {
                $message = "✅ Registrasi berhasil! Silakan <a href='login.php' style='color:#fff;font-weight:bold;text-decoration:underline;'>Login</a>";
            } else {
                $error = "❌ Gagal registrasi: " . mysqli_error($koneksi);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registrasi - E Commerce</title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}
body {
    background: #e6e6e6;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}
.main-container {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    max-width: 400px;
}
.rectangle {
    background: #8fa37c;
    width: 100%;
    padding: 35px 30px;
    border-radius: 25px;
    color: white;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}
.registrasi {
    display: block;
    text-align: center;
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 25px;
    color: white;
}
label {
    font-weight: 600;
    display: block;
    margin-bottom: 6px;
    font-size: 14px;
    color: white;
}
.input-group {
    position: relative;
    margin-bottom: 18px;
}
input, select {
    width: 100%;
    padding: 10px 12px;
    border-radius: 10px;
    border: none;
    background: #d9d9d9;
    font-size: 14px;
    color: #333;
    outline: none;
}
input:focus, select:focus {
    background: #e8e8e8;
    box-shadow: 0 0 0 2px rgba(255,255,255,0.5);
}
.eye {
    position: absolute;
    right: 12px;
    top: 38px;
    cursor: pointer;
    font-size: 16px;
    color: #555;
    background: none;
    border: none;
    padding: 0;
}
.eye:hover {
    color: #333;
}
.register {
    display: block;
    width: 100%;
    padding: 12px;
    margin-top: 10px;
    border-radius: 12px;
    border: none;
    background: #bfbfbf;
    color: white;
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    text-align: center;
    text-decoration: none;
    transition: background 0.2s;
}
.register:hover {
    background: #a8a8a8;
}
.message {
    background: #4caf50;
    color: white;
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-size: 13px;
    text-align: center;
}
.error {
    background: #f44336;
    color: white;
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-size: 13px;
    text-align: center;
}
.login-link {
    text-align: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid rgba(255,255,255,0.3);
    font-size: 13px;
    color: white;
}
.login-link a {
    color: #fff;
    font-weight: 600;
    text-decoration: underline;
}
.login-link a:hover {
    color: #e0e0e0;
}
select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    padding-right: 35px;
}
</style>
</head>
<body>
<div class="main-container">
    <div class="rectangle">
        <span class="registrasi">Registrasi</span>
        <?php if (!empty($message)): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <label>username</label>
            <div class="input-group">
                <input type="text" name="username" placeholder="Masukan username" required autocomplete="off" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
            </div>
            <label>nama lengkap</label>
            <div class="input-group">
                <input type="text" name="nama_lengkap" placeholder="Masukan nama lengkap" required value="<?= isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : '' ?>">
            </div>
            <label>level</label>
            <div class="input-group">
                <select name="level" required>
                    <option value="kasir">Kasir</option>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <label>password</label>
            <div class="input-group">
                <input type="password" id="password" name="password" placeholder="Masukan password" required>
                <button type="button" class="eye" onclick="togglePassword('password', this)">👁</button>
            </div>
            <label>masukan ulang password</label>
            <div class="input-group">
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Masukan ulang password" required>
                <button type="button" class="eye" onclick="togglePassword('confirm_password', this)">👁</button>
            </div>
            <button type="submit" name="register" class="register">Daftar</button>
        </form>
        <div class="login-link">
            Sudah punya akun? <a href="login.php">Login disini</a>
        </div>
    </div>
</div>
<script>
function togglePassword(inputId, btn) {
    var input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text";
        btn.textContent = "🙈";
    } else {
        input.type = "password";
        btn.textContent = "👁";
    }
}
</script>
</body>
</html>