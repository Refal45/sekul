<?php
session_start();
require '../koneksi.php';

$error = "";
$success = "";

// Ambil daftar username admin dan guru untuk dropdown
$stmt = $pdo->prepare("SELECT username, role FROM petugas WHERE role IN ('admin','guru')");
$stmt->execute();
$usernames = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Ambil user dari tabel petugas
    $stmt = $pdo->prepare("SELECT * FROM petugas WHERE username=? AND role IN ('admin','guru')");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Update password
        $stmt = $pdo->prepare("UPDATE petugas SET password=? WHERE id_petugas=?");
        if ($stmt->execute([$hash, $user['id_petugas']])) {
            $success = "Password berhasil diubah! Silakan <a href='login-admin.php' class='underline'>login</a>.";
        } else {
            $error = "Terjadi kesalahan saat reset password!";
        }
    } else {
        $error = "User tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        function togglePassword() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.innerHTML = '👁️';
            } else {
                pwd.type = 'password';
                icon.innerHTML = '👁️';
            }
        }
    </script>
</head>

<body class="bg-gray-50 font-sans text-gray-700">

    <div class="min-h-screen flex flex-col justify-center items-center p-6">

        <div class="w-full max-w-4xl bg-white shadow-xl rounded-3xl border border-gray-200 flex flex-col md:flex-row overflow-hidden">

            <!-- Bagian Kiri: Logo dan Info -->
            <div class="md:w-1/2 bg-blue-50 flex flex-col items-center justify-center p-10 border-r border-gray-200">
                <img src="https://th.bing.com/th/id/OIP.8LqneQAVfbtv4k0nw7CXYAHaHa?w=177&h=180&c=7&r=0&o=7&dpr=1.2&pid=1.7&rm=3"
                    alt="Logo Sekolah"
                    class="w-32 h-32 mb-6 rounded-full border-2 border-blue-300 p-2">
                <h1 class="text-4xl font-bold text-gray-800 mb-2 text-center">Reset Password</h1>
                <p class="text-gray-600 text-center mb-4">Silakan masukkan username dan password baru Anda.</p>
            </div>

            <!-- Bagian Kanan: Form Reset Password -->
            <div class="md:w-1/2 p-10 flex flex-col justify-center">

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

                <!-- Form Reset Password -->
                <form method="POST" class="space-y-6">

                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-600 mb-1">Pilih Username</label>
                        <div class="relative">
                            <span class="absolute left-3 top-3.5 text-gray-400"><i class="fa-solid fa-user"></i></span>
                            <select name="username" id="username" required
                                class="w-full pl-10 pr-3 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="" disabled selected>Pilih Username</option>
                                <?php foreach ($usernames as $u): ?>
                                    <option value="<?= htmlspecialchars($u['username']) ?>">
                                        <?= htmlspecialchars($u['username']) ?> (<?= $u['role'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Password Baru -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-600 mb-1">Password Baru</label>
                        <div class="relative">
                            <span class="absolute left-3 top-3.5 text-gray-400"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="password" id="password" placeholder="Password Baru" required
                                class="w-full pl-10 pr-10 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <button type="button" onclick="togglePassword()"
                                class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 transition" id="toggleIcon">
                                👁️
                            </button>
                        </div>
                    </div>

                    <!-- Tombol Submit -->
                    <button type="submit"
                        class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold flex items-center justify-center gap-2 hover:bg-blue-700">
                        <i class="fa-solid fa-key"></i> Reset Password
                    </button>

                </form>

                <!-- Link Kembali -->
                <div class="text-center text-gray-500 text-sm mt-6 space-x-4">
                    <a href="login-admin.php" class="underline hover:text-blue-600">← Kembali ke Login</a>
                    <a href="forgot-password.php" class="underline hover:text-blue-600">← Kembali ke Forgot Password</a>
                </div>

            </div>

        </div>

        <footer class="mt-10 text-center text-gray-400 text-sm">
            &copy; 2025 Sekolah ABC. Semua hak cipta dilindungi.
        </footer>

    </div>

</body>

</html>