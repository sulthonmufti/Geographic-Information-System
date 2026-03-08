<?php
// =========================================================================
// Konfigurasi Database
// Ubah nilai-nilai ini sesuai dengan pengaturan database Anda
// =========================================================================
$servername = "localhost"; // Alamat host database (biasanya 'localhost' untuk pengembangan lokal)
$username = "root";        // Username untuk mengakses database Anda
$password = "";            // Password untuk username database Anda (kosong jika tidak ada password)
$dbname = "pemetaan_kab_magelang"; // Nama database yang akan dihubungkan

// =========================================================================
// Buat Koneksi ke Database
// =========================================================================
$conn = new mysqli($servername, $username, $password, $dbname);

// Periksa apakah koneksi database berhasil
if ($conn->connect_error) {
    // Jika koneksi gagal, buat respons JSON dengan pesan error
    echo json_encode(['error' => 'Koneksi database gagal: ' . $conn->connect_error]);
    exit(); // Hentikan eksekusi script PHP agar tidak ada output lain yang mengganggu JSON
}
?>