<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tirta Bhagasasi Digital Library</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            height: 100vh;
            background: #dcdcdc; /* Abu */
            font-family: "Merriweather", serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* ------------------ LOGO ------------------ */
        .logo-box {
            margin-top: -50px;  /* Geser logo naik/turun */
        }

        .logo {
            width: 230px;       /* Perbesar/Perkecil Logo */
            height: auto;
        }

        /* ------------------ JUDUL ------------------ */
        .title-box {
            margin-top: -15px;   /* Geser judul naik/turun */
            text-align: center;
        }

        .landing-title {
            font-size: 28px;
            color: #04376B;
            font-weight: 500;
        }

        .landing-subtitle {
            font-size: 15px;
            color: #333;
            margin-top: 8px;      /* Jarak antara judul dan subjudul */
            font-weight: 300;
        }

        /* ------------------ TOMBOL ------------------ */
        .button-box {
            margin-top: 70px;   /* Geser tombol naik/turun */
        }

        .btn-login {
            padding: 12px 26px;
            background: #04376B;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-size: 17px;
            transition: 0.3s;
        }

        .btn-login:hover {
            background: #0658A8;
        }
    </style>
</head>
<body>

    <!-- LOGO -->
    <div class="logo-box">
        <img src="logo.png" class="logo">
    </div>

    <!-- JUDUL + SUB JUDUL -->
    <div class="title-box">
        <h1 class="landing-title">Tirta Bhagasasi Digital Library</h1>
        <p class="landing-subtitle">Perpustakaan digital untuk pengelolaan dokumen.</p>
    </div>

    <!-- TOMBOL -->
    <div class="button-box">
        <a href="login.php" class="btn-login">Masuk</a>
    </div>

</body>
</html>
