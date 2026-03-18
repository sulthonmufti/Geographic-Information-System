<?php
// admin/kelola_fasilitas.php

// Pastikan session_start() ada di baris paling awal!
session_start();

// Atur timezone ke Asia/Jakarta (WIB)
date_default_timezone_set('Asia/Jakarta');

// Periksa apakah admin sudah login, jika tidak, arahkan ke halaman login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "<p style='color: red;'>Akses ditolak. Silakan login kembali.</p>";
    exit;
}

require_once dirname(__DIR__) . '/koneksi.php'; // Pastikan path ini benar

// --- Pagination Setup ---
$records_per_page = 10; // Jumlah data per halaman. Sesuaikan jika Anda ingin lebih banyak/sedikit

// Dapatkan nomor halaman saat ini dari URL (GET parameter), default ke halaman 1
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1; // Pastikan halaman tidak kurang dari 1
}

// --- Pencarian Setup ---
$search_query = isset($_GET['search_fasilitas']) ? htmlspecialchars(trim($_GET['search_fasilitas'])) : '';
$where_clause = '';
$params = [];
$param_types = '';

if (!empty($search_query)) {
    // Gunakan prepared statement untuk keamanan
    $where_clause = " WHERE nama_fasilitas LIKE ?";
    $params[] = '%' . $search_query . '%';
    $param_types = 's';
}

// Query untuk mendapatkan total jumlah data dari tabel 'fasilitas'
// Menyesuaikan total record dengan hasil pencarian jika ada
$sql_total_records = "SELECT COUNT(*) AS total FROM fasilitas" . $where_clause;
$total_records = 0;

if (!empty($params)) {
    $stmt_total = $conn->prepare($sql_total_records);
    if ($stmt_total) {
        $stmt_total->bind_param($param_types, ...$params);
        $stmt_total->execute();
        $result_total = $stmt_total->get_result();
        $row_total = $result_total->fetch_assoc();
        $total_records = $row_total['total'];
        $stmt_total->close();
    } else {
        error_log("Error preparing total records query for fasilitas: " . $conn->error);
    }
} else {
    if ($result_total = $conn->query($sql_total_records)) {
        $row_total = $result_total->fetch_assoc();
        $total_records = $row_total['total'];
        $result_total->free();
    } else {
        error_log("Error fetching total records for kelola_fasilitas: " . $conn->error);
    }
}

// Hitung total halaman
$total_pages = ceil($total_records / $records_per_page);

// Pastikan current_page tidak melebihi total_pages jika total_pages > 0
if ($total_pages > 0 && $current_page > $total_pages) {
    $current_page = $total_pages;
} elseif ($total_pages == 0) { // Jika tidak ada data sama sekali, set current_page ke 1
    $current_page = 1;
}

// Hitung offset untuk query SQL (setelah memastikan current_page valid)
$offset = ($current_page - 1) * $records_per_page;
if ($offset < 0) {
    $offset = 0; // Pastikan offset tidak negatif
}

// Inisialisasi array untuk menampung data fasilitas
$data_fasilitas = [];

// Ambil data fasilitas dari database
$sql_fasilitas = "SELECT id_fasilitas, nama_fasilitas
                  FROM fasilitas"
                  . $where_clause . "
                  ORDER BY nama_fasilitas ASC
                  LIMIT ? OFFSET ?";

// Siapkan prepared statement
$stmt_fasilitas = $conn->prepare($sql_fasilitas);

if ($stmt_fasilitas) {
    // Tambahkan parameter LIMIT dan OFFSET ke params array
    $params_fasilitas = array_merge($params, [$records_per_page, $offset]);
    // Tambahkan tipe parameter untuk LIMIT dan OFFSET
    $param_types_fasilitas = $param_types . 'ii'; // 'i' untuk integer (limit, offset)

    $stmt_fasilitas->bind_param($param_types_fasilitas, ...$params_fasilitas);
    $stmt_fasilitas->execute();
    $result_fasilitas = $stmt_fasilitas->get_result();

    while ($row_fasilitas = $result_fasilitas->fetch_assoc()) {
        $data_fasilitas[] = $row_fasilitas;
    }
    $result_fasilitas->free();
    $stmt_fasilitas->close();
} else {
    error_log("Error preparing fasilitas data query: " . $conn->error);
    echo "<p style='color: red;'>Terjadi kesalahan saat memuat data fasilitas. Silakan coba lagi nanti.</p>";
}

$conn->close(); // Tutup koneksi di bagian akhir setelah semua operasi database selesai
?>

<link rel="stylesheet" href="style/pages/kelola_fasilitas.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<h2>Kelola Data Fasilitas</h2>
<p>Di sini Anda dapat menambah, mengubah, atau menghapus data fasilitas.</p>

<div class="action-bar">
    <button id="addFasilitasBtn" class="btn-action add-btn"><i class="fas fa-plus-circle"></i> Tambah Fasilitas Baru</button>

    <div class="search-form-container">
        <form id="searchFasilitasForm" class="search-form" method="GET" action="kelola_fasilitas.php">
            <input type="text" name="search_fasilitas" placeholder="Cari berdasarkan Nama Fasilitas..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit" class="btn-action search-btn"><i class="fas fa-search"></i> Cari</button>
            <button type="button" class="btn-action reset-btn" onclick="resetSearchFasilitas()"><i class="fas fa-redo"></i> Reset</button>
        </form>
    </div>
</div>

<div class="kelola-fasilitas-table-wrapper">
    <table class="kelola-fasilitas-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama Fasilitas</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($data_fasilitas)): ?>
                <?php $no = $offset + 1; // Sesuaikan nomor awal dengan offset ?>
                <?php foreach ($data_fasilitas as $fasilitas): ?>
                    <tr>
                        <td data-label="No."><?php echo $no++; ?></td>
                        <td data-label="Nama Fasilitas"><?php echo htmlspecialchars($fasilitas['nama_fasilitas']); ?></td>
                        <td data-label="Aksi">
                            <button type='button' class='btn-action edit-btn' onclick="loadContent('edit_fasilitas.php?id=<?php echo htmlspecialchars($fasilitas['id_fasilitas']); ?>', 'Edit Data Fasilitas')"><i class='fas fa-edit'></i> Edit</button>
                            <button class="btn-action delete-btn" data-id="<?php echo htmlspecialchars($fasilitas['id_fasilitas']); ?>" data-nama="<?php echo htmlspecialchars($fasilitas['nama_fasilitas']); ?>"><i class="fas fa-trash-alt"></i> Hapus</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" style="text-align: center;">Tidak ada data fasilitas yang ditemukan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($total_pages > 1): // Tampilkan pagination jika ada lebih dari 1 halaman ?>
    <div class="pagination">
        <?php if ($current_page > 1): // Tombol Previous ?>
            <a href="#" class="pagination-link" data-page="<?php echo $current_page - 1; ?>">&laquo; Previous</a>
        <?php endif; ?>

        <?php
        $start_page = max(1, $current_page - 2);
        $end_page = min($total_pages, $current_page + 2);

        if ($start_page > 1) {
            echo '<a href="#" class="pagination-link" data-page="1">1</a>';
            if ($start_page > 2) {
                echo '<span>...</span>';
            }
        }

        for ($i = $start_page; $i <= $end_page; $i++):
        ?>
            <a href="#" class="pagination-link <?php echo ($i == $current_page) ? 'active' : ''; ?>" data-page="<?php echo $i; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>

        <?php
        if ($end_page < $total_pages) {
            if ($end_page < $total_pages - 1) {
                echo '<span>...</span>';
            }
            echo '<a href="#" class="pagination-link" data-page="' . $total_pages . '">' . $total_pages . '</a>';
        }
        ?>

        <?php if ($current_page < $total_pages): // Tombol Next ?>
            <a href="#" class="pagination-link" data-page="<?php echo $current_page + 1; ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<script>
    // Fungsi untuk memuat konten dengan parameter pencarian dan pagination
    function loadKelolaFasilitasContent(page, searchQuery) {
        let url = 'kelola_fasilitas.php?page=' + page;
        if (searchQuery) {
            url += '&search_fasilitas=' + encodeURIComponent(searchQuery);
        }
        const menuTitle = 'Kelola Fasilitas';

        if (typeof window.loadContent === 'function') {
            window.loadContent(url, menuTitle);
            // Tambahkan updateSidebarActive agar sidebar tetap sesuai
            if (typeof window.updateSidebarActive === 'function') {
                window.updateSidebarActive('kelola_fasilitas'); // Sesuaikan dengan id/class menu kelola fasilitas
            }
        } else {
            console.error("Fungsi window.loadContent tidak ditemukan. Pastikan sudah didefinisikan di index_admin.php.");
            window.location.href = url;
        }
    }

    // Setup listener untuk pagination dan pencarian
    function setupKelolaFasilitasPaginationAndSearchListener() {
        // Targetkan elemen yang membungkus seluruh konten tabel dan pagination
        const kelolaFasilitasSection = document.querySelector('.kelola-fasilitas-table-wrapper').closest('section') || document.body;
        if (!kelolaFasilitasSection) {
            console.warn("Kelola Fasilitas section not found for listener setup.");
            return;
        }

        // Listener untuk pagination links
        kelolaFasilitasSection.addEventListener('click', function(event) {
            if (event.target.matches('.pagination-link')) {
                event.preventDefault();
                const page = event.target.dataset.page;
                const currentSearchInput = kelolaFasilitasSection.querySelector('input[name="search_fasilitas"]');
                const currentSearchQuery = currentSearchInput ? currentSearchInput.value : '';
                loadKelolaFasilitasContent(page, currentSearchQuery);
            }
        });

        // Listener untuk form pencarian (submit)
        const searchForm = kelolaFasilitasSection.querySelector('#searchFasilitasForm');
        if (searchForm) {
            searchForm.addEventListener('submit', function(event) {
                event.preventDefault();
                const searchQuery = this.querySelector('input[name="search_fasilitas"]').value;
                loadKelolaFasilitasContent(1, searchQuery); // Selalu kembali ke halaman 1 saat pencarian baru
            });
        }
    }

    // Fungsi untuk tombol Reset pencarian
    function resetSearchFasilitas() {
        const kelolaFasilitasSection = document.querySelector('.kelola-fasilitas-table-wrapper').closest('section') || document.body;
        const searchInput = kelolaFasilitasSection.querySelector('input[name="search_fasilitas"]');
        if (searchInput) {
            searchInput.value = ''; // Kosongkan input pencarian
        }
        loadKelolaFasilitasContent(1, ''); // Muat ulang halaman pertama tanpa parameter pencarian
    }

    // Setup fungsi Add/Edit/Delete
    function setupKelolaFasilitasActionButtons() {
        const kelolaFasilitasSection = document.querySelector('.kelola-fasilitas-table-wrapper').closest('section') || document.body;

        // Event Listener untuk tombol "Tambah Fasilitas Baru"
        const addFasilitasBtn = kelolaFasilitasSection.querySelector('#addFasilitasBtn');
        if (addFasilitasBtn) {
            addFasilitasBtn.addEventListener('click', function() {
                if (typeof window.loadContent === 'function') {
                    window.loadContent('tambah_fasilitas.php', 'Tambah Fasilitas Baru');
                    if (typeof window.updateSidebarActive === 'function') {
                        window.updateSidebarActive('tambah_fasilitas'); // Sesuaikan dengan id/class menu tambah fasilitas
                    }
                } else {
                    console.error("Fungsi window.loadContent tidak ditemukan.");
                    window.location.href = 'tambah_fasilitas.php'; // Fallback
                }
            });
        }

        // Event listener untuk tombol Delete
        kelolaFasilitasSection.querySelectorAll('.btn-action.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const idFasilitasToDelete = this.dataset.id;
                const namaFasilitasToDelete = this.dataset.nama;

                if (!idFasilitasToDelete) {
                    alert('ID Fasilitas tidak ditemukan untuk dihapus.');
                    return;
                }

                if (!confirm('Apakah Anda yakin ingin menghapus data fasilitas "' + namaFasilitasToDelete + '"? Tindakan ini tidak dapat dibatalkan.')) return;

                fetch('hapus_fasilitas.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ id_fasilitas: idFasilitasToDelete })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Data fasilitas berhasil dihapus.');
                        const currentSearchQuery = kelolaFasilitasSection.querySelector('input[name="search_fasilitas"]').value;
                        loadKelolaFasilitasContent(<?php echo $current_page; ?>, currentSearchQuery);
                    } else {
                        alert('Gagal menghapus data fasilitas: ' + (data.message || 'Terjadi kesalahan tidak diketahui.'));
                    }
                })
                .catch(error => {
                    console.error('Error saat menghapus data fasilitas:', error);
                    alert('Terjadi kesalahan saat menghapus data fasilitas.');
                });
            });
        });
    }

    // Panggil semua fungsi setup saat konten ini dimuat
    setTimeout(() => {
        setupKelolaFasilitasPaginationAndSearchListener();
        setupKelolaFasilitasActionButtons();
    }, 100);
</script>