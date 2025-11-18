<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
  header("Location: ../login.php?msg=Silakan login terlebih dahulu");
  exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Admin | Tirta Bhagasasi</title>

  <!-- TailwindCSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    :root {
      --primary-color: #0066B3;      /* Biru utama (sesuai logo) */
      --primary-light: #E6F2FF;      /* Biru muda (background aktif) */
      --primary-dark: #004C80;       /* Biru gelap (hover / shadow) */
    }

    .sidebar {
      transition: all 0.3s;
    }
    .sidebar.hide {
      margin-left: -250px;
    }

    .bg-primary {
      background-color: var(--primary-color);
    }
    .bg-primary-dark {
      background-color: var(--primary-dark);
    }
    .bg-primary-light {
      background-color: var(--primary-light);
    }
    .text-primary {
      color: var(--primary-color);
    }
  </style>
</head>
<body class="bg-gray-100 font-sans">

  <!-- HEADER -->
  <header class="fixed top-0 left-0 right-0 bg-primary text-white flex items-center justify-between px-4 py-3 shadow-md z-50">
    <div class="flex items-center gap-3">
      <button id="menu-btn" class="p-2 rounded hover:bg-primary-dark">
        ☰
      </button>
      <h1 class="text-lg font-bold">Tirta Bhagasasi Admin</h1>
    </div>
    <a href="../logout.php" class="bg-red-600 px-3 py-1 rounded text-sm hover:bg-red-700">Logout</a>
  </header>

  <!-- SIDEBAR -->
  <aside id="sidebar" class="sidebar fixed top-14 left-0 w-60 h-full bg-white shadow-lg pt-6">
    <nav class="flex flex-col space-y-2 px-4">
      <a href="index.php" class="bg-primary-light text-primary font-semibold rounded px-3 py-2">🏠 Dashboard</a>
      <a href="upload.php" class="text-gray-700 hover:bg-primary-light rounded px-3 py-2">📤 Upload Buku</a>
      <a href="daftar.php" class="text-gray-700 hover:bg-primary-light rounded px-3 py-2">📚 Daftar Buku</a>
    </nav>
  </aside>

  <!-- CONTENT -->
  <main id="content" class="pt-20 pl-64 pr-6 pb-10 transition-all">
    <div class="bg-white rounded-xl shadow p-6">
      <h2 class="text-2xl font-semibold text-primary mb-3">Selamat Datang, Admin 👋</h2>
      <p class="text-gray-600">Gunakan menu di sidebar untuk mengelola koleksi buku digital.</p>
    </div>
  </main>

  <!-- FOOTER -->
  <footer class="fixed bottom-0 left-0 right-0 bg-primary text-white text-center py-2 text-sm">
    © <?= date("Y") ?> Tirta Bhagasasi - Digital Library
  </footer>

  <!-- SCRIPT -->
  <script>
    const btn = document.getElementById('menu-btn');
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');

    btn.addEventListener('click', () => {
      sidebar.classList.toggle('hide');
      if (sidebar.classList.contains('hide')) {
        content.classList.remove('pl-64');
      } else {
        content.classList.add('pl-64');
      }
    });
  </script>
</body>
</html>
