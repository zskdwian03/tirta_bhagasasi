<?php
include "db.php";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    $q = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $q->execute([$email]);
    $user = $q->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        session_start();
        $_SESSION['reset_user_id'] = $user['id'];
        header("Location: reset_password.php");
        exit();
    } else {
        $error = "Email tidak terdaftar!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Lupa Password</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
:root { --primary:#04376B; --secondary:#0061a8; --bg:#f0f3f9; --text:#04376B; }
*{margin:0;padding:0;box-sizing:border-box;font-family:"Segoe UI",Arial;}
body{background:var(--bg);display:flex;justify-content:center;align-items:center;height:100vh;}
form{background:white;padding:30px;border-radius:12px;box-shadow:0 3px 15px rgba(0,0,0,0.15);width:320px;}
h2{text-align:center;color:var(--primary);margin-bottom:20px;}
input{width:100%;padding:10px;margin:8px 0 15px;border:1px solid #ccc;border-radius:8px;}
button{width:100%;background:var(--primary);color:white;padding:10px;border:none;border-radius:8px;cursor:pointer;}
button:hover{background:var(--secondary);}
#error{color:red;text-align:center;margin-top:10px;font-size:14px;}
p{text-align:center;margin-top:15px;font-size:16px;}
a{color:var(--primary);text-decoration:none;font-weight:bold;}
a:hover{text-decoration:underline;}
</style>
</head>
<body>

<form method="POST">
    <h2>Lupa Password</h2>

    <?php if ($error): ?>
        <p id="error"><?= $error ?></p>
    <?php endif; ?>

    <label>Email:</label>
    <input type="email" name="email" required>

    <button type="submit">Lanjutkan</button>

    <p>
        Kembali ke <a href="login.php">Login</a>
    </p>
</form>

</body>
</html>
