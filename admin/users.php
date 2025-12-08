<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
  header("Location: ../login.php?msg=Silakan login terlebih dahulu");
  exit();
}

require_once "../db.php";
$db = new Database();
$conn = $db->connect();

$page = basename($_SERVER['PHP_SELF']);

/* ===== Delete User ===== */
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $conn->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
  echo "<script>alert('User berhasil dihapus!'); location='users.php';</script>";
  exit;
}

/* ===== Update Status ===== */
if (isset($_GET['toggle']) && isset($_GET['status'])) {
  $id = $_GET['toggle'];
  $status = $_GET['status'];
  $conn->prepare("UPDATE users SET status=? WHERE id=?")->execute([$status, $id]);
  echo "<script>alert('Status user diperbarui!'); location='users.php';</script>";
  exit;
}

/* ===== Ambil Hanya Data User Role USER ===== */
$stmt = $conn->query("SELECT * FROM users WHERE role='user' ORDER BY id ASC");

/* ===== Hitung user pending untuk notif ===== */
$notif = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='user' AND status='pending'")->fetch(PDO::FETCH_ASSOC);
$pendingCount = $notif['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Data User | Tirta Bhagasasi</title>
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
  .sidebar.collapsed nav a { justify-content:center!important; padding-left:0!important; padding-right:0!important; }
  .sidebar.collapsed ion-icon { margin-right:0!important; font-size:22px; }

  #content.collapsed { padding-left:80px; }

  .bg-primary { background-color: var(--primary-color); }
  .bg-primary-light { background-color: var(--primary-light); }
  .text-primary { color: var(--primary-color); }

  .btn{padding:6px 10px;border-radius:6px;font-size:13px;color:white;text-decoration:none;}
  .btn-success{background:#198754;}
  .btn-danger{background:#dc3545;}
  .btn-warning{background:#ffc107;color:black;}

  /* Animasi notifikasi */
  .notification-shake {
    animation: shake 0.4s ease-in-out infinite alternate;
  }
  @keyframes shake {
    0% { transform: rotate(-8deg); }
    100% { transform: rotate(8deg); }
  }

  table {
    border-collapse: separate !important;
    border-spacing: 0;
    width: 100%;
    table-layout: fixed;
    font-size: 14px;
    background: white;
    border: 1px solid #999 !important;
    border-radius: 10px;
    overflow: hidden;
  }

  table thead th {
    background-color: #04376B;
    color: #fff;
    padding: 10px 6px;
    font-weight: 600;
    border-right: 0.5px solid #666 !important;
    border-bottom: 0.5px solid #666 !important;
    border-top: none;
    border-left: none;
  }

  table thead th:first-child {
    border-left: none;
    border-top-left-radius: 10px;
  }

  table thead th:last-child {
    border-right: none;
    border-top-right-radius: 10px;
  }

  table td {
    border-right: 0.5px solid #ccc !important;
    border-bottom: 0.5px solid #ccc !important;
    border-left: none;
    border-top: none;
    padding: 8px 6px;
    text-align: center;
    vertical-align: middle;
  }

  table td:nth-child(2),
  table td:nth-child(3) {
    text-align: justify;
    padding-left: 10px;
    padding-right: 10px;
  }

  table td:first-child {
    border-left: none !important;
  }

  table td:last-child {
    border-right: none !important;
  }

  tbody tr:hover {
    background: #f7fbff;
  }

  table tbody tr:last-child td {
    border-bottom: none !important;
  }

  table tbody tr:last-child td:first-child {
    border-bottom-left-radius: 10px;
  }

  table tbody tr:last-child td:last-child {
    border-bottom-right-radius: 10px;
  }

  #noDataRow td {
    border-right: none !important;
    border-left: none !important;
    border-bottom: none !important;
  }

  table th:nth-child(1),
  table td:nth-child(1) { width: 45px; }

  table th:nth-child(2),
  table td:nth-child(2) { width: 160px; }

  table th:nth-child(3),
  table td:nth-child(3) { width: 220px; word-break: break-word; }

  table th:nth-child(4),
  table td:nth-child(4) { width: 110px; }

  table th:nth-child(5),
  table td:nth-child(5) { width: 210px; }

  .aksi-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
  }

  .btn {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    color: white;
    min-width: 85px;
    text-align: center;
  }

  .btn-success { background: #198754; }
  .btn-danger { background: #dc3545; }
  .btn-warning { background: #ffc107; color: black; }

  @media (max-width: 640px) {
    table { font-size: 12px; }

    .aksi-wrapper {
      flex-direction: column;
      gap: 5px;
    }

    .btn {
      width: 100%;
      padding: 6px 0;
    }
  }
</style>
</head>
<body class="bg-gray-100 font-sans">

<header class="fixed top-0 left-0 right-0 bg-[#04376B] text-white flex items-center justify-between px-4 py-3 shadow-md z-50">
  <div class="flex items-center gap-3">
    <button id="menu-btn" class="p-2 rounded hover:bg-[#032A52]">☰</button>
    <h1 class="text-lg font-bold">Tirta Bhagasasi Admin</h1>
  </div>

  <div class="flex items-center gap-5">

    <!-- NOTIFIKASI -->
    <a href="users.php" class="relative">
      <ion-icon name="notifications-outline" 
        class="text-2xl cursor-pointer hover:text-gray-300 <?= $pendingCount > 0 ? 'notification-shake' : '' ?>">
      </ion-icon>

      <?php if($pendingCount > 0): ?>
        <span class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-bold px-1.5 py-0.5 rounded-full">
          <?= $pendingCount ?>
        </span>
      <?php endif; ?>
    </a>

    <!-- LOGOUT -->
    <a href="../logout.php" class="bg-red-600 px-3 py-1 rounded text-sm hover:bg-red-700">
      Logout
    </a>
  </div>
</header>

<!-- SIDEBAR -->
<aside id="sidebar" class="sidebar fixed top-14 left-0 w-60 h-full bg-white shadow-lg pt-6">
<nav class="flex flex-col space-y-2 px-4">

  <a href="index.php" 
     class="flex items-center space-x-2 rounded px-3 py-2 <?= ($page=='index.php') 
     ? 'bg-primary-light text-primary font-semibold' 
     : 'text-gray-700 hover:bg-primary-light' ?>">
    <ion-icon name="home-outline" class="text-xl"></ion-icon>
    <span>Dashboard</span>
  </a>

  <a href="upload.php" 
     class="flex items-center space-x-2 rounded px-3 py-2 <?= ($page=='upload.php') 
     ? 'bg-primary-light text-primary font-semibold' 
     : 'text-gray-700 hover:bg-primary-light' ?>">
    <ion-icon name="cloud-upload-outline" class="text-xl"></ion-icon>
    <span>Upload Buku</span>
  </a>

  <a href="daftar.php" 
     class="flex items-center space-x-2 rounded px-3 py-2 <?= ($page=='daftar.php') 
     ? 'bg-primary-light text-primary font-semibold' 
     : 'text-gray-700 hover:bg-primary-light' ?>">
    <ion-icon name="library-outline" class="text-xl"></ion-icon>
    <span>Daftar Buku</span>
  </a>

  <a href="users.php" 
     class="flex items-center space-x-2 rounded px-3 py-2 <?= ($page=='users.php') 
     ? 'bg-primary-light text-primary font-semibold' 
     : 'text-primary hover:bg-primary-light' ?>">
    <ion-icon name="people-outline" class="text-xl"></ion-icon>
    <span>Daftar User</span>
  </a>

</nav>
</aside>

<main id="content" class="pt-20 pl-64 pr-6 pb-10 transition-all">
  <div class="bg-white rounded-xl shadow p-6">
    <h2 class="text-2xl font-semibold text-[#04376B] mb-4 flex items-center justify-center gap-2">
      <ion-icon name="people-outline" class="text-3xl"></ion-icon> Daftar User
    </h2>

<!-- SEARCH BAR -->
<div class="mb-3 flex justify-start gap-2">
  <input type="text" id="searchInput" placeholder="Cari nama..." class="border border-gray-400 rounded-md px-3 py-1 w-56 text-sm">
  <button id="searchBtn"class="bg-[#04376B] text-white px-4 py-1 rounded-md hover:bg-[#032A52] text-sm">Cari</button>
</div>

    <div class="overflow-x-auto w-full">
  <table class="w-full text-sm min-w-[600px]">

    <thead class="bg-[#04376B] text-white">
      <tr>
        <th class="py-2 px-3">No</th>
        <th class="py-2 px-3">Nama</th>
        <th class="py-2 px-3">Email</th>
        <th class="py-2 px-3">Status</th>
        <th class="py-2 px-3 text-center">Aksi</th>
      </tr>
    </thead>

    <tbody>

  <!-- BARIS JIKA DATA TIDAK DITEMUKAN -->
  <tr id="noDataRow" style="display:none;">
    <td colspan="5" class="py-3 text-center font-semibold text-red-600">
      ❌ Data tidak ditemukan
    </td>
  </tr>

  <?php $no=1; foreach($stmt as $user): ?>
  <tr class="hover:bg-gray-50 data-row">
    <td class="px-3 py-2 text-center"><?= $no++ ?></td>
    <td class="px-3 py-2"><?= htmlspecialchars($user['name']) ?></td>
    <td class="px-3 py-2"><?= $user['email'] ?></td>

    <td class="px-3 py-2 font-semibold <?= $user['status']=='active' ? 'text-green-600' : 'text-orange-500' ?>">
      <?= ucfirst($user['status']) ?>
    </td>

    <td class="px-3 py-2 text-center aksi-cell">
      <div class="aksi-wrapper">

        <?php if($user['status'] == 'active'): ?>
          <a href="users.php?toggle=<?= $user['id'] ?>&status=pending" 
            class="btn btn-warning">Nonaktifkan</a>
        <?php else: ?>
          <a href="users.php?toggle=<?= $user['id'] ?>&status=active" 
            class="btn btn-success">Aktifkan</a>
        <?php endif; ?>

        <a href="users.php?delete=<?= $user['id'] ?>" 
          class="btn btn-danger"
          onclick="return confirm('Yakin ingin menghapus user ini?');">
          Hapus
        </a>

      </div>
    </td>
  </tr>
  <?php endforeach; ?>

</tbody>


  </table>
</div>

  </div>
</main>

<footer class="fixed bottom-0 left-0 right-0 bg-[#04376B] text-white text-center py-2 text-sm">
  © <?= date("Y") ?> Tirta Bhagasasi - Digital Library
</footer>

<script>
document.getElementById('menu-btn').addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('collapsed');
  document.getElementById('content').classList.toggle('collapsed');
});

// === FUNGSI PENCARIAN UTAMA ===
function performSearch() {
  let keyword = document.getElementById("searchInput").value.toLowerCase();
  let rows = document.querySelectorAll(".data-row");
  let noDataRow = document.getElementById("noDataRow");

  let match = 0;

  rows.forEach(row => {
    let nama = row.querySelector("td:nth-child(2)").innerText.toLowerCase();

    if (nama.includes(keyword)) {
      row.style.display = "";
      match++;
    } else {
      row.style.display = "none";
    }
  });

  // Munculkan baris "Data tidak ditemukan"
  noDataRow.style.display = (match === 0) ? "" : "none";
}

// === PENCARIAN SAAT TOMBOL CARI DIKLIK ===
document.getElementById("searchBtn").addEventListener("click", performSearch);

// === PENCARIAN SAAT MENEKAN ENTER ===
document.getElementById("searchInput").addEventListener("keydown", function(e) {
  if (e.key === "Enter") {
    e.preventDefault();
    performSearch();
  }
});
</script>


</body>
</html>