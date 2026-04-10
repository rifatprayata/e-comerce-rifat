<?php
// Include konfigurasi database
require_once '../config.php';

// Cek login dan start session
cekLogin();

// Ambil koneksi
$koneksi = getKoneksi();

// === AMBIL DATA STATISTIK UNTUK LAPORAN ===
$total_transaksi = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pesanan"))['total'] ?? 0;
$total_penjualan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(total_harga) as total FROM pesanan WHERE status_pembayaran = 'lunas'"))['total'] ?? 0;
$total_produk = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM produk"))['total'] ?? 0;
$total_stok = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(stok) as total FROM produk"))['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Admin Panel</title>
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
            margin-bottom: 10px; 
        }
        .page-subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 30px;
        }
        .divider { 
            width: 100%; 
            height: 5px; 
            background: #9CAF88; 
            margin: 0 0 30px 0; 
            border-radius: 2px;
        }
        
        /* REPORT CARDS */
        .report-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .report-card {
            background: #E0E0E0;
            border-radius: 12px;
            padding: 30px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            display: block;
        }
        
        .report-card:hover {
            background: #d5d5d5;
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-color: #9CAF88;
        }
        
        .report-card-icon {
            width: 60px;
            height: 60px;
            background: #9CAF88;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .report-card-icon svg {
            width: 32px;
            height: 32px;
            stroke: white;
            fill: none;
            stroke-width: 2;
        }
        
        .report-card-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #333;
        }
        
        .report-card-desc {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .report-card-stats {
            display: flex;
            gap: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(0,0,0,0.1);
        }
        
        .stat-item {
            display: flex;
            flex-direction: column;
        }
        
        .stat-label {
            font-size: 11px;
            color: #888;
            text-transform: uppercase;
            font-weight: 600;
        }
        
        .stat-value {
            font-size: 18px;
            font-weight: 700;
            color: #9CAF88;
        }
        
        .report-card-arrow {
            position: absolute;
            top: 30px;
            right: 30px;
            width: 40px;
            height: 40px;
            background: rgba(156,175,136,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
        }
        
        .report-card:hover .report-card-arrow {
            background: #9CAF88;
            transform: translateX(5px);
        }
        
        .report-card-arrow svg {
            width: 20px;
            height: 20px;
            stroke: #9CAF88;
            fill: none;
            stroke-width: 2;
        }
        
        .report-card:hover .report-card-arrow svg {
            stroke: white;
        }
        
        /* QUICK INFO */
        .quick-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-card {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #9CAF88;
        }
        
        .info-card-label {
            font-size: 12px;
            color: #888;
            margin-bottom: 5px;
        }
        
        .info-card-value {
            font-size: 24px;
            font-weight: 700;
            color: #333;
        }
        
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
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar { width: 220px; }
            .main-content { margin-left: 220px; }
        }
        @media (max-width: 768px) {
            .main-container { flex-direction: column; }
            .sidebar { width: 100%; height: auto; position: relative; }
            .main-content { margin-left: 0; }
            .report-cards { grid-template-columns: 1fr; }
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
        <a href="kelola_data_transaksi.php" class="menu-item">
            <svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            Kelola Data Transaksi
        </a>
        <a href="laporan.php" class="menu-item active">
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
        <h2 class="page-title">Laporan</h2>
        <p class="page-subtitle">Pilih jenis laporan yang ingin Anda lihat</p>
        <div class="divider"></div>
        
        <!-- Quick Info Stats -->
        <div class="quick-info">
            <div class="info-card">
                <div class="info-card-label">Total Transaksi</div>
                <div class="info-card-value"><?= number_format($total_transaksi, 0, ',', '.') ?></div>
            </div>
            <div class="info-card">
                <div class="info-card-label">Total Penjualan</div>
                <div class="info-card-value">Rp <?= number_format($total_penjualan, 0, ',', '.') ?></div>
            </div>
            <div class="info-card">
                <div class="info-card-label">Total Produk</div>
                <div class="info-card-value"><?= number_format($total_produk, 0, ',', '.') ?></div>
            </div>
            <div class="info-card">
                <div class="info-card-label">Total Stok</div>
                <div class="info-card-value"><?= number_format($total_stok, 0, ',', '.') ?></div>
            </div>
        </div>
        
        <!-- Report Type Cards -->
        <div class="report-cards">
            <!-- Laporan Transaksi -->
            <a href="jenis_laporan.php?type=transaksi" class="report-card">
                <div class="report-card-icon">
                    <svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
                <h3 class="report-card-title">Laporan Transaksi</h3>
                <p class="report-card-desc">Melihat semua riwayat transaksi yang masuk, status pesanan, dan pembayaran pelanggan.</p>
                <div class="report-card-stats">
                    <div class="stat-item">
                        <span class="stat-label">Total</span>
                        <span class="stat-value"><?= $total_transaksi ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Bulan Ini</span>
                        <span class="stat-value"><?= mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pesanan WHERE MONTH(tanggal_pesanan) = MONTH(CURRENT_DATE())"))['total'] ?? 0 ?></span>
                    </div>
                </div>
                <div class="report-card-arrow">
                    <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </div>
            </a>
            
            <!-- Laporan Penjualan -->
            <a href="jenis_laporan.php?type=penjualan" class="report-card">
                <div class="report-card-icon">
                    <svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                </div>
                <h3 class="report-card-title">Laporan Penjualan</h3>
                <p class="report-card-desc">Analisis penjualan per produk, kategori, dan periode waktu untuk evaluasi bisnis.</p>
                <div class="report-card-stats">
                    <div class="stat-item">
                        <span class="stat-label">Pendapatan</span>
                        <span class="stat-value">Rp <?= number_format($total_penjualan/1000, 0) ?>K</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Produk</span>
                        <span class="stat-value"><?= $total_produk ?></span>
                    </div>
                </div>
                <div class="report-card-arrow">
                    <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </div>
            </a>
            
            <!-- Laporan Stok -->
            <a href="jenis_laporan.php?type=stok" class="report-card">
                <div class="report-card-icon">
                    <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                </div>
                <h3 class="report-card-title">Laporan Stok</h3>
                <p class="report-card-desc">Monitoring stok produk, produk yang hampir habis, dan status ketersediaan barang.</p>
                <div class="report-card-stats">
                    <div class="stat-item">
                        <span class="stat-label">Total Stok</span>
                        <span class="stat-value"><?= $total_stok ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Stok Rendah</span>
                        <span class="stat-value"><?= mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM produk WHERE stok < 10"))['total'] ?? 0 ?></span>
                    </div>
                </div>
                <div class="report-card-arrow">
                    <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </div>
            </a>
        </div>
    </div>
</div>
</body>
</html>