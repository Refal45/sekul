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

// Ambil semua mata pelajaran yang diikuti siswa berdasarkan jadwal
$mata_pelajaran = getAllData(
    $pdo,
    "SELECT DISTINCT m.*, 
            (SELECT COUNT(DISTINCT j.id_petugas) 
             FROM jadwal j 
             WHERE j.id_mapel = m.id_mapel AND j.id_kelas = ?) as jumlah_guru,
            (SELECT COUNT(*) 
             FROM jadwal j 
             WHERE j.id_mapel = m.id_mapel AND j.id_kelas = ?) as total_jadwal,
            (SELECT GROUP_CONCAT(DISTINCT p.nama_petugas SEPARATOR ', ') 
             FROM jadwal j 
             JOIN petugas p ON j.id_petugas = p.id_petugas 
             WHERE j.id_mapel = m.id_mapel AND j.id_kelas = ?) as daftar_guru
     FROM mata_pelajaran m
     JOIN jadwal j ON m.id_mapel = j.id_mapel
     WHERE j.id_kelas = ?
     ORDER BY m.nama_mapel",
    [$siswa['id_kelas'], $siswa['id_kelas'], $siswa['id_kelas'], $siswa['id_kelas']]
);

// Ambil jadwal per mata pelajaran
$jadwal_per_mapel = [];
foreach ($mata_pelajaran as $mapel) {
    $jadwal_per_mapel[$mapel['id_mapel']] = getAllData(
        $pdo,
        "SELECT j.*, p.nama_petugas, 
                DATE_FORMAT(j.jam_mulai, '%H:%i') as jam_mulai_format,
                DATE_FORMAT(j.jam_selesai, '%H:%i') as jam_selesai_format
         FROM jadwal j
         JOIN petugas p ON j.id_petugas = p.id_petugas
         WHERE j.id_mapel = ? AND j.id_kelas = ?
         ORDER BY 
            FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'),
            j.jam_mulai",
        [$mapel['id_mapel'], $siswa['id_kelas']]
    );
}

// Hitung statistik
$total_mapel = count($mata_pelajaran);
$total_jadwal = 0;
$mapel_dengan_guru = 0;

foreach ($mata_pelajaran as $mapel) {
    $total_jadwal += $mapel['total_jadwal'];
    if ($mapel['jumlah_guru'] > 0) {
        $mapel_dengan_guru++;
    }
}

// Ambil nilai untuk setiap mata pelajaran (jika ada)
$nilai_per_mapel = [];
foreach ($mata_pelajaran as $mapel) {
    $nilai = getData(
        $pdo,
        "SELECT n.*, m.nama_mapel 
         FROM nilai n 
         JOIN mata_pelajaran m ON n.id_mapel = m.id_mapel 
         WHERE n.id_siswa = ? AND n.id_mapel = ? 
         ORDER BY n.semester DESC 
         LIMIT 1",
        [$id_siswa, $mapel['id_mapel']]
    );
    if ($nilai) {
        $nilai_per_mapel[$mapel['id_mapel']] = $nilai;
    }
}

// Fungsi untuk mendapatkan warna badge berdasarkan nilai
function getNilaiBadge($nilai)
{
    if ($nilai >= 85) return 'bg-success';
    if ($nilai >= 75) return 'bg-info';
    if ($nilai >= 65) return 'bg-warning';
    return 'bg-danger';
}

// Fungsi untuk mendapatkan predikat nilai
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
    <title>Mata Pelajaran | SekolahKu</title>
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

        .mapel-card {
            transition: all 0.3s ease;
            border-left: 4px solid #0d6efd;
        }

        .mapel-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
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

        .badge-mapel {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
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

        .jadwal-item {
            border-left: 3px solid #0d6efd;
            padding-left: 1rem;
            margin-bottom: 0.5rem;
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
            <h2 class="h3 mb-0 ms-2">Mata Pelajaran</h2>
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
                            <a href="absensi.php" class="nav-link">
                                <i class="bi bi-clipboard-check"></i> Absensi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="mapel.php" class="nav-link active">
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
                                    <h3 class="card-title">Mata Pelajaran, <?= htmlspecialchars($siswa['nama_siswa']) ?>!</h3>
                                    <p class="card-text">Berikut adalah daftar mata pelajaran yang Anda ikuti di <?= htmlspecialchars($siswa['nama_kelas']) ?></p>
                                    <p class="card-text">
                                        <small>Total: <?= $total_mapel ?> mata pelajaran | <?= $total_jadwal ?> sesi per minggu</small>
                                    </p>
                                </div>
                                <div class="col-md-4 text-center">
                                    <i class="bi bi-book" style="font-size: 4rem; opacity: 0.8;"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row">
                        <div class="col-md-3 mb-4">
                            <div class="card text-center stat-card">
                                <div class="card-body">
                                    <i class="bi bi-book text-primary" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-2">Total Mapel</h5>
                                    <h3 class="text-primary"><?= $total_mapel ?></h3>
                                    <p class="text-muted">Mata Pelajaran</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card text-center stat-card">
                                <div class="card-body">
                                    <i class="bi bi-calendar-week text-info" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-2">Total Sesi</h5>
                                    <h3 class="text-info"><?= $total_jadwal ?></h3>
                                    <p class="text-muted">Sesi/Minggu</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card text-center stat-card">
                                <div class="card-body">
                                    <i class="bi bi-people text-success" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-2">Guru</h5>
                                    <h3 class="text-success"><?= $mapel_dengan_guru ?></h3>
                                    <p class="text-muted">Mapel dengan Guru</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card text-center stat-card">
                                <div class="card-body">
                                    <i class="bi bi-journal-check text-warning" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-2">Nilai</h5>
                                    <h3 class="text-warning"><?= count($nilai_per_mapel) ?></h3>
                                    <p class="text-muted">Mapel dengan Nilai</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Daftar Mata Pelajaran -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card table-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-list-check text-primary me-2"></i>
                                        Daftar Mata Pelajaran
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <?php if (count($mata_pelajaran) > 0): ?>
                                        <div class="row p-3">
                                            <?php foreach ($mata_pelajaran as $mapel): ?>
                                                <div class="col-md-6 col-lg-4 mb-4">
                                                    <div class="card mapel-card h-100">
                                                        <div class="card-body">
                                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                                <h5 class="card-title mb-0"><?= htmlspecialchars($mapel['nama_mapel']) ?></h5>
                                                                <span class="badge bg-primary badge-mapel"><?= htmlspecialchars($mapel['kode_mapel']) ?></span>
                                                            </div>

                                                            <!-- Informasi Guru -->
                                                            <div class="mb-3">
                                                                <small class="text-muted">Guru Pengajar:</small>
                                                                <p class="mb-1 fw-medium"><?= !empty($mapel['daftar_guru']) ? htmlspecialchars($mapel['daftar_guru']) : 'Belum ditentukan' ?></p>
                                                            </div>

                                                            <!-- Jadwal -->
                                                            <div class="mb-3">
                                                                <small class="text-muted">Jadwal:</small>
                                                                <?php if (isset($jadwal_per_mapel[$mapel['id_mapel']]) && count($jadwal_per_mapel[$mapel['id_mapel']]) > 0): ?>
                                                                    <?php foreach ($jadwal_per_mapel[$mapel['id_mapel']] as $jadwal): ?>
                                                                        <div class="jadwal-item">
                                                                            <small class="fw-medium"><?= $jadwal['hari'] ?></small>
                                                                            <br>
                                                                            <small class="text-muted"><?= $jadwal['jam_mulai_format'] ?> - <?= $jadwal['jam_selesai_format'] ?></small>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <p class="text-muted mb-0"><small>Belum ada jadwal</small></p>
                                                                <?php endif; ?>
                                                            </div>

                                                            <!-- Nilai Terakhir -->
                                                            <?php if (isset($nilai_per_mapel[$mapel['id_mapel']])): ?>
                                                                <?php $nilai = $nilai_per_mapel[$mapel['id_mapel']]; ?>
                                                                <div class="border-top pt-2">
                                                                    <small class="text-muted">Nilai Semester <?= $nilai['semester'] ?>:</small>
                                                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                                                        <span class="fw-bold"><?= $nilai['nilai'] ?></span>
                                                                        <span class="badge <?= getNilaiBadge($nilai['nilai']) ?>">
                                                                            <?= getPredikat($nilai['nilai']) ?>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            <?php else: ?>
                                                                <div class="border-top pt-2">
                                                                    <small class="text-muted">Nilai:</small>
                                                                    <p class="text-muted mb-0"><small>Belum ada nilai</small></p>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-state">
                                            <i class="bi bi-book-x"></i>
                                            <h5 class="text-muted">Belum ada mata pelajaran</h5>
                                            <p class="text-muted">Data mata pelajaran akan muncul di sini.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Ringkasan -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card table-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-table text-info me-2"></i>
                                        Ringkasan Mata Pelajaran
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <?php if (count($mata_pelajaran) > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th width="20%">Mata Pelajaran</th>
                                                        <th width="15%">Kode</th>
                                                        <th width="25%">Guru Pengajar</th>
                                                        <th width="15%">Jumlah Sesi</th>
                                                        <th width="15%">Nilai Terakhir</th>
                                                        <th width="10%">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($mata_pelajaran as $mapel): ?>
                                                        <tr>
                                                            <td class="fw-medium"><?= htmlspecialchars($mapel['nama_mapel']) ?></td>
                                                            <td>
                                                                <span class="badge bg-secondary"><?= htmlspecialchars($mapel['kode_mapel']) ?></span>
                                                            </td>
                                                            <td><?= !empty($mapel['daftar_guru']) ? htmlspecialchars($mapel['daftar_guru']) : 'Belum ditentukan' ?></td>
                                                            <td>
                                                                <span class="badge bg-info"><?= $mapel['total_jadwal'] ?> sesi</span>
                                                            </td>
                                                            <td>
                                                                <?php if (isset($nilai_per_mapel[$mapel['id_mapel']])): ?>
                                                                    <?php $nilai = $nilai_per_mapel[$mapel['id_mapel']]; ?>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="fw-bold me-2"><?= $nilai['nilai'] ?></span>
                                                                        <span class="badge <?= getNilaiBadge($nilai['nilai']) ?>">
                                                                            Sem <?= $nilai['semester'] ?>
                                                                        </span>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <span class="text-muted">-</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-success">Aktif</span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-state">
                                            <i class="bi bi-book-x"></i>
                                            <h5 class="text-muted">Belum ada mata pelajaran</h5>
                                            <p class="text-muted">Data mata pelajaran akan muncul di sini.</p>
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