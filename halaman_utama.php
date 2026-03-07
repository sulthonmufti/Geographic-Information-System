<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Disparpora Kabupaten Magelang</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://kit.fontawesome.com/a57f5fcdf1.js" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="leaflet/leaflet.css" />
    <link rel="stylesheet" href="leaflet/Control.FullScreen.css" />

    <link rel="stylesheet" href="style/style.css">

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
        <div class="main-content-wrapper">
            <!-- Search Sidebar -->
            <div class="search-sidebar">
                <div id="search-box">
                    <input type="text" id="search-input" placeholder="Masukkan nama wisata...">
                    <div id="search-buttons-wrapper">
                        <button id="search-button">Cari</button>
                        <button id="reset-button">Reset</button>
                    </div>
                </div>
            </div>

            <!-- Peta -->
            <section id="map-section">
                <div id="mapid"></div>
            </section>

                <!-- Konten Tambahan -->
                <aside class="remaining-aside-content">
                    <div id="filter-kategori-box">
                        <h3><span class="strip"></span>Filter Kategori Objek Wisata</h3>
                        <ul id="filter-kategori-list" class="filter-kategori-list">
                            <!-- Daftar kategori akan ditambahkan secara dinamis -->
                        </ul>
                    </div>
                    <div id="kecamatan-wisata-terbanyak-box">
                        <h3><span class="strip"></span>Kecamatan<br>dengan wisata terbanyak</h3>
                        <ul id="kecamatan-wisata-terbanyak-list" class="filter-kategori-list">
                            </ul>
                    </div>
                </aside>
                <div class="rekomendasi">
                    <div class="rekomendasi-header">
                        <h3><span class="strip"></span>Rekomendasi objek<br>wisata di Kabupaten Magelang</h3>
                    </div>
                        <div id="rekomendasi-container" class="rekomendasi-container"></div>
                </div>
        </div>
    </main>
    <footer class="footer">
        <div>© 2025 – Pemetaan Objek Wisata Kabupaten Magelang. All rights reserved.</div>
        <div>Muhammad Sulthon Mufti (2100018213)</div>
    </footer>

    <script src="leaflet/leaflet.js"></script>
    <script src="leaflet/Control.FullScreen.js"></script>

    <script src="script.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // =========================================================================
            // Fitur: Inisialisasi Peta Leaflet (Sama seperti sebelumnya)
            // =========================================================================
            const map = L.map('mapid', {
                fullscreenControl: true,
                center: [-7.5501, 110.2167],
                zoom: 11,
                minZoom: 10,
                maxZoom: 18,
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // =========================================================================
            // Grup Layer Utama untuk Kontrol Geografis Umum
            // =========================================================================
            const geografisLayer = L.layerGroup().addTo(map); // Awalnya ditambahkan ke peta secara default

            // =========================================================================
            // Grup Layer Baru untuk Geografis Batas Wilayah
            // =========================================================================
            const batasWilayahLayer = L.layerGroup();

            // =========================================================================
            // Layer group khusus untuk GeoJSON kecamatan yang dipilih (hasil filter) ---> (main - kecamatan dengan wisata terbanyak)
            // =========================================================================
                let selectedKecamatanGeoJsonLayer = L.layerGroup().addTo(map);

            // =========================================================================
            // Grup Layer Baru untuk Frekuensi Persebaran (peta choropleth)
            // =========================================================================
            const frekuensiPersebaranLayer = L.layerGroup();

            // =========================================================================
            // GRUP LAYER BARU UNTUK FILTER KATEGORI
            // Ini akan menampung GeoJSON default dan marker yang difilter
            // =========================================================================
            const filterKategoriLayer = L.layerGroup();
            const filteredMarkersGroup = L.layerGroup(); // Sub-group untuk marker yang difilter
            filterKategoriLayer.addLayer(filteredMarkersGroup); // Tambahkan grup marker yang difilter ke dalam layer filter kategori

            // =========================================================================
            // BARU: GRUP LAYER BARU UNTUK FILTER KECAMATAN
            // Ini akan menampung marker yang difilter berdasarkan kecamatan DAN poligon GeoJSON
            // =========================================================================
            const filterKecamatanLayer = L.layerGroup();
            const filteredKecamatanMarkersGroup = L.layerGroup(); // Sub-group untuk marker yang difilter
            const kecamatanGeojsonDisplayGroup = L.layerGroup(); // BARU: Grup untuk menampilkan GeoJSON kecamatan
            filterKecamatanLayer.addLayer(filteredKecamatanMarkersGroup);
            filterKecamatanLayer.addLayer(kecamatanGeojsonDisplayGroup); // Tambahkan grup GeoJSON ke layer filter kecamatan
            
            // Variabel global untuk menyimpan data wisata (agar bisa diakses oleh filter)
            let allWisataData = [];
            // Variabel global untuk menyimpan jumlah objek wisata per kecamatan
            let wisataCounts = {};
            // Objek global untuk menyimpan referensi L.GeoJSON layer berdasarkan nama kecamatan
            const loadedKecamatanGeoJSONs = {};

            // array kosong untuk menyimpan nama kecamatan yang akan digunakan saat menghitung jumlah wisata
            let daftarNamaKecamatan = [];

            // Ini menyimpan seluruh data kecamatan yang dikirim dari backend (get_kecamatan_geojson.php)
            let kecamatanDataMap = {};

            // =========================================================================
            // Fitur: Pemuatan Data Objek Wisata dari Database dan Penambahan Marker (Database)
            // =========================================================================
            const wisataMarkerGroup = L.layerGroup(); // LayerGroup untuk marker wisata umum
            geografisLayer.addLayer(wisataMarkerGroup); // Tambahkan grup marker wisata ke grup geografis utama

            // Variabel untuk menyimpan data kategori dari database (akan diisi nanti)
            let kategoriDataMap = {};

            // Fungsi untuk membuat ikon kustom Font Awesome (Sama seperti sebelumnya)
            // function createFaIcon(iconClass, categoryClassName) {
            //     return L.divIcon({
            //         className: `custom-marker-icon ${categoryClassName}`,
            //         html: `<i class="fa-solid ${iconClass}"></i>`,
            //         iconSize: [20, 20],
            //         iconAnchor: [10, 20],
            //         popupAnchor: [0, -15]
            //     });
            // }

            // Objek untuk memetakan kategori ke ikon Font Awesome (Sama seperti sebelumnya)
            // const kategoriIcons = {
            //     "Wisata Buatan": createFaIcon('fa-building-columns', 'marker-wisata-buatan'),
            //     "Wisata Budaya": createFaIcon('fa-vihara', 'marker-wisata-budaya'),
            //     "Wisata Alam": createFaIcon('fa-tree', 'marker-wisata-alam'),
            //     "Wisata Religi": createFaIcon('fa-mosque', 'marker-wisata-religi'),
            //     "Wisata Minat Khusus": createFaIcon('fa-person-hiking', 'marker-wisata-minat-khusus'),
            //     "default": createFaIcon('fa-map-pin', 'marker-default')
            // };

            // Fungsi untuk membuat ikon kustom Font Awesome
            // Sekarang menerima objek kategori lengkap (dari database) dan customClassName untuk CSS
            function createFaIcon(categoryData, customClassName = '') {
                const iconClass = categoryData.icon_kategori || 'fa-map-pin'; // Gunakan icon_kategori dari DB, fallback ke default FA
                const bgColor = categoryData.warna_icon_kategori || '#3388ff'; // Gunakan warna_icon_kategori dari DB, fallback ke biru

                return L.divIcon({
                    // custom-marker-icon: untuk styling umum seperti border, ukuran icon FA, dll.
                    // customClassName: untuk styling spesifik per kategori jika dibutuhkan (misal: bentuk border, shadow)
                    className: `custom-marker-icon ${customClassName}`,
                    html: `<div style="background-color: ${bgColor};" class="icon-background"><i class="fa-solid ${iconClass}"></i></div>`,
                    iconSize: [16, 16], // Ukuran div icon keseluruhan
                    iconAnchor: [8, 16], // Titik jangkar (bawah tengah)
                    popupAnchor: [0, -32] // Titik jangkar popup (di atas ikon)
                });
            }

            //  Muat data kategori terlebih dahulu dari database 
            fetch('get_kategori_data.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(kategoriRawData => {
                    // Konversi array kategori menjadi map untuk akses cepat berdasarkan nama_kategori
                    kategoriRawData.forEach(kategori => {
                        kategoriDataMap[kategori.nama_kategori] = kategori;
                    });

                    // Tambahkan entri default jika belum ada (untuk fallback icon/warna)
                    if (!kategoriDataMap['default']) {
                        kategoriDataMap['default'] = {
                            nama_kategori: 'default',
                            icon_kategori: 'fa-map-pin',
                            warna_icon_kategori: '#3388ff' // Warna default
                        };
                    }

                    // Setelah data kategori dimuat, baru muat data wisata
                    return fetch('get_wisata_data.php');
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    allWisataData = data;

                    // Hitung frekuensi wisata per kecamatan
                    allWisataData.forEach(wisata => {
                        if (wisata.nama_kecamatan) {
                            wisataCounts[wisata.nama_kecamatan] = (wisataCounts[wisata.nama_kecamatan] || 0) + 1;
                        }
                    });

                    // Loop melalui setiap objek wisata dan tambahkan marker ke peta
                    allWisataData.forEach(wisata => {
                        const lat = parseFloat(wisata.latitude_wisata);
                        const lng = parseFloat(wisata.longitude_wisata);

                        if (!isNaN(lat) && !isNaN(lng)) {
                            // Ambil data kategori lengkap (termasuk icon dan warna) dari kategoriDataMap
                            // Fallback ke kategori 'default' jika nama_kategori tidak ditemukan
                            const categoryInfo = kategoriDataMap[wisata.nama_kategori] || kategoriDataMap['default'];

                            // Gunakan data kategori untuk membuat ikon
                            // Anda masih bisa menambahkan custom class per kategori jika diperlukan di CSS Anda
                            const marker = L.marker([lat, lng], {
                                icon: createFaIcon(categoryInfo, `marker-${wisata.nama_kategori ? wisata.nama_kategori.toLowerCase().replace(/\s/g, '-') : 'default'}`)
                            })
                            .bindPopup(createPopupContent(wisata));
                            wisataMarkerGroup.addLayer(marker);
                        } else {
                            console.warn(`Koordinat tidak valid untuk wisata: ${wisata.nama_wisata}`);
                        }
                    });
                    wisataMarkerGroup.addTo(map);

                    // Setelah data wisata dimuat dan marker dibuat, baru panggil fungsi-fungsi lain
                    loadGeoJsonLayers(Object.values(kecamatanDataMap)); // Perlu data kecamatan dari DB
                    renderCategoryFilters(allWisataData);
                    displayMostWisitedKecamatan(wisataCounts);
                    generateRecommendations(allWisataData);
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                    // Tampilkan pesan error ke pengguna
                    alert('Gagal memuat data. Silakan coba lagi nanti.');
                });


            //======================================================================================
            // Fungsi untuk membuat popup content (dipindahkan ke fungsi terpisah agar bisa digunakan ulang)
            //======================================================================================
            function createPopupContent(wisata) {
                let popupContent = '';
                if (wisata.url_gambar_wisata) {
                    popupContent += `<img src="${wisata.full_gambar_url}" alt="${wisata.nama_wisata}" 
                        style="width:100%; max-height:120px; object-fit: cover; display: block; border-radius: 15px 15px 5px 5px; margin-bottom: 10px;">`;
                }
                popupContent += `<h3 style="font-family: 'Poppins', sans-serif; color: #000; font-weight: bold; font-size: 15px; text-align: center; margin: 0 0 10px 0;">${wisata.nama_wisata}</h3>`;
                if (wisata.deskripsi_wisata) {
                    const words = wisata.deskripsi_wisata.split(/\s+/).filter(word => word.length > 0);
                    let deskripsiSingkat = words.slice(0, 15).join(' ');
                    if (words.length > 15) {
                        deskripsiSingkat += '...';
                    }
                    popupContent += `<p style="font-family: 'Poppins', sans-serif; color: rgba(0,0,0,0.5); font-size: 1em; margin: 0 0 10px 0;">${deskripsiSingkat}</p>`;
                }
                if (wisata.harga_tiket_wisata) {
                    popupContent += `<p style="font-family: 'Poppins', sans-serif; font-size: 1em; margin: 0 0 5px 0;"><b>Tiket masuk:</b> ${wisata.harga_tiket_wisata}</p>`;
                }
                if (wisata.jam_operasional_wisata) {
                    popupContent += `<p style="font-family: 'Poppins', sans-serif; font-size: 1em; margin: 0 0 10px 0;"><b>Operasional:</b> <span style="color: var(--primary-color);"> ${wisata.jam_operasional_wisata}</span></p>`;
                }
                popupContent += `
                    <div style="display: flex; justify-content: space-between; gap: 10px; margin-top: 10px;">
                        <a href="https://www.google.com/maps/dir/?api=1&destination=${wisata.latitude_wisata},${wisata.longitude_wisata}" target="_blank"
                            class="popup-btn btn-rute"> <i class="fas fa-route" style="margin-right: 5px;"></i> Rute
                        </a>
                        <a href="detail_wisata.php?id=${wisata.id_wisata}" class="popup-btn btn-lihat">
                            <i class="fas fa-comment-alt" style="margin-right: 5px;"></i> Lihat
                        </a>
                    </div>
                `;
                return popupContent;
            }

            //===================================================================
            //ini fungsi untuk filter kategori objek wisata (main)
            //===================================================================
            
            // function getIconClassFromKategori(kategori) {
            //     switch (kategori) {
            //         case "Wisata Buatan": return "fa-building-columns";
            //         case "Wisata Budaya": return "fa-vihara";
            //         case "Wisata Alam": return "fa-tree";
            //         case "Wisata Religi": return "fa-mosque";
            //         case "Wisata Minat Khusus": return "fa-person-hiking";
            //         default: return "fa-map-pin";
            //     }
            // }
            
            //Render Kategori untuk filter kategori objek wisata (main)
            function generateKategoriFilterList(data) {
                const filterList = document.getElementById('filter-kategori-list');
                filterList.innerHTML = ''; // Kosongkan dulu

                // Gunakan kategoriDataMap untuk mendapatkan daftar kategori unik dan informasinya
                // Pastikan hanya kategori yang benar-benar ada di allWisataData yang ditampilkan
                const existingKategoriInWisataData = new Set(data.map(wisata => wisata.nama_kategori));

                // Iterasi melalui kategoriDataMap untuk membangun daftar filter
                for (const kategoriNama in kategoriDataMap) {
                    // Hanya tampilkan kategori jika ada data wisata yang cocok (tidak termasuk 'default')
                    if (existingKategoriInWisataData.has(kategoriNama)) { // <-- PERUBAHAN DI SINI
                        const categoryInfo = kategoriDataMap[kategoriNama];
                        const iconCode = categoryInfo.icon_kategori;
                        // const bgColor = categoryInfo.warna_icon_kategori; // Tidak lagi digunakan untuk background style inline

                        const li = document.createElement('li');
                        li.classList.add('kategori-item');
                        // Menambahkan ikon tanpa background warna inline.
                        li.innerHTML = `
                            <div class="kategori-icon-wrapper">
                                <i class="fas ${iconCode}"></i>
                            </div>
                            <span>${kategoriNama}</span>
                        `;

                        li.addEventListener('click', () => {
                            const isActive = li.classList.contains('active');

                            // Reset semua 'active' class
                            document.querySelectorAll('.kategori-item').forEach(el => el.classList.remove('active'));

                            // Pastikan grup marker lain direset/dihapus saat filter kategori utama aktif
                            filteredKecamatanMarkersGroup.clearLayers();
                            kecamatanGeojsonDisplayGroup.clearLayers();
                            filteredMarkersGroup.clearLayers(); // Bersihkan juga grup filter kategori lain jika ada

                            if (isActive) {
                                // Jika sedang aktif dan diklik ulang → reset semua marker
                                showAllMarkers(); // Tampilkan semua marker utama
                            } else {
                                // Jika baru diklik → filter
                                li.classList.add('active');
                                filterByCategory(kategoriNama); // Panggil fungsi filterByCategory dengan nama kategori
                            }
                        });

                        filterList.appendChild(li);
                    }
                }
            }

            //===================================================================
            //Filter Marker Berdasarkan Kategori untuk filter kategori objek wisata (main)
            //===================================================================
            function filterByCategory(kategori) {
                wisataMarkerGroup.clearLayers(); // Hapus semua marker yang ada dari grup utama
                // Pastikan grup marker lain juga bersih jika fungsi ini dipanggil
                filteredMarkersGroup.clearLayers();
                filteredKecamatanMarkersGroup.clearLayers();
                kecamatanGeojsonDisplayGroup.clearLayers();

                // Pastikan hanya grup wisataMarkerGroup yang aktif (karena ini filter utama)
                wisataMarkerGroup.addTo(map);
                filteredMarkersGroup.remove();
                filteredKecamatanMarkersGroup.remove();
                kecamatanGeojsonDisplayGroup.remove();


                allWisataData.forEach(wisata => {
                    const lat = parseFloat(wisata.latitude_wisata);
                    const lng = parseFloat(wisata.longitude_wisata);

                    if (!isNaN(lat) && !isNaN(lng)) {
                        if (wisata.nama_kategori === kategori) {
                            // --- PERUBAHAN DI SINI ---
                            // Ambil data kategori lengkap dari kategoriDataMap
                            const categoryInfo = kategoriDataMap[wisata.nama_kategori] || kategoriDataMap['default'];

                            // Gunakan createFaIcon untuk membuat marker
                            const marker = L.marker([lat, lng], {
                                icon: createFaIcon(categoryInfo, `marker-${(wisata.nama_kategori || 'default').toLowerCase().replace(/\s/g, '-')}`)
                            });
                            // --- AKHIR PERUBAHAN ---

                            // Bagian setTimeout untuk `svgElement.style.fill = 'white';`
                            // Saya telah mengomentari bagian ini di revisi sebelumnya
                            // karena styling seharusnya ditangani oleh CSS dan `createFaIcon`
                            /*
                            setTimeout(() => {
                                const iconElement = marker.getElement();
                                if (iconElement) {
                                    const svgElement = iconElement.querySelector('svg');
                                    if (svgElement) {
                                        svgElement.style.fill = 'white';
                                    }
                                }
                            }, 50);
                            */

                            marker.bindPopup(createPopupContent(wisata));
                            marker.on('popupopen', function() {
                                const popupWrapperElement = this._popup._container;
                                if (popupWrapperElement) {
                                    popupWrapperElement.classList.add('wisata-popup');
                                }
                            });
                            wisataMarkerGroup.addLayer(marker);
                        }
                    } else {
                        console.warn(`Koordinat tidak valid untuk wisata: ${wisata.nama_wisata}`);
                    }
                });
            }

            
            
            // show all marker jika list filter kategori objek wisata diklik lagi (main)
            // Tambahkan pembersihan GeoJSON filter kecamatan di sini
            function showAllMarkers() {
                wisataMarkerGroup.clearLayers();
                // Membersihkan semua grup layer filter lain saat menampilkan semua marker utama
                filteredMarkersGroup.clearLayers();
                filteredMarkersGroup.remove(); // Hapus dari peta
                filteredKecamatanMarkersGroup.clearLayers();
                filteredKecamatanMarkersGroup.remove(); // Hapus dari peta
                kecamatanGeojsonDisplayGroup.clearLayers(); // <--- BARIS BARU / MODIFIKASI: Hapus GeoJSON filter kecamatan
                kecamatanGeojsonDisplayGroup.remove(); // Hapus dari peta

                // Pastikan grup marker utama aktif dan ditambahkan ke peta
                wisataMarkerGroup.addTo(map);

                allWisataData.forEach(wisata => {
                    const lat = parseFloat(wisata.latitude_wisata);
                    const lng = parseFloat(wisata.longitude_wisata);

                    if (!isNaN(lat) && !isNaN(lng)) {
                        // --- PERUBAHAN DI SINI ---
                        // Ambil data kategori lengkap dari kategoriDataMap
                        const categoryInfo = kategoriDataMap[wisata.nama_kategori] || kategoriDataMap['default'];

                        // Gunakan createFaIcon untuk membuat marker
                        const marker = L.marker([lat, lng], {
                            icon: createFaIcon(categoryInfo, `marker-${(wisata.nama_kategori || 'default').toLowerCase().replace(/\s/g, '-')}`)
                        });
                        // --- AKHIR PERUBAHAN ---

                        // Bagian setTimeout untuk `svgElement.style.fill = 'white';`
                        // Saya telah mengomentari bagian ini di revisi sebelumnya
                        // karena styling seharusnya ditangani oleh CSS dan `createFaIcon`
                        /*
                        setTimeout(() => {
                            const iconElement = marker.getElement();
                            if (iconElement) {
                                const svgElement = iconElement.querySelector('svg');
                                if (svgElement) {
                                    svgElement.style.fill = 'white';
                                }
                            }
                        }, 50);
                        */

                        marker.bindPopup(createPopupContent(wisata));
                        marker.on('popupopen', function() {
                            const popupWrapperElement = this._popup._container;
                            if (popupWrapperElement) {
                                popupWrapperElement.classList.add('wisata-popup');
                            }
                        });
                        wisataMarkerGroup.addLayer(marker);
                    } else {
                        console.warn(`Koordinat tidak valid untuk wisata: ${wisata.nama_wisata}`);
                    }
                });

                // PENTING: Jika loadGeoJsonLayers() Anda mengelola batasWilayahLayer,
                // pastikan ia dipanggil di sini agar batas wilayah utama muncul kembali.
                // Asumsi ini sudah ada dan berfungsi.
                loadGeoJsonLayers(); // Panggil fungsi ini untuk memastikan GeoJSON utama kembali ke peta
            }

            // Muat data kategori terlebih dahulu dari database
            fetch('get_kategori_data.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(kategoriRawData => {
                    // Konversi array kategori menjadi map untuk akses cepat berdasarkan nama_kategori
                    kategoriRawData.forEach(kategori => {
                        kategoriDataMap[kategori.nama_kategori] = kategori;
                    });

                    // Tambahkan entri default jika belum ada (untuk fallback icon/warna)
                    if (!kategoriDataMap['default']) {
                        kategoriDataMap['default'] = {
                            nama_kategori: 'default',
                            icon_kategori: 'fa-map-pin',
                            warna_icon_kategori: '#3388ff' // Warna default
                        };
                    }

                    // Setelah data kategori dimuat, baru muat data wisata
                    return fetch('get_wisata_data.php');
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        console.error("Error dari server:", data.error);
                        return;
                    }

                    allWisataData = data; // Simpan data wisata secara global

                    // Inisialisasi wisataCounts dengan semua kecamatan dari GeoJSON files
                    // Catatan: Bagian ini masih mengandalkan kecamatanDataMap (yang diasumsikan sudah ada/dimuat).
                    // Jika Anda ingin sepenuhnya tidak bergantung pada file GeoJSON, Anda perlu menghapus logic ini.
                    // Untuk saat ini, kita biarkan saja karena itu adalah bagian dari kode yang Anda berikan.
                    Object.keys(kecamatanDataMap).forEach(kecamatanName => {
                        if (kecamatanName !== "Tidak Diketahui") {
                            wisataCounts[kecamatanName] = 0;
                        }
                    });

                    allWisataData.forEach(wisata => {
                        // Hitung Frekuensi Objek Wisata per Kecamatan
                        if (wisata.nama_kecamatan) {
                            const kecamatanName = wisata.nama_kecamatan;
                            wisataCounts[kecamatanName] = (wisataCounts[kecamatanName] || 0) + 1;
                        }

                        // Tambahkan Marker ke Layer Geografis Umum
                        if (wisata.latitude_wisata && wisata.longitude_wisata) {
                            const lat = parseFloat(wisata.latitude_wisata);
                            const lng = parseFloat(wisata.longitude_wisata);

                            // Ambil data kategori lengkap (termasuk icon dan warna) dari kategoriDataMap
                            // Fallback ke kategori 'default' jika nama_kategori tidak ditemukan
                            const categoryInfo = kategoriDataMap[wisata.nama_kategori] || kategoriDataMap['default'];

                            // Gunakan data kategori untuk membuat ikon menggunakan createFaIcon
                            const marker = L.marker([lat, lng], {
                                // icon: createFaIcon(categoryInfo, `marker-${wisata.nama_kategori ? wisata.nama_kategori.toLowerCase().replace(/\s/g, '-') : 'default'}`)
                                // Menggunakan nama_kategori yang diformat untuk customClassName
                                icon: createFaIcon(categoryInfo, `marker-${(wisata.nama_kategori || 'default').toLowerCase().replace(/\s/g, '-')}`)
                            });

                            wisataMarkerGroup.addLayer(marker);

                            // Set timeout untuk memastikan ikon sudah dirender sebelum memanipulasi SVG
                            // Catatan: Dengan CSS yang benar (fill: white !important; untuk svg), bagian ini kemungkinan besar sudah tidak diperlukan.
                            // Saya mengomentarinya untuk membersihkan kode, Anda bisa menghapusnya jika sudah dipastikan berfungsi.
                            /*
                            setTimeout(() => {
                                const iconElement = marker.getElement();
                                if (iconElement) {
                                    const svgElement = iconElement.querySelector('svg');
                                    if (svgElement) {
                                        svgElement.style.fill = 'white';
                                    }
                                }
                            }, 50);
                            */

                            marker.bindPopup(createPopupContent(wisata));
                            marker.on('popupopen', function() {
                                const popupWrapperElement = this._popup._container;
                                if (popupWrapperElement) {
                                    popupWrapperElement.classList.add('wisata-popup');
                                }
                            });
                        } else {
                            console.warn(`Koordinat tidak valid untuk wisata: ${wisata.nama_wisata}`);
                        }
                    });

                    // Setelah semua data diproses, muat layer GeoJSON dan kategori filter
                    loadGeoJsonLayers(Object.values(kecamatanDataMap)); // Asumsi ini memerlukan data kecamatan
                    renderCategoryFilters(allWisataData); // Panggil fungsi untuk memuat filter kategori dari data wisata
                    generateKecamatanCheckboxes(allWisataData); // Panggil fungsi untuk memuat filter kecamatan
                    generateKategoriFilterList(allWisataData); // ← penggil filter kategori objek wisata (main)
                    displayMostWisitedKecamatan(wisataCounts); //menampilkan kecamatan dengan wisata terbanyak (main)
                    generateRecommendations(allWisataData); //Panggil fungsi untuk menghasilkan rekomendasi
                })
                .catch(error => {
                    console.error('Ada masalah dengan operasi fetch data wisata atau kategori:', error);
                    // Tampilkan pesan error ke pengguna
                    alert('Gagal memuat data utama. Silakan coba lagi nanti.');
                    loadGeoJsonLayers(Object.values(kecamatanDataMap)); // Tetap muat layer GeoJSON meskipun data wisata gagal
                });
            //===================================================================
            // Fungsi baru untuk menampilkan kecamatan dengan wisata terbanyak (main)
            //===================================================================
            function displayMostWisitedKecamatan() {
                const listElement = document.getElementById('kecamatan-wisata-terbanyak-list');
                listElement.innerHTML = ''; // Kosongkan daftar sebelum mengisi ulang

                const sortedKecamatan = Object.entries(wisataCounts)
                    .filter(([kecamatan, count]) => kecamatan !== "Tidak Diketahui")
                    .sort((a, b) => b[1] - a[1])
                    .slice(0, 3);

                if (sortedKecamatan.length > 0) {
                    sortedKecamatan.forEach(([kecamatan, count]) => {
                        const listItem = document.createElement('li');
                        listItem.classList.add('kecamatan-item');
                        listItem.innerHTML = `<span>${kecamatan}</span><span>${count}</span>`;
                        
                        listItem.addEventListener('click', () => {
                            const isActive = listItem.classList.contains('active');

                            // Reset semua kelas 'active' pada item kecamatan
                            document.querySelectorAll('.kecamatan-item').forEach(el => el.classList.remove('active'));
                            // Juga nonaktifkan semua kategori filter saat filter kecamatan aktif
                            document.querySelectorAll('.kategori-item').forEach(el => el.classList.remove('active'));

                            if (isActive) {
                                // Jika sedang aktif dan diklik ulang → reset semua marker dan GeoJSON filter
                                showAllMarkers(); // showAllMarkers sekarang juga membersihkan selectedKecamatanGeoJsonLayer
                            } else {
                                // Jika baru diklik → filter berdasarkan kecamatan
                                listItem.classList.add('active');
                                filterByKecamatan(kecamatan); // Panggil fungsi filter
                            }
                        });

                        listElement.appendChild(listItem);
                    });
                } else {
                    const listItem = document.createElement('li');
                    listItem.textContent = 'Tidak ada data wisata untuk ditampilkan.';
                    listElement.appendChild(listItem);
                }
            }

            // Filter Marker Berdasarkan Kecamatan (Fungsi LAMA Anda + Penambahan GeoJSON)
            function filterByKecamatan(kecamatan) {
                wisataMarkerGroup.clearLayers(); // Hapus semua marker yang ada

                // <--- BARIS BARU / MODIFIKASI: Hapus GeoJSON kecamatan yang sebelumnya dipilih --->
                selectedKecamatanGeoJsonLayer.clearLayers();

                allWisataData.forEach(wisata => {
                    // Hanya tambahkan marker jika nama_kecamatan cocok dengan yang dipilih
                    if (wisata.nama_kecamatan === kecamatan) {
                        const lat = parseFloat(wisata.latitude_wisata);
                        const lng = parseFloat(wisata.longitude_wisata);

                        if (!isNaN(lat) && !isNaN(lng)) {
                            // --- PERUBAHAN DI SINI ---
                            // Ambil data kategori lengkap (termasuk icon dan warna) dari kategoriDataMap
                            // Fallback ke kategori 'default' jika nama_kategori tidak ditemukan
                            const categoryInfo = kategoriDataMap[wisata.nama_kategori] || kategoriDataMap['default'];

                            // Gunakan data kategori untuk membuat ikon menggunakan createFaIcon
                            const marker = L.marker([lat, lng], {
                                // icon: createFaIcon(categoryInfo, `marker-${wisata.nama_kategori ? wisata.nama_kategori.toLowerCase().replace(/\s/g, '-') : 'default'}`)
                                // Menggunakan nama_kategori yang diformat untuk customClassName
                                icon: createFaIcon(categoryInfo, `marker-${(wisata.nama_kategori || 'default').toLowerCase().replace(/\s/g, '-')}`)
                            });
                            // --- AKHIR PERUBAHAN ---

                            // Set timeout untuk memastikan ikon sudah dirender sebelum memanipulasi SVG
                            // Catatan: Dengan CSS yang benar (fill: white !important; untuk svg), bagian ini kemungkinan besar sudah tidak diperlukan.
                            // Saya mengomentarinya untuk membersihkan kode, Anda bisa menghapusnya jika sudah dipastikan berfungsi.
                            /*
                            setTimeout(() => {
                                const iconElement = marker.getElement();
                                if (iconElement) {
                                    const svgElement = iconElement.querySelector('svg');
                                    if (svgElement) {
                                        svgElement.style.fill = 'white';
                                    }
                                }
                            }, 50);
                            */

                            marker.bindPopup(createPopupContent(wisata));
                            marker.on('popupopen', function() {
                                const popupWrapperElement = this._popup._container;
                                if (popupWrapperElement) {
                                    popupWrapperElement.classList.add('wisata-popup');
                                }
                            });
                            wisataMarkerGroup.addLayer(marker);
                        } else {
                            console.warn(`Koordinat tidak valid untuk wisata: ${wisata.nama_wisata} di kecamatan ${kecamatan}`);
                        }
                    }
                });

                // <--- Tambahkan GeoJSON untuk kecamatan yang dipilih --->
                if (loadedKecamatanGeoJSONs[kecamatan]) {
                    // Jika GeoJSON sudah dimuat sebelumnya (dari loadGeoJsonLayers atau fetch sebelumnya),
                    // tambahkan langsung ke layer group khusus ini
                    selectedKecamatanGeoJsonLayer.addLayer(loadedKecamatanGeoJSONs[kecamatan]);
                    map.fitBounds(loadedKecamatanGeoJSONs[kecamatan].getBounds());
                } else {
                    // Jika belum dimuat, fetch dan tambahkan
                    const geojsonFileName = `${kecamatan}.geojson`;
                    const geojsonFilePath = `data/geojson/${geojsonFileName}`; // Pastikan path ini benar

                    fetch(geojsonFilePath)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`Gagal memuat GeoJSON untuk ${kecamatan}: ${response.statusText}`);
                            }
                            return response.json();
                        })
                        .then(geojsonData => {
                            // Ambil warna dari objek kecamatanDataMap
                            const fillColor = kecamatanDataMap[kecamatan]?.warna || '#7f8c8d';

                            const geojsonLayer = L.geoJson(geojsonData, {
                                style: function (feature) {
                                    return {
                                        fillColor: fillColor,
                                        weight: 2,
                                        opacity: 1,
                                        color: 'white',
                                        dashArray: '3',
                                        fillOpacity: 0.7
                                    };
                                },
                                onEachFeature: function (feature, layer) {
                                    // Anda bisa menambahkan popup atau interaksi di sini jika perlu
                                    // Contoh: layer.bindPopup(`<b>${kecamatan}</b>`);
                                }
                            });
                            loadedKecamatanGeoJSONs[kecamatan] = geojsonLayer; // Simpan ke cache
                            selectedKecamatanGeoJsonLayer.addLayer(geojsonLayer); // Tambahkan ke layer group filter
                            map.fitBounds(geojsonLayer.getBounds()); // Zoom ke area GeoJSON yang dipilih
                        })
                        .catch(error => {
                            console.error(`Error loading GeoJSON for ${kecamatan}:`, error);
                        });
                }
            }


            // =========================================================================
            // Fitur: Pemuatan dan Tampilan Data GeoJSON Batas Wilayah (Kecamatan/Kelurahan)
            // =========================================================================
            const geojsonLayerGroup = L.layerGroup(); // LayerGroup untuk GeoJSON default (bagian dari Geografis Umum)
            geografisLayer.addLayer(geojsonLayerGroup); // Tambahkan grup GeoJSON default ke grup geografis utama

            // GeoJSON layer group untuk Filter Kategori (hanya menampilkan wilayah umum)
            const filterKategoriGeojsonLayer = L.layerGroup();
            filterKategoriLayer.addLayer(filterKategoriGeojsonLayer); // Tambahkan ke grup Filter Kategori

            // Daftar file GeoJSON kecamatan (Sama seperti sebelumnya)
            // Catatan: File-file ini masih akan dimuat untuk layer Batas Wilayah dan Frekuensi Persebaran.
            // Jika Anda ingin sepenuhnya menghapus dependensi GeoJSON, file-file ini juga harus dihapus.
            // const kecamatanGeojsonFiles = [
            //     'Bandongan.geojson', 'Borobudur.geojson', 'Candimulyo.geojson',
            //     'Dukun.geojson', 'Grabag.geojson', 'Kajoran.geojson',
            //     'Kaliangkrik.geojson', 'Mertoyudan.geojson', 'Mungkid.geojson',
            //     'Muntilan.geojson', 'Ngablak.geojson', 'Ngluwar.geojson',
            //     'Pakis.geojson', 'Salam.geojson', 'Salaman.geojson',
            //     'Sawangan.geojson', 'Secang.geojson', 'Srumbung.geojson',
            //     'Tegalrejo.geojson', 'Tempuran.geojson',
            //     'Windusari.geojson', 'Magelang.geojson'
            // ];
            
            // Objek untuk memetakan nama kecamatan ke kode warna heksa untuk layer "Geografis Batas Wilayah" (Sama seperti sebelumnya)
            // const kecamatanColors = {
            //     "Bandongan": "#729B6F", "Borobudur": "#BEB297", "Candimulyo": "#A47158",
            //     "Dukun": "#E77148", "Grabag": "#BECF50", "Kajoran": "#CC55FF",
            //     "Kaliangkrik": "#1A8E9B", "Mertoyudan": "#7D8B8F", "Mungkid": "#449DB6",
            //     "Muntilan": "#3725BB", "Ngablak": "#3725BB", "Ngluwar": "#12C908",
            //     "Pakis": "#8282FF", "Salam": "#2DFFF1", "Salaman": "#908773",
            //     "Sawangan": "#908773", "Secang": "#CF71CD", "Srumbung": "#814E0C",
            //     "Tegalrejo": "#748546", "Tempuran": "#BBD0D6",
            //     "Windusari": "#030302ff", "Magelang": "#FFD700",
            //     "Tidak Diketahui": "#7f8c8d"
            // };
            

            // Ambil data kecamatan (nama, warna, dan URL GeoJSON) lalu tampilkan layer GeoJSON untuk setiap kecamatan dan simpan nama kecamatan ke daftarNamaKecamatan
            fetch('get_kecamatan_geojson.php')
                .then(response => response.json())
                .then(kecamatanData => {
                    kecamatanData.forEach(kec => {
                        // Simpan nama kecamatan ke array untuk digunakan nanti
                        daftarNamaKecamatan.push(kec.nama_kecamatan);

                        // Simpan warna dan URL geojson ke dalam map berdasarkan nama kecamatan
                        kecamatanDataMap[kec.nama_kecamatan] = {
                            warna: kec.warna_geojson_kecamatan,
                            url: kec.url_geojson_kecamatan
                        };

                        // Ambil file GeoJSON dari URL yang diberikan dalam data
                        fetch(kec.url_geojson_kecamatan)
                            .then(res => res.json())
                            .then(geojson => {
                                const layer = L.geoJSON(geojson, {
                                    style: {
                                        color: kec.warna_geojson_kecamatan || "#7f8c8d", // pakai warna dari database, fallback ke abu-abu
                                        weight: 2,
                                        fillOpacity: 0.3
                                    }
                                }).bindPopup(`<strong>${kec.nama_kecamatan}</strong>`);

                                // Tambahkan ke grup layer
                                layer.addTo(batasWilayahLayer);
                            });
                    });

                    // Setelah data kecamatan berhasil diambil, lanjut fetch data wisata
                    return fetch('get_wisata_data.php');
                })

            // ============================================
            // 4. Tangani data objek wisata setelah semua kecamatan tersedia
            // ============================================
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Gagal mengambil data wisata: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(wisataData => {
                    allWisataData = wisataData;

                    // ============================================
                    // 5. Inisialisasi jumlah wisata per kecamatan
                    //    berdasarkan daftar nama kecamatan yang sudah dikumpulkan
                    // ============================================
                    daftarNamaKecamatan.forEach(nama => {
                        wisataCounts[nama] = 0;
                    });

                    // ============================================
                    // 6. Hitung jumlah wisata di setiap kecamatan
                    //    dan tambahkan marker ke peta (jika sudah ada fungsi untuk itu)
                    // ============================================
                    allWisataData.forEach(wisata => {
                        const namaKecamatan = wisata.kecamatan;
                        if (wisataCounts[namaKecamatan] !== undefined) {
                            wisataCounts[namaKecamatan]++;
                        }
                    });

                    // Jalankan fungsi render (misalnya render marker, filter, sidebar, dll)
                    renderAllWisataMarkers(allWisataData);
                    renderCategoryFilters(allWisataData);
                    renderTopKecamatanList(wisataCounts);
                })

            // ============================================
            // 7. Tangani error jika ada kegagalan dalam proses fetch
            // ============================================
                .catch(error => {
                    console.error("Terjadi kesalahan:", error);
                });


            // Fungsi styling default untuk poligon GeoJSON (untuk Geografis Umum dan Filter Kategori GeoJSON)
            function defaultPolygonStyle(feature) {
                return {
                    fillColor: '#3388ff',
                    weight: 2,
                    opacity: 1,
                    color: 'white',
                    dashArray: '3',
                    fillOpacity: 0.2
                };
            }

            // Fungsi styling kustom untuk poligon GeoJSON (untuk Geografis Batas Wilayah) (Sama seperti sebelumnya)
            function customPolygonStyle(feature) {
                const kecamatanName = feature.properties?.WADMKC || feature.properties?.NAME || feature.properties?.KECAMATAN || feature.properties?.KEC || "Tidak Diketahui";
                const color = kecamatanDataMap[kecamatanName]?.warna || '#7f8c8d';

                return {
                    fillColor: color,
                    weight: 2,
                    opacity: 1,
                    color: 'white',
                    dashArray: '3',
                    fillOpacity: 0.7
                };
            }

            // Fungsi styling untuk peta choropleth "Frekuensi Persebaran" (Sama seperti sebelumnya)
            function choroplethStyle(feature) {
                const rawKecamatanName = feature.properties?.WADMKC || feature.properties?.NAME || feature.properties?.KECAMATAN || feature.properties?.KEC || "Tidak Diketahui";

                // Cari nama yang cocok dari wisataCounts
                let count = 0;
                for (const nama in wisataCounts) {
                    if (nama.trim().toLowerCase() === rawKecamatanName.trim().toLowerCase()) {
                        count = wisataCounts[nama];
                        break;
                    }
                }

                let fillColor;
                if (count >= 0 && count <= 5) {
                    fillColor = '#FCEF91';
                } else if (count >= 6 && count <= 10) {
                    fillColor = '#FB9E3A';
                } else if (count >= 11 && count <= 15) {
                    fillColor = '#FF652F';
                } else if (count >= 16 && count <= 20) {
                    fillColor = '#FF2000';
                } else if (count >= 21) {
                    fillColor = '#B82712';
                } else {
                    fillColor = '#ccc';
                }

                return {
                    fillColor: fillColor,
                    weight: 2,
                    opacity: 1,
                    color: 'white',
                    dashArray: '3',
                    fillOpacity: 0.7
                };
            }

            // Fungsi yang dijalankan untuk setiap fitur GeoJSON (untuk pop-up informasi) (Sama seperti sebelumnya)
            function onEachFeatureForPopups(feature, layer, showCount = false) {
                const kel = feature.properties?.NAMDES || feature.properties?.KELURAHAN || feature.properties?.NAMOBJ || feature.properties?.Name || "Tidak Diketahui";
                const kec = feature.properties?.WADMKC || feature.properties?.NAME || feature.properties?.KECAMATAN || feature.properties?.KEC || "Tidak Diketahui";

                let popupContent = `<b>Kelurahan:</b> ${kel}`;
                if (kec && kec !== kel) {
                    popupContent += `, <b>Kecamatan:</b> ${kec}`;
                } else if (kec && kel === "Tidak Diketahui") {
                    popupContent = `<b>Kecamatan:</b> ${kec}`;
                }

                if (showCount && wisataCounts[kec] !== undefined) {
                    popupContent += `<br><b>Jumlah Objek Wisata:</b> ${wisataCounts[kec]} objek`;
                }

                layer.bindPopup(popupContent);
                // Menghapus baris ini untuk menghilangkan fitur zoom saat klik GeoJSON
                // layer.on("click", e => map.fitBounds(e.target.getBounds())); 
            }

            // Fungsi untuk memuat semua layer GeoJSON (Sama seperti sebelumnya, ditambah untuk filter kategori)
            function loadGeoJsonLayers() {
                const kecamatanNames = Object.keys(kecamatanDataMap); // Ganti dari kecamatanGeojsonFiles

                kecamatanNames.forEach(kecamatanName => {
                    const filePath = kecamatanDataMap[kecamatanName]?.url;
                    if (!filePath) {
                        console.warn(`Tidak ada URL GeoJSON untuk kecamatan: ${kecamatanName}`);
                        return;
                    }

                    fetch(filePath)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`Gagal memuat GeoJSON untuk ${kecamatanName}: ${response.statusText}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            // Layer 1: Geografis Umum
                            const defaultGeojsonLayer = L.geoJson(data, {
                                style: defaultPolygonStyle,
                                onEachFeature: function(feature, layer) {
                                    onEachFeatureForPopups(feature, layer, false);
                                }
                            });
                            geojsonLayerGroup.addLayer(defaultGeojsonLayer);

                            // Layer 2: Batas Wilayah (warna kustom)
                            const customColoredGeojsonLayer = L.geoJson(data, {
                                style: customPolygonStyle,
                                onEachFeature: function(feature, layer) {
                                    onEachFeatureForPopups(feature, layer, false);
                                }
                            });
                            batasWilayahLayer.addLayer(customColoredGeojsonLayer);

                            // Layer 3: Frekuensi Persebaran
                            const frekuensiGeojsonLayer = L.geoJson(data, {
                                style: choroplethStyle,
                                onEachFeature: function(feature, layer) {
                                    onEachFeatureForPopups(feature, layer, true);
                                }
                            });
                            frekuensiPersebaranLayer.addLayer(frekuensiGeojsonLayer);

                            // Layer 4: Filter Kategori
                            const filterCategoryBaseGeojsonLayer = L.geoJson(data, {
                                style: defaultPolygonStyle,
                                onEachFeature: function(feature, layer) {
                                    onEachFeatureForPopups(feature, layer, false);
                                }
                            });
                            filterKategoriGeojsonLayer.addLayer(filterCategoryBaseGeojsonLayer);

                            // Simpan cache loaded GeoJSON
                            loadedKecamatanGeojsons[kecamatanName] = L.geoJson(data, {
                                style: customPolygonStyle,
                                onEachFeature: function(feature, layer) {
                                    const kel = feature.properties?.NAMDES || feature.properties?.KELURAHAN || feature.properties?.NAMOBJ || feature.properties?.Name || "Tidak Diketahui";
                                    const kec = feature.properties?.WADMKC || feature.properties?.NAME || feature.properties?.KECAMATAN || feature.properties?.KEC || "Tidak Diketahui";

                                    let popupContent = `<b>Kelurahan:</b> ${kel}`;
                                    if (kec && kec !== kel) {
                                        popupContent += `, <b>Kecamatan:</b> ${kec}`;
                                    } else if (kec && kel === "Tidak Diketahui") {
                                        popupContent = `<b>Kecamatan:</b> ${kec}`;
                                    }
                                    layer.bindPopup(popupContent);
                                }
                            });

                            // Fit map bounds setelah semua layer dimuat
                            const totalExpectedLayers = kecamatanNames.length * 4;
                            const currentLoadedLayers = geojsonLayerGroup.getLayers().length +
                                                        batasWilayahLayer.getLayers().length +
                                                        frekuensiPersebaranLayer.getLayers().length +
                                                        filterKategoriGeojsonLayer.getLayers().length;

                            if (currentLoadedLayers === totalExpectedLayers && !map.getBounds().isValid()) {
                                map.fitBounds(geojsonLayerGroup.getBounds());
                            }
                        })
                        .catch(error => {
                            console.error(`Error loading GeoJSON untuk ${kecamatanName}:`, error);
                        });
                });
            }

            // =========================================================================
            // BARU: Fungsi untuk memfilter dan menampilkan marker berdasarkan kategori
            // =========================================================================
            function filterMarkersByCategory(selectedCategories) {
                wisataMarkerGroup.clearLayers(); // Hapus semua marker dari grup utama
                filteredMarkersGroup.clearLayers(); // Hapus semua marker yang ada dari grup filter

                // Tentukan grup marker mana yang akan digunakan untuk penambahan marker
                // Jika tidak ada kategori yang dipilih, marker akan ditampilkan di wisataMarkerGroup
                // Jika ada kategori yang dipilih, marker akan ditampilkan di filteredMarkersGroup
                const targetGroup = selectedCategories.length === 0 ? wisataMarkerGroup : filteredMarkersGroup;

                if (selectedCategories.length === 0) {
                    // Jika tidak ada kategori yang dipilih, tampilkan semua marker dari allWisataData
                    allWisataData.forEach(wisata => {
                        const lat = parseFloat(wisata.latitude_wisata);
                        const lng = parseFloat(wisata.longitude_wisata);

                        if (!isNaN(lat) && !isNaN(lng)) {
                            const categoryInfo = kategoriDataMap[wisata.nama_kategori] || kategoriDataMap['default'];
                            const marker = L.marker([lat, lng], {
                                icon: createFaIcon(categoryInfo, `marker-${(wisata.nama_kategori || 'default').toLowerCase().replace(/\s/g, '-')}`)
                            });
                            marker.bindPopup(createPopupContent(wisata));
                            marker.on('popupopen', function() {
                                const popupWrapperElement = this._popup._container;
                                if (popupWrapperElement) {
                                    popupWrapperElement.classList.add('wisata-popup');
                                }
                            });
                            targetGroup.addLayer(marker);
                        }
                    });
                    targetGroup.addTo(map); // Tambahkan grup utama ke peta
                    filteredMarkersGroup.remove(); // Pastikan grup filter dihapus jika tidak digunakan
                    return; // Hentikan eksekusi selanjutnya
                }

                allWisataData.forEach(wisata => {
                    const lat = parseFloat(wisata.latitude_wisata);
                    const lng = parseFloat(wisata.longitude_wisata);

                    if (!isNaN(lat) && !isNaN(lng)) {
                        if (selectedCategories.includes(wisata.nama_kategori)) {
                            // --- PERUBAHAN DI SINI ---
                            // Ambil data kategori lengkap (termasuk icon dan warna) dari kategoriDataMap
                            // Fallback ke kategori 'default' jika nama_kategori tidak ditemukan
                            const categoryInfo = kategoriDataMap[wisata.nama_kategori] || kategoriDataMap['default'];

                            // Gunakan data kategori untuk membuat ikon menggunakan createFaIcon
                            const marker = L.marker([lat, lng], {
                                // icon: createFaIcon(categoryInfo, `marker-${wisata.nama_kategori ? wisata.nama_kategori.toLowerCase().replace(/\s/g, '-') : 'default'}`)
                                // Menggunakan nama_kategori yang diformat untuk customClassName
                                icon: createFaIcon(categoryInfo, `marker-${(wisata.nama_kategori || 'default').toLowerCase().replace(/\s/g, '-')}`)
                            });
                            // --- AKHIR PERUBAHAN ---

                            filteredMarkersGroup.addLayer(marker);

                            // Set timeout untuk memastikan ikon sudah dirender sebelum memanipulasi SVG
                            // Catatan: Dengan CSS yang benar (fill: white !important; untuk svg), bagian ini kemungkinan besar sudah tidak diperlukan.
                            // Saya mengomentarinya untuk membersihkan kode, Anda bisa menghapusnya jika sudah dipastikan berfungsi.
                            /*
                            setTimeout(() => {
                                const iconElement = marker.getElement();
                                if (iconElement) {
                                    const svgElement = iconElement.querySelector('svg');
                                    if (svgElement) {
                                        svgElement.style.fill = 'white';
                                    }
                                }
                            }, 50);
                            */

                            marker.bindPopup(createPopupContent(wisata));
                            marker.on('popupopen', function() {
                                const popupWrapperElement = this._popup._container;
                                if (popupWrapperElement) {
                                    popupWrapperElement.classList.add('wisata-popup');
                                }
                            });
                        }
                    } else {
                        console.warn(`Koordinat tidak valid untuk wisata: ${wisata.nama_wisata}`);
                    }
                });

                // Pastikan grup yang relevan ditambahkan ke peta
                filteredMarkersGroup.addTo(map); // Tambahkan grup filter ke peta
                wisataMarkerGroup.remove(); // Hapus grup utama jika filter aktif
            }


            // =========================================================================
            // BARU: Fungsi untuk Render Checkbox Kategori dari data yang sudah ada
            // =========================================================================
            function renderCategoryFilters(data) {
                const categoryFilterContainer = document.querySelector('#category-filter-checkboxes');
                if (!categoryFilterContainer) {
                    console.error("Elemen #category-filter-checkboxes tidak ditemukan.");
                    return;
                }
                categoryFilterContainer.innerHTML = ''; // Kosongkan container sebelumnya

                // Ekstrak kategori unik dari data wisata
                const uniqueCategories = [...new Set(data.map(item => item.nama_kategori))].sort(); // Mengurutkan alfabetis

                uniqueCategories.forEach(category => {
                    if (category) { // Pastikan kategori tidak null/undefined
                        const label = document.createElement('label');
                        label.classList.add('category-filter-label'); // Tambahkan kelas untuk styling
                        const checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.value = category;
                        checkbox.name = 'filter_kategori';
                        checkbox.classList.add('category-filter-checkbox'); // Tambahkan kelas untuk styling

                        const span = document.createElement('span');
                        span.classList.add('category-filter-span'); // Tambahkan kelas untuk styling
                        span.textContent = category;

                        label.appendChild(checkbox);
                        label.appendChild(span);
                        categoryFilterContainer.appendChild(label);

                        checkbox.addEventListener('change', updateFilteredMarkers);
                    }
                });
            }

            // BARU: Fungsi untuk memperbarui marker yang difilter saat checkbox kategori berubah
            function updateFilteredMarkers() {
                const checkboxes = document.querySelectorAll('.category-filter-checkbox:checked');
                const selectedCategories = Array.from(checkboxes).map(cb => cb.value);
                filterMarkersByCategory(selectedCategories);
            }

            // =========================================================================
            // BARU: Fungsi untuk Render Checkbox Kecamatan dari data KECAMATAN_COLORS (SEMUA KECAMATAN)
            // =========================================================================
            function generateKecamatanCheckboxes(data) { // 'data' parameter mungkin tidak digunakan sepenuhnya untuk daftar uniqueKecamatan
                const kecamatanFilterContainer = document.querySelector('#kecamatan-filter-checkboxes');
                if (!kecamatanFilterContainer) {
                    console.error("Elemen #kecamatan-filter-checkboxes tidak ditemukan.");
                    return;
                }
                kecamatanFilterContainer.innerHTML = ''; // Kosongkan container sebelumnya

                const uniqueKecamatan = Object.keys(kecamatanDataMap).sort(); // Menggunakan semua kecamatan dari daftar warna

                uniqueKecamatan.forEach(kecamatanName => {
                    // Opsional: Cek jika nama kecamatan bukan "Tidak Diketahui" sebelum membuat checkbox, jika Anda tidak ingin menampilkannya.
                    if (kecamatanName !== "Tidak Diketahui") { 
                        const label = document.createElement('label');
                        label.classList.add('kecamatan-filter-label');
                        const checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.value = kecamatanName;
                        checkbox.name = 'filter_kecamatan';
                        checkbox.classList.add('kecamatan-filter-checkbox');

                        const span = document.createElement('span');
                        span.classList.add('kecamatan-filter-span');
                        span.textContent = kecamatanName;

                        label.appendChild(checkbox);
                        label.appendChild(span);
                        kecamatanFilterContainer.appendChild(label);

                        checkbox.addEventListener('change', updateFilteredKecamatanMarkers);
                    }
                });
            }

            // =========================================================================
            // BARU: Fungsi untuk memperbarui marker DAN poligon GeoJSON saat checkbox kecamatan berubah
            // =========================================================================
            function updateFilteredKecamatanMarkers() {
                filteredKecamatanMarkersGroup.clearLayers(); // Hapus semua marker yang ada dari grup filter
                kecamatanGeojsonDisplayGroup.clearLayers(); // BARU: Hapus semua poligon GeoJSON yang ada

                const checkboxes = document.querySelectorAll('.kecamatan-filter-checkbox:checked');
                const selectedKecamatanNames = Array.from(checkboxes).map(cb => cb.value);

                // Pastikan grup marker utama tersembunyi saat filter kecamatan aktif
                // dan tampilkan kembali jika tidak ada filter aktif
                if (selectedKecamatanNames.length > 0) {
                    wisataMarkerGroup.remove(); // Sembunyikan semua marker awal
                    filteredKecamatanMarkersGroup.addTo(map); // Pastikan grup filter ditambahkan ke peta
                    kecamatanGeojsonDisplayGroup.addTo(map); // Tambahkan grup GeoJSON ke peta
                } else {
                    wisataMarkerGroup.addTo(map); // Tampilkan semua marker awal
                    filteredKecamatanMarkersGroup.remove(); // Sembunyikan grup filter
                    kecamatanGeojsonDisplayGroup.remove(); // Sembunyikan grup GeoJSON
                    return; // Hentikan eksekusi selanjutnya jika tidak ada filter aktif
                }

                // BARU: Tambahkan poligon GeoJSON untuk kecamatan yang dipilih
                selectedKecamatanNames.forEach(kecamatanName => {
                    if (kecamatanDataMap[kecamatanName] && kecamatanDataMap[kecamatanName].geojsonData) {
                        // Jika data GeoJSON sudah dimuat dan disimpan di kecamatanDataMap
                        const geojsonLayer = L.geoJSON(kecamatanDataMap[kecamatanName].geojsonData, {
                            style: function(feature) {
                                return {
                                    fillColor: kecamatanDataMap[kecamatanName]?.warna || '#7f8c8d', // Gunakan warna yang sudah didefinisikan atau abu-abu default
                                    weight: 2, // Ketebalan garis batas
                                    opacity: 1, // Opasitas garis
                                    color: 'white', // Warna garis batas
                                    dashArray: '3', // Gaya garis putus-putus
                                    fillOpacity: 0.5 // Opasitas isi poligon
                                };
                            },
                            onEachFeature: function(feature, layer) {
                                if (feature.properties && feature.properties.nama_kecamatan) {
                                    layer.bindPopup(`<strong>Kecamatan:</strong> ${feature.properties.nama_kecamatan}`);
                                } else if (kecamatanName) {
                                    layer.bindPopup(`<strong>Kecamatan:</strong> ${kecamatanName}`);
                                }
                            }
                        });
                        kecamatanGeojsonDisplayGroup.addLayer(geojsonLayer);
                    } else {
                        // Jika belum dimuat atau tidak ada di cache, ambil data GeoJSON dari file
                        const geojsonFileName = kecamatanName.replace(/\s+/g, '_'); // Ganti spasi dengan underscore untuk nama file
                        fetch(`data/geojson/${geojsonFileName}.geojson`) // **Jalur file GeoJSON yang diperbarui**
                            .then(response => {
                                if (!response.ok) {
                                    if (response.status === 404) {
                                        console.warn(`File GeoJSON tidak ditemukan untuk Kecamatan ${kecamatanName}: data/geojson/${geojsonFileName}.geojson`);
                                    } else {
                                        throw new Error(`Kesalahan HTTP! status: ${response.status}`);
                                    }
                                }
                                return response.json();
                            })
                            .then(geojsonData => {
                                if (geojsonData) {
                                    // Simpan geojsonData ke kecamatanDataMap untuk caching
                                    if (kecamatanDataMap[kecamatanName]) {
                                        kecamatanDataMap[kecamatanName].geojsonData = geojsonData;
                                    } else {
                                        kecamatanDataMap[kecamatanName] = { geojsonData: geojsonData, warna: '#7f8c8d' }; // Buat entri baru jika belum ada
                                    }

                                    const geojsonLayer = L.geoJSON(geojsonData, {
                                        style: function(feature) {
                                            return {
                                                fillColor: kecamatanDataMap[kecamatanName]?.warna || '#7f8c8d',
                                                weight: 2,
                                                opacity: 1,
                                                color: 'white',
                                                dashArray: '3',
                                                fillOpacity: 0.5
                                            };
                                        },
                                        onEachFeature: function(feature, layer) {
                                            if (feature.properties && feature.properties.nama_kecamatan) {
                                                layer.bindPopup(`<strong>Kecamatan:</strong> ${feature.properties.nama_kecamatan}`);
                                            } else if (kecamatanName) {
                                                layer.bindPopup(`<strong>Kecamatan:</strong> ${kecamatanName}`);
                                            }
                                        }
                                    });
                                    kecamatanGeojsonDisplayGroup.addLayer(geojsonLayer); // Tambahkan ke grup layer batas wilayah
                                }
                            })
                            .catch(error => {
                                console.error(`Gagal memuat GeoJSON untuk ${kecamatanName}:`, error);
                            });
                    }
                });

                // Tampilkan marker untuk kecamatan yang dipilih
                allWisataData.forEach(wisata => {
                    const lat = parseFloat(wisata.latitude_wisata);
                    const lng = parseFloat(wisata.longitude_wisata);

                    if (!isNaN(lat) && !isNaN(lng)) {
                        if (selectedKecamatanNames.includes(wisata.nama_kecamatan)) {
                            // --- PERUBAHAN DI SINI ---
                            // Ambil data kategori lengkap (termasuk icon dan warna) dari kategoriDataMap
                            // Fallback ke kategori 'default' jika nama_kategori tidak ditemukan
                            const categoryInfo = kategoriDataMap[wisata.nama_kategori] || kategoriDataMap['default'];

                            // Gunakan data kategori untuk membuat ikon menggunakan createFaIcon
                            const marker = L.marker([lat, lng], {
                                // icon: createFaIcon(categoryInfo, `marker-${wisata.nama_kategori ? wisata.nama_kategori.toLowerCase().replace(/\s/g, '-') : 'default'}`)
                                // Menggunakan nama_kategori yang diformat untuk customClassName
                                icon: createFaIcon(categoryInfo, `marker-${(wisata.nama_kategori || 'default').toLowerCase().replace(/\s/g, '-')}`)
                            });
                            // --- AKHIR PERUBAHAN ---

                            filteredKecamatanMarkersGroup.addLayer(marker);

                            // Set timeout untuk memastikan ikon sudah dirender sebelum memanipulasi SVG
                            // Catatan: Dengan CSS yang benar (fill: white !important; untuk svg), bagian ini kemungkinan besar sudah tidak diperlukan.
                            // Saya mengomentarinya untuk membersihkan kode, Anda bisa menghapusnya jika sudah dipastikan berfungsi.
                            /*
                            setTimeout(() => {
                                const iconElement = marker.getElement();
                                if (iconElement) {
                                    const svgElement = iconElement.querySelector('svg');
                                    if (svgElement) {
                                        svgElement.style.fill = 'white';
                                    }
                                }
                            }, 50);
                            */

                            marker.bindPopup(createPopupContent(wisata));
                            marker.on('popupopen', function() {
                                const popupWrapperElement = this._popup._container;
                                if (popupWrapperElement) {
                                    popupWrapperElement.classList.add('wisata-popup');
                                }
                            });
                        }
                    } else {
                        console.warn(`Koordinat tidak valid untuk wisata: ${wisata.nama_wisata}`);
                    }
                });
            }

            // Tambahkan ini setelah inisialisasi allWisataData
            // Fungsi untuk menampilkan marker hasil pencarian di peta.
            // Menghapus semua marker yang ada di grup utama, lalu menambahkan marker
            // dari data pencarian yang diberikan, dan menyesuaikan tampilan peta.
            function tampilkanMarkerPencarian(dataPencarian) {
                wisataMarkerGroup.clearLayers(); // Hapus semua marker yang ada dari grup utama
                // Asumsikan kita juga ingin menghapus marker dari grup filter lainnya jika ini dipanggil
                filteredMarkersGroup.clearLayers();
                filteredKecamatanMarkersGroup.clearLayers();
                kecamatanGeojsonDisplayGroup.clearLayers();

                // Pastikan hanya grup wisataMarkerGroup yang aktif saat menampilkan hasil pencarian
                wisataMarkerGroup.addTo(map);
                filteredMarkersGroup.remove();
                filteredKecamatanMarkersGroup.remove();
                kecamatanGeojsonDisplayGroup.remove();

                dataPencarian.forEach(wisata => {
                    const lat = parseFloat(wisata.latitude_wisata);
                    const lng = parseFloat(wisata.longitude_wisata);

                    if (!isNaN(lat) && !isNaN(lng)) {
                        // --- PERUBAHAN DI SINI ---
                        // Ambil data kategori lengkap (termasuk icon dan warna) dari kategoriDataMap
                        // Fallback ke kategori 'default' jika nama_kategori tidak ditemukan
                        const categoryInfo = kategoriDataMap[wisata.nama_kategori] || kategoriDataMap['default'];

                        // Gunakan data kategori untuk membuat ikon menggunakan createFaIcon
                        const marker = L.marker([lat, lng], {
                            // icon: createFaIcon(categoryInfo, `marker-${wisata.nama_kategori ? wisata.nama_kategori.toLowerCase().replace(/\s/g, '-') : 'default'}`)
                            // Menggunakan nama_kategori yang diformat untuk customClassName
                            icon: createFaIcon(categoryInfo, `marker-${(wisata.nama_kategori || 'default').toLowerCase().replace(/\s/g, '-')}`)
                        });
                        // --- AKHIR PERUBAHAN ---

                        marker.bindPopup(createPopupContent(wisata));
                        marker.on('popupopen', function() {
                            const popupWrapperElement = this._popup._container;
                            if (popupWrapperElement) {
                                popupWrapperElement.classList.add('wisata-popup');
                            }
                        });
                        wisataMarkerGroup.addLayer(marker);
                    } else {
                        console.warn(`Koordinat tidak valid untuk wisata pencarian: ${wisata.nama_wisata}`);
                    }
                });

                if (dataPencarian.length > 0) {
                    // Zoom ke lokasi wisata pertama dalam hasil pencarian
                    const lat = parseFloat(dataPencarian[0].latitude_wisata);
                    const lng = parseFloat(dataPencarian[0].longitude_wisata);
                    if (!isNaN(lat) && !isNaN(lng)) {
                        map.setView([lat, lng], 14); // Sesuaikan zoom level sesuai kebutuhan
                    }
                } else {
                    // Opsional: Jika tidak ada hasil, kembalikan tampilan ke default atau tampilkan pesan
                    console.log("Tidak ada hasil pencarian ditemukan.");
                    // map.setView([initialLat, initialLng], initialZoom); // Contoh: kembali ke tampilan awal
                }
            }

            // Event pencarian
            const btnSearch = document.getElementById('search-button');
            const btnReset = document.getElementById('reset-button');

            btnSearch.addEventListener('click', function() {
                const keyword = document.getElementById('search-input').value.toLowerCase();
                const hasil = allWisataData.filter(w => w.nama_wisata.toLowerCase().includes(keyword));

                if (hasil.length > 0) {
                    tampilkanMarkerPencarian(hasil);
                } else {
                    alert("Objek wisata tidak ditemukan.");
                    // Opsional: Setelah alert, kembalikan ke tampilan default atau kosongkan peta
                    wisataMarkerGroup.clearLayers();
                    filteredMarkersGroup.clearLayers();
                    filteredKecamatanMarkersGroup.clearLayers();
                    kecamatanGeojsonDisplayGroup.clearLayers();
                    wisataMarkerGroup.addTo(map); // Pastikan grup utama kembali ke peta
                    // Anda juga bisa memanggil fungsi untuk merender ulang semua marker awal jika diinginkan:
                    // renderAllMarkers(); // (jika Anda memiliki fungsi seperti ini)
                    map.setView([-7.5501, 110.2167], 11); // Kembali ke tampilan awal peta
                }
            });

            btnReset.addEventListener('click', function() {
                document.getElementById('search-input').value = '';
                wisataMarkerGroup.clearLayers(); // Hapus semua marker yang ada di grup utama

                // Pastikan grup filter juga dibersihkan dan dihapus dari peta
                filteredMarkersGroup.clearLayers();
                filteredMarkersGroup.remove();
                filteredKecamatanMarkersGroup.clearLayers();
                filteredKecamatanMarkersGroup.remove();
                kecamatanGeojsonDisplayGroup.clearLayers();
                kecamatanGeojsonDisplayGroup.remove();

                // Pastikan hanya grup utama yang aktif setelah reset
                wisataMarkerGroup.addTo(map);

                allWisataData.forEach(wisata => {
                    const lat = parseFloat(wisata.latitude_wisata);
                    const lng = parseFloat(wisata.longitude_wisata);

                    if (!isNaN(lat) && !isNaN(lng)) {
                        // --- PERUBAHAN DI SINI ---
                        // Ambil data kategori lengkap (termasuk icon dan warna) dari kategoriDataMap
                        // Fallback ke kategori 'default' jika nama_kategori tidak ditemukan
                        const categoryInfo = kategoriDataMap[wisata.nama_kategori] || kategoriDataMap['default'];

                        // Gunakan data kategori untuk membuat ikon menggunakan createFaIcon
                        const marker = L.marker([lat, lng], {
                            // icon: createFaIcon(categoryInfo, `marker-${wisata.nama_kategori ? wisata.nama_kategori.toLowerCase().replace(/\s/g, '-') : 'default'}`)
                            // Menggunakan nama_kategori yang diformat untuk customClassName
                            icon: createFaIcon(categoryInfo, `marker-${(wisata.nama_kategori || 'default').toLowerCase().replace(/\s/g, '-')}`)
                        });
                        // --- AKHIR PERUBAHAN ---

                        marker.bindPopup(createPopupContent(wisata));
                        marker.on('popupopen', function() {
                            const popupWrapperElement = this._popup._container;
                            if (popupWrapperElement) {
                                popupWrapperElement.classList.add('wisata-popup');
                            }
                        });
                        wisataMarkerGroup.addLayer(marker);
                    } else {
                        console.warn(`Koordinat tidak valid untuk wisata: ${wisata.nama_wisata}`);
                    }
                });
                map.setView([-7.5501, 110.2167], 11); // Kembali ke tampilan awal peta
            });

            // =========================================================================
            // BARU: KONTROL LEGENDA GAMBAR
            // =========================================================================
            const ImageLegendControl = L.Control.extend({
                options: {
                    position: 'bottomleft' // Pojok kiri bawah
                },

                onAdd: function(map) {
                    const div = L.DomUtil.create('div', 'leaflet-control-legend-image');
                    div.innerHTML = '<img src="leaflet/images/Frekuensi_Geografis.svg" alt="Legenda Peta">';
                    return div;
                }
            });

            // Instance kontrol legenda gambar (tetapi belum ditambahkan ke peta)
            const imageLegend = new ImageLegendControl();
            let isImageLegendAdded = false; // Flag untuk melacak status penambahan legenda

            // =========================================================================
            // KONTROL LAYER KUSTOM MENGGUNAKAN L.Control.extend (Diperbarui)
            // =========================================================================
            const CustomLayerControl = L.Control.extend({
                options: {
                    position: 'topright'
                },

                onAdd: function(map) {
                    const container = L.DomUtil.create('div', 'leaflet-control-custom-layer leaflet-bar');
                    container.innerHTML = `
                        <img src="leaflet/layers-2x.png" alt="Layers Icon" id="layer-icon">
                        <div id="layer-checkboxes">
                            <label class="control-main-label"> <input type="checkbox" id="toggle-geografis" checked>
                                <span class="layer-label">Geografis Umum</span>
                            </label>
                            <label class="control-main-label"> <input type="checkbox" id="toggle-batas-wilayah">
                                <span class="layer-label">Geografis Batas Wilayah</span>
                            </label>
                            <label class="control-main-label"> <input type="checkbox" id="toggle-frekuensi-persebaran">
                                <span class="layer-label">Frekuensi Persebaran</span>
                            </label>
                            <label class="control-main-label"> <input type="checkbox" id="toggle-filter-kategori">
                                <span class="layer-label">Filter Kategori</span>
                            </label>
                            <div id="category-filter-checkboxes" class="category-filter-sub-menu">
                                </div>
                            <label class="control-main-label"> <input type="checkbox" id="toggle-kecamatan">
                                <span class="layer-label">Filter Kecamatan</span>
                            </label>
                            <div id="kecamatan-filter-checkboxes" class="kecamatan-filter-sub-menu">
                                </div>
                        </div>
                    `;

                    const layerIcon = container.querySelector('#layer-icon');
                    const layerCheckboxesContainer = container.querySelector('#layer-checkboxes'); // Container utama checkbox
                    const geografisCheckbox = container.querySelector('#toggle-geografis');
                    const batasWilayahCheckbox = container.querySelector('#toggle-batas-wilayah');
                    const frekuensiPersebaranCheckbox = container.querySelector('#toggle-frekuensi-persebaran');
                    const filterKategoriToggleCheckbox = container.querySelector('#toggle-filter-kategori'); // Checkbox utama Filter Kategori
                    const categoryFilterSubMenu = container.querySelector('#category-filter-checkboxes');
                    // BARU: Elemen Filter Kecamatan
                    const filterKecamatanToggleCheckbox = container.querySelector('#toggle-kecamatan'); // UBAH: Gunakan ID yang benar
                    const kecamatanFilterSubMenu = container.querySelector('#kecamatan-filter-checkboxes');

                    // Mencegah klik peta menyebar ke dalam container kontrol
                    L.DomEvent.disableClickPropagation(container);
                    // Mencegah scroll pada container kontrol
                    L.DomEvent.disableScrollPropagation(container);

                    // Variabel untuk melacak status buka/tutup kontrol layer
                    let isLayerControlOpen = false;

                    // Fungsi untuk menampilkan/menyembunyikan daftar layer
                    function toggleLayerControl() {
                        if (isLayerControlOpen) {
                            layerCheckboxesContainer.style.display = 'none';
                        } else {
                            layerCheckboxesContainer.style.display = 'block';
                        }
                        isLayerControlOpen = !isLayerControlOpen;
                    }

                    // Event Listener untuk ikon layer agar expand/collapse menu
                    L.DomEvent.on(layerIcon, 'click', function(e) {
                        L.DomEvent.stopPropagation(e); // Penting untuk mencegah event bubbling
                        toggleLayerControl();
                    });

                    // =========================================================================
                    // BARU: Event Listener untuk menutup kontrol saat klik di luar
                    // =========================================================================
                    L.DomEvent.on(document, 'click', function(e) {
                        // Periksa apakah target klik BUKAN bagian dari kontrol layer (icon atau menu)
                        if (isLayerControlOpen && !container.contains(e.target) && e.target !== layerIcon) {
                            toggleLayerControl(); // Tutup kontrol
                        }
                    });


                    // Status awal: Geografis Umum aktif dan yang lain mati
                    map.addLayer(geografisLayer);
                    categoryFilterSubMenu.style.display = 'none'; // Awalnya sembunyikan sub-menu kategori
                    // BARU: Sembunyikan sub-menu kecamatan di awal
                    kecamatanFilterSubMenu.style.display = 'none';

                    // BARU: Fungsi untuk memperbarui visibilitas legenda gambar
                    function updateLegendVisibility() {
                        const legendElement = imageLegend.getContainer(); // Ambil elemen div legenda

                        // Cek apakah ada layer yang relevan sedang aktif
                        const relevantLayerActive = batasWilayahCheckbox.checked || 
                                                    frekuensiPersebaranCheckbox.checked || 
                                                    filterKecamatanToggleCheckbox.checked;

                        if (relevantLayerActive) {
                            if (!isImageLegendAdded) {
                                imageLegend.addTo(map); // Tambahkan legenda ke peta jika belum
                                isImageLegendAdded = true;
                            }
                            // Tambahkan class 'visible' untuk menampilkan dengan fade in
                            if (legendElement) { // Cek keberadaan elemen sebelum manipulasi class
                                legendElement.classList.add('visible');
                            }
                        } else {
                            // Sembunyikan legenda dan hapus dari peta
                            if (isImageLegendAdded) {
                                if (legendElement) {
                                    legendElement.classList.remove('visible'); // Hapus class 'visible' untuk fade out
                                    // Tunggu animasi fade selesai sebelum menghapus dari DOM
                                    setTimeout(() => {
                                        if (!relevantLayerActive && isImageLegendAdded) { // Cek ulang status
                                            imageLegend.remove();
                                            isImageLegendAdded = false;
                                        }
                                    }, 300); // Sesuaikan dengan durasi transisi CSS
                                }
                            }
                        }
                    }


                    // =========================================================================
                    // Logika BARU: Kontrol Interaksi Layer
                    // =========================================================================

                    // Event Listener untuk layer "Geografis Umum"
                    L.DomEvent.on(geografisCheckbox, 'change', function(e) {
                        // Jika "Filter Kategori" sedang aktif, nonaktifkan terlebih dahulu
                        if (filterKategoriToggleCheckbox.checked) {
                            filterKategoriToggleCheckbox.checked = false;
                            map.removeLayer(filterKategoriLayer);
                            categoryFilterSubMenu.style.display = 'none'; // Sembunyikan sub-menu
                            filteredMarkersGroup.clearLayers(); // Hapus marker yang difilter
                            document.querySelectorAll('.category-filter-checkbox').forEach(subCb => subCb.checked = false); // Hapus centang sub-checkbox
                        }
                        // BARU: Nonaktifkan Filter Kecamatan jika aktif
                        if (filterKecamatanToggleCheckbox.checked) {
                            filterKecamatanToggleCheckbox.checked = false;
                            map.removeLayer(filterKecamatanLayer);
                            kecamatanFilterSubMenu.style.display = 'none';
                            filteredKecamatanMarkersGroup.clearLayers();
                            kecamatanGeojsonDisplayGroup.clearLayers(); // BARU: Hapus poligon GeoJSON
                            document.querySelectorAll('.kecamatan-filter-checkbox').forEach(subCb => subCb.checked = false);
                        }

                        if (this.checked) {
                            map.addLayer(geografisLayer);
                        } else {
                            map.removeLayer(geografisLayer);
                        }
                        updateLegendVisibility(); // BARU: Panggil fungsi updateLegendVisibility
                    });

                    // Event Listener untuk layer "Geografis Batas Wilayah"
                    L.DomEvent.on(batasWilayahCheckbox, 'change', function(e) {
                        // Jika "Filter Kategori" sedang aktif, nonaktifkan terlebih dahulu
                        if (filterKategoriToggleCheckbox.checked) {
                            filterKategoriToggleCheckbox.checked = false;
                            map.removeLayer(filterKategoriLayer);
                            categoryFilterSubMenu.style.display = 'none'; // Sembunyikan sub-menu
                            filteredMarkersGroup.clearLayers(); // Hapus marker yang difilter
                            document.querySelectorAll('.category-filter-checkbox').forEach(subCb => subCb.checked = false); // Hapus centang sub-checkbox
                        }
                        // BARU: Nonaktifkan Filter Kecamatan jika aktif
                        if (filterKecamatanToggleCheckbox.checked) {
                            filterKecamatanToggleCheckbox.checked = false;
                            map.removeLayer(filterKecamatanLayer);
                            kecamatanFilterSubMenu.style.display = 'none';
                            filteredKecamatanMarkersGroup.clearLayers();
                            kecamatanGeojsonDisplayGroup.clearLayers(); // BARU: Hapus poligon GeoJSON
                            document.querySelectorAll('.kecamatan-filter-checkbox').forEach(subCb => subCb.checked = false);
                        }
                        
                        if (this.checked) {
                            map.addLayer(batasWilayahLayer);
                        } else {
                            map.removeLayer(batasWilayahLayer);
                        }
                        updateLegendVisibility(); // BARU: Panggil fungsi updateLegendVisibility
                    });

                    // Event Listener untuk layer "Frekuensi Persebaran"
                    L.DomEvent.on(frekuensiPersebaranCheckbox, 'change', function(e) {
                        // Jika "Filter Kategori" sedang aktif, nonaktifkan terlebih dahulu
                        if (filterKategoriToggleCheckbox.checked) {
                            filterKategoriToggleCheckbox.checked = false;
                            map.removeLayer(filterKategoriLayer);
                            categoryFilterSubMenu.style.display = 'none'; // Sembunyikan sub-menu
                            filteredMarkersGroup.clearLayers(); // Hapus marker yang difilter
                            document.querySelectorAll('.category-filter-checkbox').forEach(subCb => subCb.checked = false); // Hapus centang sub-checkbox
                        }
                        // BARU: Nonaktifkan Filter Kecamatan jika aktif
                        if (filterKecamatanToggleCheckbox.checked) {
                            filterKecamatanToggleCheckbox.checked = false;
                            map.removeLayer(filterKecamatanLayer);
                            kecamatanFilterSubMenu.style.display = 'none';
                            filteredKecamatanMarkersGroup.clearLayers();
                            kecamatanGeojsonDisplayGroup.clearLayers(); // BARU: Hapus poligon GeoJSON
                            document.querySelectorAll('.kecamatan-filter-checkbox').forEach(subCb => subCb.checked = false);
                        }

                        if (this.checked) {
                            map.addLayer(frekuensiPersebaranLayer);
                        } else {
                            map.removeLayer(frekuensiPersebaranLayer);
                        }
                        updateLegendVisibility(); // BARU: Panggil fungsi updateLegendVisibility
                    });


                    // Event Listener untuk "Filter Kategori" (bersifat mutually exclusive dengan layer geografis)
                    L.DomEvent.on(filterKategoriToggleCheckbox, 'change', function(e) {
                        if (this.checked) {
                            // Jika "Filter Kategori" diaktifkan, nonaktifkan semua layer utama lainnya
                            geografisCheckbox.checked = false;
                            map.removeLayer(geografisLayer);
                            
                            batasWilayahCheckbox.checked = false;
                            map.removeLayer(batasWilayahLayer);
                            
                            frekuensiPersebaranCheckbox.checked = false;
                            map.removeLayer(frekuensiPersebaranLayer);

                            // BARU: Nonaktifkan Filter Kecamatan jika aktif
                            if (filterKecamatanToggleCheckbox.checked) {
                                filterKecamatanToggleCheckbox.checked = false;
                                map.removeLayer(filterKecamatanLayer);
                                kecamatanFilterSubMenu.style.display = 'none';
                                filteredKecamatanMarkersGroup.clearLayers();
                                kecamatanGeojsonDisplayGroup.clearLayers(); // BARU: Hapus poligon GeoJSON
                                document.querySelectorAll('.kecamatan-filter-checkbox').forEach(subCb => subCb.checked = false);
                            }

                            map.addLayer(filterKategoriLayer); // Tambahkan layer filter kategori ke peta
                            categoryFilterSubMenu.style.display = 'block'; // Tampilkan sub-menu kategori
                            updateFilteredMarkers(); // Perbarui marker berdasarkan pilihan sub-filter saat ini
                        } else {
                            map.removeLayer(filterKategoriLayer); // Hapus layer filter kategori dari peta
                            categoryFilterSubMenu.style.display = 'none'; // Sembunyikan sub-menu
                            filteredMarkersGroup.clearLayers(); // Hapus semua marker yang difilter saat layer utama dimatikan
                            document.querySelectorAll('.category-filter-checkbox').forEach(cb => cb.checked = false); // Hapus centang semua sub-checkbox
                        }
                        updateLegendVisibility(); // BARU: Panggil fungsi updateLegendVisibility
                    });

                    // BARU: Event Listener untuk "Filter Kecamatan" (bersifat mutually exclusive dengan layer geografis)
                    L.DomEvent.on(filterKecamatanToggleCheckbox, 'change', function(e) {
                        if (this.checked) {
                            // Jika "Filter Kecamatan" diaktifkan, nonaktifkan semua layer utama lainnya
                            geografisCheckbox.checked = false;
                            map.removeLayer(geografisLayer);
                            
                            batasWilayahCheckbox.checked = false;
                            map.removeLayer(batasWilayahLayer);
                            
                            frekuensiPersebaranCheckbox.checked = false;
                            map.removeLayer(frekuensiPersebaranLayer);

                            // Nonaktifkan Filter Kategori jika aktif
                            if (filterKategoriToggleCheckbox.checked) {
                                filterKategoriToggleCheckbox.checked = false;
                                map.removeLayer(filterKategoriLayer);
                                categoryFilterSubMenu.style.display = 'none';
                                filteredMarkersGroup.clearLayers();
                                document.querySelectorAll('.category-filter-checkbox').forEach(subCb => subCb.checked = false);
                            }

                            map.addLayer(filterKecamatanLayer); // Tambahkan layer filter kecamatan ke peta
                            kecamatanFilterSubMenu.style.display = 'block'; // Tampilkan sub-menu kecamatan
                            updateFilteredKecamatanMarkers(); // Perbarui marker dan poligon berdasarkan pilihan sub-filter saat ini
                        } else {
                            map.removeLayer(filterKecamatanLayer); // Hapus layer filter kecamatan dari peta
                            kecamatanFilterSubMenu.style.display = 'none'; // Sembunyikan sub-menu
                            filteredKecamatanMarkersGroup.clearLayers(); // Hapus semua marker yang difilter saat layer utama dimatikan
                            kecamatanGeojsonDisplayGroup.clearLayers(); // BARU: Hapus poligon GeoJSON saat layer utama dimatikan
                            document.querySelectorAll('.kecamatan-filter-checkbox').forEach(cb => cb.checked = false); // Hapus centang semua sub-checkbox
                        }
                        updateLegendVisibility(); // BARU: Panggil fungsi updateLegendVisibility
                    });
                    // BARU: Panggil updateLegendVisibility() sekali saat inisialisasi untuk memastikan status awal
                    updateLegendVisibility();


                    return container;
                },

                onRemove: function(map) {
                    // Tidak ada yang perlu dilakukan saat kontrol dihapus
                }
            });

            // Tambahkan kontrol layer kustom ke peta
            new CustomLayerControl().addTo(map);

            // Pastikan "Geografis Umum" ditambahkan ke peta saat halaman dimuat jika checkboxnya sudah tercentang di HTML
            if (document.querySelector('#toggle-geografis').checked) {
                map.addLayer(geografisLayer);
            }

            // =========================================================================
            //Fitur membuat rekomendasi objek wisata
            // =========================================================================
            // Fungsi untuk memotong deskripsi menjadi N kata
            function truncateDescription(text, maxWords) {
                if (!text) return '';
                const words = text.split(' ');
                if (words.length > maxWords) {
                    return words.slice(0, maxWords).join(' ') + '...';
                }
                return text;
            }

            // Fungsi untuk menghasilkan rekomendasi objek wisata
            function generateRecommendations() {
                const rekomendasiContainer = document.getElementById('rekomendasi-container');
                if (!rekomendasiContainer) {
                    console.error("Elemen 'rekomendasi-container' tidak ditemukan.");
                    return;
                }

                rekomendasiContainer.innerHTML = ''; // Kosongkan wadah rekomendasi sebelumnya

                // Pastikan ada data wisata dan lebih dari 4 untuk rekomendasi yang beragam
                if (!allWisataData || allWisataData.length < 4) {
                    console.warn("Tidak cukup data wisata untuk menghasilkan 4 rekomendasi.");
                    // Anda bisa menampilkan pesan "Tidak ada rekomendasi" di sini
                    rekomendasiContainer.innerHTML = "<p>Tidak ada rekomendasi objek wisata untuk ditampilkan.</p>";
                    return;
                }

                // Ambil 4 objek wisata secara acak
                const shuffledData = [...allWisataData].sort(() => 0.5 - Math.random()); // Acak array
                const selectedRecommendations = shuffledData.slice(0, 4); // Ambil 4 pertama

                selectedRecommendations.forEach(wisata => {
                    const card = document.createElement('div');
                    card.classList.add('rekomendasi-card');

                    // Pastikan URL gambar relatif terhadap halaman_utama.php
                    // get_wisata_data.php sudah memberikan full_gambar_url = 'foto_objek/' . $row['url_gambar_wisata'];
                    // Jadi, pastikan folder 'foto_objek' sejajar dengan halaman_utama.php
                    const imageUrl = wisata.full_gambar_url || 'path/to/default_image.jpg'; // Ganti dengan gambar default jika tidak ada

                    const truncatedDesc = truncateDescription(wisata.deskripsi_wisata, 10); // Potong deskripsi menjadi 10 kata

                    card.innerHTML = `
                        <img src="${imageUrl}" alt="${wisata.nama_wisata}">
                        <div class="rekomendasi-card-content">
                            <h4>${wisata.nama_wisata}</h4>
                            <p>${truncatedDesc}</p>
                            <a href="detail_wisata.php?id=${wisata.id_wisata}" class="btn-lihat-informasi">Lihat Informasi</a>
                        </div>
                    `;
                    rekomendasiContainer.appendChild(card);
                });
            }
        }); // Akhir document.addEventListener('DOMContentLoaded')
    </script>
</body>
</html>