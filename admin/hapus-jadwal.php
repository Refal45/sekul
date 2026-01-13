<?php
session_start();
require_once "../koneksi.php"; // koneksi PDO

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login-admin.php");
    exit;
}

// Validasi ID jadwal
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: jadwal.php?status=invalid");
    exit;
}

$id_jadwal = (int) $_GET['id'];

// Hapus data jadwal
try {
    $sql = "DELETE FROM jadwal WHERE id_jadwal = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id_jadwal]);

    // Redirect setelah hapus
    header("Location: jadwal.php?status=deleted");
    exit;
} catch (PDOException $e) {
    // Jika error
    echo "Error: " . $e->getMessage();
}
