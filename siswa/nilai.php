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

// Ambil data nilai semester 1
$nilai_semester1 = getAllData(
    $pdo,
    "SELECT n.*, m.nama_mapel, m.kode_mapel 
     FROM nilai n 
     JOIN mata_pelajaran m ON n.id_mapel = m.id_mapel 
     WHERE n.id_siswa = ? AND n.semester = '1'
     ORDER BY m.nama_mapel",
    [$id_siswa]
);

// Ambil data nilai semester 2
$nilai_semester2 = getAllData(
    $pdo,
    "SELECT n.*, m.nama_mapel, m.kode_mapel 
     FROM nilai n 
     JOIN mata_pelajaran m ON n.id_mapel = m.id_mapel 
     WHERE n.id_siswa = ? AND n.semester = '2'
     ORDER BY m.nama_mapel",
    [$id_siswa]
);

// Hitung rata-rata per semester
$rata_rata_sem1 = getData(
    $pdo,
    "SELECT AVG(nilai) as rata_rata FROM nilai 
     WHERE id_siswa = ? AND semester = '1'",
    [$id_siswa]
);

$rata_rata_sem2 = getData(
    $pdo,
    "SELECT AVG(nilai) as rata_rata FROM nilai 
     WHERE id_siswa = ? AND semester = '2'",
    [$id_siswa]
);

$rata_rata_sem1 = $rata_rata_sem1 ? round($rata_rata_sem1['rata_rata'], 2) : 0;
$rata_rata_sem2 = $rata_rata_sem2 ? round($rata_rata_sem2['rata_rata'], 2) : 0;

// Hitung total mata pelajaran
$total_mapel_sem1 = count($nilai_semester1);
$total_mapel_sem2 = count($nilai_semester2);

// Fungsi untuk menentukan warna badge berdasarkan nilai
function getNilaiBadgeClass($nilai)
{
    if ($nilai >= 85) return 'bg-success';
    if ($nilai >= 75) return 'bg-info';
    if ($nilai >= 65) return 'bg-warning';
    return 'bg-danger';
}

// Fungsi untuk menentukan predikat
function getPredikat($nilai)
{
    if ($nilai >= 85) return 'Sangat Baik';
    if ($nilai >= 75) return 'Baik';
    if ($nilai >= 65) return 'Cukup';
    return 'Kurang';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nilai Akademik | SekolahKu</title>
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
            transition: all 0.2s;
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
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .welcome-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stat-card {
            transition: transform 0.3s ease;
            height: 100%;
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

        .nilai-badge {
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

        .semester-badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
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
            <h2 class="h3 mb-0 ms-2">Nilai Akademik</h2>
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
                            <a href="nilai.php" class="nav-link active">
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
                                    <h3 class="card-title">Nilai Akademik, <?= htmlspecialchars($siswa['nama_siswa']) ?>!</h3>
                                    <p class="card-text">Berikut adalah rangkuman nilai akademik Anda di <?= htmlspecialchars($siswa['nama_kelas']) ?></p>
                                    <p class="card-text">
                                        <small>NIS: <?= htmlspecialchars($siswa['nis']) ?> | Tahun Ajaran: 2024/2025</small>
                                    </p>
                                </div>
                                <div class="col-md-4 text-center">
                                    <i class="bi bi-journal-text" style="font-size: 4rem; opacity: 0.8;"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row">
                        <div class="col-md-3 mb-4">
                            <div class="card text-center stat-card">
                                <div class="card-body">
                                    <i class="bi bi-journal-check text-info" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-2">Semester 1</h5>
                                    <h3 class="text-info"><?= $rata_rata_sem1 ?></h3>
                                    <p class="text-muted">Rata-rata</p>
                                    <small class="text-muted"><?= $total_mapel_sem1 ?> Mapel</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card text-center stat-card">
                                <div class="card-body">
                                    <i class="bi bi-journal-plus text-success" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-2">Semester 2</h5>
                                    <h3 class="text-success"><?= $rata_rata_sem2 ?></h3>
                                    <p class="text-muted">Rata-rata</p>
                                    <small class="text-muted"><?= $total_mapel_sem2 ?> Mapel</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card text-center stat-card">
                                <div class="card-body">
                                    <i class="bi bi-graph-up text-primary" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-2">Perkembangan Nilai</h5>
                                    <div class="row mt-3">
                                        <div class="col-6">
                                            <span class="badge bg-info semester-badge">Semester 1</span>
                                            <h4 class="mt-2"><?= $rata_rata_sem1 ?></h4>
                                        </div>
                                        <div class="col-6">
                                            <span class="badge bg-success semester-badge">Semester 2</span>
                                            <h4 class="mt-2"><?= $rata_rata_sem2 ?></h4>
                                        </div>
                                    </div>
                                    <?php if ($rata_rata_sem2 > $rata_rata_sem1): ?>
                                        <p class="text-success mt-2 mb-0">
                                            <i class="bi bi-arrow-up"></i> Meningkat <?= number_format($rata_rata_sem2 - $rata_rata_sem1, 2) ?> poin
                                        </p>
                                    <?php elseif ($rata_rata_sem2 < $rata_rata_sem1): ?>
                                        <p class="text-danger mt-2 mb-0">
                                            <i class="bi bi-arrow-down"></i> Menurun <?= number_format($rata_rata_sem1 - $rata_rata_sem2, 2) ?> poin
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

                    <!-- Semester 1 Table -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card table-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-journal-check text-info me-2"></i>
                                        Nilai Semester 1
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <?php if (count($nilai_semester1) > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th width="5%">No</th>
                                                        <th width="15%">Kode</th>
                                                        <th width="40%">Mata Pelajaran</th>
                                                        <th width="15%">Nilai</th>
                                                        <th width="15%">Predikat</th>
                                                        <th width="10%">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($nilai_semester1 as $index => $nilai): ?>
                                                        <tr>
                                                            <td class="fw-medium"><?= $index + 1 ?></td>
                                                            <td>
                                                                <span class="badge bg-secondary"><?= htmlspecialchars($nilai['kode_mapel']) ?></span>
                                                            </td>
                                                            <td class="fw-medium"><?= htmlspecialchars($nilai['nama_mapel']) ?></td>
                                                            <td>
                                                                <span class="badge <?= getNilaiBadgeClass($nilai['nilai']) ?> nilai-badge">
                                                                    <?= number_format($nilai['nilai'], 2) ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <small class="text-muted"><?= getPredikat($nilai['nilai']) ?></small>
                                                            </td>
                                                            <td>
                                                                <?php if ($nilai['nilai'] >= 75): ?>
                                                                    <span class="badge bg-success">Lulus</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-danger">Remedial</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-state">
                                            <i class="bi bi-journal-x"></i>
                                            <h5 class="text-muted">Belum ada data nilai</h5>
                                            <p class="text-muted">Data nilai semester 1 akan ditampilkan di sini</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Semester 2 Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card table-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-journal-plus text-success me-2"></i>
                                        Nilai Semester 2
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <?php if (count($nilai_semester2) > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th width="5%">No</th>
                                                        <th width="15%">Kode</th>
                                                        <th width="40%">Mata Pelajaran</th>
                                                        <th width="15%">Nilai</th>
                                                        <th width="15%">Predikat</th>
                                                        <th width="10%">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($nilai_semester2 as $index => $nilai): ?>
                                                        <tr>
                                                            <td class="fw-medium"><?= $index + 1 ?></td>
                                                            <td>
                                                                <span class="badge bg-secondary"><?= htmlspecialchars($nilai['kode_mapel']) ?></span>
                                                            </td>
                                                            <td class="fw-medium"><?= htmlspecialchars($nilai['nama_mapel']) ?></td>
                                                            <td>
                                                                <span class="badge <?= getNilaiBadgeClass($nilai['nilai']) ?> nilai-badge">
                                                                    <?= number_format($nilai['nilai'], 2) ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <small class="text-muted"><?= getPredikat($nilai['nilai']) ?></small>
                                                            </td>
                                                            <td>
                                                                <?php if ($nilai['nilai'] >= 75): ?>
                                                                    <span class="badge bg-success">Lulus</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-danger">Remedial</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-state">
                                            <i class="bi bi-journal-x"></i>
                                            <h5 class="text-muted">Belum ada data nilai</h5>
                                            <p class="text-muted">Data nilai semester 2 akan ditampilkan di sini</p>
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