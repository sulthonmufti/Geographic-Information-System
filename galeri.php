<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri pemetaan kabupaten magelang</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a57f5fcdf1.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style/untuk_galeri.css">
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
        <section class="search-section">
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Cari objek wisata berdasarkan nama">
                <button id="searchButton" class="search-btn">Cari</button>
                <button id="resetButton" class="reset-btn">Reset</button>
            </div>
        </section>

        <section class="kategori-section">
            <div id="kategori-buttons" class="kategori-buttons-wrapper">
            </div>
        </section>

        <section class="galeri-wisata-section">
            <div id="wisata-list" class="wisata-list-wrapper">
            </div>
        </section>

        <div id="pagination-container"></div>
    </main>

    <footer class="footer">
        <div>© 2025 – Pemetaan Objek Wisata Kabupaten Magelang. All rights reserved.</div>
        <div>Muhammad Sulthon Mufti (2100018213)</div>
    </footer>

    <script src="script.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ========================
            // Variabel untuk elemen penting
            // ========================
            const kategoriButtonsWrapper = document.getElementById('kategori-buttons');
            const wisataListWrapper = document.getElementById('wisata-list');
            const paginationContainer = document.getElementById('pagination-container');
            const searchInput = document.getElementById('searchInput');
            const searchButton = document.getElementById('searchButton');
            const resetButton = document.getElementById('resetButton');

            let allWisataData = [];
            let filteredWisataData = [];
            const itemsPerPage = 20; // Tentukan jumlah item per halaman
            let currentPage = 1;

            // ========================
            // Ambil data wisata dari server
            // ========================
            function fetchWisataData() {
                return fetch('get_wisata_data.php')
                    .then(response => response.json())
                    .then(data => {
                        allWisataData = data;
                        filteredWisataData = [...data];
                        renderPage(currentPage);
                        renderPagination(filteredWisataData);
                    });
            }

            // ========================
            // Fungsi untuk merandom urutan array (Fisher–Yates shuffle)
            // ========================
            function shuffleArray(array) {
                const shuffled = [...array];
                for (let i = shuffled.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
                }
                return shuffled;
            }

            // ========================
            // Fungsi untuk menampilkan data objek wisata per halaman
            // ========================
            function renderPage(page) {
                wisataListWrapper.innerHTML = '';
                const startIndex = (page - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;
                
                const shuffledData = shuffleArray(filteredWisataData); // Acak data sebelum menampilkan
                const paginatedItems = shuffledData.slice(startIndex, endIndex);

                if (paginatedItems.length === 0) {
                    wisataListWrapper.innerHTML = '<p>Tidak ada data wisata ditemukan.</p>';
                    return;
                }

                paginatedItems.forEach(wisata => {
                    const card = document.createElement('div');
                    card.className = 'wisata-card';
                    card.innerHTML = `
                        <a href="detail_wisata.php?id=${wisata.id_wisata}" class="card-link">
                            <div class="wisata-image-container">
                                <img src="${wisata.full_gambar_url}" alt="${wisata.nama_wisata}">
                                <div class="wisata-text-overlay">
                                    <h3>${wisata.nama_wisata}</h3>
                                    <p>${wisata.nama_kategori || 'Tidak ada kategori'}</p>
                                    <p>Kec. ${wisata.nama_kecamatan || 'Tidak ada kecamatan'}</p>
                                </div>
                            </div>
                        </a>
                    `;
                    wisataListWrapper.appendChild(card);
                });

                window.scrollTo({ top: 0, behavior: 'smooth' }); // Gulir ke atas saat ganti halaman
            }

            // ========================
            // Fungsi untuk membuat dan menampilkan tombol pagination
            // ========================
            function renderPagination(data) {
                paginationContainer.innerHTML = ''; // Kosongkan container pagination
                const totalPages = Math.ceil(data.length / itemsPerPage);

                if (totalPages <= 1) {
                    return; // Tidak perlu pagination jika hanya ada satu halaman
                }

                const paginationWrapper = document.createElement('div');
                paginationWrapper.className = 'pagination';

                // Tombol "Previous"
                const prevLink = document.createElement('a');
                prevLink.href = '#';
                prevLink.className = 'pagination-link';
                prevLink.innerHTML = '&laquo;';
                if (currentPage === 1) {
                    prevLink.style.pointerEvents = 'none';
                    prevLink.style.opacity = '0.5';
                }
                prevLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (currentPage > 1) {
                        currentPage--;
                        renderPage(currentPage);
                        renderPagination(data);
                    }
                });
                paginationWrapper.appendChild(prevLink);

                // Angka halaman
                for (let i = 1; i <= totalPages; i++) {
                    const pageLink = document.createElement('a');
                    pageLink.href = '#';
                    pageLink.className = `pagination-link ${i === currentPage ? 'active' : ''}`;
                    pageLink.textContent = i;
                    pageLink.addEventListener('click', (e) => {
                        e.preventDefault();
                        if (i !== currentPage) {
                            currentPage = i;
                            renderPage(currentPage);
                            renderPagination(data);
                        }
                    });
                    paginationWrapper.appendChild(pageLink);
                }

                // Tombol "Next"
                const nextLink = document.createElement('a');
                nextLink.href = '#';
                nextLink.className = 'pagination-link';
                nextLink.innerHTML = '&raquo;';
                if (currentPage === totalPages) {
                    nextLink.style.pointerEvents = 'none';
                    nextLink.style.opacity = '0.5';
                }
                nextLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (currentPage < totalPages) {
                        currentPage++;
                        renderPage(currentPage);
                        renderPagination(data);
                    }
                });
                paginationWrapper.appendChild(nextLink);

                paginationContainer.appendChild(paginationWrapper);
            }

            // ========================
            // Fungsi untuk melakukan pencarian
            // ========================
            function performSearch() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                let results = [];
                
                if (searchTerm === '') {
                    results = [...allWisataData];
                } else {
                    results = allWisataData.filter(wisata =>
                        (wisata.nama_wisata && wisata.nama_wisata.toLowerCase().includes(searchTerm))
                    );
                }
                
                filteredWisataData = [...results];
                currentPage = 1; // Reset ke halaman 1 setelah pencarian
                renderPage(currentPage);
                renderPagination(filteredWisataData);
                
                // Non-aktifkan tombol kategori aktif saat pencarian
                document.querySelectorAll('.kategori-btn').forEach(btn => setKategoriBtnStyle(btn, false));
                const semuaBtn = document.querySelector('.kategori-btn:first-child');
                if (semuaBtn) {
                    setKategoriBtnStyle(semuaBtn, true);
                }
            }

            // ========================
            // Event Listener untuk tombol Cari
            // ========================
            searchButton.addEventListener('click', performSearch);

            // ========================
            // Event Listener untuk tombol Reset
            // ========================
            resetButton.addEventListener('click', () => {
                searchInput.value = '';
                filteredWisataData = [...allWisataData];
                currentPage = 1; // Reset ke halaman 1
                renderPage(currentPage);
                renderPagination(filteredWisataData);
                
                document.querySelectorAll('.kategori-btn').forEach(btn => setKategoriBtnStyle(btn, false));
                const semuaBtn = document.querySelector('.kategori-btn:first-child');
                if (semuaBtn) {
                    setKategoriBtnStyle(semuaBtn, true);
                }
            });

            // ========================
            // Event Listener untuk enter pada input pencarian
            // ========================
            searchInput.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    performSearch();
                }
            });

            // ========================
            // Ambil kategori dari server dan buat tombol filter
            // ========================
            fetch('get_kategori_data.php')
                .then(response => response.json())
                .then(kategoriList => {
                    // Tombol default "Semua"
                    const semuaBtn = document.createElement('button');
                    semuaBtn.className = 'kategori-btn active';
                    semuaBtn.innerHTML = 'Semua';
                    setKategoriBtnStyle(semuaBtn, true);
                    semuaBtn.addEventListener('click', () => {
                        document.querySelectorAll('.kategori-btn').forEach(btn => setKategoriBtnStyle(btn, false));
                        setKategoriBtnStyle(semuaBtn, true);

                        searchInput.value = '';
                        filteredWisataData = [...allWisataData];
                        currentPage = 1; // Reset ke halaman 1 setelah filter
                        renderPage(currentPage);
                        renderPagination(filteredWisataData);
                    });
                    kategoriButtonsWrapper.appendChild(semuaBtn);

                    // Tombol berdasarkan kategori
                    kategoriList.forEach(kategori => {
                        const btn = document.createElement('button');
                        btn.className = 'kategori-btn';
                        btn.innerHTML = `${kategori.nama_kategori}`;
                        setKategoriBtnStyle(btn, false);

                        btn.addEventListener('click', () => {
                            document.querySelectorAll('.kategori-btn').forEach(btn => setKategoriBtnStyle(btn, false));
                            setKategoriBtnStyle(btn, true);

                            searchInput.value = '';

                            const filtered = allWisataData.filter(w =>
                                (w.nama_kategori || '').trim().toLowerCase() === kategori.nama_kategori.trim().toLowerCase()
                            );
                            filteredWisataData = [...filtered];
                            currentPage = 1; // Reset ke halaman 1 setelah filter
                            renderPage(currentPage);
                            renderPagination(filteredWisataData);
                        });
                        kategoriButtonsWrapper.appendChild(btn);
                    });
                });

            // ========================
            // Fungsi styling tombol aktif & tidak aktif
            // ========================
            function setKategoriBtnStyle(button, isActive) {
                if (isActive) {
                    button.classList.add('active');
                    button.style.backgroundColor = '#E50914';
                    button.style.color = '#fff';
                } else {
                    button.classList.remove('active');
                    button.style.backgroundColor = '#DDDDDD';
                    button.style.color = '#000';
                }
            }

            // Ambil data wisata setelah kategori selesai di-setup
            fetchWisataData();
        });
    </script>
</body>
</html>