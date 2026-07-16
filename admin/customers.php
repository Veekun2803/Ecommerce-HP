<?php
session_start();
include 'config.php';

/* |--------------------------------------------------------------------------
| CEK LOGIN ADMIN
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit;
}

date_default_timezone_set('Asia/Jakarta');

/* |--------------------------------------------------------------------------
| HANDLE HAPUS PELANGGAN
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] == 'hapus') {
    $id = intval($_POST['id']);
    
    try {
        $stmt = $conn->prepare("DELETE FROM customers WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['flash_msg'] = "Data pelanggan berhasil dihapus.";
            $_SESSION['flash_type'] = "success";
        }
    } catch (Exception $e) {
        $_SESSION['flash_msg'] = "Gagal! Pelanggan ini memiliki riwayat pesanan aktif.";
        $_SESSION['flash_type'] = "error";
    }
    
    header("Location: customers.php");
    exit;
}

// Menghitung total pelanggan untuk statistik
$total_customers_query = $conn->query("SELECT COUNT(id) as total FROM customers");
$total_customers = $total_customers_query->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SmartShop Admin - Customer Management</title>
    
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
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
        
        /* Custom Styling for DataTables to match Tailwind Theme */
        .dataTables_wrapper .dataTables_filter { display: none; }
        .dataTables_wrapper .dataTables_paginate { display: flex; gap: 4px; padding: 16px; justify-content: flex-end; align-items: center; }
        .dataTables_wrapper .dataTables_paginate .paginate_button { 
            padding: 4px 12px; border: 1px solid #c6c6cd; border-radius: 8px; color: #45464d !important; 
            cursor: pointer; transition: all 0.2s; font-size: 14px;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover { background: #f6f3f5; }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #0051d5 !important; color: #ffffff !important; border-color: #0051d5; font-weight: 600;
        }
        .dataTables_wrapper .dataTables_info { padding: 16px; font-size: 14px; color: #45464d; float: left; }
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
            <span class="material-symbols-outlined">dashboard</span> Dashboard
        </a>
        <a class="flex items-center gap-3 px-md py-sm text-on-surface-variant dark:text-on-surface-variant hover:bg-surface-container-highest dark:hover:bg-surface-container-highest rounded-lg font-label-lg text-label-lg transition-all active:opacity-80" href="index.php">
            <span class="material-symbols-outlined">inventory_2</span> Inventory
        </a>
        <a class="flex items-center gap-3 px-md py-sm text-on-surface-variant dark:text-on-surface-variant hover:bg-surface-container-highest dark:hover:bg-surface-container-highest rounded-lg font-label-lg text-label-lg transition-all active:opacity-80" href="orders.php">
            <span class="material-symbols-outlined">shopping_cart</span> Orders
        </a>
        <a class="flex items-center gap-3 px-md py-sm bg-secondary-container dark:bg-secondary-container text-on-secondary-container dark:text-on-secondary-container rounded-lg font-bold font-label-lg text-label-lg transition-all active:opacity-80" href="customers.php">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">group</span> Customers
        </a>
        <a class="flex items-center gap-3 px-md py-sm text-on-surface-variant dark:text-on-surface-variant hover:bg-surface-container-highest dark:hover:bg-surface-container-highest rounded-lg font-label-lg text-label-lg transition-all active:opacity-80" href="pemasukan.php">
            <span class="material-symbols-outlined">analytics</span> Analytics
        </a>
    </nav>
    <div class="mt-auto border-t border-outline-variant pt-4 flex flex-col gap-1">
        <a class="flex items-center gap-3 px-md py-sm text-on-surface-variant dark:text-on-surface-variant hover:bg-surface-container-highest dark:hover:bg-surface-container-highest rounded-lg font-label-lg text-label-lg transition-all active:opacity-80" href="#">
            <span class="material-symbols-outlined">settings</span> Settings
        </a>
        <a class="flex items-center gap-3 px-md py-sm text-error hover:bg-error-container hover:text-on-error-container rounded-lg font-label-lg text-label-lg transition-all active:opacity-80" href="logout.php" onclick="return confirm('Yakin ingin logout?')">
            <span class="material-symbols-outlined">logout</span> Logout
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
                <span class="material-symbols-outlined text-[18px]">add</span> Add Phone
            </a>
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
                <h2 class="font-headline-lg text-headline-lg text-primary mb-1">Customer Management</h2>
                <p class="font-body-md text-body-md text-on-surface-variant">View and manage your registered customer base.</p>
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
            <div class="bg-surface-container-lowest rounded-xl p-lg border border-outline-variant shadow-sm flex items-start gap-4">
                <div class="w-12 h-12 bg-primary-fixed rounded-lg flex items-center justify-center text-on-primary-fixed">
                    <span class="material-symbols-outlined text-2xl">group</span>
                </div>
                <div>
                    <p class="font-label-md text-label-md text-on-surface-variant mb-1 uppercase tracking-wider">Total Customers</p>
                    <h3 class="font-display-lg text-display-lg text-primary"><?= number_format($total_customers, 0, ',', '.') ?></h3>
                    <p class="font-body-md text-body-md text-emerald-600 flex items-center gap-1 mt-1">
                        <span class="material-symbols-outlined text-[16px]">trending_up</span> Active accounts
                    </p>
                </div>
            </div>
            </div>

        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
            <div class="px-lg py-4 border-b border-outline-variant flex flex-col sm:flex-row justify-between items-center bg-[#fdfdfd] gap-4">
                <h3 class="font-headline-md text-headline-md text-primary">Daftar Pelanggan</h3>
                <div class="relative w-full sm:w-80">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
                    <input id="customSearch" class="w-full pl-10 pr-4 py-2 bg-surface-container-low border border-outline-variant rounded-lg font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-secondary-container transition-shadow" placeholder="Cari Nama/WA..." type="text"/>
                </div>
            </div>

            <div class="overflow-x-auto w-full">
                <table id="tableCustomers" class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#F1F5F9] border-b border-outline-variant">
                            <th class="py-3 px-lg font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-16">ID</th>
                            <th class="py-3 px-lg font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Profil Pelanggan</th>
                            <th class="py-3 px-lg font-label-md text-label-md text-on-surface-variant uppercase tracking-wider hidden md:table-cell">Alamat Terdaftar</th>
                            <th class="py-3 px-lg font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Statistik Belanja</th>
                            <th class="py-3 px-lg font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant font-body-md text-body-md">
                    <?php
                    $sql = "SELECT 
                                c.*,
                                (SELECT COUNT(id) FROM orders WHERE customer_id = c.id) as total_order,
                                (SELECT SUM(total) FROM orders WHERE customer_id = c.id AND status = 'lunas') as total_spent
                            FROM customers c 
                            ORDER BY c.id DESC";
                    $customers = $conn->query($sql);
                    
                    if ($customers && $customers->num_rows > 0) {
                        while ($c = $customers->fetch_assoc()):
                            $wa = preg_replace('/[^0-9]/', '', $c['no_wa'] ?? '');
                            if (substr($wa,0,1) == '0') $wa = '62' . substr($wa,1);
                            
                            $nama = !empty($c['nama_lengkap']) ? $c['nama_lengkap'] : (!empty($c['username']) ? $c['username'] : 'Customer Tanpa Nama');
                    ?>
                        <tr class="hover:bg-[#F8FAFC] transition-colors group">
                            <td class="py-4 px-lg font-label-lg text-label-lg text-on-surface-variant align-top">#<?= $c['id'] ?></td>
                            
                            <td class="py-4 px-lg align-top">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-secondary to-primary-container text-on-primary flex items-center justify-center font-headline-md text-headline-md shrink-0">
                                        <?= strtoupper(substr($nama, 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="font-headline-md text-headline-md text-primary group-hover:text-secondary transition-colors"><?= htmlspecialchars($nama) ?></p>
                                        <?php if(!empty($wa)): ?>
                                            <p class="font-body-md text-body-md text-on-surface-variant mt-1 flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[14px]">phone</span> <?= htmlspecialchars($c['no_wa']) ?>
                                            </p>
                                        <?php else: ?>
                                            <p class="font-body-md text-body-md text-error italic text-xs mt-1">Belum ada WA</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="py-4 px-lg hidden md:table-cell text-on-surface-variant max-w-xs truncate align-top" title="<?= htmlspecialchars($c['alamat'] ?? '') ?>">
                                <?= !empty($c['alamat']) ? htmlspecialchars($c['alamat']) : '<span class="italic opacity-50">Alamat belum diatur</span>' ?>
                            </td>
                            
                            <td class="py-4 px-lg align-top">
                                <div class="flex flex-col gap-1">
                                    <?php if($c['total_order'] > 0): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-secondary-fixed text-on-secondary-fixed-variant font-label-md text-label-md w-fit">
                                            <?= $c['total_order'] ?> Orders
                                        </span>
                                        <span class="font-label-lg text-label-lg text-primary mt-1">Rp <?= number_format($c['total_spent'] ?? 0, 0, ',', '.') ?></span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-surface-variant text-on-surface-variant font-label-md text-label-md w-fit">
                                            Belum Order
                                        </span>
                                        <span class="font-label-lg text-label-lg text-on-surface-variant mt-1">Rp 0</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <td class="py-4 px-lg text-right align-top">
                                <div class="flex justify-end gap-1">
                                    <?php if(!empty($wa)): ?>
                                    <a href="https://wa.me/<?= $wa ?>" target="_blank" class="p-1.5 inline-block text-[#16a34a] hover:bg-[#22c55e]/20 rounded transition-colors" title="WhatsApp Chat">
                                        <span class="material-symbols-outlined text-[20px]">chat</span>
                                    </a>
                                    <?php endif; ?>
                                    <button class="p-1.5 inline-block text-error hover:bg-error-container rounded transition-colors" onclick="hapusPelanggan(<?= $c['id'] ?>, '<?= addslashes($nama) ?>')" title="Delete Customer">
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php 
                        endwhile; 
                    } 
                    ?>
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
$(document).ready(function() {
    // Inisialisasi DataTables
    let table = $('#tableCustomers').DataTable({
        responsive: true,
        autoWidth: false,
        order: [[0, 'desc']], 
        dom: '<"top">rt<"bottom"ip><"clear">', // Menghilangkan default filter dan merapikan posisi pagination/info
        language: { 
            paginate: { previous: "Prev", next: "Next" },
            emptyTable: "Belum ada data pelanggan terdaftar.",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "No entries available"
        }
    });

    // Custom Search Integration
    $('#customSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    // SweetAlert Flash Messages
    <?php if(isset($_SESSION['flash_msg'])): ?>
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: '<?= $_SESSION['flash_type'] ?>',
        title: '<?= $_SESSION['flash_msg'] ?>',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true
    });
    <?php unset($_SESSION['flash_msg'], $_SESSION['flash_type']); endif; ?>
});

// Fungsi Hapus Pelanggan
function hapusPelanggan(id, nama) {
    Swal.fire({
        title: 'Hapus Pelanggan?',
        html: `Anda yakin ingin menghapus <b>${nama}</b>?<br><span class="text-sm text-error mt-2 block">Perhatian: Jika pelanggan ini memiliki riwayat pesanan, proses hapus mungkin akan digagalkan oleh sistem.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ba1a1a', // Tailwind error color
        cancelButtonColor: '#76777d', // Tailwind outline color
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-xl' }
    }).then((result) => {
        if (result.isConfirmed) {
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = 'customers.php';

            let inputAksi = document.createElement('input');
            inputAksi.type = 'hidden';
            inputAksi.name = 'aksi';
            inputAksi.value = 'hapus';
            form.appendChild(inputAksi);

            let inputId = document.createElement('input');
            inputId.type = 'hidden';
            inputId.name = 'id';
            inputId.value = id;
            form.appendChild(inputId);

            document.body.appendChild(form);
            form.submit();
        }
    })
}

/* NOTIF ORDER SCRIPT SEPERTI INDEX.PHP */
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