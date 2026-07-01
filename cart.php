<?php
session_start();
include __DIR__ . '/admin/config.php';

// PROTEKSI: Wajib login
if (!isset($_SESSION['customer_id'])) {
    header("Location: auth.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'] ?? 'Guest';

/* ==========================================
    LOGIKA AJAX UPDATE (Real-time)
========================================== */
if (isset($_POST['ajax_update'])) {
    $id = intval($_POST['id']);
    $qty = max(1, intval($_POST['qty']));
    
    $produk = $conn->query("SELECT stok, harga FROM phones WHERE id=$id")->fetch_assoc();
    if ($produk) {
        $finalQty = min($qty, $produk['stok']);
        $conn->query("UPDATE cart SET qty = $finalQty WHERE customer_id = $customer_id AND phone_id = $id");
        
        $res = $conn->query("SELECT SUM(c.qty * p.harga) as subtotal_all, SUM(c.qty) as total_qty 
                             FROM cart c JOIN phones p ON c.phone_id = p.id 
                             WHERE c.customer_id = $customer_id");
        $row = $res->fetch_assoc();
        
        $subtotal_all = $row['subtotal_all'] ?? 0;
        $grand_total = $subtotal_all; // Pajak dihilangkan, grand total = subtotal
        
        echo json_encode([
            'status' => 'success',
            'new_qty' => $finalQty,
            'item_subtotal' => "Rp " . number_format($produk['harga'] * $finalQty, 0, ',', '.'),
            'subtotal_all' => "Rp " . number_format($subtotal_all, 0, ',', '.'),
            'grand_total' => "Rp " . number_format($grand_total, 0, ',', '.'),
            'total_qty' => $row['total_qty'] ?? 0
        ]);
    }
    exit;
}

/* ==========================================
    LOGIKA AJAX DELETE
========================================== */
if (isset($_POST['ajax_delete'])) {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM cart WHERE customer_id = $customer_id AND phone_id = $id");
    echo json_encode(['status' => 'success']);
    exit;
}

/* ==========================================
    AMBIL DATA TERBARU
========================================== */
$dbCart = $conn->query("SELECT c.qty, p.* FROM cart c JOIN phones p ON c.phone_id = p.id WHERE c.customer_id = $customer_id");
$cartItems = [];
$subtotalAll = 0;
$totalQty = 0;

while ($row = $dbCart->fetch_assoc()) {
    $cartItems[] = $row;
    $subtotalAll += ($row['harga'] * $row['qty']);
    $totalQty += $row['qty'];
}

$grandTotal = $subtotalAll; // Pajak dihilangkan
?>

<!DOCTYPE html>
<html class="dark" lang="id">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no" name="viewport"/>
<title>Keranjang Belanja - PhoneStore</title>
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
            background-color: #0A0C10;
            color: #e0e3e5;
            -webkit-font-smoothing: antialiased;
        }
        .glass-panel {
            background: rgba(22, 27, 34, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(48, 54, 61, 0.5);
        }
        .text-glow {
            text-shadow: 0 0 15px rgba(37, 99, 235, 0.4);
        }
        .btn-glow:hover {
            box-shadow: 0 0 20px rgba(37, 99, 235, 0.3);
        }
        .swal2-dark-custom {
            background: #161B22 !important;
            border: 1px solid #30363D !important;
            color: #e0e3e5 !important;
            border-radius: 1.5rem !important;
        }
        input[type="number"]::-webkit-inner-spin-button, 
        input[type="number"]::-webkit-outer-spin-button { 
            -webkit-appearance: none; margin: 0; 
        }
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
                <a class="text-on-surface-variant font-medium hover:text-primary transition-colors font-label-caps text-[12px]" href="index.php">Beranda</a>
                <a class="text-primary font-bold border-b-2 border-primary pb-1 font-label-caps text-[12px]" href="cart.php">Keranjang</a>
                <a class="text-on-surface-variant font-medium hover:text-primary transition-colors font-label-caps text-[12px]" href="my_orders.php">Pesanan Saya</a>
            </nav>
        </div>
        
        <div class="flex items-center gap-2 md:gap-4">
            <div class="hidden lg:flex flex-col items-end mr-2">
                <span class="font-label-caps text-[10px] text-on-surface-variant">WELCOME BACK,</span>
                <span class="font-body-md text-[14px] font-bold text-white"><?= htmlspecialchars(strtoupper(explode(' ', $customer_name)[0])) ?></span>
            </div>
            
            <a href="profile.php" class="hidden md:flex material-symbols-outlined text-on-surface-variant hover:text-primary p-2 rounded-full hover:bg-surface-variant/50 transition-all scale-95 active:scale-90" title="Profile">person</a>
            <a href="cart.php" class="hidden md:flex material-symbols-outlined text-primary hover:bg-surface-variant/50 p-2 rounded-full transition-all scale-95 active:scale-90 relative" title="Cart">
                shopping_cart
                <span id="cartBadge" class="<?= $totalQty > 0 ? '' : 'hidden' ?> absolute top-0 right-0 bg-primary text-white text-[10px] w-4 h-4 md:w-5 md:h-5 rounded-full flex items-center justify-center font-bold shadow-lg <?= $totalQty > 0 ? 'animate-bounce' : '' ?>"><?= $totalQty ?></span>
            </a>
            <a href="?logout=true" class="hidden md:flex material-symbols-outlined text-on-surface-variant hover:text-error p-2 rounded-full transition-all" title="Logout">logout</a>

            <button id="mobileMenuBtn" class="md:hidden p-2 rounded-lg hover:bg-surface-variant/50 transition-all active:scale-90 text-white relative">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                <span id="mobileCartBadge" class="<?= $totalQty > 0 ? '' : 'hidden' ?> absolute -top-1 -right-1 bg-primary text-white text-[9px] w-4 h-4 rounded-full flex items-center justify-center font-bold shadow-lg"><?= $totalQty ?></span>
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
            <a href="cart.php" class="text-primary font-bold flex items-center gap-3 justify-between">
                <div class="flex items-center gap-3"><span class="material-symbols-outlined">shopping_cart</span> Keranjang</div>
                <span id="mobileMenuCartBadge" class="<?= $totalQty > 0 ? '' : 'hidden' ?> bg-primary text-white text-[10px] w-5 h-5 rounded-full flex items-center justify-center font-bold"><?= $totalQty ?></span>
            </a>
            <a href="my_orders.php" class="text-on-surface-variant hover:text-white font-medium flex items-center gap-3"><span class="material-symbols-outlined">receipt_long</span> Pesanan Saya</a>
            <a href="profile.php" class="text-on-surface-variant hover:text-white font-medium flex items-center gap-3"><span class="material-symbols-outlined">manage_accounts</span> Profil Akun</a>
            <a href="?logout=true" class="text-error hover:text-red-300 font-medium flex items-center gap-3 mt-2 pt-5 border-t border-outline-variant/30"><span class="material-symbols-outlined">logout</span> Logout</a>
        </div>
    </div>
</header>

<?php if (empty($cartItems)): ?>
    <main class="flex-grow flex items-center justify-center px-4 md:px-12 py-24 md:py-28" id="empty-state">
        <div class="max-w-2xl w-full text-center">
            <div class="bg-surface-container p-6 sm:p-8 md:p-12 rounded-[2rem] border border-outline-variant/20 shadow-2xl relative overflow-hidden">
                <div class="absolute -top-24 -left-24 w-64 h-64 bg-primary/10 blur-[100px] rounded-full"></div>
                <div class="absolute -bottom-24 -right-24 w-64 h-64 bg-secondary/10 blur-[100px] rounded-full"></div>
                
                <span class="material-symbols-outlined text-[60px] md:text-[120px] text-outline-variant mb-6 select-none" style="font-variation-settings: 'FILL' 1;">production_quantity_limits</span>
                
                <h2 class="font-headline-lg text-xl sm:text-2xl md:text-3xl text-on-surface mb-2">Wah, Kosong Banget!</h2>
                <p class="text-on-surface-variant text-sm md:text-base mb-8 max-w-sm mx-auto">Sepertinya Anda belum menambahkan gadget impian ke keranjang belanja Anda.</p>
                
                <a href="index.php" class="inline-block bg-secondary-container text-on-secondary-container px-8 md:px-10 py-3 md:py-4 rounded-full font-bold shadow-lg hover:scale-105 active:scale-95 transition-all text-sm md:text-base">
                    Mulai Belanja
                </a>
            </div>
        </div>
    </main>

<?php else: ?>
    <main class="flex-grow max-w-[1440px] w-full mx-auto px-4 md:px-12 py-24 md:py-28" id="cart-container">
        <div class="mb-6 md:mb-12">
            <h1 class="font-headline-lg text-2xl sm:text-3xl md:text-4xl text-glow text-primary inline-block font-bold">Keranjang Belanja</h1>
            <p class="text-on-surface-variant mt-1 md:mt-2 text-xs md:text-sm lg:text-base">Kelola item pilihan Anda sebelum melakukan pembayaran.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
            
            <div class="lg:col-span-8 space-y-4 md:space-y-6">
                <?php foreach ($cartItems as $item): 
                    $id = $item['id'];
                    $imgRes = $conn->query("SELECT image FROM phone_images WHERE phone_id=$id LIMIT 1")->fetch_assoc();
                    $gambar = ($imgRes && file_exists(__DIR__."/admin/uploads/".$imgRes['image'])) 
                              ? "admin/uploads/".$imgRes['image'] : "https://via.placeholder.com/200";
                ?>
                <div id="item-card-<?= $id ?>" class="glass-panel p-4 md:p-6 rounded-xl flex flex-col sm:flex-row gap-4 md:gap-6 items-center sm:items-stretch group transition-all hover:border-outline relative">
                    
                    <button class="absolute top-4 right-4 sm:relative sm:top-auto sm:right-auto sm:order-last material-symbols-outlined text-on-surface-variant hover:text-error transition-colors p-1 bg-surface-container-low/50 sm:bg-transparent rounded-lg" onclick="confirmDelete(<?= $id ?>, '<?= htmlspecialchars($item['nama_hp']) ?>')">delete</button>

                    <div class="w-full sm:w-32 h-32 sm:h-auto bg-surface-container-low rounded-lg overflow-hidden flex-shrink-0 flex items-center justify-center p-2">
                        <img class="max-w-full max-h-full object-contain drop-shadow-md" src="<?= $gambar ?>"/>
                    </div>
                    
                    <div class="flex-grow flex flex-col justify-between h-full w-full pt-1 sm:pt-0">
                        <div>
                            <span class="font-label-caps text-[10px] md:text-[12px] text-primary mb-1 block uppercase"><?= htmlspecialchars($item['brand']) ?></span>
                            <h3 class="font-headline-lg text-base md:text-xl text-on-surface font-bold pr-8 sm:pr-0 leading-tight"><?= htmlspecialchars($item['nama_hp']) ?></h3>
                            <p class="text-[11px] md:text-sm text-on-surface-variant mt-1">Sisa Stok: <?= $item['stok'] ?></p>
                        </div>
                        
                        <div class="flex flex-col min-[400px]:flex-row justify-between items-start min-[400px]:items-end mt-4 pt-4 border-t border-outline-variant/30 sm:border-none sm:pt-0 gap-4">
                            <div class="flex items-center gap-2 md:gap-3 bg-surface-container-high rounded-full p-1 border border-outline-variant/30">
                                <button onclick="changeQty(<?= $id ?>, -1, <?= $item['stok'] ?>)" class="w-7 h-7 md:w-8 md:h-8 rounded-full flex items-center justify-center hover:bg-surface-variant transition-colors text-primary font-bold active:scale-90">-</button>
                                <input type="number" id="qty-<?= $id ?>" value="<?= $item['qty'] ?>" onchange="updateCart(<?= $id ?>, this.value)" min="1" max="<?= $item['stok'] ?>" class="font-bold w-8 text-center bg-transparent border-none p-0 focus:ring-0 text-on-surface text-[16px] md:text-base">
                                <button onclick="changeQty(<?= $id ?>, 1, <?= $item['stok'] ?>)" class="w-7 h-7 md:w-8 md:h-8 rounded-full flex items-center justify-center hover:bg-surface-variant transition-colors text-primary font-bold active:scale-90">+</button>
                            </div>
                            
                            <div class="text-left min-[400px]:text-right w-full min-[400px]:w-auto">
                                <p class="text-[10px] md:text-[12px] text-on-surface-variant uppercase font-label-caps">Subtotal</p>
                                <p class="font-headline-lg text-[15px] md:text-lg text-primary font-bold" id="item-subtotal-<?= $id ?>">Rp <?= number_format($item['harga'] * $item['qty'], 0, ',', '.') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="lg:col-span-4 lg:sticky lg:top-32 mt-2 lg:mt-0">
                <div class="glass-panel p-5 md:p-8 rounded-xl shadow-lg">
                    <h2 class="font-headline-lg text-lg md:text-xl text-on-surface mb-4 md:mb-6 font-bold flex items-center gap-2 border-b border-outline-variant/30 pb-4">
                        <span class="material-symbols-outlined">receipt</span> Ringkasan
                    </h2>
                    
                    <div class="space-y-3 text-sm md:text-base">
                        <div class="flex justify-between">
                            <span class="text-on-surface-variant">Total Item</span>
                            <span class="text-on-surface font-bold" id="summ-total-qty"><?= $totalQty ?> Produk</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-on-surface-variant">Subtotal</span>
                            <span class="text-on-surface font-bold" id="summ-subtotal">Rp <?= number_format($subtotalAll, 0, ',', '.') ?></span>
                        </div>
                        <hr class="border-outline-variant/30 my-4"/>
                        <div class="flex flex-col gap-1">
                            <span class="font-bold text-on-surface">Total Harga</span>
                            <span class="font-headline-lg text-xl sm:text-2xl md:text-3xl text-primary text-glow font-bold tracking-tighter" id="summ-grand-total">Rp <?= number_format($grandTotal, 0, ',', '.') ?></span>
                        </div>
                    </div>
                    
                    <a href="checkout.php" class="flex justify-center items-center gap-2 w-full mt-6 md:mt-8 bg-primary-container text-on-primary-container py-3 md:py-4 rounded-xl font-bold btn-glow transition-all active:scale-95 shadow-[0_4px_14px_0_rgba(37,99,235,0.39)] text-[14px] md:text-base">
                        Checkout Sekarang <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </a>
                    <a class="block text-center mt-4 text-on-surface-variant hover:text-primary transition-colors text-[13px] md:text-sm font-medium" href="index.php">
                        Kembali Belanja
                    </a>
                </div>
                
                <div class="mt-4 md:mt-6 flex items-center gap-1.5 text-on-surface-variant/60 justify-center">
                    <span class="material-symbols-outlined text-[14px] md:text-[16px]">lock</span>
                    <span class="text-[9px] md:text-[11px] font-label-caps uppercase tracking-widest">Pembayaran Aman & Terenkripsi</span>
                </div>
            </div>
        </div>
    </main>
<?php endif; ?>

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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Logic untuk tombol + / - 
    function changeQty(id, change, maxStok) {
        let input = document.getElementById('qty-' + id);
        let currentVal = parseInt(input.value);
        let newVal = currentVal + change;
        
        if (newVal >= 1 && newVal <= maxStok) {
            input.value = newVal;
            updateCart(id, newVal);
        } else if (newVal > maxStok) {
            input.value = maxStok;
            updateCart(id, maxStok);
        }
    }

    // Update via AJAX ke backend
    function updateCart(id, qty) {
        if(qty < 1 || qty === "") return;
        
        let fd = new FormData();
        fd.append('ajax_update', '1');
        fd.append('id', id);
        fd.append('qty', qty);

        fetch('cart.php', { method: 'POST', body: fd })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                document.getElementById('item-subtotal-' + id).innerText = data.item_subtotal;
                
                document.getElementById('summ-total-qty').innerText = data.total_qty + " Produk";
                document.getElementById('summ-subtotal').innerText = data.subtotal_all;
                document.getElementById('summ-grand-total').innerText = data.grand_total;
                
                // Update Semua Badge Keranjang (Navbar Desktop, Icon Mobile Menu, Inner Mobile Menu)
                let badges = [
                    document.getElementById('cartBadge'),
                    document.getElementById('mobileCartBadge'),
                    document.getElementById('mobileMenuCartBadge')
                ];

                badges.forEach(b => {
                    if (b) {
                        b.innerText = data.total_qty;
                        if(data.total_qty > 0) {
                            b.classList.remove('hidden');
                            b.classList.add('animate-bounce');
                            setTimeout(() => b.classList.remove('animate-bounce'), 1000);
                        } else {
                            b.classList.add('hidden');
                        }
                    }
                });
            }
        });
    }

    // Konfirmasi Hapus Item dengan SweetAlert2 Dark Mode
    function confirmDelete(id, itemName) {
        Swal.fire({
            title: 'Hapus Item?',
            text: `Apakah Anda yakin ingin menghapus ${itemName} dari keranjang?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2563EB',
            cancelButtonColor: '#323537',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'swal2-dark-custom',
                cancelButton: 'text-on-surface'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                let fd = new FormData();
                fd.append('ajax_delete', '1');
                fd.append('id', id);

                fetch('cart.php', { method: 'POST', body: fd })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        const card = document.getElementById('item-card-' + id);
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            window.location.reload();
                        }, 300);
                    }
                });
            }
        });
    }

    // Mobile Hamburger Toggle
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

    // Interaksi Mouse "Glow" untuk Kartu Produk (Untuk Desktop/Mouse)
    document.querySelectorAll('.glass-panel').forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            card.style.setProperty('--mouse-x', `${x}px`);
            card.style.setProperty('--mouse-y', `${y}px`);
        });
    });
</script>
</body>
</html>