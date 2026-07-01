<?php
session_start();
include __DIR__ . '/admin/config.php';

/* =========================
   WAJIB LOGIN CUSTOMER
========================= */
if (!isset($_SESSION['customer_id'])) {
    header("Location: auth.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'] ?? 'Guest';

/* =========================
   AMBIL DATA KERANJANG DARI DB
========================= */
$sql_cart = "SELECT c.qty, p.id, p.nama_hp, p.harga, p.stok, p.brand 
             FROM cart c 
             JOIN phones p ON c.phone_id = p.id 
             WHERE c.customer_id = $customer_id";
$res_cart = $conn->query($sql_cart);

if ($res_cart->num_rows == 0) {
    header("Location: index.php");
    exit;
}

$items = [];
$grandTotal = 0;
$wa_admin = "6285862030566";

while ($row = $res_cart->fetch_assoc()) {
    $qty = intval($row['qty']);
    if ($qty > $row['stok']) $qty = $row['stok'];
    if ($qty <= 0) continue;

    $total = $row['harga'] * $qty;
    $grandTotal += $total;

    $items[] = [
        'id' => $row['id'],
        'nama' => $row['nama_hp'],
        'brand' => $row['brand'],
        'harga' => $row['harga'],
        'qty' => $qty,
        'total' => $total
    ];
}

/* =========================
   AMBIL DATA CUSTOMER
========================= */
$user = $conn->query("SELECT * FROM customers WHERE id = $customer_id")->fetch_assoc();

/* =========================
   PROSES CHECKOUT
========================= */
if (isset($_POST['checkout'])) {
    $nama   = htmlspecialchars($_POST['nama']);
    $wa     = htmlspecialchars($_POST['no_wa']);
    $alamat = htmlspecialchars($_POST['alamat']);
    $metode = $_POST['metode'];

    $bukti = '';
    if (!empty($_FILES['bukti']['name'])) {
        $ext = strtolower(pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png'];

        if (in_array($ext, $allowed)) {
            $bukti = uniqid() . '.' . $ext;
            $uploadDir = __DIR__ . "/admin/uploads/bukti_bayar/";

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            move_uploaded_file($_FILES['bukti']['tmp_name'], $uploadDir . $bukti);
        }
    }

    // 1. Simpan Pesanan ke Tabel Orders
    $stmt = $conn->prepare("INSERT INTO orders (customer_id, nama, alamat, no_wa, total, metode, bukti, status) VALUES (?,?,?,?,?,?,?, 'pending')");
    $stmt->bind_param("isssiss", $customer_id, $nama, $alamat, $wa, $grandTotal, $metode, $bukti);
    $stmt->execute();
    $order_id = $conn->insert_id;

    // 2. Simpan/Perbarui Alamat & Kontak ke Tabel Customers (Auto-Save Profil)
    $stmtUpdate = $conn->prepare("UPDATE customers SET nama_lengkap=?, no_wa=?, alamat=? WHERE id=?");
    $stmtUpdate->bind_param("sssi", $nama, $wa, $alamat, $customer_id);
    $stmtUpdate->execute();

    // 3. Masukkan Detail Barang ke Order Items dan Kurangi Stok
    foreach ($items as $item) {
        $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, phone_id, qty, harga) VALUES (?,?,?,?)");
        $stmtItem->bind_param("iiii", $order_id, $item['id'], $item['qty'], $item['harga']);
        $stmtItem->execute();
        $conn->query("UPDATE phones SET stok = stok - {$item['qty']} WHERE id = {$item['id']}");
    }

    // 4. Kosongkan Keranjang Belanja
    $conn->query("DELETE FROM cart WHERE customer_id = $customer_id");

    // 5. Buat Pesan WhatsApp
    $pesan = "*PESANAN BARU - #INV$order_id*\n\n";
    $pesan .= "Nama: $nama\n";
    $pesan .= "Metode: " . strtoupper($metode) . "\n";
    $pesan .= "Total: Rp " . number_format($grandTotal,0,',','.') . "\n\n";
    $pesan .= "Detail Barang:\n";
    foreach($items as $i) { $pesan .= "- {$i['nama']} ({$i['qty']}x)\n"; }

    $link_wa = "https://wa.me/$wa_admin?text=" . urlencode($pesan);

    // Redirect ke Halaman Sukses
    header("Location: success.php?wa=".urlencode($link_wa));
    exit;
}

// Ambil total item di keranjang untuk badge Navbar
$cartCountRes = $conn->query("SELECT SUM(qty) as total FROM cart WHERE customer_id = $customer_id");
$total_cart = ($cartCountRes && $cartCountRes->num_rows > 0) ? $cartCountRes->fetch_assoc()['total'] : 0;
if (!$total_cart) $total_cart = 0;
?>

<!DOCTYPE html>
<html class="dark" lang="id">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no" name="viewport"/>
<title>Checkout | PhoneStore Premium</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-tertiary-container": "#eef0ff",
                        "outline": "#8d90a0",
                        "inverse-on-surface": "#2d3133",
                        "surface-container": "#1d2022",
                        "secondary-fixed": "#6ffbbe",
                        "on-primary-fixed": "#00174b",
                        "primary": "#b4c5ff",
                        "on-secondary-fixed": "#002113",
                        "on-tertiary-fixed-variant": "#3f465c",
                        "background": "#101415",
                        "outline-variant": "#434655",
                        "on-background": "#e0e3e5",
                        "tertiary-fixed": "#dae2fd",
                        "primary-container": "#2563eb",
                        "primary-fixed-dim": "#b4c5ff",
                        "surface-bright": "#363a3b",
                        "on-surface": "#e0e3e5",
                        "on-primary-fixed-variant": "#003ea8",
                        "inverse-primary": "#0053db",
                        "tertiary-fixed-dim": "#bec6e0",
                        "surface-container-lowest": "#0b0f10",
                        "on-secondary": "#003824",
                        "secondary-container": "#00a572",
                        "error": "#ffb4ab",
                        "surface-container-highest": "#323537",
                        "secondary-fixed-dim": "#4edea3",
                        "secondary": "#4edea3",
                        "tertiary": "#bec6e0",
                        "error-container": "#93000a",
                        "surface": "#101415",
                        "on-tertiary-fixed": "#131b2e",
                        "on-surface-variant": "#c3c6d7",
                        "on-primary": "#002a78",
                        "on-error": "#690005",
                        "inverse-surface": "#e0e3e5",
                        "on-primary-container": "#eeefff",
                        "surface-container-high": "#272a2c",
                        "surface-variant": "#323537",
                        "primary-fixed": "#dbe1ff",
                        "on-error-container": "#ffdad6",
                        "on-tertiary": "#283044",
                        "surface-tint": "#b4c5ff",
                        "tertiary-container": "#656d84",
                        "on-secondary-fixed-variant": "#005236",
                        "on-secondary-container": "#00311f",
                        "surface-container-low": "#191c1e",
                        "surface-dim": "#101415"
                    },
                    "fontFamily": {
                        "body-md": ["Plus Jakarta Sans"],
                        "body-sm": ["Plus Jakarta Sans"],
                        "label-caps": ["JetBrains Mono"],
                        "display-lg": ["Plus Jakarta Sans"],
                        "headline-lg": ["Plus Jakarta Sans"],
                        "headline-lg-mobile": ["Plus Jakarta Sans"]
                    }
                },
            },
        }
    </script>
<style>
    body {
      background-color: #0A0C10;
      color: #e0e3e5;
      -webkit-font-smoothing: antialiased;
    }
    .glass-card {
        background: rgba(29, 32, 34, 0.6);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(141, 144, 160, 0.2);
    }
    .page-glow {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: radial-gradient(circle at 50% -20%, rgba(37, 99, 235, 0.08) 0%, transparent 50%),
                    radial-gradient(circle at 0% 100%, rgba(16, 185, 129, 0.05) 0%, transparent 30%);
        pointer-events: none;
        z-index: -1;
    }
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #434655; border-radius: 10px; }
</style>
</head>
<body class="font-body-md text-body-md selection:bg-primary-container selection:text-on-primary-container min-h-screen flex flex-col overflow-x-hidden">
<div class="page-glow"></div>

<header class="fixed top-0 z-50 w-full bg-background/80 backdrop-blur-xl border-b border-outline-variant/30 shadow-sm transition-all duration-300">
    <div class="flex justify-between items-center w-full px-4 md:px-12 py-3 md:py-4 max-w-[1440px] mx-auto relative">
        <div class="flex items-center gap-4 md:gap-8">
            <a class="text-lg sm:text-xl md:text-2xl font-extrabold text-white flex items-center gap-1" href="index.php">
                PhoneStore<span class="w-1.5 h-1.5 md:w-2 md:h-2 rounded-full bg-primary mb-1"></span>
            </a>
            <nav class="hidden md:flex gap-6">
                <a class="text-on-surface-variant font-medium hover:text-primary transition-colors font-label-caps text-[12px]" href="index.php">Beranda</a>
                <a class="text-on-surface-variant font-medium hover:text-primary transition-colors font-label-caps text-[12px]" href="cart.php">Keranjang</a>
                <a class="text-on-surface-variant font-medium hover:text-primary transition-colors font-label-caps text-[12px]" href="my_orders.php">Pesanan Saya</a>
            </nav>
        </div>
        
        <div class="flex items-center gap-2 md:gap-4">
            <div class="hidden lg:flex flex-col items-end mr-2">
                <span class="font-label-caps text-[10px] text-on-surface-variant">WELCOME BACK,</span>
                <span class="font-body-md text-[14px] font-bold text-white"><?= htmlspecialchars(strtoupper(explode(' ', $user['nama_lengkap'] ?? $customer_name)[0])) ?></span>
            </div>
            
            <a href="profile.php" class="hidden md:flex material-symbols-outlined text-on-surface-variant hover:text-primary p-2 rounded-full hover:bg-surface-variant/50 transition-all scale-95 active:scale-90" title="Profile">person</a>
            <a href="cart.php" class="hidden md:flex material-symbols-outlined text-on-surface-variant hover:text-primary p-2 rounded-full hover:bg-surface-variant/50 transition-all scale-95 active:scale-90 relative" title="Cart">
                shopping_cart
                <span id="cartBadge" class="<?= $total_cart > 0 ? '' : 'hidden' ?> absolute top-0 right-0 bg-primary text-white text-[10px] w-4 h-4 md:w-5 md:h-5 rounded-full flex items-center justify-center font-bold shadow-lg <?= $total_cart > 0 ? 'animate-bounce' : '' ?>"><?= $total_cart ?></span>
            </a>
            <a href="?logout=true" class="hidden md:flex material-symbols-outlined text-on-surface-variant hover:text-error p-2 rounded-full transition-all" title="Logout">logout</a>

            <button id="mobileMenuBtn" class="md:hidden p-2 rounded-lg hover:bg-surface-variant/50 transition-all active:scale-90 text-white relative">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                <span id="mobileCartBadge" class="<?= $total_cart > 0 ? '' : 'hidden' ?> absolute -top-1 -right-1 bg-primary text-white text-[9px] w-4 h-4 rounded-full flex items-center justify-center font-bold shadow-lg"><?= $total_cart ?></span>
            </button>
        </div>
    </div>

    <div id="mobileMenu" class="hidden absolute top-full left-0 w-full bg-background/95 backdrop-blur-xl border-b border-outline-variant/30 shadow-2xl flex-col origin-top transition-all duration-300 max-h-[80vh] overflow-y-auto">
        <div class="px-5 py-6 flex flex-col gap-5">
            <div class="flex items-center gap-3 border-b border-outline-variant/30 pb-5">
                <div class="w-12 h-12 rounded-full bg-primary/20 text-primary flex items-center justify-center font-bold text-xl shadow-lg shrink-0">
                    <?= htmlspecialchars(substr(strtoupper($user['nama_lengkap'] ?? $customer_name), 0, 1)) ?>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="font-label-caps text-[10px] text-on-surface-variant uppercase tracking-wider">Welcome,</p>
                    <p class="text-base font-bold text-white truncate break-words"><?= htmlspecialchars($user['nama_lengkap'] ?? $customer_name) ?></p>
                </div>
            </div>
            <a href="index.php" class="text-on-surface-variant hover:text-white font-medium flex items-center gap-3"><span class="material-symbols-outlined">home</span> Beranda</a>
            <a href="cart.php" class="text-on-surface-variant hover:text-white font-medium flex items-center gap-3 justify-between">
                <div class="flex items-center gap-3"><span class="material-symbols-outlined">shopping_cart</span> Keranjang</div>
                <span id="mobileMenuCartBadge" class="<?= $total_cart > 0 ? '' : 'hidden' ?> bg-primary text-white text-[10px] w-5 h-5 rounded-full flex items-center justify-center font-bold"><?= $total_cart ?></span>
            </a>
            <a href="my_orders.php" class="text-on-surface-variant hover:text-white font-medium flex items-center gap-3"><span class="material-symbols-outlined">receipt_long</span> Pesanan Saya</a>
            <a href="profile.php" class="text-on-surface-variant hover:text-white font-medium flex items-center gap-3"><span class="material-symbols-outlined">manage_accounts</span> Profil Akun</a>
            <a href="?logout=true" class="text-error hover:text-red-300 font-medium flex items-center gap-3 mt-2 pt-5 border-t border-outline-variant/30"><span class="material-symbols-outlined">logout</span> Logout</a>
        </div>
    </div>
</header>

<main class="flex-grow pt-28 pb-12">
    <form method="POST" enctype="multipart/form-data" class="relative z-10 max-w-[1440px] mx-auto px-4 md:px-12 grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-8">
        
        <div class="lg:col-span-7 space-y-6">
            <div class="flex items-center gap-2">
                <a class="flex items-center gap-1 text-on-surface-variant hover:text-primary transition-colors text-[13px] md:text-body-sm font-medium" href="cart.php">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    Kembali ke Keranjang
                </a>
            </div>
            
            <section class="glass-card rounded-3xl p-6 md:p-8">
                <div class="flex items-center gap-3 mb-6 md:mb-8">
                    <div class="p-2 bg-primary/10 rounded-xl">
                        <span class="material-symbols-outlined text-primary">local_shipping</span>
                    </div>
                    <h2 class="text-xl md:text-2xl text-white font-bold tracking-tight">Detail Pengiriman</h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">
                    <div class="space-y-2">
                        <label class="font-label-caps text-[11px] text-on-surface-variant block ml-1 uppercase tracking-widest font-bold">NAMA PENERIMA</label>
                        <input type="text" name="nama" required value="<?= htmlspecialchars($user['nama_lengkap'] ?? '') ?>" class="w-full bg-[#0D1117] border border-outline-variant/50 rounded-2xl px-5 py-3.5 text-on-surface text-sm md:text-base focus:ring-2 focus:ring-primary focus:outline-none transition-all" placeholder="Masukkan nama lengkap"/>
                    </div>
                    <div class="space-y-2">
                        <label class="font-label-caps text-[11px] text-on-surface-variant block ml-1 uppercase tracking-widest font-bold">WHATSAPP</label>
                        <input type="tel" name="no_wa" required value="<?= htmlspecialchars($user['no_wa'] ?? '') ?>" class="w-full bg-[#0D1117] border border-outline-variant/50 rounded-2xl px-5 py-3.5 text-on-surface text-sm md:text-base focus:ring-2 focus:ring-primary focus:outline-none transition-all" placeholder="Contoh: 0812xxxx"/>
                    </div>
                    <div class="md:col-span-2 space-y-2">
                        <label class="font-label-caps text-[11px] text-on-surface-variant block ml-1 uppercase tracking-widest font-bold">ALAMAT PENGIRIMAN</label>
                        <textarea name="alamat" required rows="3" class="w-full bg-[#0D1117] border border-outline-variant/50 rounded-2xl px-5 py-3.5 text-on-surface text-sm md:text-base focus:ring-2 focus:ring-primary focus:outline-none transition-all resize-none" placeholder="Alamat lengkap, nomor rumah, kelurahan, kecamatan"><?= htmlspecialchars($user['alamat'] ?? '') ?></textarea>
                    </div>
                </div>
            </section>
            
            <section class="glass-card rounded-3xl p-6 md:p-8">
                <div class="flex items-center gap-3 mb-6 md:mb-8">
                    <div class="p-2 bg-secondary/10 rounded-xl">
                        <span class="material-symbols-outlined text-secondary">account_balance_wallet</span>
                    </div>
                    <h2 class="text-xl md:text-2xl text-white font-bold tracking-tight">Metode Pembayaran</h2>
                </div>
                
                <div class="space-y-6">
                    <select name="metode" id="paymentSelector" onchange="updatePaymentArea()" class="w-full bg-[#0D1117] border border-outline-variant/50 rounded-2xl px-5 py-4 text-on-surface text-sm md:text-base font-bold focus:ring-2 focus:ring-primary outline-none cursor-pointer">
                        <option value="bank">Transfer Bank (SeaBank)</option>
                        <option value="qris">QRIS (OVO, GoPay, Dana, LinkAja)</option>
                    </select>
                    
                    <div class="bg-surface-container-highest/30 border border-outline-variant/30 rounded-2xl p-5 md:p-6 transition-all duration-300" id="paymentInfo">
                        <div class="flex flex-col sm:flex-row items-center gap-5 text-center sm:text-left" id="seabankInfo">
                            <div class="w-20 h-20 bg-white rounded-2xl p-2 flex items-center justify-center flex-shrink-0 shadow-lg">
                                <img class="max-h-full max-w-full object-contain" src="https://images.seeklogo.com/logo-png/62/2/seabank-logo-png_seeklogo-620133.png" alt="SeaBank"/>
                            </div>
                            <div class="flex-1 space-y-1">
                                <p class="font-label-caps text-[10px] text-on-surface-variant font-bold">REKENING TRANSFER SEABANK</p>
                                <p class="text-2xl md:text-3xl font-extrabold text-primary tracking-widest" id="norekText">9017 5486 5539</p>
                                <p class="text-xs text-on-surface-variant font-medium uppercase">A/N FAISAL DWIKI NURDIANSYAH</p>
                            </div>
                            <button type="button" onclick="copyNorek()" class="bg-surface-container border border-outline-variant hover:border-primary/50 px-5 py-3 rounded-xl font-label-caps text-[11px] hover:bg-surface-bright text-primary font-bold transition-all w-full sm:w-auto active:scale-95 shadow-lg">SALIN NO. REK</button>
                        </div>
                        
                        <div class="hidden flex flex-col items-center gap-4 py-2" id="qrisInfo">
                            <div class="bg-white p-4 rounded-2xl shadow-inner">
                                <img class="w-48 h-48 object-cover rounded-lg" src="qris.jpeg" alt="QRIS Code"/>
                            </div>
                            <p class="text-[12px] font-label-caps text-center text-on-surface-variant tracking-wider font-bold">SUPPORT ALL E-WALLET & MOBILE BANKING</p>
                        </div>
                    </div>
                    
                    <div class="space-y-2 pt-2">
                        <label class="font-label-caps text-[11px] text-on-surface-variant block ml-1 uppercase tracking-widest font-bold">UNGGAH BUKTI PEMBAYARAN</label>
                        <div class="relative group mt-2">
                            <input class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" id="proofUpload" name="bukti" required type="file" accept="image/*"/>
                            <div class="border-2 border-dashed border-outline-variant/50 group-hover:border-primary/50 group-hover:bg-primary/5 rounded-2xl p-8 flex flex-col items-center justify-center transition-all" id="uploadWrapper">
                                <span class="material-symbols-outlined text-primary mb-3" style="font-size: 48px;">cloud_upload</span>
                                <p class="text-sm md:text-base font-bold text-white mb-1" id="uploadTitle">Pilih File Bukti Bayar</p>
                                <p class="text-xs text-on-surface-variant" id="uploadSubtitle">Format berkas: JPG atau PNG (Maks. 5MB)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        
        <aside class="lg:col-span-5">
            <div class="sticky top-[100px] glass-card rounded-3xl p-6 md:p-8 overflow-hidden">
                <div class="absolute -top-12 -right-12 w-48 h-48 bg-primary/20 blur-[80px] rounded-full pointer-events-none"></div>
                
                <h3 class="text-xl md:text-2xl text-white font-bold mb-6 relative z-10">Ringkasan Pesanan</h3>
                
                <div class="space-y-4 max-h-[280px] overflow-y-auto pr-2 custom-scrollbar mb-8 relative z-10">
                    <?php foreach ($items as $i): 
                        $imgRes = $conn->query("SELECT image FROM phone_images WHERE phone_id=".$i['id']." LIMIT 1")->fetch_assoc();
                        $gambar = ($imgRes && file_exists(__DIR__."/admin/uploads/".$imgRes['image'])) 
                                  ? "admin/uploads/".$imgRes['image'] : "https://via.placeholder.com/200";
                    ?>
                    <div class="flex gap-4 border-b border-outline-variant/20 pb-4 last:border-none last:pb-0">
                        <div class="w-20 h-20 rounded-xl bg-white/5 border border-white/10 flex-shrink-0 overflow-hidden p-2 flex items-center justify-center">
                            <img class="max-h-full max-w-full object-contain drop-shadow-lg" src="<?= $gambar ?>"/>
                        </div>
                        <div class="flex-1 flex flex-col justify-center">
                            <p class="text-sm md:text-base font-bold text-white line-clamp-2"><?= htmlspecialchars($i['nama']) ?></p>
                            <div class="flex justify-between items-center mt-2">
                                <p class="text-xs md:text-sm text-on-surface-variant font-medium">Qty: <?= $i['qty'] ?></p>
                                <p class="text-sm md:text-base font-bold text-primary">Rp <?= number_format($i['total'], 0, ',', '.') ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="space-y-4 pt-4 border-t border-outline-variant/30 text-sm md:text-base relative z-10">
                    <div class="flex justify-between text-on-surface-variant">
                        <span>Subtotal Produk</span>
                        <span class="font-medium text-white">Rp <?= number_format($grandTotal, 0, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-on-surface-variant">Pengiriman</span>
                        <span class="bg-secondary/10 text-secondary border border-secondary/20 px-3 py-1 rounded-full font-label-caps text-[10px] font-bold">GRATIS ONGKIR</span>
                    </div>
                </div>
                
                <div class="pt-6 mt-6 border-t border-outline-variant/50 relative z-10">
                    <div class="flex justify-between items-end">
                        <span class="font-label-caps text-[11px] md:text-xs text-on-surface-variant pb-1 font-bold">TOTAL PEMBAYARAN</span>
                        <span class="text-2xl md:text-3xl lg:text-4xl font-extrabold text-primary leading-none tracking-tighter">Rp <?= number_format($grandTotal, 0, ',', '.') ?></span>
                    </div>
                </div>
                
                <button type="submit" name="checkout" class="w-full bg-primary-container hover:bg-blue-600 text-white font-bold py-4 rounded-2xl transition-all flex items-center justify-center gap-2 mt-8 active:scale-[0.98] shadow-lg shadow-primary-container/20 text-sm md:text-base uppercase tracking-wider">
                    <span>Konfirmasi & Bayar</span>
                    <span class="material-symbols-outlined text-[20px]">payments</span>
                </button>
                
                <div class="flex items-center justify-center gap-1.5 text-on-surface-variant/60 mt-5">
                    <span class="material-symbols-outlined text-sm" style="font-size: 16px;">lock</span>
                    <span class="font-label-caps text-[10px] md:text-[11px] uppercase tracking-widest font-bold">Enkripsi 256-bit SSL</span>
                </div>
            </div>
        </aside>
    </form>
</main>

<footer class="w-full mt-auto bg-surface-container-lowest border-t border-outline-variant/30 relative z-10">
    <div class="flex flex-col items-center gap-6 px-4 py-8 md:py-12 w-full max-w-[1440px] mx-auto">
        <div class="w-full flex flex-col md:flex-row justify-between items-center gap-5 md:gap-6 border-b border-outline-variant/20 pb-6 md:pb-8">
            <span class="font-label-caps text-label-caps text-on-surface text-sm md:text-xl tracking-widest font-bold text-center md:text-left">PHONESTORE PREMIUM</span>
            <div class="flex flex-wrap justify-center gap-4 md:gap-8">
                <a class="font-body-sm text-[10px] md:text-sm text-on-surface-variant hover:text-primary transition-colors duration-300 uppercase tracking-wider" href="#">Privacy Policy</a>
                <a class="font-body-sm text-[10px] md:text-sm text-on-surface-variant hover:text-primary transition-colors duration-300 uppercase tracking-wider" href="#">Terms of Service</a>
                <a class="font-body-sm text-[10px] md:text-sm text-on-surface-variant hover:text-primary transition-colors duration-300 uppercase tracking-wider" href="#">Contact Support</a>
            </div>
        </div>
        <div class="text-on-surface-variant text-[10px] md:text-xs text-center">
            © 2026 PhoneStore Premium. All rights reserved.
        </div>
    </div>
</footer>

<script>
    // Penanganan Tampilan Area Instruksi Bayar
    function updatePaymentArea() {
        const selector = document.getElementById('paymentSelector');
        const seabank = document.getElementById('seabankInfo');
        const qris = document.getElementById('qrisInfo');
        const infoBox = document.getElementById('paymentInfo');
        
        infoBox.style.opacity = 0;
        
        setTimeout(() => {
            if (selector.value === 'qris') {
                seabank.classList.add('hidden');
                qris.classList.remove('hidden');
            } else {
                seabank.classList.remove('hidden');
                qris.classList.add('hidden');
            }
            infoBox.style.transition = 'opacity 0.3s ease';
            infoBox.style.opacity = 1;
        }, 150);
    }

    // Fungsi Pintas untuk Salin Nomor Rekening
    function copyNorek() {
        const rawNorek = "901754865539";
        navigator.clipboard.writeText(rawNorek).then(() => {
            alert("Nomor rekening SeaBank berhasil disalin!");
        }).catch(err => {
            console.error("Gagal menyalin text: ", err);
        });
    }

    // Interaksi Visual saat Berkas Gambar Dipilih
    const fileInput = document.getElementById('proofUpload');
    const uploadWrapper = document.getElementById('uploadWrapper');
    
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const fileName = this.files[0].name;
            
            uploadWrapper.innerHTML = `
                <span class="material-symbols-outlined text-secondary mb-3" style="font-size: 48px;">check_circle</span>
                <p class="text-sm md:text-base font-bold text-secondary mb-1">Berkas Berhasil Dimuat</p>
                <p class="text-xs text-white font-medium max-w-[280px] truncate">${fileName}</p>
                <button type="button" onclick="location.reload()" class="mt-4 text-on-surface-variant hover:text-error text-[10px] font-bold tracking-widest uppercase border border-outline-variant/40 hover:border-error/50 px-4 py-2 rounded-lg bg-[#0D1117] transition-all">GANTI FILE</button>
            `;
            uploadWrapper.classList.replace('border-outline-variant/50', 'border-secondary/50');
            uploadWrapper.classList.add('bg-secondary/5');
        }
    });

    // FUNGSI TOGGLE MOBILE MENU (HAMBURGER)
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');

    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            if (mobileMenu.classList.contains('hidden')) {
                mobileMenu.classList.remove('hidden');
                mobileMenu.classList.add('flex');
            } else {
                mobileMenu.classList.add('hidden');
                mobileMenu.classList.remove('flex');
            }
        });
    }
    
    // Scroll effect navbar
    window.addEventListener('scroll', () => {
        const header = document.querySelector('header');
        if (window.scrollY > 20) {
            header.classList.add('shadow-md');
        } else {
            header.classList.remove('shadow-md');
        }
    });
</script>
</body>
</html>