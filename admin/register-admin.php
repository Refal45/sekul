<?php
session_start();
require '../koneksi.php';

$error = "";
$success = "";
$role = "admin"; // default value

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $nip = trim($_POST['nip']);
    
    // Konversi NIP kosong menjadi NULL
    $nip = empty($nip) ? null : $nip;

    // Validasi NIP (hanya jika tidak NULL)
    if ($nip !== null) {
        // Cek apakah NIP sudah terdaftar
        $stmt = $pdo->prepare("SELECT * FROM petugas WHERE nip = ?");
        $stmt->execute([$nip]);
        if ($stmt->rowCount() > 0) {
            $error = "❌ NIP sudah digunakan!";
        }
    }

    // Cek apakah username sudah terdaftar
    if (empty($error)) {
        $stmt = $pdo->prepare("SELECT * FROM petugas WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->rowCount() > 0) {
            $error = "❌ Username sudah digunakan!";
        } else {
            // Enkripsi password
            $hash = password_hash($password, PASSWORD_DEFAULT);

            try {
                // Simpan data ke database
                $stmt = $pdo->prepare("INSERT INTO petugas (nama_petugas, username, password, role, nip) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$nama, $username, $hash, $role, $nip])) {
                    $success = "✅ Registrasi berhasil! Silakan <a href='login-admin.php'>login di sini</a>.";
                } else {
                    $error = "⚠️ Terjadi kesalahan saat registrasi. Silakan coba lagi.";
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    // Handle duplicate entry error
                    if (strpos($e->getMessage(), 'nip') !== false) {
                        $error = "❌ NIP sudah digunakan!";
                    } else if (strpos($e->getMessage(), 'username') !== false) {
                        $error = "❌ Username sudah digunakan!";
                    } else {
                        $error = "⚠️ Data sudah ada dalam sistem. Silakan cek username dan NIP.";
                    }
                } else {
                    $error = "⚠️ Terjadi kesalahan sistem: " . $e->getMessage();
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
    <title>Register Admin & Guru | Sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="https://th.bing.com/th/id/OIP.8LqneQAVfbtv4k0nw7CXYAHaHa?w=177&h=180&c=7&r=0&o=7&dpr=1.2&pid=1.7&rm=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-50 font-sans text-gray-700">

    <div class="min-h-screen flex flex-col justify-center items-center p-6">

        <div class="w-full max-w-3xl bg-white shadow-xl rounded-3xl border border-gray-200 flex flex-col md:flex-row overflow-hidden">

            <!-- Bagian Kiri: Info dan Logo -->
            <div class="md:w-1/2 bg-blue-50 flex flex-col items-center justify-center p-10 border-r border-gray-200">
                <img src="https://th.bing.com/th/id/OIP.8LqneQAVfbtv4k0nw7CXYAHaHa?w=177&h=180&c=7&r=0&o=7&dpr=1.2&pid=1.7&rm=3"
                    alt="Logo Sekolah"
                    class="w-32 h-32 mb-6 rounded-full border-2 border-blue-300 p-2">
                <h1 class="text-4xl font-bold text-gray-800 mb-2 text-center">Daftar Akun Admin & Guru</h1>
                <p class="text-gray-600 text-center mb-4">Buat akun baru sebagai Admin atau Guru untuk mengakses sistem</p>
            </div>

            <!-- Bagian Kanan: Form Register -->
            <div class="md:w-1/2 p-10 flex flex-col justify-center">

                <h2 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Register Akun</h2>

                <!-- Pesan Error -->
                <?php if ($error): ?>
                    <div class="bg-red-100 text-red-700 px-4 py-3 rounded-md text-center flex items-center justify-center gap-2 mb-4">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <!-- Pesan Sukses -->
                <?php if ($success): ?>
                    <div class="bg-green-100 text-green-700 px-4 py-3 rounded-md text-center flex items-center justify-center gap-2 mb-4">
                        <i class="fa-solid fa-circle-check"></i>
                        <span><?php echo $success; ?></span>
                    </div>
                <?php endif; ?>

                <!-- Form Register -->
                <form method="POST" class="space-y-6">

                    <div>
                        <label for="nama" class="block text-sm font-medium text-gray-600 mb-1">Nama Lengkap</label>
                        <input type="text" name="nama" id="nama" placeholder="Nama Lengkap" required
                            class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-600 mb-1">Username</label>
                        <input type="text" name="username" id="username" placeholder="Username" required
                            class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="nip" class="block text-sm font-medium text-gray-600 mb-1">NIP</label>
                        <input type="text" name="nip" id="nip" placeholder="Nomor Induk Pegawai"
                            class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">*Opsional, tetapi disarankan untuk kelengkapan data</p>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-600 mb-1">Password</label>
                        <input type="password" name="password" id="password" placeholder="Password" required
                            class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-600 mb-1">Daftar Sebagai</label>
                        <select id="role" name="role" required
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500">
                            <option value="admin" <?php if ($role == 'admin') echo 'selected'; ?>>Admin</option>
                            <option value="guru" <?php if ($role == 'guru') echo 'selected'; ?>>Guru</option>
                        </select>
                    </div>

                    <button type="submit"
                        class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold flex items-center justify-center gap-2 hover:bg-blue-700">
                        <i class="fa-solid fa-user-plus"></i> Register
                    </button>

                </form>

                <!-- Links Tambahan -->
                <div class="text-center text-gray-500 text-sm mt-6 space-y-2">
                    <p>Sudah punya akun? <a href="login-admin.php" class="underline hover:text-blue-600">Login di sini</a></p>
                </div>

            </div>

        </div>

        <footer class="mt-10 text-center text-gray-400 text-sm">
            &copy; 2025 Sekolah ABC. Semua hak cipta dilindungi.
        </footer>

    </div>

</body>

</html>