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

// === AMBIL DATA DARI DATABASE ===
$total_user = getTotal('petugas');
$total_produk = getTotal('produk');
$total_payment = getTotal('pesanan', "status_pembayaran = 'lunas'");
$total_orders = getTotal('pesanan');
$total_customer = getTotal('pelanggan');

// Format tanggal backup
$tanggal_backup = formatTanggal(date('Y-m-d'));

// Halaman aktif (untuk highlight menu)
$active_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Petugas - Admin Panel</title>
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
            background-color: white;
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

        .welcome-text {
            color: #ff0000;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 35px;
            text-align: center;
            letter-spacing: 1px;
        }

        /* STATS CARDS */
        .stats-row {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .stat-card {
            background-color: #E0E0E0;
            padding: 22px 25px;
            border-radius: 12px;
            min-width: 170px;
            flex: 1;
            max-width: 220px;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .stat-card-header {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            gap: 10px;
        }

        .stat-icon {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon svg {
            width: 100%;
            height: 100%;
            stroke: #333;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .stat-label {
            font-size: 13px;
            color: #333;
            font-weight: 500;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #333;
        }

        .backup-info {
            font-size: 14px;
            color: #555;
            margin: 25px 0;
            font-weight: 500;
            padding: 12px 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            border-left: 4px solid #9CAF88;
        }

        .backup-info strong {
            color: #333;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 220px;
            }
            .main-content {
                margin-left: 220px;
            }
        }

        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 15px;
            }
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            .stats-row {
                flex-direction: column;
            }
            .stat-card {
                max-width: 100%;
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
                <a href="../auth/logout.php" class="logout-btn">
                    <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    Logout
                </a>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <div class="header">
                <h2 class="header-title">Dashboard</h2>
                <div class="user-info">
                    <div class="user-avatar"><?= strtoupper(substr($_SESSION['nama_petugas'], 0, 1)) ?></div>
                    <span class="user-name"><?= htmlspecialchars($_SESSION['nama_petugas']) ?> (<?= ucfirst($_SESSION['level']) ?>)</span>
                </div>
            </div>

            <div class="welcome-text">WELCOME TO PETUGAS</div>

            <div class="stats-row">
                <!-- Total Produk -->
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon">
                            <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                        </div>
                        <span class="stat-label">Total Produk</span>
                    </div>
                    <span class="stat-value"><?= number_format($total_produk, 0, ',', '.') ?></span>
                </div>

                <!-- Total Payment -->
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon">
                            <svg viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                        </div>
                        <span class="stat-label">Total payment</span>
                    </div>
                    <span class="stat-value"><?= number_format($total_payment, 0, ',', '.') ?></span>
                </div>

                <!-- Orders -->
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon">
                            <svg viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                        </div>
                        <span class="stat-label">orders</span>
                    </div>
                    <span class="stat-value"><?= number_format($total_orders, 0, ',', '.') ?></span>
                </div>
            </div>

            <div class="backup-info">
                Backup Terakhir: <strong><?= $tanggal_backup ?></strong>
            </div>

            <div class="stats-row">
                <!-- Customer -->
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon">
                            <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
                        <span class="stat-label">costomer</span>
                    </div>
                    <span class="stat-value"><?= number_format($total_customer, 0, ',', '.') ?></span>
                </div>

                <!-- Total User (Petugas) -->
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon">
                            <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        </div>
                        <span class="stat-label">Total Petugas</span>
                    </div>
                    <span class="stat-value"><?= number_format($total_user, 0, ',', '.') ?></span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>