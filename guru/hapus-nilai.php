<?php
session_start();
require_once "../koneksi.php"; // koneksi PDO

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    header("Location: login-admin.php");
    exit;
}

// Validasi ID nilai
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: nilai.php?status=invalid");
    exit;
}

$id_nilai = (int) $_GET['id'];

// Hapus data nilai
try {
    $sql = "DELETE FROM nilai WHERE id_nilai = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id_nilai]);

    // Redirect setelah hapus
    header("Location: nilai.php?status=deleted");
    exit;
} catch (PDOException $e) {
    // Jika error
    header("Location: nilai.php?status=error&msg=" . urlencode($e->getMessage()));
    exit;
}
