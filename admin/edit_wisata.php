<?php
// admin/edit_wisata.php

session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "<p style='color: red;'>Akses ditolak. Silakan login kembali.</p>";
    exit;
}

require_once dirname(__DIR__) . '/koneksi.php'; // Pastikan path ke koneksi database Anda benar

$message = '';
$message_type = '';
$wisata_data = null; // Variabel untuk menyimpan data wisata yang akan diedit
$selected_fasilitas_ids = []; // Untuk fasilitas yang sudah terpilih

// --- 1. Ambil ID Wisata dari URL (GET request) ---
// Ini dijalankan saat halaman pertama kali dimuat (setelah klik 'Edit' dari kelola_wisata.php)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_wisata_to_edit = (int)$_GET['id'];

    // --- 2. Ambil Data Wisata dari Database Berdasarkan ID ---
    $sql_get_wisata = "SELECT * FROM objek_wisata WHERE id_wisata = ?";
    $stmt_get_wisata = $conn->prepare($sql_get_wisata);
    if ($stmt_get_wisata) {
        $stmt_get_wisata->bind_param("i", $id_wisata_to_edit);
        $stmt_get_wisata->execute();
        $result_get_wisata = $stmt_get_wisata->get_result();
        if ($result_get_wisata->num_rows > 0) {
            $wisata_data = $result_get_wisata->fetch_assoc();

            // Ambil fasilitas yang terkait dengan wisata ini
            $sql_get_fasilitas_wisata = "SELECT id_fasilitas FROM objek_wisata_fasilitas WHERE id_wisata = ?";
            $stmt_get_fasilitas_wisata = $conn->prepare($sql_get_fasilitas_wisata);
            if ($stmt_get_fasilitas_wisata) {
                $stmt_get_fasilitas_wisata->bind_param("i", $id_wisata_to_edit);
                $stmt_get_fasilitas_wisata->execute();
                $result_fasilitas_wisata = $stmt_get_fasilitas_wisata->get_result();
                while ($row = $result_fasilitas_wisata->fetch_assoc()) {
                    $selected_fasilitas_ids[] = $row['id_fasilitas'];
                }
                $stmt_get_fasilitas_wisata->close();
            } else {
                error_log("Error preparing fasilitas for existing wisata: " . $conn->error);
            }

        } else {
            $message = "Data wisata tidak ditemukan.";
            $message_type = "error";
            $wisata_data = null; // Pastikan null jika tidak ditemukan
        }
        $stmt_get_wisata->close();
    } else {
        $message = "Gagal menyiapkan statement untuk mengambil data wisata.";
        $message_type = "error";
        error_log("Error preparing get wisata statement: " . $conn->error);
    }
} else {
    $message = "ID wisata tidak disediakan.";
    $message_type = "error";
}

// --- Ambil data kategori dan kecamatan (Sama seperti tambah_wisata.php, ini dibutuhkan untuk dropdown) ---
$kategori_options = [];
$sql_kategori = "SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC";
if ($result_kategori = $conn->query($sql_kategori)) {
    while ($row = $result_kategori->fetch_assoc()) {
        $kategori_options[] = $row;
    }
    $result_kategori->free();
} else {
    error_log("Error fetching categories: " . $conn->error);
}

$kecamatan_options = [];
$sql_kecamatan = "SELECT id_kecamatan, nama_kecamatan FROM kecamatan ORDER BY nama_kecamatan ASC";
if ($result_kecamatan = $conn->query($sql_kecamatan)) {
    while ($row = $result_kecamatan->fetch_assoc()) {
        $kecamatan_options[] = $row;
    }
    $result_kecamatan->free();
} else {
    error_log("Error fetching districts: " . $conn->error);
}

// Ambil daftar fasilitas untuk checkbox (Sama seperti tambah_wisata.php)
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


// --- 3. Proses Form Submission (UPDATE Data - POST request) ---
// Ini dijalankan saat form disubmit (melalui AJAX)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_wisata'])) {
    // Pastikan ID wisata yang diedit ada dari POST (hidden field)
    $id_wisata = (int)$_POST['id_wisata']; // Ini akan datang dari hidden input
    
    // Pastikan data wisata untuk ID ini sudah ada di database
    // (Penting untuk mencegah update data yang tidak ada atau ID manipulasi)
    $sql_check_wisata = "SELECT url_gambar_wisata FROM objek_wisata WHERE id_wisata = ?";
    $stmt_check_wisata = $conn->prepare($sql_check_wisata);
    if ($stmt_check_wisata) {
        $stmt_check_wisata->bind_param("i", $id_wisata);
        $stmt_check_wisata->execute();
        $result_check_wisata = $stmt_check_wisata->get_result();
        if ($result_check_wisata->num_rows > 0) {
            $existing_wisata_data = $result_check_wisata->fetch_assoc();
            $url_gambar_wisata_lama = $existing_wisata_data['url_gambar_wisata'];
        } else {
            $message = "Data wisata dengan ID tersebut tidak ditemukan.";
            $message_type = "error";
            // Set wisata_data ke null agar form tidak ditampilkan jika ID tidak valid saat POST
            $wisata_data = null; 
        }
        $stmt_check_wisata->close();
    } else {
        $message = "Gagal memeriksa data wisata.";
        $message_type = "error";
        $wisata_data = null; 
    }

    if ($wisata_data !== null) { // Lanjutkan hanya jika wisata_data berhasil diambil atau divalidasi
        // Validasi dan sanitasi input (Sama seperti tambah_wisata.php)
        $nama_wisata = htmlspecialchars(trim($_POST['nama_wisata']));
        $deskripsi_wisata = htmlspecialchars(trim($_POST['deskripsi_wisata']));
        $latitude_wisata = isset($_POST['latitude_wisata']) && !empty($_POST['latitude_wisata']) ? (float)$_POST['latitude_wisata'] : 0.0;
        $longitude_wisata = isset($_POST['longitude_wisata']) && !empty($_POST['longitude_wisata']) ? (float)$_POST['longitude_wisata'] : 0.0;
        $alamat_wisata = htmlspecialchars(trim($_POST['alamat_wisata']));
        $jam_operasional_wisata = htmlspecialchars(trim($_POST['jam_operasional_wisata']));
        $harga_tiket_wisata = htmlspecialchars(trim($_POST['harga_tiket_wisata']));
        $kontak_wisata = htmlspecialchars(trim($_POST['kontak_wisata']));
        $website_resmi_wisata = htmlspecialchars(trim($_POST['website_resmi_wisata']));
        $id_kategori = (int)$_POST['id_kategori'];
        $id_kecamatan = (int)$_POST['id_kecamatan'];
        $fasilitas_ids = isset($_POST['fasilitas']) ? $_POST['fasilitas'] : [];

        $url_gambar_wisata = $url_gambar_wisata_lama; // Default: gunakan gambar lama

        // Penanganan upload gambar (Mirip dengan tambah_wisata.php, tapi ada penanganan gambar lama)
        if (isset($_FILES['gambar_wisata']) && $_FILES['gambar_wisata']['error'] == UPLOAD_ERR_OK) {
            $target_dir = dirname(__DIR__) . "/foto_objek/";
            $imageFileType = strtolower(pathinfo($_FILES["gambar_wisata"]["name"], PATHINFO_EXTENSION));
            $unique_name = uniqid() . '.' . $imageFileType;
            $target_file = $target_dir . $unique_name;
            $uploadOk = 1;

            $check = getimagesize($_FILES["gambar_wisata"]["tmp_name"]);
            if ($check === false) {
                $message = "File bukan gambar.";
                $message_type = "error";
                $uploadOk = 0;
            }
            if ($_FILES["gambar_wisata"]["size"] > 5000000) {
                $message = "Ukuran gambar terlalu besar, maksimal 5MB.";
                $message_type = "error";
                $uploadOk = 0;
            }
            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                $message = "Hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
                $message_type = "error";
                $uploadOk = 0;
            }

            if ($uploadOk == 1 && empty($message_type)) { // Pastikan tidak ada error sebelumnya
                // Hapus gambar lama jika ada dan berhasil upload gambar baru
                if (!empty($url_gambar_wisata_lama) && file_exists($target_dir . $url_gambar_wisata_lama)) {
                    unlink($target_dir . $url_gambar_wisata_lama);
                }

                if (move_uploaded_file($_FILES["gambar_wisata"]["tmp_name"], $target_file)) {
                    $url_gambar_wisata = $unique_name;
                } else {
                    $message = "Maaf, terjadi kesalahan saat mengunggah gambar baru.";
                    $message_type = "error";
                }
            }
        }


        if (empty($message_type)) { // Lanjutkan jika tidak ada error upload gambar atau validasi lainnya
            // --- 4. Query UPDATE ke Tabel objek_wisata ---
            $sql_update_wisata = "UPDATE objek_wisata SET
                                    nama_wisata = ?, deskripsi_wisata = ?, latitude_wisata = ?, longitude_wisata = ?,
                                    alamat_wisata = ?, jam_operasional_wisata = ?, harga_tiket_wisata = ?,
                                    kontak_wisata = ?, website_resmi_wisata = ?, url_gambar_wisata = ?,
                                    id_kategori = ?, id_kecamatan = ?
                                  WHERE id_wisata = ?";

            $stmt_update_wisata = $conn->prepare($sql_update_wisata);
            if ($stmt_update_wisata) {
                $stmt_update_wisata->bind_param(
                    "ssddssssssiii",
                    $nama_wisata, $deskripsi_wisata, $latitude_wisata, $longitude_wisata,
                    $alamat_wisata, $jam_operasional_wisata, $harga_tiket_wisata,
                    $kontak_wisata, $website_resmi_wisata, $url_gambar_wisata,
                    $id_kategori, $id_kecamatan, $id_wisata // ID Wisata untuk WHERE clause
                );

                if ($stmt_update_wisata->execute()) {
                    $message = "Data objek wisata berhasil diperbarui!";
                    $message_type = "success";

                    // --- 5. Update Fasilitas (Delete All and Re-Insert Selected) ---
                    // Hapus fasilitas lama yang terkait dengan wisata ini
                    $sql_delete_fasilitas = "DELETE FROM objek_wisata_fasilitas WHERE id_wisata = ?";
                    $stmt_delete_fasilitas = $conn->prepare($sql_delete_fasilitas);
                    if ($stmt_delete_fasilitas) {
                        $stmt_delete_fasilitas->bind_param("i", $id_wisata);
                        $stmt_delete_fasilitas->execute();
                        $stmt_delete_fasilitas->close();
                    } else {
                        error_log("Error preparing delete fasilitas statement: " . $conn->error);
                    }

                    // Masukkan fasilitas baru yang dipilih
                    if (!empty($fasilitas_ids) && is_array($fasilitas_ids)) {
                        $sql_insert_fasilitas = "INSERT INTO objek_wisata_fasilitas (id_wisata, id_fasilitas) VALUES (?, ?)";
                        $stmt_insert_fasilitas = $conn->prepare($sql_insert_fasilitas);
                        if ($stmt_insert_fasilitas) {
                            foreach ($fasilitas_ids as $fasilitas_id) {
                                $stmt_insert_fasilitas->bind_param("ii", $id_wisata, $fasilitas_id);
                                $stmt_insert_fasilitas->execute();
                            }
                            $stmt_insert_fasilitas->close();
                        } else {
                            error_log("Error preparing fasilitas insert statement: " . $conn->error);
                            $message .= " (Gagal memperbarui fasilitas)"; // Tambahkan pesan ke existing
                        }
                    }

                    // Setelah update sukses, ambil data terbaru untuk mengisi ulang form
                    // Ini penting agar form menampilkan data yang paling baru, termasuk gambar baru
                    $sql_get_wisata_after_update = "SELECT * FROM objek_wisata WHERE id_wisata = ?";
                    $stmt_get_wisata_after_update = $conn->prepare($sql_get_wisata_after_update);
                    if ($stmt_get_wisata_after_update) {
                        $stmt_get_wisata_after_update->bind_param("i", $id_wisata);
                        $stmt_get_wisata_after_update->execute();
                        $result_get_wisata_after_update = $stmt_get_wisata_after_update->get_result();
                        if ($result_get_wisata_after_update->num_rows > 0) {
                            $wisata_data = $result_get_wisata_after_update->fetch_assoc(); // Update $wisata_data
                        }
                        $stmt_get_wisata_after_update->close();
                    }

                    // Perbarui juga selected_fasilitas_ids setelah update
                    $selected_fasilitas_ids = [];
                    $sql_get_fasilitas_wisata_after_update = "SELECT id_fasilitas FROM objek_wisata_fasilitas WHERE id_wisata = ?";
                    $stmt_get_fasilitas_wisata_after_update = $conn->prepare($sql_get_fasilitas_wisata_after_update);
                    if ($stmt_get_fasilitas_wisata_after_update) {
                        $stmt_get_fasilitas_wisata_after_update->bind_param("i", $id_wisata);
                        $stmt_get_fasilitas_wisata_after_update->execute();
                        $result_fasilitas_wisata_after_update = $stmt_get_fasilitas_wisata_after_update->get_result();
                        while ($row = $result_fasilitas_wisata_after_update->fetch_assoc()) {
                            $selected_fasilitas_ids[] = $row['id_fasilitas'];
                        }
                        $stmt_get_fasilitas_wisata_after_update->close();
                    }

                } else {
                    $message = "Gagal memperbarui data objek wisata: " . $stmt_update_wisata->error;
                    $message_type = "error";
                    error_log("Error updating wisata: " . $stmt_update_wisata->error);
                }
                $stmt_update_wisata->close();
            } else {
                $message = "Gagal menyiapkan statement update: " . $conn->error;
                $message_type = "error";
                error_log("Error preparing main wisata update statement: " . $conn->error);
            }
        }
    }
}
$conn->close();
?>

<link rel="stylesheet" href="style/pages/form_tambah_edit_wisata.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<section class="form-section">
    <h2>Edit Data Objek Wisata</h2>

    <?php if ($message): ?>
        <div class="alert <?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if ($wisata_data): // Tampilkan form hanya jika data ditemukan ?>
        <form id="editWisataForm" class="wisata-form" method="POST" enctype="multipart/form-data" action="edit_wisata.php?id=<?php echo htmlspecialchars($wisata_data['id_wisata']); ?>">
            <input type="hidden" name="id_wisata" value="<?php echo htmlspecialchars($wisata_data['id_wisata']); ?>">

            <div class="form-group">
                <label for="nama_wisata">Nama Wisata:</label>
                <input type="text" id="nama_wisata" name="nama_wisata" value="<?php echo htmlspecialchars($wisata_data['nama_wisata'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="deskripsi_wisata">Deskripsi Wisata:</label>
                <textarea id="deskripsi_wisata" name="deskripsi_wisata" rows="5" required><?php echo htmlspecialchars($wisata_data['deskripsi_wisata'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="latitude_wisata">Latitude:</label>
                <input type="text" id="latitude_wisata" name="latitude_wisata" value="<?php echo htmlspecialchars($wisata_data['latitude_wisata'] ?? ''); ?>" required readonly>
            </div>

            <div class="form-group">
                <label for="longitude_wisata">Longitude:</label>
                <input type="text" id="longitude_wisata" name="longitude_wisata" value="<?php echo htmlspecialchars($wisata_data['longitude_wisata'] ?? ''); ?>" required readonly>
            </div>

            <div id="mapid" style="height: 300px; width: 100%;"></div>

            <div class="form-group">
                <label for="alamat_wisata">Alamat:</label>
                <textarea id="alamat_wisata" name="alamat_wisata" rows="3" required><?php echo htmlspecialchars($wisata_data['alamat_wisata'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="jam_operasional_wisata">Jam Operasional:</label>
                <input type="text" id="jam_operasional_wisata" name="jam_operasional_wisata" placeholder="Contoh: 08:00 - 17:00" value="<?php echo htmlspecialchars($wisata_data['jam_operasional_wisata'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="harga_tiket_wisata">Harga Tiket:</label>
                <input type="text" id="harga_tiket_wisata" name="harga_tiket_wisata" placeholder="Contoh: Gratis, Rp 10.000, Variatif" value="<?php echo htmlspecialchars($wisata_data['harga_tiket_wisata'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="kontak_wisata">Kontak:</label>
                <input type="text" id="kontak_wisata" name="kontak_wisata" placeholder="Contoh: 0812-3456-7890" value="<?php echo htmlspecialchars($wisata_data['kontak_wisata'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="website_resmi_wisata">Website Resmi (URL):</label>
                <input type="url" id="website_resmi_wisata" name="website_resmi_wisata" placeholder="Contoh: https://www.wisata.com" value="<?php echo htmlspecialchars($wisata_data['website_resmi_wisata'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="id_kategori">Kategori:</label>
                <select id="id_kategori" name="id_kategori" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($kategori_options as $kategori): ?>
                        <option value="<?php echo htmlspecialchars($kategori['id_kategori']); ?>"
                            <?php echo (isset($wisata_data['id_kategori']) && $wisata_data['id_kategori'] == $kategori['id_kategori']) ? 'selected' : ''; ?>>
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
                        <option value="<?php echo htmlspecialchars($kecamatan['id_kecamatan']); ?>"
                            <?php echo (isset($wisata_data['id_kecamatan']) && $wisata_data['id_kecamatan'] == $kecamatan['id_kecamatan']) ? 'selected' : ''; ?>>
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
                                <input type="checkbox" name="fasilitas[]" value="<?php echo htmlspecialchars($fasilitas['id_fasilitas']); ?>"
                                    <?php echo in_array($fasilitas['id_fasilitas'], $selected_fasilitas_ids) ? 'checked' : ''; ?>>
                                <?php echo htmlspecialchars($fasilitas['nama_fasilitas']); ?>
                            </label>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Tidak ada fasilitas tersedia.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="gambar_wisata">Unggah Gambar Baru (Biarkan kosong jika tidak diubah):</label>
                <input type="file" id="gambar_wisata" name="gambar_wisata" accept="image/*">
                <small>Ukuran maksimal 5MB (JPG, JPEG, PNG, GIF)</small>
                <?php if (!empty($wisata_data['url_gambar_wisata'])): ?>
                    <p>Gambar saat ini: <img src="../foto_objek/<?php echo htmlspecialchars($wisata_data['url_gambar_wisata']); ?>" alt="Gambar Wisata" style="max-width: 150px; height: auto; display: block; margin-top: 10px;"></p>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-action add-btn"><i class="fas fa-save"></i> Simpan Perubahan</button>
                <button type="button" class="btn-action back-btn" onclick="backToKelolaWisata()"><i class="fas fa-arrow-left"></i> Kembali</button>
            </div>
        </form>
    <?php else: ?>
        <p>Gagal memuat data wisata. Silakan kembali.</p>
        <div class="form-actions">
            <button type="button" class="btn-action back-btn" onclick="backToKelolaWisata()"><i class="fas fa-arrow-left"></i> Kembali</button>
        </div>
    <?php endif; ?>
</section>

<script>
    // Pastikan Leaflet JS dan CSS sudah dimuat di index_admin.php
    // Jika tidak, Anda perlu menambahkannya di sini atau di tempat lain yang dijamin terload sebelum script ini.
    // Contoh:
    // <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    // <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js">

    var mymap;
    var marker;

    // INI ADALAH SCRIPT LEAFLET YANG AKAN DIJALANKAN SECARA DINAMIS
    // Gunakan setTimeout agar inisialisasi map terjadi setelah div #mapid benar-benar ada dan dirender
    setTimeout(function() {
        if (document.getElementById('mapid')) {
            var initialLat = parseFloat(document.getElementById('latitude_wisata').value);
            var initialLng = parseFloat(document.getElementById('longitude_wisata').value);

            // Inisialisasi peta jika belum ada
            if (mymap instanceof L.Map) {
                mymap.remove(); // Hapus instance map yang mungkin sudah ada
            }
            mymap = L.map('mapid');

            // Set view ke lokasi wisata yang diedit, jika ada dan valid
            if (initialLat !== 0 && initialLng !== 0 && !isNaN(initialLat) && !isNaN(initialLng)) {
                mymap.setView([initialLat, initialLng], 15); // Zoom lebih dekat
            } else {
                mymap.setView([-7.6086, 110.2989], 12); // Default Magelang jika koordinat kosong/invalid
            }

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(mymap);

            // Tambahkan marker awal jika ada koordinat
            if (initialLat !== 0 && initialLng !== 0 && !isNaN(initialLat) && !isNaN(initialLng)) {
                marker = L.marker([initialLat, initialLng]).addTo(mymap);
            }

            function onMapClick(e) {
                if (marker) {
                    mymap.removeLayer(marker);
                }
                marker = L.marker(e.latlng).addTo(mymap);
                document.getElementById('latitude_wisata').value = e.latlng.lat.toFixed(6);
                document.getElementById('longitude_wisata').value = e.latlng.lng.toFixed(6);
            }

            mymap.on('click', onMapClick);

            // Penting untuk Leaflet: Perbarui ukuran peta jika div map awalnya tersembunyi
            mymap.invalidateSize();
        } else {
            console.error("Elemen 'mapid' tidak ditemukan.");
        }
    }, 100); // Memberikan sedikit waktu untuk DOM dirender

    function backToKelolaWisata() {
        if (typeof window.loadContent === 'function') {
            window.loadContent('kelola_wisata.php', 'Kelola Wisata');
            // Pastikan sidebar tetap aktif di "Kelola Wisata"
            if (typeof window.updateSidebarActive === 'function') {
                window.updateSidebarActive('kelola_wisata');
            }
        } else {
            window.location.href = 'kelola_wisata.php'; // Fallback
        }
    }

    // Tangani submit form menggunakan AJAX agar halaman tidak refresh
    document.getElementById('editWisataForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const form = this;
        const formData = new FormData(form);

        const submitButton = form.querySelector('button[type="submit"]');
        const currentButtonHtml = submitButton.innerHTML;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        submitButton.disabled = true;

        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            const dynamicContentArea = document.getElementById('dynamic-content-area');
            if (dynamicContentArea) {
                dynamicContentArea.innerHTML = html;
                executeScriptsInContent(dynamicContentArea); // Fungsi ini akan menjalankan ulang script yang ada di konten baru

                // Cek apakah ada pesan sukses
                const alertDiv = dynamicContentArea.querySelector('.alert.success');
                if (alertDiv) {
                    // Data berhasil diperbarui, arahkan ke halaman kelola_wisata setelah beberapa detik
                    setTimeout(() => {
                        if (typeof window.loadContent === 'function') {
                            window.loadContent('kelola_wisata.php', 'Kelola Wisata');
                            // Pastikan sidebar tetap aktif di "Kelola Wisata"
                            if (typeof window.updateSidebarActive === 'function') {
                                window.updateSidebarActive('kelola_wisata');
                            }
                        } else {
                            // Fallback jika loadContent tidak tersedia
                            window.location.href = 'index_admin.php?page=kelola_wisata';
                        }
                    }, 2000); // Redirect setelah 2 detik (beri waktu user membaca pesan)
                } else {
                    // Jika ada error, tetap di halaman edit_wisata dengan pesan error
                    // Dan pastikan state sidebar/header tetap "Edit Data Wisata"
                    if (typeof window.updateSidebarActive === 'function') {
                        window.updateSidebarActive('edit_wisata'); // Atau sesuai ID/kelas menu edit wisata
                    }
                    // Re-enable tombol submit jika ada error
                    submitButton.innerHTML = currentButtonHtml;
                    submitButton.disabled = false;
                }

            } else {
                console.error("Area konten dinamis tidak ditemukan.");
                alert('Terjadi kesalahan. Area konten tidak ditemukan.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
        })
        .finally(() => {
            // Ini akan dijalankan setelah fetch selesai, baik sukses maupun error
            // Tapi jika ada redirect di .then(), ini mungkin tidak sempat tereksekusi
            // Lebih baik re-enable tombol di blok else dari `if (alertDiv)`
            // atau jika tidak ada redirect.
        });
    });

    // Fungsi `executeScriptsInContent` diperlukan untuk menjalankan kembali script pada konten yang dimuat via AJAX
    function executeScriptsInContent(element) {
        const scripts = element.querySelectorAll('script');
        scripts.forEach(script => {
            const newScript = document.createElement('script');
            Array.from(script.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
            newScript.textContent = script.textContent;
            script.parentNode.replaceChild(newScript, script);
        });
    }

</script>