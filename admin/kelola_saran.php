<?php
require_once dirname(__DIR__) . '/koneksi.php';

// --- Pagination Setup ---
$records_per_page = 10;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $records_per_page;

// Ambil query pencarian
$search_query = isset($_GET['search_saran']) ? trim($_GET['search_saran']) : '';
$where_sql = '';

if (!empty($search_query)) {
    $safe_query = $conn->real_escape_string($search_query);
    $where_sql = "WHERE nama_pengirim_saran LIKE '%$safe_query%' OR status_saran LIKE '%$safe_query%'";
}

// Hitung total data
$sql_total = "SELECT COUNT(*) AS total FROM kritik_saran $where_sql";
$result_total = $conn->query($sql_total);
$total_records = ($row = $result_total->fetch_assoc()) ? $row['total'] : 0;
$total_pages = ceil($total_records / $records_per_page);
if ($total_pages > 0 && $current_page > $total_pages) {
    $current_page = $total_pages;
    $offset = ($current_page - 1) * $records_per_page;
}

// Ambil data kritik_saran
$sql = "SELECT * FROM kritik_saran $where_sql ORDER BY tanggal_kirim_saran DESC LIMIT $records_per_page OFFSET $offset";
$result = $conn->query($sql);

$data_saran = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data_saran[] = $row;
    }
}
$conn->close();
?>

<link rel="stylesheet" href="style/pages/kritik_saran.css">

<h2>Kelola Kritik dan Saran</h2>
<p>Di sini Anda dapat melihat dan mengelola kritik serta saran yang masuk dari pengunjung.</p>

<div class="action-bar">
    <div class="search-form-container-saran">
        <form id="searchSaranForm" class="search-form-saran" method="GET" action="kelola_saran.php">
            <input type="text" name="search_saran" placeholder="Cari berdasarkan pengirim atau status..." value="<?= htmlspecialchars($search_query ?? '') ?>">
            <button type="submit" class="btn-action search-btn"><i class="fas fa-search"></i> Cari</button>
            <button type="button" class="btn-action reset-btn" onclick="resetSearchSaran()"><i class="fas fa-redo"></i> Reset</button>
        </form>
    </div>
</div>

<section class="activity-section">
    <div class="data-table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama Pengirim</th>
                    <th>Pesan</th>
                    <th>Email</th>
                    <th>Telepon</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data_saran)): ?>
                    <?php $no = $offset + 1; ?>
                    <?php foreach ($data_saran as $saran): ?>
                        <tr>
                            <td data-label="No."><?= $no++; ?></td>
                            <td data-label="Nama Pengirim"><?= htmlspecialchars($saran['nama_pengirim_saran']); ?></td>
                            <td data-label="Pesan"><?= nl2br(htmlspecialchars($saran['pesan_saran'])); ?></td>
                            <td data-label="Email"><?= htmlspecialchars($saran['email_pengirim_saran']); ?></td>
                            <td data-label="Telepon"><?= htmlspecialchars($saran['no_tlp_pengirim_saran']); ?></td>
                            <td data-label="Tanggal"><?= date('d M Y H:i', strtotime($saran['tanggal_kirim_saran'])); ?></td>
                            <td data-label="Status">
                                <button class="status-button status-<?= $saran['status_saran']; ?>" 
                                    onclick="ubahStatus(<?= $saran['id_saran']; ?>, '<?= $saran['status_saran']; ?>')">
                                    <?= htmlspecialchars(ucfirst($saran['status_saran'])); ?>
                                </button>
                            </td>
                            <td data-label="Aksi">
                                <button class="delete-btn-icon" data-id="<?= $saran['id_saran']; ?>" title="Hapus data ini">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" style="text-align:center;">Belum ada data kritik atau saran.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($current_page > 1): ?>
                <a href="#" class="pagination-link" data-page="<?= $current_page - 1 ?>">&laquo; Previous</a>
            <?php endif; ?>
            <?php
            $start_page = max(1, $current_page - 2);
            $end_page = min($total_pages, $current_page + 2);
            if ($start_page > 1) echo '<a href="#" class="pagination-link" data-page="1">1</a><span>...</span>';
            for ($i = $start_page; $i <= $end_page; $i++) {
                $active = $i == $current_page ? 'active' : '';
                echo "<a href='#' class='pagination-link $active' data-page='$i'>$i</a>";
            }
            if ($end_page < $total_pages) echo '<span>...</span><a href="#" class="pagination-link" data-page="' . $total_pages . '">' . $total_pages . '</a>';
            ?>
            <?php if ($current_page < $total_pages): ?>
                <a href="#" class="pagination-link" data-page="<?= $current_page + 1 ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<script>
function setupSaranListeners() {
    document.querySelectorAll('.pagination-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const page = this.dataset.page;
            window.loadContent('kelola_saran.php?page=' + page, 'Kritik & Saran');
        });
    });

    document.querySelectorAll('.delete-btn-icon').forEach(button => {
        button.addEventListener('click', function () {
            const idSaran = this.dataset.id;
            if (!confirm('Yakin ingin menghapus saran ini?')) return;
            
            fetch('hapus_saran.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({ id_saran: idSaran })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Saran berhasil dihapus.');
                    window.loadContent('kelola_saran.php?page=<?= $current_page ?>', 'Kritik & Saran');
                } else {
                    alert('Gagal menghapus: ' + data.message);
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Terjadi kesalahan.');
            });
        });
    });
}

function ubahStatus(id, currentStatus) {
    const statusBaru = currentStatus === 'Baru' ? 'Dibaca' : 'Baru';
    fetch('ubah_status_saran.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ id_saran: id, status_saran: statusBaru })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.loadContent('kelola_saran.php?page=<?= $current_page ?>', 'Kritik & Saran');
        } else {
            alert('Gagal mengubah status.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan.');
    });
}

document.getElementById('searchSaranForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const searchQuery = this.querySelector('input[name="search_saran"]').value.trim();
    const url = 'kelola_saran.php?search_saran=' + encodeURIComponent(searchQuery);
    window.loadContent(url, 'Kritik & Saran');
});

function resetSearchSaran() {
    const form = document.getElementById('searchSaranForm');
    form.querySelector('input[name="search_saran"]').value = '';
    window.loadContent('kelola_saran.php', 'Kritik & Saran');
}

setupSaranListeners();
</script>
