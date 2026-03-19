<?php
require_once dirname(__DIR__) . '/koneksi.php';

// Ambil data kategori dari database
$sql = "SELECT * FROM kategori ORDER BY nama_kategori ASC";
$result = mysqli_query($conn, $sql);
?>

<link rel="stylesheet" href="style/pages/kelola_kategori.css">

<h2>Kelola Kategori Objek Wisata</h2>
<p>Di sini Anda dapat menambah, mengubah, atau menghapus data kategori.</p>

<div class="action-bar">
    <button type="button" class="btn-action add-btn" onclick="loadContent('tambah_kategori.php', 'Tambah Kategori Baru')">
        <i class="fas fa-plus-circle"></i> Tambah Kategori Baru
    </button>
</div>

<div class="kelola-kategori-table-wrapper">
    <table class="kelola-kategori-table">
        <thead>
            <tr>
                <th>Nama Kategori</th>
                <th>Icon</th>
                <th>Warna</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td data-label="Nama Kategori"><?= htmlspecialchars($row['nama_kategori']) ?></td>
                        <td data-label="Icon"><i class="<?= htmlspecialchars($row['icon_kategori']) ?>"></i> <?= $row['icon_kategori'] ?></td>
                        <td data-label="Warna">
                            <div class="warna-preview" style="background-color: <?= htmlspecialchars($row['warna_icon_kategori']) ?>;"></div>
                            <?= $row['warna_icon_kategori'] ?>
                        </td>
                        <td data-label="Aksi">
                            <button class="btn-action edit-btn" onclick="loadContent('edit_kategori.php?id=<?= $row['id_kategori'] ?>', 'Edit Kategori')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn-action delete-btn" onclick="hapusKategori(<?= $row['id_kategori'] ?>, '<?= htmlspecialchars($row['nama_kategori'], ENT_QUOTES) ?>')">
                                <i class="fas fa-trash-alt"></i> Hapus
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" style="text-align: center;">Belum ada kategori.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    function hapusKategori(id, namaKategori) {
        const konfirmasi = confirm(`Yakin ingin menghapus kategori "${namaKategori}"?`);
        if (!konfirmasi) return;

        fetch('hapus_kategori.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') {
                    loadContent('kelola_kategori.php', 'Kelola Kategori');
                }
            })
        .catch(error => {
            alert('Terjadi kesalahan saat menghapus.');
            console.error(error);
        });
    }
</script>
