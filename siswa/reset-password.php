<?php
session_start();
require '../koneksi.php';

$error = "";
$success = "";

$token = $_GET['token'] ?? '';
if (!$token) die("Token tidak ditemukan!");

// Ambil token aktif dari tabel password_resets
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token=? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$token]);
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset) die("Token tidak valid atau sudah digunakan!");

// Ambil data siswa
$stmt = $pdo->prepare("SELECT * FROM siswa WHERE username=?");
$stmt->execute([$reset['username']]);
$siswa = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $siswa) {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error = "Password tidak sama!";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Update password siswa
        $stmt = $pdo->prepare("UPDATE siswa SET password=? WHERE username=?");
        $stmt->execute([$hash, $siswa['username']]);

        // Hapus token agar tidak bisa dipakai lagi
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token=?");
        $stmt->execute([$token]);

        $success = "Password berhasil direset! <a href='login-siswa.php' class='text-blue-600 hover:underline'>Login sekarang</a>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password Siswa | SekolahKu</title>
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
            padding: 20px;
        }

        .reset-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
            margin: 20px;
            transition: all 0.3s ease;
        }

        .reset-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .reset-header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            color: white;
            text-align: center;
            padding: 40px 30px;
            position: relative;
            overflow: hidden;
        }

        .reset-header::before {
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

        .reset-header h1 {
            font-weight: 700;
            font-size: 1.8rem;
            margin: 15px 0 5px;
            position: relative;
            z-index: 1;
        }

        .reset-header p {
            opacity: 0.9;
            margin: 0;
            position: relative;
            z-index: 1;
        }

        .reset-body {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 12px 45px 12px 16px;
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

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            font-size: 1.1rem;
            transition: color 0.3s ease;
            z-index: 2;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .password-toggle:hover {
            color: var(--primary-blue);
            background: rgba(0, 0, 0, 0.05);
            border-radius: 50%;
        }

        .btn-reset {
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

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(13, 110, 253, 0.3);
        }

        .btn-reset::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transform: translateX(-100%);
        }

        .btn-reset:hover::after {
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
        }

        .alert-success {
            background: #d1edff;
            color: var(--dark-blue);
        }

        .alert-success a {
            color: var(--dark-blue);
            font-weight: 600;
            text-decoration: none;
        }

        .alert-success a:hover {
            text-decoration: underline;
        }

        .user-info {
            background: #e7f3ff;
            border: 1px solid #b6d4fe;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }

        .user-info h5 {
            color: var(--dark-blue);
            margin-bottom: 5px;
            font-weight: 600;
        }

        .user-info p {
            color: #495057;
            margin: 0;
            font-size: 0.9rem;
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
            display: inline-flex;
            align-items: center;
            gap: 5px;
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

        .reset-container {
            animation: fadeIn 0.8s ease-out;
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .reset-container {
                margin: 10px;
            }

            .reset-header {
                padding: 30px 20px;
            }

            .reset-body {
                padding: 30px 20px;
            }

            .reset-header h1 {
                font-size: 1.5rem;
            }
        }

        /* Fix for Edge browser icon duplication */
        .password-toggle i {
            display: inline-block;
            line-height: 1;
        }

        .form-control::-ms-reveal {
            display: none;
        }
    </style>
</head>

<body>
    <div class="reset-container">
        <!-- Header Section -->
        <div class="reset-header">
            <div class="logo-container">
                <img src="https://th.bing.com/th/id/OIP.Pg5X0hL6o9CkquPBvWIhfQHaHa?w=161&h=180&c=7&r=0&o=7&dpr=1.2&pid=1.7&rm=3"
                    alt="SekolahKu Logo"
                    class="logo">
                <h1>Reset Password</h1>
                <p>Buat password baru untuk akun Anda</p>
            </div>
        </div>

        <!-- Body Section -->
        <div class="reset-body">
            <!-- Pesan Error -->
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <!-- Pesan Sukses -->
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <span><?php echo $success; ?></span>
                </div>
            <?php endif; ?>

            <?php if (!$success && isset($siswa) && $siswa): ?>
                <!-- Info Pengguna -->
                <div class="user-info">
                    <h5>Reset Password untuk:</h5>
                    <p><strong><?php echo htmlspecialchars($siswa['nama_siswa']); ?></strong></p>
                    <p>NIS: <?php echo htmlspecialchars($siswa['nis']); ?> | Username: <?php echo htmlspecialchars($siswa['username']); ?></p>
                </div>

                <form method="POST" id="resetForm">
                    <!-- Password Baru -->
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock-fill me-2"></i>Password Baru
                        </label>
                        <input type="password" name="password" id="password" placeholder="Masukkan password baru" required class="form-control" minlength="6">
                        <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                            <i class="bi bi-eye"></i>
                        </button>
                        <div class="form-text">Password minimal 6 karakter</div>
                    </div>

                    <!-- Konfirmasi Password -->
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">
                            <i class="bi bi-lock-fill me-2"></i>Konfirmasi Password
                        </label>
                        <input type="password" name="confirm_password" id="confirm_password" placeholder="Konfirmasi password baru" required class="form-control" minlength="6">
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', this)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>

                    <button type="submit" class="btn-reset">
                        <i class="bi bi-arrow-clockwise me-2"></i>Reset Password
                    </button>
                </form>
            <?php elseif (!$success): ?>
                <div class="alert alert-error">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>Data siswa tidak ditemukan atau token sudah tidak valid.</span>
                </div>
            <?php endif; ?>

            <!-- Links Section -->
            <div class="links">
                <p>
                    <a href="login-siswa.php">
                        <i class="bi bi-arrow-left me-1"></i>Kembali ke Login
                    </a>
                </p>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>&copy; <?php echo date("Y"); ?> <strong>SekolahKu</strong> | Semua Hak Dilindungi</p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId, button) {
            const passwordInput = document.getElementById(inputId);
            const icon = button.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

        // Validasi form
        document.getElementById('resetForm')?.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password harus minimal 6 karakter!');
                return false;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak sama!');
                return false;
            }
        });

        // Auto-focus pada field password
        document.addEventListener('DOMContentLoaded', function() {
            const passwordField = document.getElementById('password');
            if (passwordField) {
                passwordField.focus();
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>