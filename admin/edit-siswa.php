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

// Ambil ID siswa dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: siswa.php");
    exit;
}

$id_siswa = $_GET['id'];

// Ambil data siswa berdasarkan ID
$stmt_siswa = $pdo->prepare("SELECT * FROM siswa WHERE id_siswa = ?");
$stmt_siswa->execute([$id_siswa]);
$siswa = $stmt_siswa->fetch(PDO::FETCH_ASSOC);

// Jika siswa tidak ditemukan, redirect
if (!$siswa) {
    $_SESSION['error'] = "Data siswa tidak ditemukan!";
    header("Location: siswa.php");
    exit;
}

// Ambil data kelas untuk dropdown
$stmt_kelas = $pdo->prepare("SELECT id_kelas, nama_kelas FROM kelas ORDER BY nama_kelas");
$stmt_kelas->execute();
$kelas = $stmt_kelas->fetchAll(PDO::FETCH_ASSOC);

// Proses form edit siswa
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nama_siswa = $_POST['nama_siswa'] ?? '';
        $nis = $_POST['nis'] ?? '';
        $username_siswa = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $no_hp = $_POST['no_hp'] ?? '';
        $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
        $id_kelas = $_POST['id_kelas'] ?? null;

        // Validasi input
        if (empty($nama_siswa) || empty($nis) || empty($username_siswa)) {
            throw new Exception("Field nama, NIS, dan username wajib diisi!");
        }

        // Cek apakah NIS sudah ada (kecuali untuk siswa ini)
        $stmt_check = $pdo->prepare("SELECT id_siswa FROM siswa WHERE nis = ? AND id_siswa != ?");
        $stmt_check->execute([$nis, $id_siswa]);
        if ($stmt_check->fetch()) {
            throw new Exception("NIS sudah terdaftar!");
        }

        // Cek apakah username sudah ada (kecuali untuk siswa ini)
        $stmt_check = $pdo->prepare("SELECT id_siswa FROM siswa WHERE username = ? AND id_siswa != ?");
        $stmt_check->execute([$username_siswa, $id_siswa]);
        if ($stmt_check->fetch()) {
            throw new Exception("Username sudah terdaftar!");
        }

        // Update data siswa
        if (!empty($password)) {
            // Jika password diisi, update password juga
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE siswa SET nama_siswa = ?, nis = ?, username = ?, password = ?, no_hp = ?, jenis_kelamin = ?, id_kelas = ? WHERE id_siswa = ?");
            $stmt->execute([$nama_siswa, $nis, $username_siswa, $hashed_password, $no_hp, $jenis_kelamin, $id_kelas, $id_siswa]);
        } else {
            // Jika password tidak diisi, jangan update password
            $stmt = $pdo->prepare("UPDATE siswa SET nama_siswa = ?, nis = ?, username = ?, no_hp = ?, jenis_kelamin = ?, id_kelas = ? WHERE id_siswa = ?");
            $stmt->execute([$nama_siswa, $nis, $username_siswa, $no_hp, $jenis_kelamin, $id_kelas, $id_siswa]);
        }

        $_SESSION['success'] = "Data siswa berhasil diperbarui!";
        $_SESSION['flash'] = "updated";
        header("Location: siswa.php");
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
    <title>Edit Siswa - Sistem Sekolah</title>
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

        .form-radio-group {
            display: flex;
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .form-radio {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-radio input {
            margin: 0;
        }

        /* Print styles */
        @media print {

            .sidebar,
            .burger-btn,
            .main-header,
            .btn {
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

        /* Password toggle */
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #6b7280;
        }

        .password-container {
            position: relative;
        }

        .password-hint {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.25rem;
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
                        <div class="text-sm font-bold truncate">Edit Siswa</div>
                        <div class="text-slate-500 text-xs truncate">Form edit data siswa</div>
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
                            <i class="fa-solid fa-user-edit text-yellow-600"></i> Edit Siswa
                        </h1>
                        <p class="text-slate-500 mt-1">Form edit data siswa</p>
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
                    <?php if ($error): ?>
                        <div class="error-message">
                            <i class="fa-solid fa-triangle-exclamation mr-2"></i><?= htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <div class="card p-6 mb-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-slate-700">
                                <i class="fa-solid fa-user-edit mr-2"></i> Form Edit Siswa
                            </h2>
                            <a href="siswa.php" class="btn btn-secondary">
                                <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                            </a>
                        </div>

                        <form method="POST" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Kolom 1 -->
                                <div class="space-y-4">
                                    <div class="form-group">
                                        <label for="nama_siswa" class="form-label">
                                            <i class="fa-solid fa-user mr-2"></i>Nama Lengkap Siswa *
                                        </label>
                                        <input type="text" id="nama_siswa" name="nama_siswa" required
                                            class="form-input" placeholder="Masukkan nama lengkap siswa"
                                            value="<?= htmlspecialchars($siswa['nama_siswa']) ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="nis" class="form-label">
                                            <i class="fa-solid fa-id-card mr-2"></i>NIS (Nomor Induk Siswa) *
                                        </label>
                                        <input type="text" id="nis" name="nis" required
                                            class="form-input" placeholder="Masukkan NIS"
                                            value="<?= htmlspecialchars($siswa['nis']) ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="username" class="form-label">
                                            <i class="fa-solid fa-user-circle mr-2"></i>Username *
                                        </label>
                                        <input type="text" id="username" name="username" required
                                            class="form-input" placeholder="Masukkan username"
                                            value="<?= htmlspecialchars($siswa['username']) ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="password" class="form-label">
                                            <i class="fa-solid fa-lock mr-2"></i>Password
                                        </label>
                                        <div class="password-container">
                                            <input type="password" id="password" name="password"
                                                class="form-input pr-10" placeholder="Kosongkan jika tidak ingin mengubah">
                                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                        </div>
                                        <div class="password-hint">
                                            <i class="fa-solid fa-info-circle mr-1"></i> Biarkan kosong jika tidak ingin mengubah password
                                        </div>
                                    </div>
                                </div>

                                <!-- Kolom 2 -->
                                <div class="space-y-4">
                                    <div class="form-group">
                                        <label for="id_kelas" class="form-label">
                                            <i class="fa-solid fa-door-open mr-2"></i>Kelas
                                        </label>
                                        <select id="id_kelas" name="id_kelas" class="form-select">
                                            <option value="">-- Pilih Kelas --</option>
                                            <?php foreach ($kelas as $k): ?>
                                                <option value="<?= $k['id_kelas'] ?>"
                                                    <?= ($siswa['id_kelas'] == $k['id_kelas']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($k['nama_kelas']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="no_hp" class="form-label">
                                            <i class="fa-solid fa-phone mr-2"></i>Nomor HP
                                        </label>
                                        <input type="tel" id="no_hp" name="no_hp"
                                            class="form-input" placeholder="Masukkan nomor HP"
                                            value="<?= htmlspecialchars($siswa['no_hp'] ?? '') ?>">
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fa-solid fa-venus-mars mr-2"></i>Jenis Kelamin *
                                        </label>
                                        <div class="form-radio-group">
                                            <label class="form-radio">
                                                <input type="radio" name="jenis_kelamin" value="L" required
                                                    <?= ($siswa['jenis_kelamin'] === 'L') ? 'checked' : '' ?>>
                                                <span>Laki-laki</span>
                                            </label>
                                            <label class="form-radio">
                                                <input type="radio" name="jenis_kelamin" value="P" required
                                                    <?= ($siswa['jenis_kelamin'] === 'P') ? 'checked' : '' ?>>
                                                <span>Perempuan</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Informasi Siswa -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                                <h3 class="font-semibold text-blue-800 mb-2 flex items-center">
                                    <i class="fa-solid fa-info-circle mr-2"></i> Informasi Siswa
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                                    <div><span class="font-medium">ID Siswa:</span> <?= htmlspecialchars($siswa['id_siswa']) ?></div>
                                    <div><span class="font-medium">Terdaftar sejak:</span> <?= date('d/m/Y', strtotime($siswa['created_at'] ?? 'now')) ?></div>
                                </div>
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-slate-200">
                                <button type="submit" class="btn btn-success flex-1">
                                    <i class="fa-solid fa-save mr-2"></i> Update Data
                                </button>
                                <button type="reset" class="btn btn-warning flex-1">
                                    <i class="fa-solid fa-rotate mr-2"></i> Reset Form
                                </button>
                                <a href="siswa.php" class="btn btn-secondary flex-1 text-center">
                                    <i class="fa-solid fa-times mr-2"></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>

                    <footer class="mt-6 text-center text-sm text-slate-500 select-none pb-4">
                        &copy; <?= date('Y'); ?> Sekolah - Edit Siswa
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

        // Password toggle
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'fa-solid fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'fa-solid fa-eye';
            }
        }

        // Auto-hide notifications
        document.addEventListener("DOMContentLoaded", () => {
            const alerts = document.querySelectorAll(".error-message, .success-message, .info-message, .warning-message");
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = "opacity 0.5s ease";
                    alert.style.opacity = "0";
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let valid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    field.style.borderColor = '#ef4444';
                } else {
                    field.style.borderColor = '#d1d5db';
                }
            });

            if (!valid) {
                e.preventDefault();
                alert('Harap isi semua field yang wajib diisi!');
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
                window.location.href = 'siswa.php';
            }
        });
    </script>
</body>

</html>