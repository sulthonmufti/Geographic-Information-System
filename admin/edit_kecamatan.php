<?php
// edit_kecamatan.php

session_start();
date_default_timezone_set('Asia/Jakarta');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once dirname(__DIR__) . '/koneksi.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "<p style='color: red;'>Akses ditolak. Silakan login kembali.</p>";
    exit;
}

$id = $_GET['id'] ?? null;
$data = null;
$alert = '';

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM kecamatan WHERE id_kecamatan = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    if (!$data) {
        echo "<p style='color: red;'>Data tidak ditemukan.</p>";
        exit;
    }
} else {
    echo "<p style='color: red;'>ID tidak valid.</p>";
    exit;
}

// Proses update saat POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_kecamatan']);
    $warna = trim($_POST['warna_geojson_kecamatan']);
    $old_file = $data['url_geojson_kecamatan'];
    $upload_dir = '../data/geojson/';
    $filename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $nama) . '.geojson';
    $relative_path = 'data/geojson/' . $filename;

    if (!empty($nama) && !empty($warna)) {
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (!empty($_FILES['geojson_file']['name'])) {
            $ext = strtolower(pathinfo($_FILES['geojson_file']['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['json', 'geojson'];

            if (in_array($ext, $allowed_exts)) {
                $path = $upload_dir . $filename;
                if (move_uploaded_file($_FILES['geojson_file']['tmp_name'], $path)) {
                    // Update path file GeoJSON baru
                    $stmt = $conn->prepare("UPDATE kecamatan SET nama_kecamatan = ?, url_geojson_kecamatan = ?, warna_geojson_kecamatan = ? WHERE id_kecamatan = ?");
                    $stmt->bind_param("sssi", $nama, $relative_path, $warna, $id);
                } else {
                    $alert = '<div class="alert error">Gagal mengunggah file GeoJSON.</div>';
                }
            } else {
                $alert = '<div class="alert error">Ekstensi file tidak valid. Hanya .json dan .geojson diperbolehkan.</div>';
            }
        } else {
            // Tidak ada file baru, update nama & warna saja
            $stmt = $conn->prepare("UPDATE kecamatan SET nama_kecamatan = ?, warna_geojson_kecamatan = ? WHERE id_kecamatan = ?");
            $stmt->bind_param("ssi", $nama, $warna, $id);
        }

        if (empty($alert)) {
            if ($stmt->execute()) {
                $alert = '<div class="alert success">Data berhasil diperbarui.</div>';
                // Ambil data terbaru setelah update
                $stmt->close();
                $stmt = $conn->prepare("SELECT * FROM kecamatan WHERE id_kecamatan = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $data = $result->fetch_assoc();
                $stmt->close();
            } else {
                $alert = '<div class="alert error">Gagal memperbarui data.</div>';
            }
        }
    } else {
        $alert = '<div class="alert error">Semua field wajib diisi.</div>';
    }
}
?>

<!-- Form Edit Kecamatan -->
<link rel="stylesheet" href="style/pages/form_tambah_edit_wisata.css">

<div class="form-section">
    <h2>Edit Kecamatan</h2>

    <?= $alert ?>

    <form id="form-edit-kecamatan" class="form-kecamatan" enctype="multipart/form-data">
        <div id="notif-edit-kecamatan" style="margin-top: 15px;"></div>
        <div class="form-group">
            <label for="nama_kecamatan">Nama Kecamatan</label>
            <input type="text" id="nama_kecamatan" name="nama_kecamatan" value="<?= htmlspecialchars($data['nama_kecamatan']) ?>" required>
        </div>

        <div class="form-group">
            <label for="geojson_file">File GeoJSON (Kosongkan jika tidak diubah)</label>
            <input type="file" id="geojson_file" name="geojson_file" accept=".json,.geojson">
            <small>File saat ini: <?= basename($data['url_geojson_kecamatan']) ?></small>
        </div>

        <div class="form-group">
            <label for="warna_geojson_kecamatan">Warna GeoJSON</label>
            <input type="color" id="warna_geojson_kecamatan" name="warna_geojson_kecamatan" value="<?= htmlspecialchars($data['warna_geojson_kecamatan']) ?>" required>
        </div>

        <div class="form-actions" style="text-align: right;">
            <button type="submit" class="btn-action add-btn"><i class="fas fa-save"></i> Simpan</button>
            <button type="button" class="btn-action back-btn" onclick="loadContent('kelola_kecamatan.php', 'Kelola Kecamatan')"><i class="fas fa-arrow-left"></i> Kembali</button>
        </div>
    </form>
</div>

<script>
document.getElementById("form-edit-kecamatan").addEventListener("submit", function(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const notif = document.getElementById("notif-edit-kecamatan");

    fetch("edit_kecamatan.php?id=<?= $id ?>", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newForm = doc.querySelector('.form-section');
        const successAlert = doc.querySelector('.alert.success');

        if (newForm) {
            document.querySelector('.form-section').replaceWith(newForm);
        } else {
            notif.innerHTML = "<p style='color: red;'>Gagal memperbarui tampilan. Silakan refresh halaman.</p>";
        }

        // Jika berhasil, tunggu 1.5 detik lalu redirect ke kelola_kecamatan.php
        if (successAlert) {
            setTimeout(() => {
                loadContent('kelola_kecamatan.php', 'Kelola Kecamatan');
            }, 1500);
        }
    })
    .catch(error => {
        notif.innerHTML = "<p style='color: red;'>Terjadi kesalahan jaringan.</p>";
        console.error(error);
    });
});
</script>

