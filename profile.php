<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: auth.php");
    exit;
}

include __DIR__ . '/admin/config.php';
$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'] ?? 'Guest';

/* ==========================================================================
   HANDLE UPDATE PROFIL
   ========================================================================== */
if (isset($_POST['update_profile'])) {
    $nama   = htmlspecialchars($_POST['nama_lengkap']);
    $wa     = htmlspecialchars($_POST['no_wa']);
    $alamat = htmlspecialchars($_POST['alamat']);

    $stmt = $conn->prepare("UPDATE customers SET nama_lengkap=?, no_wa=?, alamat=? WHERE id=?");
    $stmt->bind_param("sssi", $nama, $wa, $alamat, $customer_id);
    
    if ($stmt->execute()) {
        $_SESSION['customer_name'] = $nama; // Update session name
        $customer_name = $nama; // Update local variable
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Profil berhasil diperbarui!'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Gagal memperbarui profil.'];
    }
    header("Location: profile.php");
    exit;
}

/* ==========================================================================
   HANDLE GANTI PASSWORD
   ========================================================================== */
if (isset($_POST['change_password'])) {
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $cfm_pass = $_POST['confirm_password'];

    // Ambil password lama dari DB
    $user = $conn->query("SELECT password FROM customers WHERE id = $customer_id")->fetch_assoc();

    // Verifikasi (Asumsi: Menggunakan password_hash)
    if (!password_verify($old_pass, $user['password'])) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Kata sandi lama salah!'];
    } elseif ($new_pass !== $cfm_pass) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Konfirmasi kata sandi tidak cocok!'];
    } else {
        $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE customers SET password=? WHERE id=?");
        $stmt->bind_param("si", $hashed_pass, $customer_id);
        $stmt->execute();
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Kata sandi berhasil diganti!'];
    }
    header("Location: profile.php");
    exit;
}

// Ambil data terbaru customer
$data = $conn->query("SELECT * FROM customers WHERE id = $customer_id")->fetch_assoc();

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
<title>Profil Saya | PhoneStore Premium</title>
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
    .neon-glow:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 10px rgba(37, 99, 235, 0.2);
    }
    .avatar-gradient {
        background: linear-gradient(135deg, #2563eb 0%, #10b981 100%);
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
    
    /* SweetAlert2 Dark Mode Customization */
    .swal2-dark-custom {
        background: #161B22 !important;
        border: 1px solid #30363D !important;
        color: #e0e3e5 !important;
        border-radius: 1.5rem !important;
    }
    .swal2-title { color: #ffffff !important; font-family: 'Plus Jakarta Sans', sans-serif !important; }
    .swal2-html-container { color: #c3c6d7 !important; font-family: 'Plus Jakarta Sans', sans-serif !important; }
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
                <span class="font-body-md text-[14px] font-bold text-white"><?= htmlspecialchars(strtoupper(explode(' ', $data['nama_lengkap'] ?? $data['username'])[0])) ?></span>
            </div>
            
            <a href="profile.php" class="hidden md:flex material-symbols-outlined text-primary p-2 rounded-full bg-surface-variant/50 transition-all scale-95 active:scale-90" title="Profile">person</a>
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
                    <?= htmlspecialchars(substr(strtoupper($data['nama_lengkap'] ?? $data['username']), 0, 1)) ?>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="font-label-caps text-[10px] text-on-surface-variant uppercase tracking-wider">Welcome,</p>
                    <p class="text-base font-bold text-white truncate break-words"><?= htmlspecialchars($data['nama_lengkap'] ?? $data['username']) ?></p>
                </div>
            </div>
            <a href="index.php" class="text-on-surface-variant hover:text-white font-medium flex items-center gap-3"><span class="material-symbols-outlined">home</span> Beranda</a>
            <a href="cart.php" class="text-on-surface-variant hover:text-white font-medium flex items-center gap-3 justify-between">
                <div class="flex items-center gap-3"><span class="material-symbols-outlined">shopping_cart</span> Keranjang</div>
                <span id="mobileMenuCartBadge" class="<?= $total_cart > 0 ? '' : 'hidden' ?> bg-primary text-white text-[10px] w-5 h-5 rounded-full flex items-center justify-center font-bold"><?= $total_cart ?></span>
            </a>
            <a href="my_orders.php" class="text-on-surface-variant hover:text-white font-medium flex items-center gap-3"><span class="material-symbols-outlined">receipt_long</span> Pesanan Saya</a>
            <a href="profile.php" class="text-primary font-bold flex items-center gap-3"><span class="material-symbols-outlined">manage_accounts</span> Profil Akun</a>
            <a href="?logout=true" class="text-error hover:text-red-300 font-medium flex items-center gap-3 mt-2 pt-5 border-t border-outline-variant/30"><span class="material-symbols-outlined">logout</span> Logout</a>
        </div>
    </div>
</header>

<main class="flex-grow max-w-[1440px] mx-auto w-full px-4 md:px-12 pt-28 pb-12 grid grid-cols-1 lg:grid-cols-12 gap-8 relative z-10">
    
    <aside class="lg:col-span-3 space-y-6">
        <div class="glass-card rounded-3xl p-6 flex flex-col items-center text-center animate-in fade-in slide-in-from-left duration-700">
            <div class="w-24 h-24 avatar-gradient rounded-3xl flex items-center justify-center mb-4 shadow-lg shadow-primary/20">
                <span class="text-white text-4xl font-bold"><?= htmlspecialchars(substr(strtoupper($data['nama_lengkap'] ?? $data['username']), 0, 1)) ?></span>
            </div>
            <h2 class="text-xl md:text-2xl text-white font-bold leading-tight"><?= htmlspecialchars($data['nama_lengkap'] ?? $data['username']) ?></h2>
            <div class="mt-3 bg-secondary/10 border border-secondary/20 px-4 py-1.5 rounded-full">
                <span class="font-label-caps text-[10px] text-secondary tracking-widest uppercase font-bold">Pelanggan Setia</span>
            </div>
            <div class="w-full h-px bg-outline-variant/30 my-6"></div>
            <div class="w-full space-y-2">
                <a class="flex items-center gap-3 w-full p-3 rounded-2xl bg-primary-container text-white font-medium transition-all" href="profile.php">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">settings</span>
                    <span>Pengaturan Akun</span>
                </a>
                <a class="flex items-center gap-3 w-full p-3 rounded-2xl text-on-surface-variant hover:bg-surface-variant/50 hover:text-primary transition-all" href="my_orders.php">
                    <span class="material-symbols-outlined">shopping_bag</span>
                    <span>Pesanan Saya</span>
                </a>
            </div>
        </div>
    </aside>

    <div class="lg:col-span-9 space-y-6 md:space-y-8">
        
        <div class="mb-2">
            <h1 class="text-3xl md:text-4xl text-white font-bold tracking-tight">Pengaturan Akun</h1>
            <p class="text-on-surface-variant mt-2 text-sm md:text-base">Kelola informasi pribadi dan keamanan akun Anda untuk pengalaman berbelanja yang lebih baik.</p>
        </div>
        
        <div class="grid grid-cols-1 gap-6 md:gap-8">
            
            <section class="glass-card rounded-3xl p-6 md:p-8 animate-in fade-in slide-in-from-bottom duration-700 delay-100">
                <div class="flex items-center gap-3 mb-6 md:mb-8">
                    <div class="p-2 bg-primary/10 rounded-xl">
                        <span class="material-symbols-outlined text-primary">badge</span>
                    </div>
                    <h3 class="text-xl md:text-2xl text-white font-bold">Informasi Pribadi</h3>
                </div>
                
                <form method="POST" class="space-y-5 md:space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">
                        <div class="space-y-2">
                            <label class="font-label-caps text-[11px] text-on-surface-variant block ml-1 uppercase tracking-widest font-bold">Nama Lengkap</label>
                            <input name="nama_lengkap" type="text" required value="<?= htmlspecialchars($data['nama_lengkap'] ?? '') ?>" 
                                class="w-full bg-[#0D1117] border border-outline-variant/50 rounded-2xl px-5 py-3.5 text-on-surface text-sm md:text-base neon-glow transition-all" placeholder="Masukkan nama lengkap">
                        </div>
                        <div class="space-y-2">
                            <label class="font-label-caps text-[11px] text-on-surface-variant block ml-1 uppercase tracking-widest font-bold">Nomor WhatsApp</label>
                            <input name="no_wa" type="tel" required value="<?= htmlspecialchars($data['no_wa'] ?? '') ?>" 
                                class="w-full bg-[#0D1117] border border-outline-variant/50 rounded-2xl px-5 py-3.5 text-on-surface text-sm md:text-base neon-glow transition-all" placeholder="Contoh: 0812...">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="font-label-caps text-[11px] text-on-surface-variant block ml-1 uppercase tracking-widest font-bold">Alamat Pengiriman Utama</label>
                        <textarea name="alamat" required rows="3" 
                            class="w-full bg-[#0D1117] border border-outline-variant/50 rounded-2xl px-5 py-3.5 text-on-surface text-sm md:text-base neon-glow transition-all resize-none" placeholder="Masukkan alamat lengkap pengiriman"><?= htmlspecialchars($data['alamat'] ?? '') ?></textarea>
                    </div>
                    <div class="pt-2 flex justify-end">
                        <button name="update_profile" type="submit" class="w-full md:w-auto bg-primary-container hover:bg-blue-600 text-white font-bold px-8 py-3.5 md:py-4 rounded-2xl transition-all shadow-lg shadow-primary-container/20 active:scale-[0.98] text-sm tracking-wide uppercase">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </section>

            <section class="glass-card rounded-3xl p-6 md:p-8 animate-in fade-in slide-in-from-bottom duration-700 delay-200">
                <div class="flex items-center gap-3 mb-6 md:mb-8">
                    <div class="p-2 bg-secondary/10 rounded-xl">
                        <span class="material-symbols-outlined text-secondary">security</span>
                    </div>
                    <h3 class="text-xl md:text-2xl text-white font-bold">Keamanan Akun</h3>
                </div>
                
                <form method="POST" class="space-y-5 md:space-y-6">
                    <div class="space-y-2">
                        <label class="font-label-caps text-[11px] text-on-surface-variant block ml-1 uppercase tracking-widest font-bold">Kata Sandi Lama</label>
                        <div class="relative">
                            <input name="old_password" required type="password" 
                                class="w-full bg-[#0D1117] border border-outline-variant/50 rounded-2xl px-5 py-3.5 text-on-surface text-sm md:text-base neon-glow transition-all pr-12" placeholder="••••••••">
                            <button type="button" class="absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-primary transition-colors">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">
                        <div class="space-y-2">
                            <label class="font-label-caps text-[11px] text-on-surface-variant block ml-1 uppercase tracking-widest font-bold">Sandi Baru</label>
                            <div class="relative">
                                <input name="new_password" required type="password" 
                                    class="w-full bg-[#0D1117] border border-outline-variant/50 rounded-2xl px-5 py-3.5 text-on-surface text-sm md:text-base neon-glow transition-all pr-12" placeholder="Minimal 8 karakter">
                                <button type="button" class="absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-primary transition-colors">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="font-label-caps text-[11px] text-on-surface-variant block ml-1 uppercase tracking-widest font-bold">Konfirmasi Sandi Baru</label>
                            <div class="relative">
                                <input name="confirm_password" required type="password" 
                                    class="w-full bg-[#0D1117] border border-outline-variant/50 rounded-2xl px-5 py-3.5 text-on-surface text-sm md:text-base neon-glow transition-all pr-12" placeholder="Ulangi sandi baru">
                                <button type="button" class="absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-primary transition-colors">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="pt-2 flex justify-end">
                        <button name="change_password" type="submit" class="w-full md:w-auto bg-surface-container border border-outline-variant hover:border-primary hover:text-primary text-on-surface font-bold px-8 py-3.5 md:py-4 rounded-2xl transition-all active:scale-[0.98] text-sm tracking-wide uppercase shadow-lg shadow-black/20">
                            Update Kata Sandi
                        </button>
                    </div>
                </form>
            </section>
            
        </div>
    </div>
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

<?php if(isset($_SESSION['flash'])): ?>
<script>
    Swal.fire({
        icon: '<?= $_SESSION['flash']['type'] ?>',
        title: '<?= $_SESSION['flash']['type'] == "success" ? "Berhasil!" : "Ups!" ?>',
        text: '<?= $_SESSION['flash']['msg'] ?>',
        confirmButtonColor: '#2563eb',
        customClass: { popup: 'swal2-dark-custom' }
    });
</script>
<?php unset($_SESSION['flash']); endif; ?>

<script>
    // Fitur Toggle Password Visibility
    document.querySelectorAll('button[type="button"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.previousElementSibling;
            if (!input || input.tagName !== 'INPUT') return;
            
            const icon = btn.querySelector('.material-symbols-outlined');
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerText = 'visibility_off';
            } else {
                input.type = 'password';
                icon.innerText = 'visibility';
            }
        });
    });

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
</script>

</body>
</html>