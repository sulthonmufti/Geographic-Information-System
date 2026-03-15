<?php
// admin/kelola_wisata.php

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
$search_query = isset($_GET['search_wisata']) ? htmlspecialchars(trim($_GET['search_wisata'])) : '';
$where_clause = '';
$params = [];
$param_types = '';

if (!empty($search_query)) {
    // Gunakan prepared statement untuk keamanan
    $where_clause = " 
    WHERE (
        ow.nama_wisata LIKE ?
        OR ow.jam_operasional_wisata LIKE ?
        OR ow.harga_tiket_wisata LIKE ?
        OR ow.kontak_wisata LIKE ?
        OR ow.website_resmi_wisata LIKE ?
        OR kec.nama_kecamatan LIKE ?
        OR k.nama_kategori LIKE ?
        OR EXISTS (
            SELECT 1 FROM objek_wisata_fasilitas owf
            JOIN fasilitas f ON owf.id_fasilitas = f.id_fasilitas
            WHERE owf.id_wisata = ow.id_wisata AND f.nama_fasilitas LIKE ?
        )
    )
";
    $search_keyword = '%' . $search_query . '%';
    $params = array_fill(0, 8, $search_keyword); // Tambah jadi 8 parameter
    $param_types = str_repeat('s', 8); // semua string
}

// Query untuk mendapatkan total jumlah data dari tabel 'objek_wisata'
// Menyesuaikan total record dengan hasil pencarian jika ada
$sql_total_records = "
    SELECT COUNT(DISTINCT ow.id_wisata) AS total
    FROM objek_wisata ow
    JOIN kategori k ON ow.id_kategori = k.id_kategori
    JOIN kecamatan kec ON ow.id_kecamatan = kec.id_kecamatan
    " . $where_clause;
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
        error_log("Error preparing total records query: " . $conn->error);
    }
} else {
    if ($result_total = $conn->query($sql_total_records)) {
        $row_total = $result_total->fetch_assoc();
        $total_records = $row_total['total'];
        $result_total->free();
    } else {
        error_log("Error fetching total records for kelola_wisata: " . $conn->error);
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

// Inisialisasi array untuk menampung data wisata
$data_wisata = [];

// Ambil data objek wisata dari database dengan JOIN ke kategori dan kecamatan
$sql_wisata = "SELECT DISTINCT
                    ow.id_wisata,
                    ow.nama_wisata,
                    ow.deskripsi_wisata,
                    ow.latitude_wisata,
                    ow.longitude_wisata,
                    ow.alamat_wisata,
                    ow.jam_operasional_wisata,
                    ow.harga_tiket_wisata,
                    ow.kontak_wisata,
                    ow.website_resmi_wisata,
                    ow.url_gambar_wisata,
                    k.nama_kategori,
                    kec.nama_kecamatan
                FROM
                    objek_wisata ow
                JOIN
                    kategori k ON ow.id_kategori = k.id_kategori
                JOIN
                    kecamatan kec ON ow.id_kecamatan = kec.id_kecamatan"
                . $where_clause . "
                ORDER BY ow.nama_wisata ASC
                LIMIT ? OFFSET ?";

// Siapkan prepared statement
$stmt_wisata = $conn->prepare($sql_wisata);

if ($stmt_wisata) {
    // Tambahkan parameter LIMIT dan OFFSET ke params array
    $params_wisata = array_merge($params, [$records_per_page, $offset]);
    // Tambahkan tipe parameter untuk LIMIT dan OFFSET
    $param_types_wisata = $param_types . 'ii'; // 'i' untuk integer (limit, offset)

    $stmt_wisata->bind_param($param_types_wisata, ...$params_wisata);
    $stmt_wisata->execute();
    $result_wisata = $stmt_wisata->get_result();

    while ($row_wisata = $result_wisata->fetch_assoc()) {
        // Ambil fasilitas untuk setiap objek wisata
        $sql_fasilitas = "SELECT f.nama_fasilitas
                            FROM objek_wisata_fasilitas owf
                            JOIN fasilitas f ON owf.id_fasilitas = f.id_fasilitas
                            WHERE owf.id_wisata = ?";
        $stmt_fasilitas = $conn->prepare($sql_fasilitas);
        $fasilitas_list = [];

        if ($stmt_fasilitas) {
            $stmt_fasilitas->bind_param("i", $row_wisata['id_wisata']);
            $stmt_fasilitas->execute();
            $result_fasilitas = $stmt_fasilitas->get_result();
            while ($f = $result_fasilitas->fetch_assoc()) {
                $fasilitas_list[] = $f['nama_fasilitas'];
            }
            $result_fasilitas->free();
            $stmt_fasilitas->close();
        } else {
            error_log("Error preparing fasilitas query for id_wisata " . $row_wisata['id_wisata'] . ": " . $conn->error);
        }
        $row_wisata['fasilitas'] = !empty($fasilitas_list) ? implode(', ', $fasilitas_list) : '-'; // Gabungkan fasilitas menjadi string

        $data_wisata[] = $row_wisata;
    }
    $result_wisata->free();
    $stmt_wisata->close();
} else {
    error_log("Error preparing wisata data query: " . $conn->error);
    echo "<p style='color: red;'>Terjadi kesalahan saat memuat data objek wisata. Silakan coba lagi nanti.</p>";
}

$conn->close(); // Tutup koneksi di bagian akhir setelah semua operasi database selesai
?>

<link rel="stylesheet" href="style/pages/kelola_wisata.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<h2>Kelola Data Objek Wisata</h2>
<p>Di sini Anda dapat menambah, mengubah, atau menghapus data objek wisata.</p>

<div class="action-bar">
    <button id="addWisataBtn" class="btn-action add-btn"><i class="fas fa-plus-circle"></i> Tambah Wisata Baru</button>

    <div class="search-form-container">
        <form id="searchWisataForm" class="search-form" method="GET" action="kelola_wisata.php">
            <input type="text" name="search_wisata" placeholder="Cari berdasarkan Nama Wisata..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit" class="btn-action search-btn"><i class="fas fa-search"></i> Cari</button>
            <button type="button" class="btn-action reset-btn" onclick="resetSearch()"><i class="fas fa-redo"></i> Reset</button>
        </form>
    </div>
</div>

<div class="kelola-wisata-table-wrapper">
    <table class="kelola-wisata-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama Wisata</th>
                <th>Latitude, Longitude</th>
                <th>Alamat</th>
                <th>Jam Operasional</th>
                <th>Harga Tiket</th>
                <th>Kontak</th>
                <th>Website</th>
                <th>Gambar</th>
                <th>Kategori</th>
                <th>Kecamatan</th>
                <th>Fasilitas</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($data_wisata)): ?>
                <?php $no = $offset + 1; // Sesuaikan nomor awal dengan offset ?>
                <?php foreach ($data_wisata as $wisata): ?>
                    <tr>
                        <td data-label="No."><?php echo $no++; ?></td>
                        <td data-label="Nama Wisata"><?php echo htmlspecialchars($wisata['nama_wisata']); ?></td>
                        <td data-label="Lat, Long">
                            <?php echo htmlspecialchars($wisata['latitude_wisata'] . ', ' . $wisata['longitude_wisata']); ?>
                        </td>
                        <td data-label="Alamat"><?php echo htmlspecialchars($wisata['alamat_wisata']); ?></td>
                        <td data-label="Jam Operasional"><?php echo htmlspecialchars($wisata['jam_operasional_wisata']); ?></td>
                        <td data-label="Harga Tiket"><?php echo htmlspecialchars($wisata['harga_tiket_wisata']); ?></td>
                        <td data-label="Kontak"><?php echo htmlspecialchars($wisata['kontak_wisata']); ?></td>
                        <td data-label="Website"><a href="<?php echo htmlspecialchars($wisata['website_resmi_wisata']); ?>" target="_blank"><?php echo htmlspecialchars($wisata['website_resmi_wisata']); ?></a></td>
                        <td data-label="Gambar">
                            <?php if (!empty($wisata['url_gambar_wisata'])): ?>
                                <img src="../foto_objek/<?php echo htmlspecialchars($wisata['url_gambar_wisata']); ?>" alt="Gambar Wisata" class="wisata-gambar-thumbnail">
                            <?php else: ?>
                                Tidak ada gambar
                            <?php endif; ?>
                        </td>
                        <td data-label="Kategori"><?php echo htmlspecialchars($wisata['nama_kategori']); ?></td>
                        <td data-label="Kecamatan"><?php echo htmlspecialchars($wisata['nama_kecamatan']); ?></td>
                        <td data-label="Fasilitas"><?php echo htmlspecialchars($wisata['fasilitas']); ?></td>
                        <td data-label="Aksi">
                            <button type='button' class='btn-action edit-btn' onclick="loadContent('edit_wisata.php?id=<?php echo htmlspecialchars($wisata['id_wisata']); ?>', 'Edit Data Wisata')"><i class='fas fa-edit'></i> Edit</button>
                            <button class="btn-action delete-btn" data-id="<?php echo htmlspecialchars($wisata['id_wisata']); ?>" data-nama="<?php echo htmlspecialchars($wisata['nama_wisata']); ?>"><i class="fas fa-trash-alt"></i> Hapus</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="13" style="text-align: center;">Tidak ada data objek wisata yang ditemukan.</td>
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
    function loadKelolaWisataContent(page, searchQuery) {
        let url = 'kelola_wisata.php?page=' + page;
        if (searchQuery) {
            url += '&search_wisata=' + encodeURIComponent(searchQuery);
        }
        const menuTitle = 'Kelola Wisata';

        if (typeof window.loadContent === 'function') {
            window.loadContent(url, menuTitle);
            // Tambahkan updateSidebarActive agar sidebar tetap sesuai
            if (typeof window.updateSidebarActive === 'function') {
                window.updateSidebarActive('kelola_wisata'); // Sesuaikan dengan id/class menu kelola wisata
            }
        } else {
            console.error("Fungsi window.loadContent tidak ditemukan. Pastikan sudah didefinisikan di index_admin.php.");
            window.location.href = url;
        }
    }

    // Setup listener untuk pagination
    function setupKelolaWisataPaginationAndSearchListener() {
        // Targetkan elemen yang membungkus seluruh konten tabel dan pagination, misalnya section atau div utama
        // Ini penting karena konten dimuat via AJAX, event listener harus dipasang pada elemen statis yang sudah ada
        const kelolaWisataSection = document.querySelector('.kelola-wisata-table-wrapper').closest('section') || document.body;
        if (!kelolaWisataSection) {
            console.warn("Kelola Wisata section not found for listener setup.");
            return;
        }

        // Listener untuk pagination links
        kelolaWisataSection.addEventListener('click', function(event) {
            // Pastikan event.target adalah elemen <a> dengan class 'pagination-link'
            if (event.target.matches('.pagination-link')) {
                event.preventDefault();
                const page = event.target.dataset.page;
                // Ambil nilai pencarian saat ini dari input field
                const currentSearchInput = kelolaWisataSection.querySelector('input[name="search_wisata"]');
                const currentSearchQuery = currentSearchInput ? currentSearchInput.value : '';
                loadKelolaWisataContent(page, currentSearchQuery);
            }
        });

        // Listener untuk form pencarian (submit)
        const searchForm = kelolaWisataSection.querySelector('#searchWisataForm');
        if (searchForm) {
            searchForm.addEventListener('submit', function(event) {
                event.preventDefault(); // Mencegah submit form standar
                const searchQuery = this.querySelector('input[name="search_wisata"]').value;
                loadKelolaWisataContent(1, searchQuery); // Selalu kembali ke halaman 1 saat pencarian baru
            });
        }
    }

    // Fungsi untuk tombol Reset
    function resetSearch() {
        const kelolaWisataSection = document.querySelector('.kelola-wisata-table-wrapper').closest('section') || document.body;
        const searchInput = kelolaWisataSection.querySelector('input[name="search_wisata"]');
        if (searchInput) {
            searchInput.value = ''; // Kosongkan input pencarian
        }
        loadKelolaWisataContent(1, ''); // Muat ulang halaman pertama tanpa parameter pencarian
    }

    // Setup fungsi Edit/Delete
    function setupKelolaWisataActionButtons() {
        const kelolaWisataSection = document.querySelector('.kelola-wisata-table-wrapper').closest('section') || document.body;

        // Event Listener untuk tombol "Tambah Wisata Baru"
        const addWisataBtn = kelolaWisataSection.querySelector('#addWisataBtn');
        if (addWisataBtn) {
            addWisataBtn.addEventListener('click', function() {
                // Panggil window.loadContent untuk memuat tambah_wisata.php
                if (typeof window.loadContent === 'function') {
                    window.loadContent('tambah_wisata.php', 'Tambah Wisata Baru');
                    if (typeof window.updateSidebarActive === 'function') {
                        window.updateSidebarActive('tambah_wisata'); // Sesuaikan dengan id/class menu tambah wisata
                    }
                } else {
                    console.error("Fungsi window.loadContent tidak ditemukan.");
                    window.location.href = 'tambah_wisata.php'; // Fallback
                }
            });
        }

        // Event listener untuk tombol Delete
        // Disarankan menggunakan data-id pada tombol delete langsung
        kelolaWisataSection.querySelectorAll('.btn-action.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const idWisataToDelete = this.dataset.id; // Mengambil ID dari data-id pada button
                const namaWisataToDelete = this.dataset.nama; // Mengambil nama dari data-nama pada button

                if (!idWisataToDelete) {
                    alert('ID Wisata tidak ditemukan untuk dihapus.');
                    return;
                }

                // Ubah pesan konfirmasi
                if (!confirm('Apakah Anda yakin ingin menghapus data wisata "' + namaWisataToDelete + '"? Tindakan ini tidak dapat dibatalkan.')) return;

                // Implementasi AJAX request untuk delete
                fetch('hapus_wisata.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ id_wisata: idWisataToDelete })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Data wisata berhasil dihapus.');
                        const currentSearchQuery = kelolaWisataSection.querySelector('input[name="search_wisata"]').value;
                        loadKelolaWisataContent(<?php echo $current_page; ?>, currentSearchQuery);
                    } else {
                        alert('Gagal menghapus data wisata: ' + (data.message || 'Terjadi kesalahan tidak diketahui.'));
                    }
                })
                .catch(error => {
                    console.error('Error saat menghapus data wisata:', error);
                    alert('Terjadi kesalahan saat menghapus data wisata.');
                });
            });
        });
    }

    // Panggil semua fungsi setup saat konten ini dimuat
    // Gunakan setTimeout agar DOM sepenuhnya dirender oleh browser setelah AJAX
    setTimeout(() => {
        setupKelolaWisataPaginationAndSearchListener();
        setupKelolaWisataActionButtons();
    }, 100); // Penundaan kecil untuk memastikan elemen DOM ada
</script>