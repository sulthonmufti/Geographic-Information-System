<?php
// kelola_admin.php

session_start();

// Cek apakah admin sudah login dan memiliki peran 'Master'
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'Master') {
    echo "<p>Akses ditolak. Anda tidak memiliki izin untuk melihat halaman ini.</p>";
    exit;
}

// Sertakan file koneksi database
require_once dirname(__DIR__) . '/koneksi.php';

// Inisialisasi variabel pencarian
$search_query = "";

// Cek apakah ada input pencarian dari form
if (isset($_GET['search_admin']) && !empty($_GET['search_admin'])) {
    $search_query = $_GET['search_admin'];
}

// Persiapkan query dasar
$query = "SELECT id_admin, username_admin, nama_lengkap_admin, email_admin, role FROM admin";

// Tambahkan kondisi WHERE jika ada input pencarian
if (!empty($search_query)) {
    // Gunakan prepared statement untuk mencegah SQL Injection
    $query .= " WHERE username_admin LIKE ? OR nama_lengkap_admin LIKE ? OR email_admin LIKE ? OR role LIKE ?";
}

// Persiapkan dan eksekusi statement
$stmt = $conn->prepare($query);

// Binding parameter jika ada pencarian
if (!empty($search_query)) {
    $search_param = "%" . $search_query . "%";
    $stmt->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
}

// Eksekusi query
$stmt->execute();
$result = $stmt->get_result();
?>

<section class="kelola-admin-section">
    <h2>Kelola Data Admin</h2>
    <p>Di sini Anda dapat melihat, mengubah, atau menghapus data admin.</p>

    <div class="action-bar">
        <button id="addAdminBtn" class="btn-action add-btn"><i class="fas fa-plus-circle"></i> Tambah Admin Baru</button>

        <div class="search-form-container">
            <form id="searchAdminForm" class="search-form" method="GET" action="kelola_admin.php">
                <input type="text" name="search_admin" placeholder="Cari admin..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" class="btn-action search-btn"><i class="fas fa-search"></i> Cari</button>
                <button type="button" class="btn-action reset-btn" onclick="resetSearchAdmin()"><i class="fas fa-redo"></i> Reset</button>
            </form>
        </div>
    </div>

    <div class="kelola-admin-table-wrapper">
        <table class="kelola-admin-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Username</th>
                    <th>Nama Lengkap</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    $no = 1;
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <tr>
                            <td data-label="No."><?php echo $no++; ?></td>
                            <td data-label="Username">@<?php echo htmlspecialchars($row['username_admin']); ?></td>
                            <td data-label="Nama Lengkap"><?php echo htmlspecialchars($row['nama_lengkap_admin']); ?></td>
                            <td data-label="Email"><?php echo htmlspecialchars($row['email_admin']); ?></td>
                            <td data-label="Role">
                                <span class="role-badge <?php echo ($row['role'] === 'Master') ? 'master-role' : 'regular-role'; ?>">
                                    <?php echo htmlspecialchars($row['role']); ?>
                                </span>
                            </td>
                            <td data-label="Aksi">
                                <button class="btn-action edit-admin-btn" data-id="<?php echo htmlspecialchars($row['id_admin']); ?>"><i class='fas fa-edit'></i> Edit</button>
                                <button class="btn-action delete-admin-btn" data-id="<?php echo htmlspecialchars($row['id_admin']); ?>" data-nama="<?php echo htmlspecialchars($row['username_admin']); ?>"><i class="fas fa-trash-alt"></i> Hapus</button>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">Tidak ada data admin ditemukan.</td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</section>