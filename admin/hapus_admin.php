<?php
// hapus_admin.php

session_start();

// Cek apakah admin sudah login dan memiliki peran 'Master'
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'Master') {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Anda tidak memiliki izin untuk melakukan tindakan ini.']);
    exit;
}

// Sertakan file koneksi database
require_once dirname(__DIR__) . '/koneksi.php';

// Pastikan permintaan adalah metode POST dan ada ID admin yang dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_admin'])) {
    $id_admin_to_delete = $_POST['id_admin'];

    // Cek apakah admin yang akan dihapus adalah admin yang sedang login
    if ($id_admin_to_delete == $_SESSION['id_admin']) {
        echo json_encode(['status' => 'error', 'message' => 'Anda tidak bisa menghapus akun Anda sendiri.']);
        exit;
    }

    // Persiapkan query untuk menghapus data admin
    $query = "DELETE FROM admin WHERE id_admin = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_admin_to_delete);

    // Eksekusi query
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Admin berhasil dihapus.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus admin. Silakan coba lagi.']);
    }
    
    $stmt->close();
    exit;
} else {
    // Jika tidak ada ID atau metode request salah
    echo json_encode(['status' => 'error', 'message' => 'Permintaan tidak valid.']);
    exit;
}
?>