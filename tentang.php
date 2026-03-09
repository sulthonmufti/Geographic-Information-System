<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - Pemetaan Wisata Magelang</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a57f5fcdf1.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style/untuk_tentang.css">
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
                    <li><a href="galeri.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'galeri.php' || basename($_SERVER['PHP_SELF']) == 'detail_wisata.php') ? 'active' : ''; ?>">Galeri</a></li>
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
        <nav class="mobile-nav">
            <ul>
                <li><a href="halaman_utama.php">Home</a></li>
                <li><a href="galeri.php">Galeri</a></li>
                <li><a href="tentang.php">Tentang</a></li>
                <li><a href="kontak.php">Kontak</a></li>
            </ul>
            <a href="login.php" class="mobile-login-button">
                <i class="fas fa-user"></i> Login
            </a>
        </nav>
    </div>

    <main>
        <section class="tentang-hero">
            <div class="content-wrapper-left fade-in-left">
                <h2 class="tentang-title">
                    Membangun Jembatan Digital untuk Pariwisata Magelang
                </h2>
                <p class="tentang-intro">
                    Platform ini adalah wujud nyata dari komitmen kami untuk mengenalkan dan mempromosikan kekayaan pariwisata Kabupaten Magelang melalui pemanfaatan teknologi informasi geospasial.
                </p>
            </div>
            <div class="image-wrapper-right fade-in-right">
                <img src="gambar/latarbelakang.svg" alt="Ilustrasi pemetaan dan analisis data">
            </div>
        </section>

        <section class="section-visi-misi fade-in">
            <div class="container-visi">
                <h3 class="section-title-alt">Visi & Misi Kami</h3>
                <div class="visi-misi-grid">
                    <div class="visi-box hover-effect">
                        <h3>Visi</h3>
                        <p>Menjadi portal informasi geospasial terdepan untuk mempromosikan pariwisata Kabupaten Magelang.</p>
                    </div>
                    <div class="misi-box hover-effect">
                        <h3>Misi</h3>
                        <ul>
                            <li>Menyediakan data objek wisata yang akurat dan interaktif.</li>
                            <li>Mendukung pengembangan pariwisata lokal melalui pemanfaatan teknologi.</li>
                            <li>Memfasilitasi wisatawan dalam menjelajahi kekayaan Kabupaten Magelang.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-features fade-in">
            <h3 class="section-title">Fitur Unggulan Kami</h3>
            <div class="features-wrapper">
                <div class="feature-item hover-effect">
                    <div class="feature-icon">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <h4>Peta Interaktif</h4>
                    <p>Temukan lokasi semua objek wisata secara langsung di peta digital yang responsif.</p>
                </div>
                <div class="feature-item hover-effect">
                    <div class="feature-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <h4>Informasi Komprehensif</h4>
                    <p>Akses detail lengkap tentang setiap lokasi, mulai dari deskripsi, fasilitas, hingga galeri foto.</p>
                </div>
                <div class="feature-item hover-effect">
                    <div class="feature-icon">
                        <i class="fas fa-search-location"></i>
                    </div>
                    <h4>Sistem Pencarian</h4>
                    <p>Cari destinasi favorit Anda atau filter berdasarkan kategori untuk pengalaman yang disesuaikan.</p>
                </div>
            </div>
        </section>

        <section class="section-manfaat fade-in">
            <div class="container-manfaat">
                <h3 class="section-title-alt">Manfaat Kehadiran Kami</h3>
                <div class="manfaat-wrapper">
                    <div class="manfaat-item hover-effect">
                        <div class="manfaat-icon">
                            <i class="fas fa-plane-departure"></i>
                        </div>
                        <h4>Untuk Wisatawan</h4>
                        <p>Memudahkan dalam merencanakan perjalanan dan menemukan tempat-tempat menarik dengan lebih efisien.</p>
                    </div>
                    <div class="manfaat-item hover-effect">
                        <div class="manfaat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4>Untuk Pemerintah Daerah</h4>
                        <p>Menyediakan data terstruktur yang mendukung strategi promosi dan pengembangan pariwisata yang lebih terarah.</p>
                    </div>
                    <div class="manfaat-item hover-effect">
                        <div class="manfaat-icon">
                            <i class="fas fa-store-alt"></i>
                        </div>
                        <h4>Untuk Pengelola Wisata</h4>
                        <p>Menjadi sarana promosi yang efektif untuk menjangkau audiens lebih luas dan meningkatkan jumlah kunjungan.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="tentang-developer fade-in">
            <div class="container-developer">
                <h3 class="section-title">Pengembang Proyek</h3>
                <div class="developer-content">
                    <div class="developer-info">
                        <p class="developer-intro">
                            Proyek ini dikembangkan oleh <b>Muhammad Sulthon Mufti</b> sebagai bagian dari proyek studi di <b>Universitas Ahmad Dahlan</b>. Platform ini adalah wujud nyata dari dedikasi dan komitmen dalam memanfaatkan teknologi untuk mendukung sektor pariwisata di Kabupaten Magelang.
                        </p>
                    </div>
                    <div class="developer-image hover-effect">
                        <img src="gambar/Pengembang.jpg" alt="Ilustrasi pengembang">
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div>© 2025 – Pemetaan Objek Wisata Kabupaten Magelang. All rights reserved.</div>
        <div>Muhammad Sulthon Mufti (2100018213)</div>
    </footer>

    <script src="script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sections = document.querySelectorAll('.fade-in, .fade-in-left, .fade-in-right');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });

            sections.forEach(section => {
                observer.observe(section);
            });
        });
    </script>
</body>
</html>