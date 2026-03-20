<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "<p style='color: red;'>Akses ditolak. Silakan login kembali.</p>";
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => 'Terjadi kesalahan.'];

    $nama = trim($_POST['nama_kecamatan'] ?? '');
    $warna = trim($_POST['warna_geojson_kecamatan'] ?? '');
    $file = $_FILES['geojson_file'] ?? null;

    if (!$nama || !$warna || !$file) {
        $response['message'] = "Harap lengkapi semua data.";
    } else {
        $upload_dir = '../data/geojson/';
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['json', 'geojson'];
        $safe_filename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $nama);
        $filename = $safe_filename . '.geojson';
        $file_path = $upload_dir . $filename;

        if (in_array($ext, $allowed_exts)) {
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                $relative_path = 'data/geojson/' . $filename;
                $stmt = $conn->prepare("INSERT INTO kecamatan (nama_kecamatan, url_geojson_kecamatan, warna_geojson_kecamatan) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $nama, $relative_path, $warna);

                if ($stmt->execute()) {
                    $response = ['status' => 'success'];
                } else {
                    $response['message'] = "Gagal menyimpan ke database.";
                }

                $stmt->close();
            } else {
                $response['message'] = "Gagal mengunggah file.";
            }
        } else {
            $response['message'] = "Ekstensi file tidak valid. Harus .geojson";
        }
    }

    echo json_encode($response);
    exit;
}
?>

<!-- HTML Form -->
<div class="form-section">
    <h2>Tambah Kecamatan Baru</h2>
    <form id="formTambahKecamatan" class="form-kecamatan" enctype="multipart/form-data">
        <div class="form-group">
            <label for="nama_kecamatan">Nama Kecamatan</label>
            <input type="text" id="nama_kecamatan" name="nama_kecamatan" required>
        </div>

        <div class="form-group">
            <label for="geojson_file">File GeoJSON</label>
            <input type="file" id="geojson_file" name="geojson_file" accept=".json,.geojson" required>
        </div>

        <div class="form-group">
            <label for="warna_geojson_kecamatan">Warna GeoJSON</label>
            <input type="color" id="warna_geojson_kecamatan" name="warna_geojson_kecamatan" value="#000000" required>
        </div>

        <div class="form-actions" style="text-align: right;">
            <button type="submit" class="btn-action add-btn"><i class="fas fa-save"></i> Simpan</button>
            <button type="button" class="btn-action back-btn" onclick="loadContent('kelola_kecamatan.php', 'Kelola Kecamatan')"><i class="fas fa-arrow-left"></i> Kembali</button>
        </div>
    </form>
</div>

<script>
document.getElementById('formTambahKecamatan').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('tambah_kecamatan.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Kecamatan berhasil ditambahkan!');
            loadContent('kelola_kecamatan.php', 'Kelola Kecamatan');
        } else {
            alert('Gagal menambahkan kecamatan: ' + data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan saat mengirim data.');
    });
});
</script>
