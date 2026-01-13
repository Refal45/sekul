<?php
session_start();
require_once "../koneksi.php"; // koneksi PDO

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login-admin.php");
    exit;
}

$username    = $_SESSION['username'] ?? 'Unknown';
$namaLengkap = $_SESSION['nama_lengkap'] ?? 'Admin';
$role        = $_SESSION['role'] ?? 'user';

// Ambil data siswa join dengan kelas
$stmt = $pdo->prepare("SELECT s.id_siswa, s.nama_siswa, s.nis, s.username, s.password, s.no_hp, s.jenis_kelamin, k.nama_kelas 
                       FROM siswa s
                       LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
                       ORDER BY s.id_siswa DESC");
$stmt->execute();
$siswas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total siswa dan statistik
$totalSiswa = count($siswas);
$totalLaki = 0;
$totalPerempuan = 0;

foreach ($siswas as $s) {
    if ($s['jenis_kelamin'] === 'L') {
        $totalLaki++;
    } else {
        $totalPerempuan++;
    }
}

// Inisialisasi variabel untuk menghindari error
$error = null;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Data Siswa - Sistem Sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
    <style>
        /* ===== Base ===== */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* ===== Scrollbar Customization ===== */
        /* Untuk Webkit browsers (Chrome, Safari, Edge) */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
            transition: background 0.3s ease;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Untuk Firefox */
        * {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f5f9;
        }

        /* ===== Card ===== */
        .card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(30, 41, 59, 0.08);
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }

        .card:hover {
            box-shadow: 0 6px 18px rgba(30, 41, 59, 0.12);
            transform: translateY(-2px);
        }

        /* ===== Sidebar ===== */
        .sidebar {
            background: linear-gradient(180deg, #1e3a8a, #1e40af);
            color: #f1f5f9;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            height: 100vh;
            width: 20rem;
            overflow-y: auto;
            transition: transform 0.3s ease;
            transform: translateX(-100%);
        }

        .sidebar.open {
            transform: translateX(0);
        }

        .sidebar a {
            display: flex;
            align-items: center;
            padding: 0.9rem 1.2rem;
            border-radius: 0.5rem;
            transition: all 0.25s ease;
            color: inherit;
            text-decoration: none;
            margin: 0.25rem 0.5rem;
            font-size: 0.95rem;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(3px);
        }

        .sidebar a.active {
            background-color: #3b82f6;
            color: #fff;
            font-weight: 600;
        }

        .sidebar i {
            width: 1.5rem;
            text-align: center;
            margin-right: 0.75rem;
        }

        /* ===== Overlay ===== */
        .overlay {
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.4);
            z-index: 90;
            display: none;
            transition: opacity 0.3s;
        }

        .overlay.show {
            display: block;
        }

        /* ===== Main Content ===== */
        .main-content {
            margin-left: 0;
            padding: 1rem;
            width: 100%;
            transition: margin-left 0.3s ease;
            min-height: 100vh;
            overflow-x: hidden;
        }

        @media (min-width: 1024px) {
            .main-content {
                margin-left: 20rem;
                padding: 1.5rem 2rem;
            }

            .sidebar {
                transform: translateX(0);
            }
        }

        /* ===== Header Styles ===== */
        .main-header {
            position: sticky;
            top: 0;
            z-index: 30;
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            border-radius: 12px;
        }

        .main-header.desktop-header {
            padding: 1.25rem 2rem;
        }

        .main-header.mobile-header {
            padding: 1rem;
        }

        /* ===== Table ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 10px;
            overflow: hidden;
            font-size: 14px;
        }

        table th {
            text-align: left;
            padding: 0.75rem 1rem;
            color: #1e3a8a;
            font-weight: 700;
            background: rgba(59, 130, 246, 0.08);
        }

        table td {
            padding: 0.75rem 1rem;
            border-top: 1px solid rgba(226, 232, 240, 0.8);
        }

        table tr:nth-child(even) {
            background-color: rgba(241, 245, 249, 0.5);
        }

        tbody tr:hover {
            background-color: rgba(59, 130, 246, 0.12) !important;
        }

        /* ===== Burger Button (Mobile) ===== */
        .burger-btn {
            position: relative;
            z-index: 80;
            background: #3b82f6;
            color: #fff;
            padding: 0.6rem 0.9rem;
            border-radius: 0.6rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.12);
            transition: background-color 0.2s, transform 0.12s ease;
            cursor: pointer;
            border: none;
            outline: none;
        }

        .burger-btn:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }

        .burger-btn i {
            font-size: 1.1rem;
            line-height: 1;
        }

        /* ===== Responsive ===== */
        @media (max-width: 768px) {
            .main-content {
                padding: 0.75rem;
                margin-top: 0;
            }

            .table-scroll {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .mobile-header-container+.content-wrapper {
                margin-top: 0.5rem;
            }

            .burger-btn {
                display: inline-flex;
            }
        }

        @media (min-width: 1024px) {
            .burger-btn {
                display: none !important;
            }
        }

        /* ===== Custom Badges ===== */
        .badge {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 3px 8px;
            font-size: 13px;
            border-radius: 6px;
            font-weight: 600;
        }

        .badge-laki {
            background: #2563eb;
            color: white;
        }

        .badge-perempuan {
            background: #d63384;
            color: white;
        }

        /* ===== Chart Container ===== */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        @media (max-width: 768px) {
            .chart-container {
                height: 250px;
            }
        }

        /* ===== Stat Card ===== */
        .stat-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
            text-align: center;
            min-height: 120px;
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0.25rem 0;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #64748b;
        }

        /* ===== Logo Image ===== */
        .logo-image {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }

        /* ===== IMPROVED LAYOUT ===== */
        .content-wrapper {
            display: flex;
            flex-direction: column;
            min-height: calc(100vh - 2rem);
        }

        .page-content {
            flex: 1;
            overflow-x: hidden;
        }

        /* ===== IMPROVED TABLE RESPONSIVENESS ===== */
        @media (max-width: 640px) {
            table {
                font-size: 0.75rem;
            }

            .btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.75rem;
            }

            .table-scroll {
                border: 1px solid #e2e8f0;
                border-radius: 8px;
            }
        }

        /* ===== FIXED HEADER ISSUES ===== */
        .header-container {
            position: relative;
            width: 100%;
        }

        .mobile-header-container {
            position: sticky;
            top: 0;
            z-index: 40;
            background: #f8fafc;
            padding-bottom: 0.5rem;
        }

        .desktop-header-container {
            position: sticky;
            top: 0;
            z-index: 40;
            background: #f8fafc;
            padding-bottom: 1rem;
        }

        /* ===== IMPROVED SPACING ===== */
        .content-spacing {
            margin-top: 0;
        }

        /* ===== SCROLL BEHAVIOR ===== */
        html {
            scroll-behavior: smooth;
        }

        /* Mobile optimizations */
        @media (max-width: 640px) {
            .stat-card {
                padding: 1rem 0.5rem;
                min-height: 100px;
            }

            .stat-icon {
                font-size: 1.5rem;
            }

            .stat-value {
                font-size: 1.5rem;
            }

            .stat-label {
                font-size: 0.75rem;
            }
        }

        /* Error message styling */
        .error-message {
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        /* Success message styling */
        .success-message {
            background-color: #dcfce7;
            border: 1px solid #bbf7d0;
            color: #166534;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        /* Warning message styling */
        .warning-message {
            background-color: #fef3c7;
            border: 1px solid #fde68a;
            color: #92400e;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        /* Info message styling */
        .info-message {
            background-color: #dbeafe;
            border: 1px solid #bfdbfe;
            color: #1e40af;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        /* Sidebar sections */
        .sidebar-section {
            margin-bottom: 1.5rem;
        }

        .sidebar-section-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #93c5fd;
            margin: 1rem 1.2rem 0.5rem;
            font-weight: 600;
        }

        /* Hide header when sidebar is open on mobile */
        .mobile-header-container.hidden {
            display: none;
        }

        /* Wrapper area logout */
        .logout-wrapper {
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #1e3a8a;
            padding-bottom: 4rem;
            display: flex;
            justify-content: center;
        }

        /* Tombol logout responsif */
        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #dc2626;
            color: white;
            padding: clamp(10px, 2.5vw, 18px);
            border-radius: 14px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-decoration: none;
            font-weight: 600;
            font-size: clamp(14px, 2vw, 20px);
            width: 100%;
            max-width: 360px;
            transition: all 0.3s ease;
        }

        /* Hover */
        .logout-btn:hover {
            background: #b91c1c;
            transform: translateY(-2px);
        }

        /* Extra small HP (≤350px) */
        @media (max-width: 350px) {
            .logout-btn {
                padding: 12px;
                font-size: 14px;
            }
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
        }

        .btn-primary {
            background-color: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2563eb;
            transform: translateY(-1px);
        }

        .btn-success {
            background-color: #10b981;
            color: white;
        }

        .btn-success:hover {
            background-color: #059669;
            transform: translateY(-1px);
        }

        .btn-warning {
            background-color: #f59e0b;
            color: white;
        }

        .btn-warning:hover {
            background-color: #d97706;
            transform: translateY(-1px);
        }

        .btn-danger {
            background-color: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
            transform: translateY(-1px);
        }

        /* Search Input */
        .search-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Pagination */
        .pagination-btn {
            padding: 0.5rem 1rem;
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.875rem;
        }

        .pagination-btn:hover:not(:disabled) {
            background-color: #2563eb;
            transform: translateY(-1px);
        }

        .pagination-btn:disabled {
            background-color: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }

        /* Empty state styling */
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #64748b;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #cbd5e1;
        }

        /* Loading states */
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }

        /* Print styles */
        @media print {

            .sidebar,
            .burger-btn,
            .main-header {
                display: none !important;
            }

            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #000 !important;
            }
        }
    </style>
</head>

<body>
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="sidebar flex flex-col p-4">
            <!-- Tombol close mobile -->
            <div class="lg:hidden flex justify-end mb-2">
                <button onclick="closeSidebar()" class="text-white text-xl p-2 hover:bg-blue-700 rounded-lg transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="mb-4 border-b border-blue-700 pb-3">
                <div class="text-2xl font-bold mb-1 select-none flex items-center gap-2">
                    <img src="https://th.bing.com/th/id/OIP.Pg5X0hL6o9CkquPBvWIhfQHaHa?w=161&h=180&c=7&r=0&o=7&dpr=1.2&pid=1.7&rm=3"
                        alt="Logo Sekolah" class="logo-image">
                    <span>Sekolah Admin</span>
                </div>
                <div class="text-sm select-text">
                    <p class="font-medium">
                        <i class="fa-solid fa-user-tie mr-1"></i> <?= htmlspecialchars($namaLengkap); ?>
                        <span class="ml-2 px-2 py-0.5 rounded-full text-xs bg-blue-700 text-white">
                            <?= ucfirst(htmlspecialchars($role)); ?>
                        </span>
                    </p>
                    <p class="text-xs text-blue-200 mt-1">Username: <?= htmlspecialchars($username); ?></p>
                </div>
            </div>

            <!-- Navigasi -->
            <nav class="flex-1 space-y-1">
                <div class="sidebar-section">
                    <div class="sidebar-section-title">Dashboard</div>
                    <a href="index.php" class="rounded-md hover:bg-blue-700 hover:text-white">
                        <i class="fa-solid fa-gauge-high"></i>Dashboard
                    </a>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-section-title">Manajemen</div>
                    <a href="admin.php" class="rounded-md hover:bg-blue-700 hover:text-white">
                        <i class="fa-solid fa-users-gear"></i>Admin
                    </a>
                    <a href="guru.php" class="rounded-md hover:bg-blue-700 hover:text-white">
                        <i class="fa-solid fa-user-tie"></i>Guru
                    </a>
                    <a href="siswa.php" class="bg-blue-600 text-white rounded-md active">
                        <i class="fa-solid fa-user-graduate"></i>Siswa
                    </a>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-section-title">Akademik</div>
                    <a href="kelas.php" class="rounded-md hover:bg-blue-700 hover:text-white">
                        <i class="fa-solid fa-door-open"></i>Kelas
                    </a>
                    <a href="mapel.php" class="rounded-md hover:bg-blue-700 hover:text-white">
                        <i class="fa-solid fa-book-open"></i>Mata Pelajaran
                    </a>
                    <a href="jadwal.php" class="rounded-md hover:bg-blue-700 hover:text-white">
                        <i class="fa-solid fa-calendar-alt"></i>Jadwal
                    </a>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-section-title">Penilaian</div>
                    <a href="nilai.php" class="rounded-md hover:bg-blue-700 hover:text-white">
                        <i class="fa-solid fa-file-signature"></i>Nilai
                    </a>
                    <a href="kehadiran.php" class="rounded-md hover:bg-blue-700 hover:text-white">
                        <i class="fa-solid fa-clipboard-user"></i>Kehadiran
                    </a>
                </div>
            </nav>

            <div class="logout-wrapper">
                <a href="logout.php" class="logout-btn" onclick="return confirm('Yakin ingin logout?')">
                    <i class="fa-solid fa-power-off mr-2"></i>Logout
                </a>
            </div>
        </aside>

        <!-- Overlay for mobile -->
        <div class="overlay" id="overlay" onclick="closeSidebar()"></div>

        <!-- Main content -->
        <div class="main-content flex-1 flex flex-col transition-all duration-300">
            <!-- Header Container untuk Mobile -->
            <div class="mobile-header-container lg:hidden" id="mobileHeaderContainer">
                <!-- Header Mobile -->
                <header class="main-header mobile-header flex justify-between items-center">
                    <div class="flex-1 min-w-0">
                        <div id="waktuDepokMobile" class="text-xs text-slate-500 mb-1 truncate"></div>
                        <div class="text-sm font-bold truncate">Data Siswa</div>
                        <div class="text-slate-500 text-xs truncate">Manajemen data siswa sekolah</div>
                    </div>

                </header>

                <!-- Burger button (mobile) placed under header to avoid overlap -->
                <div class="px-4 mt-3">
                    <button id="burgerBtn" class="burger-btn lg:hidden" onclick="openSidebar()" aria-label="Buka menu">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                </div>
            </div>

            <!-- Header Container untuk Desktop -->
            <div class="desktop-header-container hidden lg:block">
                <!-- Header Desktop -->
                <header class="main-header desktop-header flex justify-between items-center">
                    <div>
                        <h1 class="text-xl font-bold flex items-center gap-2">
                            <i class="fa-solid fa-user-graduate text-blue-600"></i> Data Siswa
                        </h1>
                        <p class="text-slate-500 mt-1">Manajemen data siswa sekolah</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <div id="waktuDepok" class="text-sm text-slate-500 mb-1"></div>
                            <div class="font-medium select-text"><?= htmlspecialchars($namaLengkap); ?></div>
                            <div class="text-xs text-slate-500 select-text">Username: <?= htmlspecialchars($username); ?></div>
                        </div>

                    </div>
                </header>
            </div>

            <div class="content-wrapper content-spacing">
                <main class="page-content overflow-y-auto">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="success-message">
                            <i class="fa-solid fa-circle-check mr-2"></i><?= htmlspecialchars($_SESSION['success']); ?>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <?php if (isset($error)): ?>
                        <div class="error-message">
                            <i class="fa-solid fa-triangle-exclamation mr-2"></i><?= htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Info Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 mb-6 auto-rows-fr">
                        <div class="card stat-card">
                            <div class="stat-icon text-blue-600">
                                <i class="fa-solid fa-user-graduate"></i>
                            </div>
                            <h3 class="stat-label">Total Siswa</h3>
                            <p class="stat-value text-blue-700"><?= $totalSiswa; ?></p>
                        </div>
                        <div class="card stat-card">
                            <div class="stat-icon text-blue-500">
                                <i class="fa-solid fa-mars"></i>
                            </div>
                            <h3 class="stat-label">Laki-laki</h3>
                            <p class="stat-value text-blue-500"><?= $totalLaki; ?></p>
                        </div>
                        <div class="card stat-card">
                            <div class="stat-icon text-pink-500">
                                <i class="fa-solid fa-venus"></i>
                            </div>
                            <h3 class="stat-label">Perempuan</h3>
                            <p class="stat-value text-pink-500"><?= $totalPerempuan; ?></p>
                        </div>
                        <div class="card stat-card">
                            <div class="stat-icon text-yellow-500">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <h3 class="stat-label">Update Terakhir</h3>
                            <p class="stat-value text-yellow-500"><?= date('d/m/Y'); ?></p>
                        </div>
                    </div>

                    <!-- Notifikasi -->
                    <div class="card p-4 mb-6">
                        <?php if (isset($_SESSION['flash']) && $_SESSION['flash'] === "success"): ?>
                            <div class="success-message mb-4 flex items-center justify-between">
                                <span><i class="fa-solid fa-circle-check mr-2"></i> Siswa baru berhasil ditambahkan!</span>
                                <button onclick="this.parentElement.remove()" class="ml-3 text-green-700 hover:text-green-900">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        <?php unset($_SESSION['flash']);
                        endif; ?>

                        <?php if (isset($_SESSION['flash']) && $_SESSION['flash'] === "updated"): ?>
                            <div class="info-message mb-4 flex items-center justify-between">
                                <span><i class="fa-solid fa-pen-to-square mr-2"></i> Data siswa berhasil diperbarui!</span>
                                <button onclick="this.parentElement.remove()" class="ml-3 text-blue-700 hover:text-blue-900">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        <?php unset($_SESSION['flash']);
                        endif; ?>

                        <?php if (isset($_SESSION['flash']) && $_SESSION['flash'] === "deleted"): ?>
                            <div class="warning-message mb-4 flex items-center justify-between">
                                <span><i class="fa-solid fa-trash mr-2"></i> Data siswa berhasil dihapus!</span>
                                <button onclick="this.parentElement.remove()" class="ml-3 text-yellow-700 hover:text-yellow-900">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        <?php unset($_SESSION['flash']);
                        endif; ?>

                        <!-- Header dan Search -->
                        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
                            <a href="tambah-siswa.php" class="btn btn-success">
                                <i class="fa-solid fa-plus mr-2"></i> Tambah Siswa
                            </a>

                            <div class="w-full lg:w-64">
                                <input type="text" id="searchInput" placeholder="Cari Nama / NIS / Username..."
                                    class="search-input">
                            </div>
                        </div>

                        <!-- Tabel Siswa -->
                        <div class="table-scroll">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr>
                                        <th class="py-3 px-4">No</th>
                                        <th class="py-3 px-4">NIS</th>
                                        <th class="py-3 px-4">Nama Siswa</th>
                                        <th class="py-3 px-4">Username</th>
                                        <th class="py-3 px-4">Kelas</th>
                                        <th class="py-3 px-4">No HP</th>
                                        <th class="py-3 px-4">Jenis Kelamin</th>
                                        <th class="py-3 px-4">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($siswas)): ?>
                                        <?php $no = 1;
                                        foreach ($siswas as $s): ?>
                                            <tr class="border-t hover:bg-slate-50">
                                                <td class="py-3 px-4"><?= $no++; ?></td>
                                                <td class="py-3 px-4 nis"><?= htmlspecialchars($s['nis']); ?></td>
                                                <td class="py-3 px-4 nama"><?= htmlspecialchars($s['nama_siswa']); ?></td>
                                                <td class="py-3 px-4 username"><?= htmlspecialchars($s['username']); ?></td>
                                                <td class="py-3 px-4"><?= htmlspecialchars($s['nama_kelas'] ?? '-'); ?></td>
                                                <td class="py-3 px-4"><?= htmlspecialchars($s['no_hp'] ?? '-'); ?></td>
                                                <td class="py-3 px-4">
                                                    <span class="badge <?= $s['jenis_kelamin'] === 'L' ? 'badge-laki' : 'badge-perempuan'; ?>">
                                                        <?= $s['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan'; ?>
                                                    </span>
                                                </td>
                                                <td class="py-3 px-4">
                                                    <div class="flex flex-wrap gap-2">
                                                        <a href="edit-siswa.php?id=<?= $s['id_siswa']; ?>"
                                                            class="btn btn-warning px-3 py-1 text-sm">
                                                            <i class="fa-solid fa-pen mr-1"></i> Edit
                                                        </a>
                                                        <a href="hapus-siswa.php?id=<?= $s['id_siswa']; ?>"
                                                            onclick="return confirm('Hapus siswa <?= htmlspecialchars($s['nama_siswa']) ?>?');"
                                                            class="btn btn-danger px-3 py-1 text-sm">
                                                            <i class="fa-solid fa-trash mr-1"></i> Hapus
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="py-8 px-4 text-center">
                                                <div class="empty-state">
                                                    <i class="fa-solid fa-user-graduate"></i>
                                                    <p>Belum ada data siswa</p>
                                                    <p class="text-sm mt-2">Klik tombol "Tambah Siswa" untuk menambahkan data</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="flex flex-col sm:flex-row justify-between items-center mt-6 gap-4">
                            <div id="entriesInfo" class="text-sm text-slate-600"></div>
                            <div class="flex items-center gap-2">
                                <button id="prevBtn" class="pagination-btn">Prev</button>
                                <span id="pageInfo" class="text-sm text-slate-600 mx-2"></span>
                                <button id="nextBtn" class="pagination-btn">Next</button>
                            </div>
                        </div>
                    </div>

                    <footer class="mt-6 text-center text-sm text-slate-500 select-none pb-4">
                        &copy; <?= date('Y'); ?> Sekolah - Siswa Management
                    </footer>
                </main>
            </div>
        </div>
    </div>

    <script>
        // ChartJS Global Settings
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.plugins.tooltip.mode = 'index';
        Chart.defaults.plugins.tooltip.intersect = false;

        // Waktu Depok
        function updateWaktu() {
            const now = new Date().toLocaleString('id-ID', {
                timeZone: 'Asia/Jakarta',
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            const waktuElements = document.querySelectorAll('#waktuDepok, #waktuDepokMobile');
            waktuElements.forEach(el => {
                if (el) el.innerText = now + " WIB";
            });
        }
        setInterval(updateWaktu, 1000);
        updateWaktu();

        // Sidebar Mobile
        function openSidebar() {
            document.querySelector('.sidebar').classList.add('open');
            document.getElementById('overlay').classList.add('show');
            document.getElementById('burgerBtn').style.display = "none";
            document.getElementById('mobileHeaderContainer').classList.add('hidden');
            document.body.style.overflow = "hidden";
        }

        function closeSidebar() {
            document.querySelector('.sidebar').classList.remove('open');
            document.getElementById('overlay').classList.remove('show');
            document.getElementById('burgerBtn').style.display = "flex";
            document.getElementById('mobileHeaderContainer').classList.remove('hidden');
            document.body.style.overflow = "auto";
        }

        // Close sidebar when clicking on a link (mobile)
        document.querySelectorAll('.sidebar a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) {
                    closeSidebar();
                }
            });
        });

        // Close sidebar on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeSidebar();
            }
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                closeSidebar();
            }
        });

        // Search & Pagination
        const rows = document.querySelectorAll("tbody tr");
        const searchInput = document.getElementById("searchInput");
        const entriesInfo = document.getElementById("entriesInfo");
        const prevBtn = document.getElementById("prevBtn");
        const nextBtn = document.getElementById("nextBtn");
        const pageInfo = document.getElementById("pageInfo");

        let currentPage = 1;
        const rowsPerPage = 5;

        function displayTable() {
            let filter = searchInput.value.toLowerCase();
            let filteredRows = Array.from(rows).filter(row => {
                let nama = row.querySelector(".nama")?.innerText.toLowerCase() || '';
                let nis = row.querySelector(".nis")?.innerText.toLowerCase() || '';
                let username = row.querySelector(".username")?.innerText.toLowerCase() || '';
                return nama.includes(filter) || nis.includes(filter) || username.includes(filter);
            });

            let totalRows = filteredRows.length;
            let totalPages = Math.ceil(totalRows / rowsPerPage);

            if (currentPage > totalPages) currentPage = totalPages || 1;

            rows.forEach(row => row.style.display = "none");
            filteredRows.forEach((row, index) => {
                if (index >= (currentPage - 1) * rowsPerPage && index < currentPage * rowsPerPage) {
                    row.style.display = "";
                }
            });

            entriesInfo.textContent = `Menampilkan ${Math.min(currentPage * rowsPerPage, totalRows)} dari ${totalRows} data`;
            pageInfo.textContent = `Halaman ${currentPage} dari ${totalPages || 1}`;

            prevBtn.disabled = currentPage === 1;
            nextBtn.disabled = currentPage === totalPages || totalPages === 0;
        }

        searchInput.addEventListener("keyup", () => {
            currentPage = 1;
            displayTable();
        });

        prevBtn.addEventListener("click", () => {
            if (currentPage > 1) {
                currentPage--;
                displayTable();
            }
        });

        nextBtn.addEventListener("click", () => {
            let filter = searchInput.value.toLowerCase();
            let filteredRows = Array.from(rows).filter(row => {
                let nama = row.querySelector(".nama")?.innerText.toLowerCase() || '';
                let nis = row.querySelector(".nis")?.innerText.toLowerCase() || '';
                let username = row.querySelector(".username")?.innerText.toLowerCase() || '';
                return nama.includes(filter) || nis.includes(filter) || username.includes(filter);
            });
            let totalPages = Math.ceil(filteredRows.length / rowsPerPage);

            if (currentPage < totalPages) {
                currentPage++;
                displayTable();
            }
        });

        // Auto-hide notifications
        document.addEventListener("DOMContentLoaded", () => {
            const alerts = document.querySelectorAll(".success-message, .info-message, .warning-message");
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = "opacity 0.5s ease";
                    alert.style.opacity = "0";
                    setTimeout(() => alert.remove(), 500);
                }, 4000);
            });

            // Initialize table display
            displayTable();
        });

        // Print functionality
        function printSiswaData() {
            window.print();
        }

        // Add keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Ctrl+P for print
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                printSiswaData();
            }

            // Ctrl+Shift+L for logout
            if (e.ctrlKey && e.shiftKey && e.key === 'L') {
                e.preventDefault();
                if (confirm('Yakin ingin logout?')) {
                    window.location.href = 'logout.php';
                }
            }

            // Ctrl+Shift+S untuk tambah siswa
            if (e.ctrlKey && e.shiftKey && e.key === 'S') {
                e.preventDefault();
                window.location.href = 'tambah-siswa.php';
            }
        });

        // Performance monitoring
        window.addEventListener('load', () => {
            const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
            console.log(`Page loaded in ${loadTime}ms`);
        });
    </script>
</body>

</html>