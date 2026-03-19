<?php
session_start();
require_once dirname(__DIR__) . '/koneksi.php';

// Pastikan ID kategori dikirim melalui URL
if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID kategori tidak ditemukan.']);
    exit;
}

$id_kategori = intval($_GET['id']);

// Lakukan query DELETE
$query = "DELETE FROM kategori WHERE id_kategori = $id_kategori";
if (mysqli_query($conn, $query)) {
    echo json_encode(['status' => 'success', 'message' => 'Kategori berhasil dihapus.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus kategori.']);
}
