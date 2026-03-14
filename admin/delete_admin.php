<?php
// delete_admin.php

// Pastikan session_start() ada di baris paling awal
session_start();

// Cek apakah admin sudah login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: ../login.php");
    exit;
}

// Cek jika ada ID admin yang ingin dihapus
$data = json_decode(file_get_contents('php://input'), true);
$id_admin = $data['id_admin'] ?? null;

if ($id_admin) {
    // Menghubungkan ke database
    require_once dirname(__DIR__) . '/koneksi.php';

    // Hapus admin dari database
    $query = "DELETE FROM admin WHERE id_admin = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_admin);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
}
?>
