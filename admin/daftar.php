<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
  header("Location: ../login.php?msg=Silakan login terlebih dahulu");
  exit();
}

$page = basename($_SERVER['PHP_SELF']);
require_once "../db.php";
$db = new Database();
$conn = $db->connect();

// ==================== DELETE DATA ====================
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $stmt = $conn->prepare("SELECT file_path FROM books WHERE id=:id");
  $stmt->bindParam(':id', $id);
  $stmt->execute();
  $file = $stmt->fetch(PDO::FETCH_ASSOC);
  
  if ($file && file_exists(__DIR__ . '/../' . $file['file_path'])) {
    unlink(__DIR__ . '/../' . $file['file_path']);
  }

  $conn->prepare("DELETE FROM books WHERE id=:id")->execute([':id' => $id]);
  echo "<script>alert('Buku berhasil dihapus!'); location='daftar.php';</script>";
  exit;
}

// ==================== SEARCH & LIMIT ====================
$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$limit  = isset($_GET['limit']) ? $_GET['limit'] : 10;

// Jika pilih ALL → limitQuery kosong
$limitQuery = ($limit === "all") ? "" : "LIMIT " . intval($limit);

// ==================== QUERY ====================
if ($search != "") {
    $stmt = $conn->prepare("SELECT * FROM books 
                            WHERE title LIKE :s OR description LIKE :s
                            ORDER BY id ASC
                            $limitQuery"); 
    $stmt->bindValue(":s", "%$search%", PDO::PARAM_STR);
    $stmt->execute();
} else {
    $stmt = $conn->prepare("SELECT * FROM books 
                            ORDER BY id ASC
                            $limitQuery");
    $stmt->execute();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Daftar Buku | Tirta Bhagasasi</title>
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
  <header class="fixed top-0 left-0 right-0 bg-[#0052A1] text-white flex items-center justify-between px-4 py-3 shadow-md z-50">
    <div class="flex items-center gap-3">
      <button id="menu-btn" class="p-2 rounded hover:bg-[#004080]">☰</button>
      <h1 class="text-lg font-bold">Tirta Bhagasasi Admin</h1>
    </div>
    <a href="../logout.php" class="bg-red-600 px-3 py-1 rounded text-sm hover:bg-red-700">Logout</a>
  </header>

  <!-- SIDEBAR -->
<aside id="sidebar" class="sidebar fixed top-14 left-0 w-60 h-full bg-white shadow-lg pt-6">
  <nav class="flex flex-col space-y-2 px-4">

    <a href="index.php"
       class="flex items-center space-x-2 rounded px-3 py-2
       <?php echo ($page == 'index.php') ? 'bg-primary-light text-primary font-semibold' : 'text-gray-700 hover:bg-primary-light'; ?>">
        <ion-icon name="home-outline" class="text-xl"></ion-icon>
        <span>Dashboard</span>
    </a>

    <a href="upload.php"
       class="flex items-center space-x-2 rounded px-3 py-2
       <?php echo ($page == 'upload.php') ? 'bg-primary-light text-primary font-semibold' : 'text-gray-700 hover:bg-primary-light'; ?>">
        <ion-icon name="cloud-upload-outline" class="text-xl"></ion-icon>
        <span>Upload Buku</span>
    </a>

    <a href="daftar.php"
       class="flex items-center space-x-2 rounded px-3 py-2
       <?php echo ($page == 'daftar.php') ? 'bg-primary-light text-primary font-semibold' : 'text-gray-700 hover:bg-primary-light'; ?>">
        <ion-icon name="library-outline" class="text-xl"></ion-icon>
        <span>Daftar Buku</span>
    </a>

  </nav>
</aside>


  <!-- MAIN CONTENT -->
  <main id="content" class="pt-20 pl-64 pr-6 pb-10 transition-all">
    <div class="bg-white rounded-xl shadow p-6">
      <h2 class="text-2xl font-semibold text-black mb-4 text-center flex items-center justify-center gap-2"><ion-icon name="library-outline" class="text-3xl text-black"></ion-icon>Daftar Buku</h2>

<!-- SEARCH + FILTER SEBARIS -->
<div class="mb-4 flex items-center justify-between w-full">

  <!-- SEARCH BAR -->
  <form method="GET" class="flex items-center">
    <input 
      type="text" 
      name="search" 
      placeholder="Cari file..."
      value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
      class="border border-black px-2 py-1 rounded-l-lg w-48 text-sm focus:outline-none"
    >
    <button class="bg-[#0052A1] text-white px-3 py-1 rounded-r-lg text-sm hover:bg-[#003f7a]">
      Cari
    </button>
  </form>

  <!-- FILTER LIMIT -->
  <form method="GET" class="flex items-center gap-2">

    <?php if ($search != ""): ?>
      <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
    <?php endif; ?>

    <label class="text-sm text-gray-700">Tampilkan:</label>

    <select name="limit" onchange="this.form.submit()"
      class="border border-black rounded-lg px-2 py-1 text-sm cursor-pointer">
      <option value="5"   <?= ($limit == 5   ? "selected" : "") ?>>5</option>
      <option value="10"  <?= ($limit == 10  ? "selected" : "") ?>>10</option>
      <option value="20"  <?= ($limit == 20  ? "selected" : "") ?>>20</option>
      <option value="50"  <?= ($limit == 50  ? "selected" : "") ?>>50</option>
      <option value="100" <?= ($limit == 100 ? "selected" : "") ?>>100</option>
      <option value="all" <?= ($limit === "all" ? "selected" : "") ?>>all</option>

    </select>
  </form>
</div>


      <div class="overflow-x-auto">
        <table class="w-full border border-black border-collapse text-sm">
          <thead class="bg-[#0052A1] text-white">
            <tr>
              <th class="py-2 px-3 text-left border border-black">No</th>
              <th class="py-2 px-3 text-left border border-black">Judul</th>
              <th class="py-2 px-3 text-left border border-black">Deskripsi</th>
              <th class="py-2 px-3 text-left border border-black">File</th>
              <th class="py-2 px-3 text-left border border-black">Tanggal</th>
              <th class="py-2 px-3 text-center border border-black">Aksi</th>
            </tr>
          </thead>

          <tbody>
          <?php
          $no = 1;
          foreach ($stmt as $row):
            $tgl = date("d M Y, H:i", strtotime($row['created_at']));
            $flip = "../flipbook/index.php?file=" . urlencode($row['file_path']);
          ?>
            <tr class='hover:bg-gray-50'>
              <td class='py-2 px-3 border border-black'><?= $no ?></td>
              <td class='py-2 px-3 border border-black font-medium text-gray-800'><?= $row['title'] ?></td>
              <td class='py-2 px-3 border border-black text-gray-600'><?= $row['description'] ?></td>
              <td class='py-2 px-3 border border-black'>
                <a href='<?= $flip ?>' class='text-[#0052A1] underline'>Lihat</a>
              </td>
              <td class='py-2 px-3 border border-black text-gray-600'><?= $tgl ?></td>
              <td class='py-2 px-3 border border-black text-center'>
                <a href='edit.php?id=<?= $row['id'] ?>' class='text-yellow-600 hover:text-yellow-800' title='Edit'>✏️</a>
                <a href='?delete=<?= $row['id'] ?>' onclick='return confirm("Hapus buku ini?")'
                  class='text-red-600 hover:text-red-800 ml-2' title='Hapus'>🗑️</a>
              </td>
            </tr>
          <?php
            $no++;
          endforeach;
          ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <!-- FOOTER -->
  <footer class="fixed bottom-0 left-0 right-0 bg-[#0052A1] text-white text-center py-2 text-sm">
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
