<?php
session_start();
require '../koneksi.php';

$error = "";
$success = "";

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);

    // Cek apakah username siswa ada
    $stmt = $pdo->prepare("SELECT * FROM siswa WHERE username=?");
    $stmt->execute([$username]);
    $siswa = $stmt->fetch();

    if ($siswa) {
        // Generate token
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Simpan token ke tabel password_resets
        $stmt = $pdo->prepare("INSERT INTO password_resets (username, token, created_at, expired_at) VALUES (?, ?, NOW(), ?)");
        $stmt->execute([$username, $token, $expires]);

        // Redirect ke halaman reset-password.php dengan username & token
        header("Location: reset-password.php?username=" . urlencode($username) . "&token=" . $token);
        exit;
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password Siswa | SekolahKu</title>
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

        .forgot-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
            margin: 20px;
            transition: all 0.3s ease;
        }

        .forgot-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .forgot-header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            color: white;
            text-align: center;
            padding: 40px 30px;
            position: relative;
            overflow: hidden;
        }

        .forgot-header::before {
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

        .forgot-header h1 {
            font-weight: 700;
            font-size: 1.8rem;
            margin: 15px 0 5px;
            position: relative;
            z-index: 1;
        }

        .forgot-header p {
            opacity: 0.9;
            margin: 0;
            position: relative;
            z-index: 1;
        }

        .forgot-body {
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

        .btn-submit {
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

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(13, 110, 253, 0.3);
        }

        .btn-submit::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transform: translateX(-100%);
        }

        .btn-submit:hover::after {
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

        .forgot-container {
            animation: fadeIn 0.8s ease-out;
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .forgot-container {
                margin: 10px;
            }
            
            .forgot-header {
                padding: 30px 20px;
            }
            
            .forgot-body {
                padding: 30px 20px;
            }
            
            .forgot-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="forgot-container">
        <!-- Header Section -->
        <div class="forgot-header">
            <div class="logo-container">
                <img src="https://th.bing.com/th/id/OIP.Pg5X0hL6o9CkquPBvWIhfQHaHa?w=161&h=180&c=7&r=0&o=7&dpr=1.2&pid=1.7&rm=3" 
                     alt="SekolahKu Logo" 
                     class="logo">
                <h1>Lupa Password</h1>
                <p>Masukkan username untuk mereset password</p>
            </div>
        </div>

        <!-- Body Section -->
        <div class="forgot-body">
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

            <form method="POST">
                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="bi bi-person-badge-fill me-2"></i>Username Siswa
                    </label>
                    <input type="text" name="username" id="username" placeholder="Masukkan username Anda" required class="form-control">
                </div>

                <button type="submit" class="btn-submit">
                    <i class="bi bi-send-fill me-2"></i>Kirim Link Reset
                </button>
            </form>

            <!-- Links Section -->
            <div class="links">
                <p>
                    <a href="login-siswa.php">
                        <i class="bi bi-arrow-left"></i>Kembali ke Login
                    </a>
                </p>
                <p>
                    <a href="../web-utama/siswa.php">
                        <i class="bi bi-house-fill"></i>Kembali ke Beranda
                    </a>
                </p>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>&copy; <?php echo date("Y"); ?> <strong>SekolahKu</strong> | Semua Hak Dilindungi</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>