<?php
session_start();
require '../koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['id_siswa'])) {
    header("Location: login-siswa.php");
    exit;
}

// Fungsi untuk mendapatkan data dengan aman
function getData($pdo, $sql, $params = [])
{
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

function getAllData($pdo, $sql, $params = [])
{
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return [];
    }
}

// Ambil data siswa
$id_siswa = $_SESSION['id_siswa'];
$siswa = getData(
    $pdo,
    "SELECT s.*, k.nama_kelas FROM siswa s 
     JOIN kelas k ON s.id_kelas = k.id_kelas 
     WHERE s.id_siswa = ?",
    [$id_siswa]
);

if (!$siswa) {
    $_SESSION['error'] = "Data siswa tidak ditemukan";
    header("Location: login-siswa.php");
    exit;
}

// Ambil data statistik untuk dashboard

// 1. Nilai Rata-rata Semester 1
$nilai_data_sem1 = getData(
    $pdo,
    "SELECT AVG(nilai) as rata_rata FROM nilai 
     WHERE id_siswa = ? AND semester = '1'",
    [$id_siswa]
);
$rata_rata_nilai_sem1 = $nilai_data_sem1 && $nilai_data_sem1['rata_rata'] ?
    round($nilai_data_sem1['rata_rata'], 1) : 0;

// 2. Nilai Rata-rata Semester 2
$nilai_data_sem2 = getData(
    $pdo,
    "SELECT AVG(nilai) as rata_rata FROM nilai 
     WHERE id_siswa = ? AND semester = '2'",
    [$id_siswa]
);
$rata_rata_nilai_sem2 = $nilai_data_sem2 && $nilai_data_sem2['rata_rata'] ?
    round($nilai_data_sem2['rata_rata'], 1) : 0;

// 3. Nilai Rata-rata Keseluruhan
$nilai_data_all = getData(
    $pdo,
    "SELECT AVG(nilai) as rata_rata FROM nilai 
     WHERE id_siswa = ?",
    [$id_siswa]
);
$rata_rata_nilai_all = $nilai_data_all && $nilai_data_all['rata_rata'] ?
    round($nilai_data_all['rata_rata'], 1) : 0;

// 4. Persentase Kehadiran Bulan Ini
$bulan_ini = date('Y-m');
$kehadiran_data = getData(
    $pdo,
    "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) as hadir
     FROM kehadiran 
     WHERE id_siswa = ? 
     AND DATE_FORMAT(tanggal, '%Y-%m') = ?",
    [$id_siswa, $bulan_ini]
);
$persentase_kehadiran = $kehadiran_data && $kehadiran_data['total'] > 0 ?
    round(($kehadiran_data['hadir'] / $kehadiran_data['total']) * 100, 0) : 0;

// 5. Jumlah Jadwal Hari Ini
$hari_indonesia = [
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu',
    'Sunday' => 'Minggu'
];
$hari_sekarang = $hari_indonesia[date('l')];

$jadwal_data = getData(
    $pdo,
    "SELECT COUNT(*) as total_jadwal 
     FROM jadwal j
     JOIN kelas k ON j.id_kelas = k.id_kelas
     WHERE k.id_kelas = ? AND j.hari = ?",
    [$siswa['id_kelas'], $hari_sekarang]
);
$jumlah_jadwal = $jadwal_data ? $jadwal_data['total_jadwal'] : 0;

// 6. Jadwal Hari Ini (detail)
$jadwal_hari_ini = getAllData(
    $pdo,
    "SELECT j.*, m.nama_mapel, p.nama_petugas
     FROM jadwal j
     JOIN mata_pelajaran m ON j.id_mapel = m.id_mapel
     JOIN petugas p ON j.id_petugas = p.id_petugas
     WHERE j.id_kelas = ? AND j.hari = ?
     ORDER BY j.jam_mulai",
    [$siswa['id_kelas'], $hari_sekarang]
);

// 7. Nilai Terbaru Semester 1
$nilai_terbaru_sem1 = getData(
    $pdo,
    "SELECT n.*, m.nama_mapel 
     FROM nilai n
     JOIN mata_pelajaran m ON n.id_mapel = m.id_mapel
     WHERE n.id_siswa = ? AND n.semester = '1'
     ORDER BY n.id_nilai DESC
     LIMIT 1",
    [$id_siswa]
);

// 8. Nilai Terbaru Semester 2
$nilai_terbaru_sem2 = getData(
    $pdo,
    "SELECT n.*, m.nama_mapel 
     FROM nilai n
     JOIN mata_pelajaran m ON n.id_mapel = m.id_mapel
     WHERE n.id_siswa = ? AND n.semester = '2'
     ORDER BY n.id_nilai DESC
     LIMIT 1",
    [$id_siswa]
);

// 9. Aktivitas Terbaru (gabungan dari berbagai tabel)
$aktivitas = [];

// Aktivitas login
$aktivitas[] = [
    'icon' => 'bi-check-circle text-success',
    'text' => 'Login berhasil',
    'waktu' => 'Hari ini'
];

// Aktivitas nilai semester 1
if ($nilai_terbaru_sem1) {
    $aktivitas[] = [
        'icon' => 'bi-journal-text text-primary',
        'text' => 'Nilai ' . $nilai_terbaru_sem1['nama_mapel'] . ' (Semester 1) diperbarui',
        'waktu' => '2 hari lalu'
    ];
}

// Aktivitas nilai semester 2
if ($nilai_terbaru_sem2) {
    $aktivitas[] = [
        'icon' => 'bi-journal-text text-info',
        'text' => 'Nilai ' . $nilai_terbaru_sem2['nama_mapel'] . ' (Semester 2) diperbarui',
        'waktu' => '1 hari lalu'
    ];
}

// Aktivitas kehadiran terbaru
$kehadiran_terbaru = getData(
    $pdo,
    "SELECT k.*, m.nama_mapel
     FROM kehadiran k
     JOIN jadwal j ON k.id_jadwal = j.id_jadwal
     JOIN mata_pelajaran m ON j.id_mapel = m.id_mapel
     WHERE k.id_siswa = ?
     ORDER BY k.tanggal DESC, k.id_kehadiran DESC
     LIMIT 1",
    [$id_siswa]
);

if ($kehadiran_terbaru) {
    $status_text = $kehadiran_terbaru['status'];
    $aktivitas[] = [
        'icon' => 'bi-clipboard-check ' . ($kehadiran_terbaru['status'] == 'Hadir' ? 'text-success' : 'text-warning'),
        'text' => 'Presensi ' . $kehadiran_terbaru['nama_mapel'] . ' - ' . $status_text,
        'waktu' => '3 hari lalu'
    ];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa | SekolahKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #0d6efd;
            --primary-dark: #0a58ca;
            --secondary: #6c757d;
            --success: #198754;
            --info: #0dcaf0;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #212529;
        }

        body {
            margin: 0;
            padding: 0;
        }

        /* Header Fixed */
        .header-fixed {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1020;
            display: flex;
            align-items: center;
            padding: 0 1rem;
        }

        /* Sidebar */
        .sidebar {
            position: fixed !important;
            top: 60px !important;
            left: 0 !important;
            height: calc(100vh - 60px) !important;
            width: 250px !important;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding-top: 1rem !important;
            overflow-y: auto;
            transition: all 0.3s;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .sidebar .nav-link {
            color: white;
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 8px;
            transition: all 0.2s;
            font-weight: 500;
        }

        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }

        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            font-weight: 600;
        }

        .sidebar .nav-link i {
            width: 24px;
            text-align: center;
            margin-right: 10px;
        }

        /* Burger Menu */
        .burger-menu {
            display: none;
            background: none;
            border: none;
            color: var(--primary);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
            transition: all 0.3s;
        }

        .burger-menu:hover {
            color: var(--primary-dark);
        }

        /* Sidebar Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 60px;
            left: 0;
            width: 100%;
            height: calc(100vh - 60px);
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
        }

        /* Main Content */
        .main-content {
            margin-top: 60px;
            margin-left: 250px;
            background: #f8f9fa;
            min-height: calc(100vh - 60px);
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .welcome-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            overflow: hidden;
        }

        .stat-card {
            height: 100%;
            transition: transform 0.3s ease;
            border-left: 4px solid;
        }

        .stat-card.nilai-total {
            border-left-color: var(--primary);
        }

        .stat-card.nilai-sem1 {
            border-left-color: var(--info);
        }

        .stat-card.nilai-sem2 {
            border-left-color: var(--success);
        }

        .stat-card.kehadiran {
            border-left-color: var(--warning);
        }

        .stat-card.jadwal {
            border-left-color: var(--primary);
        }

        .stat-card.notifikasi {
            border-left-color: var(--info);
        }

        .stat-card.perkembangan {
            border-left-color: var(--success);
        }

        .stat-card .card-body {
            padding: 1.5rem;
        }

        .stat-card i {
            font-size: 2.2rem;
            margin-bottom: 15px;
            opacity: 0.9;
        }

        .stat-card.nilai-total i {
            color: var(--primary);
        }

        .stat-card.nilai-sem1 i {
            color: var(--info);
        }

        .stat-card.nilai-sem2 i {
            color: var(--success);
        }

        .stat-card.kehadiran i {
            color: var(--warning);
        }

        .stat-card.jadwal i {
            color: var(--primary);
        }

        .stat-card.notifikasi i {
            color: var(--info);
        }

        .stat-card.perkembangan i {
            color: var(--success);
        }

        .stat-card h3 {
            font-weight: 700;
            margin: 10px 0 5px;
        }

        .stat-card .card-title {
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .stat-card .text-muted {
            font-size: 0.85rem;
        }

        .list-group-item {
            border: none;
            padding: 12px 15px;
            transition: background-color 0.2s;
        }

        .list-group-item:hover {
            background-color: rgba(0, 0, 0, 0.03);
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            padding: 1rem 1.25rem;
            font-weight: 600;
        }

        .card-header h5 {
            margin-bottom: 0;
            color: var(--dark);
        }

        .progress-perkembangan {
            height: 8px;
            border-radius: 4px;
        }

        .dropdown-toggle {
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 500;
        }

        .dropdown-toggle::after {
            margin-left: 8px;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .burger-menu {
                display: block;
            }

            .sidebar {
                position: fixed !important;
                top: 60px !important;
                left: -250px !important;
                width: 250px !important;
                z-index: 1050;
                box-shadow: 3px 0 15px rgba(0, 0, 0, 0.1);
                transition: left 0.3s ease !important;
            }

            .sidebar.show {
                left: 0 !important;
            }

            .main-content {
                margin-left: 0 !important;
            }

            .stat-card {
                margin-bottom: 1rem;
            }
        }

        @media (max-width: 767.98px) {
            .card-body {
                padding: 1.25rem;
            }

            .stat-card .card-body {
                padding: 1.25rem;
            }

            .welcome-card .card-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <!-- Header Fixed -->
        <div class="header-fixed">
            <button class="burger-menu" id="burgerMenu">
                <i class="bi bi-list"></i>
            </button>
            <h2 class="h3 mb-0 ms-2">Dashboard Siswa</h2>
            <div class="ms-auto">
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($siswa['nama_siswa']) ?>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profil.php">Profil Saya</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row g-0">
            <!-- Sidebar Overlay -->
            <div class="sidebar-overlay" id="sidebarOverlay"></div>

            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar" id="sidebar">
                <div class="d-flex flex-column p-3">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="text-white text-center mb-0">
                            <i class="bi bi-mortarboard-fill"></i> SekolahKu
                        </h4>
                        <button class="btn-close btn-close-white d-md-none" id="closeSidebar"></button>
                    </div>

                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a href="index3.php" class="nav-link active">
                                <i class="bi bi-house-door"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="profil.php" class="nav-link">
                                <i class="bi bi-person"></i> Profil Saya
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="nilai.php" class="nav-link">
                                <i class="bi bi-journal-text"></i> Nilai Akademik
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="jadwal.php" class="nav-link">
                                <i class="bi bi-calendar-event"></i> Jadwal Pelajaran
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="absensi.php" class="nav-link">
                                <i class="bi bi-clipboard-check"></i> Absensi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="mapel.php" class="nav-link">
                                <i class="bi bi-book"></i> Mata Pelajaran
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a href="logout.php" class="nav-link text-danger bg-danger bg-opacity-10">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="p-4">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center">
                            <button class="burger-menu me-3" id="burgerMenu">
                                <i class="bi bi-list"></i>
                            </button>
                            <h2 class="h3 mb-0">Dashboard Siswa</h2>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($siswa['nama_siswa']) ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profil.php"><i class="bi bi-person me-2"></i>Profil Saya</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Welcome Card -->
                    <div class="card welcome-card mb-4">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h3 class="card-title">Selamat Datang, <?= htmlspecialchars($siswa['nama_siswa']) ?>!</h3>
                                    <p class="card-text">Anda login sebagai siswa <?= htmlspecialchars($siswa['nama_kelas']) ?></p>
                                    <p class="card-text mb-0">
                                        <small>NIS: <?= htmlspecialchars($siswa['nis']) ?> | Terakhir login: <?= date('d/m/Y H:i') ?></small>
                                    </p>
                                </div>
                                <div class="col-md-4 text-center">
                                    <i class="bi bi-person-check" style="font-size: 4rem; opacity: 0.8;"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card nilai-total">
                                <div class="card-body text-center">
                                    <i class="bi bi-graph-up"></i>
                                    <h5 class="card-title">Nilai Rata-rata</h5>
                                    <h3 class="text-primary"><?= $rata_rata_nilai_all ?></h3>
                                    <p class="text-muted">Keseluruhan</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card nilai-sem1">
                                <div class="card-body text-center">
                                    <i class="bi bi-journal-check"></i>
                                    <h5 class="card-title">Nilai Semester 1</h5>
                                    <h3 class="text-info"><?= $rata_rata_nilai_sem1 ?></h3>
                                    <p class="text-muted">Rata-rata</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card nilai-sem2">
                                <div class="card-body text-center">
                                    <i class="bi bi-journal-plus"></i>
                                    <h5 class="card-title">Nilai Semester 2</h5>
                                    <h3 class="text-success"><?= $rata_rata_nilai_sem2 ?></h3>
                                    <p class="text-muted">Rata-rata</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card kehadiran">
                                <div class="card-body text-center">
                                    <i class="bi bi-clipboard-check"></i>
                                    <h5 class="card-title">Kehadiran</h5>
                                    <h3 class="text-warning"><?= $persentase_kehadiran ?>%</h3>
                                    <p class="text-muted">Bulan Ini</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Second Row Stats -->
                    <div class="row mb-4">
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card stat-card jadwal">
                                <div class="card-body text-center">
                                    <i class="bi bi-calendar-event"></i>
                                    <h5 class="card-title">Jadwal Hari Ini</h5>
                                    <h3 class="text-primary"><?= $jumlah_jadwal ?></h3>
                                    <p class="text-muted">Mata Pelajaran</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card stat-card notifikasi">
                                <div class="card-body text-center">
                                    <i class="bi bi-bell"></i>
                                    <h5 class="card-title">Pemberitahuan</h5>
                                    <h3 class="text-info"><?= count($aktivitas) ?></h3>
                                    <p class="text-muted">Aktivitas Terbaru</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 mb-4">
                            <div class="card stat-card perkembangan">
                                <div class="card-body text-center">
                                    <i class="bi bi-arrow-up-right"></i>
                                    <h5 class="card-title">Perkembangan Nilai</h5>
                                    <div class="d-flex justify-content-between mt-3">
                                        <div class="text-center">
                                            <span class="badge bg-info semester-badge">Semester 1</span>
                                            <h4 class="mt-2"><?= $rata_rata_nilai_sem1 ?></h4>
                                        </div>
                                        <div class="text-center">
                                            <span class="badge bg-success semester-badge">Semester 2</span>
                                            <h4 class="mt-2"><?= $rata_rata_nilai_sem2 ?></h4>
                                        </div>
                                    </div>
                                    <?php if ($rata_rata_nilai_sem2 > $rata_rata_nilai_sem1): ?>
                                        <p class="text-success mt-2 mb-0">
                                            <i class="bi bi-arrow-up"></i> Meningkat <?= $rata_rata_nilai_sem2 - $rata_rata_nilai_sem1 ?> poin
                                        </p>
                                    <?php elseif ($rata_rata_nilai_sem2 < $rata_rata_nilai_sem1): ?>
                                        <p class="text-danger mt-2 mb-0">
                                            <i class="bi bi-arrow-down"></i> Menurun <?= $rata_rata_nilai_sem1 - $rata_rata_nilai_sem2 ?> poin
                                        </p>
                                    <?php else: ?>
                                        <p class="text-warning mt-2 mb-0">
                                            <i class="bi bi-dash"></i> Stabil
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities and Schedule -->
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Aktivitas Terbaru</h5>
                                    <span class="badge bg-primary"><?= count($aktivitas) ?></span>
                                </div>
                                <div class="card-body p-0">
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($aktivitas as $index => $aktivitas_item): ?>
                                            <div class="list-group-item d-flex align-items-center">
                                                <div class="me-3">
                                                    <i class="bi <?= $aktivitas_item['icon'] ?>" style="font-size: 1.2rem;"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="fw-medium"><?= htmlspecialchars($aktivitas_item['text']) ?></div>
                                                </div>
                                                <div>
                                                    <small class="text-muted"><?= $aktivitas_item['waktu'] ?></small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Jadwal Hari Ini</h5>
                                    <span class="badge bg-primary"><?= $hari_sekarang ?></span>
                                </div>
                                <div class="card-body p-0">
                                    <?php if (count($jadwal_hari_ini) > 0): ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach ($jadwal_hari_ini as $jadwal): ?>
                                                <div class="list-group-item">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <strong class="d-block"><?= htmlspecialchars($jadwal['nama_mapel']) ?></strong>
                                                            <small class="text-muted">Guru: <?= htmlspecialchars($jadwal['nama_petugas']) ?></small>
                                                        </div>
                                                        <div class="text-end">
                                                            <span class="badge bg-light text-dark"><?= date('H:i', strtotime($jadwal['jam_mulai'])) ?> - <?= date('H:i', strtotime($jadwal['jam_selesai'])) ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="bi bi-calendar-x text-muted" style="font-size: 2rem;"></i>
                                            <p class="text-muted mt-2 mb-0">Tidak ada jadwal pelajaran hari ini</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const burgerMenu = document.getElementById('burgerMenu');
            const sidebar = document.getElementById('sidebar');
            const closeSidebar = document.getElementById('closeSidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            // Toggle sidebar
            burgerMenu.addEventListener('click', function() {
                sidebar.classList.add('show');
                sidebarOverlay.style.display = 'block';
            });

            // Close sidebar
            function closeSidebarFunc() {
                sidebar.classList.remove('show');
                sidebarOverlay.style.display = 'none';
            }

            closeSidebar.addEventListener('click', closeSidebarFunc);
            sidebarOverlay.addEventListener('click', closeSidebarFunc);

            // Close sidebar when clicking on a link (mobile)
            if (window.innerWidth < 992) {
                const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
                sidebarLinks.forEach(link => {
                    link.addEventListener('click', closeSidebarFunc);
                });
            }

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 992) {
                    sidebar.classList.remove('show');
                    sidebarOverlay.style.display = 'none';
                }
            });
        });
    </script>
</body>

</html>