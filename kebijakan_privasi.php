<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kebijakan Privasi - Pemetaan Objek Wisata Kabupaten Magelang</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a57f5fcdf1.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style/untuk_kontak.css"> <style>
        /* Tambahan styling khusus untuk kebijakan privasi jika diperlukan */
        .privacy-content-wrapper {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            max-width: 900px; /* Lebar yang lebih besar untuk konten teks */
            width: 90%;
            margin: 40px auto;
            text-align: left;
        }

        .privacy-content-wrapper h1 {
            font-size: 2.5em;
            color: var(--heading-color);
            margin-bottom: 20px;
            text-align: center;
        }

        .privacy-content-wrapper h2 {
            font-size: 1.8em;
            color: var(--primary-color);
            margin-top: 30px;
            margin-bottom: 15px;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 5px;
        }

        .privacy-content-wrapper h3 {
            font-size: 1.3em;
            color: var(--text-color-dark);
            margin-top: 20px;
            margin-bottom: 10px;
        }

        .privacy-content-wrapper p {
            font-size: 1em;
            line-height: 1.8;
            margin-bottom: 15px;
            color: #444;
        }

        .privacy-content-wrapper ul {
            list-style: disc;
            margin-left: 25px;
            margin-bottom: 15px;
        }

        .privacy-content-wrapper ul li {
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .privacy-content-wrapper a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .privacy-content-wrapper a:hover {
            text-decoration: underline;
        }

        .last-updated {
            font-size: 0.9em;
            color: #777;
            margin-top: 30px;
            text-align: right;
            border-top: 1px solid var(--border-color);
            padding-top: 10px;
        }

        @media (max-width: 768px) {
            .privacy-content-wrapper {
                padding: 25px;
                margin: 20px auto;
            }
            .privacy-content-wrapper h1 {
                font-size: 2em;
            }
            .privacy-content-wrapper h2 {
                font-size: 1.5em;
            }
            .privacy-content-wrapper h3 {
                font-size: 1.1em;
            }
            .privacy-content-wrapper p {
                font-size: 0.95em;
            }
        }

        @media (max-width: 480px) {
            .privacy-content-wrapper {
                padding: 15px;
            }
            .privacy-content-wrapper h1 {
                font-size: 1.8em;
            }
            .privacy-content-wrapper h2 {
                font-size: 1.3em;
            }
            .privacy-content-wrapper h3 {
                font-size: 1em;
            }
            .privacy-content-wrapper ul {
                    margin-left: 15px;
            }
        }
    </style>
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
        <div class="privacy-content-wrapper">
            <h1>Kebijakan Privasi</h1>
            <p>Terakhir diperbarui: 1 Agustus 2025</p>

            <p>Selamat datang di platform Pemetaan Objek Wisata Kabupaten Magelang Berbasis Sistem Informasi Geografis (GIS). Kami berkomitmen untuk melindungi privasi Anda. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, mengungkapkan, dan melindungi informasi pribadi Anda saat Anda menggunakan situs web kami.</p>

            <h2>1. Informasi yang Kami Kumpulkan</h2>
            <p>Kami mengumpulkan informasi untuk menyediakan dan meningkatkan layanan kami kepada Anda. Jenis informasi yang kami kumpulkan meliputi:</p>
            <ul>
                <li><strong>Informasi Identifikasi Pribadi (IIP):</strong>
                    <ul>
                        <li>**Formulir Kontak:** Saat Anda menggunakan formulir kontak kami, kami mengumpulkan nama depan, nama belakang, alamat email, nomor telepon, dan pesan yang Anda kirimkan. Informasi ini digunakan untuk menanggapi pertanyaan Anda dan meningkatkan layanan kami.</li>
                        <li>**Login Pengguna (Admin/Pengelola Konten):** Untuk pengguna dengan akun admin atau pengelola konten, kami mengumpulkan kredensial login (username dan password yang terenkripsi), serta data terkait aktivitas pengelolaan konten (misalnya, siapa yang menambahkan/mengubah data objek wisata).</li>
                    </ul>
                </li>
                <li><strong>Data Penggunaan Otomatis:</strong>
                    <ul>
                        <li>Kami mungkin mengumpulkan informasi tentang bagaimana Anda mengakses dan menggunakan situs web kami, seperti alamat IP, jenis browser, halaman yang Anda kunjungi, waktu yang dihabiskan di halaman tersebut, dan data diagnostik lainnya. Data ini membantu kami memahami perilaku pengguna dan meningkatkan fungsionalitas situs.</li>
                        <li>Untuk fitur GIS, kami mungkin mencatat interaksi Anda dengan peta (misalnya, pencarian lokasi, zoom level) untuk tujuan analisis kinerja dan peningkatan pengalaman pengguna, namun ini tidak terkait langsung dengan identitas pribadi Anda.</li>
                    </ul>
                </li>
            </ul>

            <h2>2. Bagaimana Kami Menggunakan Informasi Anda</h2>
            <p>Informasi yang kami kumpulkan digunakan untuk berbagai tujuan, termasuk:</p>
            <ul>
                <li>Menyediakan dan memelihara layanan situs web kami.</li>
                <li>Meningkatkan, mempersonalisasi, dan memperluas layanan kami.</li>
                <li>Memahami dan menganalisis bagaimana Anda menggunakan situs web kami.</li>
                <li>Mengembangkan produk, layanan, fitur, dan fungsionalitas baru.</li>
                <li>Berkomunikasi dengan Anda, baik secara langsung atau melalui salah satu mitra kami, termasuk untuk layanan pelanggan, untuk memberi Anda pembaruan dan informasi lain yang berkaitan dengan situs web, dan untuk tujuan pemasaran dan promosi.</li>
                <li>Mengirim email kepada Anda (terutama sebagai respons atas kritik dan saran yang Anda kirimkan melalui formulir kontak).</li>
                <li>Mencari dan mencegah penipuan.</li>
                <li>Melakukan pemetaan dan analisis geografis terkait objek wisata.</li>
            </ul>

            <h2>3. Pengungkapan Informasi Anda</h2>
            <p>Kami tidak akan menjual, menyewakan, atau memperdagangkan informasi identifikasi pribadi Anda kepada pihak ketiga. Kami dapat membagikan informasi dalam situasi berikut:</p>
            <ul>
                <li><strong>Penyedia Layanan:</strong> Kami dapat mempekerjakan perusahaan dan individu pihak ketiga untuk memfasilitasi layanan kami (misalnya, hosting website, layanan analitik). Pihak ketiga ini memiliki akses ke informasi pribadi Anda hanya untuk melakukan tugas-tugas ini atas nama kami dan berkewajiban untuk tidak mengungkapkan atau menggunakannya untuk tujuan lain.</li>
                <li><strong>Kepatuhan Hukum:</strong> Kami dapat mengungkapkan informasi pribadi Anda jika diwajibkan oleh hukum atau sebagai tanggapan atas permintaan yang sah oleh otoritas publik (misalnya, perintah pengadilan atau permintaan pemerintah).</li>
                <li><strong>Perlindungan Hak:</strong> Kami dapat mengungkapkan informasi pribadi Anda jika kami percaya bahwa tindakan tersebut diperlukan untuk melindungi dan mempertahankan hak atau properti kami, atau untuk mencegah atau menyelidiki kemungkinan kesalahan sehubungan dengan Layanan.</li>
            </ul>

            <h2>4. Keamanan Data</h2>
            <p>Keamanan informasi Anda sangat penting bagi kami. Kami berusaha untuk menggunakan cara yang dapat diterima secara komersial untuk melindungi Informasi Pribadi Anda. Namun, perlu diingat bahwa tidak ada metode transmisi melalui Internet, atau metode penyimpanan elektronik yang 100% aman. Oleh karena itu, kami tidak dapat menjamin keamanan mutlak informasi Anda.</p>

            <h2>5. Tautan ke Situs Lain</h2>
            <p>Situs web kami mungkin berisi tautan ke situs lain yang tidak dioperasikan oleh kami. Jika Anda mengklik tautan pihak ketiga, Anda akan diarahkan ke situs pihak ketiga tersebut. Kami sangat menyarankan Anda untuk meninjau Kebijakan Privasi setiap situs yang Anda kunjungi. Kami tidak memiliki kendali atas dan tidak bertanggung jawab atas konten, kebijakan privasi, atau praktik situs atau layanan pihak ketiga mana pun.</p>

            <h2>6. Privasi Anak-anak</h2>
            <p>Layanan kami tidak ditujukan untuk siapa pun yang berusia di bawah 13 tahun ("Anak-anak"). Kami tidak secara sengaja mengumpulkan informasi identifikasi pribadi dari siapa pun yang berusia di bawah 13 tahun. Jika Anda adalah orang tua atau wali dan Anda mengetahui bahwa Anak Anda telah memberikan kami informasi pribadi, silakan hubungi kami. Jika kami mengetahui bahwa kami telah mengumpulkan informasi pribadi dari anak-anak tanpa verifikasi izin orang tua, kami mengambil langkah-langkah untuk menghapus informasi tersebut dari server kami.</p>

            <h2>7. Perubahan pada Kebijakan Privasi Ini</h2>
            <p>Kami dapat memperbarui Kebijakan Privasi kami dari waktu ke waktu. Kami akan memberi tahu Anda tentang setiap perubahan dengan memposting Kebijakan Privasi baru di halaman ini dan memperbarui tanggal "Terakhir diperbarui" di bagian atas Kebijakan Privasi ini. Anda disarankan untuk meninjau Kebijakan Privasi ini secara berkala untuk setiap perubahan. Perubahan pada Kebijakan Privasi ini efektif saat diposting di halaman ini.</p>

            <h2>8. Hubungi Kami</h2>
            <p>Jika Anda memiliki pertanyaan tentang Kebijakan Privasi ini, silakan hubungi kami:</p>
            <ul>
                <li>Melalui email: <a href="mailto:muhammad2100018213@webmail.uad.ac.id">muhammad2100018213@webmail.uad.ac.id</a></li>
                <li>Melalui formulir kontak di situs web kami: <a href="kontak.php">kontak</a></li>
            </ul>

            <p class="last-updated">Kebijakan Privasi ini disusun sebagai bagian dari tugas akhir/skripsi "Pemetaan Objek Wisata Kabupaten Magelang Berbasis Sistem Informasi Geografis".</p>
        </div>
    </main>

    <footer class="footer">
        <div>© 2025 – Pemetaan Objek Wisata Kabupaten Magelang. All rights reserved.</div>
        <div>Muhammad Sulthon Mufti (2100018213)</div>
    </footer>

    <script src="script.js"></script>
</body>
</html>