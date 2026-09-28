<?php
session_start();
// panggil koneksi database dan controller
require_once __DIR__ . '/../../database/koneksi.php';
require_once __DIR__ . '/../../controller/produk.controller.php';
require_once __DIR__ . '/../../controller/user.controller.php';

// cek hak akses: hanya admin/penjual yang boleh buka halaman ini
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php"); // redirect jika belum login
    exit();
}
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'penjual')) {
    header("Location: ../index.php"); // redirect jika bukan role admin
    exit();
}

$produkController = new ProdukController($conn);
$produkModel = $produkController->getModel();

$userController = new UserController($conn);
$userModel = $userController->getModel();

// proses aksi form (tambah menu, edit, hapus, update status pesanan)
// ── PRG Pattern: simpan feedback ke session lalu redirect ──
// Ini mencegah duplikat menu saat user refresh halaman setelah submit form.
$feedback = null;

// Ambil feedback dari session (hasil redirect sebelumnya)
if (isset($_SESSION['feedback'])) {
    $feedback = $_SESSION['feedback'];
    unset($_SESSION['feedback']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $result = null;

    // aksi tambah menu baru (CREATE)
    if ($action === 'create') {
        $result = $produkController->handleCreate($_POST, $_FILES, $_SESSION['user_id'] ?? 1);
    // aksi edit menu (UPDATE)
    } elseif ($action === 'update') {
        $result = $produkController->handleUpdate($_POST, $_FILES);
    // aksi soft delete menu (status=0)
    } elseif ($action === 'delete') {
        $result = $produkController->handleDelete($_POST['id_produk'] ?? 0);
    // aksi restore menu yang dinonaktifkan (status=1)
    } elseif ($action === 'restore') {
        $result = $produkController->handleRestore($_POST['id_produk'] ?? 0);
    // aksi hapus permanen menu (benar-benar hapus dari database)
    } elseif ($action === 'delete_permanent') {
        $result = $produkController->handleDeletePermanent($_POST['id_produk'] ?? 0);
    // aksi ubah status pesanan (diproses / dikirim / selesai)
    } elseif ($action === 'update_status') {
        $result = $produkController->handleUpdateStatusPesanan($_POST['id_pesanan'] ?? 0, $_POST['status'] ?? '');
    // aksi tambah user baru
    } elseif ($action === 'create_user') {
        $result = $userController->handleCreateUser($_POST);
    // aksi edit user
    } elseif ($action === 'update_user') {
        $result = $userController->handleUpdateUser($_POST);
    // aksi hapus user
    } elseif ($action === 'delete_user') {
        $result = $userController->handleDeleteUser(
            $_POST['user_id'] ?? 0,
            $_POST['user_role'] ?? '',
            $_SESSION['user_id'] ?? 0,
            $_SESSION['role'] ?? ''
        );
    }

    // ── Redirect setelah POST (PRG) ──
    // Simpan hasil ke session lalu redirect agar refresh tidak submit ulang
    if ($result !== null) {
        $_SESSION['feedback'] = $result;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// ambil data dari database untuk ditampilkan di dashboard
$stats = $produkModel->getStatistik(); // data statistik total menu, pesanan, omset
$search = $_GET['search'] ?? '';       // kata kunci pencarian
$kategori = $_GET['kategori'] ?? '';   // filter kategori
$menuList = $produkModel->getAllProduk($search, $kategori); // daftar menu produk aktif
$menuTerhapusList = $produkModel->getProdukTerhapus();     // daftar menu dinonaktifkan (status=0)
$pesananList = $produkModel->getAllPesanan();              // daftar pesanan masuk
$pelangganList = $produkModel->getAllPelanggan();          // daftar pelanggan
$userList = $userModel->getAllUsers();                     // daftar akun user

$adminName = $_SESSION['nama'] ?? 'Admin Catering';
$adminEmail = $_SESSION['email'] ?? 'admin@yayubi.com';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin – Dapoer Yayubi Catering</title>
    <link rel="icon" type="image/jpeg" href="../../img/logo_yayubi.jpeg">
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            200: '#fed7aa',
                            300: '#fdba74',
                            400: '#fb923c',
                            500: '#f25019', /* Yayubi signature orange */
                            600: '#ea580c',
                            700: '#c2410c',
                            800: '#9a3412',
                            900: '#7c2d12',
                        },
                        surface: '#fcfaf8',
                        card: '#ffffff'
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    borderRadius: {
                        '2xl': '1.25rem',
                        '3xl': '1.75rem',
                        '4xl': '2.25rem',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #f7f3ee;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1ede8;
        }
        ::-webkit-scrollbar-thumb {
            background: #f25019;
            border-radius: 999px;
        }
        .active-tab-nav {
            background: linear-gradient(135deg, #f25019 0%, #ea580c 100%);
            color: #ffffff !important;
            box-shadow: 0 10px 20px -5px rgba(242, 80, 25, 0.4);
        }
        .active-tab-nav i {
            color: #ffffff !important;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(254, 215, 170, 0.4);
        }
    </style>
</head>
<body class="min-h-screen text-slate-800 antialiased flex flex-col lg:flex-row p-3 md:p-6 gap-6">

    <!-- SIDEBAR (Dropify Style) -->
    <aside class="w-full lg:w-72 bg-white rounded-3xl p-6 shadow-sm border border-brand-100/60 flex flex-col justify-between shrink-0">
        <div>
            <!-- Brand / Logo -->
            <div class="flex items-center gap-3.5 pb-8 border-b border-orange-50">
                <div class="w-12 h-12 rounded-2xl overflow-hidden bg-white shadow-md shadow-brand-500/10 border border-brand-100 flex items-center justify-center p-0.5 shrink-0">
                    <img src="../../img/logo_yayubi.jpeg" alt="Logo Dapoer Yayubi" class="w-full h-full object-cover rounded-xl">
                </div>
                <div>
                    <h1 class="font-extrabold text-xl tracking-tight text-slate-900 leading-none">Dapoer Yayubi</h1>
                    <span class="text-xs font-semibold text-brand-600 tracking-wider uppercase mt-1 inline-block">Admin Catering</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="mt-8 space-y-2" id="sidebar-nav">
                <button onclick="switchTab('tab-dashboard')" id="nav-dashboard" class="nav-btn active-tab-nav w-full flex items-center gap-3.5 px-4 py-3.5 rounded-2xl font-semibold text-sm transition-all duration-200">
                    <i class="fa-solid fa-chart-pie text-base w-5 text-center"></i>
                    <span>Dashboard</span>
                </button>
                <button onclick="switchTab('tab-menu')" id="nav-menu" class="nav-btn w-full flex items-center gap-3.5 px-4 py-3.5 rounded-2xl font-semibold text-sm text-slate-600 hover:bg-brand-50 hover:text-brand-600 transition-all duration-200">
                    <i class="fa-solid fa-bowl-food text-base w-5 text-center text-slate-400"></i>
                    <span>Menu Catering</span>
                    <span class="ml-auto bg-brand-100 text-brand-700 text-xs px-2.5 py-0.5 rounded-full font-bold"><?php echo $stats['total_menu']; ?></span>
                </button>
                <button onclick="switchTab('tab-terhapus')" id="nav-terhapus" class="nav-btn w-full flex items-center gap-3.5 px-4 py-3.5 rounded-2xl font-semibold text-sm text-slate-600 hover:bg-rose-50 hover:text-rose-600 transition-all duration-200">
                    <i class="fa-solid fa-trash-can-arrow-up text-base w-5 text-center text-slate-400"></i>
                    <span>Menu Terhapus</span>
                    <?php if (count($menuTerhapusList) > 0): ?>
                        <span class="ml-auto bg-rose-100 text-rose-700 text-xs px-2.5 py-0.5 rounded-full font-bold"><?php echo count($menuTerhapusList); ?></span>
                    <?php endif; ?>
                </button>
                <button onclick="switchTab('tab-pesanan')" id="nav-pesanan" class="nav-btn w-full flex items-center gap-3.5 px-4 py-3.5 rounded-2xl font-semibold text-sm text-slate-600 hover:bg-brand-50 hover:text-brand-600 transition-all duration-200">
                    <i class="fa-solid fa-receipt text-base w-5 text-center text-slate-400"></i>
                    <span>Pesanan Masuk</span>
                    <?php if ($stats['pesanan_pending'] > 0): ?>
                        <span class="ml-auto bg-amber-500 text-white text-xs px-2 py-0.5 rounded-full font-bold animate-pulse"><?php echo $stats['pesanan_pending']; ?></span>
                    <?php endif; ?>
                </button>
                <button onclick="switchTab('tab-pelanggan')" id="nav-pelanggan" class="nav-btn w-full flex items-center gap-3.5 px-4 py-3.5 rounded-2xl font-semibold text-sm text-slate-600 hover:bg-brand-50 hover:text-brand-600 transition-all duration-200">
                    <i class="fa-solid fa-users-gear text-base w-5 text-center text-slate-400"></i>
                    <span>Kelola Akun User</span>
                    <span class="ml-auto bg-slate-100 text-slate-700 text-xs px-2.5 py-0.5 rounded-full font-bold"><?php echo count($userList); ?></span>
                </button>
            </nav>
        </div>

        <!-- Admin Profile & Logout Card -->
        <div class="mt-8 pt-6 border-t border-orange-50">
            <div class="flex items-center gap-3 bg-brand-50/60 p-3 rounded-2xl border border-brand-100">
                <div class="w-10 h-10 rounded-xl overflow-hidden bg-white border border-brand-200 shadow-sm shrink-0 flex items-center justify-center p-0.5">
                    <img src="../../img/logo_yayubi.jpeg" alt="Admin Dapoer Yayubi" class="w-full h-full object-cover rounded-lg">
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-slate-900 truncate"><?php echo htmlspecialchars($adminName); ?></p>
                    <p class="text-xs text-slate-500 truncate"><?php echo htmlspecialchars($adminEmail); ?></p>
                </div>
                <button onclick="confirmLogout()" title="Keluar / Logout" class="w-9 h-9 rounded-xl bg-white text-rose-500 hover:bg-rose-50 hover:text-rose-600 flex items-center justify-center border border-slate-200 transition-all shadow-xs">
                    <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i>
                </button>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT AREA -->
    <main class="flex-1 flex flex-col gap-6 overflow-hidden">
        
        <!-- TOP HEADER (Welcome, Search, Action) -->
        <header class="bg-white rounded-3xl p-5 md:p-6 shadow-sm border border-brand-100/60 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    Selamat Datang, <?php echo htmlspecialchars($adminName); ?>! <span class="inline-block animate-bounce">👋</span>
                </h2>
                <p class="text-xs md:text-sm text-slate-500 font-medium mt-1">
                    Kelola menu makanan catering dan pantau pesanan pelanggan dengan mudah.
                </p>
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <button onclick="location.reload()" class="flex items-center justify-center gap-2 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 px-4 py-3 rounded-2xl font-bold text-xs border border-emerald-200 shadow-sm transition">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <i class="fa-solid fa-rotate text-xs"></i>
                    <span>Live Order Sync</span>
                </button>
                <button onclick="openModalTambah()" class="w-full md:w-auto flex items-center justify-center gap-2.5 bg-gradient-to-r from-brand-500 to-brand-600 hover:from-brand-600 hover:to-brand-700 text-white px-5 py-3 rounded-2xl font-bold text-sm shadow-lg shadow-brand-500/25 transition-all duration-200 transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-plus text-sm"></i>
                    <span>Tambah Menu Baru</span>
                </button>
            </div>
        </header>

        <!-- TAB 1: DASHBOARD OVERVIEW -->
        <section id="tab-dashboard" class="tab-content space-y-6">
            <!-- 4 METRIC / KPI CARDS (Dropify Style) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
                <!-- Card 1: Total Menu -->
                <div class="bg-white rounded-3xl p-5 border border-brand-100/60 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Menu Catering</span>
                        <div class="w-11 h-11 rounded-2xl bg-orange-50 text-brand-500 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-utensils"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-3xl font-extrabold text-slate-900 tracking-tight"><?php echo $stats['total_menu']; ?></span>
                        <span class="text-xs font-semibold text-emerald-600 ml-2 bg-emerald-50 px-2 py-0.5 rounded-full">
                            <i class="fa-solid fa-check"></i> Siap Jual
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-2">Katalog menu aktif di website</p>
                </div>

                <!-- Card 2: Total Pesanan -->
                <div class="bg-white rounded-3xl p-5 border border-brand-100/60 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Pesanan</span>
                        <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-cart-shopping"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-3xl font-extrabold text-slate-900 tracking-tight"><?php echo $stats['total_pesanan']; ?></span>
                        <span class="text-xs font-semibold text-blue-600 ml-2 bg-blue-50 px-2 py-0.5 rounded-full">
                            <i class="fa-solid fa-arrow-trend-up"></i> Transaksi
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-2">Seluruh pesanan tercatat</p>
                </div>

                <!-- Card 3: Total Omset / Pendapatan -->
                <div class="bg-white rounded-3xl p-5 border border-brand-100/60 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Omset</span>
                        <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-extrabold text-emerald-600 tracking-tight">Rp <?php echo number_format($stats['total_omset'], 0, ',', '.'); ?></span>
                    </div>
                    <p class="text-xs text-slate-400 mt-2">Pendapatan pesanan valid</p>
                </div>

                <!-- Card 4: Pelanggan Terdaftar -->
                <div class="bg-white rounded-3xl p-5 border border-brand-100/60 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pelanggan Aktif</span>
                        <div class="w-11 h-11 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-user-group"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-3xl font-extrabold text-slate-900 tracking-tight"><?php echo $stats['total_pelanggan']; ?></span>
                        <span class="text-xs font-semibold text-purple-600 ml-2 bg-purple-50 px-2 py-0.5 rounded-full">
                            Akun
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-2">Pengguna web terdaftar</p>
                </div>
            </div>

            <!-- DUA KOLOM: Quick CRUD Menu Preview & Pesanan Terbaru -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Kolom Kiri: Pesanan Terbaru (2 spans) -->
                <div class="lg:col-span-2 bg-white rounded-3xl p-6 border border-brand-100/60 shadow-sm">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h3 class="font-extrabold text-lg text-slate-900">Pesanan Masuk Terbaru</h3>
                            <p class="text-xs text-slate-500">Daftar transaksi pesanan catering terakhir</p>
                        </div>
                        <button onclick="switchTab('tab-pesanan')" class="text-xs font-bold text-brand-600 hover:text-brand-700 bg-brand-50 px-3 py-1.5 rounded-xl transition">
                            Lihat Semua →
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="text-xs uppercase text-slate-400 border-b border-slate-100 font-bold">
                                <tr>
                                    <th class="pb-3">Pemesan</th>
                                    <th class="pb-3">Menu</th>
                                    <th class="pb-3">Total</th>
                                    <th class="pb-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if (empty($pesananList)): ?>
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-slate-400 text-xs">Belum ada pesanan masuk.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach (array_slice($pesananList, 0, 4) as $p): ?>
                                        <tr class="hover:bg-slate-50/60 transition">
                                            <td class="py-3.5 font-bold text-slate-900">
                                                <?php echo htmlspecialchars($p['nama_pembeli'] ?? 'Pelanggan'); ?>
                                                <div class="text-xs font-normal text-slate-400"><?php echo date('d M Y, H:i', strtotime($p['tanggal_pesanan'])); ?></div>
                                            </td>
                                            <td class="py-3.5 text-slate-600">
                                                <?php 
                                                    $menuDipilih = $p['nama_produk'] ?? 'Menu Catering';
                                                    if (!empty($p['catatan']) && preg_match('/Menu:\s*([^|]+)/i', $p['catatan'], $mMatch)) {
                                                        $menuDipilih = trim($mMatch[1]);
                                                    }
                                                    echo htmlspecialchars($menuDipilih);
                                                ?> 
                                                <span class="text-xs font-bold text-brand-600">(<?php echo $p['jumlah']; ?> item)</span>
                                            </td>
                                            <td class="py-3.5 font-bold text-slate-900">
                                                Rp <?php echo number_format($p['total_harga'], 0, ',', '.'); ?>
                                                <?php if ((!empty($p['status_pembayaran']) && $p['status_pembayaran'] === 'Lunas') || $p['status'] === 'Dibayar'): ?>
                                                    <div class="text-[10px] font-bold text-emerald-600 mt-0.5"><i class="fa-solid fa-qrcode"></i> QRIS Lunas</div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-3.5">
                                                <?php 
                                                    $st = $p['status'];
                                                    $badgeColor = 'bg-amber-50 text-amber-600 border-amber-200';
                                                    if ($st === 'Dibayar') $badgeColor = 'bg-emerald-50 text-emerald-600 border-emerald-200';
                                                    if ($st === 'Diproses') $badgeColor = 'bg-blue-50 text-blue-600 border-blue-200';
                                                    if ($st === 'Selesai') $badgeColor = 'bg-emerald-50 text-emerald-600 border-emerald-200';
                                                    if ($st === 'Batal') $badgeColor = 'bg-rose-50 text-rose-600 border-rose-200';
                                                ?>
                                                <span class="inline-block px-2.5 py-1 rounded-xl text-xs font-bold border <?php echo $badgeColor; ?>">
                                                    <?php echo $st; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Kolom Kanan: Highlight Kategori Catering -->
                <div class="bg-white rounded-3xl p-6 border border-brand-100/60 shadow-sm flex flex-col justify-between">
                    <div>
                        <h3 class="font-extrabold text-lg text-slate-900 mb-1">Kategori Catering</h3>
                        <p class="text-xs text-slate-500 mb-4">Porsi dan varian yang tersedia</p>

                        <div class="space-y-3">
                            <?php
                                $categories = [
                                    ['name' => 'Nasi Box', 'icon' => 'fa-box', 'color' => 'text-amber-500 bg-amber-50'],
                                    ['name' => 'Tumpeng', 'icon' => 'fa-mountain', 'color' => 'text-brand-500 bg-brand-50'],
                                    ['name' => 'Snack Box', 'icon' => 'fa-cookie-bite', 'color' => 'text-rose-500 bg-rose-50'],
                                ];
                                foreach ($categories as $cat):
                                    $cnt = count(array_filter($menuList, function($m) use ($cat) {
                                        return strtolower($m['kategori']) === strtolower($cat['name']);
                                    }));
                            ?>
                                <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100 hover:border-brand-200 transition">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl <?php echo $cat['color']; ?> flex items-center justify-center text-sm font-bold">
                                            <i class="fa-solid <?php echo $cat['icon']; ?>"></i>
                                        </div>
                                        <span class="text-sm font-bold text-slate-800"><?php echo $cat['name']; ?></span>
                                    </div>
                                    <span class="text-xs font-bold text-slate-500 bg-white px-2.5 py-1 rounded-lg border border-slate-200">
                                        <?php echo $cnt; ?> Menu
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mt-6 pt-5 border-t border-slate-100">
                        <button onclick="switchTab('tab-menu')" class="w-full py-3 bg-brand-50 hover:bg-brand-100 text-brand-600 rounded-2xl font-bold text-xs transition">
                            Kelola Semua Menu di Katalog →
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- TAB 2: KELOLA MENU (FULL CRUD) -->
        <section id="tab-menu" class="tab-content hidden space-y-6">
            <div class="bg-white rounded-3xl p-6 border border-brand-100/60 shadow-sm">
                
                <!-- Filter & Search Bar -->
                <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                    <div>
                        <h3 class="text-xl font-extrabold text-slate-900">Menu Catering</h3>
                        <p class="text-xs text-slate-500">Kelola katalog menu catering: Nasi Box, Tumpeng, dan Snack Box</p>
                    </div>

                    <!-- Search Input & Add Button -->
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative flex-1 md:w-64">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="text" id="searchInput" onkeyup="filterMenuTable()" placeholder="Cari nama menu..." class="w-full pl-10 pr-4 py-2.5 rounded-2xl border border-slate-200 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition">
                        </div>
                        <button onclick="openModalTambah()" class="flex items-center gap-2 bg-brand-500 hover:bg-brand-600 text-white px-4 py-2.5 rounded-2xl font-bold text-sm shadow-md shadow-brand-500/20 transition">
                            <i class="fa-solid fa-plus text-xs"></i>
                            <span>Tambah Menu</span>
                        </button>
                    </div>
                </div>

                <!-- Category Filter Buttons -->
                <div class="flex items-center gap-2 overflow-x-auto py-4">
                    <button onclick="filterKategori('Semua')" class="cat-pill bg-brand-500 text-white px-4 py-2 rounded-xl text-xs font-bold transition">Semua</button>
                    <button onclick="filterKategori('Nasi Box')" class="cat-pill bg-slate-100 text-slate-600 hover:bg-slate-200 px-4 py-2 rounded-xl text-xs font-bold transition">Nasi Box</button>
                    <button onclick="filterKategori('Tumpeng')" class="cat-pill bg-slate-100 text-slate-600 hover:bg-slate-200 px-4 py-2 rounded-xl text-xs font-bold transition">Tumpeng</button>
                    <button onclick="filterKategori('Snack Box')" class="cat-pill bg-slate-100 text-slate-600 hover:bg-slate-200 px-4 py-2 rounded-xl text-xs font-bold transition">Snack Box</button>
                </div>

                <!-- CRUD Table -->
                <div class="overflow-x-auto mt-2">
                    <table class="w-full text-left text-sm border-collapse" id="menuTable">
                        <thead>
                            <tr class="text-xs uppercase text-slate-400 font-bold border-b border-slate-200">
                                <th class="pb-3 px-3">Foto & Menu</th>
                                <th class="pb-3 px-3">Kategori</th>
                                <th class="pb-3 px-3">Harga</th>
                                <th class="pb-3 px-3">Stok Porsi</th>
                                <th class="pb-3 px-3">Deskripsi</th>
                                <th class="pb-3 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($menuList)): ?>
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-slate-400">
                                        <i class="fa-solid fa-bowl-rice text-4xl mb-2 text-slate-300 block"></i>
                                        Belum ada menu catering. Silakan klik <strong>+ Tambah Menu</strong>!
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($menuList as $m): ?>
                                    <tr class="menu-row hover:bg-brand-50/30 transition duration-150" 
                                        data-kategori="<?php echo htmlspecialchars($m['kategori']); ?>"
                                        data-nama="<?php echo strtolower(htmlspecialchars($m['nama_produk'])); ?>">
                                        
                                        <!-- Foto & Nama Menu -->
                                        <td class="py-4 px-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-14 h-14 rounded-2xl overflow-hidden bg-slate-100 border border-slate-200 shrink-0 shadow-xs">
                                                    <?php 
                                                        $gambarUrl = "../../uploads/" . (!empty($m['gambar']) ? $m['gambar'] : 'default.png');
                                                    ?>
                                                    <img src="<?php echo htmlspecialchars($gambarUrl); ?>" 
                                                         alt="<?php echo htmlspecialchars($m['nama_produk']); ?>"
                                                         class="w-full h-full object-cover"
                                                         onerror="this.src='../../img/tumpeng.png';">
                                                </div>
                                                <div>
                                                    <span class="font-extrabold text-slate-900 text-sm block"><?php echo htmlspecialchars($m['nama_produk']); ?></span>
                                                    <span class="text-xs text-slate-400">ID: #MNU-<?php echo str_pad($m['id_produk'], 3, '0', STR_PAD_LEFT); ?></span>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Kategori -->
                                        <td class="py-4 px-3">
                                            <span class="bg-orange-100 text-brand-700 text-xs font-extrabold px-3 py-1 rounded-xl">
                                                <?php echo htmlspecialchars($m['kategori']); ?>
                                            </span>
                                        </td>

                                        <!-- Harga -->
                                        <td class="py-4 px-3 font-extrabold text-slate-900">
                                            Rp <?php echo number_format($m['harga'], 0, ',', '.'); ?>
                                        </td>

                                        <!-- Stok -->
                                        <td class="py-4 px-3">
                                            <?php if ($m['stok'] > 10): ?>
                                                <span class="bg-emerald-50 text-emerald-600 text-xs font-bold px-2.5 py-1 rounded-lg">
                                                    <?php echo $m['stok']; ?> porsi
                                                </span>
                                            <?php elseif ($m['stok'] > 0): ?>
                                                <span class="bg-amber-50 text-amber-600 text-xs font-bold px-2.5 py-1 rounded-lg">
                                                    Sisa <?php echo $m['stok']; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="bg-rose-50 text-rose-600 text-xs font-bold px-2.5 py-1 rounded-lg">
                                                    Habis
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Deskripsi -->
                                        <td class="py-4 px-3 text-xs text-slate-500 max-w-xs truncate" title="<?php echo htmlspecialchars($m['deskripsi']); ?>">
                                            <?php echo htmlspecialchars($m['deskripsi'] ?: '-'); ?>
                                        </td>

                                        <!-- Tombol Aksi (UPDATE & DELETE) -->
                                        <td class="py-4 px-3 text-right">
                                            <div class="inline-flex items-center gap-1.5">
                                                <!-- Edit Button -->
                                                <button onclick='openModalEdit(<?php echo json_encode($m); ?>)' class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 hover:bg-brand-50 hover:text-brand-600 flex items-center justify-center transition" title="Edit Menu">
                                                    <i class="fa-solid fa-pen-to-square text-sm"></i>
                                                </button>
                                                <!-- Delete Button -->
                                                <button onclick="confirmDelete(<?php echo $m['id_produk']; ?>, '<?php echo addslashes($m['nama_produk']); ?>')" class="w-9 h-9 rounded-xl bg-slate-100 text-rose-500 hover:bg-rose-50 hover:text-rose-600 flex items-center justify-center transition" title="Hapus Menu">
                                                    <i class="fa-solid fa-trash text-sm"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- TAB 3: DATA PESANAN -->
        <section id="tab-pesanan" class="tab-content hidden space-y-6">
            <div class="bg-white rounded-3xl p-6 border border-brand-100/60 shadow-sm">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                    <div>
                        <h3 class="text-xl font-extrabold text-slate-900">Pesanan Masuk Catering</h3>
                        <p class="text-xs text-slate-500">Ubah status pesanan (Pending, Diproses, Selesai, Batal) dan pantau pembayaran realtime</p>
                    </div>
                </div>

                <div class="overflow-x-auto mt-4">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="text-xs uppercase text-slate-400 font-bold border-b border-slate-200">
                                <th class="pb-3 px-3">No. Order</th>
                                <th class="pb-3 px-3">Pelanggan</th>
                                <th class="pb-3 px-3">Menu Dipesan</th>
                                <th class="pb-3 px-3">Jumlah & Total</th>
                                <th class="pb-3 px-3">Catatan / Alamat</th>
                                <th class="pb-3 px-3">Status</th>
                                <th class="pb-3 px-3 text-right">Aksi Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($pesananList)): ?>
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-400 text-xs">Belum ada pesanan yang masuk.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pesananList as $ord): ?>
                                    <tr class="hover:bg-brand-50/20 transition">
                                        <td class="py-4 px-3 font-extrabold text-brand-600">
                                            #ORD-<?php echo str_pad($ord['id_pesanan'], 4, '0', STR_PAD_LEFT); ?>
                                            <div class="text-[11px] font-normal text-slate-400"><?php echo date('d/m/Y H:i', strtotime($ord['tanggal_pesanan'])); ?></div>
                                        </td>
                                        <td class="py-4 px-3">
                                            <span class="font-bold text-slate-900 block"><?php echo htmlspecialchars($ord['nama_pembeli'] ?? 'Tamu'); ?></span>
                                            <span class="text-xs text-slate-400"><?php echo htmlspecialchars($ord['no_hp_pembeli'] ?? '-'); ?></span>
                                        </td>
                                        <td class="py-4 px-3 font-semibold text-slate-800">
                                            <?php 
                                                $menuDipilih = $ord['nama_produk'] ?? 'Menu Catering';
                                                if (!empty($ord['catatan']) && preg_match('/Menu:\s*([^|]+)/i', $ord['catatan'], $mMatch)) {
                                                    $menuDipilih = trim($mMatch[1]);
                                                }
                                                echo htmlspecialchars($menuDipilih);
                                            ?>
                                        </td>
                                        <td class="py-4 px-3">
                                            <div class="font-extrabold text-slate-900">Rp <?php echo number_format($ord['total_harga'], 0, ',', '.'); ?></div>
                                            <div class="text-xs text-slate-500"><?php echo $ord['jumlah']; ?> item</div>
                                            <?php if ((!empty($ord['status_pembayaran']) && $ord['status_pembayaran'] === 'Lunas') || $ord['status'] === 'Dibayar'): ?>
                                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-100/70 px-2 py-0.5 rounded-md mt-1">
                                                    <i class="fa-solid fa-qrcode text-[10px]"></i> QRIS Lunas
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-700 bg-amber-100/60 px-2 py-0.5 rounded-md mt-1">
                                                    <i class="fa-regular fa-clock text-[10px]"></i> Belum Bayar
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-3 text-xs text-slate-500 max-w-xs">
                                            <?php echo htmlspecialchars($ord['catatan'] ?: ($ord['alamat_pembeli'] ?? '-')); ?>
                                        </td>
                                        <td class="py-4 px-3">
                                            <?php 
                                                $st = $ord['status'];
                                                $badgeClass = 'bg-amber-50 text-amber-600 border-amber-200';
                                                if ($st === 'Dibayar') $badgeClass = 'bg-emerald-50 text-emerald-600 border-emerald-200';
                                                if ($st === 'Diproses') $badgeClass = 'bg-blue-50 text-blue-600 border-blue-200';
                                                if ($st === 'Selesai') $badgeClass = 'bg-emerald-50 text-emerald-600 border-emerald-200';
                                                if ($st === 'Batal') $badgeClass = 'bg-rose-50 text-rose-600 border-rose-200';
                                            ?>
                                            <span class="inline-block px-3 py-1 rounded-xl text-xs font-bold border <?php echo $badgeClass; ?>">
                                                <?php echo $st; ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-3 text-right">
                                            <div class="inline-flex items-center gap-2">
                                                <?php 
                                                    // siapkan nomor whatsapp pembeli
                                                    $rawHp = preg_replace('/[^0-9]/', '', $ord['no_hp_pembeli'] ?? '');
                                                    if (empty($rawHp) && !empty($ord['catatan'])) {
                                                        if (preg_match('/(?:no_hp|telp|wa|hp)[:=\s]*([0-9\+]+)/i', $ord['catatan'], $hpMatch)) {
                                                            $rawHp = preg_replace('/[^0-9]/', '', $hpMatch[1]);
                                                        }
                                                    }
                                                    $waLink = null;
                                                    if (!empty($rawHp)) {
                                                        if (substr($rawHp, 0, 1) === '0') {
                                                            $cleanHp = '62' . substr($rawHp, 1);
                                                        } elseif (substr($rawHp, 0, 2) === '62') {
                                                            $cleanHp = $rawHp;
                                                        } else {
                                                            $cleanHp = '62' . $rawHp;
                                                        }
                                                        $waText = "Halo Kak *" . ($ord['nama_pembeli'] ?? 'Pelanggan') . "*,\n\nTerima kasih telah memesan catering di *Dapoer Yayubi*! 🍱\n\n📌 *Detail Pesanan #ORD-" . str_pad($ord['id_pesanan'], 4, '0', STR_PAD_LEFT) . ":*\n• Menu: " . $menuDipilih . "\n• Jumlah: " . $ord['jumlah'] . " porsi\n• Total: Rp " . number_format($ord['total_harga'], 0, ',', '.') . "\n• Status: " . $ord['status'] . "\n• Alamat Kirim: " . ($ord['alamat_pembeli'] ?? '-') . "\n\nPesanan Kakak saat ini sedang kami siapkan di dapur ya. Estimasi pengiriman sesuai jadwal yang Kakak pilih. Terima kasih banyak! 🙏😊";
                                                        $waLink = "https://wa.me/{$cleanHp}?text=" . urlencode($waText);
                                                    }
                                                ?>
                                                <?php if ($waLink): ?>
                                                    <a href="<?php echo $waLink; ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-sm" title="Hubungi Pembeli via WhatsApp">
                                                        <i class="fa-brands fa-whatsapp text-sm"></i> WA Pembeli
                                                    </a>
                                                <?php endif; ?>
                                                <!-- Form Ubah Status Cepat -->
                                                <form action="" method="post" class="inline-flex items-center gap-2">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="id_pesanan" value="<?php echo $ord['id_pesanan']; ?>">
                                                    <select name="status" onchange="this.form.submit()" class="text-xs font-bold py-1.5 px-2.5 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-brand-500 cursor-pointer">
                                                        <option value="Pending" <?php echo $ord['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="Dibayar" <?php echo $ord['status'] === 'Dibayar' ? 'selected' : ''; ?>>Dibayar (QRIS)</option>
                                                        <option value="Diproses" <?php echo $ord['status'] === 'Diproses' ? 'selected' : ''; ?>>Diproses</option>
                                                        <option value="Selesai" <?php echo $ord['status'] === 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                                                        <option value="Batal" <?php echo $ord['status'] === 'Batal' ? 'selected' : ''; ?>>Batal</option>
                                                    </select>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- TAB 4: KELOLA AKUN USER -->
        <section id="tab-pelanggan" class="tab-content hidden space-y-6">
            <div class="bg-white rounded-3xl p-6 border border-brand-100/60 shadow-sm">
                <!-- Header with Action -->
                <div class="pb-6 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-extrabold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-users-gear text-brand-500"></i>
                            Kelola Akun Pengguna
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">Manajemen akun yang dapat login ke website (Admin Catering & Pelanggan)</p>
                    </div>
                    <button onclick="openModalTambahUser()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold shadow-md shadow-brand-500/20 transition cursor-pointer">
                        <i class="fa-solid fa-user-plus text-sm"></i>
                        <span>Tambah User Baru</span>
                    </button>
                </div>

                <!-- Filters & Search Bar -->
                <div class="mt-6 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                    <!-- Role Filter Pills -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <?php
                            $totalAdmin = count(array_filter($userList, fn($u) => $u['role'] === 'admin'));
                            $totalPembeli = count(array_filter($userList, fn($u) => $u['role'] === 'pembeli'));
                        ?>
                        <button type="button" onclick="filterUserRole('all')" data-role="all" class="user-role-pill bg-brand-500 text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm">
                            Semua (<?php echo count($userList); ?>)
                        </button>
                        <button type="button" onclick="filterUserRole('admin')" data-role="admin" class="user-role-pill bg-slate-100 text-slate-600 hover:bg-slate-200 px-4 py-2 rounded-xl text-xs font-bold transition">
                            <i class="fa-solid fa-shield-halved mr-1 text-purple-500"></i> Admin (<?php echo $totalAdmin; ?>)
                        </button>
                        <button type="button" onclick="filterUserRole('pembeli')" data-role="pembeli" class="user-role-pill bg-slate-100 text-slate-600 hover:bg-slate-200 px-4 py-2 rounded-xl text-xs font-bold transition">
                            <i class="fa-solid fa-user mr-1 text-blue-500"></i> Pelanggan (<?php echo $totalPembeli; ?>)
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="relative w-full md:w-80">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" id="searchUserInput" oninput="filterUserTable()" placeholder="Cari nama, email, atau no HP..." class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 bg-slate-50/50">
                    </div>
                </div>

                <!-- Users Table -->
                <div class="overflow-x-auto mt-6">
                    <table class="w-full text-left text-sm border-collapse" id="userTable">
                        <thead>
                            <tr class="text-xs uppercase text-slate-400 font-bold border-b border-slate-200">
                                <th class="pb-3 px-3">Nama & Pengguna</th>
                                <th class="pb-3 px-3">Role Akun</th>
                                <th class="pb-3 px-3">Kontak</th>
                                <th class="pb-3 px-3">Alamat</th>
                                <th class="pb-3 px-3">Aktivitas / Order</th>
                                <th class="pb-3 px-3">Terdaftar</th>
                                <th class="pb-3 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($userList)): ?>
                                <tr id="userEmptyRow">
                                    <td colspan="7" class="py-8 text-center text-slate-400 text-xs">Belum ada akun pengguna terdaftar.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($userList as $u): ?>
                                    <?php 
                                        $isCurrent = ($u['role'] === 'admin' && $u['id'] == ($_SESSION['user_id'] ?? 0));
                                        $searchString = strtolower($u['nama'] . ' ' . $u['email'] . ' ' . ($u['no_hp'] ?? '') . ' ' . $u['role']);
                                        $initials = strtoupper(substr($u['nama'], 0, 2));
                                    ?>
                                    <tr class="user-row hover:bg-brand-50/20 transition" data-role="<?php echo $u['role']; ?>" data-search="<?php echo htmlspecialchars($searchString); ?>">
                                        <!-- Nama & Avatar -->
                                        <td class="py-4 px-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-2xl <?php echo $u['role'] === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-brand-100 text-brand-700'; ?> flex items-center justify-center font-extrabold text-xs shrink-0 shadow-xs">
                                                    <?php echo $initials; ?>
                                                </div>
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-bold text-slate-900"><?php echo htmlspecialchars($u['nama']); ?></span>
                                                        <?php if ($isCurrent): ?>
                                                            <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-full">Akun Anda</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <span class="text-xs text-slate-400 block"><?php echo htmlspecialchars($u['email']); ?></span>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Role Badge -->
                                        <td class="py-4 px-3">
                                            <?php if ($u['role'] === 'admin'): ?>
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                                    <i class="fa-solid fa-shield-halved text-[10px]"></i> Admin / Penjual
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                    <i class="fa-solid fa-user text-[10px]"></i> Pembeli / Customer
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Kontak -->
                                        <td class="py-4 px-3">
                                            <div class="text-xs text-slate-700 font-medium">
                                                <?php if (!empty($u['no_hp'])): ?>
                                                    <span class="flex items-center gap-1.5">
                                                        <i class="fa-brands fa-whatsapp text-emerald-500"></i>
                                                        <?php echo htmlspecialchars($u['no_hp']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-slate-400 italic">Belum ada no HP</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>

                                        <!-- Alamat -->
                                        <td class="py-4 px-3 text-xs text-slate-500 max-w-xs">
                                            <?php echo !empty($u['alamat']) ? htmlspecialchars($u['alamat']) : '<span class="text-slate-400 italic">-</span>'; ?>
                                        </td>

                                        <!-- Aktivitas / Order -->
                                        <td class="py-4 px-3">
                                            <?php if ($u['role'] === 'pembeli'): ?>
                                                <div class="text-xs font-bold text-brand-600"><?php echo $u['jumlah_order'] ?? 0; ?> Pesanan</div>
                                                <div class="text-[11px] font-semibold text-slate-500">Rp <?php echo number_format($u['total_belanja'] ?? 0, 0, ',', '.'); ?></div>
                                            <?php else: ?>
                                                <span class="text-xs font-semibold text-slate-400">Pengelola Web</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Terdaftar -->
                                        <td class="py-4 px-3 text-xs text-slate-400">
                                            <?php echo !empty($u['created_at']) ? date('d/m/Y', strtotime($u['created_at'])) : '-'; ?>
                                        </td>

                                        <!-- Aksi -->
                                        <td class="py-4 px-3 text-right">
                                            <div class="inline-flex items-center gap-1.5">
                                                <!-- Edit Button -->
                                                <button type="button" 
                                                    onclick='openModalEditUser(<?php echo json_encode($u, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)' 
                                                    title="Edit Akun" 
                                                    class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-100 flex items-center justify-center transition cursor-pointer">
                                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                                </button>

                                                <!-- Delete Button -->
                                                <?php if ($isCurrent): ?>
                                                    <button type="button" disabled title="Tidak dapat menghapus akun yang sedang aktif" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-300 flex items-center justify-center cursor-not-allowed">
                                                        <i class="fa-solid fa-lock text-xs"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" 
                                                        onclick="confirmDeleteUser(<?php echo $u['id']; ?>, '<?php echo $u['role']; ?>', '<?php echo addslashes(htmlspecialchars($u['nama'])); ?>')" 
                                                        title="Hapus Akun" 
                                                        class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center transition cursor-pointer">
                                                        <i class="fa-solid fa-trash text-xs"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- TAB 5: MENU TERHAPUS / RECYCLE BIN -->
        <section id="tab-terhapus" class="tab-content hidden space-y-6">
            <div class="bg-white rounded-3xl p-6 border border-rose-100/60 shadow-sm">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                    <div>
                        <h3 class="text-xl font-extrabold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-trash-can-arrow-up text-rose-500"></i>
                            Menu Terhapus (Recycle Bin)
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">
                            Menu yang dinonaktifkan. Status = <span class="font-bold text-rose-600">0 (tidak tampil)</span>. 
                            Restore untuk mengaktifkan kembali (status = <span class="font-bold text-emerald-600">1</span>).
                        </p>
                    </div>
                    <div class="flex items-center gap-2 bg-rose-50 border border-rose-200 rounded-2xl px-4 py-2">
                        <i class="fa-solid fa-circle-info text-rose-400 text-xs"></i>
                        <span class="text-xs font-semibold text-rose-700"><?php echo count($menuTerhapusList); ?> Menu Nonaktif</span>
                    </div>
                </div>

                <div class="overflow-x-auto mt-4">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="text-xs uppercase text-slate-400 font-bold border-b border-slate-200">
                                <th class="pb-3 px-3">Foto & Menu</th>
                                <th class="pb-3 px-3">Kategori</th>
                                <th class="pb-3 px-3">Harga</th>
                                <th class="pb-3 px-3">Stok</th>
                                <th class="pb-3 px-3">Status</th>
                                <th class="pb-3 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($menuTerhapusList)): ?>
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-400">
                                        <i class="fa-solid fa-circle-check text-4xl mb-3 text-emerald-300 block"></i>
                                        <span class="font-semibold text-sm">Recycle bin kosong!</span>
                                        <p class="text-xs mt-1">Semua menu sedang aktif dan tampil di website.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($menuTerhapusList as $mt): ?>
                                    <tr class="hover:bg-rose-50/30 transition duration-150 opacity-80">
                                        <!-- Foto & Nama Menu -->
                                        <td class="py-4 px-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-14 h-14 rounded-2xl overflow-hidden bg-slate-100 border border-rose-200 shrink-0 shadow-xs relative">
                                                    <?php 
                                                        $gambarUrl = "../../uploads/" . (!empty($mt['gambar']) ? $mt['gambar'] : 'default.png');
                                                    ?>
                                                    <img src="<?php echo htmlspecialchars($gambarUrl); ?>" 
                                                         alt="<?php echo htmlspecialchars($mt['nama_produk']); ?>"
                                                         class="w-full h-full object-cover grayscale"
                                                         onerror="this.src='../../img/tumpeng.png';">
                                                    <!-- overlay nonaktif -->
                                                    <div class="absolute inset-0 bg-slate-900/30 flex items-center justify-center rounded-2xl">
                                                        <i class="fa-solid fa-eye-slash text-white text-xs"></i>
                                                    </div>
                                                </div>
                                                <div>
                                                    <span class="font-extrabold text-slate-500 text-sm block line-through"><?php echo htmlspecialchars($mt['nama_produk']); ?></span>
                                                    <span class="text-xs text-slate-400">ID: #MNU-<?php echo str_pad($mt['id_produk'], 3, '0', STR_PAD_LEFT); ?></span>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Kategori -->
                                        <td class="py-4 px-3">
                                            <span class="bg-slate-100 text-slate-500 text-xs font-extrabold px-3 py-1 rounded-xl">
                                                <?php echo htmlspecialchars($mt['kategori']); ?>
                                            </span>
                                        </td>

                                        <!-- Harga -->
                                        <td class="py-4 px-3 font-extrabold text-slate-400 line-through">
                                            Rp <?php echo number_format($mt['harga'], 0, ',', '.'); ?>
                                        </td>

                                        <!-- Stok -->
                                        <td class="py-4 px-3">
                                            <span class="bg-slate-100 text-slate-500 text-xs font-bold px-2.5 py-1 rounded-lg">
                                                <?php echo $mt['stok']; ?> porsi
                                            </span>
                                        </td>

                                        <!-- Status Badge -->
                                        <td class="py-4 px-3">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-rose-50 text-rose-600 border border-rose-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 inline-block"></span>
                                                Nonaktif (0)
                                            </span>
                                        </td>

                                        <!-- Aksi: Restore & Delete Permanent -->
                                        <td class="py-4 px-3 text-right">
                                            <div class="inline-flex items-center gap-2">
                                                <!-- Tombol Restore -->
                                                <button 
                                                    onclick="confirmRestore(<?php echo $mt['id_produk']; ?>, '<?php echo addslashes($mt['nama_produk']); ?>')"
                                                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 text-xs font-bold transition"
                                                    title="Aktifkan Kembali Menu">
                                                    <i class="fa-solid fa-rotate-left text-xs"></i>
                                                    Restore
                                                </button>
                                                <!-- Tombol Hapus Permanen -->
                                                <button 
                                                    onclick="confirmDeletePermanent(<?php echo $mt['id_produk']; ?>, '<?php echo addslashes($mt['nama_produk']); ?>')"
                                                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200 text-xs font-bold transition"
                                                    title="Hapus Permanen dari Database">
                                                    <i class="fa-solid fa-trash text-xs"></i>
                                                    Hapus Permanen
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </main>

    <!-- ==================== HIDDEN FORMS: RESTORE & DELETE PERMANENT ==================== -->
    <!-- Form Restore Menu (aktifkan kembali) -->
    <form id="restoreForm" action="" method="post" style="display:none;">
        <input type="hidden" name="action" value="restore">
        <input type="hidden" name="id_produk" id="restore_id_produk" value="">
    </form>

    <!-- Form Hapus Permanen Menu -->
    <form id="deletePermanentForm" action="" method="post" style="display:none;">
        <input type="hidden" name="action" value="delete_permanent">
        <input type="hidden" name="id_produk" id="delete_permanent_id_produk" value="">
    </form>

    <!-- ==================== TAB: MENU TERHAPUS (SOFT DELETE) ==================== -->
    <!-- NOTE: Section ini dirender di luar <main> tapi ditampilkan via switchTab() -->
    <!-- Untuk ini kita masukkan sebagai bagian dari main dengan trik via JS swap -->

    <div id="modalTambah" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-brand-100 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-brand-50 text-brand-500 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-plus"></i>
                    </div>
                    <h3 class="text-lg font-extrabold text-slate-900">Tambah Menu Baru</h3>
                </div>
                <button onclick="closeModalTambah()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="" method="post" enctype="multipart/form-data" class="mt-5 space-y-4">
                <input type="hidden" name="action" value="create">

                <!-- Nama Menu -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Menu Makanan *</label>
                    <input type="text" name="nama_produk" required placeholder="Contoh: Nasi Box Ayam Geprek Spesial" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                </div>

                <!-- Kategori & Harga -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kategori *</label>
                        <select name="kategori" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                            <option value="Nasi Box">Nasi Box</option>
                            <option value="Tumpeng">Tumpeng</option>
                            <option value="Snack Box">Snack Box</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Harga (Rp) *</label>
                        <input type="number" name="harga" required min="1000" step="500" placeholder="Contoh: 25000" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    </div>
                </div>

                <!-- Stok -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Stok / Porsi Tersedia *</label>
                    <input type="number" name="stok" required min="0" value="20" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                </div>

                <!-- Deskripsi -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Deskripsi Lengkap Menu</label>
                    <textarea name="deskripsi" rows="3" placeholder="Jelaskan lauk pauk, isi paket, atau rasa makanan..." class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"></textarea>
                </div>

                <!-- Upload Gambar -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Upload Foto Menu</label>
                    <input type="file" name="gambar" accept="image/png, image/jpeg, image/webp" onchange="previewImage(this, 'previewTambah')" class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100 cursor-pointer">
                    <div id="previewTambahContainer" class="mt-3 hidden">
                        <img id="previewTambah" src="" alt="Preview" class="h-28 rounded-2xl object-cover border border-slate-200">
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="closeModalTambah()" class="px-5 py-2.5 rounded-2xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-6 py-2.5 rounded-2xl bg-brand-500 hover:bg-brand-600 text-white text-sm font-bold shadow-md shadow-brand-500/20">Simpan Menu</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL EDIT MENU (UPDATE) ==================== -->
    <div id="modalEdit" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-brand-100 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <h3 class="text-lg font-extrabold text-slate-900">Edit Menu Catering</h3>
                </div>
                <button onclick="closeModalEdit()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="" method="post" enctype="multipart/form-data" class="mt-5 space-y-4">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id_produk" id="edit_id_produk">

                <!-- Nama Menu -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Menu Makanan *</label>
                    <input type="text" name="nama_produk" id="edit_nama_produk" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                </div>

                <!-- Kategori & Harga -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kategori *</label>
                        <select name="kategori" id="edit_kategori" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                            <option value="Nasi Box">Nasi Box</option>
                            <option value="Tumpeng">Tumpeng</option>
                            <option value="Snack Box">Snack Box</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Harga (Rp) *</label>
                        <input type="number" name="harga" id="edit_harga" required min="1000" step="500" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    </div>
                </div>

                <!-- Stok -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Stok / Porsi Tersedia *</label>
                    <input type="number" name="stok" id="edit_stok" required min="0" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                </div>

                <!-- Deskripsi -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Deskripsi Lengkap Menu</label>
                    <textarea name="deskripsi" id="edit_deskripsi" rows="3" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"></textarea>
                </div>

                <!-- Ganti Foto -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Ganti Foto Menu (Opsional)</label>
                    <input type="file" name="gambar" accept="image/png, image/jpeg, image/webp" onchange="previewImage(this, 'previewEdit')" class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100 cursor-pointer">
                    <div class="mt-3 flex items-center gap-3">
                        <div>
                            <span class="text-[11px] text-slate-400 block mb-1">Foto Saat Ini / Baru:</span>
                            <img id="previewEdit" src="" alt="Foto Menu" class="h-24 w-24 rounded-2xl object-cover border border-slate-200">
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="closeModalEdit()" class="px-5 py-2.5 rounded-2xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-6 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold shadow-md shadow-amber-500/20">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden Form for Delete (DELETE Menu) -->
    <form id="deleteForm" action="" method="post" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id_produk" id="delete_id_produk">
    </form>

    <!-- Hidden Form for Delete User -->
    <form id="deleteUserForm" action="" method="post" style="display: none;">
        <input type="hidden" name="action" value="delete_user">
        <input type="hidden" name="user_id" id="delete_user_id">
        <input type="hidden" name="user_role" id="delete_user_role">
    </form>

    <!-- MODAL TAMBAH USER -->
    <div id="modalTambahUser" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 md:p-8 shadow-2xl border border-brand-100 max-h-[92vh] overflow-y-auto my-auto animate-in fade-in duration-200">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-user-plus text-brand-500"></i>
                        Tambah Pengguna Baru
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Buat akun login baru untuk Admin atau Pelanggan</p>
                </div>
                <button type="button" onclick="closeModalTambahUser()" class="w-9 h-9 rounded-xl bg-slate-100 text-slate-400 hover:text-slate-600 hover:bg-slate-200 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <form action="" method="post" class="mt-6 space-y-4">
                <input type="hidden" name="action" value="create_user">

                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap *</label>
                    <input type="text" name="nama" required placeholder="Contoh: Budi Santoso" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                </div>

                <!-- Email & Password -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Login *</label>
                        <input type="email" name="email" required placeholder="user@yayubi.com" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password *</label>
                        <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    </div>
                </div>

                <!-- Role & No HP -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Role / Peran *</label>
                        <select name="role" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm font-semibold focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 bg-white">
                            <option value="pembeli">Pembeli / Pelanggan</option>
                            <option value="admin">Admin / Penjual</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">No. WhatsApp / HP</label>
                        <input type="text" name="no_hp" placeholder="08xxxxxxxxxx" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    </div>
                </div>

                <!-- Alamat Lengkap -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Lengkap</label>
                    <textarea name="alamat" rows="2" placeholder="Alamat rumah / kantor..." class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"></textarea>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="closeModalTambahUser()" class="px-5 py-2.5 rounded-2xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 transition cursor-pointer">Batal</button>
                    <button type="submit" class="px-6 py-2.5 rounded-2xl bg-brand-500 hover:bg-brand-600 text-white text-sm font-bold shadow-md shadow-brand-500/20 transition cursor-pointer">Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT USER -->
    <div id="modalEditUser" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 md:p-8 shadow-2xl border border-brand-100 max-h-[92vh] overflow-y-auto my-auto animate-in fade-in duration-200">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-user-pen text-amber-500"></i>
                        Edit Akun Pengguna
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Ubah profil, email, role, atau reset password</p>
                </div>
                <button type="button" onclick="closeModalEditUser()" class="w-9 h-9 rounded-xl bg-slate-100 text-slate-400 hover:text-slate-600 hover:bg-slate-200 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <form action="" method="post" class="mt-6 space-y-4">
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="id" id="edit_user_id">
                <input type="hidden" name="current_role" id="edit_user_current_role">

                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap *</label>
                    <input type="text" name="nama" id="edit_user_nama" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                </div>

                <!-- Email & Role -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Login *</label>
                        <input type="email" name="email" id="edit_user_email" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Role / Peran *</label>
                        <select name="role" id="edit_user_role" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm font-semibold focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 bg-white">
                            <option value="pembeli">Pembeli / Pelanggan</option>
                            <option value="admin">Admin / Penjual</option>
                        </select>
                    </div>
                </div>

                <!-- Password Baru (Opsional) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password Baru (Opsional)</label>
                    <input type="password" name="password" id="edit_user_password" minlength="6" placeholder="Kosongkan jika tidak ingin ganti password" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    <p class="text-[11px] text-slate-400 mt-1">Isi minimal 6 karakter hanya jika ingin mengganti password akun ini.</p>
                </div>

                <!-- No HP -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">No. WhatsApp / HP</label>
                    <input type="text" name="no_hp" id="edit_user_no_hp" placeholder="08xxxxxxxxxx" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                </div>

                <!-- Alamat Lengkap -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Lengkap</label>
                    <textarea name="alamat" id="edit_user_alamat" rows="2" placeholder="Alamat pengguna..." class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"></textarea>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="closeModalEditUser()" class="px-5 py-2.5 rounded-2xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 transition cursor-pointer">Batal</button>
                    <button type="submit" class="px-6 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold shadow-md shadow-amber-500/20 transition cursor-pointer">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        // Tab Navigation
        function switchTab(tabId) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            // Remove active style from all sidebar buttons
            document.querySelectorAll('.nav-btn').forEach(btn => {
                btn.classList.remove('active-tab-nav');
                btn.classList.add('text-slate-600');
            });

            // Show selected tab
            const target = document.getElementById(tabId);
            if (target) target.classList.remove('hidden');

            // Set active nav button
            const navMap = {
                'tab-dashboard': 'nav-dashboard',
                'tab-menu': 'nav-menu',
                'tab-pesanan': 'nav-pesanan',
                'tab-pelanggan': 'nav-pelanggan'
            };
            const activeBtn = document.getElementById(navMap[tabId]);
            if (activeBtn) {
                activeBtn.classList.add('active-tab-nav');
                activeBtn.classList.remove('text-slate-600');
            }
            sessionStorage.setItem('yayubi_active_tab', tabId);
        }

        // Filter Table by Category
        function filterKategori(kategori) {
            // Update button styles
            document.querySelectorAll('.cat-pill').forEach(btn => {
                if (btn.innerText.trim().toLowerCase() === kategori.toLowerCase()) {
                    btn.className = "cat-pill bg-brand-500 text-white px-4 py-2 rounded-xl text-xs font-bold transition";
                } else {
                    btn.className = "cat-pill bg-slate-100 text-slate-600 hover:bg-slate-200 px-4 py-2 rounded-xl text-xs font-bold transition";
                }
            });

            const rows = document.querySelectorAll('#menuTable .menu-row');
            rows.forEach(row => {
                const rowCat = row.getAttribute('data-kategori') || '';
                if (kategori === 'Semua' || rowCat.toLowerCase() === kategori.toLowerCase()) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Live Search Filter for Menu Table
        function filterMenuTable() {
            const query = document.getElementById('searchInput').value.toLowerCase().trim();
            const rows = document.querySelectorAll('#menuTable .menu-row');
            rows.forEach(row => {
                const nama = row.getAttribute('data-nama') || '';
                if (nama.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Modal Tambah Menu
        function openModalTambah() {
            document.getElementById('modalTambah').classList.remove('hidden');
        }
        function closeModalTambah() {
            document.getElementById('modalTambah').classList.add('hidden');
        }

        // Modal Edit Menu
        function openModalEdit(data) {
            document.getElementById('edit_id_produk').value = data.id_produk;
            document.getElementById('edit_nama_produk').value = data.nama_produk;
            document.getElementById('edit_kategori').value = data.kategori;
            document.getElementById('edit_harga').value = parseInt(data.harga);
            document.getElementById('edit_stok').value = data.stok;
            document.getElementById('edit_deskripsi').value = data.deskripsi || '';

            const previewImg = document.getElementById('previewEdit');
            if (data.gambar) {
                previewImg.src = '../../uploads/' + data.gambar;
            } else {
                previewImg.src = '../../img/tumpeng.png';
            }

            document.getElementById('modalEdit').classList.remove('hidden');
        }
        function closeModalEdit() {
            document.getElementById('modalEdit').classList.add('hidden');
        }

        // Preview Image on File Selection
        function previewImage(input, previewId) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.getElementById(previewId);
                    img.src = e.target.result;
                    const container = document.getElementById(previewId + 'Container');
                    if (container) container.classList.remove('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // SweetAlert Soft Delete Confirmation for Menu
        function confirmDelete(id, nama) {
            Swal.fire({
                title: 'Nonaktifkan Menu Ini?',
                html: `Menu <b>"${nama}"</b> akan dinonaktifkan dan tidak akan tampil di website.<br><small class="text-slate-500">Kamu bisa mengembalikannya kapan saja lewat tab <b>"Menu Terhapus"</b>.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ea580c',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: '<i class="fa-solid fa-eye-slash mr-1"></i> Ya, Nonaktifkan!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                borderRadius: '1.5rem'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete_id_produk').value = id;
                    document.getElementById('deleteForm').submit();
                }
            });
        }

        // SweetAlert Restore Confirmation for Menu
        function confirmRestore(id, nama) {
            Swal.fire({
                title: 'Aktifkan Kembali Menu Ini?',
                html: `Menu <b>"${nama}"</b> akan ditampilkan kembali di website Yayubi.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: '<i class="fa-solid fa-rotate-left mr-1"></i> Ya, Aktifkan!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                borderRadius: '1.5rem'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('restore_id_produk').value = id;
                    document.getElementById('restoreForm').submit();
                }
            });
        }

        // SweetAlert Hapus Permanen
        function confirmDeletePermanent(id, nama) {
            Swal.fire({
                title: 'Hapus Permanen?',
                html: `Menu <b>"${nama}"</b> akan dihapus <b class="text-rose-600">secara permanen</b> beserta gambarnya.<br><small class="text-rose-500 font-semibold">Tindakan ini tidak dapat dibatalkan!</small>`,
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: '<i class="fa-solid fa-trash mr-1"></i> Hapus Permanen',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                borderRadius: '1.5rem'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete_permanent_id_produk').value = id;
                    document.getElementById('deletePermanentForm').submit();
                }
            });
        }

        // --- USER MANAGEMENT JS LOGIC ---
        function openModalTambahUser() {
            document.getElementById('modalTambahUser').classList.remove('hidden');
        }
        function closeModalTambahUser() {
            document.getElementById('modalTambahUser').classList.add('hidden');
        }

        function openModalEditUser(user) {
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_user_current_role').value = user.role;
            document.getElementById('edit_user_nama').value = user.nama || '';
            document.getElementById('edit_user_email').value = user.email || '';
            document.getElementById('edit_user_role').value = user.role || 'pembeli';
            document.getElementById('edit_user_password').value = '';
            document.getElementById('edit_user_no_hp').value = user.no_hp || '';
            document.getElementById('edit_user_alamat').value = user.alamat || '';
            document.getElementById('modalEditUser').classList.remove('hidden');
        }
        function closeModalEditUser() {
            document.getElementById('modalEditUser').classList.add('hidden');
        }

        function confirmDeleteUser(id, role, nama) {
            Swal.fire({
                title: 'Hapus Akun Pengguna?',
                html: `Apakah kamu yakin ingin menghapus akun <b>"${nama}"</b> dengan role <b>"${role}"</b>?<br><small class="text-rose-500 font-semibold">Pengguna tidak akan bisa login lagi setelah dihapus.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, Hapus Akun!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                borderRadius: '1.5rem'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete_user_id').value = id;
                    document.getElementById('delete_user_role').value = role;
                    document.getElementById('deleteUserForm').submit();
                }
            });
        }

        function filterUserRole(role) {
            document.querySelectorAll('.user-role-pill').forEach(btn => {
                if (btn.getAttribute('data-role') === role) {
                    btn.className = "user-role-pill bg-brand-500 text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm";
                } else {
                    btn.className = "user-role-pill bg-slate-100 text-slate-600 hover:bg-slate-200 px-4 py-2 rounded-xl text-xs font-bold transition";
                }
            });
            filterUserTable();
        }

        function filterUserTable() {
            const activePill = document.querySelector('.user-role-pill.bg-brand-500');
            const selectedRole = activePill ? activePill.getAttribute('data-role') : 'all';
            const query = (document.getElementById('searchUserInput')?.value || '').toLowerCase().trim();
            const rows = document.querySelectorAll('#userTable .user-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const rowRole = row.getAttribute('data-role') || '';
                const rowSearch = (row.getAttribute('data-search') || '').toLowerCase();
                
                const roleMatches = (selectedRole === 'all' || rowRole === selectedRole);
                const searchMatches = (!query || rowSearch.includes(query));
                
                if (roleMatches && searchMatches) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Handle empty state row if dynamically needed
            const emptyRow = document.getElementById('userEmptyRow');
            if (emptyRow) {
                emptyRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
            }
        }

        // SweetAlert Logout Confirmation
        function confirmLogout() {
            Swal.fire({
                title: 'Konfirmasi Keluar',
                text: 'Apakah kamu ingin keluar dari sesi Dashboard Admin Yayubi?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ea580c',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal',
                borderRadius: '1.5rem'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logout.php';
                }
            });
        }

        // Feedback notification after POST action
        <?php if ($feedback): ?>
            Swal.fire({
                icon: '<?php echo $feedback['status'] ? "success" : "error"; ?>',
                title: '<?php echo $feedback['status'] ? "Berhasil!" : "Gagal!"; ?>',
                text: '<?php echo addslashes($feedback['message']); ?>',
                confirmButtonColor: '#ea580c',
                timer: 3000,
                borderRadius: '1.5rem'
            });
        <?php endif; ?>

        // Restore active tab on page reload
        const savedTab = sessionStorage.getItem('yayubi_active_tab');
        if (savedTab && document.getElementById(savedTab)) {
            switchTab(savedTab);
        }

        // Auto-refresh orders every 25 seconds if not inside modal
        setInterval(() => {
            const modalTambah = document.getElementById('modalTambah');
            const modalEdit = document.getElementById('modalEdit');
            const modalTambahUser = document.getElementById('modalTambahUser');
            const modalEditUser = document.getElementById('modalEditUser');
            const isModalOpen = (modalTambah && !modalTambah.classList.contains('hidden')) || 
                                (modalEdit && !modalEdit.classList.contains('hidden')) ||
                                (modalTambahUser && !modalTambahUser.classList.contains('hidden')) ||
                                (modalEditUser && !modalEditUser.classList.contains('hidden'));
            if (!isModalOpen) {
                location.reload();
            }
        }, 25000);
    </script>
</body>
</html>