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
    header("Location: mapel.php");
    exit;
}

try {
    // Cek apakah mapel dengan id tersebut ada
    $cekMapel = $pdo->prepare("SELECT id_mapel FROM mata_pelajaran WHERE id_mapel = ?");
    $cekMapel->execute([$id]);
    $mapelData = $cekMapel->fetch(PDO::FETCH_ASSOC);

    if (!$mapelData) {
        // Tidak ada mapel dengan id tsb
        $_SESSION['flash'] = "notfound";
        header("Location: mapel.php");
        exit;
    }

    // Hapus mapel sesuai ID
    $stmt = $pdo->prepare("DELETE FROM mata_pelajaran WHERE id_mapel = ?");
    $stmt->execute([$id]);

    // Redirect setelah hapus
    $_SESSION['flash'] = "deleted";
    header("Location: mapel.php");
    exit;
} catch (Exception $e) {
    // Jika terjadi error
    $_SESSION['flash'] = "error";
    header("Location: mapel.php");
    exit;
}
