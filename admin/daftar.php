<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
  header("Location: ../login.php?msg=Silakan login terlebih dahulu");
  exit();
}

require_once "../db.php";
$db = new Database();
$conn = $db->connect();

// Hapus data buku
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Daftar Buku | Tirta Bhagasasi</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .sidebar { transition: all 0.3s; }
    .sidebar.hide { margin-left: -250px; }
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
      <a href="index.php" class="text-gray-700 hover:bg-blue-50 rounded px-3 py-2">🏠 Dashboard</a>
      <a href="upload.php" class="text-gray-700 hover:bg-blue-50 rounded px-3 py-2">📤 Upload Buku</a>
      <a href="daftar.php" class="bg-[#E6F0FF] text-[#0052A1] font-semibold rounded px-3 py-2">📚 Daftar Buku</a>
    </nav>
  </aside>

  <!-- MAIN CONTENT -->
  <main id="content" class="pt-20 pl-64 pr-6 pb-10 transition-all">
    <div class="bg-white rounded-xl shadow p-6">
      <h2 class="text-2xl font-semibold text-[#0052A1] mb-4 text-center">📚 Daftar Buku</h2>

      <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm">
          <thead class="bg-[#0052A1] text-white">
            <tr>
              <th class="py-2 px-3 text-left">No</th>
              <th class="py-2 px-3 text-left">Judul</th>
              <th class="py-2 px-3 text-left">Deskripsi</th>
              <th class="py-2 px-3 text-left">File</th>
              <th class="py-2 px-3 text-left">Tanggal</th>
              <th class="py-2 px-3 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php
          $stmt = $conn->query("SELECT * FROM books ORDER BY id ASC");
          $no = 1;
          foreach ($stmt as $row) {
            $tgl = date("d M Y, H:i", strtotime($row['created_at']));
            $flip = "../flipbook/index.php?file=" . urlencode($row['file_path']);
            echo "
              <tr class='border-b hover:bg-gray-50'>
                <td class='py-2 px-3'>$no</td>
                <td class='py-2 px-3 font-medium text-gray-800'>{$row['title']}</td>
                <td class='py-2 px-3 text-gray-600'>{$row['description']}</td>
                <td class='py-2 px-3'>
                  <a href='$flip' target='_blank' class='text-[#0052A1] underline'>Lihat</a>
                </td>
                <td class='py-2 px-3 text-gray-600'>$tgl</td>
                <td class='py-2 px-3 text-center'>
                  <a href='edit.php?id={$row['id']}' class='text-yellow-600 hover:text-yellow-800' title='Edit'>✏️</a>
                  <a href='?delete={$row['id']}' onclick='return confirm(\"Hapus buku ini?\")' class='text-red-600 hover:text-red-800 ml-2' title='Hapus'>🗑️</a>
                </td>
              </tr>
            ";
            $no++;
          }
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
      sidebar.classList.toggle('hide');
      content.classList.toggle('pl-64');
    });
  </script>

</body>
</html>
