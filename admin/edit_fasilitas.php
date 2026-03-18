<?php
// admin/edit_kategori.php

session_start();
date_default_timezone_set('Asia/Jakarta');

// Cek login admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "<div class='alert error'>Akses ditolak. Silakan login kembali.</div>";
    exit;
}

require_once dirname(__DIR__) . '/koneksi.php';

// Handle AJAX POST untuk menyimpan perubahan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_kategori']) && isset($_POST['nama_kategori'])) {
    $id_kategori = intval($_POST['id_kategori']);
    $nama_kategori = trim($_POST['nama_kategori']);

    if ($nama_kategori === '') {
        echo json_encode(['status' => 'error', 'message' => 'Nama kategori tidak boleh kosong.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE kategori SET nama_kategori = ? WHERE id_kategori = ?");
    if ($stmt) {
        $stmt->bind_param("si", $nama_kategori, $id_kategori);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Data kategori berhasil diperbarui.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate data.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Kesalahan saat mempersiapkan query.']);
    }
    exit;
}

// Ambil data kategori berdasarkan ID
$id_kategori = isset($_GET['id']) ? intval($_GET['id']) : 0;
$data_kategori = null;

if ($id_kategori > 0) {
    $stmt = $conn->prepare("SELECT * FROM kategori WHERE id_kategori = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id_kategori);
        $stmt->execute();
        $result = $stmt->get_result();
        $data_kategori = $result->fetch_assoc();
        $stmt->close();
    }
}

if (!$data_kategori) {
    echo "<div class='alert error'>Data kategori tidak ditemukan.</div>";
    exit;
}
?>

<!-- Gunakan CSS yang konsisten -->
<link rel="stylesheet" href="style/pages/form_tambah_edit_wisata.css">

<h2>Edit Kategori</h2>
<p>Ubah informasi kategori berikut:</p>

<div id="response-message-kategori"></div>
<section class="form-section">
    <h2>Edit Kategori</h2>
    <form id="form-edit-kategori" class="form-tambah-fasilitas">
        <input type="hidden" name="id_kategori" value="<?php echo htmlspecialchars($data_kategori['id_kategori']); ?>">

        <div class="form-group">
            <label for="nama_kategori">Nama Kategori</label>
            <input type="text" id="nama_kategori" name="nama_kategori" required
                value="<?php echo htmlspecialchars($data_kategori['nama_kategori']); ?>">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-action add-btn"><i class="fas fa-save"></i> Simpan Perubahan</button>
            <button type="button" class="btn-action reset-btn" onclick="loadContent('kelola_kategori.php', 'Kelola Kategori')">
                <i class="fas fa-arrow-left"></i> Kembali
            </button>
        </div>
    </form>
</section>

<script>
document.getElementById("form-edit-kategori").addEventListener("submit", function(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const msgContainer = document.getElementById("response-message-kategori");

    fetch("edit_kategori.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        msgContainer.innerHTML = `<div class="alert ${data.status === 'success' ? 'success' : 'error'}">${data.message}</div>`;

        if (data.status === 'success') {
            setTimeout(() => {
                loadContent('kelola_kategori.php', 'Kelola Kategori');
            }, 1500);
        }
    })
    .catch(error => {
        msgContainer.innerHTML = `<div class="alert error">Terjadi kesalahan saat mengirim data.</div>`;
        console.error("Error:", error);
    });
});
</script>
