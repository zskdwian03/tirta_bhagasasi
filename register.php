<?php
include "db.php";

$success = ""; 
$lastId = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = "user";

    $q = $pdo->prepare("INSERT INTO users(name, email, password, role) VALUES (?, ?, ?, ?)");
    $q->execute([$name, $email, $password, $role]);

    // Ambil ID user yang baru ditambahkan
    $lastId = $pdo->lastInsertId();

    $success = "Registrasi berhasil! ";
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Register - Tirta Bhagasasi Digital Library</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
:root { --primary:#04376B; --secondary:#0061a8; --bg:#f0f3f9; --text:#04376B; }
*{box-sizing:border-box;margin:0;padding:0;font-family:"Segoe UI",Arial;}
body{background:var(--bg);display:flex;justify-content:center;align-items:center;height:100vh;}
form{background:white;padding:30px;border-radius:12px;box-shadow:0 3px 15px rgba(0,0,0,0.15);width:320px;}
h2{text-align:center;color:var(--primary);margin-bottom:15px;}
label{font-weight:600;color:var(--text);}
input{width:100%;padding:10px;margin:8px 0 10px;border:1px solid #ccc;border-radius:8px;}
button{width:100%;background:var(--primary);color:white;padding:10px;border:none;border-radius:8px;cursor:pointer;}
button:hover{background:var(--secondary);}
.success{background:#e4ffe4;color:#008000;padding:8px;border-radius:6px;text-align:center;font-size:14px;margin-bottom:10px;}
.small-error{color:red;font-size:12px;display:none;margin-top:-5px;margin-bottom:8px;}
#togglePassword{position:absolute;right:10px;top:50%;transform:translateY(-50%);width:18px;height:12px;border:2px solid #000;border-radius:50%;cursor:pointer;}
#slash{position:absolute;width:2px;height:100%;background:black;top:0;left:50%;transform:rotate(45deg);}
p{text-align:center;margin-top:10px;font-size:14px;}
a{color:var(--primary);text-decoration:none;font-weight:bold;}
a:hover{text-decoration:underline;}
</style>
</head>
<body>

<form method="POST">
    <h2>Register</h2>

    <?php if ($success): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>

    <label>Nama:</label>
    <input type="text" name="name" required>

    <label>Email:</label>
    <input type="email" name="email" id="email" required>
    <div id="emailError" class="small-error">Email harus menggunakan @gmail.com</div>

    <label>Password:</label>
    <div style="position:relative;">
        <input type="password" name="password" id="password" required style="padding-right:35px;">
        <div id="togglePassword"><div id="slash"></div></div>
    </div>
    <div id="passError" class="small-error">Password minimal 4 karakter</div>

    <button type="submit">Daftar</button>

    <p style="text-align:center;margin-top:15px;font-size:16px;">
    Sudah punya akun? <a href="login.php">Login di sini</a>
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

const form=document.querySelector("form");
const email=document.querySelector("#email");
const pass=document.querySelector("#password");
const emailError=document.querySelector("#emailError");
const passError=document.querySelector("#passError");

form.addEventListener("submit",function(e){
    let valid=true;
    if(!email.value.endsWith("@gmail.com")){
        emailError.style.display="block";
        valid=false;
    } else emailError.style.display="none";

    if(pass.value.length < 4){
        passError.style.display="block";
        valid=false;
    } else passError.style.display="none";

    if(!valid) e.preventDefault();
});
</script>

</body>
</html>
