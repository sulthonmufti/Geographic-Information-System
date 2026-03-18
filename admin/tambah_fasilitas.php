<?php
// admin/tambah_fasilitas.php

session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "<p style='color: red;'>Akses ditolak. Silakan login kembali.</p>";
    exit;
}

require_once dirname(__DIR__) . '/koneksi.php';

// Tangani AJAX request POST (submit form)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['ajax'])) {
    header('Content-Type: application/json');

    $nama_fasilitas = isset($_POST['nama_fasilitas']) ? trim($_POST['nama_fasilitas']) : '';
    if (empty($nama_fasilitas)) {
        echo json_encode(['status' => 'error', 'message' => 'Nama fasilitas tidak boleh kosong.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO fasilitas (nama_fasilitas) VALUES (?)");
    if ($stmt) {
        $stmt->bind_param("s", $nama_fasilitas);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Data fasilitas berhasil ditambahkan.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan fasilitas. ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Kesalahan statement: ' . $conn->error]);
    }
    exit;
}
?>

<!-- Form Tambah Fasilitas -->
<link rel="stylesheet" href="style/pages/form_tambah_edit_wisata.css">
<h2>Tambah Fasilitas Baru</h2>
<p>Isi form berikut untuk menambahkan data fasilitas baru.</p>

<div id="response-message-fasilitas"></div>

<div class="form-section">
    <h2>Tambah Fasilitas Baru</h2>

    <div id="response-message-fasilitas"></div>

    <form id="form-tambah-fasilitas" class="wisata-form">
        <div class="form-group">
            <input type="text" id="nama_fasilitas" name="nama_fasilitas" required placeholder="Masukkan nama fasilitas">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-action add-btn"><i class="fas fa-save"></i> Simpan</button>
            <button type="button" class="btn-action back-btn" onclick="loadContent('kelola_fasilitas.php', 'Kelola Fasilitas')"><i class="fas fa-arrow-left"></i> Kembali</button>
        </div>
    </form>
</div>

<script>
    // Menangani submit form tanpa reload
    document.getElementById("form-tambah-fasilitas").addEventListener("submit", function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append("ajax", "1"); // Tambahkan penanda AJAX

        fetch("tambah_fasilitas.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            const msgContainer = document.getElementById("response-message-fasilitas");
            msgContainer.innerHTML = `<div class="alert ${data.status === 'success' ? 'success' : 'error'}">${data.message}</div>`;

            if (data.status === "success") {
                // Tunggu sebentar lalu kembali ke halaman kelola fasilitas
                setTimeout(() => {
                    loadContent("kelola_fasilitas.php", "Kelola Fasilitas");
                }, 1500);
            }
        })
        .catch(err => {
            document.getElementById("response-message-fasilitas").innerHTML = `<div class="error-message">Terjadi kesalahan: ${err.message}</div>`;
        });
    });

    // Aktifkan menu sidebar
    if (typeof window.updateSidebarActive === 'function') {
        updateSidebarActive('kelola_fasilitas');
    }
</script>
