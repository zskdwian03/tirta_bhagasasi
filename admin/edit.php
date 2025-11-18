<?php
require_once "../db.php";

$db = new Database();
$conn = $db->connect();

$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID tidak ditemukan.");
}

// Ambil data buku berdasarkan ID
$stmt = $conn->prepare("SELECT * FROM books WHERE id = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    die("Buku tidak ditemukan.");
}

// Proses update data
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST['title'];
    $desc  = $_POST['description'];

    $query = "UPDATE books SET title = :title, description = :description WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $desc);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo "<script>alert('Buku berhasil diperbarui!'); window.location.href='index.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Buku - Admin Panel</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f4f8;
            margin: 0;
            padding: 50px 20px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        h2 {
            color: #04376B;
            margin: 0 0 20px 0;
            font-size: 20px;
            text-align: left;
        }

        .edit-container {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #04376B;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-bottom: 15px;
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input:focus, textarea:focus {
            border-color: #0652A0;
            box-shadow: 0 0 5px rgba(6, 82, 160, 0.3);
            outline: none;
        }

        button, .back-btn {
            padding: 6px 12px;
            font-size: 13px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            transition: background 0.2s;
        }

        button {
            background: #04376B;
            color: white;
        }

        button:hover {
            background: #0652A0;
        }

        .back-btn {
            background: #ccc;
            color: #04376B;
            text-decoration: none;
            display: inline-block;
        }

        .back-btn:hover {
            background: #aaa;
        }

        .info-box {
            background: #eaf1ff;
            border-left: 4px solid #04376B;
            padding: 10px 12px;
            margin-bottom: 15px;
            border-radius: 6px;
            color: #04376B;
            font-size: 13px;
        }

        .btn-group {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
    </style>
</head>
<body>

<div class="edit-container">
    <h2>Edit Buku</h2>

    <form method="POST">
        <div class="info-box">
            File PDF saat ini: 
            <strong><?= htmlspecialchars(basename($book['file_path'] ?? 'Belum ada file')) ?></strong>
        </div>

        <label>Judul Buku:</label>
        <input type="text" name="title" value="<?= htmlspecialchars($book['title']) ?>" required>

        <label>Deskripsi:</label>
        <textarea name="description" rows="4"><?= htmlspecialchars($book['description']) ?></textarea>

        <div class="btn-group">
            <button type="submit">Simpan Perubahan</button>
            <a href="index.php" class="back-btn">Batal</a>
        </div>
    </form>
</div>

</body>
</html>
