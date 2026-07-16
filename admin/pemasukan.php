<?php
session_start();
include __DIR__ . '/config.php';

// PROTEKSI: Pastikan hanya admin yang bisa mengakses halaman ini
// if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit; }

/* ===========================================================
   1. AMBIL DATA RINGKASAN KARTU (TOTAL & BULAN INI)
   =========================================================== */
$sql_total = "SELECT SUM(total) as grand_pemasukan FROM orders WHERE status = 'lunas'";
$res_total = $conn->query($sql_total)->fetch_assoc();
$grandPemasukan = $res_total['grand_pemasukan'] ?? 0;

$bln_ini = date('m');
$thn_ini = date('Y');
$sql_bulan = "SELECT SUM(total) as bulan_pemasukan FROM orders WHERE status = 'lunas' AND MONTH(created_at) = '$bln_ini' AND YEAR(created_at) = '$thn_ini'";
$res_bulan = $conn->query($sql_bulan)->fetch_assoc();
$bulanPemasukan = $res_bulan['bulan_pemasukan'] ?? 0;

/* ===========================================================
   2. LOGIKA FILTER GRAFIK (HARIAN / MINGGUAN / BULANAN / KUSTOM)
   =========================================================== */
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'monthly';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$chartLabels = [];
$chartData = [];
$chartTitle = "";
$kondisi_tabel = "status = 'lunas'"; // Default kondisi untuk tabel histori

if ($filter == 'custom' && !empty($start_date) && !empty($end_date)) {
    // FILTER RENTANG TANGGAL KHUSUS
    $sql_chart = "SELECT DATE(created_at) as tgl, SUM(total) as val 
                  FROM orders 
                  WHERE status = 'lunas' AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'
                  GROUP BY DATE(created_at) ORDER BY DATE(created_at) ASC";
    $res = $conn->query($sql_chart);
    while($row = $res->fetch_assoc()) {
        $chartLabels[] = date('d M Y', strtotime($row['tgl']));
        $chartData[] = (int)$row['val'];
    }
    $chartTitle = "Periode: " . date('d M Y', strtotime($start_date)) . " - " . date('d M Y', strtotime($end_date));
    $kondisi_tabel = "status = 'lunas' AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'";

} elseif ($filter == 'daily') {
    // Laporan Harian (14 Hari Terakhir)
    $sql_chart = "SELECT DATE(created_at) as tgl, SUM(total) as val 
                  FROM orders WHERE status = 'lunas' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
                  GROUP BY DATE(created_at) ORDER BY DATE(created_at) ASC";
    $res = $conn->query($sql_chart);
    while($row = $res->fetch_assoc()) {
        $chartLabels[] = date('d M', strtotime($row['tgl']));
        $chartData[] = (int)$row['val'];
    }
    $chartTitle = "Laporan Harian (14 Hari Terakhir)";
    $kondisi_tabel = "status = 'lunas' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)";

} elseif ($filter == 'weekly') {
    // Laporan Mingguan (8 Minggu Terakhir)
    $sql_chart = "SELECT YEARWEEK(created_at, 1) as pekan, SUM(total) as val 
                  FROM orders WHERE status = 'lunas' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 8 WEEK)
                  GROUP BY YEARWEEK(created_at, 1) ORDER BY YEARWEEK(created_at, 1) ASC";
    $res = $conn->query($sql_chart);
    while($row = $res->fetch_assoc()) {
        $chartLabels[] = 'Pekan ' . substr($row['pekan'], 4);
        $chartData[] = (int)$row['val'];
    }
    $chartTitle = "Laporan Mingguan (8 Minggu Terakhir)";
    $kondisi_tabel = "status = 'lunas' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 8 WEEK)";

} else {
    // Laporan Bulanan (Tahun Berjalan)
    $sql_chart = "SELECT MONTH(created_at) as bln, SUM(total) as val 
                  FROM orders WHERE status = 'lunas' AND YEAR(created_at) = YEAR(CURDATE())
                  GROUP BY MONTH(created_at) ORDER BY MONTH(created_at) ASC";
    $res = $conn->query($sql_chart);
    $namaBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
    while($row = $res->fetch_assoc()) {
        $chartLabels[] = $namaBulan[$row['bln'] - 1];
        $chartData[] = (int)$row['val'];
    }
    $chartTitle = "Laporan Bulanan Berjalan Tahun " . date('Y');
    $kondisi_tabel = "status = 'lunas' AND YEAR(created_at) = YEAR(CURDATE())";
}

/* ===========================================================
   3. TABEL HISTORI
   =========================================================== */
$sql_history = "SELECT id, nama, metode, total, created_at, no_resi FROM orders WHERE $kondisi_tabel ORDER BY created_at DESC";
$res_history = $conn->query($sql_history);

// Generate param string for Export buttons
$params = "filter=$filter";
if($filter == 'custom') {
    $params .= "&start_date=$start_date&end_date=$end_date";
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>SmartShop Admin - Laporan Pemasukan</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    input[type="date"]::-webkit-calendar-picker-indicator { cursor: pointer; opacity: 0.6; transition: 0.2s; }
    input[type="date"]::-webkit-calendar-picker-indicator:hover { opacity: 1; }
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
<a class="flex items-center gap-3 px-md py-sm text-on-surface-variant dark:text-on-surface-variant hover:bg-surface-container-highest dark:hover:bg-surface-container-highest rounded-lg font-label-lg text-label-lg transition-all active:opacity-80" href="dashboard.php">
<span class="material-symbols-outlined">dashboard</span>
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
<a class="flex items-center gap-3 px-md py-sm bg-secondary-container dark:bg-secondary-container text-on-secondary-container dark:text-on-secondary-container rounded-lg font-bold font-label-lg text-label-lg transition-all active:opacity-80" href="pemasukan.php">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">analytics</span>
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
<input class="w-full pl-10 pr-4 py-2 bg-surface-container-low border border-outline-variant rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary-container focus:border-secondary-container transition-shadow" placeholder="Search analytics..." type="text"/>
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
            <h2 class="font-headline-lg text-headline-lg text-primary mb-1">Laporan Pemasukan</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Analisis dan rekapitulasi pendapatan toko</p>
        </div>
        <div class="flex gap-3">
            <a href="cetak_laporan.php?<?= $params ?>" target="_blank" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-label-md text-label-md text-emerald-700 hover:bg-surface-container-highest transition-colors">
                <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span> Unduh PDF
            </a>
            <a href="cetak_excel.php?<?= $params ?>" class="flex items-center gap-2 px-4 py-2 bg-secondary-container text-on-secondary-container rounded-lg font-label-md text-label-md hover:bg-secondary transition-colors">
                <span class="material-symbols-outlined text-[18px]">table_chart</span> Excel
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-gutter mb-8">
        <div class="bg-surface-container-lowest rounded-xl p-lg border border-outline-variant shadow-sm flex items-start gap-4">
            <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center text-emerald-700">
                <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">account_balance_wallet</span>
            </div>
            <div>
                <p class="font-label-md text-label-md text-on-surface-variant mb-1 uppercase tracking-wider">Total Keseluruhan</p>
                <h3 class="font-display-lg text-display-lg text-primary">Rp <?= number_format($grandPemasukan, 0, ',', '.') ?></h3>
            </div>
        </div>
        <div class="bg-surface-container-lowest rounded-xl p-lg border border-outline-variant shadow-sm flex items-start gap-4">
            <div class="w-12 h-12 bg-secondary-fixed rounded-lg flex items-center justify-center text-on-secondary-fixed">
                <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">calendar_month</span>
            </div>
            <div>
                <p class="font-label-md text-label-md text-on-surface-variant mb-1 uppercase tracking-wider">Bulan Ini (<?= date('M Y') ?>)</p>
                <h3 class="font-display-lg text-display-lg text-primary">Rp <?= number_format($bulanPemasukan, 0, ',', '.') ?></h3>
            </div>
        </div>
    </div>

    <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden mb-8">
        <div class="px-lg py-4 border-b border-outline-variant flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-[#fdfdfd]">
            <div>
                <h3 class="font-headline-md text-headline-md text-primary">Tren Pemasukan</h3>
                <p class="font-body-md text-body-md text-on-surface-variant"><?= $chartTitle ?></p>
            </div>
            
            <div class="flex flex-col xl:flex-row items-center gap-4">
                <div class="flex bg-surface-container-low rounded-lg p-1 border border-outline-variant">
                    <a href="?filter=daily" class="px-4 py-1.5 rounded-md <?= $filter == 'daily' ? 'bg-surface-container-lowest text-primary shadow-sm font-bold' : 'text-on-surface-variant hover:bg-surface-container transition-colors' ?> font-label-md text-label-md">Harian</a>
                    <a href="?filter=weekly" class="px-4 py-1.5 rounded-md <?= $filter == 'weekly' ? 'bg-surface-container-lowest text-primary shadow-sm font-bold' : 'text-on-surface-variant hover:bg-surface-container transition-colors' ?> font-label-md text-label-md">Mingguan</a>
                    <a href="?filter=monthly" class="px-4 py-1.5 rounded-md <?= $filter == 'monthly' ? 'bg-surface-container-lowest text-primary shadow-sm font-bold' : 'text-on-surface-variant hover:bg-surface-container transition-colors' ?> font-label-md text-label-md">Bulanan</a>
                </div>
                
                <div class="h-8 w-px bg-outline-variant hidden xl:block"></div>
                
                <form method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="filter" value="custom">
                    <div class="flex items-center bg-surface-container-lowest border border-outline-variant rounded-lg px-3 py-1.5">
                        <span class="material-symbols-outlined text-on-surface-variant text-[18px] mr-2">date_range</span>
                        <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" required class="bg-transparent border-none text-sm outline-none text-primary font-body-md w-[110px] focus:ring-0 p-0">
                        <span class="text-on-surface-variant font-bold mx-2">-</span>
                        <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" required class="bg-transparent border-none text-sm outline-none text-primary font-body-md w-[110px] focus:ring-0 p-0">
                    </div>
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-secondary text-on-secondary hover:bg-secondary-container transition-colors font-label-md text-label-md">
                        Filter
                    </button>
                </form>
            </div>
        </div>
        
        <div class="p-lg h-[400px] w-full relative bg-surface-container-lowest">
            <?php if(empty($chartData)): ?>
                <div class="absolute inset-0 flex items-center justify-center">
                    <p class="text-on-surface-variant font-body-md">Tidak ada data pemasukan pada periode ini.</p>
                </div>
            <?php else: ?>
                <canvas id="revenueChart"></canvas>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden mb-12">
        <div class="px-lg py-4 border-b border-outline-variant bg-[#fdfdfd]">
            <h3 class="font-headline-md text-headline-md text-primary">Riwayat Transaksi (<?= $res_history->num_rows ?>)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F1F5F9] border-b border-outline-variant">
                        <th class="py-3 px-lg font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">ID Inv</th>
                        <th class="py-3 px-lg font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Pelanggan</th>
                        <th class="py-3 px-lg font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Waktu Transaksi</th>
                        <th class="py-3 px-lg font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Metode</th>
                        <th class="py-3 px-lg font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant font-body-md text-body-md">
                    <?php if ($res_history->num_rows > 0): ?>
                        <?php 
                            $bg_colors = [
                                'bg-primary-container text-on-primary-container', 
                                'bg-emerald-100 text-emerald-700', 
                                'bg-purple-100 text-purple-700', 
                                'bg-orange-100 text-orange-700',
                                'bg-blue-100 text-blue-700'
                            ];
                        ?>
                        <?php while ($row = $res_history->fetch_assoc()): ?>
                            <?php 
                                $initials = strtoupper(substr($row['nama'], 0, 2));
                                $colorIndex = crc32($row['nama']) % count($bg_colors);
                                $color_class = $bg_colors[$colorIndex];
                                
                                $metode = strtolower($row['metode']);
                                $badgeClass = "bg-surface-container text-on-surface-variant";
                                if($metode == 'transfer' || $metode == 'bank') $badgeClass = "bg-blue-100 text-blue-800";
                                elseif($metode == 'cash' || $metode == 'tunai') $badgeClass = "bg-emerald-100 text-emerald-800";
                                elseif($metode == 'qris') $badgeClass = "bg-orange-100 text-orange-800";
                            ?>
                            <tr class="hover:bg-[#F8FAFC] transition-colors group">
                                <td class="py-4 px-lg font-label-lg text-label-lg text-secondary group-hover:underline cursor-pointer">#INV<?= $row['id'] ?></td>
                                <td class="py-4 px-lg">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded border border-outline-variant <?= $color_class ?> flex items-center justify-center font-bold text-xs"><?= $initials ?></div>
                                        <span class="font-label-lg text-label-lg text-primary"><?= htmlspecialchars($row['nama']) ?></span>
                                    </div>
                                </td>
                                <td class="py-4 px-lg text-on-surface-variant"><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></td>
                                <td class="py-4 px-lg">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide <?= $badgeClass ?>">
                                        <?= strtoupper($row['metode']) ?>
                                    </span>
                                </td>
                                <td class="py-4 px-lg font-label-lg text-label-lg text-primary text-right">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="py-8 text-center text-on-surface-variant font-body-md">Belum ada transaksi pada periode ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</main>

<audio id="notifSound" preload="auto">
    <source src="https://www.soundjay.com/buttons/sounds/button-3.mp3" type="audio/mpeg">
</audio>

<script>
/* NOTIF ORDER SCRIPT SEPERTI DI INDEX.PHP */
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

<?php if(!empty($chartData)): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        
        const labels = <?= json_encode($chartLabels) ?>;
        const dataValues = <?= json_encode($chartData) ?>;

        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(49, 107, 243, 0.4)'); // secondary-container
        gradient.addColorStop(1, 'rgba(49, 107, 243, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: dataValues,
                    borderColor: '#316bf3',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#316bf3',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#131b2e',
                        titleFont: { family: 'Plus Jakarta Sans', size: 13 },
                        bodyFont: { family: 'Plus Jakarta Sans', size: 14, weight: 'bold' },
                        padding: 12,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                let value = context.raw || 0;
                                return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(198, 198, 205, 0.4)', // outline-variant with opacity
                            drawBorder: false
                        },
                        ticks: {
                            font: { family: 'Plus Jakarta Sans', size: 12 },
                            color: '#76777d',
                            callback: function(value) {
                                if (value >= 1000000) return (value / 1000000) + ' Jt';
                                if (value >= 1000) return (value / 1000) + ' Rb';
                                return value;
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: { family: 'Plus Jakarta Sans', size: 12 },
                            color: '#76777d'
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
            }
        });
    });
</script>
<?php endif; ?>
</body>
</html>