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
  $stmt = $pdo->prepare("SELECT * FROM books WHERE title LIKE ? OR description LIKE ? ORDER BY title ASC");
  $stmt->execute(["%$search%", "%$search%"]);
  $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
  $books = $pdo->query("SELECT * FROM books ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User panel - Daftar Buku</title>

  <style>
    :root {
      --primary: #04376B;
      --bg: #f0f3f9;
      --text: #04376B;
    }

    body {
      margin: 0;
      background: var(--bg);
      font-family: "Segoe UI", sans-serif;
      color: var(--text);
      overflow: hidden;
    }

    /* HEADER */
    .header {
      position: fixed;
      top: 0;
      width: 100%;
      background: var(--primary);
      color: white;
      padding: 12px 20px;
      display: flex;
      align-items: center;
      font-weight: 600;
      z-index: 1000;
    }

    .header .title {
      flex: 1;
    }

    .logout-btn {
      background: #001f54;
      padding: 6px 16px;
      border-radius: 6px;
      color: white !important;
      text-decoration: none;
      font-weight: 600;
      margin-right: 30px;
    }

    .logout-btn:hover {
      background: #003b8a;
    }

    /* MAIN */
    .main-content {
      height: calc(100vh - 150px);
      display: flex;
      justify-content: center;
      align-items: center;
      padding-top: 65px;
      padding-bottom: 65px;
    }

    .box {
      width: 100%;
      max-width: 1000px;
      background: white;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.12);
      margin-top: 35px; /* ⬅️ AGAR TIDAK MENEMPEL HEADER DAN TERLIHAT MENGGANTUNG */
    }

    /* SCROLL HANYA UNTUK DAFTAR BUKU */
    .book-scroll {
      max-height: 350px;
      overflow-y: auto;
      margin-top: 12px;
      border-radius: 6px;
    }

    /* TABEL */
    .book-table {
      width: 100%;
      border-collapse: collapse;
    }

    .book-table thead th {
      position: sticky;
      top: 0;
      background: var(--primary);
      color: white;
      padding: 12px;
      z-index: 5;
    }

    .book-table td {
      padding: 10px;
      border-bottom: 1px solid #dcdcdc;
    }

    .book-table tr:hover {
      background: #e6effa;
      cursor: pointer;
    }

    /* FOOTER */
    footer {
      position: fixed;
      bottom: 0;
      width: 100%;
      background: var(--primary);
      color: white;
      padding: 10px;
      text-align: center;
      font-size: 14px;
      z-index: 1000;
    }
  </style>
</head>

<body>

  <div class="header">
    <span class="title">Tirta Bhagasasi Digital Library</span>
    <a href="../logout.php" class="logout-btn">Logout</a>
  </div>

  <div class="main-content">
    <div class="box">
      <h2 style="margin-top:3px; text-align:center;">📚 Daftar Buku</h2>

<form method="get" style="margin-bottom:12px; text-align:center;">

        <input type="text" name="search" placeholder="Cari buku"
          value="<?= htmlspecialchars($search) ?>"
          style="padding:8px;border-radius:6px;border:1px solid #ccc;width:250px;">
        <button type="submit"
          style="padding:8px 12px;border:none;border-radius:6px;background:#04376B;color:white;">
          Cari
        </button>
      </form>

      <!-- Scroll hanya daftar buku -->
      <div class="book-scroll">
        <table class="book-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Judul Buku</th>
              <th>Deskripsi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($books) === 0): ?>
              <tr>
                <td colspan="3" style="padding:15px;text-align:center;">Tidak ada buku ditemukan.</td>
              </tr>
            <?php else: ?>
              <?php $no = 1; ?>
              <?php foreach ($books as $book): ?>
                <tr onclick="openFlipbook('<?= htmlspecialchars($book['file_path']) ?>')">
                  <td><?= $no++ ?></td>
                  <td><?= htmlspecialchars($book['title']) ?></td>
                  <td><?= htmlspecialchars(mb_strimwidth($book['description'], 0, 90, '...')) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

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
