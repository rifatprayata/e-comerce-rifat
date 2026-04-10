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

// Update Status Transaksi
if (isset($_POST['update_transaksi'])) {
    $id = (int)$_POST['id'];
    $status_pesanan = escape($_POST['status_pesanan']);
    $status_pembayaran = escape($_POST['status_pembayaran']);
    
    $query = "UPDATE pesanan SET status_pesanan = '$status_pesanan', status_pembayaran = '$status_pembayaran' WHERE id = $id";
    if (mysqli_query($koneksi, $query)) {
        $message = "✅ Transaksi berhasil diupdate!";
    } else {
        $error = "❌ Gagal: " . mysqli_error($koneksi);
    }
}

// Hapus Transaksi
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    // Hapus detail pesanan dulu (foreign key constraint)
    mysqli_query($koneksi, "DELETE FROM detail_pesanan WHERE id_pesanan = $id");
    mysqli_query($koneksi, "DELETE FROM pesanan WHERE id = $id");
    $message = "✅ Transaksi berhasil dihapus!";
}

// === AMBIL DATA TRANSAKSI DARI DATABASE ===
$query = "SELECT p.*, pl.nama as nama_pelanggan 
          FROM pesanan p 
          LEFT JOIN pelanggan pl ON p.id_pelanggan = pl.id 
          ORDER BY p.tanggal_pesanan DESC";
$result = mysqli_query($koneksi, $query);
$transactions = [];
while ($row = mysqli_fetch_assoc($result)) {
    $transactions[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Transaksi - Admin Panel</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', Arial, sans-serif; background: #f5f5f5; color: #333; }
        
        .main-container { display: flex; min-height: 100vh; }
        
        /* SIDEBAR - SAMA DENGAN DASHBOARD.PHP */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #9CAF88 0%, #8A9B76 100%);
            padding: 25px 20px;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            text-align: center;
            padding: 15px 0;
            margin-bottom: 25px;
            border-bottom: 2px solid rgba(255,255,255,0.3);
        }

        .sidebar-title {
            color: white;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .menu-item {
            background-color: rgba(255,255,255,0.9);
            padding: 14px 18px;
            margin-bottom: 12px;
            border-radius: 8px;
            text-align: left;
            cursor: pointer;
            font-weight: 500;
            color: #333;
            text-decoration: none;
            transition: all 0.2s ease;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .menu-item:hover {
            background-color: white;
            transform: translateX(4px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .menu-item.active {
            background-color: white;
            color: #9CAF88;
            font-weight: 700;
            border-left: 4px solid #9CAF88;
        }

        .menu-item svg {
            width: 18px;
            height: 18px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
        }

        .logout-section {
            margin-top: auto;
            padding-top: 20px;
            border-top: 2px solid rgba(255,255,255,0.3);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            background-color: rgba(255,255,255,0.2);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: background-color 0.2s;
        }

        .logout-btn:hover {
            background-color: rgba(255,255,255,0.3);
        }

        .logout-btn svg {
            width: 18px;
            height: 18px;
            stroke: white;
            fill: none;
            stroke-width: 2;
        }
        
        /* MAIN CONTENT */
        .main-content { 
            flex: 1; 
            margin-left: 260px; 
            padding: 30px; 
            background: #fff; 
            min-height: 100vh; 
        }
        .page-title { 
            font-size: 24px; 
            font-weight: 700; 
            color: #000; 
            margin-bottom: 25px; 
        }
        .divider { 
            width: 100%; 
            height: 5px; 
            background: #9CAF88; 
            margin: 0 0 25px 0; 
            border-radius: 2px;
        }
        
        /* TABLE HEADER */
        .table-header { 
            width: 100%; 
            height: 50px; 
            background: #9CAF88; 
            display: flex; 
            align-items: center; 
            position: relative; 
            margin-bottom: 2px; 
            border-radius: 8px 8px 0 0;
        }
        .table-header span { 
            font-family: Inter; 
            font-size: 14px; 
            position: absolute; 
            font-weight: 600;
            color: white;
        }
        .table-header .col-no { left: 20px; }
        .table-header .col-nomor { left: 80px; }
        .table-header .col-pelanggan { left: 200px; }
        .table-header .col-tanggal { left: 380px; }
        .table-header .col-total { left: 540px; }
        .table-header .col-status { left: 660px; }
        .table-header .col-pembayaran { left: 780px; }
        .table-header .col-aksi { left: 880px; }
        
        /* TABLE ROW */
        .table-row { 
            width: 100%; 
            height: 50px; 
            background: #E0E0E0; 
            display: flex; 
            align-items: center; 
            position: relative; 
            margin-bottom: 2px; 
        }
        .table-row:hover {
            background: #d5d5d5;
        }
        .table-row span { 
            font-family: Inter; 
            font-size: 13px; 
            position: absolute; 
        }
        .table-row .col-no { left: 20px; font-weight: 600; }
        .table-row .col-nomor { left: 80px; }
        .table-row .col-pelanggan { left: 200px; }
        .table-row .col-tanggal { left: 380px; }
        .table-row .col-total { left: 540px; }
        .table-row .col-status { left: 660px; font-weight: 500; }
        .table-row .col-pembayaran { left: 780px; font-weight: 500; }
        
        /* ACTION BUTTONS */
        .btn-action { 
            width: 30px; 
            height: 30px; 
            position: absolute; 
            top: 50%; 
            transform: translateY(-50%); 
            cursor: pointer; 
            background: #fff; 
            border: none; 
            border-radius: 6px; 
            padding: 0; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            transition: 0.2s;
        }
        .btn-action:hover { background: #ddd; }
        .btn-action svg { 
            width: 15px; 
            height: 15px; 
            stroke: #333; 
            fill: none; 
            stroke-width: 2; 
        }
        .btn-edit { left: 885px; }
        .btn-delete { left: 925px; }
        .btn-delete svg { stroke: #c62828; }
        .btn-delete:hover { background: #ffebee; }
        
        /* ADD BUTTON */
        .btn-add { 
            display: inline-block; 
            padding: 10px 20px; 
            background: #9CAF88; 
            color: white; 
            border: none; 
            border-radius: 8px; 
            font-size: 14px; 
            font-weight: 600; 
            cursor: pointer; 
            margin-bottom: 20px; 
            transition: 0.2s;
        }
        .btn-add:hover { 
            background: #7F8F6B; 
            transform: translateY(-2px);
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
        .modal.active { display: flex; }
        .modal-content { 
            background: white; 
            padding: 25px; 
            border-radius: 12px; 
            width: 450px; 
            max-width: 90%; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-title { 
            font-size: 20px; 
            font-weight: 700; 
            margin-bottom: 20px; 
            color: #333;
        }
        .form-group { 
            margin-bottom: 15px; 
        }
        .form-group label { 
            display: block; 
            margin-bottom: 6px; 
            font-weight: 500; 
            font-size: 13px;
            color: #555;
        }
        .form-group input, .form-group select { 
            width: 100%; 
            padding: 10px 12px; 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            font-size: 14px; 
            outline: none;
            transition: 0.2s;
        }
        .form-group input:focus, .form-group select:focus { 
            border-color: #9CAF88; 
            box-shadow: 0 0 0 3px rgba(156,175,136,0.2);
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
            font-size: 14px;
            transition: 0.2s;
        }
        .btn-save { 
            background: #9CAF88; 
            color: white; 
        }
        .btn-save:hover { background: #7F8F6B; }
        .btn-cancel { 
            background: #e0e0e0; 
            color: #333; 
        }
        .btn-cancel:hover { background: #d0d0d0; }
        
        /* MESSAGE */
        .message { 
            padding: 12px 18px; 
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
        
        /* EMPTY STATE */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #888;
            font-size: 14px;
        }
        
        /* BADGE STATUS */
        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar { width: 220px; }
            .main-content { margin-left: 220px; }
            .table-header span, .table-row span { font-size: 12px; }
        }
        @media (max-width: 768px) {
            .main-container { flex-direction: column; }
            .sidebar { width: 100%; height: auto; position: relative; }
            .main-content { margin-left: 0; }
            .table-header, .table-row { overflow-x: auto; }
        }
    </style>
</head>
<body>
<div class="main-container">
    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h1 class="sidebar-title">Admin Panel</h1>
        </div>

        <a href="dashboard.php" class="menu-item">
            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
            Dashboard
        </a>
        <a href="kelola_data_user.php" class="menu-item">
            <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            Kelola Data User
        </a>
        <a href="kelola_data_produk.php" class="menu-item">
            <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
            Kelola Data Produk
        </a>
        <a href="kelola_data_transaksi.php" class="menu-item active">
            <svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            Kelola Data Transaksi
        </a>
        <a href="laporan.php" class="menu-item">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            Laporan
        </a>
        <a href="backup_data.php" class="menu-item">
            <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
            Backup Data
        </a>
        <a href="restore_data.php" class="menu-item">
            <svg viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"></polyline><polyline points="23 20 23 14 17 14"></polyline><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"></path></svg>
            Restore Data
        </a>

        <div class="logout-section">
            <a href="../auth/logout.php" class="logout-btn">
                <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                Logout
            </a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <h2 class="page-title">Kelola Data Transaksi</h2>
        <div class="divider"></div>
        
        <?php if (!empty($message)): ?>
            <div class="message success">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                <?= $message ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="message error">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <!-- Table Header -->
        <div class="table-header">
            <span class="col-no">No</span>
            <span class="col-nomor">No Pesanan</span>
            <span class="col-pelanggan">Pelanggan</span>
            <span class="col-tanggal">Tanggal</span>
            <span class="col-total">Total</span>
            <span class="col-status">Status</span>
            <span class="col-pembayaran">Pembayaran</span>
            <span class="col-aksi">Aksi</span>
        </div>
        
        <!-- Table Rows (Data dari Database) -->
        <?php $no = 1; foreach ($transactions as $trx): ?>
        <div class="table-row">
            <span class="col-no"><?= $no++ ?></span>
            <span class="col-nomor"><?= htmlspecialchars($trx['nomor_pesanan']) ?></span>
            <span class="col-pelanggan"><?= htmlspecialchars($trx['nama_pelanggan'] ?? 'Umum') ?></span>
            <span class="col-tanggal"><?= date('d/m/Y', strtotime($trx['tanggal_pesanan'])) ?></span>
            <span class="col-total">Rp <?= number_format($trx['total_harga'], 0, ',', '.') ?></span>
            <span class="col-status">
                <span class="badge badge-<?= $trx['status_pesanan']=='selesai'?'success':($trx['status_pesanan']=='batal'?'danger':'info') ?>">
                    <?= ucfirst($trx['status_pesanan']) ?>
                </span>
            </span>
            <span class="col-pembayaran">
                <span class="badge badge-<?= $trx['status_pembayaran']=='lunas'?'success':'warning' ?>">
                    <?= ucfirst($trx['status_pembayaran']) ?>
                </span>
            </span>
            
            <!-- Edit Button -->
            <button class="btn-action btn-edit" onclick='openModal("edit", <?= json_encode($trx) ?>)' title="Edit Status">
                <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
            </button>
            
            <!-- Delete Button -->
            <a href="?hapus=<?= $trx['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Hapus transaksi ini?')" title="Hapus">
                <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
            </a>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($transactions)): ?>
        <div class="table-row">
            <div class="empty-state">Belum ada data transaksi</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL UPDATE STATUS TRANSAKSI -->
<div class="modal" id="transactionModal">
    <div class="modal-content">
        <h3 class="modal-title" id="modalTitle">Update Status Transaksi</h3>
        <form method="POST" id="transactionForm">
            <input type="hidden" name="id" id="transactionId">
            
            <div class="form-group">
                <label>Nomor Pesanan</label>
                <input type="text" id="nomor_pesanan" readonly style="background: #f5f5f5;">
            </div>
            
            <div class="form-group">
                <label>Status Pesanan</label>
                <select name="status_pesanan" id="status_pesanan" required>
                    <option value="pending">Pending</option>
                    <option value="diproses">Diproses</option>
                    <option value="dikirim">Dikirim</option>
                    <option value="selesai">Selesai</option>
                    <option value="batal">Batal</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Status Pembayaran</label>
                <select name="status_pembayaran" id="status_pembayaran" required>
                    <option value="belum_bayar">Belum Bayar</option>
                    <option value="lunas">Lunas</option>
                </select>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn-modal btn-cancel" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn-modal btn-save" name="update_transaksi">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
// Modal Functions
function openModal(type, data = null) {
    const modal = document.getElementById('transactionModal');
    const title = document.getElementById('modalTitle');
    
    modal.classList.add('active');
    
    if (type === 'edit' && data) {
        title.textContent = 'Update Status Transaksi';
        
        document.getElementById('transactionId').value = data.id;
        document.getElementById('nomor_pesanan').value = data.nomor_pesanan;
        document.getElementById('status_pesanan').value = data.status_pesanan;
        document.getElementById('status_pembayaran').value = data.status_pembayaran;
    }
}

function closeModal() {
    document.getElementById('transactionModal').classList.remove('active');
}

// Close modal when clicking outside
window.onclick = function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
    }
}
</script>
</body>
</html>