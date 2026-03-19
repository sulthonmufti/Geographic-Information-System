<?php
// tambah_kategori.php
require_once dirname(__DIR__) . '/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => 'Terjadi kesalahan'];

    $nama = trim($_POST['nama_kategori'] ?? '');
    $icon = trim($_POST['icon_kategori'] ?? '');
    $warna = trim($_POST['warna_icon_kategori'] ?? '');

    if ($nama && $icon && $warna) {
        $stmt = $conn->prepare("INSERT INTO kategori (nama_kategori, icon_kategori, warna_icon_kategori) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nama, $icon, $warna);

        if ($stmt->execute()) {
            $response = ['status' => 'success'];
        } else {
            $response['message'] = "Gagal menyimpan ke database.";
        }

        $stmt->close();
    } else {
        $response['message'] = "Data tidak lengkap.";
    }

    echo json_encode($response);
    exit;
}
?>

<!-- Form Tambah Kategori -->
<div class="form-section">
    <h2>Tambah Kategori Baru</h2>

    <form id="formTambahKategori" class="form-kategori">
        <div class="form-group">
            <label for="nama_kategori">Nama Kategori</label>
            <input type="text" id="nama_kategori" name="nama_kategori" required>
        </div>

        <div class="form-group">
            <label for="icon_kategori">Icon Kategori (contoh: fas fa-tree)</label>
            <input type="text" id="icon_kategori" name="icon_kategori" required>
        </div>

        <div class="form-group">
            <label for="warna_icon_kategori">Warna Icon</label>
            <input type="color" id="warna_icon_kategori" name="warna_icon_kategori" value="#000000" required>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-action add-btn"><i class="fas fa-save"></i> Simpan</button>
            <button type="button" class="btn-action back-btn" onclick="loadContent('kelola_kategori.php', 'Kelola Kategori')"><i class="fas fa-arrow-left"></i> Kembali</button>
        </div>
    </form>
</div>

<script>
    document.getElementById('formTambahKategori').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('tambah_kategori.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Kategori berhasil ditambahkan!');
                loadContent('kelola_kategori.php', 'Kelola Kategori');
            } else {
                alert('Gagal menambahkan kategori: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan saat mengirim data.');
        });
    });
</script>
