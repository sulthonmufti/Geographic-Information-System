<?php
date_default_timezone_set('Asia/Jakarta');

include 'koneksi.php';

$tanggal = date("Y-m-d");
$jam = date("H:i:s");
$user_agent = $_SERVER['HTTP_USER_AGENT'];

$stmt = $conn->prepare("INSERT INTO pengunjung_website (tanggal_kunjungan, jam_kunjungan, user_agent_kunjungan) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $tanggal, $jam, $user_agent);
$stmt->execute();
$stmt->close();
?>


<!-- index.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Geografis - Magelang</title>
    <link rel="stylesheet" href="style/untukindex.css">
    
    <!-- Font Poppins dari Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome untuk ikon -->
    <script src="https://kit.fontawesome.com/a57f5fcdf1.js" crossorigin="anonymous"></script>
</head>
<body>
    <header class="header">
        <!-- Logo dan teks -->
        <a href="index.php" class="logo-container">
            <img src="gambar/logo_magelang.png" alt="Logo" class="logo">
            <div class="logo-text">Disparpora<br>Kabupaten Magelang</div>
        </a>

        <!-- Navigasi -->
        <nav class="nav">
            <a href="halaman_utama.php"><i class="fas fa-map"></i> Peta</a>
            <a href="login.php"><i class="fas fa-user"></i> Login</a>
        </nav>
    </header>

    <main class="hero">
        <div class="overlay"></div>
        <div class="content">
            <h1>SISTEM INFORMASI GEOGRAFIS</h1>
            <p>Pemetaan Objek Wisata Kabupaten Magelang</p>
            <a href="halaman_utama.php" class="cta-button">
                <i class="fas fa-map-marker-alt"></i> Jelajahi Sekarang
            </a>
        </div>
    </main>

    <footer class="footer">
        <div>© 2025 – Pemetaan Objek Wisata Kabupaten Magelang. All rights reserved.</div>
        <div>Muhammad Sulthon Mufti (2100018213)</div>
    </footer>
</body>
</html>
