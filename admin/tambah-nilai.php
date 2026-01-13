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

// Ambil data siswa, mapel untuk dropdown
$siswa = $pdo->query("SELECT id_siswa, nis, nama_siswa FROM siswa ORDER BY nama_siswa")->fetchAll(PDO::FETCH_ASSOC);
$mapel = $pdo->query("SELECT id_mapel, nama_mapel FROM mata_pelajaran ORDER BY nama_mapel")->fetchAll(PDO::FETCH_ASSOC);

// Proses form tambah nilai
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id_siswa = $_POST['id_siswa'] ?? '';
        $id_mapel = $_POST['id_mapel'] ?? '';
        $semester = $_POST['semester'] ?? '';
        $nilai = $_POST['nilai'] ?? '';

        // Validasi
        if (empty($id_siswa) || empty($id_mapel) || empty($semester) || empty($nilai)) {
            throw new Exception("Semua field harus diisi!");
        }

        if (!is_numeric($nilai) || $nilai < 0 || $nilai > 100) {
            throw new Exception("Nilai harus antara 0 - 100!");
        }

        // Cek apakah nilai untuk siswa, mapel, dan semester ini sudah ada
        $checkSql = "SELECT id_nilai FROM nilai WHERE id_siswa = ? AND id_mapel = ? AND semester = ?";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$id_siswa, $id_mapel, $semester]);

        if ($checkStmt->fetch()) {
            throw new Exception("Nilai untuk siswa, mata pelajaran, dan semester ini sudah ada!");
        }

        // Insert data
        $sql = "INSERT INTO nilai (id_siswa, id_mapel, semester, nilai) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_siswa, $id_mapel, $semester, $nilai]);

        $_SESSION['flash'] = "success";
        header("Location: nilai.php");
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
    <title>Tambah Nilai - Sistem Sekolah</title>
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
        .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-input.error,
        .form-select.error {
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

        /* Nilai Preview */
        .nilai-preview {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 1.125rem;
            min-width: 80px;
            margin-left: 1rem;
        }

        .nilai-sangat-baik {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .nilai-baik {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
        }

        .nilai-cukup {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        .nilai-kurang {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .btn {
                padding: 0.75rem 1rem;
                font-size: 0.75rem;
            }

            .nilai-preview {
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
                    <a href="nilai.php" class="bg-blue-600 text-white rounded-md active">
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
                        <div class="text-sm font-bold truncate">Tambah Nilai</div>
                        <div class="text-slate-500 text-xs truncate">Form tambah data nilai siswa</div>
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
                            <i class="fa-solid fa-file-signature text-blue-600"></i> Tambah Nilai
                        </h1>
                        <p class="text-slate-500 mt-1">Form tambah data nilai siswa</p>
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
                        <a href="nilai.php" class="text-blue-600 hover:text-blue-800">
                            <i class="fa-solid fa-file-signature mr-1"></i> Data Nilai
                        </a>
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                        <span class="text-slate-700 font-medium">Tambah Nilai</span>
                    </div>

                    <?php if ($error): ?>
                        <div class="error-message">
                            <i class="fa-solid fa-triangle-exclamation mr-2"></i><?= htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Form Tambah Nilai -->
                    <div class="card">
                        <div class="form-header">
                            <h3 class="text-lg font-bold flex items-center gap-2">
                                <i class="fa-solid fa-plus"></i>
                                Form Tambah Nilai Siswa
                            </h3>
                        </div>

                        <div class="p-6">
                            <form method="POST" id="nilaiForm">
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
                                        <div class="form-help">Pilih siswa yang akan diberi nilai</div>
                                    </div>

                                    <!-- Mata Pelajaran -->
                                    <div class="form-group">
                                        <label for="id_mapel" class="form-label">
                                            <i class="fa-solid fa-book-open mr-2"></i>Mata Pelajaran
                                        </label>
                                        <select name="id_mapel" id="id_mapel" class="form-select" required>
                                            <option value="">Pilih Mata Pelajaran</option>
                                            <?php foreach ($mapel as $m): ?>
                                                <option value="<?= $m['id_mapel'] ?>"
                                                    <?= (isset($_POST['id_mapel']) && $_POST['id_mapel'] == $m['id_mapel']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($m['nama_mapel']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-help">Pilih mata pelajaran</div>
                                    </div>

                                    <!-- Semester -->
                                    <div class="form-group">
                                        <label for="semester" class="form-label">
                                            <i class="fa-solid fa-calendar-alt mr-2"></i>Semester
                                        </label>
                                        <select name="semester" id="semester" class="form-select" required>
                                            <option value="">Pilih Semester</option>
                                            <option value="1" <?= (isset($_POST['semester']) && $_POST['semester'] == '1') ? 'selected' : '' ?>>Semester 1</option>
                                            <option value="2" <?= (isset($_POST['semester']) && $_POST['semester'] == '2') ? 'selected' : '' ?>>Semester 2</option>
                                        </select>
                                        <div class="form-help">Pilih semester</div>
                                    </div>

                                    <!-- Nilai -->
                                    <div class="form-group">
                                        <label for="nilai" class="form-label">
                                            <i class="fa-solid fa-percent mr-2"></i>Nilai
                                        </label>
                                        <div class="flex items-center">
                                            <input type="number" name="nilai" id="nilai"
                                                min="0" max="100" step="0.1"
                                                value="<?= htmlspecialchars($_POST['nilai'] ?? '') ?>"
                                                class="form-input" placeholder="0 - 100" required>
                                            <span id="nilaiPreview" class="nilai-preview nilai-kurang">-</span>
                                        </div>
                                        <div class="form-help">Masukkan nilai antara 0 - 100</div>
                                        <div id="nilaiError" class="form-error"></div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex flex-col sm:flex-row gap-3 justify-end mt-8 pt-6 border-t border-slate-200">
                                    <a href="nilai.php" class="btn btn-secondary">
                                        <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                                    </a>
                                    <button type="reset" class="btn btn-warning">
                                        <i class="fa-solid fa-rotate mr-2"></i> Reset
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Nilai
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <footer class="mt-6 text-center text-sm text-slate-500 select-none pb-4">
                        &copy; <?= date('Y'); ?> Sekolah - Nilai Management
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

        // Nilai Preview
        const nilaiInput = document.getElementById('nilai');
        const nilaiPreview = document.getElementById('nilaiPreview');
        const nilaiError = document.getElementById('nilaiError');

        function updateNilaiPreview() {
            const nilai = parseFloat(nilaiInput.value) || 0;

            // Update preview
            nilaiPreview.textContent = nilai;

            // Update class based on value
            nilaiPreview.className = 'nilai-preview ';
            if (nilai >= 90) {
                nilaiPreview.classList.add('nilai-sangat-baik');
            } else if (nilai >= 80) {
                nilaiPreview.classList.add('nilai-baik');
            } else if (nilai >= 70) {
                nilaiPreview.classList.add('nilai-cukup');
            } else {
                nilaiPreview.classList.add('nilai-kurang');
            }

            // Validation
            if (nilai < 0 || nilai > 100) {
                nilaiInput.classList.add('error');
                nilaiError.textContent = 'Nilai harus antara 0 - 100';
            } else {
                nilaiInput.classList.remove('error');
                nilaiError.textContent = '';
            }
        }

        nilaiInput.addEventListener('input', updateNilaiPreview);
        nilaiInput.addEventListener('change', updateNilaiPreview);

        // Form validation
        document.getElementById('nilaiForm').addEventListener('submit', function(e) {
            const nilai = parseFloat(nilaiInput.value);

            if (isNaN(nilai) || nilai < 0 || nilai > 100) {
                e.preventDefault();
                nilaiInput.classList.add('error');
                nilaiError.textContent = 'Nilai harus antara 0 - 100';
                nilaiInput.focus();
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
                window.location.href = 'nilai.php';
            }
        });
    </script>
</body>

</html>