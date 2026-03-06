<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Disparpora Kabupaten Magelang</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://kit.fontawesome.com/a57f5fcdf1.js" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="leaflet/leaflet.css" />
    <link rel="stylesheet" href="leaflet/Control.FullScreen.css" />

    <link rel="stylesheet" href="mobile.css">

</head>
<body>
    <header class="main-header">
        <div class="container">
            <a href="index.php" class="logo-container">
                <img src="gambar/logo_magelang.png" alt="Logo Disparpora" class="logo">
                <div class="logo-text">
                    <p>Disparpora</p>
                    <p>Kabupaten Magelang</p>
                </div>
            </a>
            <nav class="main-nav">
                <ul>
                    <li><a href="halaman_utama.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'halaman_utama.php') ? 'active' : ''; ?>">Home</a></li>
                    <li><a href="galeri.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'galeri.php') ? 'active' : ''; ?>">Galeri</a></li>
                    <li><a href="tentang.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'tentang.php') ? 'active' : ''; ?>">Tentang</a></li>
                    <li><a href="kontak.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'kontak.php') ? 'active' : ''; ?>">Kontak</a></li>
                </ul>
                <a href="login.php" class="login-button">
                    <i class="fas fa-user"></i> Login
                </a>
            </nav>
            <button class="hamburger-menu">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <div class="mobile-nav-overlay">
        <div class="mobile-nav-menu">
            <ul>
                <li><a href="halaman_utama.php">Home</a></li>
                <li><a href="galeri.php">Galeri</a></li>
                <li><a href="tentang.php">Tentang</a></li>
                <li><a href="kontak.php">Kontak</a></li>
            </ul>
            <a href="login.php" class="mobile-login-button">
                <i class="fas fa-user"></i> Login
            </a>
        </div>
    </div>

    <main>
        <div style="display: flex; gap: 20px;">
            <!-- Kiri: Search  -->
            <div id="search-box">
                    <input type="text" id="search-input" placeholder="Masukkan nama wisata...">
                    
                    <!-- Tombol cari & reset sejajar -->
                    <div id="search-buttons-wrapper">
                        <button id="search-button">Cari</button>
                        <button id="reset-button">Reset</button>
                    </div>
                </div>

            <!-- Kanan: Peta -->
            <section id="map-section" style="width: 75%;">
            <div id="mapid"></div>
            </section>

            <!-- Kiri: Sidebar -->
            <aside id="search-sidebar" style="width: 25%;">
                <!-- Konten aside lainnya nanti bisa ditambahkan di sini -->
            </aside>
        </div>
    </main>

    <footer class="footer">
        <div>© 2025 – Pemetaan Objek Wisata Kabupaten Magelang. All rights reserved.</div>
        <div>Muhammad Sulthon Mufti (2100018213)</div>
    </footer>

    <script src="leaflet/leaflet.js"></script>
    <script src="leaflet/Control.FullScreen.js"></script>

    <script src="script.js"></script>
    
</body>
</html>
?>