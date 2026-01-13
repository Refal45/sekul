<?php
session_start();
require_once "../koneksi.php"; // koneksi PDO

// Pastikan hanya guru yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    header("Location: login-admin.php");
    exit;
}

$username    = $_SESSION['username'] ?? 'Unknown';
$namaLengkap = $_SESSION['nama_lengkap'] ?? 'Guru';
$role        = $_SESSION['role'] ?? 'user';

// Inisialisasi variabel dengan nilai default
$totalSiswa = $totalKelas = $totalMapel = $totalGuru = 0;
$mapel = $rataNilai = $warna = $bulan = $hadir = $tahun = [];
$labelGender = $dataGender = $labelGuruMapel = $dataGuruMapel = [];
$recentSiswa = $recentKehadiran = $nilaiTertinggi = [];
$jadwalHariIni = $recentGuru = [];
$error = null;

// ===== Statistik =====
try {
    // Total semua siswa
    $sqlTotalSiswa = "SELECT COUNT(*) as total_siswa FROM siswa";
    $stmt = $pdo->prepare($sqlTotalSiswa);
    $stmt->execute();
    $totalSiswa = $stmt->fetchColumn();

    // Total semua kelas
    $sqlTotalKelas = "SELECT COUNT(*) as total_kelas FROM kelas";
    $stmt = $pdo->prepare($sqlTotalKelas);
    $stmt->execute();
    $totalKelas = $stmt->fetchColumn();

    // Total semua mapel
    $sqlTotalMapel = "SELECT COUNT(*) as total_mapel FROM mata_pelajaran";
    $stmt = $pdo->prepare($sqlTotalMapel);
    $stmt->execute();
    $totalMapel = $stmt->fetchColumn();

    // Total guru (termasuk admin)
    $sqlTotalGuru = "SELECT COUNT(*) as total_guru FROM petugas WHERE role IN ('guru')";
    $stmt = $pdo->prepare($sqlTotalGuru);
    $stmt->execute();
    $totalGuru = $stmt->fetchColumn();

    // ===== Grafik Nilai rata-rata per Mapel (semua mapel) =====
    $sql = "SELECT m.nama_mapel, AVG(n.nilai) as rata_nilai 
            FROM nilai n 
            JOIN mata_pelajaran m ON n.id_mapel = m.id_mapel 
            GROUP BY n.id_mapel";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $mapel[] = $row['nama_mapel'];
        $nilai = round($row['rata_nilai'], 2);
        $rataNilai[] = $nilai;

        // ===== Menentukan warna berdasarkan nilai sesuai standar Indonesia =====
        if ($nilai >= 85) {
            $warna[] = '#16a34a'; // A hijau
        } elseif ($nilai >= 70) {
            $warna[] = '#ca8a04'; // B kuning
        } elseif ($nilai >= 55) {
            $warna[] = '#f97316'; // C oranye
        } elseif ($nilai >= 40) {
            $warna[] = '#f43f5e'; // D merah muda
        } else {
            $warna[] = '#b91c1c'; // E merah gelap
        }
    }

    // ===== Grafik Kehadiran per Bulan & Tahun (semua kehadiran) =====
    $sql2 = "SELECT YEAR(tanggal) as tahun, MONTH(tanggal) as bulan, COUNT(*) as total_hadir 
             FROM kehadiran 
             WHERE status='Hadir' 
             GROUP BY YEAR(tanggal), MONTH(tanggal) 
             ORDER BY tahun, bulan";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute();

    while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        $tahun[] = $row['tahun'];
        $bulan[] = date("M", mktime(0, 0, 0, $row['bulan'], 10)) . " " . $row['tahun'];
        $hadir[] = $row['total_hadir'];
    }

    // ===== Grafik Pie Jenis Kelamin (semua siswa) =====
    $sql3 = "SELECT jenis_kelamin, COUNT(*) as total 
             FROM siswa 
             GROUP BY jenis_kelamin";
    $stmt3 = $pdo->prepare($sql3);
    $stmt3->execute();

    while ($row = $stmt3->fetch(PDO::FETCH_ASSOC)) {
        $labelGender[] = ($row['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan');
        $dataGender[] = $row['total'];
    }

    // ===== Grafik Doughnut Mapel (semua mapel) =====
    $sql4 = "SELECT nama_mapel, 1 as total_kelas 
             FROM mata_pelajaran 
             LIMIT 8";
    $stmt4 = $pdo->prepare($sql4);
    $stmt4->execute();

    while ($row = $stmt4->fetch(PDO::FETCH_ASSOC)) {
        $labelGuruMapel[] = $row['nama_mapel'];
        $dataGuruMapel[] = $row['total_kelas'];
    }

    // ===== Data Siswa terbaru (semua siswa) =====
    $sqlRecentSiswa = "SELECT nama_siswa, nis 
                       FROM siswa 
                       ORDER BY id_siswa DESC 
                       LIMIT 5";
    $stmt = $pdo->prepare($sqlRecentSiswa);
    $stmt->execute();
    $recentSiswa = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ===== Data Guru terbaru =====
    $sqlRecentGuru = "SELECT nama_petugas, username, role 
                      FROM petugas 
                      WHERE role IN ('guru')
                      ORDER BY id_petugas DESC 
                      LIMIT 5";
    $stmt = $pdo->prepare($sqlRecentGuru);
    $stmt->execute();
    $recentGuru = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ===== Kehadiran terbaru (semua kehadiran) =====
    $sqlRecentKehadiran = "SELECT s.nama_siswa, k.tanggal, k.status 
                           FROM kehadiran k 
                           JOIN siswa s ON k.id_siswa = s.id_siswa
                           ORDER BY k.id_kehadiran DESC 
                           LIMIT 5";
    $stmt = $pdo->prepare($sqlRecentKehadiran);
    $stmt->execute();
    $recentKehadiran = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ===== Nilai Tertinggi per Mapel (semua nilai) =====
    $sqlNilaiTertinggi = "SELECT m.nama_mapel, MAX(n.nilai) as nilai_max
                          FROM nilai n
                          JOIN mata_pelajaran m ON n.id_mapel = m.id_mapel
                          GROUP BY n.id_mapel";
    $stmt = $pdo->prepare($sqlNilaiTertinggi);
    $stmt->execute();
    $nilaiTertinggi = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ===== JADWAL KESELURUHAN (untuk guru ini) =====
    $sqlJadwal = "SELECT j.id_jadwal, j.hari, j.jam_mulai, j.jam_selesai, 
                         k.tingkat, k.nama_kelas, m.nama_mapel, p.nama_petugas
                  FROM jadwal j
                  JOIN kelas k ON j.id_kelas = k.id_kelas
                  JOIN mata_pelajaran m ON j.id_mapel = m.id_mapel
                  JOIN petugas p ON j.id_petugas = p.id_petugas
                  WHERE j.id_petugas = ?
                  ORDER BY 
                    FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'),
                    j.jam_mulai";
    $stmt = $pdo->prepare($sqlJadwal);
    $stmt->execute([$_SESSION['id_petugas']]);
    $jadwal = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ===== DATA KELAS =====
    $sqlKelas = "SELECT k.id_kelas, k.tingkat, k.nama_kelas, 
                        COUNT(s.id_siswa) as jumlah_siswa
                 FROM kelas k
                 LEFT JOIN siswa s ON k.id_kelas = s.id_kelas
                 GROUP BY k.id_kelas, k.tingkat, k.nama_kelas
                 ORDER BY k.tingkat, k.nama_kelas";
    $stmt = $pdo->prepare($sqlKelas);
    $stmt->execute();
    $kelas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log error dan tampilkan pesan yang ramah pengguna
    error_log("Database error: " . $e->getMessage());
    $error = "Terjadi kesalahan dalam memuat data. Silakan coba lagi nanti.";
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard Guru - Sistem Sekolah</title>
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
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-hadir {
            background-color: #dcfce7;
            color: #166534;
        }

        .badge-izin {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-sakit {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .badge-alpa {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .badge-admin {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-guru {
            background-color: #dbeafe;
            color: #1e40af;
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

        /* Loading states */
        .loading {
            opacity: 0.7;
            pointer-events: none;
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

        /* Jadwal hari styling */
        .hari-section {
            margin-bottom: 1.5rem;
        }

        .hari-header {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 8px 8px 0 0;
            margin-bottom: 0;
        }

        .hari-title {
            font-weight: 600;
            font-size: 1rem;
        }

        /* ===== PERBAIKAN LAYOUT CARD UNTUK EDGE ===== */
        .card-grid-fix {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
            width: 100%;
        }

        .card-fix {
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 300px;
        }

        .card-fix .table-scroll {
            flex: 1;
            overflow-x: auto;
        }

        .card-fix table {
            min-width: 100%;
            table-layout: fixed;
        }

        .card-fix th,
        .card-fix td {
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* Perbaikan khusus untuk Edge */
        @supports (-ms-ime-align: auto) {
            .card-grid-fix {
                display: flex;
                flex-wrap: wrap;
            }

            .card-fix {
                flex: 1 1 300px;
                margin-bottom: 1rem;
            }
        }

        /* Perbaikan untuk browser lama */
        @media all and (-ms-high-contrast: none),
        (-ms-high-contrast: active) {
            .card-grid-fix {
                display: flex;
                flex-wrap: wrap;
            }

            .card-fix {
                flex: 1 1 300px;
                margin: 0.5rem;
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
                    <a href="index-2.php" class="bg-blue-600 text-white rounded-md active">
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
                    <a href="jadwal.php" class="rounded-md hover:bg-blue-700 hover:text-white">
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
                        <div class="text-sm font-bold truncate">Dashboard</div>
                        <div class="text-slate-500 text-xs truncate">Ringkasan data mengajar</div>
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
                            <i class="fa-solid fa-chalkboard-teacher text-blue-600"></i> Dashboard Guru
                        </h1>
                        <p class="text-slate-500 mt-1">Ringkasan data mengajar & performa</p>
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
                            <p class="text-sm mt-2">Detail: <?= $e->getMessage() ?? 'Unknown error'; ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Info Cards - IMPROVED -->
                    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-4 mb-6 auto-rows-fr">
                        <div class="card stat-card bg-gradient-to-br from-blue-50 to-blue-100 border-blue-200">
                            <div class="stat-icon text-blue-600">
                                <i class="fa-solid fa-user-graduate"></i>
                            </div>
                            <h3 class="stat-label">Total Siswa</h3>
                            <p class="stat-value text-blue-700"><?= $totalSiswa; ?></p>
                        </div>
                        <div class="card stat-card bg-gradient-to-br from-green-50 to-green-100 border-green-200">
                            <div class="stat-icon text-green-600">
                                <i class="fa-solid fa-door-closed"></i>
                            </div>
                            <h3 class="stat-label">Total Kelas</h3>
                            <p class="stat-value text-green-600"><?= $totalKelas; ?></p>
                        </div>
                        <div class="card stat-card bg-gradient-to-br from-red-50 to-red-100 border-red-200">
                            <div class="stat-icon text-red-500">
                                <i class="fa-solid fa-book"></i>
                            </div>
                            <h3 class="stat-label">Mata Pelajaran</h3>
                            <p class="stat-value text-red-500"><?= $totalMapel; ?></p>
                        </div>
                        <div class="card stat-card bg-gradient-to-br from-purple-50 to-purple-100 border-purple-200">
                            <div class="stat-icon text-purple-600">
                                <i class="fa-solid fa-user-tie"></i>
                            </div>
                            <h3 class="stat-label">Total Guru</h3>
                            <p class="stat-value text-purple-600"><?= $totalGuru; ?></p>
                        </div>
                    </div>

                    <!-- Jadwal Mengajar (Keseluruhan) -->
                    <div class="card mb-6 overflow-hidden">
                        <div class="hari-header">
                            <h3 class="hari-title flex items-center gap-2">
                                <i class="fa-solid fa-calendar-alt"></i> Jadwal Mengajar Keseluruhan
                            </h3>
                        </div>
                        <div class="p-4">
                            <?php if (!empty($jadwal)): ?>
                                <?php
                                $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                                $jadwalPerHari = [];

                                // Kelompokkan jadwal per hari
                                foreach ($jadwal as $j) {
                                    $hari = $j['hari'];
                                    if (!isset($jadwalPerHari[$hari])) {
                                        $jadwalPerHari[$hari] = [];
                                    }
                                    $jadwalPerHari[$hari][] = $j;
                                }
                                ?>

                                <?php foreach ($hariList as $hari): ?>
                                    <?php if (isset($jadwalPerHari[$hari])): ?>
                                        <div class="hari-section">
                                            <h4 class="font-semibold text-lg mb-3 text-slate-700 border-b pb-2">
                                                <i class="fa-solid fa-calendar-day text-blue-500 mr-2"></i><?= $hari ?>
                                            </h4>
                                            <div class="table-scroll">
                                                <table class="w-full text-left border-collapse">
                                                    <thead>
                                                        <tr class="bg-slate-50">
                                                            <th class="py-2 px-3 text-slate-600 text-sm">Kelas</th>
                                                            <th class="py-2 px-3 text-slate-600 text-sm">Mata Pelajaran</th>
                                                            <th class="py-2 px-3 text-slate-600 text-sm">Jam</th>
                                                            <th class="py-2 px-3 text-slate-600 text-sm">Guru</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($jadwalPerHari[$hari] as $j): ?>
                                                            <tr class="border-t hover:bg-slate-50">
                                                                <td class="py-2 px-3 text-sm"><?= htmlspecialchars($j['tingkat'] . ' - ' . $j['nama_kelas']); ?></td>
                                                                <td class="py-2 px-3 text-sm"><?= htmlspecialchars($j['nama_mapel']); ?></td>
                                                                <td class="py-2 px-3 text-sm"><?= htmlspecialchars($j['jam_mulai'] . ' - ' . $j['jam_selesai']); ?></td>
                                                                <td class="py-2 px-3 text-sm"><?= htmlspecialchars($j['nama_petugas']); ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state py-8">
                                    <i class="fa-solid fa-calendar-times"></i>
                                    <p>Tidak ada jadwal mengajar</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Data Kelas -->
                    <div class="card mb-6 overflow-hidden">
                        <div class="hari-header">
                            <h3 class="hari-title flex items-center gap-2">
                                <i class="fa-solid fa-door-open"></i> Data Kelas
                            </h3>
                        </div>
                        <div class="p-4">
                            <?php if (!empty($kelas)): ?>
                                <div class="table-scroll">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="bg-slate-50">
                                                <th class="py-2 px-3 text-slate-600 text-sm">Tingkat</th>
                                                <th class="py-2 px-3 text-slate-600 text-sm">Nama Kelas</th>
                                                <th class="py-2 px-3 text-slate-600 text-sm">Jumlah Siswa</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($kelas as $k): ?>
                                                <tr class="border-t hover:bg-slate-50">
                                                    <td class="py-2 px-3 text-sm font-medium"><?= htmlspecialchars($k['tingkat']); ?></td>
                                                    <td class="py-2 px-3 text-sm"><?= htmlspecialchars($k['nama_kelas']); ?></td>
                                                    <td class="py-2 px-3 text-sm">
                                                        <span class="badge bg-blue-100 text-blue-700">
                                                            <?= htmlspecialchars($k['jumlah_siswa']); ?> Siswa
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="empty-state py-8">
                                    <i class="fa-solid fa-door-closed"></i>
                                    <p>Tidak ada data kelas</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Grafik - IMPROVED CARDS -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
                        <div class="card p-4 flex flex-col">
                            <h3 class="mb-3 font-semibold text-slate-700 flex items-center gap-2 text-sm lg:text-base">
                                <i class="fa-solid fa-chart-column text-blue-500"></i> Rata-rata Nilai per Mapel
                            </h3>
                            <div class="chart-container">
                                <?php if (!empty($mapel)): ?>
                                    <canvas id="barChart"></canvas>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fa-solid fa-chart-bar"></i>
                                        <p>Tidak ada data nilai</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card p-4 flex flex-col">
                            <h3 class="mb-3 font-semibold text-slate-700 flex items-center gap-2 text-sm lg:text-base">
                                <i class="fa-solid fa-chart-bar text-green-500"></i> Kehadiran Bulanan
                            </h3>
                            <div class="chart-container">
                                <?php if (!empty($bulan)): ?>
                                    <canvas id="barChartKehadiran"></canvas>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fa-solid fa-calendar-check"></i>
                                        <p>Tidak ada data kehadiran</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card p-4 flex flex-col">
                            <h3 class="mb-3 font-semibold text-slate-700 flex items-center gap-2 text-sm lg:text-base">
                                <i class="fa-solid fa-chart-pie text-purple-500"></i> Distribusi Siswa (Jenis Kelamin)
                            </h3>
                            <div class="chart-container">
                                <?php if (!empty($labelGender)): ?>
                                    <canvas id="pieChart"></canvas>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fa-solid fa-users"></i>
                                        <p>Tidak ada data siswa</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card p-4 flex flex-col">
                            <h3 class="mb-3 font-semibold text-slate-700 flex items-center gap-2 text-sm lg:text-base">
                                <i class="fa-solid fa-chart-donut text-yellow-500"></i> Mata Pelajaran
                            </h3>
                            <div class="chart-container">
                                <?php if (!empty($labelGuruMapel)): ?>
                                    <canvas id="doughnutChart"></canvas>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fa-solid fa-book"></i>
                                        <p>Tidak ada data mata pelajaran</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel - IMPROVED CARDS dengan perbaikan untuk Edge -->
                    <div class="card-grid-fix mb-6">
                        <div class="card card-fix p-4">
                            <h3 class="font-semibold mb-3 text-slate-700 flex items-center gap-2 text-sm lg:text-base">
                                <i class="fa-solid fa-user-plus text-blue-500"></i> Siswa Terbaru
                            </h3>
                            <div class="table-scroll">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-slate-50">
                                            <th class="py-2 px-3 text-slate-600 text-xs lg:text-sm w-1/2">Nama</th>
                                            <th class="py-2 px-3 text-slate-600 text-xs lg:text-sm w-1/2">NIS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($recentSiswa)): ?>
                                            <?php foreach ($recentSiswa as $s): ?>
                                                <tr class="border-t hover:bg-slate-50">
                                                    <td class="py-2 px-3 text-xs lg:text-sm truncate"><?= htmlspecialchars($s['nama_siswa']); ?></td>
                                                    <td class="py-2 px-3 text-xs lg:text-sm truncate"><?= htmlspecialchars($s['nis']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="2" class="py-2 px-3 text-center text-xs lg:text-sm text-slate-500">
                                                    <div class="empty-state py-4">
                                                        <i class="fa-solid fa-user-slash"></i>
                                                        <p>Tidak ada data siswa</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card card-fix p-4">
                            <h3 class="font-semibold mb-3 text-slate-700 flex items-center gap-2 text-sm lg:text-base">
                                <i class="fa-solid fa-user-tie text-purple-500"></i> Guru Terbaru
                            </h3>
                            <div class="table-scroll">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-slate-50">
                                            <th class="py-2 px-3 text-slate-600 text-xs lg:text-sm w-2/5">Nama</th>
                                            <th class="py-2 px-3 text-slate-600 text-xs lg:text-sm w-2/5">Username</th>
                                            <th class="py-2 px-3 text-slate-600 text-xs lg:text-sm w-1/5">Role</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($recentGuru)): ?>
                                            <?php foreach ($recentGuru as $g): ?>
                                                <tr class="border-t hover:bg-slate-50">
                                                    <td class="py-2 px-3 text-xs lg:text-sm truncate"><?= htmlspecialchars($g['nama_petugas']); ?></td>
                                                    <td class="py-2 px-3 text-xs lg:text-sm truncate"><?= htmlspecialchars($g['username']); ?></td>
                                                    <td class="py-2 px-3 text-xs lg:text-sm">
                                                        <?php
                                                        $role = strtolower($g['role']);
                                                        $badgeClass = match ($role) {
                                                            'admin' => 'badge badge-admin',
                                                            'guru'  => 'badge badge-guru',
                                                            default => 'badge bg-slate-100 text-slate-700'
                                                        };
                                                        ?>
                                                        <span class="<?= $badgeClass; ?> text-xs">
                                                            <?= htmlspecialchars($g['role']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="py-2 px-3 text-center text-xs lg:text-sm text-slate-500">
                                                    <div class="empty-state py-4">
                                                        <i class="fa-solid fa-user-slash"></i>
                                                        <p>Tidak ada data guru</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card card-fix p-4">
                            <h3 class="font-semibold mb-3 text-slate-700 flex items-center gap-2 text-sm lg:text-base">
                                <i class="fa-solid fa-user-check text-green-500"></i> Kehadiran Terbaru
                            </h3>
                            <div class="table-scroll">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-slate-50">
                                            <th class="py-2 px-3 text-slate-600 text-xs lg:text-sm w-2/5">Nama</th>
                                            <th class="py-2 px-3 text-slate-600 text-xs lg:text-sm w-2/5">Tanggal</th>
                                            <th class="py-2 px-3 text-slate-600 text-xs lg:text-sm w-1/5">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($recentKehadiran)): ?>
                                            <?php foreach ($recentKehadiran as $k): ?>
                                                <tr class="border-t hover:bg-slate-50">
                                                    <td class="py-2 px-3 text-xs lg:text-sm truncate"><?= htmlspecialchars($k['nama_siswa']); ?></td>
                                                    <td class="py-2 px-3 text-xs lg:text-sm truncate"><?= htmlspecialchars($k['tanggal']); ?></td>
                                                    <td class="py-2 px-3 text-xs lg:text-sm">
                                                        <?php
                                                        $status = strtolower($k['status']);
                                                        $badgeClass = match ($status) {
                                                            'hadir' => 'badge badge-hadir',
                                                            'izin'  => 'badge badge-izin',
                                                            'sakit' => 'badge badge-sakit',
                                                            'alpa'  => 'badge badge-alpa',
                                                            default => 'badge bg-slate-100 text-slate-700'
                                                        };
                                                        ?>
                                                        <span class="<?= $badgeClass; ?> text-xs">
                                                            <?= htmlspecialchars($k['status']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="py-2 px-3 text-center text-xs lg:text-sm text-slate-500">
                                                    <div class="empty-state py-4">
                                                        <i class="fa-solid fa-clipboard-question"></i>
                                                        <p>Tidak ada data kehadiran</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Nilai Tertinggi per Mapel -->
                    <div class="card mb-6">
                        <div class="hari-header">
                            <h3 class="hari-title flex items-center gap-2">
                                <i class="fa-solid fa-medal text-yellow-500"></i> Nilai Tertinggi per Mapel
                            </h3>
                        </div>
                        <div class="p-4">
                            <div class="table-scroll">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-slate-50">
                                            <th class="py-2 px-3 text-slate-600 text-xs lg:text-sm">Mapel</th>
                                            <th class="py-2 px-3 text-slate-600 text-xs lg:text-sm">Nilai Tertinggi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($nilaiTertinggi)): ?>
                                            <?php foreach ($nilaiTertinggi as $n): ?>
                                                <tr class="border-t hover:bg-slate-50">
                                                    <td class="py-2 px-3 text-xs lg:text-sm"><?= htmlspecialchars($n['nama_mapel']); ?></td>
                                                    <td class="py-2 px-3 text-xs lg:text-sm font-bold text-blue-700"><?= htmlspecialchars($n['nilai_max']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="2" class="py-2 px-3 text-center text-xs lg:text-sm text-slate-500">
                                                    <div class="empty-state py-4">
                                                        <i class="fa-solid fa-chart-line"></i>
                                                        <p>Tidak ada data nilai</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <footer class="mt-6 text-center text-sm text-slate-500 select-none pb-4">
                        &copy; <?= date('Y'); ?> Sekolah - Dashboard Guru
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
        Chart.defaults.plugins.legend.labels.usePointStyle = true;

        // Bar Chart - Nilai Rata-rata
        <?php if (!empty($mapel)): ?>
            new Chart(document.getElementById('barChart'), {
                type: 'bar',
                data: {
                    labels: <?= json_encode($mapel); ?>,
                    datasets: [{
                        label: 'Rata-rata Nilai',
                        data: <?= json_encode($rataNilai); ?>,
                        backgroundColor: <?= json_encode($warna); ?>,
                        borderRadius: 6,
                        borderWidth: 1,
                        borderColor: '#e2e8f0'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Nilai: ${context.parsed.y}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            grid: {
                                color: 'rgba(226, 232, 240, 0.5)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value;
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        <?php endif; ?>

        // Bar Chart - Kehadiran Bulanan
        <?php if (!empty($bulan)): ?>
            new Chart(document.getElementById('barChartKehadiran'), {
                type: 'bar',
                data: {
                    labels: <?= json_encode($bulan); ?>,
                    datasets: [{
                        label: 'Jumlah Kehadiran',
                        data: <?= json_encode($hadir); ?>,
                        backgroundColor: '#10b981',
                        borderRadius: 6,
                        borderWidth: 1,
                        borderColor: '#059669'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(226, 232, 240, 0.5)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        <?php endif; ?>

        // Pie Chart
        <?php if (!empty($labelGender)): ?>
            const labels = <?= json_encode($labelGender); ?>;
            const data = <?= json_encode($dataGender); ?>;

            const colors = labels.map(label => {
                if (label.toLowerCase().includes("laki")) {
                    return '#3b82f6'; // Biru untuk Laki-laki
                } else if (label.toLowerCase().includes("perempuan")) {
                    return '#f472b6'; // Pink untuk Perempuan
                } else {
                    return '#d1d5db'; // Abu-abu default kalau ada kategori lain
                }
            });

            new Chart(document.getElementById('pieChart'), {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors,
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true
                            }
                        }
                    }
                }
            });
        <?php endif; ?>

        // Doughnut Chart
        <?php if (!empty($labelGuruMapel)): ?>
            new Chart(document.getElementById('doughnutChart'), {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($labelGuruMapel); ?>,
                    datasets: [{
                        data: <?= json_encode($dataGuruMapel); ?>,
                        backgroundColor: ['#f59e0b', '#10b981', '#3b82f6', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        <?php endif; ?>

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

        // Auto-refresh data every 5 minutes
        setInterval(() => {
            // You can implement AJAX refresh here if needed
            console.log('Auto-refresh triggered at: ' + new Date().toLocaleString());
        }, 300000); // 5 minutes

        // Print functionality
        function printDashboard() {
            window.print();
        }

        // Add keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Ctrl+P for print
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                printDashboard();
            }

            // Ctrl+Shift+L for logout
            if (e.ctrlKey && e.shiftKey && e.key === 'L') {
                e.preventDefault();
                if (confirm('Yakin ingin logout?')) {
                    window.location.href = 'logout.php';
                }
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