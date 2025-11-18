<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =======================
   AUTO LOGOUT (15 menit)
======================= */
$timeout = 900; 

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: login.php?msg=Sesi berakhir, silakan login lagi");
    exit();
}

$_SESSION['last_activity'] = time();

/* =======================
   CEK LOGIN
======================= */
if (!isset($_SESSION['user'])) {

    // Biar tidak looping login.php → auth.php → login.php
    if (basename($_SERVER['PHP_SELF']) !== "login.php") {
        header("Location: /buku_panduan-useradmin/login.php?msg=Silakan login terlebih dahulu");
        exit();
    }
}

/* =======================
   CEK ROLE & BATAS Akses Folder
======================= */
$currentFolder = basename(dirname($_SERVER['PHP_SELF']));

// Admin akses folder user → blok
if ($_SESSION['role'] === 'admin' && $currentFolder === "user") {
    header("Location: /buku_panduan-useradmin/admin/index.php?msg=Akses ditolak");
    exit();
}

// User akses folder admin → blok
if ($_SESSION['role'] === 'user' && $currentFolder === "admin") {
    header("Location: /buku_panduan-useradmin/user/index.php?msg=Akses ditolak");
    exit();
}
?>
