<?php 
session_start();

$namaUser = $_SESSION['name'] ?? 'User';

include "../auth.php";
require __DIR__ . '/../db.php';

// =============================
// 🔢 LIMIT DATA
// =============================
$limitOptions = [5, 10, 20, 50, 100];
$limit = isset($_GET['limit']) && in_array($_GET['limit'], $limitOptions) 
         ? (int)$_GET['limit'] 
         : 10;

// 🔍 PENCARIAN
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search !== '') {
  $stmt = $pdo->prepare("SELECT * FROM books 
                         WHERE title LIKE ? OR description LIKE ? 
                         ORDER BY title ASC 
                         LIMIT $limit");
  $stmt->execute(["%$search%", "%$search%"]);
  $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
  $stmt = $pdo->prepare("SELECT * FROM books ORDER BY title ASC LIMIT $limit");
  $stmt->execute();
  $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User - Daftar Buku</title>

  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

  <!-- ===========================
        📌 CSS SUDAH DIRAPIKAN
       =========================== -->
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
      overflow-x: hidden;
      overflow-y: auto;
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

    /* MAIN CONTENT */
    .main-content {
      padding-top: 95px;
      padding-bottom: 95px;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      min-height: calc(100vh - 190px);
    }

    .box {
      width: 100%;
      max-width: 1100px;
      background: white;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 3px 8px rgba(0,0,0,0.12);
    }

    /* TABEL */
    .book-scroll {
      max-height: 420px;
      overflow-y: auto;
      overflow-x: hidden;
      margin-top: 12px;
      background: white;
    }

    table {
      width: 100%;
      border: 1px solid black;
      border-collapse: collapse;
      font-size: 14px;
      table-layout: fixed;
    }

    table thead th {
      position: sticky;
      top: 0;
      z-index: 10;
      background: var(--primary);
      color: white;
      padding: 10px;
      border: 1px solid black;
      text-align: left;
    }

    td {
      padding: 10px 12px;
      border: 1px solid black;
      color: black;
    }

    tr:hover {
      background: #e8f1ff;
      cursor: pointer;
    }

    /* KOLOM DESKRIPSI */
    td.desc-col {
      width: 160px;
      max-width: 160px;
      white-space: normal;
      word-wrap: break-word;
      overflow-wrap: anywhere;
      line-height: 1.4;
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

    <!-- Nama user tampil -->
    <span style="margin-right:20px;font-size:15px;background:white;color:black;padding:4px 12px;border-radius:6px;font-weight:600;">
        Hi, <?= htmlspecialchars($namaUser) ?>! 
    </span>

    <a href="../logout.php" class="logout-btn">Logout</a>
</div>

<div class="main-content">
    <div class="box">

      <h2 style="margin:3px 0 18px 0;text-align:center;display:flex;justify-content:center;align-items:center;gap:8px;">
        <ion-icon name="library-outline" style="font-size:30px;color:black;"></ion-icon>
        <span style="color:black;font-size:23px;font-weight:600;">Daftar Buku</span>
      </h2>

      <form method="get" style="margin-bottom:12px;display:flex;justify-content:center;gap:10px;">
        <input type="text" name="search" placeholder="Cari buku"
          value="<?= htmlspecialchars($search) ?>"
          style="padding:8px;border-radius:6px;border:1px solid #ccc;width:260px;">

        <button type="submit" style="padding:8px 13px;border:none;border-radius:6px;background:#04376B;color:white;">
          Cari
        </button>

        <div style="display:flex;align-items:center;gap:5px;">
          <span style="font-size:14px;color:black;">Tampilkan:</span>

          <select name="limit" onchange="this.form.submit()"
            style="padding:8px;border-radius:6px;border:1px solid #ccc;">
            <?php foreach ($limitOptions as $opt): ?>
              <option value="<?= $opt ?>" <?= ($opt == $limit ? 'selected' : '') ?>>
                <?= $opt ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </form>

      <div class="book-scroll">
        <table>
          <thead>
            <tr>
              <th style="width:20px;">No</th>
              <th style="width:85px;">Judul Buku</th>
              <th style="width:140px;">Deskripsi</th>
              <th style="width:20px;">File</th>
            </tr>
          </thead>

          <tbody>
            <?php if (count($books) === 0): ?>
              <tr><td colspan="4" style="text-align:center;padding:15px;">Tidak ada buku ditemukan.</td></tr>
            <?php else: 
              $no = 1; foreach ($books as $b): ?>
              <tr>
                <td><?= $no++ ?></td>
                <td style="font-weight:600;white-space:normal;word-wrap:break-word;">
                    <?= htmlspecialchars($b['title']) ?>
                </td>
                <td class="desc-col"><?= htmlspecialchars($b['description']) ?></td>
                <td>
                  <a href="../flipbook/index.php?file=<?= urlencode($b['file_path']) ?>"
                    style="color:#0052A1;text-decoration:underline;">
                    Lihat
                  </a>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>

        </table>
      </div>

    </div>
</div>

<footer>© <?= date('Y') ?> Tirta Bhagasasi - Digital Library</footer>

</body>
</html>
