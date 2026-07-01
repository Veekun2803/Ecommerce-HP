<?php 
session_start();
include __DIR__ . '/admin/config.php'; 

// Proteksi login
if (!isset($_SESSION['customer_id'])) {
    header("Location: auth.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);
$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'] ?? 'Guest';

// Ambil data produk dengan Prepared Statement
$stmt = $conn->prepare("SELECT * FROM phones WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    die("<script>alert('Produk tidak ditemukan!'); window.location='index.php';</script>");
}

// Ambil semua gambar
$stmtImg = $conn->prepare("SELECT * FROM phone_images WHERE phone_id = ?");
$stmtImg->bind_param("i", $id);
$stmtImg->execute();
$images = $stmtImg->get_result();

$gambarList = [];
while ($img = $images->fetch_assoc()) {
    $path = "admin/uploads/" . $img['image'];
    if (file_exists(__DIR__ . "/" . $path)) {
        $gambarList[] = $path;
    }
}

// Default image jika kosong
if (empty($gambarList)) {
    $gambarList[] = "https://via.placeholder.com/600x600?text=No+Image";
}

// Hitung total keranjang untuk badge navbar (disamakan dengan index.php)
$cartCountRes = $conn->query("SELECT SUM(qty) as total FROM cart WHERE customer_id = $customer_id");
$total_cart = ($cartCountRes && $cartCountRes->num_rows > 0) ? $cartCountRes->fetch_assoc()['total'] : 0;
if (!$total_cart) $total_cart = 0;
?>

<!DOCTYPE html>
<html class="dark" lang="id">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no" name="viewport"/>
<title><?= htmlspecialchars($data['nama_hp']) ?> | PhoneStore Premium</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        .atmospheric-glow {
            background: radial-gradient(circle at 50% 50%, rgba(37, 99, 235, 0.08) 0%, transparent 70%);
        }
        .active-thumb {
            border-color: #2563EB !important;
            box-shadow: 0 0 15px rgba(37, 99, 235, 0.3);
        }
        /* Hide number arrows */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; margin: 0; 
        }
        .swal2-dark-custom {
            background: #161B22 !important;
            border: 1px solid #30363D !important;
            color: #e0e3e5 !important;
            border-radius: 1.5rem !important;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        /* Custom Scrollbar for Mobile Thumbnails */
        .custom-scrollbar::-webkit-scrollbar {
            height: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(141, 144, 160, 0.3);
            border-radius: 4px;
        }
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
                <a class="text-on-surface-variant font-medium hover:text-primary transition-colors font-label-caps text-[12px]" href="index.php">Beranda</a>
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

<main class="flex-grow pt-24 pb-16 px-4 md:px-8 max-w-[1440px] mx-auto relative w-full">
    <div class="absolute top-0 right-0 w-[300px] md:w-[500px] h-[300px] md:h-[500px] atmospheric-glow -z-10 pointer-events-none"></div>
    
    <nav class="mb-4 md:mb-10 flex items-center gap-2 text-on-surface-variant text-xs md:text-sm overflow-x-auto whitespace-nowrap pb-2">
        <a class="hover:text-primary transition-colors flex items-center gap-1 shrink-0" href="index.php">
            <span class="material-symbols-outlined text-[16px]">home</span> Home
        </a>
        <span class="material-symbols-outlined text-[14px] shrink-0">chevron_right</span>
        <span class="text-on-surface font-medium truncate shrink-0"><?= htmlspecialchars($data['nama_hp']) ?></span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 md:gap-12 items-start">
        
        <div class="lg:col-span-7 flex flex-col gap-4 md:gap-6 w-full">
            <div class="relative aspect-square md:aspect-[4/3] w-full rounded-2xl md:rounded-[2rem] overflow-hidden glass-card p-6 md:p-12 group flex items-center justify-center">
                <img id="main-display-image" class="max-w-full max-h-full object-contain transition-all duration-700 ease-in-out group-hover:scale-105 drop-shadow-2xl" src="<?= $gambarList[0] ?>" alt="<?= htmlspecialchars($data['nama_hp']) ?>"/>
            </div>
            
            <div class="flex overflow-x-auto gap-3 pb-3 custom-scrollbar md:grid md:grid-cols-5 md:overflow-visible w-full snap-x">
                <?php foreach ($gambarList as $index => $g): ?>
                    <button class="thumbnail-btn flex-shrink-0 w-20 h-20 md:w-full md:h-auto md:aspect-square rounded-xl glass-card p-2 border-transparent transition-all snap-center <?= $index === 0 ? 'active-thumb' : '' ?>" onclick="switchImage('<?= $g ?>', this)">
                        <img class="w-full h-full object-contain rounded-lg opacity-<?= $index === 0 ? '100' : '60' ?> hover:opacity-100 transition-opacity" src="<?= $g ?>"/>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="lg:col-span-5 flex flex-col gap-6 md:gap-8 lg:sticky lg:top-32 w-full">
            
            <div class="flex flex-col gap-3">
                <span class="font-label-caps text-[10px] md:text-[12px] text-primary tracking-[0.2em] uppercase border border-primary/20 bg-primary/5 w-max px-3 py-1 rounded-md"><?= htmlspecialchars($data['brand']) ?></span>
                <h1 class="font-display-lg text-2xl sm:text-3xl md:text-5xl text-on-surface leading-tight font-bold tracking-tight break-words"><?= htmlspecialchars($data['nama_hp']) ?></h1>
                
                <div class="flex flex-wrap items-center gap-3 mt-2">
                    <span class="text-2xl md:text-3xl font-bold text-secondary text-glow">Rp <?= number_format($data['harga'], 0, ',', '.') ?></span>
                    <?php if($data['stok'] > 0): ?>
                        <span class="px-3 py-1 bg-secondary-container/20 text-secondary border border-secondary/30 rounded-full font-label-caps text-[10px] md:text-[11px] font-bold flex items-center gap-1.5 whitespace-nowrap">
                            <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
                            SISA: <?= $data['stok'] ?>
                        </span>
                    <?php else: ?>
                        <span class="px-3 py-1 bg-error-container/20 text-error border border-error/30 rounded-full font-label-caps text-[10px] md:text-[11px] font-bold whitespace-nowrap">HABIS</span>
                    <?php endif; ?>
                </div>
            </div>

            <hr class="border-outline-variant/30"/>

            <div class="flex flex-col gap-4">
                <div class="flex flex-col sm:flex-row items-center gap-3 w-full">
                    <div class="flex items-center bg-surface-container-low rounded-xl border border-outline-variant h-14 px-2 w-full sm:w-auto shrink-0 justify-between sm:justify-start">
                        <button class="w-12 h-full flex items-center justify-center text-on-surface hover:text-primary transition-colors font-bold text-xl active:scale-95" onclick="adjustQty(-1)">-</button>
                        <input class="w-12 bg-transparent border-none text-center font-bold text-on-surface focus:ring-0 focus:outline-none appearance-none" id="qty-input" min="1" max="<?= $data['stok'] ?>" type="number" value="1" readonly/>
                        <button class="w-12 h-full flex items-center justify-center text-on-surface hover:text-primary transition-colors font-bold text-xl active:scale-95" onclick="adjustQty(1)">+</button>
                    </div>
                    
                    <button id="btnCart" onclick="handleAddToCart(<?= $data['id'] ?>)" <?= $data['stok'] <= 0 ? 'disabled' : '' ?> class="w-full sm:flex-1 h-14 min-h-[56px] shrink-0 bg-primary-container text-on-primary-container rounded-xl font-bold flex items-center justify-center gap-2 active:scale-[0.98] transition-all shadow-[0_0_20px_rgba(37,99,235,0.3)] disabled:bg-surface-variant disabled:text-outline disabled:shadow-none disabled:cursor-not-allowed">
                        <span id="iconCart" class="material-symbols-outlined text-[20px]">shopping_bag</span>
                        <span id="textCart" class="uppercase tracking-wide text-sm whitespace-nowrap">Add to Cart</span>
                    </button>
                </div>
                
                <a href="https://wa.me/6285862030566?text=Halo%20admin,%20saya%20tertarik%20dengan:%20<?= urlencode($data['nama_hp']) ?>" target="_blank" class="w-full h-14 border border-outline-variant rounded-xl font-bold text-on-surface hover:bg-surface-variant transition-all flex items-center justify-center gap-2 uppercase text-sm tracking-wide active:scale-[0.98]">
                    <span class="material-symbols-outlined text-[20px] text-secondary">chat</span>
                    Tanya Admin
                </a>
            </div>

            <div class="grid grid-cols-2 gap-3 mt-2">
                <div class="p-3 glass-card rounded-xl flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary text-[24px]" style="font-variation-settings: 'FILL' 1;">workspace_premium</span>
                    <div class="min-w-0">
                        <p class="font-label-caps text-[9px] text-on-surface-variant truncate">JAMINAN</p>
                        <p class="font-bold text-[11px] sm:text-[12px] md:text-[13px] truncate">100% Original</p>
                    </div>
                </div>
                <div class="p-3 glass-card rounded-xl flex items-center gap-3">
                    <span class="material-symbols-outlined text-secondary text-[24px]" style="font-variation-settings: 'FILL' 1;">shield</span>
                    <div class="min-w-0">
                        <p class="font-label-caps text-[9px] text-on-surface-variant truncate">GARANSI</p>
                        <p class="font-bold text-[11px] sm:text-[12px] md:text-[13px] truncate">Resmi 1 Tahun</p>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    <section class="mt-12 md:mt-24 grid grid-cols-1 lg:grid-cols-12 gap-8 md:gap-12">
        <div class="lg:col-span-7 flex flex-col gap-6">
            <div class="p-5 md:p-10 glass-card rounded-2xl md:rounded-[2rem] relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 md:w-32 md:h-32 bg-primary/10 blur-[60px] rounded-full"></div>
                <h3 class="font-headline-lg text-xl md:text-3xl mb-4 md:mb-6 text-on-surface font-bold flex items-center gap-2 md:gap-3">
                    <span class="material-symbols-outlined text-primary text-[24px] md:text-[28px]">description</span> 
                    Deskripsi
                </h3>
                <div class="text-on-surface-variant text-sm md:text-base leading-relaxed space-y-4 break-words">
                    <?= nl2br(htmlspecialchars($data['deskripsi'])) ?>
                </div>
            </div>
        </div>

        <div class="lg:col-span-5">
            <div class="p-5 md:p-10 glass-card rounded-2xl md:rounded-[2rem] border-primary/20">
                <h3 class="font-headline-lg text-xl md:text-3xl mb-4 md:mb-6 text-on-surface font-bold flex items-center gap-2 md:gap-3">
                    <span class="material-symbols-outlined text-secondary text-[24px] md:text-[28px]">tune</span>
                    Spesifikasi
                </h3>
                <div class="flex flex-col">
                    <?php 
                    $specs = explode("\n", $data['spesifikasi']);
                    foreach($specs as $spec):
                        $spec = trim($spec);
                        if($spec != ""): 
                            if(strpos($spec, ':') !== false) {
                                $parts = explode(':', $spec, 2);
                                $label = trim($parts[0]);
                                $value = trim($parts[1]);
                            } else {
                                $label = "•"; 
                                $value = $spec;
                            }
                    ?>
                        <div class="flex justify-between py-3 md:py-4 border-b border-outline-variant/30 last:border-0 gap-3 md:gap-4 group">
                            <span class="text-on-surface-variant text-[12px] md:text-[15px] w-2/5 font-medium shrink-0"><?= htmlspecialchars($label) ?></span>
                            <span class="font-bold text-on-surface text-[12px] md:text-[15px] text-right w-3/5 group-hover:text-primary transition-colors break-words"><?= htmlspecialchars($value) ?></span>
                        </div>
                    <?php 
                        endif; 
                    endforeach; 
                    ?>
                </div>
            </div>
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
    // Gallery Logic
    function switchImage(src, button) {
        document.querySelectorAll('.thumbnail-btn').forEach(btn => {
            btn.classList.remove('active-thumb');
            btn.querySelector('img').classList.remove('opacity-100');
            btn.querySelector('img').classList.add('opacity-60');
        });
        
        button.classList.add('active-thumb');
        button.querySelector('img').classList.remove('opacity-60');
        button.querySelector('img').classList.add('opacity-100');
        
        const mainImg = document.getElementById('main-display-image');
        mainImg.style.opacity = '0';
        mainImg.style.transform = 'scale(0.95)';
        
        setTimeout(() => {
            mainImg.src = src;
            mainImg.style.opacity = '1';
            mainImg.style.transform = 'scale(1)';
        }, 300);
    }

    // Quantity Logic
    function adjustQty(amount) {
        const input = document.getElementById('qty-input');
        let current = parseInt(input.value);
        let max = <?= $data['stok'] ?>;
        let next = current + amount;
        
        if (next >= 1 && next <= max) {
            input.value = next;
        }
    }

    // Add to Cart Logic (AJAX) - Diupdate untuk sync semua badge keranjang
    function handleAddToCart(id) {
        const qty = document.getElementById('qty-input').value;
        const btn = document.getElementById('btnCart');
        const textCart = document.getElementById('textCart');
        const iconCart = document.getElementById('iconCart');
        const originalText = textCart.innerText;
        
        btn.disabled = true;
        textCart.innerText = 'PROCESSING...';
        iconCart.innerText = 'sync';
        iconCart.classList.add('animate-spin');

        let fd = new FormData();
        fd.append('ajax', 'add_cart');
        fd.append('phone_id', id);
        fd.append('qty', qty);

        fetch('index.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if(data.total !== undefined) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: qty + ' unit ditambahkan ke keranjang.',
                    showConfirmButton: false,
                    timer: 1500,
                    background: '#161B22',
                    color: '#e0e3e5',
                    customClass: { popup: 'swal2-dark-custom rounded-[1.5rem] border border-[#30363D]' }
                });

                btn.classList.replace('bg-primary-container', 'bg-secondary-container');
                btn.classList.replace('text-on-primary-container', 'text-white');
                textCart.innerText = 'ADDED TO CART';
                iconCart.classList.remove('animate-spin');
                iconCart.innerText = 'check_circle';
                
                // Update Semua Badge (Desktop & Mobile Menu)
                let badges = [
                    document.getElementById('cartBadge'),
                    document.getElementById('mobileCartBadge'),
                    document.getElementById('mobileMenuCartBadge')
                ];

                badges.forEach(b => {
                    if (b) {
                        b.innerText = data.total;
                        b.classList.remove('hidden');
                        b.classList.add('animate-bounce');
                        setTimeout(() => b.classList.remove('animate-bounce'), 1000);
                    }
                });
            } else {
                throw new Error(data.message || 'Gagal menambahkan');
            }
        })
        .catch(err => {
            Swal.fire({ 
                icon: 'error', 
                title: 'Oops...', 
                text: err.message,
                background: '#161B22',
                color: '#e0e3e5',
                customClass: { popup: 'swal2-dark-custom rounded-[1.5rem] border border-[#30363D]' }
            });
            textCart.innerText = originalText;
            iconCart.classList.remove('animate-spin');
            iconCart.innerText = 'shopping_bag';
        })
        .finally(() => {
            setTimeout(() => {
                btn.disabled = false;
                btn.classList.replace('bg-secondary-container', 'bg-primary-container');
                btn.classList.replace('text-white', 'text-on-primary-container');
                textCart.innerText = originalText;
                iconCart.innerText = 'shopping_bag';
            }, 2000);
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

    // Scroll effect navbar - Sekarang mencari 'header' akan bekerja dengan baik
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