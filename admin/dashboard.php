<?php
session_start();
include 'config.php';

/*
|--------------------------------------------------------------------------
| CEK LOGIN ADMIN
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['admin'])) {
    header("Location: auth.php");
    exit;
}

date_default_timezone_set('Asia/Jakarta');

/*
|--------------------------------------------------------------------------
| MENGAMBIL DATA STATISTIK UNTUK DASHBOARD
|--------------------------------------------------------------------------
*/
// 1. Total Pendapatan (Pesanan berstatus Lunas)
$sql_revenue = "SELECT SUM(total) as grand_pemasukan FROM orders WHERE status = 'lunas'";
$res_revenue = $conn->query($sql_revenue)->fetch_assoc();
$totalRevenue = $res_revenue['grand_pemasukan'] ?? 0;

// 2. Total Pesanan (Semua status)
$sql_orders = "SELECT COUNT(id) as total_order FROM orders";
$res_orders = $conn->query($sql_orders)->fetch_assoc();
$totalOrders = $res_orders['total_order'] ?? 0;

// 3. Total Pelanggan Aktif
$sql_customers = "SELECT COUNT(id) as total_customer FROM customers";
$res_customers = $conn->query($sql_customers)->fetch_assoc();
$totalCustomers = $res_customers['total_customer'] ?? 0;

// 4. Stok Kritis / Habis (Dari tabel phones)
$sql_stock = "SELECT COUNT(id) as out_of_stock FROM phones WHERE stok <= 0";
$res_stock = $conn->query($sql_stock)->fetch_assoc();
$outOfStock = $res_stock['out_of_stock'] ?? 0;

// 5. 4 Pesanan Terbaru
$recent_orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 4");
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>SmartShop Admin - Dashboard</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                    "on-tertiary": "#ffffff",
                    "inverse-on-surface": "#f3f0f2",
                    "outline": "#76777d",
                    "on-error": "#ffffff",
                    "on-primary-fixed-variant": "#3f465c",
                    "surface-dim": "#dcd9db",
                    "tertiary": "#000000",
                    "on-secondary-container": "#fefcff",
                    "on-secondary-fixed": "#00174b",
                    "on-primary": "#ffffff",
                    "on-surface": "#1b1b1d",
                    "secondary-fixed-dim": "#b4c5ff",
                    "on-tertiary-container": "#98805d",
                    "inverse-surface": "#303032",
                    "on-tertiary-fixed-variant": "#574425",
                    "on-secondary": "#ffffff",
                    "tertiary-fixed": "#fcdeb5",
                    "on-primary-fixed": "#131b2e",
                    "surface-bright": "#fcf8fa",
                    "surface-tint": "#565e74",
                    "surface": "#fcf8fa",
                    "primary-fixed": "#dae2fd",
                    "surface-container-highest": "#e4e2e4",
                    "primary-fixed-dim": "#bec6e0",
                    "on-tertiary-fixed": "#271901",
                    "surface-container": "#f0edef",
                    "surface-container-low": "#f6f3f5",
                    "error": "#ba1a1a",
                    "secondary": "#0051d5",
                    "error-container": "#ffdad6",
                    "on-surface-variant": "#45464d",
                    "secondary-container": "#316bf3",
                    "primary-container": "#131b2e",
                    "on-error-container": "#93000a",
                    "background": "#fcf8fa",
                    "on-primary-container": "#7c839b",
                    "on-secondary-fixed-variant": "#003ea8",
                    "tertiary-container": "#271901",
                    "inverse-primary": "#bec6e0",
                    "surface-container-lowest": "#ffffff",
                    "secondary-fixed": "#dbe1ff",
                    "primary": "#000000",
                    "outline-variant": "#c6c6cd",
                    "tertiary-fixed-dim": "#dec29a",
                    "surface-container-high": "#eae7e9",
                    "surface-variant": "#e4e2e4",
                    "on-background": "#1b1b1d"
            },
            "borderRadius": {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "full": "9999px"
            },
            "spacing": {
                    "margin": "32px",
                    "base": "4px",
                    "md": "16px",
                    "xl": "32px",
                    "xs": "4px",
                    "gutter": "24px",
                    "lg": "24px",
                    "2xl": "48px",
                    "sm": "8px"
            },
            "fontFamily": {
                    "body-md": ["Plus Jakarta Sans"],
                    "headline-lg": ["Plus Jakarta Sans"],
                    "label-lg": ["Plus Jakarta Sans"],
                    "label-md": ["Plus Jakarta Sans"],
                    "headline-md": ["Plus Jakarta Sans"],
                    "body-lg": ["Plus Jakarta Sans"],
                    "display-lg": ["Plus Jakarta Sans"]
            },
            "fontSize": {
                    "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                    "headline-lg": ["24px", {"lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "600"}],
                    "label-lg": ["14px", {"lineHeight": "20px", "fontWeight": "600"}],
                    "label-md": ["12px", {"lineHeight": "16px", "fontWeight": "500"}],
                    "headline-md": ["20px", {"lineHeight": "28px", "fontWeight": "600"}],
                    "body-lg": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                    "display-lg": ["36px", {"lineHeight": "44px", "letterSpacing": "-0.02em", "fontWeight": "700"}]
            }
          },
        },
      }
    </script>
<style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8FAFC; color: #1b1b1d; }
    </style>
</head>
<body class="flex bg-[#F8FAFC]">
<aside class="fixed left-0 top-0 h-screen w-[280px] bg-surface-container-low dark:bg-surface-container-low border-r border-outline-variant dark:border-outline-variant flex flex-col p-md gap-sm z-40">
<div class="px-md py-lg border-b border-outline-variant mb-4 flex items-center gap-4">
<img class="w-10 h-10 object-contain" alt="SmartShop Logo" src="https://lh3.googleusercontent.com/aida-public/AB6AXuC1WUCvSlpetn4azuvElW5M7sGGCMsQxDj960XnzP8M8-lw_FD1EMEzJerMA0yfIhHxKIhfkrF22vibtpl3_6GEF7faic7wenOrdCDzzJcNDIftN6R8M8gyPpRTL5MEZBQNvXFXY2t2Y9FUhjwEKywq1aqM814c4dnh6q-17VLvJ4GvLcTe7M6IoktC4G9I5R7arl_p9rnDuBDbx6RbeQSsxYumffVOMs0rI4fumgWryCLC-Kq4XEcEwY0P1X65mJ-4Cs4T_n0qtUY"/>
<div>
<h1 class="font-headline-sm text-headline-sm font-bold text-primary">SmartShop</h1>
<p class="font-label-md text-label-md text-on-surface-variant">Admin Terminal</p>
</div>
</div>
<nav class="flex-1 flex flex-col gap-1">
<a class="flex items-center gap-3 px-md py-sm bg-secondary-container dark:bg-secondary-container text-on-secondary-container dark:text-on-secondary-container rounded-lg font-bold font-label-lg text-label-lg transition-all active:opacity-80" href="dashboard.php">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">dashboard</span>
                Dashboard
            </a>
<a class="flex items-center gap-3 px-md py-sm text-on-surface-variant dark:text-on-surface-variant hover:bg-surface-container-highest dark:hover:bg-surface-container-highest rounded-lg font-label-lg text-label-lg transition-all active:opacity-80" href="index.php">
<span class="material-symbols-outlined">inventory_2</span>
                Inventory
            </a>
<a class="flex items-center gap-3 px-md py-sm text-on-surface-variant dark:text-on-surface-variant hover:bg-surface-container-highest dark:hover:bg-surface-container-highest rounded-lg font-label-lg text-label-lg transition-all active:opacity-80" href="orders.php">
<span class="material-symbols-outlined">shopping_cart</span>
                Orders
            </a>
<a class="flex items-center gap-3 px-md py-sm text-on-surface-variant dark:text-on-surface-variant hover:bg-surface-container-highest dark:hover:bg-surface-container-highest rounded-lg font-label-lg text-label-lg transition-all active:opacity-80" href="customers.php">
<span class="material-symbols-outlined">group</span>
                Customers
            </a>
<a class="flex items-center gap-3 px-md py-sm text-on-surface-variant dark:text-on-surface-variant hover:bg-surface-container-highest dark:hover:bg-surface-container-highest rounded-lg font-label-lg text-label-lg transition-all active:opacity-80" href="pemasukan.php">
<span class="material-symbols-outlined">analytics</span>
                Analytics
            </a>
</nav>
<div class="mt-auto border-t border-outline-variant pt-4 flex flex-col gap-1">
<a class="flex items-center gap-3 px-md py-sm text-on-surface-variant dark:text-on-surface-variant hover:bg-surface-container-highest dark:hover:bg-surface-container-highest rounded-lg font-label-lg text-label-lg transition-all active:opacity-80" href="#">
<span class="material-symbols-outlined">settings</span>
                Settings
            </a>
<a class="flex items-center gap-3 px-md py-sm text-error hover:bg-error-container hover:text-on-error-container rounded-lg font-label-lg text-label-lg transition-all active:opacity-80" href="logout.php" onclick="return confirm('Yakin ingin logout?')">
<span class="material-symbols-outlined">logout</span>
                Logout
            </a>
</div>
</aside>
<main class="ml-[280px] flex-1 flex flex-col min-h-screen">
<header class="flex justify-between items-center w-full px-xl h-16 sticky top-0 z-50 bg-surface-container-lowest dark:bg-surface-container-lowest border-b border-outline-variant dark:border-outline-variant shadow-sm text-primary dark:text-on-primary-fixed font-body-md text-body-md">
<div class="flex items-center gap-4 flex-1">
<div class="relative w-96">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
<input class="w-full pl-10 pr-4 py-2 bg-surface-container-low border border-outline-variant rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary-container focus:border-secondary-container transition-shadow" placeholder="Search..." type="text"/>
</div>
</div>
<nav class="hidden md:flex gap-8 mx-8">
<a class="text-on-surface-variant dark:text-on-surface-variant font-label-lg text-label-lg hover:text-secondary dark:hover:text-secondary-fixed transition-colors active:scale-95" href="#">Customers</a>
<a class="text-on-surface-variant dark:text-on-surface-variant font-label-lg text-label-lg hover:text-secondary dark:hover:text-secondary-fixed transition-colors active:scale-95" href="#">Orders</a>
<a class="text-on-surface-variant dark:text-on-surface-variant font-label-lg text-label-lg hover:text-secondary dark:hover:text-secondary-fixed transition-colors active:scale-95" href="#">Reports</a>
</nav>
<div class="flex items-center gap-4">
<span class="font-label-md text-label-md text-on-surface-variant mr-2 hidden md:block">Halo, <?= htmlspecialchars($_SESSION['admin']['username'] ?? 'Admin') ?></span>
<div class="flex items-center gap-2 border-l border-outline-variant pl-4">
<a href="orders.php" class="p-2 text-on-surface-variant hover:bg-surface-container-highest rounded-full transition-colors relative">
<span class="material-symbols-outlined">notifications</span>
<span id="notifBadge" class="hidden absolute top-1 right-1 w-3 h-3 text-[8px] flex items-center justify-center font-bold text-white bg-error rounded-full"></span>
</a>
<button class="p-2 text-on-surface-variant hover:bg-surface-container-highest rounded-full transition-colors">
<span class="material-symbols-outlined">settings</span>
</button>
<img class="w-8 h-8 rounded-full border border-outline-variant ml-2 object-cover" alt="Admin Profile" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBtMnrbH3hkmiCdcO-Ev1FOrN3Rm4orj06YIIju-wuRYNaGNx3MWE8ysavt7Z3K3xKsA3gL7su4-lAaw2iiGxYQxR4k1A8mFUQtLXX2Vs6RnDWAnUJwal3YkXFccWw5nLRKM8rJczu2iiTcxUpDUO1kcVcHZY7XSJncASL0oxB8fn4_iQimuqcWvuLpAJhDgIHSBS7Wg8cdEN7vhJrN1fXbZF1uAHhkPt-qRsv1PiQLF10VxI053JTJzyCYt47tEPN6fjFt5LPUfeE"/>
</div>
</div>
</header>
<div class="p-margin mx-auto w-full max-w-7xl">
    <div class="mb-8 flex justify-between items-end">
        <div>
            <h2 class="font-headline-lg text-headline-lg text-primary mb-1">Dashboard Overview</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Ringkasan performa dan aktivitas toko Anda.</p>
        </div>
        <div class="flex gap-3">
            <button class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-label-md text-label-md text-on-surface hover:bg-surface-container-highest transition-colors">
                <span class="material-symbols-outlined text-[18px]">download</span> Export Report
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-gutter mb-8">
        <div class="bg-surface-container-lowest rounded-xl p-lg border border-outline-variant shadow-sm flex flex-col justify-between h-full">
            <div class="flex items-start gap-4 mb-4">
                <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center text-emerald-700">
                    <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">payments</span>
                </div>
                <div>
                    <p class="font-label-md text-label-md text-on-surface-variant mb-1 uppercase tracking-wider">Total Revenue</p>
                    <h3 class="font-display-lg text-display-lg text-primary">Rp <?= number_format($totalRevenue, 0, ',', '.') ?></h3>
                </div>
            </div>
            <p class="font-body-md text-body-md text-emerald-600 flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">trending_up</span> Pesanan Lunas
            </p>
        </div>

        <div class="bg-surface-container-lowest rounded-xl p-lg border border-outline-variant shadow-sm flex flex-col justify-between h-full">
            <div class="flex items-start gap-4 mb-4">
                <div class="w-12 h-12 bg-secondary-fixed rounded-lg flex items-center justify-center text-on-secondary-fixed">
                    <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">shopping_bag</span>
                </div>
                <div>
                    <p class="font-label-md text-label-md text-on-surface-variant mb-1 uppercase tracking-wider">Total Orders</p>
                    <h3 class="font-display-lg text-display-lg text-primary"><?= number_format($totalOrders, 0, ',', '.') ?></h3>
                </div>
            </div>
            <p class="font-body-md text-body-md text-on-surface-variant flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">info</span> Semua data pesanan
            </p>
        </div>

        <div class="bg-surface-container-lowest rounded-xl p-lg border border-outline-variant shadow-sm flex flex-col justify-between h-full">
            <div class="flex items-start gap-4 mb-4">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center text-purple-700">
                    <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">person</span>
                </div>
                <div>
                    <p class="font-label-md text-label-md text-on-surface-variant mb-1 uppercase tracking-wider">Customers</p>
                    <h3 class="font-display-lg text-display-lg text-primary"><?= number_format($totalCustomers, 0, ',', '.') ?></h3>
                </div>
            </div>
            <p class="font-body-md text-body-md text-on-surface-variant flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">groups</span> Pelanggan terdaftar
            </p>
        </div>

        <div class="bg-error-container rounded-xl p-lg border border-[#ffb4ab] shadow-sm flex flex-col justify-between h-full relative overflow-hidden">
            <div class="absolute -right-4 -top-4 opacity-10">
                <span class="material-symbols-outlined text-[100px] text-error" style="font-variation-settings: 'FILL' 1;">warning</span>
            </div>
            <div class="flex items-start gap-4 mb-4 relative z-10">
                <div class="w-12 h-12 bg-[#ffb4ab] rounded-lg flex items-center justify-center text-[#93000a]">
                    <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">inventory_2</span>
                </div>
                <div>
                    <p class="font-label-md text-label-md text-on-error-container uppercase tracking-wider">Out of Stock</p>
                    <h3 class="font-display-lg text-display-lg text-on-error-container"><?= number_format($outOfStock, 0, ',', '.') ?></h3>
                </div>
            </div>
            <p class="font-body-md text-body-md text-error flex items-center gap-1 relative z-10">
                <span class="material-symbols-outlined text-[16px]">error</span> Items perlu direstock
            </p>
        </div>
    </div>

    <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden mb-8">
        <div class="px-lg py-4 border-b border-outline-variant flex justify-between items-center bg-[#fdfdfd]">
            <h3 class="font-headline-md text-headline-md text-primary">Revenue Trend (Last 7 Days)</h3>
            <button class="text-on-surface-variant hover:text-secondary transition-colors">
                <span class="material-symbols-outlined">more_vert</span>
            </button>
        </div>
        <div class="p-lg">
            <div class="w-full h-[280px] bg-[#F8FAFC] rounded-lg border border-outline-variant border-dashed flex items-center justify-center relative overflow-hidden">
                <p class="text-on-surface-variant font-label-md">Chart Render Area</p>
                <div class="absolute bottom-0 left-0 w-full h-[100px] bg-gradient-to-t from-secondary-container/20 to-transparent pointer-events-none"></div>
                <svg class="absolute inset-0 w-full h-full pointer-events-none" preserveAspectRatio="none" viewBox="0 0 100 100">
                    <path class="opacity-40" d="M0,80 Q25,20 50,60 T100,30" fill="none" stroke="#316bf3" stroke-width="2"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter mb-8">
        <div class="lg:col-span-2 bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
            <div class="px-lg py-4 border-b border-outline-variant flex justify-between items-center bg-[#fdfdfd]">
                <h3 class="font-headline-md text-headline-md text-primary">Recent Orders</h3>
                <a class="font-label-md text-label-md text-secondary hover:underline" href="orders.php">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#F1F5F9] border-b border-outline-variant">
                            <th class="py-3 px-lg font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Order ID</th>
                            <th class="py-3 px-lg font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Customer</th>
                            <th class="py-3 px-lg font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-right">Amount</th>
                            <th class="py-3 px-lg font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant font-body-md text-body-md">
                        <?php if($recent_orders && $recent_orders->num_rows > 0): ?>
                            <?php while($order = $recent_orders->fetch_assoc()): ?>
                                <tr class="hover:bg-[#F8FAFC] transition-colors group">
                                    <td class="py-4 px-lg font-label-lg text-label-lg text-primary">#ORD-<?= htmlspecialchars($order['id']) ?></td>
                                    <td class="py-4 px-lg text-on-surface"><?= htmlspecialchars($order['nama']) ?></td>
                                    <td class="py-4 px-lg text-on-surface text-right font-medium">Rp <?= number_format($order['total'], 0, ',', '.') ?></td>
                                    <td class="py-4 px-lg text-center">
                                        <?php 
                                        $status = strtolower($order['status']);
                                        if($status == 'lunas') {
                                            echo '<span class="inline-flex px-2 py-1 rounded-full text-[10px] font-bold tracking-wide uppercase bg-emerald-100 text-emerald-800">Lunas</span>';
                                        } else if($status == 'ditolak') {
                                            echo '<span class="inline-flex px-2 py-1 rounded-full text-[10px] font-bold tracking-wide uppercase bg-error-container text-on-error-container">Ditolak</span>';
                                        } else {
                                            echo '<span class="inline-flex px-2 py-1 rounded-full text-[10px] font-bold tracking-wide uppercase bg-amber-100 text-amber-800">Pending</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="py-8 text-center text-on-surface-variant font-body-md">Belum ada pesanan terbaru.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden flex flex-col">
            <div class="px-lg py-4 border-b border-outline-variant bg-[#fdfdfd]">
                <h3 class="font-headline-md text-headline-md text-primary">Top Products</h3>
            </div>
            <div class="flex-1 p-4 flex flex-col gap-2">
                <div class="flex items-center gap-3 p-3 rounded-lg hover:bg-surface-container-low transition-colors border border-transparent hover:border-outline-variant">
                    <div class="w-12 h-12 rounded bg-surface-container flex items-center justify-center shrink-0 border border-outline-variant">
                        <span class="material-symbols-outlined text-on-surface-variant">smartphone</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-label-lg text-label-lg text-primary truncate">IPhone 15 Pro</h4>
                        <p class="font-label-md text-label-md text-on-surface-variant truncate">Electronics</p>
                    </div>
                    <div class="text-right">
                        <span class="font-headline-md text-headline-md text-primary">342</span>
                        <p class="font-label-md text-label-md text-on-surface-variant">Units</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 rounded-lg hover:bg-surface-container-low transition-colors border border-transparent hover:border-outline-variant">
                    <div class="w-12 h-12 rounded bg-surface-container flex items-center justify-center shrink-0 border border-outline-variant">
                        <span class="material-symbols-outlined text-on-surface-variant">laptop_mac</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-label-lg text-label-lg text-primary truncate">MacBook Air M2</h4>
                        <p class="font-label-md text-label-md text-on-surface-variant truncate">Computers</p>
                    </div>
                    <div class="text-right">
                        <span class="font-headline-md text-headline-md text-primary">128</span>
                        <p class="font-label-md text-label-md text-on-surface-variant">Units</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 rounded-lg hover:bg-surface-container-low transition-colors border border-transparent hover:border-outline-variant">
                    <div class="w-12 h-12 rounded bg-surface-container flex items-center justify-center shrink-0 border border-outline-variant">
                        <span class="material-symbols-outlined text-on-surface-variant">headphones</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-label-lg text-label-lg text-primary truncate">Sony WH-1000XM5</h4>
                        <p class="font-label-md text-label-md text-on-surface-variant truncate">Audio</p>
                    </div>
                    <div class="text-right">
                        <span class="font-headline-md text-headline-md text-primary">89</span>
                        <p class="font-label-md text-label-md text-on-surface-variant">Units</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</main>

<audio id="notifSound" preload="auto">
    <source src="https://www.soundjay.com/buttons/sounds/button-3.mp3" type="audio/mpeg">
</audio>

<script>
/* NOTIF ORDER */
let lastTotal = 0;
let firstLoad = true;

function loadNotif() {
    fetch('get_orders.php')
        .then(res => res.json())
        .then(data => {
            let badge = document.getElementById('notifBadge');
            
            if (data.total > 0) {
                badge.innerText = data.total > 99 ? '99+' : data.total;
                badge.classList.remove('hidden');

                if (!firstLoad && data.total > lastTotal) {
                    document.getElementById('notifSound').play();

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Ada Pesanan Baru!',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            } else {
                badge.classList.add('hidden');
            }
            lastTotal = data.total;
            firstLoad = false;
        })
        .catch(err => console.error("Error fetching orders:", err));
}

loadNotif();
setInterval(loadNotif, 5000);
</script>
</body>
</html>