<?php
// Include konfigurasi database
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['login_petugas']) || $_SESSION['level'] != 'user') {
    header("Location: ../login.php"); exit();
}
$koneksi = getKoneksi();
$id_user = $_SESSION['id_petugas'];
$nama_user = $_SESSION['nama_petugas'];

// === LOGOUT ===
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../login.php"); exit();
}

// === HANDLE ACTION ===
// 1. Add to Cart
if (isset($_POST['add_to_cart'])) {
    $id_produk = $_POST['id_produk']; $qty = $_POST['qty'];
    $produk = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM produk WHERE id = '$id_produk'"));
    if ($produk) {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        if (isset($_SESSION['cart'][$id_produk])) $_SESSION['cart'][$id_produk]['qty'] += $qty;
        else $_SESSION['cart'][$id_produk] = ['id' => $produk['id'], 'nama' => $produk['nama_produk'], 'harga' => $produk['harga'], 'qty' => $qty];
        header("Location: dashboard_user.php?page=cart"); exit();
    }
}
// 2. Buy Now
if (isset($_POST['buy_now'])) {
    $id_produk = $_POST['id_produk']; $qty = $_POST['qty'];
    $produk = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM produk WHERE id = '$id_produk'"));
    if ($produk) {
        $_SESSION['cart'] = [];
        $_SESSION['cart'][$id_produk] = ['id' => $produk['id'], 'nama' => $produk['nama_produk'], 'harga' => $produk['harga'], 'qty' => $qty];
        header("Location: dashboard_user.php?page=checkout&total=".($produk['harga'] * $qty)); exit();
    }
}
// 3. Update Cart
if (isset($_POST['update_cart'])) {
    if (isset($_POST['qty']) && is_array($_POST['qty'])) {
        foreach ($_POST['qty'] as $id => $quantity) {
            if ($quantity > 0 && isset($_SESSION['cart'][$id])) $_SESSION['cart'][$id]['qty'] = $quantity;
        }
    }
    header("Location: dashboard_user.php?page=cart"); exit();
}
// 4. Remove / Clear
if (isset($_GET['remove'])) { $remove_id = $_GET['remove']; if (isset($_SESSION['cart'][$remove_id])) unset($_SESSION['cart'][$remove_id]); header("Location: dashboard_user.php?page=cart"); exit(); }
if (isset($_GET['clear_cart'])) { unset($_SESSION['cart']); header("Location: dashboard_user.php?page=cart"); exit(); }

// 6. Checkout Process
if (isset($_POST['checkout'])) {
    $alamat = $_POST['alamat']; $metode = $_POST['metode_pembayaran'];
    $metode_detail = $_POST['metode_detail'] ?? '-'; $kurir = $_POST['kurir'];
    $paket = $_POST['paket']; $total = $_POST['total_harga'];
    $nama_penerima = $_POST['nama_penerima']; $telepon = $_POST['telepon'];
    
    if (!empty($_SESSION['cart'])) {
        $no_invoice = "INV-" . date('Ymd') . "-" . rand(1000, 9999);
        $resi = "RESI-" . strtoupper(substr(md5(uniqid()), 0, 8));
        
        $query = "INSERT INTO pesanan (nomor_pesanan, id_pelanggan, tanggal_pesanan, total_harga, status_pesanan, status_pembayaran, metode_pembayaran, metode_pembayaran_detail, kurir, paket_pengiriman, resi_pengiriman, alamat_pengiriman, nama_penerima, telepon_penerima)
        VALUES ('$no_invoice', '$id_user', NOW(), '$total', 'pending', 'belum_bayar', '$metode', '$metode_detail', '$kurir', '$paket', '$resi', '".escape($alamat)."', '".escape($nama_penerima)."', '".escape($telepon)."')";
        
        if (mysqli_query($koneksi, $query)) {
            $id_pesanan = mysqli_insert_id($koneksi);
            foreach ($_SESSION['cart'] as $item) {
                $sub = $item['harga'] * $item['qty'];
                mysqli_query($koneksi, "INSERT INTO detail_pesanan (id_pesanan, id_produk, jumlah, subtotal) VALUES ('$id_pesanan', '{$item['id']}', '{$item['qty']}', '$sub')");
            }
            unset($_SESSION['cart']);
            header("Location: dashboard_user.php?page=detail&id=$id_pesanan"); exit();
        } else { $error_checkout = "Gagal: " . mysqli_error($koneksi); }
    }
}

// === AMBIL DATA ===
$total_orders = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM pesanan WHERE id_pelanggan = '$id_user'"));
$total_spent = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(total_harga) as total FROM pesanan WHERE id_pelanggan = '$id_user' AND status_pembayaran = 'lunas'"))['total'] ?? 0;
$active_page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard User - E Commerce</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Inter', sans-serif; background:#f5f5f5; color:#333; }
.main-container { display:flex; min-height:100vh; }
/* SIDEBAR */
.sidebar { width:260px; background:linear-gradient(180deg, #9CAF88, #8A9B76); padding:25px 20px; display:flex; flex-direction:column; position:fixed; height:100vh; overflow-y:auto; }
.sidebar-header { text-align:center; padding:15px 0; margin-bottom:25px; border-bottom:2px solid rgba(255,255,255,0.3); }
.sidebar-title { color:white; font-size:20px; font-weight:700; }
.menu-item { background:rgba(255,255,255,0.9); padding:14px 18px; margin-bottom:12px; border-radius:8px; color:#333; text-decoration:none; font-weight:500; display:flex; align-items:center; gap:10px; transition:0.2s; }
.menu-item:hover { background:white; transform:translateX(4px); }
.menu-item.active { background:white; color:#9CAF88; font-weight:700; border-left:4px solid #9CAF88; }
.menu-item svg { width:18px; height:18px; stroke:currentColor; fill:none; stroke-width:2; }
.logout-section { margin-top:auto; padding-top:20px; border-top:2px solid rgba(255,255,255,0.3); }
.logout-btn { display:flex; align-items:center; gap:10px; padding:14px 18px; background:rgba(255,255,255,0.2); color:white; border:none; border-radius:8px; cursor:pointer; font-size:14px; text-decoration:none; }
.logout-btn:hover { background:rgba(255,255,255,0.3); }
.logout-btn svg { width:18px; height:18px; fill:white; }
/* MAIN */
.main-content { flex:1; margin-left:260px; padding:30px; background:white; min-height:100vh; }
.header { display:flex; justify-content:space-between; align-items:center; margin-bottom:35px; padding-bottom:15px; border-bottom:6px solid #9CAF88; }
.header-title { font-size:22px; font-weight:700; }
.user-info { display:flex; align-items:center; gap:15px; }
.user-avatar { width:40px; height:40px; background:#9CAF88; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:600; }
.profile-link { display:flex; align-items:center; gap:6px; padding:8px 14px; background:#f0f0f0; border-radius:20px; text-decoration:none; color:#333; font-size:13px; border:1px solid #ddd; }
.profile-link:hover, .profile-link.active { background:#9CAF88; color:white; border-color:#9CAF88; }
/* GRID & CARDS */
.product-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:20px; }
.product-card { background:#fff; border:1px solid #eee; border-radius:10px; padding:15px; text-align:center; transition:0.2s; box-shadow:0 2px 5px rgba(0,0,0,0.05); }
.product-card:hover { transform:translateY(-5px); box-shadow:0 5px 15px rgba(0,0,0,0.1); }
.product-img { width:100%; height:180px; object-fit:cover; border-radius:8px; background:#eee; margin-bottom:10px; }
.btn-group { display:flex; gap:8px; margin-top:8px; }
.btn-add { background:#9CAF88; color:white; border:none; padding:8px; border-radius:5px; cursor:pointer; flex:1; }
.btn-buy { background:#FF6B6B; color:white; border:none; padding:8px; border-radius:5px; cursor:pointer; flex:1; }
/* TABLES & FORMS */
.cart-table, .order-table, .detail-table { width:100%; border-collapse:collapse; margin-bottom:20px; }
.cart-table th, .cart-table td, .order-table th, .order-table td, .detail-table th, .detail-table td { padding:12px; border-bottom:1px solid #eee; text-align:left; }
.form-group { margin-bottom:15px; }
.form-group label { display:block; margin-bottom:5px; font-weight:600; }
.form-group input, .form-group select, .form-group textarea { width:100%; padding:10px; border:1px solid #ddd; border-radius:5px; }
.btn-checkout { background:#9CAF88; color:white; padding:12px 25px; border:none; border-radius:8px; font-weight:700; cursor:pointer; width:100%; }
.btn-checkout:hover { background:#8A9B76; }
.btn-remove { background:#f44336; color:white; padding:6px 12px; border:none; border-radius:5px; cursor:pointer; text-decoration:none; font-size:12px; }
.btn-detail { background:#4A90E2; color:white; padding:6px 12px; border:none; border-radius:5px; cursor:pointer; text-decoration:none; font-size:12px; }
/* TRACKING TIMELINE */
.tracking-box { background:#f8f9fa; border:1px solid #ddd; border-radius:10px; padding:20px; margin:20px 0; }
.timeline { display:flex; justify-content:space-between; position:relative; margin:20px 0; }
.timeline::before { content:''; position:absolute; top:15px; left:0; right:0; height:3px; background:#ddd; z-index:0; }
.step { position:relative; z-index:1; text-align:center; width:20%; }
.step-icon { width:30px; height:30px; background:#ddd; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 8px; color:white; font-weight:bold; transition:0.3s; }
.step.active .step-icon { background:#9CAF88; }
.step.completed .step-icon { background:#4CAF50; }
.step-label { font-size:11px; color:#666; font-weight:500; }
.payment-info-box { background:white; padding:15px; border-radius:8px; border:1px dashed #9CAF88; margin:15px 0; }
.hidden { display:none; }
@keyframes fadeIn { from{opacity:0; transform:translateY(5px);} to{opacity:1; transform:translateY(0);} }

/* PRINT STYLES - IMPROVED */
@media print {
    body { background: white !important; }
    .sidebar, .header, .btn-checkout, .profile-link, .logout-section, .no-print { 
        display: none !important; 
    }
    .main-container { display: block !important; }
    .main-content { 
        margin: 0 !important; 
        padding: 20px !important; 
        background: white !important;
        min-height: auto !important;
    }
    .tracking-box, .detail-table {
        border: 1px solid #ddd !important;
        page-break-inside: avoid !important;
    }
    .tracking-box { background: #f8f9fa !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    h3, h4 { color: #333 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
}

@media (max-width:768px){ .main-container{flex-direction:column;} .sidebar{width:100%; height:auto; position:relative;} .main-content{margin-left:0;} .header{flex-direction:column; align-items:flex-start; gap:15px;} .product-grid{grid-template-columns:repeat(2,1fr);} }
</style>
</head>
<body>
<div class="main-container">
    <div class="sidebar">
        <div class="sidebar-header"><h1 class="sidebar-title">Toko Online</h1></div>
        <a href="dashboard_user.php?page=home" class="menu-item <?= $active_page=='home'?'active':'' ?>"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg> Produk</a>
        <a href="dashboard_user.php?page=cart" class="menu-item <?= $active_page=='cart'?'active':'' ?>"><svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg> Keranjang (<span id="cart-count"><?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?></span>)</a>
        <a href="dashboard_user.php?page=orders" class="menu-item <?= $active_page=='orders'?'active':'' ?>"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg> Riwayat Pesanan</a>
        <div class="logout-section">
            <a href="../auth/login.php" class="logout-btn">
                <svg viewBox="0 0 24 24"><path d="M17 16l4-4-4-4M3 12h18M21 3v18H3V3" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
                Logout
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <h2 class="header-title"><?= ucfirst($active_page == 'detail' ? 'Detail & Tracking Pesanan' : $active_page) ?></h2>
            <div class="user-info">
                <a href="profile_user.php" class="profile-link <?= $active_page=='profile'?'active':'' ?>"><svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg> Profile</a>
                <div class="user-avatar"><?= strtoupper(substr($nama_user, 0, 1)) ?></div>
                <span><?= htmlspecialchars($nama_user) ?></span>
            </div>
        </div>

        <?php if ($active_page == 'home'): ?>
        <div class="stats-row" style="display:flex; gap:20px; margin-bottom:30px; flex-wrap:wrap;">
            <div style="background:#E0E0E0; padding:20px; border-radius:12px; flex:1; min-width:150px;"><span>Total Pesanan</span><div style="font-size:28px; font-weight:700;"><?= $total_orders ?></div></div>
            <div style="background:#E0E0E0; padding:20px; border-radius:12px; flex:1; min-width:150px;"><span>Total Belanja</span><div style="font-size:28px; font-weight:700;">Rp <?= number_format($total_spent,0,',','.') ?></div></div>
        </div>
        <div class="product-grid">
            <?php $produk_query = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY id DESC"); while($p = mysqli_fetch_assoc($produk_query)): ?>
            <div class="product-card">
                <img src="../uploads/<?= $p['gambar'] ?? 'default.jpg' ?>" class="product-img">
                <div style="font-weight:600; height:40px; overflow:hidden;"><?= htmlspecialchars($p['nama_produk']) ?></div>
                <div style="color:#9CAF88; font-weight:700; margin:5px 0;">Rp <?= number_format($p['harga'],0,',','.') ?></div>
                <form method="POST"><input type="hidden" name="id_produk" value="<?= $p['id'] ?>"><input type="hidden" name="qty" value="1">
                <div class="btn-group"><button type="submit" name="add_to_cart" class="btn-add">🛒 Keranjang</button><button type="submit" name="buy_now" class="btn-buy">⚡ Beli</button></div>
                </form>
            </div>
            <?php endwhile; ?>
        </div>

        <?php elseif ($active_page == 'cart'): ?>
        <?php if (empty($_SESSION['cart'])): ?><p>Keranjang kosong.</p><a href="?page=home" style="color:#9CAF88;">Kembali Belanja</a>
        <?php else: ?>
        <form method="POST">
            <table class="cart-table"><thead><tr><th>Produk</th><th>Harga</th><th>Qty</th><th>Subtotal</th><th>Aksi</th></tr></thead><tbody>
            <?php $grand_total = 0; foreach ($_SESSION['cart'] as $id => $item): $sub = $item['harga']*$item['qty']; $grand_total+=$sub; ?>
            <tr><td><?= htmlspecialchars($item['nama']) ?></td><td>Rp <?= number_format($item['harga'],0,',','.') ?></td><td><input type="number" name="qty[<?= $id ?>]" value="<?= $item['qty'] ?>" min="1" style="width:60px;"></td><td>Rp <?= number_format($sub,0,',','.') ?></td><td><a href="?page=cart&remove=<?= $id ?>" class="btn-remove" onclick="return confirm('Hapus?')">🗑 Hapus</a></td></tr>
            <?php endforeach; ?></tbody></table>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:15px;">
                <button type="submit" name="update_cart" style="padding:10px; background:#666; color:white; border:none; border-radius:5px;">Update Cart</button>
                <button type="button" onclick="location.href='?page=checkout&total=<?= $grand_total ?>'" class="btn-checkout" style="width:auto; margin:0;">Lanjut Checkout</button>
            </div>
        </form>
        <?php endif; ?>

        <?php elseif ($active_page == 'checkout'): ?>
        <form method="POST" style="max-width:600px;" id="checkout-form">
            <div class="form-group"><label>Nama Penerima</label><input type="text" name="nama_penerima" required value="<?= htmlspecialchars($nama_user) ?>"></div>
            <div class="form-group"><label>Nomor Telepon</label><input type="text" name="telepon" required placeholder="08xxxxxxxxxx"></div>
            <div class="form-group"><label>Alamat Pengiriman</label><textarea name="alamat" rows="3" required></textarea></div>
            
            <div style="display:flex; gap:10px;">
                <div class="form-group" style="flex:1;"><label>Kurir Pengiriman</label>
                <select name="kurir" required><option value="JNE">JNE</option><option value="J&T">J&T Express</option><option value="SiCepat">SiCepat</option><option value="POS">POS Indonesia</option></select></div>
                <div class="form-group" style="flex:1;"><label>Paket Pengiriman</label>
                <select name="paket" required><option value="Reguler (2-4 Hari)">Reguler (2-4 Hari)</option><option value="Kilat (1 Hari)">Kilat (1 Hari)</option><option value="Same Day (4 Jam)">Same Day (4 Jam)</option><option value="Kargo (3-5 Hari)">Kargo (3-5 Hari)</option></select></div>
            </div>

            <div class="form-group"><label>Metode Pembayaran</label>
            <select name="metode_pembayaran" id="metode_pembayaran" required onchange="togglePaymentDetails()">
                <option value="">-- Pilih Metode --</option><option value="Transfer Bank">Transfer Bank</option><option value="E-Wallet">E-Wallet</option><option value="QRIS">QRIS</option><option value="COD">COD (Bayar di Tempat)</option>
            </select></div>

            <div class="form-group hidden" id="detail_payment_box"><label>Pilih Rekening / E-Wallet</label>
            <select name="metode_detail" id="metode_detail"></select></div>

            <div class="form-group"><label>Total Bayar</label><input type="text" value="Rp <?= number_format($_GET['total'],0,',','.') ?>" readonly style="background:#eee; font-weight:bold;"><input type="hidden" name="total_harga" value="<?= $_GET['total'] ?>"></div>

            <button type="button" id="confirm-pay-btn" class="btn-checkout" style="background:#555;" onclick="showPaymentPreview()">🔒 Konfirmasi Metode Pembayaran</button>
            <div id="payment-preview" class="hidden payment-info-box"></div>
            <button type="submit" name="checkout" id="final-submit-btn" class="hidden btn-checkout" style="margin-top:10px;">✅ Buat Pesanan & Bayar</button>
        </form>

        <?php elseif ($active_page == 'orders'): ?>
        <table class="order-table"><thead><tr><th>No Invoice</th><th>Tanggal</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
        <?php $orders = mysqli_query($koneksi, "SELECT * FROM pesanan WHERE id_pelanggan='$id_user' ORDER BY id DESC"); while($o = mysqli_fetch_assoc($orders)): ?>
        <tr><td><?= $o['nomor_pesanan'] ?></td><td><?= $o['tanggal_pesanan'] ?></td><td>Rp <?= number_format($o['total_harga'],0,',','.') ?></td>
        <td><span style="padding:4px 8px; border-radius:4px; background:<?= $o['status_pembayaran']=='lunas'?'#d4edda':'#fff3cd' ?>; color:<?= $o['status_pembayaran']=='lunas'?'#155724':'#856404' ?>; font-size:12px;"><?= $o['status_pembayaran'] ?></span></td>
        <td><a href="?page=detail&id=<?= $o['id'] ?>" class="btn-detail">📦 Detail & Tracking</a></td></tr>
        <?php endwhile; ?></tbody></table>

        <?php elseif ($active_page == 'detail' && isset($_GET['id'])): ?>
        <?php $det = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pesanan WHERE id='{$_GET['id']}' AND id_pelanggan='$id_user'")); if($det): 
            $details_prod = mysqli_query($koneksi, "SELECT dp.*, p.nama_produk FROM detail_pesanan dp JOIN produk p ON dp.id_produk=p.id WHERE dp.id_pesanan='{$det['id']}'");
            $status_map = ['pending' => 0, 'diproses' => 1, 'dikirim' => 2, 'selesai' => 3, 'batal' => 4];
            $current_step = $status_map[$det['status_pesanan']] ?? 0;
        ?>
        <div style="max-width:800px; margin:0 auto;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h3>📦 Detail & Tracking Pesanan</h3>
                <a href="?page=orders" style="color:#666; text-decoration:none;">← Kembali</a>
            </div>
            
            <div class="tracking-box">
                <h4 style="margin-bottom:15px;">Status Pengiriman: <span style="color:#9CAF88; text-transform:uppercase;"><?= $det['status_pesanan'] ?></span></h4>
                <div class="timeline">
                    <div class="step <?= $current_step>=0?'completed':'' ?> <?= $current_step==0?'active':'' ?>"><div class="step-icon">1</div><div class="step-label">Dipesan</div></div>
                    <div class="step <?= $current_step>=1?'completed':'' ?> <?= $current_step==1?'active':'' ?>"><div class="step-icon">2</div><div class="step-label">Diproses</div></div>
                    <div class="step <?= $current_step>=2?'completed':'' ?> <?= $current_step==2?'active':'' ?>"><div class="step-icon">3</div><div class="step-label">Dikirim</div></div>
                    <div class="step <?= $current_step>=3?'completed':'' ?> <?= $current_step==3?'active':'' ?>"><div class="step-icon">4</div><div class="step-label">Diterima</div></div>
                </div>
                <div style="display:flex; gap:15px; flex-wrap:wrap; margin-top:15px; font-size:13px;">
                    <div><strong>Kurir:</strong> <?= $det['kurir'] ?></div>
                    <div><strong>Paket:</strong> <?= $det['paket_pengiriman'] ?></div>
                    <div><strong>Resi:</strong> <?= $det['resi_pengiriman'] ?></div>
                </div>
            </div>

            <div style="background:white; padding:20px; border:1px solid #eee; border-radius:8px; margin-bottom:20px;">
                <h4>📄 Informasi Transaksi</h4>
                <table class="detail-table">
                    <tr><td style="width:150px;"><strong>No Invoice</strong></td><td><?= $det['nomor_pesanan'] ?></td></tr>
                    <tr><td><strong>Tanggal</strong></td><td><?= $det['tanggal_pesanan'] ?></td></tr>
                    <tr><td><strong>Penerima</strong></td><td><?= htmlspecialchars($det['nama_penerima']) ?> (<?= $det['telepon_penerima'] ?>)</td></tr>
                    <tr><td><strong>Alamat</strong></td><td><?= nl2br(htmlspecialchars($det['alamat_pengiriman'])) ?></td></tr>
                    <tr><td><strong>Pembayaran</strong></td><td><?= $det['metode_pembayaran'] ?> <?= $det['metode_pembayaran_detail'] != '-' ? '('.$det['metode_pembayaran_detail'].')' : '' ?></td></tr>
                    <tr><td><strong>Status Bayar</strong></td><td><?= $det['status_pembayaran'] ?></td></tr>
                </table>
                <table class="detail-table" style="margin-top:15px;"><thead><tr><th>Produk</th><th>Qty</th><th>Subtotal</th></tr></thead><tbody>
                <?php while($dp = mysqli_fetch_assoc($details_prod)): ?>
                <tr><td><?= htmlspecialchars($dp['nama_produk']) ?></td><td><?= $dp['jumlah'] ?></td><td>Rp <?= number_format($dp['subtotal'],0,',','.') ?></td></tr>
                <?php endwhile; ?>
                <tr><td colspan="2" style="text-align:right; font-weight:700;">Total</td><td style="font-weight:700; color:#9CAF88;">Rp <?= number_format($det['total_harga'],0,',','.') ?></td></tr>
                </tbody></table>
            </div>
            <button onclick="window.print()" class="btn-checkout" style="background:#333;">🖨 Cetak / Simpan Bukti</button>
        </div>
        <?php else: ?><p>Data tidak ditemukan.</p> <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// Dynamic Bank/E-Wallet Dropdown
function togglePaymentDetails() {
    const main = document.getElementById('metode_pembayaran').value;
    const box = document.getElementById('detail_payment_box');
    const sel = document.getElementById('metode_detail');
    sel.innerHTML = '<option value="-">-</option>';
    box.classList.add('hidden');
    
    if(main === 'Transfer Bank') {
        box.classList.remove('hidden');
        ['BCA - 1234567890 (A.N. Toko Online)', 'BNI - 0987654321 (A.N. Toko Online)', 'BRI - 1122334455 (A.N. Toko Online)', 'Mandiri - 9988776655 (A.N. Toko Online)'].forEach(v => sel.innerHTML += `<option value="${v.split(' - ')[0]}">${v}</option>`);
    } else if(main === 'E-Wallet') {
        box.classList.remove('hidden');
        ['GoPay - 081234567890', 'OVO - 089876543210', 'DANA - 085678901234', 'ShopeePay - 082345678901'].forEach(v => sel.innerHTML += `<option value="${v.split(' - ')[0]}">${v}</option>`);
    }
}

// Payment Preview & Flow
function showPaymentPreview() {
    const method = document.getElementById('metode_pembayaran').value;
    const detail = document.getElementById('metode_detail').value;
    const box = document.getElementById('payment-preview');
    const btn = document.getElementById('confirm-pay-btn');
    const sub = document.getElementById('final-submit-btn');
    
    if(!method) { alert('Pilih metode pembayaran!'); return; }
    if((method === 'Transfer Bank' || method === 'E-Wallet') && detail === '-') { alert('Pilih detail rekening/e-wallet!'); return; }

    let html = '';
    const total = 'Rp <?= number_format($_GET['total'],0,',','.') ?>';
    if(method === 'QRIS') html = `<h4>💳 QRIS</h4><p>Scan QR di aplikasi pembayaran Anda.</p><div style="width:120px;height:120px;background:#eee;margin:10px auto;display:flex;align-items:center;justify-content:center;">QR Code</div><p>Total: ${total}</p>`;
    else if(method === 'COD') html = `<h4>📦 COD</h4><p>Bayar tunai saat barang sampai. Pastikan alamat & telepon aktif.</p>`;
    else html = `<h4>📱 ${method}</h4><p><strong>Ke:</strong> ${detail}</p><p><strong>Total:</strong> ${total}</p><p style="font-size:12px;color:#666;">Harap transfer sesuai nominal.</p>`;
    
    box.innerHTML = html;
    box.classList.remove('hidden');
    btn.classList.add('hidden');
    sub.classList.remove('hidden');
}
</script>
</body>
</html>