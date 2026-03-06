<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Home - Disparpora Kabupaten Magelang</title>

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <script src="https://kit.fontawesome.com/a57f5fcdf1.js" crossorigin="anonymous"></script>

  <link rel="stylesheet" href="leaflet/leaflet.css" />
  <link rel="stylesheet" href="leaflet/Control.FullScreen.css" />
  <link rel="stylesheet" href="style/style.css" />

  
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
    <section id="map-section" style="position: relative;">
      <div id="mapid"></div>

      <!-- Kontrol Layer (Icon + Checkbox) -->
      <div id="custom-layer-toggle">
        <img src="leaflet/layers-2x.png" alt="Layers Icon" id="layer-icon">
        <div id="layer-checkboxes">
          <label>
            <input type="checkbox" id="toggle-geografis" checked>
            <span class="layer-label">Geografis Umum</span>
          </label>
        </div>
      </div>
    </section>

    <p style="text-align: center; margin-top: 50px; font-size: 1.2em;">fitur lainnya is coming soon~</p>
  </main>

  <footer class="footer">
        <div>© 2025 – Pemetaan Objek Wisata Kabupaten Magelang. All rights reserved.</div>
        <div>Muhammad Sulthon Mufti (2100018213)</div>
  </footer>

  <script src="leaflet/leaflet.js"></script>
  <script src="leaflet/Control.FullScreen.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const map = L.map('mapid', {
        fullscreenControl: true,
        center: [-7.5501, 110.2167],
        zoom: 11,
        minZoom: 10,
        maxZoom: 18,
      });

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(map);

      // ✅ Grup utama layer geografis
      const geografisLayer = L.layerGroup().addTo(map);

      // ===============================
      // ✅ GeoJSON Layer
      // ===============================
      const geojsonLayerGroup = L.layerGroup();
      geografisLayer.addLayer(geojsonLayerGroup);

      const kecamatanGeojsonFiles = [
        'Bandongan.geojson', 'Borobudur.geojson', 'Candimulyo.geojson',
        'Dukun.geojson', 'Grabag.geojson', 'Kajoran.geojson',
        'Kaliangkrik.geojson', 'Magelang.geojson', 'Mertoyudan.geojson',
        'Mungkid.geojson', 'Muntilan.geojson', 'Ngablak.geojson',
        'Ngluwar.geojson', 'Pakis.geojson', 'Salam.geojson',
        'Salaman.geojson', 'Sawangan.geojson', 'Secang.geojson',
        'Srumbung.geojson', 'Tegalrejo.geojson', 'Tempuran.geojson',
        'Windusari.geojson'
      ];

      function style(feature) {
        return {
          fillColor: '#3388ff',
          weight: 2,
          opacity: 1,
          color: 'white',
          dashArray: '3',
          fillOpacity: 0.2
        };
      }

      function onEachFeature(feature, layer) {
        const kel = feature.properties?.NAMDES || feature.properties?.KELURAHAN || feature.properties?.NAMOBJ || feature.properties?.Name || "Tidak Diketahui";
        const kec = feature.properties?.WADMKC || feature.properties?.NAME || feature.properties?.KECAMATAN || feature.properties?.KEC || "Tidak Diketahui";
        let popupContent = `<b>Kelurahan:</b> ${kel}`;
        if (kec && kec !== kel) popupContent += `, <b>Kecamatan:</b> ${kec}`;
        else if (kel === "Tidak Diketahui") popupContent = `<b>Kecamatan:</b> ${kec}`;

        layer.bindPopup(popupContent);
        layer.on("click", e => map.fitBounds(e.target.getBounds()));
      }

      kecamatanGeojsonFiles.forEach(fileName => {
        fetch(`data/geojson/${fileName}`)
          .then(res => res.json())
          .then(data => {
            const geojsonLayer = L.geoJson(data, {
              style,
              onEachFeature
            });
            geojsonLayerGroup.addLayer(geojsonLayer);
          });
      });

      // ===============================
      //  Marker dari Database
      // ===============================
      const wisataMarkerGroup = L.layerGroup();
      geografisLayer.addLayer(wisataMarkerGroup);

      function createFaIcon(iconClass, categoryClassName) {
        return L.divIcon({
          className: `custom-marker-icon ${categoryClassName}`,
          html: `<i class="fa-solid ${iconClass}"></i>`,
          iconSize: [20, 20],
          iconAnchor: [10, 20],
          popupAnchor: [0, -15]
        });
      }

      const kategoriIcons = {
        "Wisata Buatan": createFaIcon('fa-building-columns', 'marker-wisata-buatan'),
        "Wisata Budaya": createFaIcon('fa-vihara', 'marker-wisata-budaya'),
        "Wisata Alam": createFaIcon('fa-tree', 'marker-wisata-alam'),
        "Wisata Religi": createFaIcon('fa-mosque', 'marker-wisata-religi'),
        "Wisata Minat Khusus": createFaIcon('fa-person-hiking', 'marker-wisata-minat-khusus'),
      };

      fetch('get_wisata_data.php')
        .then(res => res.json())
        .then(data => {
          data.forEach(wisata => {
            if (wisata.latitude_wisata && wisata.longitude_wisata) {
              const icon = kategoriIcons[wisata.nama_kategori] || createFaIcon('fa-map-pin', 'marker-default');
              const marker = L.marker([wisata.latitude_wisata, wisata.longitude_wisata], { icon });
              wisataMarkerGroup.addLayer(marker);

              let popupContent = '';
              if (wisata.url_gambar_wisata) {
                popupContent += `<img src="${wisata.full_gambar_url}" style="width:100%; max-height:120px; object-fit: cover; border-radius: 15px 15px 5px 5px; margin-bottom: 10px;">`;
              }
              popupContent += `<h3 style="font-family: Poppins; font-weight: bold; font-size: 15px; text-align: center;">${wisata.nama_wisata}</h3>`;
              if (wisata.deskripsi_wisata) {
                const words = wisata.deskripsi_wisata.split(/\s+/);
                const desc = words.slice(0, 15).join(" ") + (words.length > 15 ? "..." : "");
                popupContent += `<p style="font-family: Poppins; color: rgba(0,0,0,0.5);">${desc}</p>`;
              }
              if (wisata.harga_tiket_wisata) {
                popupContent += `<p><b>Tiket masuk:</b> ${wisata.harga_tiket_wisata}</p>`;
              }
              if (wisata.jam_operasional_wisata) {
                popupContent += `<p><b>Operasional:</b> <span style="color: var(--primary-color);">${wisata.jam_operasional_wisata}</span></p>`;
              }
              popupContent += `
                <div style="display: flex; justify-content: space-between; gap: 10px;">
                  <a href="https://www.google.com/maps/dir/?api=1&destination=${wisata.latitude_wisata},${wisata.longitude_wisata}" target="_blank" class="popup-btn btn-rute">
                    <i class="fas fa-route" style="margin-right: 5px;"></i> Rute
                  </a>
                  <a href="#" onclick="alert('Fitur Lihat untuk ${wisata.nama_wisata} akan datang.'); return false;" class="popup-btn btn-lihat">
                    <i class="fas fa-comment-alt" style="margin-right: 5px;"></i> Lihat
                  </a>
                </div>
              `;
              marker.bindPopup(popupContent);
            }
          });
        });

      // ===============================
      //  Kontrol Checkbox Layer
      // ===============================
      const layerIcon = document.getElementById('layer-icon');
      const layerCheckboxes = document.getElementById('layer-checkboxes');
      const geografisCheckbox = document.getElementById('toggle-geografis');

      layerIcon.addEventListener('click', function () {
        layerCheckboxes.style.display = layerCheckboxes.style.display === 'block' ? 'none' : 'block';
      });

      geografisCheckbox.addEventListener('change', function () {
        if (this.checked) {
          map.addLayer(geografisLayer);
        } else {
          map.removeLayer(geografisLayer);
        }
      });
    });
  </script>
</body>
</html>