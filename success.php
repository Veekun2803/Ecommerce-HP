<?php
session_start();

// Jika tidak ada parameter WA, arahkan kembali ke beranda
if (!isset($_GET['wa'])) {
    header("Location: index.php");
    exit;
}

$link_wa = $_GET['wa'];
$customer_name = $_SESSION['customer_name'] ?? 'Guest';
?>
<!DOCTYPE html>
<html class="dark" lang="id">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>Pesanan Berhasil - PhoneStore Premium</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface-dim": "#101415",
                        "primary-container": "#2563eb",
                        "on-primary-fixed-variant": "#003ea8",
                        "surface-tint": "#b4c5ff",
                        "surface-variant": "#323537",
                        "on-tertiary-fixed-variant": "#3f465c",
                        "error-container": "#93000a",
                        "on-tertiary-fixed": "#131b2e",
                        "outline": "#8d90a0",
                        "secondary": "#4edea3",
                        "surface-container": "#1d2022",
                        "on-tertiary": "#283044",
                        "surface-bright": "#363a3b",
                        "on-tertiary-container": "#eef0ff",
                        "on-error": "#690005",
                        "secondary-container": "#00a572",
                        "inverse-primary": "#0053db",
                        "background": "#101415",
                        "secondary-fixed-dim": "#4edea3",
                        "surface-container-low": "#191c1e",
                        "primary": "#b4c5ff",
                        "tertiary-fixed-dim": "#bec6e0",
                        "on-primary-container": "#eeefff",
                        "on-secondary-container": "#00311f",
                        "on-secondary": "#003824",
                        "on-error-container": "#ffdad6",
                        "on-surface": "#e0e3e5",
                        "surface-container-high": "#272a2c",
                        "tertiary-container": "#656d84",
                        "inverse-on-surface": "#2d3133",
                        "on-surface-variant": "#c3c6d7",
                        "on-secondary-fixed-variant": "#005236",
                        "on-background": "#e0e3e5",
                        "inverse-surface": "#e0e3e5",
                        "secondary-fixed": "#6ffbbe",
                        "primary-fixed": "#dbe1ff",
                        "surface": "#101415",
                        "on-primary-fixed": "#00174b",
                        "surface-container-highest": "#323537",
                        "primary-fixed-dim": "#b4c5ff",
                        "on-secondary-fixed": "#002113",
                        "error": "#ffb4ab",
                        "tertiary-fixed": "#dae2fd",
                        "on-primary": "#002a78",
                        "outline-variant": "#434655",
                        "tertiary": "#bec6e0",
                        "surface-container-lowest": "#0b0f10"
                    },
                    "fontFamily": {
                        "label-caps": ["JetBrains Mono"],
                        "body-sm": ["Plus Jakarta Sans"],
                        "body-md": ["Plus Jakarta Sans"],
                        "display-lg": ["Plus Jakarta Sans"],
                        "headline-lg-mobile": ["Plus Jakarta Sans"],
                        "headline-lg": ["Plus Jakarta Sans"]
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

        .premium-glow {
            box-shadow: 0 0 40px rgba(37, 99, 235, 0.08), inset 0 0 20px rgba(255, 255, 255, 0.02);
        }

        .success-pulse {
            animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse-ring {
            0%, 100% { opacity: 0.1; transform: scale(1); }
            50% { opacity: 0.3; transform: scale(1.1); }
        }

        .step-line::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, #30363D 0%, rgba(48, 54, 61, 0) 100%);
            z-index: 0;
        }

        @media (max-width: 768px) {
            .step-line::after { display: none; }
        }
    </style>
</head>
<body class="flex flex-col min-h-screen">

<!-- TopNavBar -->
<header class="fixed top-0 z-50 w-full bg-background/80 backdrop-blur-xl border-b border-outline-variant/30 shadow-sm">
    <div class="flex justify-between items-center w-full px-4 md:px-8 py-3 md:py-4 max-w-[1440px] mx-auto relative">
        <div class="flex items-center gap-8">
            <a class="font-headline-lg text-[20px] md:text-[24px] font-bold text-on-background flex items-center gap-1" href="index.php">
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
                <span class="font-body-md text-[14px] font-bold text-on-background"><?= htmlspecialchars(strtoupper(explode(' ', $customer_name)[0])) ?></span>
            </div>
            
            <a href="profile.php" class="hidden md:flex material-symbols-outlined text-primary hover:bg-surface-variant/50 p-2 rounded-full transition-all scale-95 active:scale-90">person</a>
            <a href="cart.php" class="hidden md:flex material-symbols-outlined text-primary hover:bg-surface-variant/50 p-2 rounded-full transition-all scale-95 active:scale-90">shopping_cart</a>
            <a href="?logout=true" class="hidden md:flex material-symbols-outlined text-on-surface-variant hover:text-error p-2 rounded-full transition-all">logout</a>

            <!-- Mobile Hamburger Button -->
            <button id="mobileMenuBtn" class="md:hidden p-2 rounded-lg hover:bg-surface-variant/50 transition-all active:scale-90 text-on-background">
                <span class="material-symbols-outlined">menu</span>
            </button>
        </div>
    </div>

    <!-- Mobile Dropdown Menu -->
    <div id="mobileMenu" class="hidden absolute top-full left-0 w-full bg-background/95 backdrop-blur-xl border-b border-outline-variant/30 shadow-2xl flex-col origin-top transition-all duration-300">
        <div class="px-5 py-6 flex flex-col gap-5">
            <div class="flex items-center gap-3 border-b border-outline-variant/30 pb-5">
                <div class="w-12 h-12 rounded-full bg-primary/20 text-primary flex items-center justify-center font-bold text-xl">
                    <?= htmlspecialchars(substr(strtoupper($customer_name), 0, 1)) ?>
                </div>
                <div>
                    <p class="font-label-caps text-[10px] text-on-surface-variant uppercase tracking-wider">Welcome,</p>
                    <p class="text-base font-bold text-white"><?= htmlspecialchars($customer_name) ?></p>
                </div>
            </div>
            <a href="index.php" class="text-on-surface-variant hover:text-white font-medium flex items-center gap-3"><span class="material-symbols-outlined">home</span> Beranda</a>
            <a href="my_orders.php" class="text-on-surface-variant hover:text-white font-medium flex items-center gap-3"><span class="material-symbols-outlined">receipt_long</span> Pesanan Saya</a>
            <a href="profile.php" class="text-on-surface-variant hover:text-white font-medium flex items-center gap-3"><span class="material-symbols-outlined">manage_accounts</span> Profil Akun</a>
            <a href="?logout=true" class="text-error hover:text-red-300 font-medium flex items-center gap-3 mt-2 pt-5 border-t border-outline-variant/30"><span class="material-symbols-outlined">logout</span> Logout</a>
        </div>
    </div>
</header>

<!-- Main Content -->
<main class="flex-grow flex items-center justify-center pt-[100px] md:pt-[120px] pb-12 px-4 relative overflow-hidden">
    <!-- Ambient Atmosphere -->
    <div class="absolute top-1/4 left-1/4 w-[300px] md:w-[500px] h-[300px] md:h-[500px] bg-primary/5 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-1/4 right-1/4 w-[250px] md:w-[400px] h-[250px] md:h-[400px] bg-secondary/5 rounded-full blur-[100px] pointer-events-none"></div>
    
    <div class="max-w-[640px] w-full z-10">
        <!-- Premium Card Container -->
        <div class="bg-[#161B22] border border-[#30363D] rounded-2xl md:rounded-[2rem] p-6 md:p-12 premium-glow text-center relative overflow-hidden">
            
            <!-- Success Visual Element -->
            <div class="relative mb-8 inline-block">
                <div class="absolute inset-0 bg-secondary/20 rounded-full blur-xl success-pulse"></div>
                <div class="relative w-20 h-20 bg-secondary-container rounded-full flex items-center justify-center shadow-lg">
                    <span class="material-symbols-outlined text-white text-5xl" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                </div>
            </div>
            
            <!-- Messaging -->
            <h1 class="font-headline-lg text-[24px] md:text-[32px] font-bold text-white mb-2 tracking-tight">
                Pesanan Diterima!
            </h1>
            <p class="font-body-md text-sm md:text-base text-on-surface-variant mb-10 leading-relaxed max-w-[480px] mx-auto">
                Terima kasih telah berbelanja di PhoneStore. Pesanan Anda telah tercatat dalam sistem kami dan sedang menunggu verifikasi pembayaran.
            </p>
            
            <!-- Actions -->
            <div class="flex flex-col gap-4">
                <a class="w-full flex items-center justify-center gap-2 bg-secondary hover:bg-secondary/90 text-[#002113] font-bold py-3.5 md:py-4 px-6 md:px-8 rounded-xl transition-all duration-300 transform active:scale-95 shadow-lg shadow-secondary/10" 
                   href="<?= htmlspecialchars($link_wa) ?>" target="_blank"
                   style="box-shadow: rgba(78, 222, 163, 0.1) 0px 10px 15px -3px;">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">chat</span>
                    Konfirmasi ke WhatsApp
                </a>
                <a class="text-primary font-medium hover:underline text-sm md:text-base py-2 transition-colors mt-2" href="index.php">
                    Kembali ke Katalog
                </a>
            </div>
            
            <!-- Next Steps Section -->
            <div class="mt-10 md:mt-12 pt-8 md:pt-10 border-t border-outline-variant/20">
                <h3 class="font-label-caps text-[10px] md:text-[12px] text-outline mb-6 md:mb-8 text-center uppercase tracking-widest">Tahap Selanjutnya</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8 relative step-line">
                    <!-- Step 1 -->
                    <div class="flex flex-col items-center gap-2 relative z-10 bg-[#161B22] px-2">
                        <div class="w-10 h-10 rounded-full bg-surface-container-highest border border-outline-variant flex items-center justify-center mb-1">
                            <span class="material-symbols-outlined text-primary text-sm">send</span>
                        </div>
                        <span class="font-label-caps text-[11px] md:text-[12px] text-on-surface font-bold">Kirim Pesan WA</span>
                        <span class="font-body-sm text-[10px] md:text-[12px] text-on-surface-variant">Konfirmasi bukti bayar</span>
                    </div>
                    <!-- Step 2 -->
                    <div class="flex flex-col items-center gap-2 relative z-10 bg-[#161B22] px-2">
                        <div class="w-10 h-10 rounded-full bg-surface-container-highest border border-outline-variant flex items-center justify-center mb-1">
                            <span class="material-symbols-outlined text-primary text-sm">verified_user</span>
                        </div>
                        <span class="font-label-caps text-[11px] md:text-[12px] text-on-surface font-bold">Admin Verifikasi</span>
                        <span class="font-body-sm text-[10px] md:text-[12px] text-on-surface-variant">Pengecekan transaksi</span>
                    </div>
                    <!-- Step 3 -->
                    <div class="flex flex-col items-center gap-2 relative z-10 bg-[#161B22] px-2">
                        <div class="w-10 h-10 rounded-full bg-surface-container-highest border border-outline-variant flex items-center justify-center mb-1">
                            <span class="material-symbols-outlined text-primary text-sm">local_shipping</span>
                        </div>
                        <span class="font-label-caps text-[11px] md:text-[12px] text-on-surface font-bold">Barang Dikirim</span>
                        <span class="font-body-sm text-[10px] md:text-[12px] text-on-surface-variant">Resi akan diinfokan</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Support Section -->
        <div class="mt-6 flex justify-center">
            <div class="bg-surface-container-low border border-outline-variant/30 p-4 md:p-6 rounded-xl md:rounded-[2rem] flex flex-col md:flex-row items-center gap-4 max-w-sm w-full">
                <div class="bg-secondary/10 p-3 rounded-xl flex-shrink-0">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 28px;">support_agent</span>
                </div>
                <div class="text-center md:text-left">
                    <p class="font-label-caps text-[10px] md:text-[12px] text-secondary mb-1 uppercase tracking-widest font-bold">BANTUAN 24/7</p>
                    <p class="text-[12px] md:text-[14px] text-on-surface">Butuh bantuan? Hubungi CS kami.</p>
                </div>
            </div>
        </div>
        
    </div>
</main>

<!-- Footer -->
<footer class="bg-surface-container dark:bg-surface-container border-t border-outline-variant/20 py-8 mt-auto">
    <div class="flex flex-col md:flex-row justify-between items-center px-4 md:px-8 max-w-[1440px] mx-auto w-full gap-4">
        <div class="font-headline-lg text-[18px] md:text-[20px] font-bold text-on-background">
            PhoneStore
        </div>
        <div class="flex flex-col md:flex-row items-center gap-4 md:gap-8">
            <nav class="flex flex-wrap justify-center gap-4 md:gap-6">
                <a class="font-label-caps text-[10px] md:text-[12px] text-on-surface-variant hover:text-primary transition-colors" href="#">Kebijakan Privasi</a>
                <a class="font-label-caps text-[10px] md:text-[12px] text-on-surface-variant hover:text-primary transition-colors" href="#">Syarat & Ketentuan</a>
                <a class="font-label-caps text-[10px] md:text-[12px] text-on-surface-variant hover:text-primary transition-colors" href="#">Hubungi Kami</a>
            </nav>
            <p class="font-body-sm text-[10px] md:text-[12px] text-on-surface-variant">
                © 2024 PhoneStore Premium. All rights reserved.
            </p>
        </div>
    </div>
</footer>

<script>
    // Micro-interaction WA Button
    const waButton = document.querySelector('a[href^="https://wa.me"]');
    if (waButton) {
        waButton.addEventListener('mouseenter', () => {
            waButton.style.boxShadow = '0 0 30px rgba(78, 222, 163, 0.3)';
        });
        waButton.addEventListener('mouseleave', () => {
            waButton.style.boxShadow = '0 10px 15px -3px rgba(78, 222, 163, 0.1)';
        });
    }

    // Toggle Mobile Menu
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
</script>

</body>
</html>