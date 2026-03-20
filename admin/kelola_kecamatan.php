<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "<p style='color: red;'>Akses ditolak. Silakan login kembali.</p>";
    exit;
}

require_once dirname(__DIR__) . '/koneksi.php';

// --- Pagination ---
$records_per_page = 10;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

// --- Pencarian ---
$search_query = isset($_GET['search_kecamatan']) ? htmlspecialchars(trim($_GET['search_kecamatan'])) : '';
$where_clause = '';
$params = [];
$param_types = '';

if (!empty($search_query)) {
    $where_clause = " WHERE nama_kecamatan LIKE ?";
    $params[] = '%' . $search_query . '%';
    $param_types = 's';
}

// Hitung total data
$sql_total = "SELECT COUNT(*) AS total FROM kecamatan" . $where_clause;
$total_records = 0;

if (!empty($params)) {
    $stmt_total = $conn->prepare($sql_total);
    $stmt_total->bind_param($param_types, ...$params);
    $stmt_total->execute();
    $result_total = $stmt_total->get_result();
    $row_total = $result_total->fetch_assoc();
    $total_records = $row_total['total'];
    $stmt_total->close();
} else {
    $result_total = $conn->query($sql_total);
    $row_total = $result_total->fetch_assoc();
    $total_records = $row_total['total'];
}

$total_pages = ceil($total_records / $records_per_page);
if ($total_pages > 0 && $current_page > $total_pages) $current_page = $total_pages;
if ($total_pages == 0) $current_page = 1;
$offset = ($current_page - 1) * $records_per_page;
if ($offset < 0) $offset = 0;

// Ambil data kecamatan
$sql_data = "SELECT * FROM kecamatan" . $where_clause . " ORDER BY nama_kecamatan ASC LIMIT ? OFFSET ?";
$params_data = array_merge($params, [$records_per_page, $offset]);
$param_types_data = $param_types . 'ii';

$stmt_data = $conn->prepare($sql_data);
$stmt_data->bind_param($param_types_data, ...$params_data);
$stmt_data->execute();
$result = $stmt_data->get_result();
?>

<link rel="stylesheet" href="style/pages/kelola_kecamatan.css">

<h2>Kelola Kecamatan</h2>
<p>Di sini Anda dapat mengelola data kecamatan untuk pemetaan objek wisata.</p>

<div class="action-bar">
    <button type="button" class="btn-action add-btn" onclick="loadContent('tambah_kecamatan.php', 'Tambah Kecamatan')">
        <i class="fas fa-plus-circle"></i> Tambah Kecamatan
    </button>

    <div class="search-form-container">
        <form id="searchKecamatanForm" class="search-form" method="GET" action="kelola_kecamatan.php">
            <input type="text" name="search_kecamatan" placeholder="Cari berdasarkan Nama Kecamatan..." value="<?= htmlspecialchars($search_query) ?>">
            <button type="submit" class="btn-action search-btn"><i class="fas fa-search"></i> Cari</button>
            <button type="button" class="btn-action reset-btn" onclick="resetSearchKecamatan()"><i class="fas fa-redo"></i> Reset</button>
        </form>
    </div>
</div>

<div class="kelola-kecamatan-table-wrapper">
    <table class="kelola-kecamatan-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama Kecamatan</th>
                <th>URL GeoJSON</th>
                <th>Warna</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php $no = $offset + 1; ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['nama_kecamatan']) ?></td>
                        <td><?= htmlspecialchars($row['url_geojson_kecamatan']) ?></td>
                        <td>
                            <div class="warna-preview" style="background-color: <?= htmlspecialchars($row['warna_geojson_kecamatan']) ?>;"></div>
                            <?= $row['warna_geojson_kecamatan'] ?>
                        </td>
                        <td>
                            <button class="btn-action edit-btn" onclick="loadContent('edit_kecamatan.php?id=<?= $row['id_kecamatan'] ?>', 'Edit Kecamatan')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn-action delete-btn" onclick="hapusKecamatan(<?= $row['id_kecamatan'] ?>, '<?= htmlspecialchars($row['nama_kecamatan'], ENT_QUOTES) ?>')">
                                <i class="fas fa-trash-alt"></i> Hapus
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align: center;">Tidak ada data kecamatan ditemukan.</td></tr>
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

        if ($start_page > 1) {
            echo '<a href="#" class="pagination-link" data-page="1">1</a>';
            if ($start_page > 2) echo '<span>...</span>';
        }

        for ($i = $start_page; $i <= $end_page; $i++):
        ?>
            <a href="#" class="pagination-link <?= ($i == $current_page) ? 'active' : '' ?>" data-page="<?= $i ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($end_page < $total_pages): ?>
            <?php if ($end_page < $total_pages - 1) echo '<span>...</span>'; ?>
            <a href="#" class="pagination-link" data-page="<?= $total_pages ?>"><?= $total_pages ?></a>
        <?php endif; ?>

        <?php if ($current_page < $total_pages): ?>
            <a href="#" class="pagination-link" data-page="<?= $current_page + 1 ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<script>
function hapusKecamatan(id, nama) {
    if (confirm(`Yakin ingin menghapus kecamatan "${nama}"?`)) {
        fetch('hapus_kecamatan.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') {
                    const searchQuery = document.querySelector('input[name="search_kecamatan"]').value;
                    loadKelolaKecamatanContent(<?= $current_page ?>, searchQuery);
                }
            })
            .catch(error => {
                alert('Terjadi kesalahan saat menghapus kecamatan.');
                console.error(error);
            });
    }
}

function loadKelolaKecamatanContent(page, searchQuery) {
    let url = 'kelola_kecamatan.php?page=' + page;
    if (searchQuery) {
        url += '&search_kecamatan=' + encodeURIComponent(searchQuery);
    }
    window.loadContent(url, 'Kelola Kecamatan');
    if (typeof window.updateSidebarActive === 'function') {
        window.updateSidebarActive('kelola_kecamatan');
    }
}

function resetSearchKecamatan() {
    document.querySelector('input[name="search_kecamatan"]').value = '';
    loadKelolaKecamatanContent(1, '');
}

setTimeout(() => {
    const wrapper = document.querySelector('.kelola-kecamatan-table-wrapper').closest('section') || document.body;

    wrapper.addEventListener('click', function(event) {
        if (event.target.matches('.pagination-link')) {
            event.preventDefault();
            const page = event.target.dataset.page;
            const searchQuery = document.querySelector('input[name="search_kecamatan"]').value;
            loadKelolaKecamatanContent(page, searchQuery);
        }
    });

    const searchForm = document.querySelector('#searchKecamatanForm');
    if (searchForm) {
        searchForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const query = this.querySelector('input[name="search_kecamatan"]').value;
            loadKelolaKecamatanContent(1, query);
        });
    }
}, 100);
</script>
