<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>E-Library | Perumda Tirta Bhagasasi</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <style>
        * {
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:"Poppins", sans-serif;
        }

        body {
            background: #f7fafd;
            color:#333;
            scroll-behavior: smooth;
        }

        header {
            width:100%;
            padding:12px 3%; /* sebelumnya 20px */
            display:flex;
            justify-content:space-between;
            align-items:center;
            background:#ffffff;
            box-shadow:0 3px 10px rgba(0,0,0,0.05);
            position:sticky;
            top:0;
            z-index:100;
        }

        .brand {
            display:flex;
            align-items:center;
            gap:12px;
            margin-left:-20px;
        }

        .logo {
            width:50px;
            height:auto;
        }

        header h2 {
            color: #04376B;
            font-weight:600;
            font-size:20px;
        }

        header nav {
            display:flex;
            align-items:center;
        }

        header nav a {
            margin-left:18px;
            text-decoration:none;
            color: #04376B;
            font-weight:500;
            transition:.2s;
        }

        header nav a:hover { color:#0052A1; }

        .btn {
            padding:10px 18px;
            border-radius:6px;
            text-decoration:none;
            font-weight:600;
            transition:.25s;
        }

        .btn-login {
            background:#0052A1;
            color:white;
        }

        .btn-login:hover { background:#003d78; }

        .btn-register {
            border:1px solid #0052A1;
            color:#0052A1;
        }

        .btn-register:hover { background:#0052A1; color:white; }

        .menu-toggle {
            display:none;
            font-size:30px;
            cursor:pointer;
            user-select:none;
        }

        .hero {
            padding:110px 8%;
            text-align:center;
            background:
                linear-gradient(rgba(0,82,161,0.45), rgba(0,82,161,0.45)),
                url("pdam.png");
            background-size:cover;
            background-position:center -300px;
            color:white;
        }

        .hero h1 { 
            font-size:42px; 
            margin-bottom:12px; 
            color: #fff;
        }

        .hero p { 
            font-size:18px; 
            margin-bottom:30px;
            color: #fff; 
        }

        .hero .hero-btn {
            padding:14px 26px;
            background:white;
            color:#0052A1;
            font-weight:600;
            border-radius:8px;
            text-decoration:none;
        }

        .hero .hero-btn:hover { background:#cfe8ff; }

        .features {
            padding:70px 8%;
            text-align:center;
            background:white;
        }

        .features h2 {
            font-size:32px;      
            margin-bottom:30px;  
            text-align:center;   
            color:#0052A1;
        }

        .feature-box {
            display:flex;
            gap:25px;
            justify-content:center;
            flex-wrap:wrap;
        }

        .card {
            width:250px;
            background:white;
            padding:25px;
            border-radius:12px;
            box-shadow:
                0 4px 8px rgba(0,0,0,0.08),
                0 10px 20px rgba(0,0,0,0.10);
            transition:0.3s;
        }

        .card:hover {
            transform:translateY(-8px);
            box-shadow:
                0 8px 15px rgba(0,0,0,0.05),
                0 18px 30px rgba(0,0,0,0.08);
        }

        .card h3 {
            font-size:18px;
            margin-bottom:12px;
            color:#0052A1;
            display:flex;
            align-items:center;  
            justify-content:center; 
            gap:8px;          
        }

        .card p { font-size:14px; }

        .about {
            padding-bottom: 50px;
            text-align:center;
            background:#f2f6fc;
        }

        .about h2 { color:#0052A1; margin-bottom:20px; }

        .about p {
            font-size:15px;
            line-height:1.7;
            max-width:850px;
            margin:auto;
            text-align: justify;
            text-justify: inter-word;
            line-height: 1.8;
        }

        #about {
            padding-top: 40px;
        }

        #contact {
            margin-top: 0;
            padding-top: 30px;
        }

        .contact {
            background:white;
            padding:40px 10%;
            text-align:center;
            box-shadow:0 -3px 15px rgba(0,0,0,0.05);
            font-size:16px;
            display:flex;
            justify-content:center;
            gap:40px;
            font-weight:500;
        }

        .contact p {
            margin:0;
            display:flex;
            align-items:center;
            gap:10px;
        }

        .contact ion-icon {
            font-size: 20px; 
            color: #04376B;   
        }

        footer {
            text-align:center;
            padding:25px;
            background: #04376B;
            color:white;
            font-size:14px;
            margin-top: 0px;
        }

        @media(max-width:900px) {

            .menu-toggle {
                display:block;
            }

            header nav {
                display:none;
                flex-direction:column;
                background:white;
                position:absolute;
                top:80px;
                right:20px;
                width:220px;
                padding:15px 0;
                box-shadow:0 5px 15px rgba(0,0,0,0.15);
                border-radius:8px;
                animation:fade .25s ease;
            }

            header nav.show {
                display:flex;
            }

            header nav a {
                margin:10px 0;
                padding:8px 20px;
                display:block;
            }
        }

        @keyframes fade {
            from { opacity:0; transform:translateY(-5px);}
            to   { opacity:1; transform:translateY(0);}
        }

        @media(max-width:768px){
            .hero h1 { font-size:30px; }
            .contact { flex-direction:column; gap:10px; }
        }

        section {
            opacity:0;
            transform:translateY(20px);
            transition:0.8s;
        }

        section.visible {
            opacity:1;
            transform:translateY(0);
        }
    </style>
</head>
<body>

    <header>
        <div class="brand">
            <img src="logo.png" alt="Logo" class="logo">
            <h2>E-Library Tirta Bhagasasi</h2>
        </div>

        <div class="menu-toggle" onclick="toggleMenu()">☰</div>

        <nav id="nav-menu">
            <a href="#">Beranda</a>
            <a href="#about">Tentang</a>
            <a href="#features">Fitur</a>
            <a href="#contact">Kontak</a>
            <a href="login.php" class="btn btn-login">Login</a>
            <a href="register.php" class="btn btn-register">Register</a>
        </nav>
    </header>

    <section class="hero">
        <h1>Selamat Datang di E-Library</h1>
        <p>Akses dokumen dan arsip digital secara mudah dan efisien.</p>
        <a href="login.php" class="hero-btn">Mulai Akses</a>
    </section>

    <section id="features" class="features">
        <h2>Fitur Utama</h2>
        <div class="feature-box">
            <div class="card">
                <h3><ion-icon name="library-outline"></ion-icon> Koleksi Digital</h3>
                <p>Akses dokumen penting dan file internal perusahaan.</p>
            </div>

            <div class="card">
                <h3><ion-icon name="search-outline"></ion-icon> Pencarian Cepat</h3>
                <p>Temukan file berdasarkan judul atau deskripsi.</p>
            </div>

            <div class="card">
                <h3><ion-icon name="book-outline"></ion-icon> Flipbook Viewer</h3>
                <p>Membaca dokumen layaknya membuka buku digital yang interaktif.</p>
            </div>

            <div class="card">
                <h3><ion-icon name="lock-closed-outline"></ion-icon> Role Access</h3>
                <p>Admin mengelola data dan user hanya membaca — aman & terstruktur.</p>
            </div>
        </div>
    </section>

    <section id="about" class="about">
        <h2>Tentang Sistem</h2>
    <p>
        E-Library Perumda Tirta Bhagasasi merupakan sistem perpustakaan digital yang dirancang untuk mempermudah
        pegawai dalam mengakses dokumen penting dan arsip perusahaan secara cepat dan aman. 
        Sistem ini menyediakan antarmuka interaktif yang user-friendly, dilengkapi dengan fitur pencarian cerdas
        sehingga pengguna dapat menemukan dokumen berdasarkan judul, kategori, atau kata kunci dengan efisien. 
        Selain itu, E-Library memastikan keamanan data melalui pengaturan hak akses berbasis peran, di mana 
        admin dapat mengelola konten dan pengguna, sedangkan user hanya dapat membaca dokumen sesuai izin.
        Dengan sistem ini, seluruh informasi perusahaan tersentralisasi, proses kerja menjadi lebih efisien, 
        dan kolaborasi antar pegawai dapat berjalan lebih lancar.
    </p>
    </section>

    <section id="contact" class="contact">
        <p><ion-icon name="call-outline"></ion-icon> <b>Telepon:</b>  (021)-89327101</p>
        <p><ion-icon name="mail-outline"></ion-icon> <b>Email:</b> customercare@tirtabhagasasi.co.id</p>
    </section>

<footer>
    © 2025 Perumda Tirta Bhagasasi • Sistem E-Library
</footer>

<script>
    const sections = document.querySelectorAll("section");

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if(entry.isIntersecting){
                entry.target.classList.add("visible");
            }
        });
    },{threshold:0.2});

    sections.forEach(sec => observer.observe(sec));

    function toggleMenu() {
        document.getElementById("nav-menu").classList.toggle("show");
    }
</script>

</body>
</html>
