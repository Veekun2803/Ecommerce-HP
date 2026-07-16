<?php
session_start();
include 'config.php';

/* |--------------------------------------------------------------------------
| CEK LOGIN ADMIN
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['admin'])) {
    header("Location: auth.php");
    exit;
}

date_default_timezone_set('Asia/Jakarta');

/* |--------------------------------------------------------------------------
| HANDLE AKSI (POST)
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi'], $_POST['id'])) {
    $id   = intval($_POST['id']);
    $aksi = $_POST['aksi'];
    
    // Tambahkan 'input_resi' ke daftar aksi yang diizinkan
    $allowed = ['verifikasi','tolak','hapus', 'input_resi'];
    if (!in_array($aksi, $allowed)) die("Aksi tidak valid");

    $stmt = $conn->prepare("SELECT * FROM orders WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if ($order) {
        $msg = "";
        $type = "success";

        if ($aksi == 'verifikasi') {
            $conn->query("UPDATE orders SET status='lunas' WHERE id=$id");
            $msg = "Pesanan #$id berhasil diverifikasi.";
        }
        if ($aksi == 'tolak') {
            $conn->query("UPDATE orders SET status='ditolak' WHERE id=$id");
            $msg = "Pesanan #$id telah ditolak.";
            $type = "info";
        }
        if ($aksi == 'hapus') {
            $conn->begin_transaction();
            try {
                if (!empty($order['bukti'])) {
                    $file = "uploads/bukti_bayar/" . $order['bukti'];
                    if (file_exists($file)) unlink($file);
                }
                $conn->query("DELETE FROM order_items WHERE order_id=$id");
                $conn->query("DELETE FROM orders WHERE id=$id");
                $conn->commit();
                $msg = "Data pesanan berhasil dihapus secara permanen.";
            } catch (Exception $e) { 
                $conn->rollback(); 
                $msg = "Gagal menghapus data.";
                $type = "error";
            }
        }
        // AKSI BARU: Simpan Nomor Resi
        if ($aksi == 'input_resi' && isset($_POST['no_resi'])) {
            $no_resi = htmlspecialchars($_POST['no_resi']);
            $stmtResi = $conn->prepare("UPDATE orders SET no_resi=? WHERE id=?");
            $stmtResi->bind_param("si", $no_resi, $id);
            if($stmtResi->execute()){
                $msg = "Nomor resi untuk Pesanan #$id berhasil disimpan.";
            } else {
                $msg = "Gagal menyimpan nomor resi.";
                $type = "error";
            }
        }
        
        $_SESSION['flash_msg'] = $msg;
        $_SESSION['flash_type'] = $type;
    }
    
    header("Location: orders.php");
    exit;
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SmartShop Admin - Orders Management</title>
    
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
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
          }
        }
      }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8FAFC; color: #1b1b1d; }
        .dataTables_wrapper .dataTables_filter { display: none; }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #0051d5 !important;
            color: white !important;
            border-radius: 8px;
            border: none;
        }
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
        <a class="flex items-center gap-3 px-md py-sm bg-secondary-container dark:bg-secondary-container text-on-secondary-container dark:text-on-secondary-container rounded-lg font-bold font-label-lg text-label-lg transition-all active:opacity-80" href="orders.php">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">shopping_cart</span>
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
                </div>
        </div>
        <nav class="hidden md:flex gap-8 mx-8">
            <a class="text-on-surface-variant dark:text-on-surface-variant font-label-lg text-label-lg hover:text-secondary dark:hover:text-secondary-fixed transition-colors active:scale-95" href="#">Customers</a>
            <a class="text-on-surface-variant dark:text-on-surface-variant font-label-lg text-label-lg hover:text-secondary dark:hover:text-secondary-fixed transition-colors active:scale-95" href="#">Orders</a>
            <a class="text-on-surface-variant dark:text-on-surface-variant font-label-lg text-label-lg hover:text-secondary dark:hover:text-secondary-fixed transition-colors active:scale-95" href="#">Reports</a>
        </nav>
        <div class="flex items-center gap-4">
            <span class="font-label-md text-label-md text-on-surface-variant mr-2 hidden md:block">Halo, <?= htmlspecialchars($_SESSION['admin']['username'] ?? 'Admin') ?></span>
            <a href="create.php" class="bg-primary text-on-primary font-label-lg text-label-lg px-6 py-2 rounded-lg hover:bg-secondary transition-colors shadow-sm active:scale-95 flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Add Phone
            </a>
            <div class="flex items-center gap-2 border-l border-outline-variant pl-4">
                <button class="p-2 text-on-surface-variant hover:bg-surface-container-highest rounded-full transition-colors relative">
                    <span class="material-symbols-outlined">notifications</span>
                    <span id="notifBadge" class="hidden absolute top-1 right-1 w-2 h-2 bg-error rounded-full"></span>
                </button>
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
                <h2 class="font-headline-lg text-headline-lg text-primary mb-1">Orders Management</h2>
                <p class="font-body-md text-body-md text-on-surface-variant">Review, verify, and process customer transactions.</p>
            </div>
            <div class="flex gap-3">
                <button class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-label-md text-label-md text-on-surface hover:bg-surface-container-highest transition-colors">
                    <span class="material-symbols-outlined text-[18px]">filter_list</span> Filter
                </button>
                <button class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-label-md text-label-md text-on-surface hover:bg-surface-container-highest transition-colors">
                    <span class="material-symbols-outlined text-[18px]">download</span> Export
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter mb-8">
            <div class="bg-surface-container-lowest rounded-xl p-lg border border-outline-variant shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 bg-primary-fixed rounded-lg flex items-center justify-center text-on-primary-fixed">
                    <span class="material-symbols-outlined text-2xl">shopping_bag</span>
                </div>
                <div>
                    <p class="font-label-md text-label-md text-on-surface-variant mb-1 uppercase tracking-wider">Total Order</p>
                    <h3 class="font-display-lg text-display-lg text-primary"><?= $conn->query("SELECT id FROM orders")->num_rows ?></h3>
                </div>
            </div>
            
            <div class="bg-surface-container-lowest rounded-xl p-lg border border-outline-variant shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center text-amber-800">
                    <span class="material-symbols-outlined text-2xl">pending_actions</span>
                </div>
                <div>
                    <p class="font-label-md text-label-md text-on-surface-variant mb-1 uppercase tracking-wider">Perlu Verifikasi</p>
                    <h3 class="font-display-lg text-display-lg text-primary"><?= $conn->query("SELECT id FROM orders WHERE status='pending'")->num_rows ?></h3>
                </div>
            </div>

            <div class="bg-surface-container-lowest rounded-xl p-lg border border-outline-variant shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center text-emerald-800">
                    <span class="material-symbols-outlined text-2xl">check_circle</span>
                </div>
                <div>
                    <p class="font-label-md text-label-md text-on-surface-variant mb-1 uppercase tracking-wider">Lunas</p>
                    <h3 class="font-display-lg text-display-lg text-primary"><?= $conn->query("SELECT id FROM orders WHERE status='lunas'")->num_rows ?></h3>
                </div>
            </div>
        </div>

        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
            <div class="px-lg py-4 border-b border-outline-variant flex flex-col sm:flex-row justify-between items-center bg-[#fdfdfd] gap-4">
                <h3 class="font-headline-md text-headline-md text-primary">Daftar Transaksi</h3>
                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    <div class="relative w-full sm:w-64">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
                        <input type="text" id="customSearch" placeholder="Cari Nama/ID/Resi..." class="w-full pl-10 pr-4 py-2 bg-surface-container-low border border-outline-variant rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary-container transition-shadow text-sm"/>
                    </div>
                    <select id="filterStatus" class="bg-surface-container-low border border-outline-variant rounded-lg px-4 py-2 text-sm font-label-md text-on-surface outline-none focus:ring-2 focus:ring-secondary-container cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="pending">PENDING</option>
                        <option value="lunas">LUNAS</option>
                        <option value="ditolak">DITOLAK</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table id="tableOrders" class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#F1F5F9] border-b border-outline-variant">
                            <th class="py-3 px-lg font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Order ID</th>
                            <th class="py-3 px-lg font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Pelanggan & Alamat</th>
                            <th class="py-3 px-lg font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Total</th>
                            <th class="py-3 px-lg font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-center">Bukti</th>
                            <th class="py-3 px-lg font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Status</th>
                            <th class="py-3 px-lg font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Waktu</th>
                            <th class="py-3 px-lg font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant font-body-md text-body-md">
                    <?php
                    $orders = $conn->query("SELECT * FROM orders ORDER BY id DESC");
                    while ($o = $orders->fetch_assoc()):
                        $wa = preg_replace('/[^0-9]/', '', $o['no_wa']);
                        if (substr($wa,0,1) == '0') $wa = '62' . substr($wa,1);
                        
                        $status = $o['status'] ?? 'pending';
                        $badgeClass = match($status) {
                            'lunas' => 'bg-emerald-100 text-emerald-800',
                            'ditolak' => 'bg-error-container text-on-error-container',
                            default => 'bg-amber-100 text-amber-800'
                        };
                    ?>
                        <tr class="hover:bg-[#F8FAFC] transition-colors group">
                            <td class="py-4 px-lg font-bold text-secondary align-top">#<?= $o['id'] ?></td>
                            
                            <td class="py-4 px-lg align-top">
                                <div class="font-label-lg text-primary"><?= htmlspecialchars($o['nama']) ?></div>
                                <div class="flex items-center gap-1 mt-1 mb-2">
                                    <span class="material-symbols-outlined text-[14px] text-emerald-600">chat</span>
                                    <a href="https://wa.me/<?= $wa ?>" target="_blank" class="text-xs text-on-surface-variant hover:text-emerald-600 font-medium transition-colors"><?= $o['no_wa'] ?></a>
                                </div>
                                <div class="mt-2 text-[11px] text-on-surface-variant leading-relaxed bg-surface-container-low p-2 rounded-lg border border-outline-variant max-w-xs">
                                    <span class="font-bold text-on-surface uppercase tracking-wider text-[9px] flex items-center gap-1 mb-1">
                                        <span class="material-symbols-outlined text-[12px]">location_on</span> Alamat Pengiriman
                                    </span>
                                    <?= nl2br(htmlspecialchars($o['alamat'] ?? '-')) ?>
                                </div>
                            </td>
                            
                            <td class="py-4 px-lg align-top">
                                <div class="text-xs text-on-surface-variant font-bold mb-1 border border-outline-variant inline-block px-2 py-0.5 rounded-md bg-surface-container-highest">
                                    <?= strtoupper($o['metode']) ?>
                                </div>
                                <div class="font-bold text-primary">Rp <?= number_format($o['total'],0,',','.') ?></div>
                            </td>
                            
                            <td class="py-4 px-lg text-center align-top">
                                <?php if($o['bukti']): ?>
                                    <a href="uploads/bukti_bayar/<?= $o['bukti'] ?>" target="_blank" class="inline-flex p-2 bg-secondary-fixed text-on-secondary-fixed rounded-lg hover:bg-secondary hover:text-white transition-colors" title="Lihat Bukti">
                                        <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                                    </a>
                                <?php else: ?>
                                    <span class="text-on-surface-variant italic text-xs">Belum upload</span>
                                <?php endif; ?>
                            </td>
                            
                            <td class="py-4 px-lg align-top">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide <?= $badgeClass ?>">
                                    <?= $status ?>
                                </span>
                                
                                <?php if ($status == 'lunas' && !empty($o['no_resi'])): ?>
                                    <div class="mt-2 text-[10px] font-bold text-on-surface-variant border border-outline-variant inline-flex items-center gap-1 px-2 py-1 rounded-md bg-surface-container-low">
                                        <span class="material-symbols-outlined text-[12px]">local_shipping</span> 
                                        <span class="text-secondary"><?= htmlspecialchars($o['no_resi']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </td>
                            
                            <td class="py-4 px-lg text-on-surface-variant text-xs align-top">
                                <div><?= date('d M Y', strtotime($o['created_at'])) ?></div>
                                <div class="font-bold text-primary mt-0.5"><?= date('H:i', strtotime($o['created_at'])) ?> WIB</div>
                            </td>
                            
                            <td class="py-4 px-lg align-top">
                                <div class="flex flex-wrap items-center justify-center gap-1">
                                    <a href="invoice.php?id=<?= $o['id'] ?>" target="_blank" title="Cetak Invoice" class="p-1.5 text-on-surface-variant hover:text-secondary hover:bg-surface-container-highest rounded transition-colors">
                                        <span class="material-symbols-outlined text-[20px]">print</span>
                                    </a>

                                    <button class="detailBtn p-1.5 text-on-surface-variant hover:text-primary hover:bg-surface-container-highest rounded transition-colors" data-id="<?= $o['id'] ?>" title="Detail Pesanan">
                                        <span class="material-symbols-outlined text-[20px] transition-transform duration-200 icon-expand">expand_more</span>
                                    </button>

                                    <?php if ($status == 'pending'): ?>
                                        <button onclick="confirmAction('verifikasi', <?= $o['id'] ?>, 'Terima pembayaran ini?')" class="p-1.5 text-on-surface-variant hover:text-emerald-600 hover:bg-emerald-50 rounded transition-colors" title="Verifikasi Pesanan">
                                            <span class="material-symbols-outlined text-[20px]">check_circle</span>
                                        </button>
                                        <button onclick="confirmAction('tolak', <?= $o['id'] ?>, 'Tolak pesanan ini?')" class="p-1.5 text-on-surface-variant hover:text-error hover:bg-error-container rounded transition-colors" title="Tolak Pesanan">
                                            <span class="material-symbols-outlined text-[20px]">cancel</span>
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($status == 'lunas'): ?>
                                        <button onclick="inputResi(<?= $o['id'] ?>, '<?= htmlspecialchars($o['no_resi'] ?? '') ?>')" class="p-1.5 text-on-surface-variant hover:text-secondary hover:bg-secondary-fixed rounded transition-colors" title="Input/Edit Nomor Resi">
                                            <span class="material-symbols-outlined text-[20px]">local_shipping</span>
                                        </button>
                                    <?php endif; ?>

                                    <button onclick="confirmAction('hapus', <?= $o['id'] ?>, 'Hapus permanen data ini?')" class="p-1.5 text-on-surface-variant hover:text-error hover:bg-error-container rounded transition-colors" title="Hapus Permanen">
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script>
$(document).ready(function() {
    let table = $('#tableOrders').DataTable({
        responsive: true,
        autoWidth: false,
        order: [[0, 'desc']],
        dom: 'rtp', 
        language: { 
            paginate: { previous: "Prev", next: "Next" }
        }
    });

    $('#customSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    $('#filterStatus').on('change', function() {
        table.column(4).search(this.value).draw();
    });

    $('#tableOrders tbody').on('click', '.detailBtn', function () {
        let tr = $(this).closest('tr');
        let row = table.row(tr);
        let id = $(this).data('id');
        let btn = $(this);
        let icon = btn.find('.icon-expand');

        if (row.child.isShown()) {
            row.child.hide();
            icon.text('expand_more').removeClass('rotate-180');
            btn.removeClass('text-primary bg-surface-container-highest');
        } else {
            icon.text('hourglass_empty').addClass('animate-spin');
            
            fetch('detail_order.php?id=' + id)
                .then(res => res.text())
                .then(html => {
                    row.child(`<div class="p-lg bg-surface-container-highest rounded-xl border border-outline-variant m-2 shadow-inner overflow-x-auto text-sm">${html}</div>`).show();
                    icon.text('expand_less').removeClass('animate-spin hourglass_empty');
                    btn.addClass('text-primary bg-surface-container-highest');
                })
                .catch(() => {
                    icon.text('expand_more').removeClass('animate-spin');
                });
        }
    });

    <?php if(isset($_SESSION['flash_msg'])): ?>
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: '<?= $_SESSION['flash_type'] ?>',
        title: '<?= $_SESSION['flash_msg'] ?>',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
    <?php unset($_SESSION['flash_msg'], $_SESSION['flash_type']); endif; ?>
});

function confirmAction(aksi, id, text) {
    Swal.fire({
        title: 'Konfirmasi',
        text: text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0051d5', // SmartShop secondary
        cancelButtonColor: '#76777d', // SmartShop outline
        confirmButtonText: 'Ya, Proses',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-xl' }
    }).then((result) => {
        if (result.isConfirmed) {
            submitForm(aksi, id);
        }
    })
}

function inputResi(id, currentResi) {
    Swal.fire({
        title: 'Input Nomor Resi',
        input: 'text',
        inputValue: currentResi,
        inputPlaceholder: 'Contoh: JNT1234567890',
        showCancelButton: true,
        confirmButtonColor: '#0051d5',
        cancelButtonColor: '#76777d',
        confirmButtonText: 'Simpan',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-xl' },
        inputValidator: (value) => {
            if (!value) {
                return 'Nomor resi tidak boleh kosong!'
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            submitForm('input_resi', id, result.value);
        }
    })
}

function submitForm(aksi, id, resiValue = null) {
    let form = document.createElement('form');
    form.method = 'POST';
    form.action = 'orders.php';

    let inputAksi = document.createElement('input');
    inputAksi.type = 'hidden';
    inputAksi.name = 'aksi';
    inputAksi.value = aksi;
    form.appendChild(inputAksi);

    let inputId = document.createElement('input');
    inputId.type = 'hidden';
    inputId.name = 'id';
    inputId.value = id;
    form.appendChild(inputId);

    if (resiValue !== null) {
        let inputResi = document.createElement('input');
        inputResi.type = 'hidden';
        inputResi.name = 'no_resi';
        inputResi.value = resiValue;
        form.appendChild(inputResi);
    }

    document.body.appendChild(form);
    form.submit();
}
</script>

</body>
</html>