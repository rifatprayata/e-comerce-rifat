<?php
// Include konfigurasi database
require_once '../config.php';  // ✅ BENAR untuk Petugas!

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
    mysqli_query($koneksi, "DELETE FROM detail_pesanan WHERE id_pesanan = $id");
    mysqli_query($koneksi, "DELETE FROM pesanan WHERE id = $id");
    $message = "✅ Transaksi berhasil dihapus!";
}

// === AMBIL DATA TRANSAKSI ===
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
    <title>Kelola Data Transaksi - Dashboard Petugas</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', Arial, sans-serif;
            background-color: #ffffff;
            color: #333;
        }

        .main-container {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */
        .sidebar {
            width: 320px;
            background: #9CAF88;
            padding: 25px 20px;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            padding: 15px 10px;
            margin-bottom: 40px;
        }

        .back-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            text-decoration: none;
            margin-right: 15px;
            transition: 0.2s;
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .back-btn svg {
            width: 24px;
            height: 24px;
            stroke: white;
            fill: none;
            stroke-width: 2;
        }

        .sidebar-title {
            color: white;
            font-size: 22px;
            font-weight: 700;
        }

        .menu-item {
            background-color: #f0f0f0;
            padding: 14px 25px;
            margin-bottom: 18px;
            border-radius: 12px;
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
        }

        .menu-item.active {
            background-color: white;
            color: #9CAF88;
            font-weight: 700;
        }

        /* MAIN CONTENT */
        .main-content {
            flex: 1;
            margin-left: 320px;
            padding: 40px 50px;
            background-color: white;
            min-height: 100vh;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #000;
            margin-bottom: 15px;
        }

        .divider {
            width: 100%;
            height: 4px;
            background: #000;
            border-radius: 2px;
        }

        /* TABLE */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #9CAF88;
            color: white;
        }

        th {
            padding: 15px 20px;
            text-align: left;
            font-weight: 600;
            font-size: 15px;
        }

        tbody tr {
            background: #C9C4C4;
            border-bottom: 1px solid #fff;
        }

        tbody tr:nth-child(even) {
            background: #D1CDCD;
        }

        tbody tr:hover {
            background: #bfbab9;
        }

        td {
            padding: 15px 20px;
            font-size: 14px;
        }

        /* ACTION BUTTONS */
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: transparent;
            border: none;
            cursor: pointer;
            margin-right: 8px;
            transition: 0.2s;
        }

        .btn-action:hover {
            transform: scale(1.1);
        }

        .btn-action svg {
            width: 20px;
            height: 20px;
            stroke: #333;
            fill: none;
            stroke-width: 2;
        }

        .btn-delete svg {
            stroke: #333;
        }

        .btn-delete:hover svg {
            stroke: #c62828;
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
            width: 450px;
            max-width: 90%;
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
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }

        .form-group input[readonly] {
            background: #f0f0f0;
            color: #666;
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
    </style>
</head>
<body>
<div class="main-container">
    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-header">
            <a href="dashboard_petugas.php" class="back-btn">
                <svg viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <h1 class="sidebar-title">Dashboard Petugas</h1>
        </div>
        
        <!-- ✅ LINK PETUGAS (BUKAN ADMIN!) -->
        <a href="dashboard_petugas.php" class="menu-item">Dashboard</a>
        <a href="kelola_data_produk_petugas.php" class="menu-item">Kelola Data Produk</a>
        <a href="kelola_data_transaksi_petugas.php" class="menu-item active">Kelola Data Transaksi</a>
        <a href="laporan_petugas.php" class="menu-item">Laporan</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="page-header">
            <h2 class="page-title">Kelola Data Transaksi</h2>
            <div class="divider"></div>
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
                        <th>Nama Pelanggan</th>
                        <th>waktu transaksi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($transactions as $trx): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($trx['nama_pelanggan'] ?? 'Umum') ?></td>
                        <td><?= date('d/m/Y', strtotime($trx['tanggal_pesanan'])) ?></td>
                        <td>
                            <button class="btn-action" onclick='openModal("edit", <?= json_encode($trx) ?>)' title="Edit">
                                <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            </button>
                            
                            <a href="?hapus=<?= $trx['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Hapus transaksi ini?')" title="Hapus">
                                <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 30px; color: #666;">Belum ada data transaksi</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL UPDATE STATUS -->
<div class="modal" id="transactionModal">
    <div class="modal-content">
        <h3 class="modal-title" id="modalTitle">Update Status Transaksi</h3>
        <form method="POST" id="transactionForm">
            <input type="hidden" name="id" id="transactionId">
            
            <div class="form-group">
                <label>Nomor Pesanan</label>
                <input type="text" id="nomor_pesanan" readonly>
            </div>
            
            <div class="form-group">
                <label>Pelanggan</label>
                <input type="text" id="nama_pelanggan" readonly>
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
function openModal(type, data = null) {
    const modal = document.getElementById('transactionModal');
    const title = document.getElementById('modalTitle');
    
    modal.classList.add('active');
    
    if (type === 'edit' && data) {
        title.textContent = 'Update Status Transaksi';
        
        document.getElementById('transactionId').value = data.id;
        document.getElementById('nomor_pesanan').value = data.nomor_pesanan;
        document.getElementById('nama_pelanggan').value = data.nama_pelanggan || 'Umum';
        document.getElementById('status_pesanan').value = data.status_pesanan;
        document.getElementById('status_pembayaran').value = data.status_pembayaran;
    }
}

function closeModal() {
    document.getElementById('transactionModal').classList.remove('active');
}

window.onclick = function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
    }
}
</script>
</body>
</html>