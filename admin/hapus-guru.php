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
    header("Location: guru.php");
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
        header("Location: guru.php");
        exit;
    }

    if ($userData['role'] !== 'guru') {
        // Hanya guru yang boleh dihapus melalui file ini
        $_SESSION['flash'] = "notguru";
        header("Location: guru.php");
        exit;
    }

    // Hapus guru sesuai ID
    $stmt = $pdo->prepare("DELETE FROM petugas WHERE id_petugas = ?");
    $stmt->execute([$id]);

    // Redirect setelah hapus
    $_SESSION['flash'] = "deleted";
    header("Location: guru.php");
    exit;
} catch (Exception $e) {
    // Jika terjadi error
    $_SESSION['flash'] = "error";
    header("Location: guru.php");
    exit;
}
