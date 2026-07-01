<?php
session_start();
include __DIR__ . '/admin/config.php';

// Jika sudah login, langsung lempar ke index
if (isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit;
}

$error = "";
$success = "";
$active_tab = "login"; // Default tab aktif

// PROSES REGISTRASI
if (isset($_POST['register'])) {
    $active_tab = "register";
    $nama = $conn->real_escape_string($_POST['nama']);
    $email = $conn->real_escape_string($_POST['email']);
    $pass = $_POST['password'];
    
    // Verifikasi Centang Manual
    if (!isset($_POST['human_verify'])) {
        $error = "Silakan centang verifikasi keamanan.";
    } else {
        $checkEmail = $conn->query("SELECT email FROM customers WHERE email='$email'");
        if ($checkEmail->num_rows > 0) {
            $error = "Email sudah digunakan!";
        } else {
            $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
            $query = "INSERT INTO customers (nama_lengkap, email, password) VALUES ('$nama', '$email', '$hashed_password')";
            if ($conn->query($query)) {
                $success = "Akun berhasil dibuat! Silakan login.";
                $active_tab = "login"; // Pindah ke login setelah sukses
            } else {
                $error = "Gagal mendaftar, coba lagi.";
            }
        }
    }
}

// PROSES LOGIN
if (isset($_POST['login'])) {
    $active_tab = "login";
    $email = $conn->real_escape_string($_POST['email']);
    $pass = $_POST['password'];

    $res = $conn->query("SELECT * FROM customers WHERE email='$email'");
    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();
        if (password_verify($pass, $user['password'])) {
            $_SESSION['customer_id'] = $user['id'];
            $_SESSION['customer_name'] = $user['nama_lengkap'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak terdaftar!";
    }
}

// Cek apakah auth modal perlu dibuka sejak awal
$show_auth = (isset($_POST['login']) || isset($_POST['register']) || $error || $success);
?>

<!DOCTYPE html>
<html class="dark" lang="id">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>PhoneStore Premium | Ganti HP Jadi Lebih Mudah</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&family=JetBrains+Mono:wght@500&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-secondary-container": "#00311f",
                        "surface-container-low": "#191c1e",
                        "secondary-fixed": "#6ffbbe",
                        "surface-container-high": "#272a2c",
                        "on-tertiary-fixed-variant": "#3f465c",
                        "on-secondary-fixed": "#002113",
                        "error-container": "#93000a",
                        "on-primary-container": "#eeefff",
                        "inverse-surface": "#e0e3e5",
                        "tertiary-fixed": "#dae2fd",
                        "primary-container": "#2563eb",
                        "background": "#101415",
                        "tertiary-fixed-dim": "#bec6e0",
                        "outline": "#8d90a0",
                        "on-surface-variant": "#c3c6d7",
                        "on-primary-fixed": "#00174b",
                        "tertiary": "#bec6e0",
                        "surface-variant": "#323537",
                        "surface-tint": "#b4c5ff",
                        "secondary-fixed-dim": "#4edea3",
                        "on-tertiary-fixed": "#131b2e",
                        "on-surface": "#e0e3e5",
                        "on-secondary": "#003824",
                        "on-tertiary-container": "#eef0ff",
                        "on-error": "#690005",
                        "surface-container-lowest": "#0b0f10",
                        "surface-container": "#1d2022",
                        "error": "#ffb4ab",
                        "on-secondary-fixed-variant": "#005236",
                        "surface-dim": "#101415",
                        "surface": "#101415",
                        "on-primary-fixed-variant": "#003ea8",
                        "on-error-container": "#ffdad6",
                        "on-background": "#e0e3e5",
                        "inverse-on-surface": "#2d3133",
                        "primary-fixed-dim": "#b4c5ff",
                        "tertiary-container": "#656d84",
                        "outline-variant": "#434655",
                        "surface-container-highest": "#323537",
                        "primary-fixed": "#dbe1ff",
                        "primary": "#b4c5ff",
                        "secondary-container": "#00a572",
                        "on-tertiary": "#283044",
                        "secondary": "#4edea3",
                        "on-primary": "#002a78",
                        "inverse-primary": "#0053db",
                        "surface-bright": "#363a3b"
                    },
                    "fontFamily": {
                        "label-caps": ["JetBrains Mono"],
                        "headline-lg-mobile": ["Plus Jakarta Sans"],
                        "body-sm": ["Plus Jakarta Sans"],
                        "headline-lg": ["Plus Jakarta Sans"],
                        "display-lg": ["Plus Jakarta Sans"],
                        "body-md": ["Plus Jakarta Sans"]
                    }
                }
            }
        }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        
        .glass-card {
            background: rgba(29, 32, 34, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(141, 144, 160, 0.2);
        }

        .auth-input:focus {
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
            border-color: #2563eb;
        }

        .cinematic-gradient {
            background: radial-gradient(circle at 70% 30%, rgba(37, 99, 235, 0.15) 0%, transparent 60%),
                        radial-gradient(circle at 20% 80%, rgba(78, 222, 163, 0.05) 0%, transparent 50%);
        }

        .active-tab-indicator {
            position: absolute;
            bottom: -1px;
            height: 2px;
            background: #2563eb;
            transition: all 0.3s ease;
        }

        /* Hide Scrollbar */
        ::-webkit-scrollbar { width: 0px; background: transparent; }
    </style>
</head>
<body class="bg-background text-on-background font-body-md selection:bg-primary-container selection:text-white min-h-screen flex flex-col <?= $show_auth ? 'overflow-hidden' : '' ?>">

<header class="fixed top-0 left-0 right-0 z-[100] bg-background/80 backdrop-blur-xl border-b border-outline-variant/30 transition-all">
    <nav class="flex justify-between items-center w-full px-4 md:px-12 py-3 md:py-4 max-w-[1440px] mx-auto relative">
        <div class="font-headline-lg text-xl md:text-[32px] font-bold text-on-background flex items-center gap-1 cursor-pointer" onclick="hideAuth()">
            PhoneStore<span class="w-1.5 h-1.5 md:w-2 md:h-2 rounded-full bg-primary mb-1"></span>
        </div>
        
        <div class="hidden md:flex items-center gap-8">
            <a class="text-primary font-bold border-b-2 border-primary pb-1 font-label-caps text-xs tracking-widest" href="#">BERANDA</a>
            <a class="text-on-surface-variant font-medium hover:text-primary transition-colors font-label-caps text-xs tracking-widest" href="#features">FITUR</a>
            <a class="text-on-surface-variant font-medium hover:text-primary transition-colors font-label-caps text-xs tracking-widest" href="#">PROMO</a>
            <a class="text-on-surface-variant font-medium hover:text-primary transition-colors font-label-caps text-xs tracking-widest" href="#">BANTUAN</a>
        </div>
        
        <div class="flex items-center gap-2 md:gap-4">
            <button class="hidden md:block px-6 py-2.5 bg-primary-container text-white rounded-xl font-label-caps text-xs tracking-widest font-bold hover:brightness-110 active:scale-95 transition-all shadow-[0_0_15px_rgba(37,99,235,0.4)]" onclick="toggleAuth('login')">
                MASUK
            </button>
            <button id="mobileMenuBtn" class="md:hidden p-2 rounded-lg hover:bg-surface-variant/50 transition-all active:scale-90 text-on-background">
                <span class="material-symbols-outlined">menu</span>
            </button>
        </div>
    </nav>

    <div id="mobileMenu" class="hidden absolute top-full left-0 w-full bg-background/95 backdrop-blur-xl border-b border-outline-variant/30 shadow-2xl flex-col origin-top transition-all duration-300">
        <div class="px-5 py-6 flex flex-col gap-4">
            <a href="#" class="text-on-surface-variant hover:text-white font-medium flex items-center gap-3" onclick="hideAuth(); toggleMobileMenu();"><span class="material-symbols-outlined text-[20px]">home</span> Beranda</a>
            <a href="#features" class="text-on-surface-variant hover:text-white font-medium flex items-center gap-3" onclick="hideAuth(); toggleMobileMenu();"><span class="material-symbols-outlined text-[20px]">star</span> Fitur Keunggulan</a>
            <a href="#" class="text-on-surface-variant hover:text-white font-medium flex items-center gap-3" onclick="hideAuth(); toggleMobileMenu();"><span class="material-symbols-outlined text-[20px]">local_offer</span> Promo</a>
            <a href="#" class="text-on-surface-variant hover:text-white font-medium flex items-center gap-3" onclick="hideAuth(); toggleMobileMenu();"><span class="material-symbols-outlined text-[20px]">help</span> Bantuan</a>
            
            <div class="border-t border-outline-variant/30 pt-4 mt-2">
                <button class="w-full px-6 py-3.5 bg-primary-container text-white rounded-xl font-label-caps text-[11px] tracking-widest font-bold hover:brightness-110 active:scale-95 transition-all shadow-lg" onclick="toggleAuth('login')">
                    MASUK / DAFTAR
                </button>
            </div>
        </div>
    </div>
</header>

<main class="relative flex-grow">
    <section class="relative pt-20 md:pt-24 overflow-hidden min-h-screen cinematic-gradient pb-10 md:pb-20" id="landing-page">
        
        <div class="max-w-[1440px] mx-auto px-4 md:px-12 flex flex-col md:flex-row items-center justify-between gap-8 md:gap-12 pt-8 md:pt-16">
            
            <div class="flex-1 space-y-4 md:space-y-6 text-center md:text-left z-10 w-full mt-4 md:mt-0">
                <span class="inline-block px-3 py-1.5 bg-primary/10 border border-primary/20 text-primary rounded-full font-label-caps text-[9px] md:text-[11px] font-bold tracking-widest">
                    EDISI TERBATAS 2024
                </span>
                
                <h1 class="font-display-lg text-4xl sm:text-5xl md:text-6xl lg:text-[72px] font-bold leading-[1.1] md:leading-tight md:max-w-xl mx-auto md:mx-0">
                    Ganti HP Jadi <br/><span class="text-primary text-glow block mt-1">Lebih Mudah.</span>
                </h1>
                
                <p class="text-on-surface-variant text-sm md:text-base lg:text-lg max-w-sm md:max-w-lg mx-auto md:mx-0 leading-relaxed px-4 md:px-0">
                    Rasakan pengalaman berbelanja gadget premium dengan layanan eksklusif, pengiriman secepat kilat, dan jaminan keaslian 100%.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-3 md:gap-4 pt-4 md:pt-6 justify-center md:justify-start px-4 md:px-0">
                    <button class="w-full sm:w-auto px-6 md:px-8 py-3.5 md:py-4 bg-primary-container text-white rounded-xl font-bold hover:shadow-[0_0_30px_-5px_rgba(37,99,235,0.5)] active:scale-95 transition-all text-sm md:text-base flex items-center justify-center gap-2" onclick="toggleAuth('register')">
                        Mulai Belanja Gratis <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </button>
                    <a href="#features" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 md:px-8 py-3.5 md:py-4 bg-surface-container border border-outline-variant text-on-surface rounded-xl font-bold hover:bg-surface-container-high active:scale-95 transition-all text-sm md:text-base">
                        Lihat Fitur <span class="material-symbols-outlined text-[18px]">expand_more</span>
                    </a>
                </div>
            </div>

            <div class="flex-1 relative w-full aspect-square md:aspect-auto h-[320px] sm:h-[400px] md:h-[600px] group mt-6 md:mt-0">
                <div class="absolute inset-0 bg-primary/10 blur-[80px] md:blur-[100px] rounded-full group-hover:bg-primary/20 transition-all duration-1000"></div>
                <img alt="Premium Smartphone" class="w-full h-full object-contain relative z-10 drop-shadow-2xl rounded-xl" src="https://images.unsplash.com/photo-1616348436168-de43ad0db179?q=80&w=2000&auto=format&fit=crop"/>
            </div>
        </div>

        <div id="features" class="max-w-[1440px] mx-auto px-4 md:px-12 py-16 md:py-20 mt-4 md:mt-10 grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-8 scroll-mt-24">
            <div class="bg-surface-container p-6 md:p-10 rounded-[1.5rem] md:rounded-[2rem] space-y-3 md:space-y-4 border border-outline-variant/30 hover:border-primary/50 transition-colors group">
                <div class="w-12 h-12 md:w-14 md:h-14 bg-primary/10 rounded-xl flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-[24px] md:text-[28px]" style="font-variation-settings: 'FILL' 1;">bolt</span>
                </div>
                <h3 class="font-headline-lg text-lg md:text-xl font-bold text-on-background">Pengiriman Cepat</h3>
                <p class="text-on-surface-variant text-[13px] md:text-sm leading-relaxed">Sistem logistik cerdas kami memastikan gadget impian tiba di tangan Anda dalam waktu kurang dari 24 jam.</p>
            </div>
            <div class="bg-surface-container p-6 md:p-10 rounded-[1.5rem] md:rounded-[2rem] space-y-3 md:space-y-4 border border-outline-variant/30 hover:border-secondary/50 transition-colors group">
                <div class="w-12 h-12 md:w-14 md:h-14 bg-secondary/10 rounded-xl flex items-center justify-center text-secondary group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-[24px] md:text-[28px]" style="font-variation-settings: 'FILL' 1;">verified_user</span>
                </div>
                <h3 class="font-headline-lg text-lg md:text-xl font-bold text-on-background">Garansi Resmi</h3>
                <p class="text-on-surface-variant text-[13px] md:text-sm leading-relaxed">Semua unit dilindungi oleh garansi resmi pabrikan selama 2 tahun dengan dukungan purna jual premium.</p>
            </div>
            <div class="bg-surface-container p-6 md:p-10 rounded-[1.5rem] md:rounded-[2rem] space-y-3 md:space-y-4 border border-outline-variant/30 hover:border-primary/50 transition-colors group">
                <div class="w-12 h-12 md:w-14 md:h-14 bg-primary/10 rounded-xl flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-[24px] md:text-[28px]" style="font-variation-settings: 'FILL' 1;">payments</span>
                </div>
                <h3 class="font-headline-lg text-lg md:text-xl font-bold text-on-background">Pembayaran Aman</h3>
                <p class="text-on-surface-variant text-[13px] md:text-sm leading-relaxed">Pilihan metode pembayaran terenkripsi mulai dari kartu kredit, transfer bank, hingga cicilan 0%.</p>
            </div>
        </div>
    </section>

    <section class="fixed inset-0 z-[200] <?= $show_auth ? 'flex' : 'hidden' ?> bg-surface-container-lowest/90 backdrop-blur-md items-center justify-center p-4" id="auth-section">
        <div class="max-w-md w-full glass-card rounded-[2rem] overflow-hidden shadow-2xl relative animate-in fade-in zoom-in duration-300 border border-outline-variant/40">
            
            <button class="absolute top-4 right-4 md:top-6 md:right-6 text-on-surface-variant hover:text-white transition-colors z-10 bg-surface-container-high/60 p-2 rounded-full backdrop-blur-md" onclick="hideAuth()">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
            
            <div class="p-6 md:p-8 pt-12 md:pt-14">
                
                <div class="text-center mb-6 md:mb-8">
                    <h2 class="text-2xl md:text-3xl font-black text-on-background tracking-tighter">Phone<span class="text-primary">Store.</span></h2>
                    <p class="text-on-surface-variant mt-1.5 text-xs md:text-sm">Silakan masuk atau daftar akun baru</p>
                </div>

                <?php if($error): ?>
                    <div class="mb-5 p-3 md:p-3.5 bg-error-container/20 text-error text-[13px] md:text-sm rounded-xl border border-error/30 text-center font-bold flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[16px] md:text-[18px]">error</span> <?= $error ?>
                    </div>
                <?php endif; ?>
                <?php if($success): ?>
                    <div class="mb-5 p-3 md:p-3.5 bg-secondary-container/20 text-secondary text-[13px] md:text-sm rounded-xl border border-secondary/30 text-center font-bold flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[16px] md:text-[18px]">check_circle</span> <?= $success ?>
                    </div>
                <?php endif; ?>

                <div class="flex relative border-b border-outline-variant/30 mb-6 md:mb-8">
                    <button class="flex-1 py-3 text-sm font-bold text-primary transition-colors" id="tab-login-btn" onclick="switchTab('login')">Login</button>
                    <button class="flex-1 py-3 text-sm font-bold text-on-surface-variant transition-colors" id="tab-register-btn" onclick="switchTab('register')">Daftar</button>
                    <div class="active-tab-indicator w-1/2 left-0" id="tab-indicator"></div>
                </div>

                <form method="POST" class="space-y-4 md:space-y-5" id="form-login">
                    <div class="space-y-1.5">
                        <label class="font-label-caps text-[10px] md:text-xs text-on-surface-variant uppercase tracking-widest ml-1">Email</label>
                        <input name="email" required class="auth-input w-full bg-surface-container-low border border-outline-variant/50 text-white px-4 py-3 md:px-5 md:py-3.5 rounded-xl outline-none transition-all text-sm md:text-base" placeholder="contoh@email.com" type="email"/>
                    </div>
                    <div class="space-y-1.5">
                        <label class="font-label-caps text-[10px] md:text-xs text-on-surface-variant uppercase tracking-widest ml-1">Password</label>
                        <input name="password" required class="auth-input w-full bg-surface-container-low border border-outline-variant/50 text-white px-4 py-3 md:px-5 md:py-3.5 rounded-xl outline-none transition-all text-sm md:text-base" placeholder="••••••••" type="password"/>
                    </div>
                    <button type="submit" name="login" class="w-full py-3.5 md:py-4 bg-primary-container text-white rounded-xl font-bold hover:brightness-110 shadow-[0_0_20px_rgba(37,99,235,0.3)] active:scale-[0.98] transition-all mt-4 flex items-center justify-center gap-2 uppercase tracking-wide text-[13px] md:text-sm">
                        Masuk ke Akun
                        <span class="material-symbols-outlined text-[18px]">login</span>
                    </button>
                </form>

                <form method="POST" class="space-y-4 md:space-y-5 hidden" id="form-register">
                    <div class="space-y-1.5">
                        <label class="font-label-caps text-[10px] md:text-xs text-on-surface-variant uppercase tracking-widest ml-1">Nama Lengkap</label>
                        <input name="nama" required class="auth-input w-full bg-surface-container-low border border-outline-variant/50 text-white px-4 py-3 md:px-5 md:py-3.5 rounded-xl outline-none transition-all text-sm md:text-base" placeholder="Faisal" type="text"/>
                    </div>
                    <div class="space-y-1.5">
                        <label class="font-label-caps text-[10px] md:text-xs text-on-surface-variant uppercase tracking-widest ml-1">Email</label>
                        <input name="email" required class="auth-input w-full bg-surface-container-low border border-outline-variant/50 text-white px-4 py-3 md:px-5 md:py-3.5 rounded-xl outline-none transition-all text-sm md:text-base" placeholder="contoh@email.com" type="email"/>
                    </div>
                    <div class="space-y-1.5">
                        <label class="font-label-caps text-[10px] md:text-xs text-on-surface-variant uppercase tracking-widest ml-1">Password</label>
                        <input name="password" required class="auth-input w-full bg-surface-container-low border border-outline-variant/50 text-white px-4 py-3 md:px-5 md:py-3.5 rounded-xl outline-none transition-all text-sm md:text-base" placeholder="Minimal 8 karakter" type="password"/>
                    </div>
                    
                    <div class="bg-surface-container/50 border border-outline-variant/50 rounded-xl p-3.5 md:p-4 flex items-center justify-between mt-2">
                        <div class="flex items-center gap-3">
                            <input name="human_verify" id="captcha" type="checkbox" class="w-4 h-4 md:w-5 md:h-5 rounded border-outline-variant bg-surface-container-low text-primary focus:ring-primary focus:ring-offset-background cursor-pointer"/>
                            <label class="text-[13px] md:text-sm font-medium text-on-surface cursor-pointer select-none" for="captcha">Saya bukan robot</label>
                        </div>
                        <div class="flex flex-col items-center">
                            <span class="material-symbols-outlined text-secondary text-[20px] md:text-[24px]">verified_user</span>
                        </div>
                    </div>
                    
                    <button type="submit" name="register" class="w-full py-3.5 md:py-4 bg-secondary text-on-secondary-container rounded-xl font-bold hover:brightness-110 active:scale-[0.98] transition-all mt-4 flex items-center justify-center gap-2 uppercase tracking-wide text-[13px] md:text-sm shadow-lg shadow-secondary/20">
                        Daftar Sekarang
                        <span class="material-symbols-outlined text-[18px]">person_add</span>
                    </button>
                </form>
                
                <div class="mt-6 md:mt-8 pt-5 md:pt-6 border-t border-outline-variant/20">
                    <button class="w-full py-3 md:py-3.5 border border-outline-variant/50 bg-surface-container-low rounded-xl flex items-center justify-center gap-3 hover:bg-surface-container-high transition-all text-[13px] md:text-sm font-medium">
                        <svg class="w-4 h-4 md:w-5 md:h-5" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="currentColor"></path><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="currentColor"></path><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="currentColor"></path><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="currentColor"></path></svg>
                        Lanjutkan dengan Google
                    </button>
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
    // Logic for Nav Mobile Menu
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    
    function toggleMobileMenu() {
        if(mobileMenu.classList.contains('hidden')) {
            mobileMenu.classList.remove('hidden');
            mobileMenu.classList.add('flex');
        } else {
            mobileMenu.classList.add('hidden');
            mobileMenu.classList.remove('flex');
        }
    }
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', toggleMobileMenu);
    }

    // Logic for Auth Modal
    function toggleAuth(type = 'login') {
        const authSection = document.getElementById('auth-section');
        authSection.classList.remove('hidden');
        authSection.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        
        // Hide mobile menu if open
        if(mobileMenu && !mobileMenu.classList.contains('hidden')) {
            toggleMobileMenu();
        }
        
        switchTab(type);
    }

    function hideAuth() {
        const authSection = document.getElementById('auth-section');
        authSection.classList.remove('flex');
        authSection.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function switchTab(type) {
        const loginForm = document.getElementById('form-login');
        const registerForm = document.getElementById('form-register');
        const loginBtn = document.getElementById('tab-login-btn');
        const registerBtn = document.getElementById('tab-register-btn');
        const indicator = document.getElementById('tab-indicator');

        if (type === 'login') {
            loginForm.classList.remove('hidden');
            registerForm.classList.add('hidden');
            loginBtn.classList.replace('text-on-surface-variant', 'text-primary');
            registerBtn.classList.replace('text-primary', 'text-on-surface-variant');
            indicator.style.left = '0%';
        } else {
            loginForm.classList.add('hidden');
            registerForm.classList.remove('hidden');
            registerBtn.classList.replace('text-on-surface-variant', 'text-primary');
            loginBtn.classList.replace('text-primary', 'text-on-surface-variant');
            indicator.style.left = '50%';
        }
    }

    // Inisialisasi tab saat halaman load agar pesan error PHP ter-render di tab yang tepat
    window.onload = () => {
        switchTab('<?= $active_tab ?>');
    };

    // Close auth modal on background click (only if clicking the dark overlay)
    document.getElementById('auth-section').addEventListener('click', function(e) {
        if (e.target === this) hideAuth();
    });
</script>
</body>
</html>