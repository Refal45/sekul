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

$error = '';
$success = '';

// Proses tambah admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_input = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $nama_petugas = trim($_POST['nama_petugas'] ?? '');
    $nip = trim($_POST['nip'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');

    // Validasi
    if (empty($username_input) || empty($password) || empty($nama_petugas)) {
        $error = 'Username, Password, dan Nama Lengkap harus diisi!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan Konfirmasi Password tidak cocok!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        try {
            // Cek apakah username sudah ada
            $stmt = $pdo->prepare("SELECT id_petugas FROM petugas WHERE username = ?");
            $stmt->execute([$username_input]);

            if ($stmt->fetch()) {
                $error = 'Username sudah digunakan!';
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert admin baru
                $stmt = $pdo->prepare("INSERT INTO petugas (username, password, nama_petugas, nip, no_hp, role) VALUES (?, ?, ?, ?, ?, 'admin')");
                $stmt->execute([$username_input, $hashed_password, $nama_petugas, $nip, $no_hp]);

                $_SESSION['flash'] = "success";
                header("Location: admin.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Tambah Admin - Sistem Sekolah</title>
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

        /* ===== Form Styles ===== */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
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

        .form-input.error {
            border-color: #ef4444;
        }

        .form-input.success {
            border-color: #10b981;
        }

        .form-help {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }

        .form-error {
            font-size: 0.75rem;
            color: #ef4444;
            margin-top: 0.25rem;
        }

        /* ===== Button Styles ===== */
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

        /* ===== Error Message ===== */
        .error-message {
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        /* ===== Success Message ===== */
        .success-message {
            background-color: #dcfce7;
            border: 1px solid #bbf7d0;
            color: #166534;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        /* ===== Sidebar Sections ===== */
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

        /* Logout Wrapper */
        .logout-wrapper {
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #1e3a8a;
            padding-bottom: 4rem;
            display: flex;
            justify-content: center;
        }

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

        /* Password strength indicator */
        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 0.5rem;
            transition: all 0.3s ease;
        }

        .strength-weak {
            background-color: #ef4444;
            width: 25%;
        }

        .strength-medium {
            background-color: #f59e0b;
            width: 50%;
        }

        .strength-strong {
            background-color: #10b981;
            width: 100%;
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
                    <a href="admin.php" class="bg-blue-600 text-white rounded-md active">
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
                        <div class="text-sm font-bold truncate">Tambah Admin</div>
                        <div class="text-slate-500 text-xs truncate">Tambah akun admin baru</div>
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
                            <i class="fa-solid fa-user-plus text-green-600"></i> Tambah Admin Baru
                        </h1>
                        <p class="text-slate-500 mt-1">Tambah akun administrator baru untuk sistem sekolah</p>
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
                    <div class="flex items-center gap-2 text-sm text-slate-600 mb-6">
                        <a href="admin.php" class="hover:text-blue-600 transition-colors">
                            <i class="fa-solid fa-users-gear mr-1"></i>Data Admin
                        </a>
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                        <span class="text-blue-600 font-medium">Tambah Admin</span>
                    </div>

                    <?php if ($error): ?>
                        <div class="error-message">
                            <i class="fa-solid fa-triangle-exclamation mr-2"></i><?= htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="success-message">
                            <i class="fa-solid fa-circle-check mr-2"></i><?= htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Form Tambah Admin -->
                    <div class="card p-6">
                        <form method="POST" action="" class="max-w-2xl">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Kolom Kiri -->
                                <div class="space-y-4">
                                    <div class="form-group">
                                        <label for="username" class="form-label">
                                            <i class="fa-solid fa-user mr-2"></i>Username *
                                        </label>
                                        <input type="text" id="username" name="username"
                                            value="<?= htmlspecialchars($_POST['username'] ?? ''); ?>"
                                            class="form-input <?= $error && empty($_POST['username']) ? 'error' : '' ?>"
                                            placeholder="Masukkan username" required>
                                        <div class="form-help">Username harus unik dan tidak boleh mengandung spasi</div>
                                    </div>

                                    <div class="form-group">
                                        <label for="nama_petugas" class="form-label">
                                            <i class="fa-solid fa-signature mr-2"></i>Nama Lengkap *
                                        </label>
                                        <input type="text" id="nama_petugas" name="nama_petugas"
                                            value="<?= htmlspecialchars($_POST['nama_petugas'] ?? ''); ?>"
                                            class="form-input <?= $error && empty($_POST['nama_petugas']) ? 'error' : '' ?>"
                                            placeholder="Masukkan nama lengkap" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="nip" class="form-label">
                                            <i class="fa-solid fa-id-card mr-2"></i>NIP
                                        </label>
                                        <input type="text" id="nip" name="nip"
                                            value="<?= htmlspecialchars($_POST['nip'] ?? ''); ?>"
                                            class="form-input"
                                            placeholder="Masukkan NIP (opsional)">
                                    </div>
                                </div>

                                <!-- Kolom Kanan -->
                                <div class="space-y-4">
                                    <div class="form-group">
                                        <label for="password" class="form-label">
                                            <i class="fa-solid fa-lock mr-2"></i>Password *
                                        </label>
                                        <input type="password" id="password" name="password"
                                            class="form-input <?= $error && (empty($_POST['password']) || isset($_POST['password']) && strlen($_POST['password']) < 6) ? 'error' : '' ?>"
                                            placeholder="Masukkan password" required>
                                        <div id="passwordStrength" class="password-strength"></div>
                                        <div class="form-help">Password minimal 6 karakter</div>
                                    </div>

                                    <div class="form-group">
                                        <label for="confirm_password" class="form-label">
                                            <i class="fa-solid fa-lock mr-2"></i>Konfirmasi Password *
                                        </label>
                                        <input type="password" id="confirm_password" name="confirm_password"
                                            class="form-input <?= $error && isset($_POST['password']) && $_POST['password'] !== ($_POST['confirm_password'] ?? '') ? 'error' : '' ?>"
                                            placeholder="Ulangi password" required>
                                        <div id="passwordMatch" class="form-help"></div>
                                    </div>

                                    <div class="form-group">
                                        <label for="no_hp" class="form-label">
                                            <i class="fa-solid fa-phone mr-2"></i>Nomor HP
                                        </label>
                                        <input type="tel" id="no_hp" name="no_hp"
                                            value="<?= htmlspecialchars($_POST['no_hp'] ?? ''); ?>"
                                            class="form-input"
                                            placeholder="Masukkan nomor HP (opsional)">
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="flex flex-col sm:flex-row gap-3 mt-8 pt-6 border-t border-slate-200">
                                <button type="submit" class="btn btn-success flex-1">
                                    <i class="fa-solid fa-save mr-2"></i> Simpan Admin
                                </button>
                                <a href="admin.php" class="btn btn-secondary flex-1 text-center">
                                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                                </a>
                                <button type="reset" class="btn btn-warning flex-1">
                                    <i class="fa-solid fa-rotate mr-2"></i> Reset Form
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Informasi -->
                    <div class="card p-6 mt-6">
                        <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-circle-info text-blue-600"></i> Informasi Tambahan
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-slate-700">
                            <div class="flex items-start gap-3">
                                <i class="fa-solid fa-shield-alt text-green-600 mt-1"></i>
                                <div>
                                    <strong>Hak Akses Admin</strong>
                                    <p class="text-slate-600 mt-1">Admin memiliki akses penuh untuk mengelola semua data sistem sekolah</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <i class="fa-solid fa-key text-yellow-600 mt-1"></i>
                                <div>
                                    <strong>Keamanan Password</strong>
                                    <p class="text-slate-600 mt-1">Gunakan password yang kuat dengan kombinasi huruf, angka, dan simbol</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <footer class="mt-6 text-center text-sm text-slate-500 select-none pb-4">
                        &copy; <?= date('Y'); ?> Sekolah - Admin Management
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

        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const passwordStrength = document.getElementById('passwordStrength');
        const passwordMatch = document.getElementById('passwordMatch');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;

            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/\d/)) strength++;
            if (password.match(/[^a-zA-Z\d]/)) strength++;

            passwordStrength.className = 'password-strength';
            if (password.length > 0) {
                if (strength <= 1) {
                    passwordStrength.classList.add('strength-weak');
                } else if (strength <= 3) {
                    passwordStrength.classList.add('strength-medium');
                } else {
                    passwordStrength.classList.add('strength-strong');
                }
            }
        });

        // Password confirmation check
        confirmPasswordInput.addEventListener('input', function() {
            const password = passwordInput.value;
            const confirmPassword = this.value;

            if (confirmPassword.length > 0) {
                if (password === confirmPassword) {
                    passwordMatch.innerHTML = '<i class="fa-solid fa-check text-green-600 mr-1"></i>Password cocok';
                    passwordMatch.className = 'form-help text-green-600';
                } else {
                    passwordMatch.innerHTML = '<i class="fa-solid fa-xmark text-red-600 mr-1"></i>Password tidak cocok';
                    passwordMatch.className = 'form-help text-red-600';
                }
            } else {
                passwordMatch.textContent = '';
            }
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            if (password.length < 6) {
                e.preventDefault();
                alert('Password minimal 6 karakter!');
                passwordInput.focus();
                return;
            }

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan Konfirmasi Password tidak cocok!');
                confirmPasswordInput.focus();
                return;
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
                window.location.href = 'admin.php';
            }
        });
    </script>
</body>

</html>