<?php
header('Content-Type: application/json');
include 'koneksi.php';

$sql = "SELECT id_kategori, nama_kategori, icon_kategori, warna_icon_kategori FROM kategori";
$result = $conn->query($sql);

$kategori_data = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $kategori_data[] = $row;
    }
}
$conn->close();
echo json_encode($kategori_data);
?>