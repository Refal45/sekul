<?php
session_start();
require_once "../koneksi.php";

// Pastikan hanya admin/guru yang bisa akses
if (!isset($_SESSION['id_petugas']) || !isset($_SESSION['username'])) {
    header("Location: ../login-admin.php");
    exit;
}

$username    = $_SESSION['username'] ?? 'Unknown';
$namaLengkap = $_SESSION['nama_lengkap'] ?? 'Guru';
$role        = $_SESSION['role'] ?? 'user';

date_default_timezone_set("Asia/Jakarta");

// Ambil ID jadwal dari URL
$id_jadwal = $_GET['id'] ?? '';
if (empty($id_jadwal)) {
    header("Location: jadwal.php");
    exit;
}

// Ambil data jadwal yang akan diedit
$sql_jadwal = "SELECT j.*, k.tingkat, k.nama_kelas, m.nama_mapel, m.kode_mapel, p.nama_petugas, p.nip 
               FROM jadwal j
               JOIN kelas k ON j.id_kelas = k.id_kelas
               JOIN mata_pelajaran m ON j.id_mapel = m.id_mapel
               JOIN petugas p ON j.id_petugas = p.id_petugas
               WHERE j.id_jadwal = ?";
$stmt_jadwal = $pdo->prepare($sql_jadwal);
$stmt_jadwal->execute([$id_jadwal]);
$jadwal = $stmt_jadwal->fetch(PDO::FETCH_ASSOC);

if (!$jadwal) {
    $_SESSION['error'] = "Data jadwal tidak ditemukan!";
    header("Location: jadwal.php");
    exit;
}

// Ambil data untuk dropdown
$kelas = $pdo->query("SELECT * FROM kelas ORDER BY tingkat")->fetchAll(PDO::FETCH_ASSOC);
$mapel = $pdo->query("SELECT * FROM mata_pelajaran ORDER BY nama_mapel")->fetchAll(PDO::FETCH_ASSOC);
$guru = $pdo->query("SELECT * FROM petugas WHERE role = 'guru' ORDER BY nama_petugas")->fetchAll(PDO::FETCH_ASSOC);

$error = null;
$success = null;

// Proses form edit jadwal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id_kelas = $_POST['id_kelas'] ?? '';
        $id_mapel = $_POST['id_mapel'] ?? '';
        $id_petugas = $_POST['id_petugas'] ?? '';
        $hari = $_POST['hari'] ?? '';
        $jam_mulai = $_POST['jam_mulai'] ?? '';
        $jam_selesai = $_POST['jam_selesai'] ?? '';

        // Validasi
        if (empty($id_kelas) || empty($id_mapel) || empty($id_petugas) || empty($hari) || empty($jam_mulai) || empty($jam_selesai)) {
            throw new Exception("Semua field harus diisi!");
        }

        // Validasi waktu
        if (strtotime($jam_selesai) <= strtotime($jam_mulai)) {
            throw new Exception("Jam selesai harus setelah jam mulai!");
        }

        // Cek konflik jadwal (kecuali dengan dirinya sendiri)
        $sql_konflik = "SELECT COUNT(*) FROM jadwal 
                       WHERE id_kelas = ? AND hari = ? AND id_jadwal != ?
                       AND ((jam_mulai <= ? AND jam_selesai > ?) 
                       OR (jam_mulai < ? AND jam_selesai >= ?)
                       OR (jam_mulai >= ? AND jam_selesai <= ?))";
        $stmt_konflik = $pdo->prepare($sql_konflik);
        $stmt_konflik->execute([
            $id_kelas,
            $hari,
            $id_jadwal,
            $jam_mulai,
            $jam_mulai,
            $jam_selesai,
            $jam_selesai,
            $jam_mulai,
            $jam_selesai
        ]);
        $konflik = $stmt_konflik->fetchColumn();

        if ($konflik > 0) {
            throw new Exception("Konflik jadwal! Kelas sudah memiliki jadwal pada waktu tersebut.");
        }

        // Update data
        $sql = "UPDATE jadwal SET id_kelas = ?, id_mapel = ?, id_petugas = ?, hari = ?, jam_mulai = ?, jam_selesai = ? 
                WHERE id_jadwal = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_kelas, $id_mapel, $id_petugas, $hari, $jam_mulai, $jam_selesai, $id_jadwal]);

        $_SESSION['flash'] = "updated";
        header("Location: jadwal.php");
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
    <title>Edit Jadwal - Sistem Sekolah</title>
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

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            background-color: white;
        }

        .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            resize: vertical;
            min-height: 100px;
        }

        .form-textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Form Header */
        .form-header {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 1.5rem;
            border-radius: 12px 12px 0 0;
            margin-bottom: 0;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }

        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }

            .action-buttons .btn {
                width: 100%;
            }
        }

        /* Grid Form */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 1.5rem;
        }

        @media (min-width: 768px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Required field indicator */
        .required::after {
            content: " *";
            color: #ef4444;
        }

        /* Jadwal Info */
        .jadwal-info {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .jadwal-info h4 {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .jadwal-info p {
            color: #6b7280;
            font-size: 0.875rem;
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
                    <span>Sekolah Guru</span>
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
                    <a href="index-2.php" class="rounded-md hover:bg-blue-700 hover:text-white">
                        <i class="fa-solid fa-gauge-high"></i>Dashboard
                    </a>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-section-title">Manajemen Data</div>
                    <a href="guru.php" class="rounded-md hover:bg-blue-700 hover:text-white">
                        <i class="fa-solid fa-user-tie"></i>Data Guru
                    </a>
                    <a href="siswa.php" class="rounded-md hover:bg-blue-700 hover:text-white">
                        <i class="fa-solid fa-user-graduate"></i>Data Siswa
                    </a>
                    <a href="kelas.php" class="rounded-md hover:bg-blue-700 hover:text-white">
                        <i class="fa-solid fa-door-open"></i>Data Kelas
                    </a>
                    <a href="mapel.php" class="rounded-md hover:bg-blue-700 hover:text-white">
                        <i class="fa-solid fa-book-open"></i>Data Mapel
                    </a>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-section-title">Akademik</div>
                    <a href="jadwal.php" class="bg-blue-600 text-white rounded-md active">
                        <i class="fa-solid fa-calendar-alt"></i>Jadwal Mengajar
                    </a>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-section-title">Penilaian</div>
                    <a href="nilai.php" class="rounded-md hover:bg-blue-700 hover:text-white">
                        <i class="fa-solid fa-file-signature"></i>Data Nilai
                    </a>
                    <a href="kehadiran.php" class="rounded-md hover:bg-blue-700 hover:text-white">
                        <i class="fa-solid fa-clipboard-user"></i>Data Kehadiran
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
                        <div class="text-sm font-bold truncate">Edit Jadwal</div>
                        <div class="text-slate-500 text-xs truncate">Form edit jadwal pelajaran</div>
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
                            <i class="fa-solid fa-pen-to-square text-blue-600"></i> Edit Jadwal
                        </h1>
                        <p class="text-slate-500 mt-1">Form edit jadwal pelajaran</p>
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
                    <div class="mb-6 flex items-center text-sm text-slate-500">
                        <a href="jadwal.php" class="hover:text-blue-600 flex items-center">
                            <i class="fa-solid fa-calendar-alt mr-2"></i> Jadwal Mengajar
                        </a>
                        <i class="fa-solid fa-chevron-right mx-2"></i>
                        <span class="text-blue-600 font-medium">Edit Jadwal</span>
                    </div>

                    <?php if ($error): ?>
                        <div class="error-message mb-6">
                            <i class="fa-solid fa-triangle-exclamation mr-2"></i><?= htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Info Jadwal -->
                    <div class="jadwal-info mb-6">
                        <h4 class="flex items-center gap-2">
                            <i class="fa-solid fa-info-circle text-blue-500"></i>
                            Informasi Jadwal Saat Ini
                        </h4>
                        <p>
                            <strong>Kelas:</strong> <?= htmlspecialchars($jadwal['tingkat'] . ' - ' . $jadwal['nama_kelas']); ?> |
                            <strong>Mapel:</strong> <?= htmlspecialchars($jadwal['nama_mapel']); ?> |
                            <strong>Guru:</strong> <?= htmlspecialchars($jadwal['nama_petugas']); ?> |
                            <strong>Hari:</strong> <?= htmlspecialchars($jadwal['hari']); ?> |
                            <strong>Jam:</strong> <?= htmlspecialchars($jadwal['jam_mulai'] . ' - ' . $jadwal['jam_selesai']); ?>
                        </p>
                    </div>

                    <!-- Form Edit Jadwal -->
                    <div class="card">
                        <div class="form-header">
                            <h3 class="text-lg font-bold flex items-center gap-2">
                                <i class="fa-solid fa-edit"></i>
                                Form Edit Jadwal
                            </h3>
                            <p class="text-orange-100 mt-1">Perbarui data jadwal sesuai kebutuhan</p>
                        </div>

                        <div class="p-6">
                            <form method="POST" id="jadwalForm">
                                <div class="form-grid">
                                    <!-- Kelas -->
                                    <div class="form-group">
                                        <label for="id_kelas" class="form-label required">Kelas</label>
                                        <select name="id_kelas" id="id_kelas" class="form-select" required>
                                            <option value="">Pilih Kelas</option>
                                            <?php foreach ($kelas as $k): ?>
                                                <option value="<?= $k['id_kelas']; ?>"
                                                    <?= (isset($_POST['id_kelas']) ? $_POST['id_kelas'] : $jadwal['id_kelas']) == $k['id_kelas'] ? 'selected' : ''; ?>>
                                                    <?= htmlspecialchars($k['tingkat'] . ' - ' . $k['nama_kelas']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Mata Pelajaran -->
                                    <div class="form-group">
                                        <label for="id_mapel" class="form-label required">Mata Pelajaran</label>
                                        <select name="id_mapel" id="id_mapel" class="form-select" required>
                                            <option value="">Pilih Mata Pelajaran</option>
                                            <?php foreach ($mapel as $m): ?>
                                                <option value="<?= $m['id_mapel']; ?>" <?= (isset($_POST['id_mapel']) ? $_POST['id_mapel'] : $jadwal['id_mapel']) == $m['id_mapel'] ? 'selected' : ''; ?>>
                                                    <?= htmlspecialchars($m['nama_mapel'] . ' (' . $m['kode_mapel'] . ')'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Guru Pengajar -->
                                    <div class="form-group">
                                        <label for="id_petugas" class="form-label required">Guru Pengajar</label>
                                        <select name="id_petugas" id="id_petugas" class="form-select" required>
                                            <option value="">Pilih Guru</option>
                                            <?php foreach ($guru as $g): ?>
                                                <option value="<?= $g['id_petugas']; ?>" <?= (isset($_POST['id_petugas']) ? $_POST['id_petugas'] : $jadwal['id_petugas']) == $g['id_petugas'] ? 'selected' : ''; ?>>
                                                    <?= htmlspecialchars($g['nama_petugas'] . ' - ' . $g['nip']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Hari -->
                                    <div class="form-group">
                                        <label for="hari" class="form-label required">Hari</label>
                                        <select name="hari" id="hari" class="form-select" required>
                                            <option value="">Pilih Hari</option>
                                            <option value="Senin" <?= (isset($_POST['hari']) ? $_POST['hari'] : $jadwal['hari']) == 'Senin' ? 'selected' : ''; ?>>Senin</option>
                                            <option value="Selasa" <?= (isset($_POST['hari']) ? $_POST['hari'] : $jadwal['hari']) == 'Selasa' ? 'selected' : ''; ?>>Selasa</option>
                                            <option value="Rabu" <?= (isset($_POST['hari']) ? $_POST['hari'] : $jadwal['hari']) == 'Rabu' ? 'selected' : ''; ?>>Rabu</option>
                                            <option value="Kamis" <?= (isset($_POST['hari']) ? $_POST['hari'] : $jadwal['hari']) == 'Kamis' ? 'selected' : ''; ?>>Kamis</option>
                                            <option value="Jumat" <?= (isset($_POST['hari']) ? $_POST['hari'] : $jadwal['hari']) == 'Jumat' ? 'selected' : ''; ?>>Jumat</option>
                                            <option value="Sabtu" <?= (isset($_POST['hari']) ? $_POST['hari'] : $jadwal['hari']) == 'Sabtu' ? 'selected' : ''; ?>>Sabtu</option>
                                        </select>
                                    </div>

                                    <!-- Jam Mulai -->
                                    <div class="form-group">
                                        <label for="jam_mulai" class="form-label required">Jam Mulai</label>
                                        <input type="time" name="jam_mulai" id="jam_mulai" class="form-input"
                                            value="<?= isset($_POST['jam_mulai']) ? $_POST['jam_mulai'] : $jadwal['jam_mulai']; ?>" required>
                                    </div>

                                    <!-- Jam Selesai -->
                                    <div class="form-group">
                                        <label for="jam_selesai" class="form-label required">Jam Selesai</label>
                                        <input type="time" name="jam_selesai" id="jam_selesai" class="form-input"
                                            value="<?= isset($_POST['jam_selesai']) ? $_POST['jam_selesai'] : $jadwal['jam_selesai']; ?>" required>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="action-buttons">
                                    <a href="jadwal.php" class="btn btn-secondary">
                                        <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                                    </a>
                                    <button type="reset" class="btn btn-warning">
                                        <i class="fa-solid fa-rotate mr-2"></i> Reset
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <footer class="mt-6 text-center text-sm text-slate-500 select-none pb-4">
                        &copy; <?= date('Y'); ?> Sekolah - Edit Jadwal Management
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

        // Form validation
        document.getElementById('jadwalForm').addEventListener('submit', function(e) {
            const jamMulai = document.getElementById('jam_mulai').value;
            const jamSelesai = document.getElementById('jam_selesai').value;

            if (jamMulai && jamSelesai) {
                if (jamSelesai <= jamMulai) {
                    e.preventDefault();
                    alert('Jam selesai harus setelah jam mulai!');
                    document.getElementById('jam_selesai').focus();
                }
            }
        });

        // Auto-set jam selesai based on jam mulai
        document.getElementById('jam_mulai').addEventListener('change', function() {
            const jamMulai = this.value;
            const jamSelesai = document.getElementById('jam_selesai');

            if (jamMulai && !jamSelesai.value) {
                // Set default jam selesai 2 jam setelah jam mulai
                const [hours, minutes] = jamMulai.split(':');
                let endHours = parseInt(hours) + 2;
                if (endHours >= 24) endHours -= 24;
                const endTime = endHours.toString().padStart(2, '0') + ':' + minutes;
                jamSelesai.value = endTime;
            }
        });

        // Keyboard shortcuts
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
                window.location.href = 'jadwal.php';
            }
        });

        // Confirm before reset
        document.querySelector('button[type="reset"]').addEventListener('click', function(e) {
            if (!confirm('Yakin ingin mereset form? Semua perubahan yang belum disimpan akan hilang.')) {
                e.preventDefault();
            }
        });
    </script>
</body>

</html>