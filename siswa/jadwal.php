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

// Daftar hari dalam bahasa Indonesia
$hari_indonesia = [
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu',
    'Sunday' => 'Minggu'
];

// Ambil jadwal untuk semua hari
$jadwal_per_hari = [];
$hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

foreach ($hari_list as $hari) {
    $jadwal_per_hari[$hari] = getAllData(
        $pdo,
        "SELECT j.*, m.nama_mapel, m.kode_mapel, p.nama_petugas 
         FROM jadwal j
         JOIN mata_pelajaran m ON j.id_mapel = m.id_mapel
         JOIN petugas p ON j.id_petugas = p.id_petugas
         WHERE j.id_kelas = ? AND j.hari = ?
         ORDER BY j.jam_mulai",
        [$siswa['id_kelas'], $hari]
    );
}

// Ambil jadwal hari ini
$hari_sekarang = $hari_indonesia[date('l')];
$jadwal_hari_ini = getAllData(
    $pdo,
    "SELECT j.*, m.nama_mapel, m.kode_mapel, p.nama_petugas 
     FROM jadwal j
     JOIN mata_pelajaran m ON j.id_mapel = m.id_mapel
     JOIN petugas p ON j.id_petugas = p.id_petugas
     WHERE j.id_kelas = ? AND j.hari = ?
     ORDER BY j.jam_mulai",
    [$siswa['id_kelas'], $hari_sekarang]
);

// Hitung statistik
$total_jadwal = 0;
foreach ($jadwal_per_hari as $jadwal_hari) {
    $total_jadwal += count($jadwal_hari);
}

$hari_aktif = 0;
foreach ($jadwal_per_hari as $hari => $jadwal_hari) {
    if (count($jadwal_hari) > 0) {
        $hari_aktif++;
    }
}

// Hitung jumlah guru unik
$semua_guru = [];
foreach ($jadwal_per_hari as $jadwal_hari) {
    foreach ($jadwal_hari as $jadwal) {
        $semua_guru[] = $jadwal['nama_petugas'];
    }
}
$jumlah_guru = count(array_unique($semua_guru));

// Fungsi untuk format jam
function formatJam($time)
{
    return date('H:i', strtotime($time));
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Pelajaran | SekolahKu</title>
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

        .jadwal-badge {
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
            <h2 class="h3 mb-0 ms-2">Jadwal Pelajaran</h2>
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
                            <a href="jadwal.php" class="nav-link active">
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
                                    <h3 class="card-title">Jadwal Pelajaran, <?= htmlspecialchars($siswa['nama_siswa']) ?>!</h3>
                                    <p class="card-text">Berikut adalah jadwal pelajaran Anda di <?= htmlspecialchars($siswa['nama_kelas']) ?></p>
                                    <p class="card-text">
                                        <small>Hari ini: <?= $hari_sekarang ?> | Total: <?= $total_jadwal ?> jam pelajaran</small>
                                    </p>
                                </div>
                                <div class="col-md-4 text-center">
                                    <i class="bi bi-calendar-event" style="font-size: 4rem; opacity: 0.8;"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row">
                        <div class="col-md-3 mb-4">
                            <div class="card text-center stat-card">
                                <div class="card-body">
                                    <i class="bi bi-calendar-week text-primary" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-2">Total Jadwal</h5>
                                    <h3 class="text-primary"><?= $total_jadwal ?></h3>
                                    <p class="text-muted">Jam Pelajaran</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card text-center stat-card">
                                <div class="card-body">
                                    <i class="bi bi-calendar-day text-info" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-2">Hari Aktif</h5>
                                    <h3 class="text-info"><?= $hari_aktif ?></h3>
                                    <p class="text-muted">Hari/Minggu</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card text-center stat-card">
                                <div class="card-body">
                                    <i class="bi bi-clock text-success" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-2">Hari Ini</h5>
                                    <h3 class="text-success"><?= count($jadwal_hari_ini) ?></h3>
                                    <p class="text-muted">Jam Pelajaran</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card text-center stat-card">
                                <div class="card-body">
                                    <i class="bi bi-people text-warning" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-2">Guru</h5>
                                    <h3 class="text-warning"><?= $jumlah_guru ?></h3>
                                    <p class="text-muted">Pengajar</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Jadwal Hari Ini -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card table-card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-calendar-day text-primary me-2"></i>
                                        Jadwal Hari Ini (<?= $hari_sekarang ?>)
                                    </h5>
                                    <span class="badge bg-primary"><?= count($jadwal_hari_ini) ?> Pelajaran</span>
                                </div>
                                <div class="card-body p-0">
                                    <?php if (count($jadwal_hari_ini) > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th width="15%">Jam Pelajaran</th>
                                                        <th width="25%">Mata Pelajaran</th>
                                                        <th width="20%">Kode Mapel</th>
                                                        <th width="30%">Guru Pengajar</th>
                                                        <th width="10%">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($jadwal_hari_ini as $jadwal): ?>
                                                        <tr>
                                                            <td>
                                                                <span class="badge bg-primary jadwal-badge">
                                                                    <?= formatJam($jadwal['jam_mulai']) ?> - <?= formatJam($jadwal['jam_selesai']) ?>
                                                                </span>
                                                            </td>
                                                            <td class="fw-medium"><?= htmlspecialchars($jadwal['nama_mapel']) ?></td>
                                                            <td>
                                                                <span class="badge bg-secondary"><?= htmlspecialchars($jadwal['kode_mapel']) ?></span>
                                                            </td>
                                                            <td><?= htmlspecialchars($jadwal['nama_petugas']) ?></td>
                                                            <td>
                                                                <span class="badge bg-success">Hari Ini</span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-state">
                                            <i class="bi bi-calendar-x"></i>
                                            <h5 class="text-muted">Tidak ada jadwal hari ini</h5>
                                            <p class="text-muted">Selamat beristirahat!</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Jadwal Mingguan -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card table-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-calendar-week text-info me-2"></i>
                                        Jadwal Mingguan
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <!-- Tab Navigation -->
                                    <div class="nav nav-pills px-3 pt-3" id="hariTab" role="tablist">
                                        <?php foreach ($hari_list as $index => $hari): ?>
                                            <button class="nav-link <?= $hari === $hari_sekarang ? 'active' : '' ?>"
                                                id="<?= $hari ?>-tab"
                                                data-bs-toggle="pill"
                                                data-bs-target="#<?= $hari ?>"
                                                type="button"
                                                role="tab">
                                                <?= $hari ?>
                                                <span class="badge bg-secondary ms-1"><?= count($jadwal_per_hari[$hari]) ?></span>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- Tab Content -->
                                    <div class="tab-content p-3" id="hariTabContent">
                                        <?php foreach ($hari_list as $index => $hari): ?>
                                            <div class="tab-pane fade <?= $hari === $hari_sekarang ? 'show active' : '' ?>"
                                                id="<?= $hari ?>"
                                                role="tabpanel">
                                                <?php if (count($jadwal_per_hari[$hari]) > 0): ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-hover">
                                                            <thead>
                                                                <tr>
                                                                    <th width="15%">Jam Pelajaran</th>
                                                                    <th width="25%">Mata Pelajaran</th>
                                                                    <th width="20%">Kode Mapel</th>
                                                                    <th width="30%">Guru Pengajar</th>
                                                                    <th width="10%">Status</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($jadwal_per_hari[$hari] as $jadwal): ?>
                                                                    <tr>
                                                                        <td>
                                                                            <span class="badge bg-primary jadwal-badge">
                                                                                <?= formatJam($jadwal['jam_mulai']) ?> - <?= formatJam($jadwal['jam_selesai']) ?>
                                                                            </span>
                                                                        </td>
                                                                        <td class="fw-medium"><?= htmlspecialchars($jadwal['nama_mapel']) ?></td>
                                                                        <td>
                                                                            <span class="badge bg-secondary"><?= htmlspecialchars($jadwal['kode_mapel']) ?></span>
                                                                        </td>
                                                                        <td><?= htmlspecialchars($jadwal['nama_petugas']) ?></td>
                                                                        <td>
                                                                            <?php if ($hari === $hari_sekarang): ?>
                                                                                <span class="badge bg-success">Hari Ini</span>
                                                                            <?php else: ?>
                                                                                <span class="badge bg-info">Terjadwal</span>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="empty-state py-4">
                                                        <i class="bi bi-calendar-x"></i>
                                                        <h5 class="text-muted">Tidak ada jadwal</h5>
                                                        <p class="text-muted">Tidak ada pelajaran yang terjadwal untuk hari <?= $hari ?>.</p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
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

            // Auto-select today's tab (fallback)
            const today = '<?= $hari_sekarang ?>';
            const todayTab = document.getElementById(today + '-tab');
            const todayContent = document.getElementById(today);

            if (todayTab && todayContent && !todayTab.classList.contains('active')) {
                // Remove active class from all tabs
                document.querySelectorAll('.nav-link').forEach(tab => {
                    tab.classList.remove('active');
                });
                document.querySelectorAll('.tab-pane').forEach(pane => {
                    pane.classList.remove('show', 'active');
                });

                // Add active class to today's tab
                todayTab.classList.add('active');
                todayContent.classList.add('show', 'active');
            }
        });
    </script>
</body>

</html>