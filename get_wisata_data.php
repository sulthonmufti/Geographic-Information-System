<?php
// Mengatur header HTTP agar browser tahu bahwa respons ini adalah dalam format JSON
header('Content-Type: application/json');

// =========================================================================
// Konfigurasi Database
include 'koneksi.php';


// =========================================================================
// Query SQL untuk Mengambil Data Objek Wisata
// =========================================================================
// Query ini mengambil semua kolom dari tabel 'objek_wisata'.
// Menggunakan LEFT JOIN untuk mengambil 'nama_kategori' dari tabel 'kategori'
// dan 'nama_kecamatan' dari tabel 'kecamatan'.
// LEFT JOIN digunakan agar data wisata tetap muncul meskipun id_kategori atau id_kecamatan-nya null/tidak cocok.
$sql = "SELECT
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
            k.nama_kategori,        -- Ambil nama kategori dari tabel kategori
            kc.nama_kecamatan       -- Ambil nama kecamatan dari tabel kecamatan
        FROM
            objek_wisata ow
        LEFT JOIN
            kategori k ON ow.id_kategori = k.id_kategori
        LEFT JOIN
            kecamatan kc ON ow.id_kecamatan = kc.id_kecamatan";

// Menjalankan query SQL
$result = $conn->query($sql);

// Array untuk menyimpan semua data wisata yang akan diubah menjadi JSON
$wisata_data = [];

// =========================================================================
// Proses Hasil Query
// =========================================================================
// Memeriksa apakah ada baris data yang dikembalikan dari query
if ($result->num_rows > 0) {
    // Loop melalui setiap baris data yang ditemukan
    while($row = $result->fetch_assoc()) {
        // Mengubah nilai latitude_wisata dan longitude_wisata menjadi tipe data float.
        // Ini penting karena data dari database bisa saja berupa string,
        // dan Leaflet memerlukan angka (float) untuk koordinat.
        $row['latitude_wisata'] = (float) $row['latitude_wisata'];
        $row['longitude_wisata'] = (float) $row['longitude_wisata'];

        // Menambahkan properti 'full_gambar_url' ke setiap objek wisata.
        // Ini adalah path lengkap ke gambar di sisi klien (browser).
        // Asumsi: folder 'foto_objek' berada di direktori yang sama dengan 'get_wisata_data.php'.
        $row['full_gambar_url'] = 'foto_objek/' . $row['url_gambar_wisata'];

        // Menambahkan baris data yang sudah diproses ke array $wisata_data
        $wisata_data[] = $row;
    }
}

// =========================================================================
// Tutup Koneksi Database
// Penting untuk membebaskan sumber daya server
// =========================================================================
$conn->close();

// =========================================================================
// Kirim Data dalam Format JSON
// =========================================================================
// Mengubah array $wisata_data menjadi string JSON dan mencetaknya sebagai respons HTTP.
// Ini adalah data yang akan diterima oleh JavaScript di sisi klien.
echo json_encode($wisata_data);
?>