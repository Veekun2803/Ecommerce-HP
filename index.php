<?php
session_start();

// PROTEKSI: Cek apakah user sudah login
if (!isset($_SESSION['customer_id'])) {
    header("Location: auth.php");
    exit;
}

include __DIR__ . '/admin/config.php';

$no_admin = "6285862030566"; 
$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'] ?? 'Guest';

// HANDLE LOGOUT
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: auth.php");
    exit;
}

// HANDLE AJAX
if (isset($_POST['ajax'])) {
    // 1. Tambah Keranjang
    if ($_POST['ajax'] == 'add_cart') {
        $id  = intval($_POST['phone_id']);
        $qty = intval($_POST['qty']);
        
        $resStok = $conn->query("SELECT stok FROM phones WHERE id=$id");
        $stok = ($resStok && $resStok->num_rows > 0) ? $resStok->fetch_assoc()['stok'] : 0;
        
        $check = $conn->query("SELECT id, qty FROM cart WHERE customer_id = $customer_id AND phone_id = $id");
        
        if ($check->num_rows > 0) {
            $current = $check->fetch_assoc();
            $newQty = min($current['qty'] + $qty, $stok);
            $conn->query("UPDATE cart SET qty = $newQty WHERE id = " . $current['id']);
        } else {
            $newQty = min($qty, $stok);
            $conn->query("INSERT INTO cart (customer_id, phone_id, qty) VALUES ($customer_id, $id, $newQty)");
        }

        $totalItems = $conn->query("SELECT SUM(qty) as total FROM cart WHERE customer_id = $customer_id")->fetch_assoc()['total'];
        
        echo json_encode(['total' => $totalItems ?? 0]);
        exit;
    }

    // 2. Pencarian & Filter Produk
    if ($_POST['ajax'] == 'search') {
        $search = $conn->real_escape_string($_POST['search']);
        $min = !empty($_POST['min_price']) ? intval(str_replace('.', '', $_POST['min_price'])) : 0;
        $max = !empty($_POST['max_price']) ? intval(str_replace('.', '', $_POST['max_price'])) : 999999999;

        $sql = "SELECT * FROM phones WHERE (nama_hp LIKE '%$search%' OR brand LIKE '%$search%') 
                AND harga BETWEEN $min AND $max 
                AND stok > 0 
                ORDER BY id DESC";
        $result = $conn->query($sql);
        
        ob_start();
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) renderProductCard($row, $conn, $no_admin);
        } else {
            echo '<div class="col-span-full text-center py-20 text-on-surface-variant font-medium italic bg-surface-container-low rounded-[2rem] border border-outline-variant/30 px-4">Produk tidak ditemukan atau stok sedang habis...</div>';
        }
        echo ob_get_clean();
        exit;
    }
}

// FUNGSI RENDER PRODUCT CARD (Desain Premium Dark Mode Baru)
function renderProductCard($row, $conn, $no_admin) {
    $imgQuery = $conn->query("SELECT image FROM phone_images WHERE phone_id=".$row['id']." LIMIT 1")->fetch_assoc();
    $gambar = ($imgQuery && file_exists(__DIR__."/admin/uploads/".$imgQuery['image'])) 
              ? "admin/uploads/".$imgQuery['image'] : "https://via.placeholder.com/400x300?text=No+Image";
    
    $pesanWA = urlencode("Halo admin, saya tertarik dengan: " . $row['nama_hp']);
    ?>
    <article class="glass-card rounded-2xl p-4 md:p-5 flex flex-col h-full border border-outline-variant/30 hover:border-primary/50 transition-all duration-300 hover:-translate-y-2 hover:shadow-[0_10px_30px_-10px_rgba(37,99,235,0.3)]">
        <div class="h-40 md:h-48 lg:h-56 mb-4 md:mb-5 flex items-center justify-center overflow-hidden bg-white/5 rounded-xl p-4 relative group">
            <div class="absolute inset-0 bg-primary/5 blur-xl rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <img alt="<?= htmlspecialchars($row['nama_hp']) ?>" class="max-h-full object-contain drop-shadow-2xl transition-transform duration-500 group-hover:scale-110 relative z-10" src="<?= $gambar ?>"/>
        </div>
        <div class="flex-grow flex flex-col justify-end">
            <span class="text-[10px] font-bold text-primary uppercase tracking-widest mb-1"><?= htmlspecialchars($row['brand']) ?></span>
            <h4 class="text-sm md:text-base lg:text-lg font-bold mb-1 text-on-surface line-clamp-2 md:line-clamp-1" title="<?= htmlspecialchars($row['nama_hp']) ?>"><?= htmlspecialchars($row['nama_hp']) ?></h4>
            <p class="text-base md:text-lg lg:text-xl font-extrabold text-secondary mb-3 md:mb-4">Rp <?= number_format($row['harga'],0,',','.') ?></p>
        </div>
        <div class="flex justify-between items-center mb-3 md:mb-4 border-t border-outline-variant/30 pt-3 md:pt-4">
            <span class="text-[10px] text-on-surface-variant font-bold uppercase tracking-wider flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-secondary animate-pulse"></span>
                SISA: <span class="text-secondary"><?= $row['stok'] ?> Unit</span>
            </span>
        </div>
        <div class="space-y-2">
            <div class="flex flex-col min-[400px]:flex-row gap-2">
                <a href="detail.php?id=<?= $row['id'] ?>" class="flex-1 bg-surface-container-high hover:bg-surface-variant border border-outline-variant/50 text-on-surface text-xs font-bold py-2.5 md:py-3 rounded-lg transition-colors text-center flex items-center justify-center active:scale-95">Detail</a>
                <button onclick="addToCart(<?= $row['id'] ?>, event)" class="flex-1 bg-primary-container hover:bg-blue-600 text-white text-xs font-bold py-2.5 md:py-3 rounded-lg transition-colors shadow-lg shadow-primary-container/20 active:scale-95">+ Cart</button>
            </div>
            <a href="https://wa.me/<?= $no_admin ?>?text=<?= $pesanWA ?>" target="_blank" class="w-full bg-surface-container hover:bg-surface-variant border border-outline-variant text-on-surface text-xs font-bold py-2.5 md:py-3 rounded-lg transition-colors text-center flex items-center justify-center gap-1.5 active:scale-95">
                <span class="material-symbols-outlined text-[16px] text-secondary">chat</span> Tanya Admin
            </a>
        </div>
    </article>
    <?php
}

$cartCountRes = $conn->query("SELECT SUM(qty) as total FROM cart WHERE customer_id = $customer_id");
$total_cart = ($cartCountRes && $cartCountRes->num_rows > 0) ? $cartCountRes->fetch_assoc()['total'] : 0;
if (!$total_cart) $total_cart = 0;
?>

<!DOCTYPE html>
<html class="dark" lang="id">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no" name="viewport"/>
<title>PhoneStore Premium | Upgrade Your Tech</title>
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
    .hero-gradient {
      background: linear-gradient(135deg, rgba(37, 99, 235, 0.15) 0%, rgba(16, 20, 21, 0) 60%), 
                  linear-gradient(225deg, rgba(37, 99, 235, 0.05) 0%, rgba(16, 20, 21, 0) 40%);
    }
    .hero-banner-bg {
      background: linear-gradient(105deg, #101415 0%, #1a1e26 100%);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .glass-search {
      background: rgba(255, 255, 255, 0.03);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(67, 70, 85, 0.4);
    }
    .price-format::-webkit-inner-spin-button, .price-format::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
  </style>
</head>
<body class="font-body-md text-body-md selection:bg-primary-container selection:text-on-primary-container min-h-screen flex flex-col overflow-x-hidden">

<header class="fixed top-0 z-50 w-full bg-background/80 backdrop-blur-xl border-b border-outline-variant/30 shadow-sm transition-all duration-300">
    <div class="flex justify-between items-center w-full px-4 md:px-12 py-3 md:py-4 max-w-[1440px] mx-auto relative">
        <div class="flex items-center gap-4 md:gap-8">
            <a class="text-lg sm:text-xl md:text-2xl font-extrabold text-white flex items-center gap-1" href="index.php">
                PhoneStore<span class="w-1.5 h-1.5 md:w-2 md:h-2 rounded-full bg-primary mb-1"></span>
            </a>
            <nav class="hidden md:flex gap-6">
                <a class="text-primary font-bold border-b-2 border-primary pb-1 font-label-caps text-[12px]" href="index.php">Beranda</a>
                <a class="text-on-surface-variant font-medium hover:text-primary transition-colors font-label-caps text-[12px]" href="cart.php">Keranjang</a>
                <a class="text-on-surface-variant font-medium hover:text-primary transition-colors font-label-caps text-[12px]" href="my_orders.php">Pesanan Saya</a>
            </nav>
        </div>
        
        <div class="flex items-center gap-2 md:gap-4">
            <div class="hidden lg:flex flex-col items-end mr-2">
                <span class="font-label-caps text-[10px] text-on-surface-variant">WELCOME BACK,</span>
                <span class="font-body-md text-[14px] font-bold text-white"><?= htmlspecialchars(strtoupper(explode(' ', $customer_name)[0])) ?></span>
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
                    <?= htmlspecialchars(substr(strtoupper($customer_name), 0, 1)) ?>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="font-label-caps text-[10px] text-on-surface-variant uppercase tracking-wider">Welcome,</p>
                    <p class="text-base font-bold text-white truncate break-words"><?= htmlspecialchars($customer_name) ?></p>
                </div>
            </div>
            <a href="index.php" class="text-primary font-bold flex items-center gap-3"><span class="material-symbols-outlined">home</span> Beranda</a>
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
<main class="flex-grow pt-20 md:pt-24">
    <section class="pt-4 md:pt-8 px-4 md:px-12 hero-gradient">
        <div class="max-w-[1280px] mx-auto hero-banner-bg rounded-[2rem] md:rounded-[3rem] overflow-hidden relative flex flex-col md:flex-row items-center shadow-2xl">
            <div class="flex-1 p-6 md:p-8 lg:p-16 z-10 w-full relative">
                <div class="absolute -top-12 -left-12 w-48 h-48 bg-primary/20 blur-[80px] rounded-full"></div>
                
                <h2 class="text-primary font-label-caps tracking-widest text-[10px] md:text-xs mb-3 md:mb-4 uppercase font-bold border border-primary/20 bg-primary/5 w-max px-3 py-1 rounded-md">UPGRADE YOUR TECH.</h2>
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold mb-6 md:mb-10 tracking-tight leading-tight text-white drop-shadow-lg">Your Dream Gadget <br/><span class="text-primary">Awaits.</span></h1>
                
                <div class="relative max-w-lg mb-4 group">
                    <input id="searchInput" class="w-full h-12 md:h-14 pl-4 md:pl-6 pr-12 md:pr-14 rounded-2xl glass-search text-[16px] md:text-base text-black focus:ring-2 focus:ring-primary focus:outline-none placeholder:text-on-surface-variant transition-all" placeholder="Cari gadget impian..." type="text"/>
                    <div class="absolute right-2 top-1 md:top-2 h-10 w-10 flex items-center justify-center bg-transparent text-on-surface-variant group-focus-within:text-primary transition-colors">
                        <span class="material-symbols-outlined text-[20px] md:text-[24px]">search</span>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-lg">
                    <input type="text" id="minPrice" placeholder="HARGA MIN" class="price-format px-4 py-3 md:py-3.5 w-full rounded-xl bg-white/5 border border-white/10 text-[16px] md:text-xs font-semibold text-white placeholder-on-surface-variant focus:outline-none focus:ring-1 focus:ring-primary focus:bg-white/10 transition-colors text-center uppercase tracking-wider">
                    <input type="text" id="maxPrice" placeholder="HARGA MAX" class="price-format px-4 py-3 md:py-3.5 w-full rounded-xl bg-white/5 border border-white/10 text-[16px] md:text-xs font-semibold text-white placeholder-on-surface-variant focus:outline-none focus:ring-1 focus:ring-primary focus:bg-white/10 transition-colors text-center uppercase tracking-wider">
                </div>
            </div>
            <div class="absolute inset-0 z-0 opacity-30 md:opacity-50 pointer-events-none">
                <img alt="Premium Smartphone Background" class="w-full h-full object-cover object-center" src="https://images.unsplash.com/photo-1616348436168-de43ad0db179?q=80&w=2000&auto=format&fit=crop"/>
                <div class="absolute inset-0 bg-gradient-to-t md:bg-gradient-to-r from-[#101415] via-[#101415]/80 to-transparent"></div>
            </div>
        </div>
    </section>
    <section class="py-8 md:py-20 px-4 md:px-12 max-w-[1440px] mx-auto min-h-[400px]">
        <div class="flex items-center justify-between mb-6 md:mb-12 border-b border-outline-variant/30 pb-4">
            <h3 class="text-lg md:text-2xl font-extrabold tracking-tight uppercase flex items-center gap-2 md:gap-3">
                <span class="material-symbols-outlined text-primary text-[24px] md:text-[28px]">view_cozy</span>
                KOLEKSI TERBARU
            </h3>
        </div>
        
        <div id="productsContainer" class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-6 transition-opacity duration-300">
            <?php
            $res = $conn->query("SELECT * FROM phones WHERE stok > 0 ORDER BY id DESC");
            if ($res && $res->num_rows > 0) {
                while($row = $res->fetch_assoc()) renderProductCard($row, $conn, $no_admin);
            } else {
                echo '<div class="col-span-full text-center py-20 text-on-surface-variant font-medium italic bg-surface-container-low rounded-[2rem] border border-outline-variant/30 px-4">Belum ada stok barang tersedia...</div>';
            }
            ?>
        </div>
    </section>
</main>

<footer class="w-full mt-auto bg-surface-container-lowest border-t border-outline-variant/30">
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
    // FUNGSI SEARCH & FILTER AJAX
    const container = document.getElementById('productsContainer');
    
    function filter() {
        let fd = new FormData();
        fd.append('ajax', 'search');
        fd.append('search', document.getElementById('searchInput').value);
        fd.append('min_price', document.getElementById('minPrice').value.replace(/\./g, ''));
        fd.append('max_price', document.getElementById('maxPrice').value.replace(/\./g, ''));

        container.style.opacity = '0.4'; 

        fetch('index.php', { method: 'POST', body: fd })
        .then(r => r.text()).then(h => { 
            container.innerHTML = h; 
            container.style.opacity = '1';
        });
    }

    document.getElementById('searchInput').addEventListener('input', filter);
    document.querySelectorAll('.price-format').forEach(i => {
        i.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            filter();
        });
    });

    // FUNGSI TAMBAH KERANJANG AJAX
    function addToCart(id, e) {
        let fd = new FormData();
        fd.append('ajax', 'add_cart');
        fd.append('phone_id', id);
        fd.append('qty', 1);

        const btn = e.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="material-symbols-outlined text-[16px] animate-spin">sync</span>';
        btn.disabled = true;

        fetch('index.php', { method: 'POST', body: fd })
        .then(r => r.json()).then(d => {
            // Update Semua Badge (Desktop & Mobile Menu)
            let badges = [
                document.getElementById('cartBadge'),
                document.getElementById('mobileCartBadge'),
                document.getElementById('mobileMenuCartBadge')
            ];

            badges.forEach(b => {
                if (b) {
                    b.innerText = d.total;
                    b.classList.remove('hidden');
                    b.classList.add('animate-bounce');
                    setTimeout(() => b.classList.remove('animate-bounce'), 1000);
                }
            });
            
            // Animasi feedback tombol
            btn.innerHTML = '✔ Sip!';
            btn.classList.replace('bg-primary-container', 'bg-secondary-container');
            btn.classList.replace('hover:bg-blue-600', 'hover:brightness-110');
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.replace('bg-secondary-container', 'bg-primary-container');
                btn.classList.replace('hover:brightness-110', 'hover:bg-blue-600');
                btn.disabled = false;
            }, 1500);
        }).catch(err => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

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