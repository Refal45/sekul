<?php
session_start();
require '../koneksi.php';

$error = "";
$role = isset($_POST['role']) ? $_POST['role'] : 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $password = $_POST['password'];
  $nip = trim($_POST['nip']); // Field NIP baru

  // Query untuk mencari user berdasarkan username atau NIP
  $sql = "SELECT * FROM petugas WHERE (username = ? OR nip = ?) AND role = ?";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$username, $nip, $role]);
  $user = $stmt->fetch();

  if ($user) {
    if (password_verify($password, $user['password'])) {
      // Set session
      $_SESSION['id_petugas'] = $user['id_petugas'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['nama_lengkap'] = $user['nama_petugas'];
      $_SESSION['role'] = $user['role'];
      $_SESSION['nip'] = $user['nip']; // Simpan NIP di session

      // Redirect berdasarkan role
      if ($user['role'] === 'admin') {
        header("Location: index.php");
      } elseif ($user['role'] === 'guru') {
        header("Location: ../guru/index-2.php");
      }
      exit;
    } else {
      $error = "Password salah!";
    }
  } else {
    $error = "Username/NIP tidak ditemukan untuk role yang dipilih!";
  }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Admin & Guru | SekolahKu</title>
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
      max-width: 1000px;
      width: 100%;
      margin: 20px;
      transition: all 0.3s ease;
    }

    .login-container:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .login-left {
      background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
      color: white;
      padding: 50px 40px;
      position: relative;
      overflow: hidden;
    }

    .login-left::before {
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
      text-align: center;
    }

    .logo {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      border: 3px solid rgba(255, 255, 255, 0.3);
      padding: 5px;
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      margin-bottom: 20px;
    }

    .login-left h1 {
      font-weight: 700;
      font-size: 2.2rem;
      margin-bottom: 15px;
      position: relative;
      z-index: 1;
    }

    .login-left p {
      opacity: 0.9;
      margin-bottom: 25px;
      position: relative;
      z-index: 1;
      font-size: 1.1rem;
    }

    .info-box {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 15px;
      padding: 20px;
      border: 1px solid rgba(255, 255, 255, 0.2);
      position: relative;
      z-index: 1;
    }

    .info-box p {
      margin: 8px 0;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .login-right {
      padding: 50px 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .login-right h2 {
      font-weight: 700;
      font-size: 1.8rem;
      color: var(--dark-blue);
      margin-bottom: 30px;
      text-align: center;
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

    .divider {
      display: flex;
      align-items: center;
      margin: 25px 0;
      color: #6c757d;
    }

    .divider::before,
    .divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background: #e9ecef;
    }

    .divider span {
      padding: 0 15px;
      font-size: 0.9rem;
    }

    .links {
      text-align: center;
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
      margin: 0 10px;
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

    .login-options {
      display: flex;
      gap: 10px;
      margin-bottom: 1rem;
    }

    .login-option-btn {
      flex: 1;
      padding: 10px;
      border: 2px solid #e9ecef;
      border-radius: 8px;
      background: #f8f9fa;
      cursor: pointer;
      transition: all 0.3s ease;
      text-align: center;
      font-weight: 500;
    }

    .login-option-btn.active {
      border-color: var(--primary-blue);
      background: var(--light-blue);
      color: var(--primary-blue);
    }

    .login-option-btn:hover {
      border-color: var(--primary-blue);
    }

    .optional-text {
      font-size: 0.8rem;
      color: #6c757d;
      margin-top: 5px;
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
    @media (max-width: 768px) {
      .login-container {
        flex-direction: column;
        max-width: 450px;
      }

      .login-left,
      .login-right {
        padding: 40px 30px;
      }

      .login-left h1 {
        font-size: 1.8rem;
      }

      .remember-forgot {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
      }

      .links {
        display: flex;
        flex-direction: column;
        gap: 10px;
      }

      .links a {
        margin: 5px 0;
      }
    }

    @media (max-width: 480px) {

      .login-left,
      .login-right {
        padding: 30px 20px;
      }

      .login-left h1 {
        font-size: 1.5rem;
      }

      .logo {
        width: 80px;
        height: 80px;
      }
    }
  </style>
</head>

<body>
  <div class="login-container d-md-flex">
    <!-- Left Section -->
    <div class="login-left col-md-6">
      <div class="logo-container">
        <img src="https://th.bing.com/th/id/OIP.Pg5X0hL6o9CkquPBvWIhfQHaHa?w=161&h=180&c=7&r=0&o=7&dpr=1.2&pid=1.7&rm=3"
          alt="SekolahKu Logo" class="logo">
        <h1>Selamat Datang Admin & Guru</h1>
        <p>Masukkan akun Anda untuk mengakses sistem manajemen sekolah</p>

        <div class="info-box">
          <p>
            <i class="bi bi-info-circle-fill"></i>
            Login menggunakan Username atau NIP
          </p>
          <p>
            <i class="bi bi-shield-lock-fill"></i>
            Data Anda aman dan terenkripsi
          </p>
          <p>
            <i class="bi bi-person-badge-fill"></i>
            NIP dapat digunakan sebagai alternatif login
          </p>
        </div>
      </div>
    </div>

    <!-- Right Section -->
    <div class="login-right col-md-6">
      <h2>Login Akun</h2>

      <!-- Pesan Error -->
      <?php if ($error): ?>
        <div class="alert alert-error">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <span><?php echo $error; ?></span>
        </div>
      <?php endif; ?>

      <form method="POST">
        <!-- Role Selector -->
        <div class="form-group">
          <label for="role" class="form-label">
            <i class="bi bi-person-badge-fill me-2"></i>Login Sebagai
          </label>
          <select id="role" name="role" class="form-control" required>
            <option value="admin" <?php if ($role == 'admin') echo 'selected'; ?>>Admin</option>
            <option value="guru" <?php if ($role == 'guru') echo 'selected'; ?>>Guru</option>
          </select>
        </div>

        <!-- Username -->
        <div class="form-group">
          <label for="username" class="form-label">
            <i class="bi bi-person-fill me-2"></i>Username
          </label>
          <div class="input-icon">
            <i class="bi bi-person-fill"></i>
            <input type="text" name="username" id="username" placeholder="Masukkan username" class="form-control">
          </div>
        </div>

        <!-- NIP -->
        <div class="form-group">
          <label for="nip" class="form-label">
            <i class="bi bi-person-badge me-2"></i>NIP (Alternatif)
          </label>
          <div class="input-icon">
            <i class="bi bi-person-badge"></i>
            <input type="text" name="nip" id="nip" placeholder="Masukkan NIP" class="form-control">
          </div>
          <div class="optional-text">
            *Gunakan username ATAU NIP untuk login
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

      <!-- Divider -->
      <div class="divider">
        <span>atau</span>
      </div>

      <!-- Links Section -->
      <div class="links">
        <a href="register-admin.php">
          <i class="bi bi-person-plus-fill"></i>Register Admin & Guru
        </a>
        <a href="../web-utama/index.php">
          <i class="bi bi-house-fill"></i>Kembali ke Beranda
        </a>
      </div>

      <!-- Footer -->
      <div class="footer">
        <p>&copy; <?php echo date("Y"); ?> <strong>SekolahKu</strong> | Semua Hak Dilindungi</p>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Validasi form - minimal salah satu dari username atau NIP harus diisi
    document.querySelector('form').addEventListener('submit', function(e) {
      const username = document.getElementById('username').value.trim();
      const nip = document.getElementById('nip').value.trim();

      if (!username && !nip) {
        e.preventDefault();
        alert('Silakan isi username ATAU NIP untuk login!');
        document.getElementById('username').focus();
      }
    });

    // Auto-focus logic
    document.addEventListener('DOMContentLoaded', function() {
      const usernameField = document.getElementById('username');
      const nipField = document.getElementById('nip');

      // Jika username diisi, kosongkan NIP dan sebaliknya
      usernameField.addEventListener('input', function() {
        if (this.value.trim() !== '') {
          nipField.value = '';
        }
      });

      nipField.addEventListener('input', function() {
        if (this.value.trim() !== '') {
          usernameField.value = '';
        }
      });
    });
  </script>
</body>

</html>