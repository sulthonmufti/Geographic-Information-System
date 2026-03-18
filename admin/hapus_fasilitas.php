<?php
// admin/hapus_fasilitas.php

session_start();
header('Content-Type: application/json'); // Penting: Memberi tahu browser bahwa responsnya adalah JSON

// Periksa apakah admin sudah login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Silakan login kembali.']);
    exit;
}

require_once dirname(__DIR__) . '/koneksi.php'; // Pastikan path ke koneksi database Anda benar

$response = ['success' => false, 'message' => ''];

// Pastikan permintaan adalah POST dan id_fasilitas diterima
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_fasilitas'])) {
    $id_fasilitas = (int)$_POST['id_fasilitas'];

    // Validasi ID fasilitas
    if ($id_fasilitas <= 0) {
        $response['message'] = 'ID fasilitas tidak valid.';
        echo json_encode($response);
        exit;
    }

    // Periksa apakah fasilitas ini terkait dengan objek wisata (ada di objek_wisata_fasilitas)
    // Jika ya, sebaiknya tidak boleh dihapus atau tanyakan konfirmasi ekstra
    // Untuk saat ini, kita akan langsung coba hapus. Jika ada FK constraint, akan gagal.
    // Jika Anda ingin mencegah penghapusan jika ada relasi, tambahkan query SELECT di sini.

    // Mulai transaksi untuk memastikan integritas data
    $conn->begin_transaction();

    try {
        // Cek apakah fasilitas sedang digunakan di tabel objek_wisata_fasilitas
        $sql_check_usage = "SELECT COUNT(*) FROM objek_wisata_fasilitas WHERE id_fasilitas = ?";
        $stmt_check = $conn->prepare($sql_check_usage);
        if (!$stmt_check) {
            throw new Exception("Gagal menyiapkan statement cek penggunaan fasilitas: " . $conn->error);
        }
        $stmt_check->bind_param("i", $id_fasilitas);
        $stmt_check->execute();
        $stmt_check->bind_result($count_usage);
        $stmt_check->fetch();
        $stmt_check->close();

        if ($count_usage > 0) {
            // Jika fasilitas sedang digunakan, batalkan penghapusan
            $conn->rollback();
            $response['message'] = 'Fasilitas ini tidak dapat dihapus karena masih digunakan oleh ' . $count_usage . ' objek wisata.';
            echo json_encode($response);
            exit;
        }

        // Jika tidak digunakan, lanjutkan dengan penghapusan dari tabel fasilitas
        $sql_delete_fasilitas = "DELETE FROM fasilitas WHERE id_fasilitas = ?";
        $stmt_delete_fasilitas = $conn->prepare($sql_delete_fasilitas);

        if (!$stmt_delete_fasilitas) {
            throw new Exception("Gagal menyiapkan statement hapus fasilitas: " . $conn->error);
        }

        $stmt_delete_fasilitas->bind_param("i", $id_fasilitas);
        $stmt_delete_fasilitas->execute();

        if ($stmt_delete_fasilitas->affected_rows > 0) {
            $conn->commit(); // Commit transaksi jika berhasil
            $response['success'] = true;
            $response['message'] = 'Fasilitas berhasil dihapus.';
        } else {
            $conn->rollback(); // Rollback jika tidak ada baris yang terpengaruh
            $response['message'] = 'Fasilitas tidak ditemukan atau sudah dihapus.';
        }
        $stmt_delete_fasilitas->close();

    } catch (Exception $e) {
        $conn->rollback(); // Rollback transaksi jika ada error
        $response['message'] = 'Gagal menghapus fasilitas: ' . $e->getMessage();
        error_log("Error deleting fasilitas (ID: $id_fasilitas): " . $e->getMessage());
    } finally {
        $conn->close();
    }
} else {
    $response['message'] = 'Permintaan tidak valid.';
}

echo json_encode($response);
exit;
?>