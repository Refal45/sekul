<?php
session_start();
require_once "../koneksi.php"; // koneksi PDO

// Pastikan hanya guru yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    header("Location: login-admin.php");
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['flash'] = "error";
    header("Location: siswa.php");
    exit;
}

try {
    // Cek apakah siswa ada
    $stmt = $pdo->prepare("SELECT * FROM siswa WHERE id_siswa = ?");
    $stmt->execute([$id]);
    $siswa = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$siswa) {
        $_SESSION['flash'] = "error";
        header("Location: siswa.php");
        exit;
    }

    // Hapus siswa
    $delete = $pdo->prepare("DELETE FROM siswa WHERE id_siswa = ?");
    $delete->execute([$id]);

    $_SESSION['flash'] = "deleted";
    header("Location: siswa.php");
    exit;
} catch (PDOException $e) {
    error_log("Error hapus siswa: " . $e->getMessage());
    $_SESSION['flash'] = "error";
    header("Location: siswa.php");
    exit;
}
