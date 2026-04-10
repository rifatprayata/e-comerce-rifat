<?php
// Include konfigurasi database
require_once '../config.php';
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Auto setup database & admin (hanya pertama kali)
autoSetupDatabase();
createDefaultAdmin();

$error = "";

// === PROSES LOGIN ===
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $koneksi = getKoneksi();
    
    // Cari user di database
    $result = mysqli_query($koneksi, "SELECT * FROM petugas WHERE username = '".escape($username)."'");
    
    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Verifikasi password (menggunakan password_hash)
        if (password_verify($password, $row['password'])) {
            // Set session yang sesuai dengan dashboard.php
            $_SESSION['login_petugas'] = true;
            $_SESSION['id_petugas'] = $row['id'];
            $_SESSION['nama_petugas'] = $row['nama_lengkap'];
            $_SESSION['level'] = $row['level'];
            
            // ✅ REDIRECT BERDASARKAN LEVEL USER
            if ($row['level'] == 'admin') {
                header("Location: ../admin/dashboard.php");
                exit();
            } elseif ($row['level'] == 'kasir' || $row['level'] == 'gudang') {
                header("Location: ../petugas/dashboard_petugas.php");
                exit();
            } elseif ($row['level'] == 'user') {
                // User akan dialihkan ke Dashboard User (E-commerce)
                header("Location: ../user/dashboard_user.php");
                exit();
            } else {
                $error = "Level pengguna tidak dikenali!";
            }
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Login - E Commerce</title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}
body {
    background: #e6e6e6;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}
.login-box {
    background: #8fa37c;
    width: 280px;
    padding: 40px 30px;
    border-radius: 25px;
    color: white;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}
.login-box h2 {
    text-align: center;
    margin-bottom: 25px;
}
label {
    font-weight: bold;
    display: block;
    margin-bottom: 5px;
    font-size: 14px;
}
.input-group {
    position: relative;
    margin-bottom: 18px;
}
input {
    width: 100%;
    padding: 10px 40px 10px 12px;
    border-radius: 10px;
    border: none;
    background: #d9d9d9;
    font-size: 14px;
    outline: none;
}
input:focus {
    background: #e8e8e8;
    box-shadow: 0 0 0 2px rgba(255,255,255,0.5);
}
.eye {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 16px;
    color: #555;
    background: none;
    border: none;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.eye:hover {
    color: #333;
}
button[type="submit"] {
    display: block;
    width: 100%;
    padding: 12px;
    border-radius: 10px;
    border: none;
    background: #bfbfbf;
    font-weight: bold;
    cursor: pointer;
    font-size: 15px;
    transition: background 0.2s;
}
button[type="submit"]:hover {
    background: #a8a8a8;
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
.info-box {
    background-color: rgba(255,255,255,0.2);
    padding: 10px;
    border-radius: 8px;
    margin-top: 20px;
    text-align: center;
    font-size: 12px;
}
.register-link {
    text-align: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid rgba(255,255,255,0.3);
    font-size: 13px;
    color: white;
}
.register-link a {
    color: #fff;
    font-weight: 600;
    text-decoration: underline;
}
.register-link a:hover {
    color: #e0e0e0;
}
</style>
</head>
<body>
<div class="login-box">
    <h2>Login</h2>
    <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
    <form method="POST">
        <label>username</label>
        <div class="input-group">
            <input type="text" name="username" required autocomplete="off">
        </div>
        <label>password</label>
        <div class="input-group">
            <input type="password" id="password" name="password" required>
            <button type="button" class="eye" onclick="togglePassword()">👁</button>
        </div>
        <button type="submit">login</button>
    </form>
    <div class="info-box">
        <strong>Default Login:</strong><br>
        Username: <strong>admin</strong><br>
        Password: <strong>admin123</strong>
    </div>
    <div class="register-link">
        Belum punya akun? <a href="register.php">Daftar disini</a>
    </div>
</div>
<script>
function togglePassword() {
    var pass = document.getElementById("password");
    var eye = document.querySelector(".eye");
    if (pass.type === "password") {
        pass.type = "text";
        eye.textContent = "🙈";
    } else {
        pass.type = "password";
        eye.textContent = "👁";
    }
}
</script>
</body>
</html>