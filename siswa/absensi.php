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

// Ambil data absensi siswa
$absensi_siswa = getAllData(
    $pdo,
    "SELECT k.*, j.hari, m.nama_mapel, m.kode_mapel, p.nama_petugas,
            DATE_FORMAT(k.tanggal, '%d/%m/%Y') as tanggal_format
     FROM kehadiran k
     JOIN jadwal j ON k.id_jadwal = j.id_jadwal
     JOIN mata_pelajaran m ON j.id_mapel = m.id_mapel
     JOIN petugas p ON j.id_petugas = p.id_petugas
     WHERE k.id_siswa = ?
     ORDER BY k.tanggal DESC, j.jam_mulai DESC",
    [$id_siswa]
);

// Hitung statistik absensi
$total_kehadiran = count($absensi_siswa);
$hadir_count = 0;
$izin_count = 0;
$sakit_count = 0;
$alpha_count = 0;

foreach ($absensi_siswa as $absensi) {
    switch ($absensi['status']) {
        case 'Hadir':
            $hadir_count++;
            break;
        case 'Izin':
            $izin_count++;
            break;
        case 'Sakit':
            $sakit_count++;
            break;
        case 'Alpha':
            $alpha_count++;
            break;
    }
}

// Hitung persentase kehadiran
$persentase_hadir = $total_kehadiran > 0 ? round(($hadir_count / $total_kehadiran) * 100, 1) : 0;

// Ambil data absensi bulan ini
$bulan_ini = date('Y-m');
$absensi_bulan_ini = getAllData(
    $pdo,
    "SELECT k.*, j.hari, m.nama_mapel, m.kode_mapel, p.nama_petugas,
            DATE_FORMAT(k.tanggal, '%d/%m/%Y') as tanggal_format
     FROM kehadiran k
     JOIN jadwal j ON k.id_jadwal = j.id_jadwal
     JOIN mata_pelajaran m ON j.id_mapel = m.id_mapel
     JOIN petugas p ON j.id_petugas = p.id_petugas
     WHERE k.id_siswa = ? AND DATE_FORMAT(k.tanggal, '%Y-%m') = ?
     ORDER BY k.tanggal DESC, j.jam_mulai DESC",
    [$id_siswa, $bulan_ini]
);

// Hitung statistik bulan ini
$total_bulan_ini = count($absensi_bulan_ini);
$hadir_bulan_ini = 0;
$izin_bulan_ini = 0;
$sakit_bulan_ini = 0;
$alpha_bulan_ini = 0;

foreach ($absensi_bulan_ini as $absensi) {
    switch ($absensi['status']) {
        case 'Hadir':
            $hadir_bulan_ini++;
            break;
        case 'Izin':
            $izin_bulan_ini++;
            break;
        case 'Sakit':
            $sakit_bulan_ini++;
            break;
        case 'Alpha':
            $alpha_bulan_ini++;
            break;
    }
}

// Fungsi untuk mendapatkan badge class berdasarkan status
function getStatusBadge($status)
{
    switch ($status) {
        case 'Hadir':
            return 'bg-success';
        case 'Izin':
            return 'bg-warning';
        case 'Sakit':
            return 'bg-info';
        case 'Alpha':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

// Fungsi untuk format bulan Indonesia
function formatBulanIndonesia($bulan)
{
    $bulan_indonesia = [
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember'
    ];

    $tahun = substr($bulan, 0, 4);
    $bulan_angka = substr($bulan, 5, 2);

    return $bulan_indonesia[$bulan_angka] . ' ' . $tahun;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi | SekolahKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
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
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            padding-top: 1rem !important;
            overflow-y: auto;
            transition: all 0.3s;
        }

        .sidebar .nav-link {
            color: white;
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 8px;
        }

        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Burger Menu */
        .burger-menu {
            display: none;
            background: none;
            border: none;
            color: #0d6efd;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
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
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .welcome-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stat-card {
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .table-card {
            border-radius: 15px;
            overflow: hidden;
        }

        .table th {
            background-color: #0d6efd;
            color: white;
            border: none;
            padding: 1rem;
            font-weight: 600;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #dee2e6;
        }

        .table tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .status-badge {
            font-size: 0.85rem;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            padding: 1.25rem 1.5rem;
            font-weight: 600;
        }

        .nav-pills .nav-link {
            border-radius: 10px;
            margin: 0.2rem;
            font-weight: 500;
        }

        .nav-pills .nav-link.active {
            background-color: #0d6efd;
        }

        .progress {
            height: 8px;
            border-radius: 10px;
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
                box-shadow: 3px 0 10px rgba(0, 0, 0, 0.1);
                transition: left 0.3s ease !important;
            }

            .sidebar.show {
                left: 0 !important;
            }

            .main-content {
                margin-left: 0 !important;
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
            <h2 class="h3 mb-0 ms-2">Absensi</h2>
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
                <div class="d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-4 ps-3 pe-3">
                        <h4 class="text-white text-center mb-0">
                            <i class="bi bi-mortarboard-fill"></i> SekolahKu
                        </h4>
                        <button class="btn-close btn-close-white d-md-none" id="closeSidebar"></button>
                    </div>

                    <ul class="nav nav-pills flex-column ps-3 pe-3">
                        <li class="nav-item">
                            <a href="index3.php" class="nav-link">
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
                            <a href="absensi.php" class="nav-link active">
                                <i class="bi bi-clipboard-check"></i> Absensi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="mapel.php" class="nav-link">
                                <i class="bi bi-book"></i> Mata Pelajaran
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="logout.php" class="nav-link text-danger">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="p-4">

                    <!-- Welcome Card -->
                    <div class="card welcome-card mb-4">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h3 class="card-title">Rekap Absensi, <?= htmlspecialchars($siswa['nama_siswa']) ?>!</h3>
                                    <p class="card-text">Berikut adalah rekap kehadiran Anda di <?= htmlspecialchars($siswa['nama_kelas']) ?></p>
                                    <p class="card-text">
                                        <small>Total Kehadiran: <?= $total_kehadiran ?> sesi | Persentase Hadir: <?= $persentase_hadir ?>%</small>
                                    </p>
                                </div>
                                <div class="col-md-4 text-center">
                                    <i class="bi bi-clipboard-check" style="font-size: 4rem; opacity: 0.8;"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row">
                        <div class="col-md-3 mb-4">
                            <div class="card text-center stat-card">
                                <div class="card-body">
                                    <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-2">Hadir</h5>
                                    <h3 class="text-success"><?= $hadir_count ?></h3>
                                    <p class="text-muted">Sesi</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card text-center stat-card">
                                <div class="card-body">
                                    <i class="bi bi-info-circle text-warning" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-2">Izin</h5>
                                    <h3 class="text-warning"><?= $izin_count ?></h3>
                                    <p class="text-muted">Sesi</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card text-center stat-card">
                                <div class="card-body">
                                    <i class="bi bi-heart-pulse text-info" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-2">Sakit</h5>
                                    <h3 class="text-info"><?= $sakit_count ?></h3>
                                    <p class="text-muted">Sesi</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card text-center stat-card">
                                <div class="card-body">
                                    <i class="bi bi-x-circle text-danger" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-2">Alpha</h5>
                                    <h3 class="text-danger"><?= $alpha_count ?></h3>
                                    <p class="text-muted">Sesi</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Persentase Kehadiran</h5>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Total Kehadiran</span>
                                        <span><?= $persentase_hadir ?>%</span>
                                    </div>
                                    <div class="progress mb-3">
                                        <div class="progress-bar bg-success" role="progressbar"
                                            style="width: <?= $persentase_hadir ?>%"
                                            aria-valuenow="<?= $persentase_hadir ?>"
                                            aria-valuemin="0"
                                            aria-valuemax="100">
                                        </div>
                                    </div>
                                    <div class="row text-center">
                                        <div class="col-3">
                                            <small class="text-muted">Hadir: <?= $hadir_count ?></small>
                                        </div>
                                        <div class="col-3">
                                            <small class="text-muted">Izin: <?= $izin_count ?></small>
                                        </div>
                                        <div class="col-3">
                                            <small class="text-muted">Sakit: <?= $sakit_count ?></small>
                                        </div>
                                        <div class="col-3">
                                            <small class="text-muted">Alpha: <?= $alpha_count ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Absensi Bulan Ini -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card table-card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-calendar-month text-primary me-2"></i>
                                        Absensi Bulan Ini (<?= formatBulanIndonesia($bulan_ini) ?>)
                                    </h5>
                                    <span class="badge bg-primary"><?= $total_bulan_ini ?> Sesi</span>
                                </div>
                                <div class="card-body p-0">
                                    <?php if (count($absensi_bulan_ini) > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th width="15%">Tanggal</th>
                                                        <th width="20%">Hari</th>
                                                        <th width="25%">Mata Pelajaran</th>
                                                        <th width="20%">Guru Pengajar</th>
                                                        <th width="20%">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($absensi_bulan_ini as $absensi): ?>
                                                        <tr>
                                                            <td class="fw-medium"><?= $absensi['tanggal_format'] ?></td>
                                                            <td><?= $absensi['hari'] ?></td>
                                                            <td>
                                                                <div class="fw-medium"><?= htmlspecialchars($absensi['nama_mapel']) ?></div>
                                                                <small class="text-muted"><?= htmlspecialchars($absensi['kode_mapel']) ?></small>
                                                            </td>
                                                            <td><?= htmlspecialchars($absensi['nama_petugas']) ?></td>
                                                            <td>
                                                                <span class="badge status-badge <?= getStatusBadge($absensi['status']) ?>">
                                                                    <?= $absensi['status'] ?>
                                                                </span>
                                                                <?php if (!empty($absensi['keterangan'])): ?>
                                                                    <br>
                                                                    <small class="text-muted"><?= htmlspecialchars($absensi['keterangan']) ?></small>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-state">
                                            <i class="bi bi-clipboard-x"></i>
                                            <h5 class="text-muted">Belum ada data absensi</h5>
                                            <p class="text-muted">Data absensi untuk bulan ini akan muncul di sini.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Riwayat Absensi Lengkap -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card table-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-clock-history text-info me-2"></i>
                                        Riwayat Absensi Lengkap
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <?php if (count($absensi_siswa) > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th width="15%">Tanggal</th>
                                                        <th width="15%">Hari</th>
                                                        <th width="25%">Mata Pelajaran</th>
                                                        <th width="20%">Guru Pengajar</th>
                                                        <th width="15%">Status</th>
                                                        <th width="10%">Keterangan</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($absensi_siswa as $absensi): ?>
                                                        <tr>
                                                            <td class="fw-medium"><?= $absensi['tanggal_format'] ?></td>
                                                            <td><?= $absensi['hari'] ?></td>
                                                            <td>
                                                                <div class="fw-medium"><?= htmlspecialchars($absensi['nama_mapel']) ?></div>
                                                                <small class="text-muted"><?= htmlspecialchars($absensi['kode_mapel']) ?></small>
                                                            </td>
                                                            <td><?= htmlspecialchars($absensi['nama_petugas']) ?></td>
                                                            <td>
                                                                <span class="badge status-badge <?= getStatusBadge($absensi['status']) ?>">
                                                                    <?= $absensi['status'] ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <?php if (!empty($absensi['keterangan'])): ?>
                                                                    <button class="btn btn-sm btn-outline-secondary"
                                                                        data-bs-toggle="tooltip"
                                                                        title="<?= htmlspecialchars($absensi['keterangan']) ?>">
                                                                        <i class="bi bi-info-circle"></i>
                                                                    </button>
                                                                <?php else: ?>
                                                                    <span class="text-muted">-</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-state">
                                            <i class="bi bi-clipboard-x"></i>
                                            <h5 class="text-muted">Belum ada data absensi</h5>
                                            <p class="text-muted">Data absensi Anda akan muncul di sini.</p>
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

            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>

</html>