<?php
session_start();
require_once "../koneksi.php"; // koneksi PDO

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login-admin.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: admin.php");
    exit;
}

try {
    // Cek apakah user dengan id tersebut ada
    $cekUser = $pdo->prepare("SELECT role FROM petugas WHERE id_petugas = ?");
    $cekUser->execute([$id]);
    $userData = $cekUser->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        // Tidak ada user dengan id tsb
        $_SESSION['flash'] = "notfound";
        header("Location: admin.php");
        exit;
    }

    if ($userData['role'] !== 'admin') {
        // Hanya admin yang boleh dihapus
        $_SESSION['flash'] = "notadmin";
        header("Location: admin.php");
        exit;
    }

    // Hitung jumlah admin
    $check = $pdo->query("SELECT COUNT(*) FROM petugas WHERE role='admin'");
    $remainingAdmins = $check->fetchColumn();

    // Cegah hapus admin terakhir
    if ($remainingAdmins == 1) {
        $_SESSION['flash'] = "last_admin";
        header("Location: admin.php");
        exit;
    }

    // Cegah admin menghapus dirinya sendiri jika tersisa 1 (opsional)
    if ($_SESSION['id_petugas'] == $id && $remainingAdmins == 1) {
        $_SESSION['flash'] = "cannot_delete_yourself";
        header("Location: admin.php");
        exit;
    }

    // Hapus admin sesuai ID
    $stmt = $pdo->prepare("DELETE FROM petugas WHERE id_petugas = ?");
    $stmt->execute([$id]);

    // Redirect setelah hapus
    $_SESSION['flash'] = "deleted";
    header("Location: admin.php");
    exit;
} catch (Exception $e) {
    // Jika terjadi error
    $_SESSION['flash'] = "error";
    header("Location: admin.php");
    exit;
}
