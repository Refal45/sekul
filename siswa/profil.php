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

// Ambil data siswa
$id_siswa = $_SESSION['id_siswa'];
$siswa = getData(
    $pdo,
    "SELECT s.*, k.nama_kelas, k.tingkat 
     FROM siswa s 
     JOIN kelas k ON s.id_kelas = k.id_kelas 
     WHERE s.id_siswa = ?",
    [$id_siswa]
);

// Jika data tidak ditemukan
if (!$siswa) {
    $_SESSION['error'] = "Data siswa tidak ditemukan";
    header("Location: login-siswa.php");
    exit;
}

// Proses update profil
$pesan_sukses = '';
$pesan_error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profil'])) {
    $nama_siswa = $_POST['nama_siswa'];
    $no_hp = $_POST['no_hp'];
    $jenis_kelamin = $_POST['jenis_kelamin'];

    // Validasi input
    if (empty($nama_siswa)) {
        $pesan_error = "Nama siswa harus diisi!";
    } else {
        try {
            // Update data siswa
            $sql_update = "UPDATE siswa SET nama_siswa = ?, no_hp = ?, jenis_kelamin = ? WHERE id_siswa = ?";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([$nama_siswa, $no_hp, $jenis_kelamin, $id_siswa]);

            $pesan_sukses = "Profil berhasil diperbarui!";

            // Refresh data siswa
            $siswa = getData(
                $pdo,
                "SELECT s.*, k.nama_kelas, k.tingkat 
                 FROM siswa s 
                 JOIN kelas k ON s.id_kelas = k.id_kelas 
                 WHERE s.id_siswa = ?",
                [$id_siswa]
            );
        } catch (PDOException $e) {
            $pesan_error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// Proses ganti password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ganti_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // Validasi
    if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
        $pesan_error = "Semua field password harus diisi!";
    } elseif ($password_baru !== $konfirmasi_password) {
        $pesan_error = "Konfirmasi password tidak sesuai!";
    } elseif (strlen($password_baru) < 6) {
        $pesan_error = "Password baru minimal 6 karakter!";
    } else {
        // Verifikasi password lama
        if (password_verify($password_lama, $siswa['password'])) {
            // Hash password baru
            $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);

            // Update password
            $sql_password = "UPDATE siswa SET password = ? WHERE id_siswa = ?";
            $stmt_password = $pdo->prepare($sql_password);
            $stmt_password->execute([$password_hash, $id_siswa]);

            $pesan_sukses = "Password berhasil diubah!";
        } else {
            $pesan_error = "Password lama salah!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Siswa | SekolahKu</title>
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

        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }

        .avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            border: 4px solid white;
        }

        .info-card {
            transition: transform 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            padding: 1.25rem 1.5rem;
            font-weight: 600;
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
            <h2 class="h3 mb-0 ms-2">Profil Siswa</h2>
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
                            <a href="profil.php" class="nav-link active">
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

                    <!-- Notifikasi -->
                    <?php if ($pesan_sukses): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i><?= $pesan_sukses ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($pesan_error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i><?= $pesan_error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <!-- Informasi Profil -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card info-card">
                                <div class="profile-header p-4 text-center">
                                    <div class="avatar mx-auto mb-3">
                                        <i class="bi bi-person-fill text-white"></i>
                                    </div>
                                    <h4 class="mb-2"><?= htmlspecialchars($siswa['nama_siswa']) ?></h4>
                                    <p class="mb-0 opacity-75">Siswa <?= htmlspecialchars($siswa['nama_kelas']) ?></p>
                                </div>
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span class="text-muted">NIS:</span>
                                            <strong class="text-primary"><?= htmlspecialchars($siswa['nis']) ?></strong>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span class="text-muted">Kelas:</span>
                                            <strong><?= htmlspecialchars($siswa['nama_kelas']) ?> (<?= htmlspecialchars($siswa['tingkat']) ?>)</strong>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span class="text-muted">Jenis Kelamin:</span>
                                            <strong>
                                                <?php if ($siswa['jenis_kelamin'] == 'L'): ?>
                                                    <span class="badge bg-primary">Laki-laki</span>
                                                <?php else: ?>
                                                    <span class="badge bg-pink" style="background-color: #e83e8c;">Perempuan</span>
                                                <?php endif; ?>
                                            </strong>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span class="text-muted">No. HP:</span>
                                            <strong><?= $siswa['no_hp'] ? htmlspecialchars($siswa['no_hp']) : '<span class="text-muted">-</span>' ?></strong>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span class="text-muted">Username:</span>
                                            <strong class="text-info"><?= htmlspecialchars($siswa['username']) ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Profil & Ganti Password -->
                        <div class="col-lg-8 col-md-6">
                            <!-- Edit Profil -->
                            <div class="card mb-4">
                                <div class="card-header bg-transparent">
                                    <h5 class="card-title mb-0 text-primary">
                                        <i class="bi bi-pencil-square me-2"></i>Edit Profil
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <input type="hidden" name="update_profil" value="1">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="nama_siswa" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="nama_siswa" name="nama_siswa"
                                                    value="<?= htmlspecialchars($siswa['nama_siswa']) ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="no_hp" class="form-label">No. Handphone</label>
                                                <input type="text" class="form-control" id="no_hp" name="no_hp"
                                                    value="<?= htmlspecialchars($siswa['no_hp'] ?? '') ?>"
                                                    placeholder="Contoh: 081234567890">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="jenis_kelamin"
                                                            id="laki" value="L" <?= $siswa['jenis_kelamin'] == 'L' ? 'checked' : '' ?> required>
                                                        <label class="form-check-label" for="laki">
                                                            <i class="bi bi-gender-male me-1"></i>Laki-laki
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="jenis_kelamin"
                                                            id="perempuan" value="P" <?= $siswa['jenis_kelamin'] == 'P' ? 'checked' : '' ?> required>
                                                        <label class="form-check-label" for="perempuan">
                                                            <i class="bi bi-gender-female me-1"></i>Perempuan
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Informasi Tidak Dapat Diubah</label>
                                                <div class="alert alert-light border">
                                                    <small class="text-muted">
                                                        <i class="bi bi-info-circle me-1"></i>
                                                        NIS dan username tidak dapat diubah. Hubungi administrator untuk perubahan data tersebut.
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <span class="text-danger">*</span> Menandakan field wajib diisi
                                            </small>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Ganti Password -->
                            <div class="card">
                                <div class="card-header bg-transparent">
                                    <h5 class="card-title mb-0 text-warning">
                                        <i class="bi bi-shield-lock me-2"></i>Ganti Password
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <input type="hidden" name="ganti_password" value="1">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="password_lama" class="form-label">Password Lama <span class="text-danger">*</span></label>
                                                <input type="password" class="form-control" id="password_lama" name="password_lama"
                                                    placeholder="Masukkan password lama" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="password_baru" class="form-label">Password Baru <span class="text-danger">*</span></label>
                                                <input type="password" class="form-control" id="password_baru" name="password_baru"
                                                    placeholder="Minimal 6 karakter" required minlength="6">
                                                <div class="form-text text-muted">
                                                    <i class="bi bi-info-circle me-1"></i>Password minimal 6 karakter
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="konfirmasi_password" class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                                <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password"
                                                    placeholder="Ulangi password baru" required minlength="6">
                                            </div>
                                            <div class="col-md-6 mb-3 d-flex align-items-end">
                                                <button type="submit" class="btn btn-warning w-100">
                                                    <i class="bi bi-key me-2"></i>Ganti Password
                                                </button>
                                            </div>
                                        </div>
                                        <div class="alert alert-info mt-3">
                                            <small>
                                                <i class="bi bi-lightbulb me-2"></i>
                                                <strong>Tips:</strong> Gunakan password yang kuat dengan kombinasi huruf, angka, dan simbol.
                                            </small>
                                        </div>
                                    </form>
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
            // Sidebar functionality
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

            // Password validation
            const passwordForm = document.querySelector('form[action=""] input[name="ganti_password"]');
            if (passwordForm) {
                passwordForm.closest('form').addEventListener('submit', function(e) {
                    const passwordBaru = document.getElementById('password_baru').value;
                    const konfirmasiPassword = document.getElementById('konfirmasi_password').value;

                    if (passwordBaru !== konfirmasiPassword) {
                        e.preventDefault();
                        alert('Konfirmasi password tidak sesuai!');
                        return;
                    }

                    if (passwordBaru.length < 6) {
                        e.preventDefault();
                        alert('Password baru minimal 6 karakter!');
                        return;
                    }
                });
            }

            // Real-time password confirmation check
            const passwordBaruInput = document.getElementById('password_baru');
            const konfirmasiPasswordInput = document.getElementById('konfirmasi_password');

            if (passwordBaruInput && konfirmasiPasswordInput) {
                function checkPasswordMatch() {
                    const passwordBaru = passwordBaruInput.value;
                    const konfirmasiPassword = konfirmasiPasswordInput.value;

                    if (konfirmasiPassword === '') return;

                    if (passwordBaru !== konfirmasiPassword) {
                        konfirmasiPasswordInput.classList.add('is-invalid');
                        konfirmasiPasswordInput.classList.remove('is-valid');
                    } else {
                        konfirmasiPasswordInput.classList.remove('is-invalid');
                        konfirmasiPasswordInput.classList.add('is-valid');
                    }
                }

                passwordBaruInput.addEventListener('input', checkPasswordMatch);
                konfirmasiPasswordInput.addEventListener('input', checkPasswordMatch);
            }
        });
    </script>
</body>

</html>