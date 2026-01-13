<?php
session_start();
require_once "../koneksi.php"; // koneksi PDO

// Pastikan hanya guru yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    header("Location: login-admin.php");
    exit;
}

// Validasi ID kehadiran
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: kehadiran.php?status=invalid");
    exit;
}

$id_kehadiran = (int) $_GET['id'];

// Hapus data kehadiran
try {
    $sql = "DELETE FROM kehadiran WHERE id_kehadiran = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id_kehadiran]);

    // Redirect setelah hapus
    header("Location: kehadiran.php?status=deleted");
    exit;
} catch (PDOException $e) {
    // Jika error
    header("Location: kehadiran.php?status=error&msg=" . urlencode($e->getMessage()));
    exit;
}
