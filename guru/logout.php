<?php
session_start();

// Hapus semua session
$_SESSION = [];
session_unset();
session_destroy();

// Redirect ke halaman login
header("Location: ../admin/login-admin.php");
exit;
