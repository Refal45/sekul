<?php
session_start();
require_once "../koneksi.php";

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login-admin.php");
    exit;
}

$username    = $_SESSION['username'] ?? 'Unknown';
$namaLengkap = $_SESSION['nama_lengkap'] ?? 'Admin';
$role        = $_SESSION['role'] ?? 'user';

$error = null;
$success = null;

// Ambil data siswa, jadwal untuk dropdown
$siswa = $pdo->query("SELECT id_siswa, nis, nama_siswa FROM siswa ORDER BY nama_siswa")->fetchAll(PDO::FETCH_ASSOC);
$jadwal = $pdo->query("SELECT j.id_jadwal, m.nama_mapel, p.nama_petugas, j.hari, j.jam_mulai, j.jam_selesai 
                       FROM jadwal j 
                       JOIN mata_pelajaran m ON j.id_mapel = m.id_mapel 
                       JOIN petugas p ON j.id_petugas = p.id_petugas 
                       ORDER BY m.nama_mapel")->fetchAll(PDO::FETCH_ASSOC);

// Proses form tambah kehadiran
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id_siswa = $_POST['id_siswa'] ?? '';
        $id_jadwal = $_POST['id_jadwal'] ?? '';
        $tanggal = $_POST['tanggal'] ?? '';
        $status = $_POST['status'] ?? '';
        $keterangan = $_POST['keterangan'] ?? '';

        // Validasi
        if (empty($id_siswa) || empty($id_jadwal) || empty($tanggal) || empty($status)) {
            throw new Exception("Semua field wajib harus diisi!");
        }

        // Validasi tanggal tidak boleh lebih dari hari ini
        $today = date('Y-m-d');
        if ($tanggal > $today) {
            throw new Exception("Tanggal tidak boleh lebih dari hari ini!");
        }

        // Cek apakah kehadiran untuk siswa, jadwal, dan tanggal ini sudah ada
        $checkSql = "SELECT id_kehadiran FROM kehadiran WHERE id_siswa = ? AND id_jadwal = ? AND tanggal = ?";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$id_siswa, $id_jadwal, $tanggal]);

        if ($checkStmt->fetch()) {
            throw new Exception("Kehadiran untuk siswa, jadwal, dan tanggal ini sudah ada!");
        }

        // Insert data
        $sql = "INSERT INTO kehadiran (id_siswa, id_jadwal, tanggal, status, keterangan) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_siswa, $id_jadwal, $tanggal, $status, $keterangan]);

        $_SESSION['flash'] = "success";
        header("Location: kehadiran.php");
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Tambah Kehadiran - Sistem Sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
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

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-input.error,
        .form-select.error,
        .form-textarea.error {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        .form-help {
            margin-top: 0.25rem;
            font-size: 0.75rem;
            color: #6b7280;
        }

        .form-error {
            margin-top: 0.25rem;
            font-size: 0.75rem;
            color: #ef4444;
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

        .logout-btn:hover {
            background: #b91c1c;
            transform: translateY(-2px);
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
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

        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #4b5563;
            transform: translateY(-1px);
        }

        /* Form Header */
        .form-header {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 1.5rem;
            border-radius: 12px 12px 0 0;
            margin-bottom: 0;
        }

        /* Status Preview */
        .status-preview {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 1.125rem;
            min-width: 100px;
            margin-left: 1rem;
        }

        .status-hadir {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .status-izin {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        .status-sakit {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
        }

        .status-alpha {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        /* Jadwal Info */
        .jadwal-info {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 0.5rem;
        }

        .jadwal-info.hidden {
            display: none;
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .btn {
                padding: 0.75rem 1rem;
                font-size: 0.75rem;
            }

            .status-preview {
                margin-left: 0;
                margin-top: 0.5rem;
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
                    <a href="siswa.php" class="rounded-md hover:bg-blue-700 hover:text-white">
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
                    <a href="kehadiran.php" class="bg-blue-600 text-white rounded-md active">
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
                        <div class="text-sm font-bold truncate">Tambah Kehadiran</div>
                        <div class="text-slate-500 text-xs truncate">Form tambah data kehadiran siswa</div>
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
                            <i class="fa-solid fa-clipboard-user text-blue-600"></i> Tambah Kehadiran
                        </h1>
                        <p class="text-slate-500 mt-1">Form tambah data kehadiran siswa</p>
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
                    <!-- Breadcrumb -->
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <a href="kehadiran.php" class="text-blue-600 hover:text-blue-800">
                            <i class="fa-solid fa-clipboard-user mr-1"></i> Data Kehadiran
                        </a>
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                        <span class="text-slate-700 font-medium">Tambah Kehadiran</span>
                    </div>

                    <?php if ($error): ?>
                        <div class="error-message">
                            <i class="fa-solid fa-triangle-exclamation mr-2"></i><?= htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Form Tambah Kehadiran -->
                    <div class="card">
                        <div class="form-header">
                            <h3 class="text-lg font-bold flex items-center gap-2">
                                <i class="fa-solid fa-plus"></i>
                                Form Tambah Kehadiran Siswa
                            </h3>
                        </div>

                        <div class="p-6">
                            <form method="POST" id="kehadiranForm">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Siswa -->
                                    <div class="form-group">
                                        <label for="id_siswa" class="form-label">
                                            <i class="fa-solid fa-user-graduate mr-2"></i>Siswa
                                        </label>
                                        <select name="id_siswa" id="id_siswa" class="form-select" required>
                                            <option value="">Pilih Siswa</option>
                                            <?php foreach ($siswa as $s): ?>
                                                <option value="<?= $s['id_siswa'] ?>"
                                                    <?= (isset($_POST['id_siswa']) && $_POST['id_siswa'] == $s['id_siswa']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($s['nis'] . ' - ' . $s['nama_siswa']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-help">Pilih siswa yang akan dicatat kehadirannya</div>
                                    </div>

                                    <!-- Jadwal -->
                                    <div class="form-group">
                                        <label for="id_jadwal" class="form-label">
                                            <i class="fa-solid fa-calendar-alt mr-2"></i>Jadwal
                                        </label>
                                        <select name="id_jadwal" id="id_jadwal" class="form-select" required>
                                            <option value="">Pilih Jadwal</option>
                                            <?php foreach ($jadwal as $j): ?>
                                                <option value="<?= $j['id_jadwal'] ?>"
                                                    data-mapel="<?= htmlspecialchars($j['nama_mapel']) ?>"
                                                    data-guru="<?= htmlspecialchars($j['nama_petugas']) ?>"
                                                    data-hari="<?= htmlspecialchars($j['hari']) ?>"
                                                    data-jam="<?= htmlspecialchars($j['jam_mulai'] . ' - ' . $j['jam_selesai']) ?>"
                                                    <?= (isset($_POST['id_jadwal']) && $_POST['id_jadwal'] == $j['id_jadwal']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($j['nama_mapel'] . ' - ' . $j['nama_petugas']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-help">Pilih jadwal pelajaran</div>
                                        <div id="jadwalInfo" class="jadwal-info hidden">
                                            <div class="text-sm">
                                                <div><strong>Mata Pelajaran:</strong> <span id="infoMapel">-</span></div>
                                                <div><strong>Guru:</strong> <span id="infoGuru">-</span></div>
                                                <div><strong>Hari:</strong> <span id="infoHari">-</span></div>
                                                <div><strong>Jam:</strong> <span id="infoJam">-</span></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tanggal -->
                                    <div class="form-group">
                                        <label for="tanggal" class="form-label">
                                            <i class="fa-solid fa-calendar-day mr-2"></i>Tanggal
                                        </label>
                                        <input type="date" name="tanggal" id="tanggal"
                                            value="<?= htmlspecialchars($_POST['tanggal'] ?? date('Y-m-d')) ?>"
                                            class="form-input" required>
                                        <div class="form-help">Pilih tanggal kehadiran</div>
                                        <div id="tanggalError" class="form-error"></div>
                                    </div>

                                    <!-- Status -->
                                    <div class="form-group">
                                        <label for="status" class="form-label">
                                            <i class="fa-solid fa-clipboard-check mr-2"></i>Status Kehadiran
                                        </label>
                                        <div class="flex items-center">
                                            <select name="status" id="status" class="form-select" required>
                                                <option value="">Pilih Status</option>
                                                <option value="Hadir" <?= (isset($_POST['status']) && $_POST['status'] == 'Hadir') ? 'selected' : '' ?>>Hadir</option>
                                                <option value="Izin" <?= (isset($_POST['status']) && $_POST['status'] == 'Izin') ? 'selected' : '' ?>>Izin</option>
                                                <option value="Sakit" <?= (isset($_POST['status']) && $_POST['status'] == 'Sakit') ? 'selected' : '' ?>>Sakit</option>
                                                <option value="Alpha" <?= (isset($_POST['status']) && $_POST['status'] == 'Alpha') ? 'selected' : '' ?>>Alpha</option>
                                            </select>
                                            <span id="statusPreview" class="status-preview status-alpha">-</span>
                                        </div>
                                        <div class="form-help">Pilih status kehadiran siswa</div>
                                    </div>

                                    <!-- Keterangan -->
                                    <div class="form-group md:col-span-2">
                                        <label for="keterangan" class="form-label">
                                            <i class="fa-solid fa-note-sticky mr-2"></i>Keterangan
                                        </label>
                                        <textarea name="keterangan" id="keterangan"
                                            class="form-textarea"
                                            placeholder="Masukkan keterangan (opsional)"
                                            rows="3"><?= htmlspecialchars($_POST['keterangan'] ?? '') ?></textarea>
                                        <div class="form-help">Keterangan tambahan untuk status kehadiran</div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex flex-col sm:flex-row gap-3 justify-end mt-8 pt-6 border-t border-slate-200">
                                    <a href="kehadiran.php" class="btn btn-secondary">
                                        <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                                    </a>
                                    <button type="reset" class="btn btn-warning">
                                        <i class="fa-solid fa-rotate mr-2"></i> Reset
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Kehadiran
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <footer class="mt-6 text-center text-sm text-slate-500 select-none pb-4">
                        &copy; <?= date('Y'); ?> Sekolah - Kehadiran Management
                    </footer>
                </main>
            </div>
        </div>
    </div>

    <script>
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

        // Status Preview
        const statusSelect = document.getElementById('status');
        const statusPreview = document.getElementById('statusPreview');

        function updateStatusPreview() {
            const status = statusSelect.value;

            // Update preview
            statusPreview.textContent = status || '-';

            // Update class based on value
            statusPreview.className = 'status-preview ';
            if (status === 'Hadir') {
                statusPreview.classList.add('status-hadir');
            } else if (status === 'Izin') {
                statusPreview.classList.add('status-izin');
            } else if (status === 'Sakit') {
                statusPreview.classList.add('status-sakit');
            } else if (status === 'Alpha') {
                statusPreview.classList.add('status-alpha');
            } else {
                statusPreview.classList.add('status-alpha');
            }
        }

        statusSelect.addEventListener('change', updateStatusPreview);

        // Jadwal Info
        const jadwalSelect = document.getElementById('id_jadwal');
        const jadwalInfo = document.getElementById('jadwalInfo');
        const infoMapel = document.getElementById('infoMapel');
        const infoGuru = document.getElementById('infoGuru');
        const infoHari = document.getElementById('infoHari');
        const infoJam = document.getElementById('infoJam');

        jadwalSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];

            if (selectedOption.value) {
                infoMapel.textContent = selectedOption.getAttribute('data-mapel');
                infoGuru.textContent = selectedOption.getAttribute('data-guru');
                infoHari.textContent = selectedOption.getAttribute('data-hari');
                infoJam.textContent = selectedOption.getAttribute('data-jam');
                jadwalInfo.classList.remove('hidden');
            } else {
                jadwalInfo.classList.add('hidden');
            }
        });

        // Tanggal validation
        const tanggalInput = document.getElementById('tanggal');
        const tanggalError = document.getElementById('tanggalError');

        function validateTanggal() {
            const selectedDate = new Date(tanggalInput.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (selectedDate > today) {
                tanggalInput.classList.add('error');
                tanggalError.textContent = 'Tanggal tidak boleh lebih dari hari ini';
                return false;
            } else {
                tanggalInput.classList.remove('error');
                tanggalError.textContent = '';
                return true;
            }
        }

        tanggalInput.addEventListener('change', validateTanggal);

        // Form validation
        document.getElementById('kehadiranForm').addEventListener('submit', function(e) {
            if (!validateTanggal()) {
                e.preventDefault();
                tanggalInput.focus();
            }
        });

        // Auto-hide notifications
        document.addEventListener("DOMContentLoaded", () => {
            const alerts = document.querySelectorAll(".error-message, .success-message");
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = "opacity 0.5s ease";
                    alert.style.opacity = "0";
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });
        });

        // Add keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Ctrl+Shift+L for logout
            if (e.ctrlKey && e.shiftKey && e.key === 'L') {
                e.preventDefault();
                if (confirm('Yakin ingin logout?')) {
                    window.location.href = 'logout.php';
                }
            }

            // Ctrl+Shift+B untuk kembali
            if (e.ctrlKey && e.shiftKey && e.key === 'B') {
                e.preventDefault();
                window.location.href = 'kehadiran.php';
            }
        });

        // Initialize status preview
        document.addEventListener('DOMContentLoaded', function() {
            updateStatusPreview();
        });
    </script>
</body>

</html>