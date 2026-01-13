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
    header("Location: kelas.php"); // redirect ke halaman kelas
    exit;
}

try {
    // Cek apakah kelas dengan id tersebut ada
    $cekKelas = $pdo->prepare("SELECT * FROM kelas WHERE id_kelas = ?");
    $cekKelas->execute([$id]);
    $kelasData = $cekKelas->fetch(PDO::FETCH_ASSOC);

    if (!$kelasData) {
        // Tidak ada kelas dengan id tsb
        $_SESSION['flash'] = "notfound";
        header("Location: kelas.php");
        exit;
    }

    // Hapus kelas sesuai ID
    $stmt = $pdo->prepare("DELETE FROM kelas WHERE id_kelas = ?");
    $stmt->execute([$id]);

    // Redirect setelah hapus
    $_SESSION['flash'] = "deleted";
    header("Location: kelas.php");
    exit;
} catch (Exception $e) {
    // Jika terjadi error
    $_SESSION['flash'] = "error";
    header("Location: kelas.php");
    exit;
}
