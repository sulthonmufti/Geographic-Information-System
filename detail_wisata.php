<?php
// Pastikan ada ID wisata yang dikirim melalui URL
if (isset($_GET['id'])) {
    $id_wisata = $_GET['id'];

    // Include file koneksi database Anda
    include 'koneksi.php';

    // Query untuk mengambil detail objek wisata berdasarkan ID
    $sql_wisata = "SELECT
                ow.nama_wisata,
                ow.deskripsi_wisata,
                ow.alamat_wisata,
                ow.jam_operasional_wisata,
                ow.harga_tiket_wisata,
                ow.kontak_wisata,
                ow.website_resmi_wisata,
                ow.url_gambar_wisata,
                ow.latitude_wisata, -- Tambahkan latitude dan longitude untuk tombol rute
                ow.longitude_wisata, -- Tambahkan latitude dan longitude untuk tombol rute
                k.nama_kategori,
                kc.nama_kecamatan
            FROM
                objek_wisata ow
            LEFT JOIN
                kategori k ON ow.id_kategori = k.id_kategori
            LEFT JOIN
                kecamatan kc ON ow.id_kecamatan = kc.id_kecamatan
            WHERE
                ow.id_wisata = ?"; 

    // Siapkan statement untuk objek wisata
    $stmt_wisata = $conn->prepare($sql_wisata);
    $stmt_wisata->bind_param("i", $id_wisata);
    $stmt_wisata->execute();
    $result_wisata = $stmt_wisata->get_result();

    $wisata_detail = null;
    if ($result_wisata->num_rows > 0) {
        $wisata_detail = $result_wisata->fetch_assoc();
        $wisata_detail['full_gambar_url'] = 'foto_objek/' . $wisata_detail['url_gambar_wisata'];
    }
    $stmt_wisata->close();

    // Query untuk mengambil fasilitas yang terkait dengan objek wisata ini
    $sql_fasilitas = "SELECT
                        f.nama_fasilitas
                    FROM
                        fasilitas f
                    JOIN
                        objek_wisata_fasilitas owf ON f.id_fasilitas = owf.id_fasilitas
                    WHERE
                        owf.id_wisata = ?";

    // Siapkan statement untuk fasilitas
    $stmt_fasilitas = $conn->prepare($sql_fasilitas);
    $stmt_fasilitas->bind_param("i", $id_wisata);
    $stmt_fasilitas->execute();
    $result_fasilitas = $stmt_fasilitas->get_result();

    $fasilitas_list = [];
    if ($result_fasilitas->num_rows > 0) {
        while ($row_fasilitas = $result_fasilitas->fetch_assoc()) {
            $fasilitas_list[] = $row_fasilitas['nama_fasilitas'];
        }
    }
    $stmt_fasilitas->close();

    $conn->close();

} else {
    // Jika tidak ada ID yang diberikan, arahkan kembali ke halaman utama atau tampilkan pesan error
    header("Location: halaman_utama.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Objek Wisata: <?php echo htmlspecialchars($wisata_detail['nama_wisata'] ?? 'Tidak Ditemukan'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a57f5fcdf1.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style/untuk_detail.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <a href="index.php" class="logo-container">
                <img src="gambar/logo_magelang.png" alt="Logo Disparpora" class="logo">
                <div class="logo-text">
                    <p>Disparpora</p>
                    <p>Kabupaten Magelang</p>
                </div>
            </a>
            <nav class="main-nav">
                <ul>
                    <li><a href="halaman_utama.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'halaman_utama.php') ? 'active' : ''; ?>">Home</a></li>
                    <li><a href="galeri.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'galeri.php' || basename($_SERVER['PHP_SELF']) == 'detail_wisata.php') ? 'active' : ''; ?>">Galeri</a>
                    <li><a href="tentang.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'tentang.php') ? 'active' : ''; ?>">Tentang</a></li>
                    <li><a href="kontak.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'kontak.php') ? 'active' : ''; ?>">Kontak</a></li>
                </ul>
                <a href="login.php" class="login-button">
                    <i class="fas fa-user"></i> Login
                </a>
            </nav>
            <button class="hamburger-menu">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <div class="mobile-nav-overlay">
        <nav class="mobile-nav">
            <ul>
                <li><a href="halaman_utama.php">Home</a></li>
                <li><a href="galeri.php">Galeri</a></li>
                <li><a href="tentang.php">Tentang</a></li>
                <li><a href="kontak.php">Kontak</a></li>
            </ul>
            <a href="login.php" class="mobile-login-button">
                <i class="fas fa-user"></i> Login
            </a>
        </nav>
    </div>


    <main>
        <?php if ($wisata_detail): ?>
            <div class="detail-content-wrapper">
                <div class="main-info-section">
                    <div class="wisata-image-container">
                        <img src="<?php echo htmlspecialchars($wisata_detail['full_gambar_url']); ?>" alt="<?php echo htmlspecialchars($wisata_detail['nama_wisata']); ?>" class="wisata-main-image">
                        <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo htmlspecialchars($wisata_detail['latitude_wisata']); ?>,<?php echo htmlspecialchars($wisata_detail['longitude_wisata']); ?>" target="_blank" class="btn-lihat-rute">
                            <i class="fas fa-route"></i> Lihat rute
                        </a>
                    </div>
                    <div class="wisata-text-content">
                        <h1 class="wisata-title"><?php echo htmlspecialchars($wisata_detail['nama_wisata']); ?></h1>
                        <p class="wisata-kategori-kecamatan"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($wisata_detail['nama_kecamatan'] ?? 'N/A'); ?>, Kab. Magelang</p>
                        <p class="wisata-full-description"><?php echo nl2br(htmlspecialchars($wisata_detail['deskripsi_wisata'] ?? 'Tidak ada deskripsi tersedia.')); ?></p>
                    </div>
                </div>

                <div class="additional-info-section">
                    <h2 class="section-title-hidden">Informasi Lengkap</h2>
                    <table class="wisata-info-table">
                        <tr>
                            <td>Nama objek wisata</td>
                            <td><?php echo htmlspecialchars($wisata_detail['nama_wisata']); ?></td>
                        </tr>
                        <tr>
                            <td>Kategori</td>
                            <td><?php echo htmlspecialchars($wisata_detail['nama_kategori'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td>Kecamatan</td>
                            <td><?php echo htmlspecialchars($wisata_detail['nama_kecamatan'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td><?php echo htmlspecialchars($wisata_detail['alamat_wisata'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td>Jam operasional</td>
                            <td><?php echo htmlspecialchars($wisata_detail['jam_operasional_wisata'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td>Harga tiket</td>
                            <td><?php echo htmlspecialchars($wisata_detail['harga_tiket_wisata'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td>Kontak</td>
                            <td><?php echo htmlspecialchars($wisata_detail['kontak_wisata'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td>Website</td>
                            <td>
                                <?php
                                // Ambil data website dari array $wisata_detail
                                $website_url = $wisata_detail['website_resmi_wisata'] ?? '';

                                // Trim whitespace dan cek apakah URL tidak kosong dan bukan hanya "-"
                                if (!empty(trim($website_url)) && trim($website_url) !== '-') {
                                ?>
                                    <a href="<?php echo htmlspecialchars($website_url); ?>" target="_blank" class="btn-buka-website"><i class="fa-solid fa-link"></i>Buka website</a>
                                <?php
                                } else {
                                    // Jika data kosong atau "-", tampilkan teks "N/A" dan tambahkan button untuk alert
                                    // Kita akan menggunakan sebuah tombol/link yang ketika diklik akan memicu alert
                                    // Ini lebih interaktif daripada langsung alert saat halaman dimuat
                                ?>
                                    N/A <button type="button" class="btn-info-website" onclick="showAlertWebsite('<?php echo htmlspecialchars($wisata_detail['nama_wisata']); ?>')">?</button>
                                <?php
                                }
                                ?>
                            </td>
                        </tr>

                            <td>Fasilitas</td>
                            <td>
                                <div class="fasilitas-grid">
                                    <?php if (!empty($fasilitas_list)): ?>
                                        <?php foreach ($fasilitas_list as $fasilitas): ?>
                                            <div class="fasilitas-item">
                                                <i class="fas fa-check"></i> <?php echo htmlspecialchars($fasilitas); ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        Tidak ada fasilitas
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <p class="error-message">Objek wisata tidak ditemukan atau terjadi kesalahan.</p>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <div>© 2025 – Pemetaan Objek Wisata Kabupaten Magelang. All rights reserved.</div>
        <div>Muhammad Sulthon Mufti (2100018213)</div>
    </footer>

    <script src="script.js"></script>

    <script>
        function showAlertWebsite(namaWisata) {
            alert('Mohon maaf, untuk saat ini data website objek wisata ' + namaWisata + ' tidak tersedia.');
        }
    </script>

</body>
</html>