<?php
ini_set('session.cookie_lifetime',0);
ini_set('session.gc_maxlifetime',0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "db.php"; 
$error = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $q = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $q->execute([$email]);
    $user = $q->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {

        // =========================
        // **CHECK STATUS USER**
        // =========================
        if ($user['role'] !== 'admin' && $user['status'] !== 'active') {
            $error = "Akun Anda belum diaktifkan oleh admin!";
        } else {
            $_SESSION['user'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: admin/index.php");
            } else {
                header("Location: user/index.php");
            }
            exit();
        }

    } else {
        $error = "Login gagal! Email atau password salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Login - Tirta Bhagasasi Digital Library</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
:root { --primary:#04376B; --secondary:#0061a8; --bg:#f0f3f9; --text:#04376B; }
*{margin:0;padding:0;box-sizing:border-box;font-family:"Segoe UI",Arial;}
body{background:var(--bg);display:flex;justify-content:center;align-items:center;height:100vh;}
form{background:white;padding:30px;border-radius:12px;box-shadow:0 3px 15px rgba(0,0,0,0.15);width:320px;}
h2{text-align:center;color:var(--primary);margin-bottom:20px;}
label{font-weight:600;color:var(--text);}
input{width:100%;padding:10px;margin:8px 0 15px;border:1px solid #ccc;border-radius:8px;}
button{width:100%;background:var(--primary);color:white;padding:10px;border:none;border-radius:8px;cursor:pointer;}
button:hover{background:var(--secondary);}
#error{color:red;text-align:center;margin-top:10px;font-size:14px;}
#togglePassword{position:absolute;right:10px;top:50%;transform:translateY(-50%);width:18px;height:12px;border:2px solid #000;border-radius:50%;cursor:pointer;}
#slash{position:absolute;width:2px;height:100%;background:black;top:0;left:50%;transform:rotate(45deg);}
p{text-align:center;margin-top:15px;font-size:16px;}
a{color:var(--primary);text-decoration:none;font-weight:bold;}
a:hover{text-decoration:underline;}
.forgot{display:block;text-align:right;font-size:14px;margin:5px 0;}
</style>
</head>
<body>

<form method="POST">
    <h2>Login</h2>

    <label>Email:</label>
    <input type="email" name="email" required>

    <label>Password:</label>
    <div style="position:relative;">
        <input type="password" name="password" id="password" required style="padding-right:35px;">
        <div id="togglePassword"><div id="slash"></div></div>
    </div>

    <a href="forgot_password.php" class="forgot">Lupa Password?</a>

    <button type="submit">Login</button>

    <?php if ($error): ?>
        <p id="error"><?= $error ?></p>
    <?php endif; ?>

    <p>
        Belum punya akun? <a href="register.php">Daftar di sini</a>
    </p>
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
