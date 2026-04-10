<?php
// Include konfigurasi database
require_once '../config.php';

// Start session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destroy semua session
session_unset();
session_destroy();

// Redirect ke halaman login
header("Location: login.php");
exit;
?>