<?php
session_start();
require '../koneksi.php';

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $login = $_POST['login']; // Bisa berisi NIS atau username
  $pass = $_POST['password'];

  // Query untuk mencari user berdasarkan NIS atau username
  $sql = "SELECT * FROM siswa WHERE nis = ? OR username = ?";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$login, $login]);
  $user = $stmt->fetch();

  if ($user) {
    if (password_verify($pass, $user['password'])) {
      $_SESSION['id_siswa'] = $user['id_siswa'];
      $_SESSION['nis'] = $user['nis'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['nama_siswa'] = $user['nama_siswa'];
      $_SESSION['id_kelas'] = $user['id_kelas'];
      header("Location: index3.php");
      exit;
    } else {
      $error = "Password salah!";
    }
  } else {
    $error = "NIS/Username tidak ditemukan!";
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Siswa | SekolahKu</title>
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

    .login-container {
      background: white;
      border-radius: 20px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      max-width: 450px;
      width: 100%;
      margin: 20px;
      transition: all 0.3s ease;
    }

    .login-container:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .login-header {
      background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
      color: white;
      text-align: center;
      padding: 40px 30px;
      position: relative;
      overflow: hidden;
    }

    .login-header::before {
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

    .login-header h1 {
      font-weight: 700;
      font-size: 1.8rem;
      margin: 15px 0 5px;
      position: relative;
      z-index: 1;
    }

    .login-header p {
      opacity: 0.9;
      margin: 0;
      position: relative;
      z-index: 1;
    }

    .login-body {
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

    .input-icon {
      position: relative;
    }

    .input-icon i {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #6c757d;
    }

    .input-icon .form-control {
      padding-left: 45px;
    }

    .remember-forgot {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
      font-size: 0.9rem;
    }

    .remember-me {
      display: flex;
      align-items: center;
      gap: 8px;
      color: #495057;
    }

    .remember-me input[type="checkbox"] {
      width: 16px;
      height: 16px;
      accent-color: var(--primary-blue);
    }

    .forgot-link {
      color: var(--primary-blue);
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .forgot-link:hover {
      color: var(--dark-blue);
      text-decoration: underline;
    }

    .btn-login {
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

    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(13, 110, 253, 0.3);
    }

    .btn-login::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transform: translateX(-100%);
    }

    .btn-login:hover::after {
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

    .info-box {
      background: #e7f3ff;
      border: 1px solid #b6d4fe;
      border-radius: 10px;
      padding: 12px 15px;
      margin-bottom: 20px;
      font-size: 0.85rem;
      color: #084298;
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

    .login-container {
      animation: fadeIn 0.8s ease-out;
    }

    /* Responsive adjustments */
    @media (max-width: 480px) {
      .login-container {
        margin: 10px;
      }

      .login-header {
        padding: 30px 20px;
      }

      .login-body {
        padding: 30px 20px;
      }

      .login-header h1 {
        font-size: 1.5rem;
      }

      .remember-forgot {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
      }
    }
  </style>
</head>

<body>
  <div class="login-container">
    <!-- Header Section -->
    <div class="login-header">
      <div class="logo-container">
        <img src="https://th.bing.com/th/id/OIP.Pg5X0hL6o9CkquPBvWIhfQHaHa?w=161&h=180&c=7&r=0&o=7&dpr=1.2&pid=1.7&rm=3"
          alt="SekolahKu Logo"
          class="logo">
        <h1>Selamat Datang Siswa</h1>
        <p>Masukkan akun Anda untuk mengakses portal siswa</p>
      </div>
    </div>

    <!-- Body Section -->
    <div class="login-body">
      <!-- Info Box -->
      <div class="info-box">
        <i class="bi bi-info-circle-fill"></i>
        <strong>Informasi Login:</strong> Gunakan NIS atau Username dan password yang telah Anda buat saat registrasi.
      </div>

      <!-- Pesan Error -->
      <?php if ($error): ?>
        <div class="alert alert-error">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <span><?php echo $error; ?></span>
        </div>
      <?php endif; ?>

      <form method="POST">
        <!-- NIS atau Username -->
        <div class="form-group">
          <label for="login" class="form-label">
            <i class="bi bi-person-badge me-2"></i>NIS atau Username
          </label>
          <div class="input-icon">
            <i class="bi bi-person-circle"></i>
            <input type="text" name="login" id="login" placeholder="Masukkan NIS atau username" required class="form-control">
          </div>
        </div>

        <!-- Password -->
        <div class="form-group">
          <label for="password" class="form-label">
            <i class="bi bi-lock-fill me-2"></i>Password
          </label>
          <div class="input-icon">
            <i class="bi bi-lock-fill"></i>
            <input type="password" name="password" id="password" placeholder="Masukkan password" required class="form-control">
          </div>
        </div>

        <!-- Remember me & Forgot Password -->
        <div class="remember-forgot">
          <label class="remember-me">
            <input type="checkbox" name="remember">
            Ingat Saya
          </label>
          <a href="forgot-password.php" class="forgot-link">
            Lupa Password?
          </a>
        </div>

        <button type="submit" class="btn-login">
          <i class="bi bi-box-arrow-in-right me-2"></i>Login
        </button>
      </form>

      <!-- Links Section -->
      <div class="links">
        <p>
          Belum punya akun?
          <a href="register-siswa.php">
            <i class="bi bi-person-plus-fill"></i>Register Siswa
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

  <script>
    // Auto-focus pada field login saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
      const loginField = document.getElementById('login');
      loginField.focus();

      // Validasi input NIS hanya angka (jika yang dimasukkan adalah NIS)
      loginField.addEventListener('input', function(e) {
        // Jika user hanya memasukkan angka, biarkan saja (mungkin NIS)
        // Jika ada huruf, berarti username, biarkan juga
        // Tidak perlu validasi khusus karena bisa menerima keduanya
      });
    });
  </script>
</body>

</html>