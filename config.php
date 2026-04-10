<?php
// config.php - Konfigurasi Database
// File ini bisa di-include di semua halaman yang butuh koneksi database

// === KONFIGURASI DATABASE ===
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'rifat_ukk');

// === FUNGSI KONEKSI DATABASE ===
function getKoneksi() {
    static $koneksi = null;
    if ($koneksi === null) {
        $koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if (!$koneksi) {
            die("❌ Koneksi database gagal: " . mysqli_connect_error());
        }
        // Set charset agar tidak error dengan karakter khusus
        mysqli_set_charset($koneksi, "utf8mb4");
    }
    return $koneksi;
}

// === FUNGSI ESCAPE STRING (Mencegah SQL Injection) ===
function escape($string) {
    $koneksi = getKoneksi();
    return mysqli_real_escape_string($koneksi, $string);
}

// === FUNGSI REDIRECT AMAN ===
function redirect($url) {
    if (!headers_sent()) {
        header("Location: " . $url);
        exit;
    } else {
        echo "<script>window.location.href='$url';</script>";
        exit;
    }
}

// === FUNGSI CEK LOGIN ===
function cekLogin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['login_petugas'])) {
        redirect('../login.php');
    }
}

// === FUNGSI LOGOUT ===
function doLogout() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_destroy();
    redirect('../login.php');
}

// === FUNGSI AMBIL DATA STATISTIK ===
function getTotal($tabel, $where = '') {
    $koneksi = getKoneksi();
    $query = "SELECT COUNT(*) as total FROM $tabel";
    if ($where) {
        $query .= " WHERE $where";
    }
    $result = mysqli_query($koneksi, $query);
    return $result ? mysqli_fetch_assoc($result)['total'] : 0;
}

// === FUNGSI FORMAT RUPIAH ===
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

// === FUNGSI FORMAT TANGGAL ===
function formatTanggal($tanggal, $format = 'd F Y') {
    return date($format, strtotime($tanggal));
}

// === FUNGSI UPLOAD GAMBAR ===
function uploadGambar($file, $targetDir = '../uploads/') {
    if (!isset($file) || $file['error'] == 4) {
        return 'default.jpg';
    }
    
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        return 'error';
    }
    
    $newName = uniqid() . '.' . $ext;
    $targetPath = $targetDir . $newName;
    
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $newName;
    }
    
    return 'error';
}

// === AUTO BUAT TABEL JIKA BELUM ADA ===
function autoSetupDatabase() {
    $koneksi = getKoneksi();
    
    // Tabel: petugas (termasuk user) - UPDATED: tambah level 'user'
    mysqli_query($koneksi, "
    CREATE TABLE IF NOT EXISTS petugas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        nama_lengkap VARCHAR(100) NOT NULL,
        level ENUM('admin', 'kasir', 'gudang', 'user') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Tabel: kategori
    mysqli_query($koneksi, "
    CREATE TABLE IF NOT EXISTS kategori (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_kategori VARCHAR(100) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Tabel: produk - UPDATED: tambah kolom gambar
    mysqli_query($koneksi, "
    CREATE TABLE IF NOT EXISTS produk (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kode_produk VARCHAR(50) UNIQUE NOT NULL,
        nama_produk VARCHAR(100) NOT NULL,
        id_kategori INT,
        harga DECIMAL(10,2) NOT NULL,
        stok INT NOT NULL,
        deskripsi TEXT,
        gambar VARCHAR(255),
        status ENUM('tersedia', 'habis', 'diskon') DEFAULT 'tersedia',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_kategori) REFERENCES kategori(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Tabel: pelanggan
    mysqli_query($koneksi, "
    CREATE TABLE IF NOT EXISTS pelanggan (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        email VARCHAR(100),
        telepon VARCHAR(20),
        alamat TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Tabel: pesanan - UPDATED: foreign key ke petugas, tambah kolom alamat
    mysqli_query($koneksi, "
    CREATE TABLE IF NOT EXISTS pesanan (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nomor_pesanan VARCHAR(50) UNIQUE NOT NULL,
        id_pelanggan INT,
        tanggal_pesanan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        total_harga DECIMAL(10,2) NOT NULL,
        status_pesanan ENUM('pending', 'diproses', 'dikirim', 'selesai', 'batal') DEFAULT 'pending',
        status_pembayaran ENUM('belum_bayar', 'lunas') DEFAULT 'belum_bayar',
        metode_pembayaran VARCHAR(50),
        alamat_pengiriman TEXT,
        nama_penerima VARCHAR(100),
        telepon_penerima VARCHAR(20),
        FOREIGN KEY (id_pelanggan) REFERENCES petugas(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Tabel: detail_pesanan
    mysqli_query($koneksi, "
    CREATE TABLE IF NOT EXISTS detail_pesanan (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_pesanan INT NOT NULL,
        id_produk INT NOT NULL,
        jumlah INT NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (id_pesanan) REFERENCES pesanan(id) ON DELETE CASCADE,
        FOREIGN KEY (id_produk) REFERENCES produk(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

// === BUAT ADMIN DEFAULT JIKA BELUM ADA ===
function createDefaultAdmin() {
    $koneksi = getKoneksi();
    $cek = mysqli_query($koneksi, "SELECT id FROM petugas WHERE username = 'admin'");
    if (mysqli_num_rows($cek) == 0) {
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        mysqli_query($koneksi, "
        INSERT INTO petugas (username, password, nama_lengkap, level)
        VALUES ('admin', '$password', 'Administrator', 'admin')
        ");
        // Insert data dummy
        mysqli_query($koneksi, "INSERT INTO kategori (nama_kategori) VALUES ('Makanan'), ('Minuman'), ('Elektronik')");
    }
}
?>