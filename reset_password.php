<?php
session_start();
include "db.php";

$error = "";
$success = "";

// pastikan user id ada di session
if (!isset($_SESSION['reset_user_id'])) {
    header("Location: forgot_password.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (strlen($new_password) < 4) {
        $error = "Password minimal 4 karakter";
    } elseif ($new_password !== $confirm_password) {
        $error = "Password dan konfirmasi tidak sama!";
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $q = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $q->execute([$hashed, $_SESSION['reset_user_id']]);
        $success = "Password berhasil diubah! <a href='login.php'>Login di sini</a>";
        unset($_SESSION['reset_user_id']);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Reset Password - Tirta Bhagasasi Digital Library</title>
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
.error{color:red;text-align:center;margin-top:10px;font-size:14px;}
.success{color:green;text-align:center;margin-top:10px;font-size:14px;}
p{text-align:center;margin-top:15px;font-size:16px;}
a{color:var(--primary);text-decoration:none;font-weight:bold;}
a:hover{text-decoration:underline;}
#togglePassword{position:absolute;right:10px;top:50%;transform:translateY(-50%);width:18px;height:12px;border:2px solid #000;border-radius:50%;cursor:pointer;}
#slash{position:absolute;width:2px;height:100%;background:black;top:0;left:50%;transform:rotate(45deg);}
</style>
</head>
<body>

<form method="POST">
    <h2>Reset Password</h2>

    <?php if ($error): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="success"><?= $success ?></p>
    <?php else: ?>
        <label>Password Baru:</label>
        <div style="position:relative;">
            <input type="password" name="password" id="password" required style="padding-right:35px;">
            <div id="togglePassword"><div id="slash"></div></div>
        </div>

        <label>Konfirmasi Password:</label>
        <input type="password" name="confirm_password" required>

        <button type="submit">Ubah Password</button>
    <?php endif; ?>
</form>

<script>
const t=document.querySelector('#togglePassword'),
s=document.querySelector('#slash'),
p=document.querySelector('#password');

t.addEventListener('click',()=>{
    const y=p.getAttribute('type')==='password'?'text':'password';
    p.setAttribute('type',y);
    s.style.display=y==='password'?'block':'none';
});
</script>

</body>
</html>
