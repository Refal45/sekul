<?php
session_start();
require '../koneksi.php';

// Pastikan $pdo sudah didefinisikan di koneksi.php
if (!isset($pdo)) {
    die("Database connection error!");
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $nama = trim($_POST['nama']);
    $nis = trim($_POST['nis']);

    // Validasi input
    if (empty($username) || empty($password) || empty($nama) || empty($nis)) {
        $error = "Semua field harus diisi!";
    } else {
        // Cek apakah siswa terdaftar di database admin (tabel siswa)
        $stmt = $pdo->prepare("SELECT * FROM siswa WHERE nis = ? AND nama_siswa = ?");
        $stmt->execute([$nis, $nama]);
        $siswa_terdaftar = $stmt->fetch();

        if (!$siswa_terdaftar) {
            $error = "Data siswa tidak ditemukan! Pastikan NIS dan nama sesuai dengan data sekolah.";
        } else {
            // Cek apakah siswa sudah pernah mendaftar akun
            $stmt = $pdo->prepare("SELECT * FROM siswa WHERE nis = ? AND username IS NOT NULL");
            $stmt->execute([$nis]);
            if ($stmt->fetch()) {
                $error = "Akun dengan NIS ini sudah terdaftar! Silakan <a href='login-siswa.php' class='alert-link'>login</a> menggunakan username dan password Anda.";
            } else {
                // Cek apakah username sudah digunakan
                $stmt = $pdo->prepare("SELECT * FROM siswa WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $error = "Username sudah digunakan! Silakan pilih username lain.";
                } else {
                    // Hash password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                    // Update data siswa dengan username dan password
                    $stmt = $pdo->prepare("UPDATE siswa SET username = ?, password = ? WHERE nis = ?");
                    if ($stmt->execute([$username, $hashedPassword, $nis])) {
                        $success = "Registrasi berhasil! Silakan <a href='login-siswa.php' class='alert-link'>login</a> untuk mengakses portal siswa.";
                    } else {
                        $error = "Terjadi kesalahan saat registrasi! Silakan coba lagi.";
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Siswa | SekolahKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #0d6efd;
            --light-blue: #e3f2fd;
            --dark-blue: #0a58ca;
            --accent-blue: #3d8bfd;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            margin: 20px;
            transition: all 0.3s ease;
        }

        .register-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .register-header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            color: white;
            text-align: center;
            padding: 40px 30px;
            position: relative;
            overflow: hidden;
        }

        .register-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,186.7C384,213,480,235,576,213.3C672,192,768,128,864,122.7C960,117,1056,171,1152,197.3C1248,224,1344,224,1392,224L1440,224L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-size: cover;
            background-position: center bottom;
        }

        .logo-container {
            position: relative;
            z-index: 1;
        }

        .logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.3);
            padding: 5px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .register-header h1 {
            font-weight: 700;
            font-size: 1.8rem;
            margin: 15px 0 5px;
            position: relative;
            z-index: 1;
        }

        .register-header p {
            opacity: 0.9;
            margin: 0;
            position: relative;
            z-index: 1;
        }

        .register-body {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-blue);
            background: white;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }

        .btn-register {
            width: 100%;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(13, 110, 253, 0.3);
        }

        .btn-register::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transform: translateX(-100%);
        }

        .btn-register:hover::after {
            animation: shine 1.5s ease;
        }

        .alert {
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 1.5rem;
            border: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .alert-success {
            background: #d1edff;
            color: var(--dark-blue);
            border-left: 4px solid var(--primary-blue);
        }

        .alert-success a,
        .alert-error a {
            color: inherit;
            font-weight: 600;
            text-decoration: underline;
        }

        .alert-success a:hover,
        .alert-error a:hover {
            text-decoration: none;
            opacity: 0.8;
        }

        .info-box {
            background: #e7f3ff;
            border: 1px solid #b6d4fe;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            color: #084298;
            border-left: 4px solid var(--primary-blue);
        }

        .info-box i {
            color: var(--primary-blue);
            margin-right: 8px;
        }

        .links {
            text-align: center;
            margin-top: 2rem;
            color: #6c757d;
        }

        .links a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .links a:hover {
            color: var(--dark-blue);
            text-decoration: underline;
        }

        .footer {
            text-align: center;
            margin-top: 2rem;
            color: #6c757d;
            font-size: 0.9rem;
        }

        @keyframes shine {
            to {
                transform: translateX(100%);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .register-container {
            animation: fadeIn 0.8s ease-out;
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .register-container {
                margin: 10px;
            }

            .register-header {
                padding: 30px 20px;
            }

            .register-body {
                padding: 30px 20px;
            }

            .register-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="register-container">
        <!-- Header Section -->
        <div class="register-header">
            <div class="logo-container">
                <img src="https://th.bing.com/th/id/OIP.Pg5X0hL6o9CkquPBvWIhfQHaHa?w=161&h=180&c=7&r=0&o=7&dpr=1.2&pid=1.7&rm=3"
                    alt="SekolahKu Logo"
                    class="logo">
                <h1>Daftar Akun Siswa</h1>
                <p>Buat akun baru untuk mengakses portal siswa</p>
            </div>
        </div>

        <!-- Body Section -->
        <div class="register-body">
            <!-- Info Box -->
            <div class="info-box">
                <i class="bi bi-info-circle-fill"></i>
                <strong>Informasi Penting:</strong> Hanya siswa yang terdaftar di database sekolah yang dapat membuat akun.
                Pastikan NIS dan nama lengkap sesuai dengan data sekolah.
            </div>

            <!-- Pesan Error -->
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>
                        <?php
                        // Untuk pesan error yang mengandung HTML, kita echo langsung
                        if (strpos($error, '<a') !== false) {
                            echo $error;
                        } else {
                            echo htmlspecialchars($error);
                        }
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Pesan Sukses -->
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <div><?php echo $success; ?></div>
                </div>
            <?php endif; ?>

            <form method="POST">
                <!-- NIS -->
                <div class="form-group">
                    <label for="nis" class="form-label">
                        <i class="bi bi-card-heading me-2"></i>Nomor Induk Siswa (NIS)
                    </label>
                    <input type="text" name="nis" id="nis" placeholder="Masukkan NIS" required class="form-control">
                    <small class="text-muted">NIS harus sesuai dengan data sekolah</small>
                </div>

                <!-- Nama Lengkap -->
                <div class="form-group">
                    <label for="nama" class="form-label">
                        <i class="bi bi-person-fill me-2"></i>Nama Lengkap
                    </label>
                    <input type="text" name="nama" id="nama" placeholder="Masukkan nama lengkap sesuai data sekolah" required class="form-control">
                </div>

                <!-- Username -->
                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="bi bi-person-badge-fill me-2"></i>Username
                    </label>
                    <input type="text" name="username" id="username" placeholder="Buat username untuk login" required class="form-control">
                    <small class="text-muted">Username harus unik dan belum digunakan</small>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock-fill me-2"></i>Password
                    </label>
                    <input type="password" name="password" id="password" placeholder="Buat password yang kuat" required class="form-control">
                    <small class="text-muted">Minimal 6 karakter</small>
                </div>

                <button type="submit" class="btn-register">
                    <i class="bi bi-person-plus-fill me-2"></i>Daftar Sekarang
                </button>
            </form>

            <!-- Links Section -->
            <div class="links">
                <p>Sudah punya akun? <a href="login-siswa.php">Login di sini</a></p>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>&copy; <?php echo date("Y"); ?> <strong>SekolahKu</strong> | Semua Hak Dilindungi</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Validasi client-side
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const passwordInput = document.getElementById('password');

            form.addEventListener('submit', function(e) {
                const password = passwordInput.value;

                if (password.length < 6) {
                    e.preventDefault();
                    alert('Password harus minimal 6 karakter!');
                    passwordInput.focus();
                    return false;
                }

                return true;
            });

            // Auto-focus pada field NIS
            document.getElementById('nis').focus();
        });
    </script>
</body>

</html>