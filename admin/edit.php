<?php
session_start();
include 'config.php';

/*
|--------------------------------------------------------------------------
| CEK LOGIN ADMIN
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("ID tidak ditemukan");
}

$id = intval($_GET['id']);

// =======================
// PROSES UPDATE
// =======================
if (isset($_POST['update'])) {
    $nama  = $_POST['nama_hp'];
    $brand = $_POST['brand'];
    
    // Menghapus pemisah ribuan agar bisa masuk ke database dengan benar
    $harga_raw = str_replace(['.', ','], '', $_POST['harga']);
    $harga = intval($harga_raw);
    
    $stok  = $_POST['stok'];
    $desk  = $_POST['deskripsi'];
    $spec  = $_POST['spesifikasi'];
    $kat   = $_POST['kategori'];

    $stmt = $conn->prepare("UPDATE phones SET 
        nama_hp=?, brand=?, harga=?, stok=?, deskripsi=?, spesifikasi=?, kategori=? 
        WHERE id=?");

    $stmt->bind_param("ssdisssi", $nama, $brand, $harga, $stok, $desk, $spec, $kat, $id);
    $stmt->execute();

    $uploadDir = __DIR__ . "/uploads/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    if (!empty($_FILES['images']['name'][0])) {
        $allowed = ['jpg','jpeg','png'];
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $file_name = $_FILES['images']['name'][$key];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) continue;
            $new_name = uniqid() . '.' . $ext;
            if (move_uploaded_file($tmp_name, $uploadDir . $new_name)) {
                $stmtImg = $conn->prepare("INSERT INTO phone_images (phone_id, image) VALUES (?, ?)");
                $stmtImg->bind_param("is", $id, $new_name);
                $stmtImg->execute();
            }
        }
    }

    header("Location: edit.php?id=$id&success=1");
    exit;
}

// =======================
// AMBIL DATA
// =======================
$stmt = $conn->prepare("SELECT * FROM phones WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) die("Data tidak ditemukan");

$stmtImg = $conn->prepare("SELECT * FROM phone_images WHERE phone_id=?");
$stmtImg->bind_param("i", $id);
$stmtImg->execute();
$resultImages = $stmtImg->get_result();
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Edit Product - SmartShop Admin</title>
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
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #0051d5;
            box-shadow: 0 0 0 4px rgba(0, 81, 213, 0.1);
            transition: all 0.2s ease;
        }

        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }

        .drag-zone { transition: all 0.2s ease; }
        .drag-zone.dragover {
            background-color: #f6f3f5;
            border-color: #0051d5;
            transform: scale(1.01);
        }
    </style>
</head>
<body class="flex bg-[#F8FAFC] overflow-hidden">
<aside class="fixed left-0 top-0 h-screen w-[280px] bg-surface-container-low border-r border-outline-variant flex flex-col p-md gap-sm z-40 hidden md:flex">
    <div class="px-md py-lg border-b border-outline-variant mb-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-primary-container flex items-center justify-center text-on-primary-container">
            <span class="material-symbols-outlined" data-icon="storefront">storefront</span>
        </div>
        <div>
            <h1 class="font-headline-sm text-headline-sm font-bold text-primary">SmartShop</h1>
            <p class="font-label-md text-label-md text-on-surface-variant">Admin Terminal</p>
        </div>
    </div>
    <nav class="flex-1 flex flex-col gap-1">
        <a class="flex items-center gap-3 px-md py-sm text-on-surface-variant hover:bg-surface-container-highest rounded-lg font-label-lg text-label-lg transition-all active:opacity-80" href="dashboard.php">
            <span class="material-symbols-outlined">dashboard</span> Dashboard
        </a>
        <a class="flex items-center gap-3 px-md py-sm bg-secondary-container text-on-secondary-container rounded-lg font-bold font-label-lg text-label-lg transition-all active:opacity-80" href="index.php">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">inventory_2</span> Inventory
        </a>
        <a class="flex items-center gap-3 px-md py-sm text-on-surface-variant hover:bg-surface-container-highest rounded-lg font-label-lg text-label-lg transition-all active:opacity-80" href="orders.php">
            <span class="material-symbols-outlined">shopping_cart</span> Orders
        </a>
        <a class="flex items-center gap-3 px-md py-sm text-on-surface-variant hover:bg-surface-container-highest rounded-lg font-label-lg text-label-lg transition-all active:opacity-80" href="customers.php">
            <span class="material-symbols-outlined">group</span> Customers
        </a>
        <a class="flex items-center gap-3 px-md py-sm text-on-surface-variant hover:bg-surface-container-highest rounded-lg font-label-lg text-label-lg transition-all active:opacity-80" href="pemasukan.php">
            <span class="material-symbols-outlined">analytics</span> Analytics
        </a>
    </nav>
    <div class="mt-auto border-t border-outline-variant pt-4 flex flex-col gap-1">
        <a class="flex items-center gap-3 px-md py-sm text-on-surface-variant hover:bg-surface-container-highest rounded-lg font-label-lg text-label-lg transition-all active:opacity-80" href="#">
            <span class="material-symbols-outlined">settings</span> Settings
        </a>
        <a class="flex items-center gap-3 px-md py-sm text-error hover:bg-error-container hover:text-on-error-container rounded-lg font-label-lg text-label-lg transition-all active:opacity-80" href="logout.php" onclick="return confirm('Yakin ingin logout?')">
            <span class="material-symbols-outlined">logout</span> Logout
        </a>
    </div>
</aside>

<main class="flex-1 flex flex-col md:ml-[280px] h-screen overflow-hidden">
    <header class="bg-surface-container-lowest flex justify-between items-center w-full px-xl h-16 sticky top-0 z-50 border-b border-outline-variant shadow-sm shrink-0">
        <button class="md:hidden text-primary hover:bg-surface-container-low p-sm rounded-full transition-colors">
            <span class="material-symbols-outlined" data-icon="menu">menu</span>
        </button>
        <div class="md:hidden font-headline-md text-headline-md font-bold text-primary">SmartShop</div>
        <div class="hidden md:flex items-center gap-sm text-on-surface-variant font-body-md text-body-md">
            <span>Inventory</span>
            <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
            <span class="text-primary font-medium">Edit Product</span>
        </div>
        <div class="flex items-center gap-md">
            <button class="text-on-surface-variant hover:text-secondary transition-colors active:scale-95 transition-transform duration-200">
                <span class="material-symbols-outlined" data-icon="notifications">notifications</span>
            </button>
            <div class="w-8 h-8 rounded-full bg-primary-container overflow-hidden border border-outline-variant cursor-pointer hover:ring-2 ring-primary-fixed-dim transition-all">
                <img alt="User Profile" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCpqTXlUd-VW3L-5EUAlMnZ9pCYqIjgXbioDOQGEABlPI21zOiYA6qc9Bb-2e-kq36LzICGH1lIS6klJvWi7VaH4yN0Hgeg5bd3fwyBNUfU7YIS07AlQE7eLDjLfwZQJDc6-ihkLltFAOtjHj5vwkr1ngzv8_8BjwTMq2icnANI_v76a8RtLk1TG5hEs1_IAE1ElQ2Bkby84aEKRAS1NjDooC7slzUXrYmzCmdAlFh6KV6IV-w5EFEo7CQkfIdlDk01dNoiqnKllvs"/>
            </div>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto p-md md:p-xl scroll-smooth">
        <div class="max-w-5xl mx-auto flex flex-col gap-lg">
            
            <?php if (isset($_GET['success'])): ?>
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg font-body-md text-body-md flex items-start gap-3">
                    <span class="material-symbols-outlined shrink-0 mt-0.5 text-emerald-600">check_circle</span>
                    Data produk berhasil diperbarui!
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-lg">
                
                <div class="lg:col-span-3 flex flex-col md:flex-row md:items-center justify-between gap-md mb-sm">
                    <div>
                        <div class="flex items-center gap-sm mb-1">
                            <a href="index.php" class="text-on-surface-variant hover:bg-surface-container-highest p-1 rounded-full transition-colors flex items-center justify-center inline-block">
                                <span class="material-symbols-outlined" data-icon="arrow_back">arrow_back</span>
                            </a>
                            <h2 class="font-display-lg text-display-lg text-primary">Edit Product</h2>
                        </div>
                        <p class="font-body-lg text-body-lg text-on-surface-variant ml-10">Update details for <?= htmlspecialchars($data['nama_hp']) ?>.</p>
                    </div>
                    <div class="flex items-center gap-sm ml-10 md:ml-0">
                        <a href="index.php" class="px-lg py-sm rounded-full bg-surface-container-high text-primary font-label-lg text-label-lg hover:bg-surface-container-highest transition-colors inline-block text-center cursor-pointer">Discard</a>
                        <button type="submit" name="update" class="px-lg py-sm rounded-full bg-secondary-container text-on-secondary-container font-label-lg text-label-lg hover:opacity-90 transition-opacity shadow-sm flex items-center gap-xs">
                            <span class="material-symbols-outlined text-[18px]" data-icon="save">save</span>
                            Save Changes
                        </button>
                    </div>
                </div>

                <div class="lg:col-span-2 flex flex-col gap-lg">
                    <div class="bg-surface-container-lowest rounded-xl p-lg shadow-sm border border-outline-variant/30 flex flex-col gap-md">
                        <h3 class="font-headline-md text-headline-md text-primary border-b border-surface-variant pb-sm mb-sm">Basic Information</h3>
                        <div class="flex flex-col gap-xs">
                            <label class="font-label-lg text-label-lg text-on-surface" for="product_name">Smartphone Name</label>
                            <input required name="nama_hp" value="<?= htmlspecialchars($data['nama_hp']) ?>" class="w-full bg-surface-bright border border-outline-variant rounded-lg px-md py-3 font-body-md text-body-md text-on-background placeholder:text-on-surface-variant/50 focus:border-secondary focus:ring-secondary/20" id="product_name" type="text"/>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
                            <div class="flex flex-col gap-xs">
                                <label class="font-label-lg text-label-lg text-on-surface" for="brand">Brand</label>
                                <input required type="text" name="brand" value="<?= htmlspecialchars($data['brand']) ?>" class="w-full bg-surface-bright border border-outline-variant rounded-lg px-md py-3 font-body-md text-body-md text-on-background focus:border-secondary focus:ring-secondary/20" id="brand">
                            </div>
                            <div class="flex flex-col gap-xs">
                                <label class="font-label-lg text-label-lg text-on-surface" for="category">Category</label>
                                <select required name="kategori" class="w-full bg-surface-bright border border-outline-variant rounded-lg px-md py-3 font-body-md text-body-md text-on-background focus:border-secondary focus:ring-secondary/20 appearance-none" id="category">
                                    <option value="Android" <?= $data['kategori'] == 'Android' ? 'selected' : '' ?>>Android</option>
                                    <option value="iPhone" <?= $data['kategori'] == 'iPhone' ? 'selected' : '' ?>>iPhone / iOS</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-surface-container-lowest rounded-xl p-lg shadow-sm border border-outline-variant/30 flex flex-col gap-md">
                        <h3 class="font-headline-md text-headline-md text-primary border-b border-surface-variant pb-sm mb-sm">Description</h3>
                        <div class="flex flex-col gap-xs">
                            <label class="font-label-lg text-label-lg text-on-surface" for="short_desc">Short Description</label>
                            <textarea name="deskripsi" class="w-full bg-surface-bright border border-outline-variant rounded-lg px-md py-3 font-body-md text-body-md text-on-background placeholder:text-on-surface-variant/50 focus:border-secondary focus:ring-secondary/20 resize-none" id="short_desc" rows="3"><?= htmlspecialchars($data['deskripsi']) ?></textarea>
                        </div>
                        <div class="flex flex-col gap-xs">
                            <label class="font-label-lg text-label-lg text-on-surface" for="detailed_specs">Detailed Specifications</label>
                            <textarea name="spesifikasi" class="w-full bg-surface-bright border border-outline-variant rounded-lg px-md py-3 font-body-md text-body-md text-on-background placeholder:text-on-surface-variant/50 focus:border-secondary focus:ring-secondary/20 resize-none" id="detailed_specs" rows="6"><?= htmlspecialchars($data['spesifikasi']) ?></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-col gap-lg">
                    <div class="bg-surface-container-lowest rounded-xl p-lg shadow-sm border border-outline-variant/30 flex flex-col gap-md">
                        <h3 class="font-headline-md text-headline-md text-primary border-b border-surface-variant pb-sm mb-sm">Pricing & Inventory</h3>
                        <div class="flex flex-col gap-xs">
                            <label class="font-label-lg text-label-lg text-on-surface" for="price">Base Price</label>
                            <div class="relative flex items-center">
                                <span class="absolute left-md font-body-md text-on-surface-variant pointer-events-none">Rp</span>
                                <input required name="harga" value="<?= $data['harga'] ?>" class="w-full bg-surface-bright border border-outline-variant rounded-lg pl-[40px] pr-md py-3 font-body-md text-body-md text-on-background focus:border-secondary focus:ring-secondary/20" id="price" oninput="formatRupiah(this)" type="text" inputmode="numeric"/>
                            </div>
                        </div>
                        <div class="flex flex-col gap-xs">
                            <label class="font-label-lg text-label-lg text-on-surface" for="stock">Stock Quantity</label>
                            <div class="relative flex items-center">
                                <input required name="stok" value="<?= $data['stok'] ?>" class="w-full bg-surface-bright border border-outline-variant rounded-lg pl-md pr-[50px] py-3 font-body-md text-body-md text-on-background focus:border-secondary focus:ring-secondary/20 appearance-none" id="stock" type="number" min="0"/>
                                <span class="absolute right-md font-body-md text-on-surface-variant pointer-events-none">Units</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-surface-container-lowest rounded-xl p-lg shadow-sm border border-outline-variant/30 flex flex-col gap-md">
                        <h3 class="font-headline-md text-headline-md text-primary border-b border-surface-variant pb-sm mb-sm">Product Images</h3>
                        
                        <?php if ($resultImages->num_rows > 0): ?>
                            <div class="mb-2">
                                <p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider mb-2">Current Images</p>
                                <div class="grid grid-cols-3 gap-sm">
                                    <?php while ($img = $resultImages->fetch_assoc()): ?>
                                        <div class="relative aspect-square rounded-lg bg-surface-container-high overflow-hidden border border-outline-variant/30 group">
                                            <img src="uploads/<?= $img['image'] ?>" class="w-full h-full object-cover">
                                            <a href="hapus_gambar.php?id=<?= $img['id'] ?>&phone_id=<?= $id ?>" onclick="return confirm('Hapus gambar ini?')" class="absolute top-1 right-1 w-6 h-6 bg-surface-container-lowest/90 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-error-container text-error shadow-sm">
                                                <span class="material-symbols-outlined text-[16px]" data-icon="delete">delete</span>
                                            </a>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider mt-2">Upload New</p>
                        <div class="drag-zone border-2 border-dashed border-outline-variant rounded-xl p-xl flex flex-col items-center justify-center text-center cursor-pointer hover:bg-surface-container-low min-h-[120px]" id="drop-zone">
                            <span class="material-symbols-outlined text-[32px] text-on-surface-variant/50 mb-xs" data-icon="add_photo_alternate">add_photo_alternate</span>
                            <p class="font-label-md text-label-md text-primary mb-1">Click or drag images</p>
                            <input name="images[]" accept="image/*" class="hidden" id="file-upload" multiple="" type="file"/>
                        </div>
                        
                        <div id="previewArea" class="grid grid-cols-3 gap-sm mt-2 empty:hidden"></div>
                    </div>
                </div>
            </form>
            <div class="h-xl"></div>
        </div>
    </div>
</main>

<script>
    // Inisialisasi format harga saat load
    document.addEventListener("DOMContentLoaded", function() {
        const priceInput = document.getElementById('price');
        if (priceInput.value) {
            priceInput.value = formatRupiahValue(priceInput.value);
        }
    });

    // Real-time Rupiah Formatting
    function formatRupiah(input) {
        input.value = formatRupiahValue(input.value);
    }

    function formatRupiahValue(val) {
        let value = val.replace(/[^,\d]/g, '').toString();
        let split = value.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    }

    // Drag and Drop Zone & Preview Interactivity
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file-upload');
    const previewArea = document.getElementById('previewArea');

    dropZone.addEventListener('click', () => fileInput.click());

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('dragover');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('dragover');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        
        if(e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            handleFiles(e.dataTransfer.files);
        }
    });

    fileInput.addEventListener('change', function(e) {
        handleFiles(e.target.files);
    });

    function handleFiles(files) {
        previewArea.innerHTML = ''; 
        
        [...files].forEach(file => {
            const reader = new FileReader();
            reader.onload = function(event) {
                const div = document.createElement('div');
                div.className = 'relative aspect-square rounded-lg bg-surface-container-high overflow-hidden border border-outline-variant/30 group';
                
                div.innerHTML = `
                    <img src="${event.target.result}" class="w-full h-full object-cover">
                    <button type="button" class="absolute top-1 right-1 w-6 h-6 bg-surface-container-lowest/80 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-error-container text-error" onclick="this.parentElement.remove()">
                        <span class="material-symbols-outlined text-[16px]" data-icon="close">close</span>
                    </button>
                `;
                
                previewArea.appendChild(div);
            }
            reader.readAsDataURL(file);
        });
    }
</script>
</body>
</html>