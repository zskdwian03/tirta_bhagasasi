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

// Hitung user pending untuk notif
$notif = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='user' AND status='pending'")->fetch(PDO::FETCH_ASSOC);
$pendingCount = $notif['total'];

// Hapus buku
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

// Edit buku
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['edit_id'])) {
  $id    = $_POST['edit_id'];
  $title = $_POST['edit_title'];
  $desc  = $_POST['edit_description'];

  $stmt = $conn->prepare("UPDATE books SET title=:title, description=:description WHERE id=:id");
  $stmt->bindParam(':title', $title);
  $stmt->bindParam(':description', $desc);
  $stmt->bindParam(':id', $id);
  $stmt->execute();

  echo "<script>alert('Buku berhasil diperbarui!'); location='daftar.php';</script>";
  exit;
}

$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$limit  = isset($_GET['limit']) ? $_GET['limit'] : 10;
$limitQuery = ($limit === "all") ? "" : "LIMIT " . intval($limit);

if ($search != "") {
  $stmt = $conn->prepare("SELECT * FROM books 
                          WHERE title LIKE :title OR description LIKE :desc
                          ORDER BY id ASC $limitQuery"); 
  $stmt->bindValue(":title", "%$search%", PDO::PARAM_STR);
  $stmt->bindValue(":desc", "%$search%", PDO::PARAM_STR);
  $stmt->execute();
} else {
  $stmt = $conn->prepare("SELECT * FROM books ORDER BY id ASC $limitQuery");
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
  --primary-color: #04376B;
  --primary-light: #E8EFF6;
  --primary-dark: #03294B;
  --overlay-dark: rgba(0,0,0,0.35);
  --border-soft: rgba(0,0,0,0.06);
}

.sidebar.collapsed { width: 60px; }
.sidebar.collapsed span { display: none; }
.sidebar.collapsed nav a { justify-content: center !important; padding-left:0 !important; padding-right:0 !important; }
.sidebar.collapsed ion-icon { margin-right:0 !important; font-size:22px; }
#content.collapsed { padding-left:80px; }

.bg-primary { background-color: var(--primary-color); }
.bg-primary-dark { background-color: var(--primary-dark); }
.bg-primary-light { background-color: var(--primary-light); }
.text-primary { color: var(--primary-color); }

input:focus, textarea:focus { outline:none; border-color:var(--primary-color) !important; box-shadow:0 0 0 2px rgba(4,55,107,0.25); }

.modal-overlay {
  position: fixed; top:0; left:0; width:100%; height:100%;
  background: var(--overlay-dark); display:flex; align-items:center; justify-content:center;
  z-index:200; backdrop-filter: blur(3px);
}
.modal-box {
  background:white; width:90%; max-width:620px; padding:28px; border-radius:18px;
  box-shadow:0 6px 22px rgba(0,0,0,0.15); border:1px solid var(--border-soft);
  animation: fadeIn 0.25s ease-in-out;
}
@keyframes fadeIn { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }

/* Notif badge */
.notif-badge {
  position: absolute; top:-5px; right:-5px;
  background:red; color:white; font-size:0.7rem; font-weight:bold; padding:0 5px; border-radius:9999px;
}

  .notification-shake {
    animation: shake 0.4s ease-in-out infinite alternate;
  }
  @keyframes shake {
    0% { transform: rotate(-8deg); }
    100% { transform: rotate(8deg); }
  }
</style>
</head>
<body class="bg-gray-100 font-sans">

<!-- HEADER -->
<header class="fixed top-0 left-0 right-0 bg-primary text-white flex items-center justify-between px-4 py-3 shadow-md z-50">
  <div class="flex items-center gap-3">
    <button id="menu-btn" class="p-2 rounded hover:bg-primary-dark">☰</button>
    <h1 class="text-lg font-bold">Tirta Bhagasasi Admin</h1>
  </div>

  <div class="flex items-center gap-4">
    <!-- Notifikasi -->
    <a href="users.php" class="relative">
            <ion-icon name="notifications-outline" 
        class="text-2xl cursor-pointer hover:text-gray-300 <?= $pendingCount > 0 ? 'notification-shake' : '' ?>">
      </ion-icon>
      <?php if($pendingCount > 0): ?>
        <span class="notif-badge"><?= $pendingCount ?></span>
      <?php endif; ?>
    </a>
    <!-- Logout -->
    <a href="../logout.php" class="bg-red-600 px-3 py-1 rounded text-sm hover:bg-red-700">Logout</a>
  </div>
</header>

<!-- SIDEBAR (tidak diubah) -->
<aside id="sidebar" class="sidebar fixed top-14 left-0 w-60 h-full bg-white shadow-lg pt-6">
  <nav class="flex flex-col space-y-2 px-4">
    <a href="index.php" class="flex items-center space-x-2 rounded px-3 py-2 <?= ($page=='index.php')?'bg-primary-light text-primary font-semibold':'text-gray-700 hover:bg-primary-light' ?>">
      <ion-icon name="home-outline" class="text-xl"></ion-icon><span>Dashboard</span>
    </a>
    <a href="upload.php" class="flex items-center space-x-2 rounded px-3 py-2 <?= ($page=='upload.php')?'bg-primary-light text-primary font-semibold':'text-gray-700 hover:bg-primary-light' ?>">
      <ion-icon name="cloud-upload-outline" class="text-xl"></ion-icon><span>Upload Buku</span>
    </a>
    <a href="daftar.php" class="flex items-center space-x-2 rounded px-3 py-2 <?= ($page=='daftar.php')?'bg-primary-light text-primary font-semibold':'text-gray-700 hover:bg-primary-light' ?>">
      <ion-icon name="library-outline" class="text-xl"></ion-icon><span>Daftar Buku</span>
    </a>
    <a href="users.php" class="flex items-center space-x-2 rounded px-3 py-2 <?= ($page=='users.php')?'bg-primary-light text-primary font-semibold':'text-primary hover:bg-primary-light' ?>">
      <ion-icon name="people-outline" class="text-xl"></ion-icon><span>Daftar User</span>
    </a>
  </nav>
</aside>

<!-- CONTENT -->
<main id="content" class="pt-20 pl-64 pr-6 pb-10 transition-all">
  <div class="bg-white rounded-xl shadow p-6">
    <h2 class="text-2xl font-semibold text-primary mb-4 flex items-center justify-center gap-2">
      <ion-icon name="library-outline" class="text-3xl text-primary"></ion-icon>Daftar Buku
    </h2>

    <!-- Form Pencarian & Filter -->
    <div class="mb-4 flex items-center justify-between w-full">
      <form method="GET" class="flex items-center">
        <input type="text" name="search" placeholder="Cari file..." value="<?= htmlspecialchars($search) ?>" class="border border-black px-2 py-1 rounded-l-lg w-48 text-sm focus:outline-none">
        <button class="bg-primary text-white px-3 py-1 rounded-r-lg text-sm hover:bg-primary-dark">Cari</button>
      </form>

      <form method="GET" class="flex items-center gap-2">
        <?php if ($search != ""): ?><input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>"><?php endif; ?>
        <label class="text-sm text-gray-700">Tampilkan:</label>
        <select name="limit" onchange="this.form.submit()" class="border border-black rounded-lg px-2 py-1 text-sm cursor-pointer">
          <option value="all" <?= ($limit==="all"?"selected":"") ?>>All</option>
          <option value="5" <?= ($limit==5?"selected":"") ?>>5</option>
          <option value="10" <?= ($limit==10?"selected":"") ?>>10</option>
          <option value="50" <?= ($limit==50?"selected":"") ?>>50</option>
          <option value="100" <?= ($limit==100?"selected":"") ?>>100</option>
        </select>
      </form>
    </div>

    <!-- Tabel Buku -->
    <div class="overflow-x-auto">
      <table class="w-full border border-black border-collapse text-sm">
        <thead class="bg-primary text-white">
          <tr>
            <th class="py-2 px-3 border border-black">No</th>
            <th class="py-2 px-3 border border-black">Judul</th>
            <th class="py-2 px-3 border border-black">Deskripsi</th>
            <th class="py-2 px-3 border border-black">File</th>
            <th class="py-2 px-3 border border-black">Tanggal</th>
            <th class="py-2 px-3 border border-black text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $no=1;
          foreach($stmt as $row): 
            $tgl = date("d M Y, H:i", strtotime($row['created_at']));
            $flip = "../flipbook/index.php?file=" . urlencode($row['file_path']);
          ?>
          <tr class="hover:bg-gray-50">
            <td class="py-2 px-3 border border-black"><?= $no ?></td>
            <td class="py-2 px-3 border border-black font-medium text-gray-800"><?= $row['title'] ?></td>
            <td class="py-2 px-3 border border-black text-gray-600"><?= $row['description'] ?></td>
            <td class="py-2 px-3 border border-black"><a href="<?= $flip ?>" class="text-[#053F84] underline">Lihat</a></td>
            <td class="py-2 px-3 border border-black text-gray-600"><?= $tgl ?></td>
            <td class="py-2 px-3 border border-black text-center">
              <div class="flex justify-center gap-3">
                <button class="text-yellow-600 hover:text-yellow-800 edit-btn" 
                        data-id="<?= $row['id'] ?>" 
                        data-title="<?= htmlspecialchars($row['title']) ?>" 
                        data-desc="<?= htmlspecialchars($row['description']) ?>" 
                        data-file="<?= basename($row['file_path']) ?>" title="Edit">✏️</button>
                <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Hapus buku ini?')" class="text-red-600 hover:text-red-800" title="Hapus">🗑️</a>
              </div>
            </td>
          </tr>
          <?php $no++; endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- EDIT MODAL -->
<div id="editModal" class="modal-overlay hidden">
  <div class="modal-box">
    <h3 class="text-2xl font-semibold text-primary mb-3">Edit Buku</h3>
    <div id="currentFile" class="bg-primary-light text-primary px-4 py-2 rounded-md mb-4 font-semibold">
      File PDF saat ini: <span id="fileName">NamaFile.pdf</span>
    </div>
    <form method="POST">
      <input type="hidden" name="edit_id" id="editId">
      <label class="text-sm font-semibold">Judul Buku:</label>
      <input type="text" name="edit_title" id="editTitle" required class="border-2 border-gray-300 w-full p-2 rounded-md mb-3 focus:border-primary focus:ring focus:ring-[#04376B33]">
      <label class="text-sm font-semibold">Deskripsi:</label>
      <textarea name="edit_description" id="editDesc" rows="4" class="border-2 border-gray-300 w-full p-2 rounded-md focus:border-primary focus:ring focus:ring-[#04376B33]"></textarea>
      <div class="flex justify-end gap-2 mt-5">
        <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition">Batal</button>
        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg font-semibold hover:bg-primary-dark transition">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

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

const modal = document.getElementById("editModal");
const closeModal = document.getElementById("closeModal");
const editButtons = document.querySelectorAll(".edit-btn");
editButtons.forEach(btn => {
  btn.addEventListener("click", () => {
    document.getElementById("editId").value = btn.dataset.id;
    document.getElementById("editTitle").value = btn.dataset.title;
    document.getElementById("editDesc").value = btn.dataset.desc;
    document.getElementById("fileName").textContent = btn.dataset.file;
    modal.style.display = "flex";
  });
});
closeModal.onclick = () => modal.style.display = "none";
window.onclick = (e) => { if(e.target === modal) modal.style.display="none"; };
</script>
</body>
</html>
