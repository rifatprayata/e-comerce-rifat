<?php
// Include konfigurasi database
require_once '../config.php';
// Cek login dan start session
cekLogin();
// Ambil koneksi
$koneksi = getKoneksi();
// === PROSES CRUD ===
$message = "";
$error = "";

// Tambah Produk
if (isset($_POST['tambah_produk'])) {
    $kode = escape($_POST['kode_produk']);
    $nama = escape($_POST['nama_produk']);
    $harga = 200000; // ✅ Harga fix 200.000
    $stok = (int)$_POST['stok'];
    $deskripsi = escape($_POST['deskripsi']);
    $status = escape($_POST['status']);
    
    // Upload gambar
    $gambar = uploadGambar($_FILES['gambar']);
    if ($gambar == 'error') {
        $error = "Format gambar tidak didukung!";
    } else {
        $cek = mysqli_query($koneksi, "SELECT id FROM produk WHERE kode_produk = '$kode'");
        if (mysqli_num_rows($cek) > 0) {
            $error = "Kode produk sudah terdaftar!";
        } else {
            $query = "INSERT INTO produk (kode_produk, nama_produk, harga, stok, deskripsi, status, gambar)
            VALUES ('$kode', '$nama', $harga, $stok, '$deskripsi', '$status', '$gambar')";
            if (mysqli_query($koneksi, $query)) {
                $message = "✅ Produk berhasil ditambahkan!";
            } else {
                $error = "❌ Gagal: " . mysqli_error($koneksi);
            }
        }
    }
}

// Edit Produk
if (isset($_POST['edit_produk'])) {
    $id = (int)$_POST['id'];
    $nama = escape($_POST['nama_produk']);
    $harga = 200000; // ✅ Harga fix 200.000
    $stok = (int)$_POST['stok'];
    $deskripsi = escape($_POST['deskripsi']);
    $status = escape($_POST['status']);
    
    // Upload gambar (jika ada)
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $gambar = uploadGambar($_FILES['gambar']);
        if ($gambar != 'error') {
            // Hapus gambar lama
            $old = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT gambar FROM produk WHERE id = $id"));
            if ($old['gambar'] && $old['gambar'] != 'default.jpg' && file_exists('../uploads/' . $old['gambar'])) {
                unlink('../uploads/' . $old['gambar']);
            }
            $query = "UPDATE produk SET nama_produk = '$nama', harga = $harga,
            stok = $stok, deskripsi = '$deskripsi', status = '$status', gambar = '$gambar' WHERE id = $id";
        } else {
            $query = "UPDATE produk SET nama_produk = '$nama', harga = $harga,
            stok = $stok, deskripsi = '$deskripsi', status = '$status' WHERE id = $id";
        }
    } else {
        $query = "UPDATE produk SET nama_produk = '$nama', harga = $harga,
        stok = $stok, deskripsi = '$deskripsi', status = '$status' WHERE id = $id";
    }
    
    if (mysqli_query($koneksi, $query)) {
        $message = "✅ Produk berhasil diupdate!";
    } else {
        $error = "❌ Gagal: " . mysqli_error($koneksi);
    }
}

// Hapus Produk
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    // Hapus gambar juga
    $old = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT gambar FROM produk WHERE id = $id"));
    if ($old['gambar'] && $old['gambar'] != 'default.jpg' && file_exists('../uploads/' . $old['gambar'])) {
        unlink('../uploads/' . $old['gambar']);
    }
    mysqli_query($koneksi, "DELETE FROM produk WHERE id = $id");
    $message = "✅ Produk berhasil dihapus!";
}

// === AMBIL DATA PRODUK ===
$query = "SELECT * FROM produk ORDER BY id ASC";
$result = mysqli_query($koneksi, $query);
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Data Produk - Dashboard Petugas</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
<style>
* {
margin: 0;
padding: 0;
box-sizing: border-box;
}
body {
font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
background-color: #f5f5f5;
color: #333;
}
.main-container {
display: flex;
min-height: 100vh;
}
/* SIDEBAR */
.sidebar {
width: 280px;
background: #9CAF88;
padding: 30px 20px;
display: flex;
flex-direction: column;
position: fixed;
height: 100vh;
overflow-y: auto;
}
.sidebar-header {
text-align: center;
padding: 15px 0;
margin-bottom: 30px;
border-bottom: 2px solid rgba(255,255,255,0.3);
}
.sidebar-title {
color: white;
font-size: 24px;
font-weight: 700;
letter-spacing: 0.5px;
}
.menu-item {
background-color: #f0f0f0;
padding: 14px 20px;
margin-bottom: 15px;
border-radius: 10px;
text-align: center;
cursor: pointer;
font-weight: 600;
color: #333;
text-decoration: none;
transition: all 0.2s ease;
font-size: 15px;
display: block;
}
.menu-item:hover {
background-color: #e0e0e0;
transform: translateX(3px);
}
.menu-item.active {
background-color: white;
color: #9CAF88;
font-weight: 700;
}
.logout-section {
margin-top: auto;
padding-top: 20px;
border-top: 2px solid rgba(255,255,255,0.3);
}
.logout-btn {
display: block;
padding: 14px 20px;
background-color: rgba(255,255,255,0.2);
color: white;
border: none;
border-radius: 10px;
cursor: pointer;
font-size: 15px;
font-weight: 600;
text-decoration: none;
transition: background-color 0.2s;
text-align: center;
}
.logout-btn:hover {
background-color: rgba(255,255,255,0.3);
}
/* MAIN CONTENT */
.main-content {
flex: 1;
margin-left: 280px;
padding: 30px;
background-color: white;
min-height: 100vh;
}
.page-header {
display: flex;
justify-content: space-between;
align-items: center;
margin-bottom: 30px;
padding-bottom: 15px;
border-bottom: 5px solid #9CAF88;
}
.page-title {
font-size: 26px;
font-weight: 700;
color: #333;
}
/* BUTTON ADD */
.btn-add {
display: inline-block;
padding: 12px 25px;
background: #9CAF88;
color: white;
border: none;
border-radius: 8px;
font-size: 15px;
font-weight: 600;
cursor: pointer;
transition: 0.2s;
}
.btn-add:hover {
background: #7F8F6B;
}
/* TABLE */
.table-container {
overflow-x: auto;
}
table {
width: 100%;
border-collapse: collapse;
margin-top: 20px;
}
thead {
background: #9CAF88;
color: white;
}
th {
padding: 15px;
text-align: left;
font-weight: 600;
font-size: 15px;
}
tbody tr {
background: #E0E0E0;
border-bottom: 2px solid #fff;
}
tbody tr:hover {
background: #d5d5d5;
}
td {
padding: 15px;
font-size: 14px;
}
.badge {
padding: 5px 12px;
border-radius: 15px;
font-size: 13px;
font-weight: 500;
display: inline-block;
}
.badge-success {
background: #d4edda;
color: #155724;
}
.badge-danger {
background: #f8d7da;
color: #721c24;
}
.badge-warning {
background: #fff3cd;
color: #856404;
}
/* ACTION BUTTONS */
.btn-action {
display: inline-flex;
align-items: center;
justify-content: center;
width: 35px;
height: 35px;
background: white;
border: none;
border-radius: 6px;
cursor: pointer;
margin-right: 5px;
transition: 0.2s;
}
.btn-action:hover {
background: #ddd;
}
.btn-action svg {
width: 18px;
height: 18px;
stroke: #333;
fill: none;
stroke-width: 2;
}
.btn-delete svg {
stroke: #c62828;
}
.btn-delete:hover {
background: #ffebee;
}
/* MESSAGE */
.message {
padding: 12px 20px;
border-radius: 8px;
margin-bottom: 20px;
font-size: 14px;
display: flex;
align-items: center;
gap: 10px;
}
.message.success {
background: #d4edda;
color: #155724;
border: 1px solid #c3e6cb;
}
.message.error {
background: #f8d7da;
color: #721c24;
border: 1px solid #f5c6cb;
}
/* MODAL */
.modal {
display: none;
position: fixed;
top: 0;
left: 0;
width: 100%;
height: 100%;
background: rgba(0,0,0,0.5);
z-index: 1000;
align-items: center;
justify-content: center;
}
.modal.active {
display: flex;
}
.modal-content {
background: white;
padding: 30px;
border-radius: 15px;
width: 500px;
max-width: 90%;
max-height: 90vh;
overflow-y: auto;
}
.modal-title {
font-size: 22px;
font-weight: 700;
margin-bottom: 20px;
color: #333;
}
.form-group {
margin-bottom: 15px;
}
.form-group label {
display: block;
margin-bottom: 5px;
font-weight: 500;
font-size: 14px;
}
.form-group input,
.form-group select,
.form-group textarea {
width: 100%;
padding: 10px;
border: 1px solid #ddd;
border-radius: 8px;
font-size: 14px;
}
.form-group textarea {
resize: vertical;
min-height: 80px;
}
.form-row {
display: flex;
gap: 15px;
}
.form-row .form-group {
flex: 1;
}
.modal-actions {
display: flex;
gap: 10px;
margin-top: 20px;
}
.btn-modal {
flex: 1;
padding: 10px;
border: none;
border-radius: 8px;
font-weight: 600;
cursor: pointer;
}
.btn-save {
background: #9CAF88;
color: white;
}
.btn-cancel {
background: #ccc;
color: #333;
}
/* Responsive */
@media (max-width: 768px) {
.sidebar {
width: 100%;
height: auto;
position: relative;
}
.main-content {
margin-left: 0;
}
}
</style>
</head>
<body>
<div class="main-container">
<!-- SIDEBAR -->
<div class="sidebar">
<div class="sidebar-header">
<h1 class="sidebar-title">Dashboard Petugas</h1>
</div>
<a href="dashboard_petugas.php" class="menu-item">Dashboard</a>
<a href="kelola_data_produk_petugas.php" class="menu-item active">Kelola Data Produk</a>
<a href="kelola_data_transaksi_petugas.php" class="menu-item">Kelola Data Transaksi</a>
<a href="laporan_petugas.php" class="menu-item">Laporan</a>
<div class="logout-section">
<a href="?logout=true" class="logout-btn">Logout</a>
</div>
</div>
<!-- MAIN CONTENT -->
<div class="main-content">
<div class="page-header">
<h2 class="page-title">Kelola Data Produk</h2>
<button class="btn-add" onclick="openModal('add')">+ Tambah Produk</button>
</div>
<?php if (!empty($message)): ?>
<div class="message success"><?= $message ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="message error"><?= $error ?></div>
<?php endif; ?>
<!-- Table -->
<div class="table-container">
<table>
<thead>
<tr>
<th>No</th>
<th>Kode</th>
<th>Nama Produk</th>
<th>Harga</th>
<th>Stok</th>
<th>Gambar</th>
<th>Status</th>
<th>Aksi</th>
</tr>
</thead>
<tbody>
<?php $no = 1; foreach ($products as $product): ?>
<tr>
<td><?= $no++ ?></td>
<td><?= htmlspecialchars($product['kode_produk']) ?></td>
<td><?= htmlspecialchars($product['nama_produk']) ?></td>
<td>Rp <?= number_format(200000, 0, ',', '.') ?></td>
<td><?= $product['stok'] ?></td>
<td><img src="../uploads/<?= $product['gambar'] ?? 'default.jpg' ?>" alt="Produk" style="width:50px;height:50px;object-fit:cover;border-radius:5px;"></td>
<td>
<span class="badge badge-<?= $product['status']=='tersedia'?'success':($product['status']=='habis'?'danger':'warning') ?>">
<?= ucfirst($product['status']) ?>
</span>
</td>
<td>
<button class="btn-action" onclick='openModal("edit", <?= json_encode($product) ?>)' title="Edit">
<svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
</button>
<a href="?hapus=<?= $product['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Hapus produk ini?')" title="Hapus">
<svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
</a>
</td>
</tr>
<?php endforeach; ?>
<?php if (empty($products)): ?>
<tr>
<td colspan="8" style="text-align: center; padding: 30px; color: #888;">Belum ada data produk</td>
</tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>
<!-- MODAL -->
<div class="modal" id="productModal">
<div class="modal-content">
<h3 class="modal-title" id="modalTitle">Tambah Produk</h3>
<form method="POST" id="productForm" enctype="multipart/form-data">
<input type="hidden" name="id" id="productId">
<div class="form-group">
<label>Kode Produk</label>
<input type="text" name="kode_produk" id="kode_produk" required autocomplete="off">
</div>
<div class="form-group">
<label>Nama Produk</label>
<input type="text" name="nama_produk" id="nama_produk" required>
</div>
<div class="form-group">
<label>Harga</label>
<input type="text" value="Rp 200.000" readonly style="background: #f0f0f0; color: #666;">
<input type="hidden" name="harga" value="200000">
</div>
<div class="form-group">
<label>Stok</label>
<input type="number" name="stok" id="stok" required min="0">
</div>
<div class="form-group">
<label>Deskripsi</label>
<textarea name="deskripsi" id="deskripsi" rows="3"></textarea>
</div>
<div class="form-group">
<label>Gambar Produk</label>
<input type="file" name="gambar" id="gambar" accept="image/*">
<small style="color: #666; font-size: 11px;">Format: JPG, PNG, GIF, WEBP (Max 2MB)</small>
</div>
<div class="form-group">
<label>Status</label>
<select name="status" id="status" required>
<option value="tersedia">Tersedia</option>
<option value="habis">Habis</option>
<option value="diskon">Diskon</option>
</select>
</div>
<div class="modal-actions">
<button type="button" class="btn-modal btn-cancel" onclick="closeModal()">Batal</button>
<button type="submit" class="btn-modal btn-save" name="tambah_produk" id="submitBtn">Tambah</button>
</div>
</form>
</div>
</div>
<script>
function openModal(type, data = null) {
const modal = document.getElementById('productModal');
const title = document.getElementById('modalTitle');
const form = document.getElementById('productForm');
const submitBtn = document.getElementById('submitBtn');
modal.classList.add('active');
if (type === 'add') {
title.textContent = 'Tambah Produk';
submitBtn.name = 'tambah_produk';
submitBtn.textContent = 'Tambah';
document.getElementById('productId').value = '';
document.getElementById('kode_produk').value = '';
document.getElementById('kode_produk').readOnly = false;
document.getElementById('nama_produk').value = '';
document.getElementById('stok').value = '';
document.getElementById('deskripsi').value = '';
document.getElementById('gambar').value = '';
document.getElementById('status').value = 'tersedia';
} else if (type === 'edit' && data) {
title.textContent = 'Edit Produk';
submitBtn.name = 'edit_produk';
submitBtn.textContent = 'Update';
document.getElementById('productId').value = data.id;
document.getElementById('kode_produk').value = data.kode_produk;
document.getElementById('kode_produk').readOnly = true;
document.getElementById('nama_produk').value = data.nama_produk;
document.getElementById('stok').value = data.stok;
document.getElementById('deskripsi').value = data.deskripsi || '';
document.getElementById('status').value = data.status;
}
}
function closeModal() {
document.getElementById('productModal').classList.remove('active');
}
window.onclick = function(e) {
if (e.target.classList.contains('modal')) {
e.target.classList.remove('active');
}
}
</script>
</body>
</html>