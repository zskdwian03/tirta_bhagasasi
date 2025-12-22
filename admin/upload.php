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

// Proses upload buku
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['pdf'])) {
  $title = $_POST['title'];
  $desc = $_POST['description'];
  $pdf = $_FILES['pdf'];

  $maxSize = 100 * 1024 * 1024;
  if ($pdf['size'] > $maxSize) {
    echo "<script>alert('Ukuran file maksimal 100MB!'); history.back();</script>";
    exit();
  }
  if ($pdf['error'] !== 0) {
    echo "<script>alert('Upload gagal! Error code: " . $pdf['error'] . "');</script>";
    exit();
  }

date_default_timezone_set('Asia/Jakarta');
$ext = strtolower(pathinfo($pdf['name'], PATHINFO_EXTENSION));

if ($ext !== 'pdf') {
  echo "<script>alert('Hanya file PDF yang diperbolehkan!'); history.back();</script>";
  exit();
}

$folderName = 'dlib' . date('Ym'); 
$targetDir = "../upload/" . $folderName . "/";

if (!is_dir($targetDir)) {
  mkdir($targetDir, 0777, true);
}

$newFileName = date('YmdHis') . '.' . $ext;
$targetPath = $targetDir . $newFileName;

$dbPath = "upload/" . $folderName . "/" . $newFileName;

  if (move_uploaded_file($pdf['tmp_name'], $targetPath)) {
    $stmt = $conn->prepare("INSERT INTO books (title, description, file_path) VALUES (:title, :desc, :file)");
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':desc', $desc);
    $stmt->bindParam(':file', $dbPath);
    $stmt->execute();
    echo "<script>alert('Buku berhasil diupload!'); window.location='daftar.php';</script>";
  } else {
    echo "<script>alert('Upload gagal! File tidak bisa dipindahkan.');</script>";
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Upload Buku | Tirta Bhagasasi</title>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
<script src="https://cdn.tailwindcss.com"></script>

<style>
:root {
  --primary-color: #04376B;
  --primary-light: #E8EFF6;
  --primary-dark: #03294B;
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

input:focus, textarea:focus {
  outline: none;
  border-color: var(--primary-color) !important;
  box-shadow: 0 0 0 2px rgba(4,55,107,0.25);
}

/* Notif badge */
.notif-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: red;
  color: white;
  font-size: 0.7rem;
  font-weight: bold;
  padding: 0 5px;
  border-radius: 9999px;
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
    <h1 class="text-lg font-bold">Digital Library - Tirta Bhagasasi</h1>
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
      <ion-icon name="home-outline" class="text-xl"></ion-icon>
      <span>Dashboard</span>
    </a>
    <a href="upload.php" class="flex items-center space-x-2 rounded px-3 py-2 <?= ($page=='upload.php')?'bg-primary-light text-primary font-semibold':'text-gray-700 hover:bg-primary-light' ?>">
      <ion-icon name="cloud-upload-outline" class="text-xl"></ion-icon>
      <span>Upload Buku</span>
    </a>
    <a href="daftar.php" class="flex items-center space-x-2 rounded px-3 py-2 <?= ($page=='daftar.php')?'bg-primary-light text-primary font-semibold':'text-gray-700 hover:bg-primary-light' ?>">
      <ion-icon name="library-outline" class="text-xl"></ion-icon>
      <span>Daftar Buku</span>
    </a>
    <a href="users.php" class="flex items-center space-x-2 rounded px-3 py-2 <?= ($page=='users.php')?'bg-primary-light text-primary font-semibold':'text-primary hover:bg-primary-light' ?>">
      <ion-icon name="people-outline" class="text-xl"></ion-icon>
      <span>Daftar User</span>
    </a>
  </nav>
</aside>

<!-- CONTENT -->
<main id="content" class="pt-20 pl-64 pr-6 pb-10 transition-all">
  <div class="bg-white rounded-xl shadow p-6 max-w-lg mx-auto">
    <h2 class="text-2xl font-semibold text-primary mb-4 text-center">Upload Buku Baru</h2>
    <form method="POST" enctype="multipart/form-data" class="space-y-3">
      <div>
        <label class="block mb-1 font-semibold text-gray-700">Judul Buku</label>
        <input type="text" name="title" required class="w-full border rounded-lg px-3 py-2">
      </div>
      <div>
        <label class="block mb-1 font-semibold text-gray-700">Deskripsi</label>
        <textarea name="description" rows="3" class="w-full border rounded-lg px-3 py-2"></textarea>
      </div>
      <div>
        <label class="block mb-1 font-semibold text-gray-700">File PDF</label>
        <input type="file" name="pdf" required class="w-full border rounded-lg px-3 py-2">
      </div>
      <button type="submit" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-primary-dark transition">Upload</button>
    </form>
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
