<?php
session_start();
require '../koneksi.php';

$error = "";
$success = "";

$role = isset($_POST['role']) ? $_POST['role'] : 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $role = $_POST['role'];

    // Cek apakah user ada di database
    $stmt = $pdo->prepare("SELECT * FROM petugas WHERE username=? AND role=?");
    $stmt->execute([$username, $role]);
    $user = $stmt->fetch();

    if ($user) {
        // Generate token reset
        $token = bin2hex(random_bytes(32));

        // Simpan token ke database
        $stmt = $pdo->prepare("INSERT INTO password_resets (username, token, created_at, expired_at) VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 1 HOUR))");
        $stmt->execute([$username, $token]);

        // Redirect ke halaman reset-password.php
        header("Location: reset-password.php?username=" . urlencode($username) . "&token=" . $token . "&role=" . $role);
        exit;
    } else {
        $error = "Username tidak ditemukan untuk role $role!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password | Sekolah</title>
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
                <h1 class="text-4xl font-bold text-gray-800 mb-2 text-center">Lupa Password</h1>
                <p class="text-gray-600 text-center mb-4">Masukkan username dan pilih role Anda untuk menerima token reset password.</p>

                <div class="bg-blue-100 p-4 rounded-xl w-full text-center text-gray-700">
                    <p><i class="fa-solid fa-circle-info mr-2"></i> Token hanya berlaku selama 1 jam.</p>
                    <p class="mt-2"><i class="fa-solid fa-lock mr-2"></i> Data Anda aman dan terenkripsi.</p>
                </div>
            </div>

            <!-- Bagian Kanan: Form Lupa Password -->
            <div class="md:w-1/2 p-10 flex flex-col justify-center">

                <!-- Pesan Error -->
                <?php if ($error): ?>
                    <div class="bg-red-100 text-red-700 px-4 py-3 rounded-md text-center flex items-center justify-center gap-2 mb-4">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <!-- Form Forgot Password -->
                <form method="POST" class="space-y-6">

                    <!-- Role Selector -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-600 mb-1">Login Sebagai</label>
                        <select id="role" name="role"
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="admin" <?php if ($role == 'admin') echo 'selected'; ?>>Admin</option>
                            <option value="guru" <?php if ($role == 'guru') echo 'selected'; ?>>Guru</option>
                        </select>
                    </div>

                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-600 mb-1">Username</label>
                        <div class="relative">
                            <input type="text" name="username" id="username" placeholder="Username" required
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 pl-10">
                            <i class="fa-solid fa-user absolute left-3 top-3.5 text-gray-400"></i>
                        </div>
                    </div>

                    <!-- Tombol Kirim Token -->
                    <button type="submit"
                        class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold flex items-center justify-center gap-2 hover:bg-blue-700">
                        <i class="fa-solid fa-paper-plane"></i> Kirim Token
                    </button>

                </form>

                <!-- Link Kembali -->
                <div class="text-center text-gray-500 text-sm mt-6">
                    <a href="login-admin.php" class="underline hover:text-blue-600">Kembali ke Login</a>
                </div>

            </div>

        </div>

        <footer class="mt-10 text-center text-gray-400 text-sm">
            &copy; 2025 Sekolah ABC. Semua hak cipta dilindungi.
        </footer>

    </div>

</body>

</html>