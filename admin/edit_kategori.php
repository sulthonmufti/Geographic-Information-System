<?php
require_once dirname(__DIR__) . '/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // === PROSES SAAT FORM DIKIRIM VIA AJAX ===
    $id_kategori = isset($_POST['id_kategori']) ? (int)$_POST['id_kategori'] : 0;
    $nama_kategori = mysqli_real_escape_string($conn, $_POST['nama_kategori'] ?? '');
    $icon_kategori = mysqli_real_escape_string($conn, $_POST['icon_kategori'] ?? '');
    $warna_icon_kategori = mysqli_real_escape_string($conn, $_POST['warna_icon_kategori'] ?? '');

    if (!$id_kategori || !$nama_kategori || !$icon_kategori) {
        echo "Data tidak lengkap.";
        exit;
    }

    $update_sql = "UPDATE kategori SET 
        nama_kategori = '$nama_kategori',
        icon_kategori = '$icon_kategori',
        warna_icon_kategori = '$warna_icon_kategori'
        WHERE id_kategori = $id_kategori";

    if (mysqli_query($conn, $update_sql)) {
        echo "success";
    } else {
        echo "Gagal menyimpan data: " . mysqli_error($conn);
    }
    exit;
}

// === AMBIL DATA UNTUK FORM (GET) ===
if (!isset($_GET['id'])) {
    echo "<p>ID Kategori tidak ditemukan.</p>";
    exit;
}

$id = (int)$_GET['id'];
$sql = "SELECT * FROM kategori WHERE id_kategori = $id";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) === 0) {
    echo "<p>Kategori tidak ditemukan.</p>";
    exit;
}

$data = mysqli_fetch_assoc($result);
?>

<link rel="stylesheet" href="style/pages/tambah_edit_kategori.css">

<div class="form-section-kategori">
    <h2>Edit Kategori</h2>
    <div id="notif-kategori" class="alert" style="display: none;"></div>

    <form id="formEditKategori" class="form-kategori">
        <input type="hidden" name="id_kategori" value="<?= $data['id_kategori'] ?>">

        <div class="form-group">
            <label for="nama_kategori">Nama Kategori</label>
            <input type="text" id="nama_kategori" name="nama_kategori" required value="<?= htmlspecialchars($data['nama_kategori']) ?>">
        </div>

        <div class="form-group">
            <label for="icon_kategori">Icon Kategori</label>
            <input type="text" id="icon_kategori" name="icon_kategori" required value="<?= htmlspecialchars($data['icon_kategori']) ?>">
            <small>Gunakan class Font Awesome, contoh: <code>fas fa-tree</code></small>
        </div>

        <div class="form-group">
            <label for="warna_icon_kategori">Warna Icon</label>
            <input type="color" id="warna_icon_kategori" name="warna_icon_kategori" value="<?= htmlspecialchars($data['warna_icon_kategori']) ?>">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-action add-btn"><i class="fas fa-save"></i> Simpan Perubahan</button>
            <button type="button" class="btn-action back-btn" onclick="loadContent('kelola_kategori.php', 'Kelola Kategori')">
                <i class="fas fa-arrow-left"></i> Kembali
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('formEditKategori').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const notifBox = document.getElementById('notif-kategori');

    fetch('edit_kategori.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(res => {
        if (res.trim() === 'success') {
            notifBox.innerHTML = '<div class="alert success"><i class="fas fa-check-circle"></i> Perubahan berhasil disimpan.</div>';
            notifBox.style.display = 'block';

            // Delay 2.5 detik, baru kembali ke halaman utama
            setTimeout(() => {
                loadContent('kelola_kategori.php', 'Kelola Kategori');
            }, 2500);
        } else {
            notifBox.innerHTML = '<div class="alert error"><i class="fas fa-times-circle"></i> Gagal menyimpan perubahan: ' + res + '</div>';
            notifBox.style.display = 'block';
        }
    })
    .catch(error => {
        notifBox.innerHTML = '<div class="notif error"><i class="fas fa-exclamation-triangle"></i> Terjadi kesalahan: ' + error + '</div>';
        notifBox.style.display = 'block';
    });
});
</script>
