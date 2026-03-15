<?php
// admin/tambah_wisata.php

session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "<p style='color: red;'>Akses ditolak. Silakan login kembali.</p>";
    exit;
}

require_once dirname(__DIR__) . '/koneksi.php'; // Path ke koneksi database

// Inisialisasi variabel untuk pesan feedback
$message = '';
$message_type = ''; // 'success' atau 'error'

// Ambil data kategori dan kecamatan untuk dropdown
$kategori_options = [];
$kecamatan_options = [];

// Query untuk kategori
$sql_kategori = "SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC";
if ($result_kategori = $conn->query($sql_kategori)) {
    while ($row = $result_kategori->fetch_assoc()) {
        $kategori_options[] = $row;
    }
    $result_kategori->free();
} else {
    error_log("Error fetching categories: " . $conn->error);
}

// Query untuk kecamatan
$sql_kecamatan = "SELECT id_kecamatan, nama_kecamatan FROM kecamatan ORDER BY nama_kecamatan ASC";
if ($result_kecamatan = $conn->query($sql_kecamatan)) {
    while ($row = $result_kecamatan->fetch_assoc()) {
        $kecamatan_options[] = $row;
    }
    $result_kecamatan->free();
} else {
    error_log("Error fetching districts: " . $conn->error);
}

// Proses form submission (jika ada POST request)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi dan sanitasi input
    $nama_wisata = htmlspecialchars(trim($_POST['nama_wisata']));
    $deskripsi_wisata = htmlspecialchars(trim($_POST['deskripsi_wisata']));
    // Pastikan ini divalidasi dengan baik, atau set default jika kosong
    $latitude_wisata = isset($_POST['latitude_wisata']) && !empty($_POST['latitude_wisata']) ? (float)$_POST['latitude_wisata'] : 0.0;
    $longitude_wisata = isset($_POST['longitude_wisata']) && !empty($_POST['longitude_wisata']) ? (float)$_POST['longitude_wisata'] : 0.0;
    
    $alamat_wisata = htmlspecialchars(trim($_POST['alamat_wisata']));
    $jam_operasional_wisata = htmlspecialchars(trim($_POST['jam_operasional_wisata']));
    $harga_tiket_wisata = htmlspecialchars(trim($_POST['harga_tiket_wisata']));
    $kontak_wisata = htmlspecialchars(trim($_POST['kontak_wisata']));
    $website_resmi_wisata = htmlspecialchars(trim($_POST['website_resmi_wisata']));
    $id_kategori = (int)$_POST['id_kategori'];
    $id_kecamatan = (int)$_POST['id_kecamatan'];
    $fasilitas_ids = isset($_POST['fasilitas']) ? $_POST['fasilitas'] : []; // Array ID fasilitas

    $url_gambar_wisata = ''; // Default kosong

    // Penanganan upload gambar
    if (isset($_FILES['gambar_wisata']) && $_FILES['gambar_wisata']['error'] == UPLOAD_ERR_OK) {
        $target_dir = dirname(__DIR__) . "/foto_objek/"; // Direktori penyimpanan gambar
        $imageFileType = strtolower(pathinfo($_FILES["gambar_wisata"]["name"], PATHINFO_EXTENSION));
        $unique_name = uniqid() . '.' . $imageFileType; // Nama unik untuk gambar
        $target_file = $target_dir . $unique_name;
        $uploadOk = 1;

        // Periksa apakah file gambar asli atau palsu
        $check = getimagesize($_FILES["gambar_wisata"]["tmp_name"]);
        if ($check === false) {
            $message = "File bukan gambar.";
            $message_type = "error";
            $uploadOk = 0;
        }

        // Periksa ukuran file (misal: max 5MB)
        if ($_FILES["gambar_wisata"]["size"] > 5000000) {
            $message = "Ukuran gambar terlalu besar, maksimal 5MB.";
            $message_type = "error";
            $uploadOk = 0;
        }

        // Izinkan format file tertentu
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $message = "Hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
            $message_type = "error";
            $uploadOk = 0;
        }

        // Coba upload file jika semua cek lolos
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["gambar_wisata"]["tmp_name"], $target_file)) {
                $url_gambar_wisata = $unique_name; // Simpan nama unik di database
            } else {
                $message = "Maaf, terjadi kesalahan saat mengunggah gambar.";
                $message_type = "error";
            }
        }
    }


    if (empty($message_type)) { // Lanjutkan jika tidak ada error upload gambar
        // Masukkan data ke tabel objek_wisata
        $sql_insert_wisata = "INSERT INTO objek_wisata (
                                nama_wisata, deskripsi_wisata, latitude_wisata, longitude_wisata,
                                alamat_wisata, jam_operasional_wisata, harga_tiket_wisata,
                                kontak_wisata, website_resmi_wisata, url_gambar_wisata,
                                id_kategori, id_kecamatan
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt_insert_wisata = $conn->prepare($sql_insert_wisata);
        if ($stmt_insert_wisata) {
            $stmt_insert_wisata->bind_param(
                "ssddssssssii",
                $nama_wisata, $deskripsi_wisata, $latitude_wisata, $longitude_wisata,
                $alamat_wisata, $jam_operasional_wisata, $harga_tiket_wisata,
                $kontak_wisata, $website_resmi_wisata, $url_gambar_wisata,
                $id_kategori, $id_kecamatan
            );

            if ($stmt_insert_wisata->execute()) {
                $new_wisata_id = $conn->insert_id; // Ambil ID wisata yang baru ditambahkan
                $message = "Data objek wisata berhasil ditambahkan!";
                $message_type = "success";

                // Masukkan fasilitas ke tabel objek_wisata_fasilitas
                if (!empty($fasilitas_ids) && is_array($fasilitas_ids)) {
                    $sql_insert_fasilitas = "INSERT INTO objek_wisata_fasilitas (id_wisata, id_fasilitas) VALUES (?, ?)";
                    $stmt_insert_fasilitas = $conn->prepare($sql_insert_fasilitas);
                    if ($stmt_insert_fasilitas) {
                        foreach ($fasilitas_ids as $fasilitas_id) {
                            $stmt_insert_fasilitas->bind_param("ii", $new_wisata_id, $fasilitas_id);
                            $stmt_insert_fasilitas->execute();
                        }
                        $stmt_insert_fasilitas->close();
                    } else {
                        error_log("Error preparing fasilitas insert statement: " . $conn->error);
                        $message .= " (Gagal menambahkan fasilitas)";
                    }
                }

            } else {
                $message = "Gagal menambahkan data objek wisata: " . $stmt_insert_wisata->error;
                $message_type = "error";
                error_log("Error inserting new wisata: " . $stmt_insert_wisata->error);
            }
            $stmt_insert_wisata->close();
        } else {
            $message = "Gagal menyiapkan statement: " . $conn->error;
            $message_type = "error";
            error_log("Error preparing main wisata insert statement: " . $conn->error);
        }
    }
}

// Ambil daftar fasilitas untuk checkbox
$fasilitas_options = [];
$sql_fasilitas_all = "SELECT id_fasilitas, nama_fasilitas FROM fasilitas ORDER BY nama_fasilitas ASC";
if ($result_fasilitas_all = $conn->query($sql_fasilitas_all)) {
    while ($row = $result_fasilitas_all->fetch_assoc()) {
        $fasilitas_options[] = $row;
    }
    $result_fasilitas_all->free();
} else {
    error_log("Error fetching all facilities: " . $conn->error);
}

$conn->close();
?>

<link rel="stylesheet" href="style/pages/form_tambah_edit_wisata.css"> 
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<section class="form-section">
    <h2>Tambah Data Objek Wisata Baru</h2>

    <?php if ($message): ?>
        <div class="alert <?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form id="tambahWisataForm" class="wisata-form" method="POST" enctype="multipart/form-data" action="tambah_wisata.php">
        <div class="form-group">
            <label for="nama_wisata">Nama Wisata:</label>
            <input type="text" id="nama_wisata" name="nama_wisata" required>
        </div>

        <div class="form-group">
            <label for="deskripsi_wisata">Deskripsi Wisata:</label>
            <textarea id="deskripsi_wisata" name="deskripsi_wisata" rows="5" required></textarea>
        </div>

        <div class="form-group">
            <label for="latitude_wisata">Latitude:</label>
            <input type="text" id="latitude_wisata" name="latitude_wisata" required readonly> 
        </div>

        <div class="form-group">
            <label for="longitude_wisata">Longitude:</label>
            <input type="text" id="longitude_wisata" name="longitude_wisata" required readonly> 
        </div>

        <div id="mapid"></div>

        <div class="form-group">
            <label for="alamat_wisata">Alamat:</label>
            <textarea id="alamat_wisata" name="alamat_wisata" rows="3" required></textarea>
        </div>

        <div class="form-group">
            <label for="jam_operasional_wisata">Jam Operasional:</label>
            <input type="text" id="jam_operasional_wisata" name="jam_operasional_wisata" placeholder="Contoh: 08:00 - 17:00" required>
        </div>

        <div class="form-group">
            <label for="harga_tiket_wisata">Harga Tiket:</label>
            <input type="text" id="harga_tiket_wisata" name="harga_tiket_wisata" placeholder="Contoh: Gratis, Rp 10.000, Variatif" required>
        </div>

        <div class="form-group">
            <label for="kontak_wisata">Kontak:</label>
            <input type="text" id="kontak_wisata" name="kontak_wisata" placeholder="Contoh: 0812-3456-7890" required>
        </div>

        <div class="form-group">
            <label for="website_resmi_wisata">Website Resmi (URL):</label>
            <input type="url" id="website_resmi_wisata" name="website_resmi_wisata" placeholder="Contoh: https://www.wisata.com" required>
        </div>

        <div class="form-group">
            <label for="id_kategori">Kategori:</label>
            <select id="id_kategori" name="id_kategori" required>
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($kategori_options as $kategori): ?>
                    <option value="<?php echo htmlspecialchars($kategori['id_kategori']); ?>">
                        <?php echo htmlspecialchars($kategori['nama_kategori']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="id_kecamatan">Kecamatan:</label>
            <select id="id_kecamatan" name="id_kecamatan" required>
                <option value="">-- Pilih Kecamatan --</option>
                <?php foreach ($kecamatan_options as $kecamatan): ?>
                    <option value="<?php echo htmlspecialchars($kecamatan['id_kecamatan']); ?>">
                        <?php echo htmlspecialchars($kecamatan['nama_kecamatan']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Fasilitas:</label>
            <div class="checkbox-group">
                <?php if (!empty($fasilitas_options)): ?>
                    <?php foreach ($fasilitas_options as $fasilitas): ?>
                        <label>
                            <input type="checkbox" name="fasilitas[]" value="<?php echo htmlspecialchars($fasilitas['id_fasilitas']); ?>">
                            <?php echo htmlspecialchars($fasilitas['nama_fasilitas']); ?>
                        </label>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Tidak ada fasilitas tersedia.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group">
            <label for="gambar_wisata">Unggah Gambar:</label>
            <input type="file" id="gambar_wisata" name="gambar_wisata" accept="image/*">
            <small>Ukuran maksimal 5MB (JPG, JPEG, PNG, GIF)</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-action add-btn"><i class="fas fa-save"></i> Simpan Data</button>
            <button type="button" class="btn-action back-btn" onclick="backToKelolaWisata()"><i class="fas fa-arrow-left"></i> Kembali</button>
        </div>
    </form>
</section>

<script>
    // INI ADALAH SCRIPT LEAFLET YANG AKAN DIJALANKAN SECARA DINAMIS
    // Pastikan ini DILUAR fungsi backToKelolaWisata dan submit form AJAX

    // PENTING: Inisialisasi peta setelah elemen DOM tersedia dan berukuran
    // Gunakan setTimeout kecil untuk memberi waktu browser merender div #mapid
    setTimeout(function() {
        if (document.getElementById('mapid')) {
            var mymap = L.map('mapid').setView([-7.6086, 110.2989], 12); // Koordinat default untuk Magelang, zoom level 12

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(mymap);

            var marker;

            function onMapClick(e) {
                if (marker) {
                    mymap.removeLayer(marker);
                }
                marker = L.marker(e.latlng).addTo(mymap);
                document.getElementById('latitude_wisata').value = e.latlng.lat.toFixed(6);
                document.getElementById('longitude_wisata').value = e.latlng.lng.toFixed(6);
            }

            mymap.on('click', onMapClick);

            // Jika ada nilai latitude/longitude dari POST (misal, setelah submit dengan error validasi lain)
            // Maka tampilkan marker di lokasi tersebut
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['latitude_wisata']) && !empty($_POST['latitude_wisata']) && isset($_POST['longitude_wisata']) && !empty($_POST['longitude_wisata'])) {
                $prev_lat = (float)$_POST['latitude_wisata'];
                $prev_lng = (float)$_POST['longitude_wisata'];
                echo "var initialLat = " . $prev_lat . ";\n";
                echo "var initialLng = " . $prev_lng . ";\n";
                echo "marker = L.marker([initialLat, initialLng]).addTo(mymap);\n";
                echo "mymap.setView([initialLat, initialLng], 15);\n"; // Zoom lebih dekat ke marker
            }
            ?>
            // Penting: Invalidate size setelah elemen #mapid terlihat dan memiliki dimensi
            mymap.invalidateSize(); 
        }
    }, 100); // Beri sedikit delay, misalnya 100ms

    // Fungsi untuk kembali ke halaman kelola_wisata.php
    function backToKelolaWisata() {
        if (typeof window.loadContent === 'function') {
            window.loadContent('kelola_wisata.php', 'Kelola Wisata');
        } else {
            // Ini sebenarnya tidak akan terpakai karena loadContent pasti ada
            window.location.href = 'kelola_wisata.php'; 
        }
    }

    // Tangani submit form menggunakan AJAX agar halaman tidak refresh
    document.getElementById('tambahWisataForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const form = this;
        const formData = new FormData(form);

        const currentButtonHtml = form.querySelector('button[type="submit"]').innerHTML; // Simpan HTML tombol
        form.querySelector('button[type="submit"]').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        form.querySelector('button[type="submit"]').disabled = true;

        fetch(form.action, { 
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            const dynamicContentArea = document.getElementById('dynamic-content-area'); 
            if (dynamicContentArea) {
                dynamicContentArea.innerHTML = html;
                // Panggil executeScriptsInContent lagi untuk script yang baru di-render ulang
                // Ini akan memicu inisialisasi peta lagi dengan setTimeout
                executeScriptsInContent(dynamicContentArea); 
            } else {
                console.error("Main content area not found.");
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
        })
        .finally(() => {
            // Tombol akan di-reset saat konten dimuat ulang (karena tombol juga dirender ulang)
            // Jadi, kode ini mungkin tidak terlalu penting di sini,
            // tapi tetap baik untuk berjaga-jaga jika ada perubahan flow.
            form.querySelector('button[type="submit"]').innerHTML = currentButtonHtml;
            form.querySelector('button[type="submit"]').disabled = false;
        });
    });
</script>