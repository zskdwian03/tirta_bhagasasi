<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
  header("Location: ../login.php?msg=Silakan login terlebih dahulu");
  exit();
}

require_once "../db.php";
$db = new Database();
$conn = $db->connect();

// QUERY total buku
$totalBuku = $conn->query("SELECT COUNT(*) FROM books")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Admin | Tirta Bhagasasi</title>
  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    :root {
      --primary-color: #0052A1;
      --primary-light: #E6F2FF;
      --primary-dark: #003f7a;
    }

    .sidebar.collapsed {
      width: 60px;
    }

    .sidebar.collapsed span {
      display: none;
    }

    .sidebar.collapsed nav a {
      justify-content: center !important;
      padding-left: 0 !important;
      padding-right: 0 !important;
    }

    .sidebar.collapsed ion-icon {
      margin-right: 0 !important;
      font-size: 22px; 
    }

    #content.collapsed {
      padding-left: 80px;
    }

    .bg-primary { background-color: var(--primary-color); }
    .bg-primary-dark { background-color: var(--primary-dark); }
    .bg-primary-light { background-color: var(--primary-light); }
    .text-primary { color: var(--primary-color); }
  </style>
</head>
<body class="bg-gray-100 font-sans">

  <!-- HEADER -->
  <header class="fixed top-0 left-0 right-0 bg-primary text-white flex items-center justify-between px-4 py-3 shadow-md z-50">
    <div class="flex items-center gap-3">
      <button id="menu-btn" class="p-2 rounded hover:bg-primary-dark">☰</button>
      <h1 class="text-lg font-bold">Tirta Bhagasasi Admin</h1>
    </div>
    <a href="../logout.php" class="bg-red-600 px-3 py-1 rounded text-sm hover:bg-red-700">Logout</a>
  </header>

  <!-- SIDEBAR -->
  <aside id="sidebar" class="sidebar fixed top-14 left-0 w-60 h-full bg-white shadow-lg pt-6">
    <nav class="flex flex-col space-y-2 px-4">

      <a href="index.php" class="bg-primary-light text-primary font-semibold rounded px-3 py-2 flex items-center space-x-2">
        <ion-icon name="home-outline" class="text-lg"></ion-icon>
        <span>Dashboard</span>
      </a>

      <a href="upload.php" class="text-gray-700 hover:bg-primary-light rounded px-3 py-2 flex items-center space-x-2">
        <ion-icon name="cloud-upload-outline" class="text-lg"></ion-icon>
        <span>Upload Buku</span>
      </a>

      <a href="daftar.php" class="text-gray-700 hover:bg-primary-light rounded px-3 py-2 flex items-center space-x-2">
        <ion-icon name="library-outline" class="text-lg"></ion-icon>
        <span>Daftar Buku</span>
      </a>

    </nav>
  </aside>

  <!-- CONTENT -->
  <main id="content" class="pt-20 pl-64 pr-6 pb-10 transition-all">

    <!-- CARD SELAMAT DATANG -->
    <div class="bg-white rounded-xl shadow p-6 mb-4">
      <h2 class="text-2xl font-semibold text-primary mb-3">Selamat Datang, Admin 👋</h2>
      <p class="text-gray-600">Gunakan menu di sidebar untuk mengelola koleksi buku digital.</p>
    </div>

    <!-- CARD TOTAL BUKU (TANPA SHADOW) -->
    <div class="bg-white rounded-xl p-6 flex items-center gap-4 border border-gray-200">
      <div class="p-3 bg-primary-light rounded-full">
        <ion-icon name="library-outline" class="text-3xl text-primary"></ion-icon>
      </div>
      <div>
        <h3 class="text-xl font-semibold text-primary">Total Buku Yang Sudah Diupload</h3>
        <p class="text-gray-700 text-lg font-bold"><?= $totalBuku ?> Buku</p>
      </div>
    </div>

  </main>

  <!-- FOOTER -->
  <footer class="fixed bottom-0 left-0 right-0 bg-primary text-white text-center py-2 text-sm">
    © <?= date("Y") ?> Tirta Bhagasasi - Digital Library
  </footer>

  <script>
    const btn = document.getElementById('menu-btn');
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');

    btn.addEventListener('click', () => {
      sidebar.classList.toggle('collapsed');
      content.classList.toggle('collapsed');
    });
  </script>
</body>
</html>
