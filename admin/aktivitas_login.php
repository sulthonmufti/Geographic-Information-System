<?php
// admin/aktivitas_login.php

// Pastikan koneksi.php ada di direktori induk dari direktori admin
require_once dirname(__DIR__) . '/koneksi.php';

// --- Pagination Setup ---
$records_per_page = 10; // Jumlah data per halaman

// Dapatkan nomor halaman saat ini dari URL (GET parameter), default ke halaman 1
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1; // Pastikan halaman tidak kurang dari 1
}

// Debugging PHP (akan terlihat di source code HTML yang dimuat via AJAX)
echo ""; // Tidak ditampilkan apa-apa

// Hitung offset untuk query SQL
$offset = ($current_page - 1) * $records_per_page;

// Query untuk mendapatkan total jumlah data (tanpa limit)
$sql_total_records = "SELECT COUNT(*) AS total FROM aktivitas_login";
$total_records = 0;
if ($result_total = $conn->query($sql_total_records)) {
    $row_total = $result_total->fetch_assoc();
    $total_records = $row_total['total'];
    $result_total->free();
} else {
    error_log("Error fetching total records for login activity: " . $conn->error);
}

// Hitung total halaman
$total_pages = ceil($total_records / $records_per_page);

// Debugging PHP
echo ""; // Tidak ditampilkan
echo ""; // Tidak ditampilkan
echo ""; // Tidak ditampilkan

// Pastikan current_page tidak melebihi total_pages jika total_pages > 0
if ($total_pages > 0 && $current_page > $total_pages) {
    $current_page = $total_pages;
    // Sesuaikan offset juga jika halaman berubah
    $offset = ($current_page - 1) * $records_per_page;
}

// Ambil data aktivitas login dari database dengan LIMIT dan OFFSET
$aktivitas_login = [];
$sql_aktivitas = "SELECT waktu_percobaan, username_attempt, password_attempt, ip_address_login, user_agent_login, status_login
                   FROM aktivitas_login
                   ORDER BY waktu_percobaan DESC
                   LIMIT $records_per_page OFFSET $offset";

if ($result_aktivitas = $conn->query($sql_aktivitas)) {
    while ($row = $result_aktivitas->fetch_assoc()) {
        $row['formatted_waktu_percobaan'] = date('d M Y H:i', strtotime($row['waktu_percobaan']));
        $aktivitas_login[] = $row;
    }
    $result_aktivitas->free();
} else {
    echo "<p style='color: red;'>Error mengambil data aktivitas login: " . $conn->error . "</p>";
    error_log("Error fetching login activity for aktivitas_login.php: " . $conn->error);
}

$conn->close();
?>

<link rel="stylesheet" href="style/pages/aktivitas_login.css">

<section class="activity-section">
    <h3>Aktivitas Percobaan Login</h3>
    <div class="data-table-container-aktivitas">
        <table class="data-table-aktivitas">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Waktu Percobaan</th>
                    <th>Username Dicoba</th>
                    <th>Password Dicoba</th>
                    <th>IP Address</th>
                    <th>User Agent</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($aktivitas_login)): ?>
                    <?php $no = $offset + 1; ?>
                    <?php foreach ($aktivitas_login as $log): ?>
                        <tr>
                            <td data-label="No."><?php echo $no++; ?></td>
                            <td data-label="Waktu Percobaan"><?php echo htmlspecialchars($log['formatted_waktu_percobaan']); ?></td>
                            <td data-label="Username Dicoba"><?php echo htmlspecialchars($log['username_attempt']); ?></td>
                            <td data-label="Password Dicoba"><?php echo htmlspecialchars($log['password_attempt']); ?></td>
                            <td data-label="IP Address"><?php echo htmlspecialchars($log['ip_address_login']); ?></td>
                            <td data-label="User Agent"><?php echo htmlspecialchars($log['user_agent_login']); ?></td>
                            <td data-label="Status">
                                <span class="status-badge <?php echo htmlspecialchars($log['status_login']); ?>">
                                    <?php echo htmlspecialchars(ucfirst($log['status_login'])); ?>
                                </span>
                            </td>
                            <td data-label="Aksi">
                                <button class="delete-btn-icon"
                                        data-waktu="<?php echo $log['waktu_percobaan']; ?>"
                                        title="Hapus log ini">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">Belum ada aktivitas percobaan login yang tercatat.</td>
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
            // Logika untuk menampilkan nomor halaman
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
</section>

<script>
    // Pastikan skrip ini dieksekusi setiap kali aktivitas_login.php dimuat ulang melalui AJAX
    function setupPaginationListener() {
        const activitySection = document.querySelector('.activity-section');
        if (!activitySection) {
            console.warn("Activity section not found when setting up pagination listener.");
            return;
        }

        console.log("Pagination listener aktif di halaman:", <?php echo $current_page; ?>); // Debug log

        activitySection.addEventListener('click', function(event) {
            if (event.target.classList.contains('pagination-link')) {
                event.preventDefault();
                const page = event.target.dataset.page;

                console.log("Pagination link clicked! Target page:", page); // Debug log

                const newUrl = 'aktivitas_login.php?page=' + page;
                const menuTitle = 'Percobaan Login'; 

                // Panggil fungsi loadContent dari window (global scope)
                if (typeof window.loadContent === 'function') {
                    window.loadContent(newUrl, menuTitle);
                } else {
                    console.error("Fungsi window.loadContent tidak ditemukan. Silakan pastikan sudah didefinisikan di index_admin.php.");
                    window.location.href = newUrl; // Fallback jika loadContent tidak ada
                }
            }
        });
    }

    function setupDeleteButtons() {
        document.querySelectorAll('.delete-btn-icon').forEach(button => {
            button.addEventListener('click', function () {
                const waktuPercobaan = this.dataset.waktu;

                if (!confirm('Apakah Anda yakin ingin menghapus log ini?')) return;

                fetch('hapus_aktivitas_login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ waktu_percobaan: waktuPercobaan })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Log berhasil dihapus.');
                        // Reload konten halaman saat ini
                        const currentUrl = 'aktivitas_login.php?page=<?php echo $current_page; ?>';
                        window.loadContent(currentUrl, 'Percobaan Login');
                    } else {
                        alert('Gagal menghapus log: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error saat menghapus log:', error);
                    alert('Terjadi kesalahan saat menghapus log.');
                });
            });
        });
    }

    // Panggil saat konten ini dimuat
    setupPaginationListener();
    setupDeleteButtons();
</script>
