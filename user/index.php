<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php?msg=Silakan login terlebih dahulu");
    exit();
}
?>

<?php
include "../auth.php";
require __DIR__ . '/../db.php';

// 🔍 Fitur pencarian
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search !== '') {
  $stmt = $pdo->prepare("SELECT * FROM books WHERE title LIKE ? OR description LIKE ? ORDER BY id DESC");
  $stmt->execute(["%$search%", "%$search%"]);
  $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
  $books = $pdo->query("SELECT * FROM books ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User panel - Daftar Buku</title>

<style>
/* ---------- Root & Base ---------- */
:root {
  --primary: #04376B;
  --secondary: #0061a8;
  --bg: #f0f3f9;
  --text: #04376B;
  --shadow: rgba(0,0,0,0.1);
}

* { box-sizing: border-box; } /* penting agar padding tidak menambah lebar */
html, body {
  height: 100%;
  margin: 0;
  padding: 0;
  font-family: "Segoe UI", Arial, sans-serif;
  background: var(--bg);
  color: var(--text);
  -webkit-font-smoothing:antialiased;
}

/* ---------- Header (fixed, aman) ---------- */
.header {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  background: var(--primary);
  color: white;
  padding: 12px 20px;           /* ruang kiri-kanan */
  font-weight: 600;
  font-size: 15px;
  z-index: 1000;
  display: flex;
  gap: 12px;
  align-items: center;
  box-shadow: 0 2px 5px rgba(0,0,0,0.15);
}

/* judul header diberi elipsis bila terlalu panjang (tidak menekan tombol) */
.header .title {
  flex: 1 1 auto;
  min-width: 0;                /* penting untuk text-overflow bekerja di flex */
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* area tombol tetap di sisi kanan, tidak mengecil */
.header .actions {
  flex: 0 0 auto;
  display: flex;
  gap: 8px;
  align-items: center;
}
.logout-btn {
    color: white !important;
    background: #001f54; /* ✅ Navy */
    padding: 6px 14px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    transition: 0.2s;
    margin-right: 20px; /* ✅ Biar tidak nempel pinggir kanan */
}

.logout-btn:hover {
    background: #003b8a; /* ✅ Hover navy lebih terang */
}


/* ---------- Layout utama (menghitung header + footer) ---------- */
.container {
  display: flex;
  height: calc(100vh - 100px); /* header + footer total kira-kira 100px */
  margin-top: 55px;            /* tinggi header */
  margin-bottom: 45px;         /* tinggi footer */
}

/* ---------- Sidebar ---------- */
.sidebar {
  width: 300px;
  background: white;
  border-right: 2px solid #e0e0e0;
  box-shadow: 3px 0 6px rgba(0,0,0,0.05);
  display: flex;
  flex-direction: column;
  padding: 20px;
  overflow-y: auto;
}

.sidebar h2 {
  text-align: center;
  color: var(--primary);
  font-size: 20px;
  margin-bottom: 15px;
}

.search-box {
  display: flex;
  gap: 6px;
  margin-bottom: 15px;
}

.search-box input {
  flex: 1;
  padding: 8px 10px;
  border-radius: 8px;
  border: 1px solid #ccc;
}

.search-box button {
  background: var(--primary);
  color: white;
  border: none;
  border-radius: 8px;
  padding: 8px 12px;
  cursor: pointer;
}
.search-box button:hover { background: var(--secondary); }

.book-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
  overflow-y: auto;
  flex: 1;
  padding-right: 5px;
}

.book-item {
  background: #fff;
  border: 1px solid #e0e0e0;
  border-radius: 10px;
  padding: 10px;
  box-shadow: 0 2px 4px var(--shadow);
  cursor: pointer;
  transition: 0.2s;
}
.book-item:hover { background: var(--primary); color:white; }

/* ---------- Main content ---------- */
.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  padding: 30px;
  overflow-y: auto;
}

/* ---------- Footer (fixed) ---------- */
footer {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  background: var(--primary);
  color: white;
  padding: 10px 20px;
  text-align: center;
  font-size: 14px;
  z-index: 1000;
}

/* ---------- Responsive ---------- */
@media (max-width: 800px) {
  .container {
    flex-direction: column;
    height: auto;
    margin-top: 55px;
    margin-bottom: 55px;
  }
  .sidebar { width: 100%; max-height: 250px; }
  .header { padding: 10px 12px; }
  footer { padding: 12px; }
}
</style>
</head>

<body>
<!-- ✅ HEADER FIXED -->
<div class="header">
  <span class="title">Tirta Bhagasasi Digital Library</span>

  <div class="actions">
    <a href="../logout.php" class="logout-btn">Logout</a>
  </div>
</div>


<div class="container">
  <!-- Sidebar -->
  <div class="sidebar">
    <h2>📚 Daftar Buku</h2>
    <form method="get" class="search-box">
      <input type="text" name="search" placeholder="Cari buku" value="<?= htmlspecialchars($search) ?>">
      <button type="submit">Cari</button>
    </form>

    
    <div class="book-list">
      <?php if (count($books) === 0): ?>
        <p style="text-align:center;">Tidak ada buku ditemukan.</p>
      <?php else: ?>
        <?php foreach ($books as $book): ?>
          <div class="book-item" onclick="openFlipbook('<?= htmlspecialchars($book['file_path']) ?>')">
            <strong><?= htmlspecialchars($book['title']) ?></strong><br>
            <small><?= htmlspecialchars(mb_strimwidth($book['description'], 0, 60, '...')) ?></small>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Main Content -->
  
  <div class="main-content">
    <div class="welcome">✨  Halo, Selamat Datang!</div>
    <p style="max-width:600px;line-height:1.6;">
      Selamat datang di<strong> Flipbook PDF</strong> dengan tampilan flipbook yang menarik.  
      Klik salah satu buku di sidebar kiri untuk mulai membaca dan menikmati pengalaman membaca yang seru 📖✨
    </p>
  </div>
</div>

<footer>© <?= date('Y') ?> Flipbook - PDF</footer>

<script>
function openFlipbook(filePath) {
  window.open("../flipbook/index.php?file=" + encodeURIComponent(filePath), "_blank");
}
</script>

</body>
</html>
