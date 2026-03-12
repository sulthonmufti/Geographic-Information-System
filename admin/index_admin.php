<?php
session_start(); // Pastikan session_start() ada di baris paling awal!

// Atur timezone ke Asia/Jakarta (WIB)
date_default_timezone_set('Asia/Jakarta');

// Periksa apakah admin sudah login, jika tidak, arahkan ke halaman login
$nama_lengkap_admin = '';
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: ../login.php");
    exit;
} else {
    if (isset($_SESSION['nama_lengkap_admin'])) {
        $nama_lengkap_admin = $_SESSION['nama_lengkap_admin'];
    }
}

require_once '../koneksi.php'; // Pastikan path ini benar
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Pemetaan Objek Wisata Kabupaten Magelang</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a57f5fcdf1.js" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="style/admin_dashboard.css">
    <link rel="stylesheet" href="style/pages/dashboard.css">
    <link rel="stylesheet" href="style/pages/aktivitas_login.css">
    <link rel="stylesheet" href="style/pages/kelola_wisata.css">
    <link rel="stylesheet" href="style/pages/form_tambah_edit_wisata.css">
    <link rel="stylesheet" href="style/pages/kelola_fasilitas.css">
    <link rel="stylesheet" href="style/pages/kelola_kategori.css">
    <link rel="stylesheet" href="style/pages/kelola_kecamatan.css">
    <link rel="stylesheet" href="style/pages/kritik_saran.css">
    <link rel="stylesheet" href="style/pages/tentang_admin.css">
    <link rel="stylesheet" href="style/pages/kelola_admin.css">
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="logo-section">
                <img src="../gambar/logo_magelang.png" alt="Logo Disparpora" class="logo">
                <div class="logo-text">
                    <p>Disparpora</p>
                    <p>Kabupaten Magelang</p>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="#" id="menuDashboard" data-target="dashboard_content.php" class="active"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                    <li><a href="#" id="menuKelolaWisata" data-target="kelola_wisata.php"><i class="fas fa-map-marked-alt"></i> <span>Kelola Wisata</span></a></li>
                    <li><a href="#" id="menuKelolaFasilitas" data-target="kelola_fasilitas.php"><i class="fa-solid fa-boxes-stacked"></i> <span>Kelola Fasilitas</span></a></li>
                    <li><a href="#" id="menuKelolaKategori" data-target="kelola_kategori.php"><i class="fa-solid fa-table"></i> <span>Kelola Kategori</span></a></li>
                    <li><a href="#" id="menuKelolaKecamatan" data-target="kelola_kecamatan.php"><i class="fa-solid fa-map"></i> <span>Kelola Kecamatan</span></a></li>
                    <li><a href="#" id="menuKelolaSaran" data-target="kelola_saran.php"><i class="fa-solid fa-message"></i> <span>Kritik & Saran</span></a></li>
                    <li><a href="#" id="menuTentangAdmin" data-target="tentang_admin.php"><i class="fa-solid fa-user-tie"></i> <span>Tentang Admin</span></a></li>
                    <li><a href="#" id="menuPercobaanLogin" data-target="aktivitas_login.php"><i class="fa-solid fa-circle-exclamation"></i> <span>Percobaan Login</span></a></li>
                </ul>
            </nav>
            <a href="../logout.php" class="logout-button">
                <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
            </a>
        </aside>

        <div class="main-content">
            <header class="dashboard-header">
                <h1 id="dashboardTitle">Dashboard Admin</h1>
                <div class="user-info">
                    <span>Selamat Datang, <?php echo htmlspecialchars($nama_lengkap_admin); ?>!</span>
                    <div class="user-avatar"><?php echo strtoupper(substr($nama_lengkap_admin, 0, 1)); ?></div>
                </div>
                <button class="hamburger-menu">
                    <i class="fas fa-bars"></i>
                </button>
            </header>

            <div id="dynamic-content-area">
                </div>

        </div>
    </div>
    <div class="mobile-nav-overlay">
        <nav class="mobile-nav">
            <ul>
                <li><a href="#" data-target="dashboard_content.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                <li><a href="#" data-target="kelola_wisata.php"><i class="fas fa-map-marked-alt"></i> <span>Kelola Wisata</span></a></li>
                <li><a href="#" data-target="kelola_fasilitas.php"><i class="fa-solid fa-boxes-stacked"></i> <span>Kelola Fasilitas</span></a></li>
                <li><a href="#" data-target="kelola_kategori.php"><i class="fa-solid fa-table"></i> <span>Kelola Kategori</span></a></li>
                <li><a href="#" data-target="kelola_kecamatan.php"><i class="fa-solid fa-map"></i> <span>Kelola Kecamatan</span></a></li>
                <li><a href="#" data-target="kelola_saran.php"><i class="fa-solid fa-message"></i> <span>Kritik & Saran</span></a></li>
                <li><a href="#" data-target="tentang_admin.php"><i class="fa-solid fa-user-tie"></i> <span>Tentang Admin</span></a></li>
                <li><a href="#" data-target="aktivitas_login.php"><i class="fa-solid fa-circle-exclamation"></i> <span>Percobaan Login</span></a></li>
            </ul>
            <a href="../logout.php" class="mobile-login-button">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>

    <footer class="footer">
        <div>© 2025 – Pemetaan Objek Wisata Kabupaten Magelang. All rights reserved.</div>
        <div>Muhammad Sulthon Mufti (2100018213)</div>
    </footer>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
    // Membuat loadContent menjadi fungsi global
    window.loadContent = function(url, title) {
        const dynamicContentArea = document.getElementById('dynamic-content-area');
        const dashboardTitle = document.getElementById('dashboardTitle');
        const navLinks = document.querySelectorAll('.sidebar nav ul li a, .mobile-nav ul li a');

        // Hapus kelas 'active' dari semua tautan navigasi
        navLinks.forEach(link => link.classList.remove('active'));

        // Tambahkan kelas 'active' ke tautan yang sesuai
        const baseTargetUrl = url.split('?')[0];
        navLinks.forEach(link => {
            if (link.dataset.target === baseTargetUrl) {
                link.classList.add('active');
            }
        });

        // Ambil konten dari server
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.text();
            })
            .then(html => {
                // Masukkan konten HTML ke area dinamis
                dynamicContentArea.innerHTML = html;
                
                // Perbarui judul dashboard
                dashboardTitle.textContent = title;

                // Eksekusi skrip yang ada di dalam konten yang baru dimuat
                executeScriptsInContent(dynamicContentArea);
            })
            .catch(error => {
                console.error('There has been a problem with your fetch operation:', error);
                dynamicContentArea.innerHTML = '<p style="color: red;">Gagal memuat konten. Silakan coba lagi nanti.</p>';
            });
    };

    // Fungsi untuk mengeksekusi script yang dimuat secara dinamis
    function executeScriptsInContent(element) {
        const scripts = element.querySelectorAll('script');
        scripts.forEach(script => {
            const newScript = document.createElement('script');
            // Salin semua atribut
            Array.from(script.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));

            if (script.src) {
                // Untuk script eksternal
                const existingScript = document.querySelector(`script[src="${script.src}"]`);
                if (!existingScript) {
                    newScript.onload = () => {};
                    document.head.appendChild(newScript);
                }
            } else {
                // Untuk inline script
                newScript.textContent = script.textContent;
                element.appendChild(newScript);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Logika hamburger menu (sudah ada)
        const hamburger = document.querySelector('.hamburger-menu');
        const mobileNavOverlay = document.querySelector('.mobile-nav-overlay');
        const mobileNav = document.querySelector('.mobile-nav');

        if (hamburger && mobileNavOverlay && mobileNav) {
            hamburger.addEventListener('click', function() {
                mobileNavOverlay.classList.add('active');
                mobileNavOverlay.style.display = 'flex';
                setTimeout(() => {
                    mobileNav.classList.add('active');
                }, 10);
            });

            mobileNavOverlay.addEventListener('click', function(event) {
                if (!mobileNav.contains(event.target) && event.target !== hamburger) {
                    mobileNav.classList.remove('active');
                    setTimeout(() => {
                        mobileNavOverlay.classList.remove('active');
                        mobileNavOverlay.style.display = 'none';
                    }, 300);
                }
            });

            const mobileNavLinks = mobileNav.querySelectorAll('ul li a');
            mobileNavLinks.forEach(link => {
                link.addEventListener('click', function() {
                    mobileNav.classList.remove('active');
                    setTimeout(() => {
                        mobileNavOverlay.classList.remove('active');
                        mobileNavOverlay.style.display = 'none';
                    }, 300);
                });
            });
        }

        // Event delegation untuk semua tautan navigasi utama
        document.addEventListener('click', function(event) {
            // Check jika yang diklik adalah tautan navigasi
            if (event.target.closest('.sidebar nav ul li a, .mobile-nav ul li a')) {
                const link = event.target.closest('.sidebar nav ul li a, .mobile-nav ul li a');
                event.preventDefault();
                const targetFile = link.dataset.target;
                const menuText = link.querySelector('span') ? link.querySelector('span').textContent : link.textContent;
                loadContent(targetFile, menuText);
            }

            // Event listener untuk tombol 'Edit Profile' di halaman tentang_admin.php
            if (event.target && event.target.id === 'editProfileBtn') {
                event.preventDefault();
                loadContent('edit_profile.php', 'Edit Profil Admin');
            }

            // Event listener BARU untuk tombol 'Lihat Daftar Admin'
            if (event.target && event.target.id === 'lihatDaftarAdminBtn') {
                event.preventDefault();
                loadContent('kelola_admin.php', 'Kelola Admin');
            }

            // Event listener untuk tombol 'Tambah Admin Baru' di halaman kelola_admin.php
            if (event.target && event.target.id === 'addAdminBtn') {
                event.preventDefault();
                loadContent('tambah_admin.php', 'Tambah Admin Baru');
            }

            // TANGANI KLIK TOMBOL EDIT
            if (event.target && event.target.classList.contains('edit-admin-btn')) {
                event.preventDefault();
                const idAdmin = event.target.dataset.id;
                loadContent(`edit_admin.php?id_admin=${idAdmin}`, 'Edit Admin');
            }

            // TANGANI KLIK TOMBOL RESET PENCARIAN
            if (event.target && event.target.classList.contains('reset-btn')) {
                event.preventDefault();
                loadContent('kelola_admin.php', 'Kelola Admin');
            }

            // TANGANI KLIK TOMBOL HAPUS ADMIN
            if (event.target && event.target.classList.contains('delete-admin-btn')) {
                event.preventDefault();
                const idAdmin = event.target.dataset.id;
                const namaAdmin = event.target.dataset.nama;

                // Tampilkan konfirmasi kepada pengguna
                if (confirm(`Apakah Anda yakin ingin menghapus admin dengan username '@${namaAdmin}'?`)) {
                    // Kirim permintaan penghapusan ke server
                    const formData = new FormData();
                    formData.append('id_admin', idAdmin);

                    fetch('hapus_admin.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.status === 'success') {
                            // Jika berhasil, muat ulang halaman kelola_admin.php untuk memperbarui tabel
                            window.loadContent('kelola_admin.php', 'Kelola Admin');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat menghapus data. Coba lagi nanti.');
                    });
                }
            }
        });

        // Muat konten default saat halaman pertama kali dimuat
        loadContent('dashboard_content.php', 'Dashboard Admin');
    });

    // Tambahkan event delegation untuk form pencarian
    document.addEventListener('submit', function(event) {
        if (event.target && event.target.id === 'searchAdminForm') {
            event.preventDefault(); // Mencegah form submit tradisional

            const form = event.target;
            const searchInput = form.querySelector('input[name="search_admin"]');
            const searchValue = searchInput.value;
            const url = `kelola_admin.php?search_admin=${encodeURIComponent(searchValue)}`;

            window.loadContent(url, 'Kelola Admin');
        }
    });

    //=========================================
    // Event delegation untuk form edit profile
    //=========================================
    document.addEventListener('submit', function(event) {
        if (event.target.id === 'editProfileForm') {
            event.preventDefault();
            const passwordInput = document.getElementById('password_admin');
            const confirmPasswordInput = document.getElementById('confirm_password_admin');
            
            if (passwordInput && confirmPasswordInput && passwordInput.value !== '' && passwordInput.value !== confirmPasswordInput.value) {
                alert('Password baru dan konfirmasi password tidak cocok. Silakan periksa kembali.');
                return;
            }

            const formData = new FormData(event.target);
            
            fetch('edit_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') {
                    setTimeout(() => {
                        window.loadContent('tentang_admin.php', 'Tentang Admin');
                    }, 500);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan, coba lagi nanti');
            });
        }

        //-----------------------------------------------
        // untuk form Tambah Admin
        //-----------------------------------------------
        if (event.target.id === 'addAdminForm') {
            event.preventDefault();

            const passwordInput = document.getElementById('password_admin');
            const confirmPasswordInput = document.getElementById('confirm_password_admin');

            if (passwordInput.value !== confirmPasswordInput.value) {
                alert('Password dan konfirmasi password tidak cocok. Silakan periksa kembali.');
                return;
            }

            const formData = new FormData(event.target);

            fetch('tambah_admin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') {
                    // Setelah sukses, kembali ke halaman kelola_admin.php
                    setTimeout(() => {
                        window.loadContent('kelola_admin.php', 'Kelola Admin');
                    }, 500);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan, coba lagi nanti');
            });
        }

        //====================================
        // TANGANI SUBMIT FORM EDIT ADMIN BARU
        //====================================
        if (event.target.id === 'editAdminForm') {
            event.preventDefault();
            const passwordInput = document.getElementById('password_admin_edit');
            const confirmPasswordInput = document.getElementById('confirm_password_admin_edit');
            
            if (passwordInput && confirmPasswordInput && passwordInput.value !== '' && passwordInput.value !== confirmPasswordInput.value) {
                alert('Password baru dan konfirmasi password tidak cocok. Silakan periksa kembali.');
                return;
            }

            const formData = new FormData(event.target);
            
            fetch('edit_admin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') {
                    // Setelah sukses, kembali ke halaman kelola_admin.php
                    setTimeout(() => {
                        window.loadContent('kelola_admin.php', 'Kelola Admin');
                    }, 500);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan, coba lagi nanti');
            });
        }
    });

    // Event delegation untuk menampilkan form konfirmasi password (edit_profile edit_admin)
    document.addEventListener('input', function(event) {
        // Logika untuk form edit_profile.php
        if (event.target.id === 'password_admin') {
            const passwordInput = event.target;
            const confirmPasswordRow = document.getElementById('confirmPasswordRow');
            const confirmPasswordInput = document.getElementById('confirm_password_admin');
            if (confirmPasswordRow && confirmPasswordInput) {
                confirmPasswordRow.style.display = passwordInput.value !== '' ? 'table-row' : 'none';
                if (passwordInput.value === '') {
                    confirmPasswordInput.value = '';
                }
            }
        }
        
        // Logika untuk form edit_admin.php
        if (event.target.id === 'password_admin_edit') {
            const passwordInput = event.target;
            const confirmPasswordRow = document.getElementById('confirmPasswordRowEdit');
            const confirmPasswordInput = document.getElementById('confirm_password_admin_edit');
            if (confirmPasswordRow && confirmPasswordInput) {
                confirmPasswordRow.style.display = passwordInput.value !== '' ? 'table-row' : 'none';
                if (passwordInput.value === '') {
                    confirmPasswordInput.value = '';
                }
            }
        }
    });
</script>
</body>
</html>