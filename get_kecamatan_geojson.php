<?php
include "koneksi.php";
header('Content-Type: application/json');

$sql = "SELECT nama_kecamatan, url_geojson_kecamatan, warna_geojson_kecamatan FROM kecamatan";
$result = mysqli_query($conn, $sql);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);
?>