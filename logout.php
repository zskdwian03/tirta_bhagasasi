<?php
session_start();

// Hapus semua data session
session_unset();
session_destroy();

// Redirect ke halaman login
header("Location: login.php?msg=Anda telah logout");
exit();
?>
