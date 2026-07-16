<?php
session_start();
include 'config.php';

/*
|--------------------------------------------------------------------------
| CEK LOGIN ADMIN
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['submit'])) {
    $conn->begin_transaction();

    try {
        $nama_hp = $_POST['nama_hp'];
        $brand = $_POST['brand'];
        
        // Menghapus titik/koma dari input harga sebelum diubah ke integer
        $harga_raw = str_replace(['.', ','], '', $_POST['harga']);
        $harga = intval($harga_raw);

        $stok = intval($_POST['stok']);
        $deskripsi = $_POST['deskripsi'];
        $spesifikasi = $_POST['spesifikasi'];
        $kategori = $_POST['kategori'];

        if ($harga < 0 || $stok < 0) {
            throw new Exception("Harga atau Stok tidak boleh negatif!");
        }

        $stmt = $conn->prepare("INSERT INTO phones (nama_hp, brand, harga, stok, deskripsi, spesifikasi, kategori) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiisss", $nama_hp, $brand, $harga, $stok, $deskripsi, $spesifikasi, $kategori);
        $stmt->execute();
        $phone_id = $conn->insert_id;

        $uploadDir = __DIR__ . "/uploads/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                $nama_file = $_FILES['images']['name'][$key];
                $ext = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
                if (in_array($ext, $allowed) && $_FILES['images']['size'][$key] <= 2000000) {
                    $file_name = uniqid() . '.' . $ext;
                    if (move_uploaded_file($tmp_name, $uploadDir . $file_name)) {
                        $stmtImg = $conn->prepare("INSERT INTO phone_images (phone_id, image) VALUES (?, ?)");
                        $stmtImg->bind_param("is", $phone_id, $file_name);
                        $stmtImg->execute();
                    }
                }
            }
        }

        $conn->commit();
        $_SESSION['msg'] = "Data berhasil disimpan!";
        header("Location: index.php");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Add New Product - InvAdmin</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "secondary-fixed": "#dbe1ff",
                        "on-primary-fixed-variant": "#3f465c",
                        "on-secondary-fixed-variant": "#003ea8",
                        "on-secondary": "#ffffff",
                        "outline": "#76777d",
                        "on-primary-container": "#7c839b",
                        "on-surface-variant": "#45464d",
                        "on-secondary-fixed": "#00174b",
                        "surface-dim": "#dcd9db",
                        "secondary": "#0051d5",
                        "primary-fixed": "#dae2fd",
                        "on-primary": "#ffffff",
                        "surface-container-low": "#f6f3f5",
                        "on-tertiary-fixed-variant": "#574425",
                        "on-primary-fixed": "#131b2e",
                        "error-container": "#ffdad6",
                        "on-tertiary": "#ffffff",
                        "inverse-on-surface": "#f3f0f2",
                        "surface-container": "#f0edef",
                        "on-secondary-container": "#fefcff",
                        "secondary-container": "#316bf3",
                        "on-background": "#1b1b1d",
                        "error": "#ba1a1a",
                        "surface": "#fcf8fa",
                        "tertiary-fixed": "#fcdeb5",
                        "primary-container": "#131b2e",
                        "outline-variant": "#c6c6cd",
                        "surface-container-lowest": "#ffffff",
                        "tertiary-container": "#271901",
                        "tertiary-fixed-dim": "#dec29a",
                        "secondary-fixed-dim": "#b4c5ff",
                        "tertiary": "#000000",
                        "inverse-surface": "#303032",
                        "primary": "#000000",
                        "surface-container-highest": "#e4e2e4",
                        "on-surface": "#1b1b1d",
                        "surface-container-high": "#eae7e9",
                        "background": "#fcf8fa",
                        "on-error": "#ffffff",
                        "inverse-primary": "#bec6e0",
                        "primary-fixed-dim": "#bec6e0",
                        "on-error-container": "#93000a",
                        "on-tertiary-fixed": "#271901",
                        "surface-tint": "#565e74",
                        "on-tertiary-container": "#98805d",
                        "surface-bright": "#fcf8fa",
                        "surface-variant": "#e4e2e4"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "md": "16px",
                        "xl": "32px",
                        "gutter": "24px",
                        "sm": "8px",
                        "xs": "4px",
                        "base": "4px",
                        "lg": "24px",
                        "margin": "32px",
                        "2xl": "48px"
                    },
                    "fontFamily": {
                        "label-md": ["Plus Jakarta Sans"],
                        "body-md": ["Plus Jakarta Sans"],
                        "label-lg": ["Plus Jakarta Sans"],
                        "headline-lg": ["Plus Jakarta Sans"],
                        "display-lg": ["Plus Jakarta Sans"],
                        "headline-md": ["Plus Jakarta Sans"],
                        "body-lg": ["Plus Jakarta Sans"]
                    },
                    "fontSize": {
                        "label-md": ["12px", { "lineHeight": "16px", "fontWeight": "500" }],
                        "body-md": ["14px", { "lineHeight": "20px", "fontWeight": "400" }],
                        "label-lg": ["14px", { "lineHeight": "20px", "fontWeight": "600" }],
                        "headline-lg": ["24px", { "lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "600" }],
                        "display-lg": ["36px", { "lineHeight": "44px", "letterSpacing": "-0.02em", "fontWeight": "700" }],
                        "headline-md": ["20px", { "lineHeight": "28px", "fontWeight": "600" }],
                        "body-lg": ["16px", { "lineHeight": "24px", "fontWeight": "400" }]
                    }
                },
            }
        }
    </script>
<style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* Premium focus rings */
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #0051d5;
            box-shadow: 0 0 0 4px rgba(0, 81, 213, 0.1);
            transition: all 0.2s ease;
        }

        /* Hilangkan style panah atas bawah pada input number */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }

        /* Drag and drop zone transition */
        .drag-zone {
            transition: all 0.2s ease;
        }
        .drag-zone.dragover {
            background-color: #f6f3f5;
            border-color: #0051d5;
            transform: scale(1.01);
        }
    </style>
</head>
<body class="bg-background text-on-background min-h-screen flex overflow-hidden">
<nav class="fixed left-0 top-0 h-screen w-[280px] bg-surface-container-low border-r border-outline-variant flex flex-col p-md gap-sm z-40 hidden md:flex">
<div class="px-sm py-md flex items-center gap-sm mb-lg">
<div class="w-10 h-10 rounded-lg bg-primary-container flex items-center justify-center text-on-primary-container">
<span class="material-symbols-outlined" data-icon="storefront">storefront</span>
</div>
<div>
<h1 class="font-headline-sm text-headline-sm font-bold text-primary">SmartShop</h1>
<p class="font-label-md text-label-md text-on-surface-variant">Admin Terminal</p>
</div>
</div>
<div class="flex-1 flex flex-col gap-xs overflow-y-auto no-scrollbar">
<a class="flex items-center gap-md px-md py-sm text-on-surface-variant hover:bg-surface-container-highest transition-all rounded-lg active:opacity-80" href="dashboard.php">
<span class="material-symbols-outlined" data-icon="dashboard">dashboard</span>
<span class="font-label-lg text-label-lg">Dashboard</span>
</a>
<a class="flex items-center gap-md px-md py-sm bg-secondary-container text-on-secondary-container font-bold rounded-lg active:opacity-80" href="index.php">
<span class="material-symbols-outlined" data-icon="inventory_2">inventory_2</span>
<span class="font-label-lg text-label-lg">Inventory</span>
</a>
<a class="flex items-center gap-md px-md py-sm text-on-surface-variant hover:bg-surface-container-highest transition-all rounded-lg active:opacity-80" href="orders.php">
<span class="material-symbols-outlined" data-icon="shopping_cart">shopping_cart</span>
<span class="font-label-lg text-label-lg">Orders</span>
</a>
<a class="flex items-center gap-md px-md py-sm text-on-surface-variant hover:bg-surface-container-highest transition-all rounded-lg active:opacity-80" href="customers.php">
<span class="material-symbols-outlined" data-icon="group">group</span>
<span class="font-label-lg text-label-lg">Customers</span>
</a>
<a class="flex items-center gap-md px-md py-sm text-on-surface-variant hover:bg-surface-container-highest transition-all rounded-lg active:opacity-80" href="pemasukan.php">
<span class="material-symbols-outlined" data-icon="analytics">analytics</span>
<span class="font-label-lg text-label-lg">Analytics</span>
</a>
</div>
<div class="mt-auto flex flex-col gap-xs border-t border-outline-variant pt-md">
<a class="flex items-center gap-md px-md py-sm text-on-surface-variant hover:bg-surface-container-highest transition-all rounded-lg active:opacity-80" href="#">
<span class="material-symbols-outlined" data-icon="settings">settings</span>
<span class="font-label-lg text-label-lg">Settings</span>
</a>
<a class="flex items-center gap-md px-md py-sm text-error hover:bg-error-container transition-all rounded-lg active:opacity-80" href="logout.php">
<span class="material-symbols-outlined" data-icon="logout">logout</span>
<span class="font-label-lg text-label-lg">Logout</span>
</a>
</div>
</nav>
<main class="flex-1 flex flex-col md:ml-[280px] h-screen overflow-hidden">
<header class="bg-surface-container-lowest flex justify-between items-center w-full px-xl h-16 sticky top-0 z-50 border-b border-outline-variant shadow-sm shrink-0">
<button class="md:hidden text-primary hover:bg-surface-container-low p-sm rounded-full transition-colors">
<span class="material-symbols-outlined" data-icon="menu">menu</span>
</button>
<div class="md:hidden font-headline-md text-headline-md font-bold text-primary">
                SmartShop
            </div>
<div class="hidden md:flex items-center gap-sm text-on-surface-variant font-body-md text-body-md">
<span>Inventory</span>
<span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
<span class="text-primary font-medium">Add New Product</span>
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

<?php if(isset($error)): ?>
    <div class="bg-error-container border border-error text-on-error-container p-4 rounded-lg font-body-md text-body-md flex items-start gap-3">
        <span class="material-symbols-outlined shrink-0 mt-0.5 text-error">error</span>
        <?= $error ?>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-lg">
    
    <div class="lg:col-span-3 flex flex-col md:flex-row md:items-center justify-between gap-md mb-sm">
        <div>
            <div class="flex items-center gap-sm mb-1">
                <a href="index.php" class="text-on-surface-variant hover:bg-surface-container-highest p-1 rounded-full transition-colors flex items-center justify-center inline-block">
                    <span class="material-symbols-outlined" data-icon="arrow_back">arrow_back</span>
                </a>
                <h2 class="font-display-lg text-display-lg text-primary">Add New Product</h2>
            </div>
            <p class="font-body-lg text-body-lg text-on-surface-variant ml-10">Enter the details for the new smartphone inventory item.</p>
        </div>
        <div class="flex items-center gap-sm ml-10 md:ml-0">
            <a href="index.php" class="px-lg py-sm rounded-full bg-surface-container-high text-primary font-label-lg text-label-lg hover:bg-surface-container-highest transition-colors inline-block text-center cursor-pointer">Discard</a>
            <button type="submit" name="submit" class="px-lg py-sm rounded-full bg-secondary-container text-on-secondary-container font-label-lg text-label-lg hover:opacity-90 transition-opacity shadow-sm flex items-center gap-xs">
                <span class="material-symbols-outlined text-[18px]" data-icon="save">save</span>
                                        Save Product
            </button>
        </div>
    </div>

    <div class="lg:col-span-2 flex flex-col gap-lg">
        <div class="bg-surface-container-lowest rounded-xl p-lg shadow-sm border border-outline-variant/30 flex flex-col gap-md">
            <h3 class="font-headline-md text-headline-md text-primary border-b border-surface-variant pb-sm mb-sm">Basic Information</h3>
            <div class="flex flex-col gap-xs">
                <label class="font-label-lg text-label-lg text-on-surface" for="product_name">Smartphone Name</label>
                <input required name="nama_hp" class="w-full bg-surface-bright border border-outline-variant rounded-lg px-md py-3 font-body-md text-body-md text-on-background placeholder:text-on-surface-variant/50 focus:border-secondary focus:ring-secondary/20" id="product_name" placeholder="e.g. Samsung Galaxy S24 Ultra" type="text"/>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
                <div class="flex flex-col gap-xs">
                    <label class="font-label-lg text-label-lg text-on-surface" for="brand">Brand</label>
                    <select required name="brand" class="w-full bg-surface-bright border border-outline-variant rounded-lg px-md py-3 font-body-md text-body-md text-on-background focus:border-secondary focus:ring-secondary/20 appearance-none" id="brand">
                        <option disabled="" selected="" value="">Select Brand</option>
                        <option value="Apple">Apple</option>
                        <option value="Samsung">Samsung</option>
                        <option value="Xiaomi">Xiaomi</option>
                        <option value="Oppo">Oppo</option>
                        <option value="Vivo">Vivo</option>
                        <option value="Realme">Realme</option>
                        <option value="Infinix">Infinix</option>
                        <option value="Asus">Asus</option>
                        <option value="Pixel">Pixel</option>
                        <option value="Itel">Itel</option>
                    </select>
                </div>
                <div class="flex flex-col gap-xs">
                    <label class="font-label-lg text-label-lg text-on-surface" for="category">Category</label>
                    <select required name="kategori" class="w-full bg-surface-bright border border-outline-variant rounded-lg px-md py-3 font-body-md text-body-md text-on-background focus:border-secondary focus:ring-secondary/20 appearance-none" id="category">
                        <option value="Android">Android</option>
                        <option value="iPhone">iPhone / iOS</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="bg-surface-container-lowest rounded-xl p-lg shadow-sm border border-outline-variant/30 flex flex-col gap-md">
            <h3 class="font-headline-md text-headline-md text-primary border-b border-surface-variant pb-sm mb-sm">Description</h3>
            <div class="flex flex-col gap-xs">
                <label class="font-label-lg text-label-lg text-on-surface" for="short_desc">Short Description</label>
                <textarea name="deskripsi" class="w-full bg-surface-bright border border-outline-variant rounded-lg px-md py-3 font-body-md text-body-md text-on-background placeholder:text-on-surface-variant/50 focus:border-secondary focus:ring-secondary/20 resize-none" id="short_desc" placeholder="Brief summary of the smartphone's key selling points..." rows="3"></textarea>
            </div>
            <div class="flex flex-col gap-xs">
                <label class="font-label-lg text-label-lg text-on-surface" for="detailed_specs">Detailed Specifications</label>
                <textarea name="spesifikasi" class="w-full bg-surface-bright border border-outline-variant rounded-lg px-md py-3 font-body-md text-body-md text-on-background placeholder:text-on-surface-variant/50 focus:border-secondary focus:ring-secondary/20 resize-none" id="detailed_specs" placeholder="RAM, Storage, Processor, Battery capacity, Camera specs..." rows="6"></textarea>
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
                    <input required name="harga" class="w-full bg-surface-bright border border-outline-variant rounded-lg pl-[40px] pr-md py-3 font-body-md text-body-md text-on-background focus:border-secondary focus:ring-secondary/20" id="price" oninput="formatRupiah(this)" placeholder="0" type="text" inputmode="numeric"/>
                </div>
            </div>
            <div class="flex flex-col gap-xs">
                <label class="font-label-lg text-label-lg text-on-surface" for="stock">Stock Quantity</label>
                <div class="relative flex items-center">
                    <input required name="stok" class="w-full bg-surface-bright border border-outline-variant rounded-lg pl-md pr-[50px] py-3 font-body-md text-body-md text-on-background focus:border-secondary focus:ring-secondary/20 appearance-none" id="stock" placeholder="0" type="number" min="0"/>
                    <span class="absolute right-md font-body-md text-on-surface-variant pointer-events-none">Units</span>
                </div>
            </div>
        </div>
        
        <div class="bg-surface-container-lowest rounded-xl p-lg shadow-sm border border-outline-variant/30 flex flex-col gap-md">
            <h3 class="font-headline-md text-headline-md text-primary border-b border-surface-variant pb-sm mb-sm">Product Images</h3>
            
            <div class="drag-zone border-2 border-dashed border-outline-variant rounded-xl p-xl flex flex-col items-center justify-center text-center cursor-pointer hover:bg-surface-container-low min-h-[160px]" id="drop-zone">
                <span class="material-symbols-outlined text-[48px] text-on-surface-variant/50 mb-sm" data-icon="add_photo_alternate">add_photo_alternate</span>
                <p class="font-label-lg text-label-lg text-primary mb-1">Click to upload or drag and drop</p>
                <p class="font-body-md text-label-md text-on-surface-variant">SVG, PNG, JPG or WEBP (max. 2MB)</p>
                <input name="images[]" accept="image/*" class="hidden" id="file-upload" multiple="" type="file"/>
            </div>
            
            <div id="previewArea" class="grid grid-cols-3 gap-sm mt-sm empty:hidden"></div>
            
        </div>
    </div>
</form>
<div class="h-xl"></div>
</div>
</div>
</main>
<script>
        // Real-time Rupiah Formatting
        function formatRupiah(input) {
            let value = input.value.replace(/[^,\d]/g, '').toString();
            let split = value.split(',');
            let sisa = value.length % 3;
            let rupiah = value.substr(0, sisa);
            let ribuan = value.substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            input.value = rupiah;
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
            
            // Assign dropped files to the file input
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
</body></html>