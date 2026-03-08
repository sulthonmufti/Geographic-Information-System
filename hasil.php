<?php
// Koneksi ke database
$host = "localhost";
$user = "root"; // Ganti jika username database Anda berbeda
$password = ""; // Ganti jika ada password
$database = "pemetaan_kab_magelang";

$conn = new mysqli($host, $user, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Query untuk mengambil semua data objek wisata beserta nama kategori dan kecamatan
$sql = "
    SELECT ow.*, k.nama_kategori, kc.nama_kecamatan 
    FROM objek_wisata ow
    JOIN kategori k ON ow.id_kategori = k.id_kategori
    JOIN kecamatan kc ON ow.id_kecamatan = kc.id_kecamatan
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Objek Wisata Kabupaten Magelang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .card {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }
        .card img {
            width: 200px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }
        .card-info {
            max-width: 800px;
        }
        .card-info h2 {
            margin-top: 0;
        }
        .label {
            font-weight: bold;
        }
        
    </style>
</head>
<body>

    <h1>Daftar Objek Wisata di Kabupaten Magelang</h1>

    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="card">
                <img src="foto_objek/<?php echo htmlspecialchars($row['url_gambar_wisata']); ?>" alt="Gambar Wisata">
                <div class="card-info">
                    <h2><?php echo htmlspecialchars($row['nama_wisata']); ?></h2>
                    <p><span class="label">Kategori:</span> <?php echo htmlspecialchars($row['nama_kategori']); ?></p>
                    <p><span class="label">Kecamatan:</span> <?php echo htmlspecialchars($row['nama_kecamatan']); ?></p>
                    <p><span class="label">Deskripsi:</span> <?php echo nl2br(htmlspecialchars($row['deskripsi_wisata'])); ?></p>
                    <p><span class="label">Alamat:</span> <?php echo htmlspecialchars($row['alamat_wisata']); ?></p>
                    <p><span class="label">Jam Operasional:</span> <?php echo htmlspecialchars($row['jam_operasional_wisata']); ?></p>
                    <p><span class="label">Harga Tiket:</span> <?php echo htmlspecialchars($row['harga_tiket_wisata']); ?></p>
                    <p><span class="label">Kontak:</span> <?php echo htmlspecialchars($row['kontak_wisata']); ?></p>
                    <p><span class="label">Website:</span> <a href="<?php echo htmlspecialchars($row['website_resmi_wisata']); ?>" target="_blank"><?php echo htmlspecialchars($row['website_resmi_wisata']); ?></a></p>
                    <p><span class="label">Koordinat:</span> <?php echo $row['latitude_wisata'] . ', ' . $row['longitude_wisata']; ?></p>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Tidak ada data objek wisata yang tersedia.</p>
    <?php endif; ?>

<?php
$conn->close();
?>
</body>
</html>
