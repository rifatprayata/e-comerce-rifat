<?php
// Include konfigurasi database
require_once '../config.php';

// Cek login dan start session
cekLogin();

// Ambil koneksi
 $koneksi = getKoneksi();

// === LOGOUT ===
if (isset($_GET['logout'])) {
    doLogout();
}

// === GET TYPE LAPORAN ===
 $type = isset($_GET['type']) ? $_GET['type'] : 'transaksi';

// === VALIDASI TYPE ===
 $valid_types = ['transaksi', 'penjualan', 'stok'];
if (!in_array($type, $valid_types)) {
    $type = 'transaksi';
}

// === AMBIL DATA BERDASARKAN TYPE ===
 $transactions = [];
 $sales = [];
 $stocks = [];
 $total_penjualan = 0;
 $total_stok = 0;

if ($type == 'transaksi') {
    $query = "SELECT p.*, pl.nama as nama_pelanggan 
              FROM pesanan p 
              LEFT JOIN pelanggan pl ON p.id_pelanggan = pl.id 
              ORDER BY p.tanggal_pesanan DESC";
    $result = mysqli_query($koneksi, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $transactions[] = $row;
    }
    $total_penjualan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(total_harga) as total FROM pesanan WHERE status_pembayaran = 'lunas'"))['total'] ?? 0;
}

if ($type == 'penjualan') {
    $query = "SELECT pr.nama_produk, k.nama_kategori, 
                     SUM(dp.jumlah) as terjual, 
                     SUM(dp.subtotal) as pendapatan
              FROM detail_pesanan dp
              JOIN produk pr ON dp.id_produk = pr.id
              LEFT JOIN kategori k ON pr.id_kategori = k.id
              GROUP BY dp.id_produk
              ORDER BY pendapatan DESC";
    $result = mysqli_query($koneksi, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $sales[] = $row;
    }
    $total_penjualan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(total_harga) as total FROM pesanan WHERE status_pembayaran = 'lunas'"))['total'] ?? 0;
}

if ($type == 'stok') {
    $query = "SELECT p.*, k.nama_kategori 
              FROM produk p 
              LEFT JOIN kategori k ON p.id_kategori = k.id 
              ORDER BY p.stok ASC";
    $result = mysqli_query($koneksi, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $stocks[] = $row;
    }
    $total_stok = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(stok) as total FROM produk"))['total'] ?? 0;
}

// Judul halaman
 $titles = [
    'transaksi' => 'Laporan Transaksi',
    'penjualan' => 'Laporan Penjualan',
    'stok' => 'Laporan Stok'
];
 $page_title = $titles[$type];

// Halaman aktif (untuk highlight menu)
 $active_page = 'laporan';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Dashboard Petugas</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter :wght@400;700&display=swap">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', Arial, sans-serif; background: #f5f5f5; color: #333; }
        
        .main-container { display: flex; min-height: 100vh; }
        
        /* SIDEBAR - SAMA DENGAN DASHBOARD PETUGAS */
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

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            padding-bottom: 15px;
            border-bottom: 6px solid #9CAF88;
        }

        .header-title {
            font-size: 22px;
            font-weight: 700;
            color: #333;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background-color: #9CAF88;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
        }

        .user-name {
            color: #666;
            font-size: 14px;
        }

        .page-subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }
        
        /* BACK BUTTON */
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            background: #f0f0f0;
            color: #333;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 20px;
            transition: 0.2s;
        }
        .back-btn:hover {
            background: #e0e0e0;
        }
        .back-btn svg {
            width: 16px;
            height: 16px;
            stroke: #333;
            fill: none;
            stroke-width: 2;
        }
        
        /* SUMMARY CARDS */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background: #E0E0E0;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        
        .summary-card-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
            text-transform: uppercase;
            font-weight: 600;
        }
        
        .summary-card-value {
            font-size: 24px;
            font-weight: 700;
            color: #9CAF88;
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
        
        /* BADGE */
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
        
        /* EMPTY STATE */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #888;
            font-size: 14px;
            width: 100%;
        }
        
        /* PRINT BUTTON */
        .print-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
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
        .print-btn:hover {
            background: #7F8F6B;
        }
        .print-btn svg {
            width: 16px;
            height: 16px;
            stroke: white;
            fill: none;
            stroke-width: 2;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar { width: 220px; }
            .main-content { margin-left: 220px; }
        }
        @media (max-width: 768px) {
            .main-container { flex-direction: column; }
            .sidebar { width: 100%; height: auto; position: relative; }
            .main-content { margin-left: 0; }
        }
        @media print {
            .sidebar, .back-btn, .print-btn { display: none; }
            .main-content { margin-left: 0; }
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

        <a href="dashboard_petugas.php" class="menu-item <?= $active_page=='dashboard'?'active':'' ?>">
            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
            Dashboard
        </a>
        <a href="kelola_data_produk_petugas.php" class="menu-item <?= $active_page=='produk'?'active':'' ?>">
            <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
            Kelola Data Produk
        </a>
        <a href="kelola_data_transaksi_petugas.php" class="menu-item <?= $active_page=='transaksi'?'active':'' ?>">
            <svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            Kelola Data Transaksi
        </a>
        <a href="laporan_petugas.php" class="menu-item <?= $active_page=='laporan'?'active':'' ?>">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            Laporan
        </a>

        <div class="logout-section">
            <a href="?logout=true" class="logout-btn">
                <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                Logout
            </a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="header">
            <h2 class="header-title"><?= $page_title ?></h2>
            <div class="user-info">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['nama_petugas'], 0, 1)) ?></div>
                <span class="user-name"><?= htmlspecialchars($_SESSION['nama_petugas']) ?> (<?= ucfirst($_SESSION['level']) ?>)</span>
            </div>
        </div>

        <a href="laporan_petugas.php" class="back-btn">
            <svg viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Kembali ke Pilihan Laporan
        </a>
        
        <p class="page-subtitle">Data laporan per tanggal <?= date('d F Y') ?></p>
        
        <!-- Print Button -->
        <button class="print-btn" onclick="window.print()">
            <svg viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Cetak Laporan
        </button>
        
        <?php if ($type == 'transaksi'): ?>
        <!-- SUMMARY CARDS -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-card-label">Total Transaksi</div>
                <div class="summary-card-value"><?= count($transactions) ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-card-label">Total Pendapatan</div>
                <div class="summary-card-value">Rp <?= number_format($total_penjualan, 0, ',', '.') ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-card-label">Transaksi Lunas</div>
                <div class="summary-card-value"><?= mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pesanan WHERE status_pembayaran = 'lunas'"))['total'] ?? 0 ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-card-label">Transaksi Pending</div>
                <div class="summary-card-value"><?= mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pesanan WHERE status_pembayaran = 'belum_bayar'"))['total'] ?? 0 ?></div>
            </div>
        </div>
        
        <!-- TABLE HEADER -->
        <div class="table-header">
            <span style="left: 20px;">No</span>
            <span style="left: 80px;">No Pesanan</span>
            <span style="left: 220px;">Pelanggan</span>
            <span style="left: 380px;">Tanggal</span>
            <span style="left: 520px;">Total</span>
            <span style="left: 640px;">Status</span>
            <span style="left: 760px;">Pembayaran</span>
        </div>
        
        <!-- TABLE ROWS -->
        <?php $no = 1; foreach ($transactions as $trx): ?>
        <div class="table-row">
            <span style="left: 20px; font-weight: 600;"><?= $no++ ?></span>
            <span style="left: 80px;"><?= htmlspecialchars($trx['nomor_pesanan']) ?></span>
            <span style="left: 220px;"><?= htmlspecialchars($trx['nama_pelanggan'] ?? 'Umum') ?></span>
            <span style="left: 380px;"><?= date('d/m/Y', strtotime($trx['tanggal_pesanan'])) ?></span>
            <span style="left: 520px;">Rp <?= number_format($trx['total_harga'], 0, ',', '.') ?></span>
            <span style="left: 640px;">
                <span class="badge badge-<?= $trx['status_pesanan']=='selesai'?'success':($trx['status_pesanan']=='batal'?'danger':'info') ?>">
                    <?= ucfirst($trx['status_pesanan']) ?>
                </span>
            </span>
            <span style="left: 760px;">
                <span class="badge badge-<?= $trx['status_pembayaran']=='lunas'?'success':'warning' ?>">
                    <?= ucfirst($trx['status_pembayaran']) ?>
                </span>
            </span>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($transactions)): ?>
        <div class="table-row">
            <div class="empty-state">Belum ada data transaksi</div>
        </div>
        <?php endif; ?>
        
        <?php elseif ($type == 'penjualan'): ?>
        <!-- SUMMARY CARDS -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-card-label">Total Pendapatan</div>
                <div class="summary-card-value">Rp <?= number_format($total_penjualan, 0, ',', '.') ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-card-label">Produk Terjual</div>
                <div class="summary-card-value"><?= count($sales) ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-card-label">Rata-rata Penjualan</div>
                <div class="summary-card-value">Rp <?= number_format(count($sales) > 0 ? $total_penjualan / count($sales) : 0, 0, ',', '.') ?></div>
            </div>
        </div>
        
        <!-- TABLE HEADER -->
        <div class="table-header">
            <span style="left: 20px;">No</span>
            <span style="left: 80px;">Nama Produk</span>
            <span style="left: 300px;">Kategori</span>
            <span style="left: 480px;">Terjual</span>
            <span style="left: 620px;">Pendapatan</span>
        </div>
        
        <!-- TABLE ROWS -->
        <?php $no = 1; foreach ($sales as $sale): ?>
        <div class="table-row">
            <span style="left: 20px; font-weight: 600;"><?= $no++ ?></span>
            <span style="left: 80px;"><?= htmlspecialchars($sale['nama_produk']) ?></span>
            <span style="left: 300px;"><?= htmlspecialchars($sale['nama_kategori'] ?? '-') ?></span>
            <span style="left: 480px;"><?= $sale['terjual'] ?> unit</span>
            <span style="left: 620px;">Rp <?= number_format($sale['pendapatan'], 0, ',', '.') ?></span>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($sales)): ?>
        <div class="table-row">
            <div class="empty-state">Belum ada data penjualan</div>
        </div>
        <?php endif; ?>
        
        <?php elseif ($type == 'stok'): ?>
        <!-- SUMMARY CARDS -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-card-label">Total Produk</div>
                <div class="summary-card-value"><?= count($stocks) ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-card-label">Total Stok</div>
                <div class="summary-card-value"><?= $total_stok ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-card-label">Stok Rendah (&lt;10)</div>
                <div class="summary-card-value"><?= mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM produk WHERE stok < 10"))['total'] ?? 0 ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-card-label">Produk Habis</div>
                <div class="summary-card-value"><?= mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM produk WHERE stok = 0"))['total'] ?? 0 ?></div>
            </div>
        </div>
        
        <!-- TABLE HEADER -->
        <div class="table-header">
            <span style="left: 20px;">No</span>
            <span style="left: 80px;">Kode</span>
            <span style="left: 180px;">Nama Produk</span>
            <span style="left: 380px;">Kategori</span>
            <span style="left: 540px;">Stok</span>
            <span style="left: 660px;">Status</span>
        </div>
        
        <!-- TABLE ROWS -->
        <?php $no = 1; foreach ($stocks as $stock): 
            $status_badge = $stock['stok'] == 0 ? 'danger' : ($stock['stok'] < 10 ? 'warning' : 'success');
            $status_text = $stock['stok'] == 0 ? 'Habis' : ($stock['stok'] < 10 ? 'Rendah' : 'Aman');
        ?>
        <div class="table-row">
            <span style="left: 20px; font-weight: 600;"><?= $no++ ?></span>
            <span style="left: 80px;"><?= htmlspecialchars($stock['kode_produk']) ?></span>
            <span style="left: 180px;"><?= htmlspecialchars($stock['nama_produk']) ?></span>
            <span style="left: 380px;"><?= htmlspecialchars($stock['nama_kategori'] ?? '-') ?></span>
            <span style="left: 540px;"><?= $stock['stok'] ?></span>
            <span style="left: 660px;">
                <span class="badge badge-<?= $status_badge ?>">
                    <?= $status_text ?>
                </span>
            </span>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($stocks)): ?>
        <div class="table-row">
            <div class="empty-state">Belum ada data stok</div>
        </div>
        <?php endif; ?>
        
        <?php endif; ?>
    </div>
</div>
</body>
</html>