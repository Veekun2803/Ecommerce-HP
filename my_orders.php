<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: auth.php");
    exit;
}

include __DIR__ . '/admin/config.php';
$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'] ?? 'Guest';

// HANDLE AJAX UNTUK DETAIL PESANAN
if (isset($_GET['ajax_detail'])) {
    $order_id = intval($_GET['ajax_detail']);
    $sql = "SELECT oi.*, p.nama_hp, p.brand 
            FROM order_items oi 
            JOIN phones p ON oi.phone_id = p.id 
            WHERE oi.order_id = $order_id";
    $res = $conn->query($sql);
    
    if ($res->num_rows > 0) {
        while ($item = $res->fetch_assoc()) {
            echo "
            <div class='flex justify-between items-center py-4 border-b border-outline-variant/30 last:border-0'>
                <div>
                    <p class='font-label-caps text-[10px] text-primary uppercase tracking-widest border border-primary/20 bg-primary/5 px-2 py-0.5 rounded inline-block mb-1'>{$item['brand']}</p>
                    <p class='text-sm md:text-base font-bold text-on-surface leading-tight'>{$item['nama_hp']}</p>
                    <p class='text-xs text-on-surface-variant font-medium mt-1'>{$item['qty']} unit x Rp " . number_format($item['harga'], 0, ',', '.') . "</p>
                </div>
                <div class='text-right'>
                    <p class='text-sm md:text-base font-bold text-primary'>Rp " . number_format($item['harga'] * $item['qty'], 0, ',', '.') . "</p>
                </div>
            </div>";
        }
    } else {
        echo "<div class='text-center py-8'><p class='text-on-surface-variant font-bold uppercase text-xs tracking-widest'>Data tidak ditemukan</p></div>";
    }
    exit;
}

// Ambil total item di keranjang untuk badge Navbar (disamakan dengan index.php)
$cartCountRes = $conn->query("SELECT SUM(qty) as total FROM cart WHERE customer_id = $customer_id");
$total_cart = ($cartCountRes && $cartCountRes->num_rows > 0) ? $cartCountRes->fetch_assoc()['total'] : 0;
if (!$total_cart) $total_cart = 0;
?>

<!DOCTYPE html>
<html class="dark" lang="id">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no" name="viewport"/>
<title>Pesanan Saya | PhoneStore Premium</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet"/>
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
            background-color: #101415;
            color: #e0e3e5;
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }
        .glass-card {
            background: rgba(22, 27, 34, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(48, 54, 61, 0.5);
        }
        .glow-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at 50% 0%, rgba(37, 99, 235, 0.05) 0%, transparent 60%);
            pointer-events: none; z-index: 0;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #434655; border-radius: 10px; }
    </style>
</head>
<body class="dark bg-background text-on-background flex flex-col min-h-screen">
<div class="glow-overlay"></div>

<header class="fixed top-0 z-50 w-full bg-background/80 backdrop-blur-xl border-b border-outline-variant/30 shadow-sm transition-all duration-300">
    <div class="flex justify-between items-center w-full px-4 md:px-12 py-3 md:py-4 max-w-[1440px] mx-auto relative">
        <div class="flex items-center gap-4 md:gap-8">
            <a class="text-lg sm:text-xl md:text-2xl font-extrabold text-white flex items-center gap-1" href="index.php">
                PhoneStore<span class="w-1.5 h-1.5 md:w-2 md:h-2 rounded-full bg-primary mb-1"></span>
            </a>
            <nav class="hidden md:flex gap-6">
                <a class="text-on-surface-variant font-medium hover:text-primary transition-colors font-label-caps text-[12px]" href="index.php">Beranda</a>
                <a class="text-on-surface-variant font-medium hover:text-primary transition-colors font-label-caps text-[12px]" href="cart.php">Keranjang</a>
                <a class="text-primary font-bold border-b-2 border-primary pb-1 font-label-caps text-[12px]" href="my_orders.php">Pesanan Saya</a>
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
            <a href="my_orders.php" class="text-primary font-bold flex items-center gap-3"><span class="material-symbols-outlined">receipt_long</span> Pesanan Saya</a>
            <a href="profile.php" class="text-on-surface-variant hover:text-white font-medium flex items-center gap-3"><span class="material-symbols-outlined">manage_accounts</span> Profil Akun</a>
            <a href="?logout=true" class="text-error hover:text-red-300 font-medium flex items-center gap-3 mt-2 pt-5 border-t border-outline-variant/30"><span class="material-symbols-outlined">logout</span> Logout</a>
        </div>
    </div>
</header>

<main class="flex-grow max-w-[1024px] mx-auto w-full px-4 md:px-8 pt-24 md:pt-32 pb-20 relative z-10">
    <div class="mb-8 md:mb-12 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="font-headline-lg text-3xl md:text-4xl font-bold italic tracking-tighter">My <span class="text-primary">Orders</span></h1>
            <p class="text-on-surface-variant mt-2 text-sm md:text-base">Pantau status transaksi dan riwayat belanja Anda di sini.</p>
        </div>
        <a href="index.php" class="flex items-center gap-2 text-primary hover:text-on-primary-container transition-colors bg-primary/10 px-4 py-2 rounded-full w-fit">
            <span class="material-symbols-outlined text-[18px]">shopping_bag</span>
            <span class="font-label-caps text-[11px] uppercase tracking-widest font-bold">Lanjut Belanja</span>
        </a>
    </div>

    <div class="space-y-4 md:space-y-6">
        <?php
        $sql = "SELECT * FROM orders WHERE customer_id = $customer_id ORDER BY id DESC";
        $res = $conn->query($sql);

        if ($res->num_rows > 0):
            while ($row = $res->fetch_assoc()):
                $status = $row['status'];
                
                if ($status == 'lunas') {
                    $bg_status = "bg-secondary/10 text-secondary border-secondary/20";
                    $label = "Siap Dikirim / Diambil";
                    $icon = "check_circle";
                } elseif ($status == 'ditolak') {
                    $bg_status = "bg-error/10 text-error border-error/20";
                    $label = "Pembayaran Tidak Valid";
                    $icon = "cancel";
                } else {
                    $bg_status = "bg-yellow-500/10 text-yellow-400 border-yellow-500/20";
                    $label = "Menunggu Verifikasi";
                    $icon = "hourglass_empty";
                }
        ?>
            <div class="glass-card p-5 md:p-8 rounded-[1.5rem] md:rounded-[2rem] shadow-lg transition-all hover:border-outline-variant/80 flex flex-col md:flex-row md:items-center justify-between gap-6">
                
                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-3 mb-4">
                        <span class="font-label-caps text-[10px] md:text-[11px] font-bold text-primary uppercase tracking-[0.1em] bg-primary/10 border border-primary/20 px-3 py-1 rounded-full italic flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">receipt_long</span> INV-<?= $row['id'] ?>
                        </span>
                        <span class="flex items-center gap-1 px-3 py-1 rounded-full text-[10px] md:text-[11px] font-bold uppercase border tracking-wider <?= $bg_status ?>">
                            <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;"><?= $icon ?></span>
                            <?= $label ?>
                        </span>
                    </div>
                    
                    <h3 class="font-headline-lg font-bold text-on-surface text-2xl md:text-3xl tracking-tighter mb-1">Rp <?= number_format($row['total'], 0, ',', '.') ?></h3>
                    
                    <div class="flex items-center gap-2 text-[11px] md:text-[12px] text-on-surface-variant font-medium">
                        <span class="material-symbols-outlined text-[14px]">calendar_today</span>
                        <?= date('d M Y • H:i', strtotime($row['created_at'])) ?> 
                        <span class="mx-1">•</span> 
                        <span class="uppercase tracking-widest font-label-caps">VIA <?= $row['metode'] ?></span>
                    </div>
                    
                    <?php if (!empty($row['no_resi'])): ?>
                        <div class="mt-5 inline-flex items-center gap-3 px-4 py-2 bg-surface-container-high border border-outline-variant/30 rounded-xl">
                            <span class="material-symbols-outlined text-primary">local_shipping</span>
                            <div>
                                <p class="font-label-caps text-[9px] text-on-surface-variant uppercase tracking-widest">Resi Pengiriman</p>
                                <p class="text-sm md:text-base font-bold text-primary tracking-widest select-all"><?= htmlspecialchars($row['no_resi']) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 md:gap-4 mt-4 md:mt-0 w-full md:w-auto border-t border-outline-variant/20 md:border-none pt-4 md:pt-0">
                    <?php if ($status == 'pending'): ?>
                        <div class="flex items-center justify-center gap-2 w-full sm:w-auto text-yellow-400 bg-yellow-500/10 px-4 py-2.5 rounded-xl border border-yellow-500/20">
                            <span class="flex h-2 w-2 rounded-full bg-yellow-400 animate-ping"></span>
                            <span class="font-label-caps text-[10px] md:text-[11px] font-bold uppercase tracking-widest italic">Checking...</span>
                        </div>
                    <?php endif; ?>
                    
                    <button onclick="showDetail(<?= $row['id'] ?>)" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-3 bg-surface-container-highest border border-outline-variant/50 text-on-surface text-[11px] md:text-xs font-bold rounded-xl hover:bg-primary-container hover:text-on-primary-container hover:border-primary-container transition-all uppercase tracking-[0.1em] shadow-lg active:scale-95">
                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                        Lihat Detail
                    </button>
                </div>
            </div>
        <?php 
            endwhile;
        else: 
        ?>
            <div class="text-center py-20 md:py-28 glass-card rounded-[2rem] md:rounded-[3rem] border-dashed border-outline-variant/40 flex flex-col items-center justify-center">
                <span class="material-symbols-outlined text-[80px] md:text-[100px] text-outline-variant/50 mb-6 select-none" style="font-variation-settings: 'FILL' 1;">package_2</span>
                <p class="text-on-surface font-bold text-xl md:text-2xl mb-2 tracking-tight">Belum Ada Pesanan</p>
                <p class="text-on-surface-variant text-sm md:text-base max-w-md mx-auto mb-8">Riwayat transaksi Anda masih kosong. Temukan gadget impian Anda sekarang!</p>
                <a href="index.php" class="inline-flex items-center gap-2 px-8 py-3.5 bg-primary-container text-on-primary-container rounded-full font-bold text-sm shadow-[0_0_20px_rgba(37,99,235,0.4)] hover:bg-primary-container/90 hover:scale-105 transition-all">
                    Mulai Belanja <span class="material-symbols-outlined text-[18px]">shopping_cart</span>
                </a>
            </div>
        <?php endif; ?>
    </div>
</main>

<div id="modalDetail" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-background/80 backdrop-blur-md" onclick="closeModal()"></div>
    
    <div class="bg-surface-container-high w-full max-w-lg rounded-[2rem] border border-outline-variant/30 relative z-10 shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="px-6 py-5 md:px-8 md:py-6 flex justify-between items-center border-b border-outline-variant/30 bg-surface-container-highest/50">
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-on-surface tracking-tighter uppercase italic">Detail <span class="text-primary">Items</span></h2>
                <p id="modalInvId" class="font-label-caps text-[10px] text-on-surface-variant uppercase tracking-[0.2em] mt-1"></p>
            </div>
            <button onclick="closeModal()" class="p-2 bg-surface-container rounded-full hover:bg-error/20 hover:text-error text-on-surface-variant transition-colors group">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
        
        <div id="modalBody" class="px-6 md:px-8 py-2 max-h-[50vh] overflow-y-auto custom-scrollbar">
            <div class="flex flex-col items-center justify-center py-12 gap-3">
                <div class="animate-spin rounded-full h-8 w-8 border-[3px] border-primary border-t-transparent"></div>
                <span class="font-label-caps text-[10px] text-primary uppercase tracking-widest">Memuat Data...</span>
            </div>
        </div>

        <div class="p-6 md:p-8 bg-surface-container-highest/30 border-t border-outline-variant/30">
            <button onclick="closeModal()" class="w-full py-3.5 bg-primary-container text-on-primary-container font-bold text-sm rounded-xl shadow-lg hover:bg-primary-container/90 transition-all uppercase tracking-widest active:scale-[0.98]">
                Tutup Jendela
            </button>
        </div>
    </div>
</div>

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
    // Logic Modal
    const modal = document.getElementById('modalDetail');
    const modalBody = document.getElementById('modalBody');
    const modalInvId = document.getElementById('modalInvId');

    function showDetail(orderId) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; 
        modalInvId.innerText = 'Purchase Invoice #' + orderId;
        
        modalBody.innerHTML = `
            <div class="flex flex-col items-center justify-center py-12 gap-3">
                <div class="animate-spin rounded-full h-8 w-8 border-[3px] border-primary border-t-transparent"></div>
                <span class="font-label-caps text-[10px] text-primary uppercase tracking-widest">Memuat Data...</span>
            </div>
        `;
        
        fetch('my_orders.php?ajax_detail=' + orderId)
            .then(response => response.text())
            .then(data => {
                setTimeout(() => { modalBody.innerHTML = data; }, 250);
            });
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto'; 
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
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