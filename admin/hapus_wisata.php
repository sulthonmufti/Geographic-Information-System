<?php
// admin/hapus_wisata.php

session_start();
header('Content-Type: application/json'); // Penting: Memberi tahu browser bahwa responsnya adalah JSON

// Periksa apakah admin sudah login, jika tidak, kirim respons error
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Silakan login kembali.']);
    exit;
}

require_once dirname(__DIR__) . '/koneksi.php'; // Pastikan path ke koneksi database Anda benar

$response = ['success' => false, 'message' => ''];

// Pastikan permintaan adalah POST dan id_wisata diterima
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_wisata'])) {
    $id_wisata = (int)$_POST['id_wisata'];

    // Validasi ID wisata (opsional tapi disarankan)
    if ($id_wisata <= 0) {
        $response['message'] = 'ID wisata tidak valid.';
        echo json_encode($response);
        exit;
    }

    // Mulai transaksi untuk memastikan integritas data
    $conn->begin_transaction();

    try {
        // 1. Ambil nama file gambar yang terkait dengan objek wisata yang akan dihapus
        $sql_get_image = "SELECT url_gambar_wisata FROM objek_wisata WHERE id_wisata = ?";
        $stmt_get_image = $conn->prepare($sql_get_image);
        if (!$stmt_get_image) {
            throw new Exception("Gagal menyiapkan statement ambil gambar: " . $conn->error);
        }
        $stmt_get_image->bind_param("i", $id_wisata);
        $stmt_get_image->execute();
        $result_get_image = $stmt_get_image->get_result();
        $image_row = $result_get_image->fetch_assoc();
        $stmt_get_image->close();
        $image_to_delete = $image_row ? $image_row['url_gambar_wisata'] : null;

        // 2. Hapus entri dari tabel 'objek_wisata_fasilitas' terlebih dahulu (karena ada FOREIGN KEY)
        $sql_delete_fasilitas = "DELETE FROM objek_wisata_fasilitas WHERE id_wisata = ?";
        $stmt_delete_fasilitas = $conn->prepare($sql_delete_fasilitas);
        if (!$stmt_delete_fasilitas) {
            throw new Exception("Gagal menyiapkan statement hapus fasilitas: " . $conn->error);
        }
        $stmt_delete_fasilitas->bind_param("i", $id_wisata);
        $stmt_delete_fasilitas->execute();
        $stmt_delete_fasilitas->close();

        // 3. Hapus entri dari tabel 'objek_wisata'
        $sql_delete_wisata = "DELETE FROM objek_wisata WHERE id_wisata = ?";
        $stmt_delete_wisata = $conn->prepare($sql_delete_wisata);
        if (!$stmt_delete_wisata) {
            throw new Exception("Gagal menyiapkan statement hapus wisata: " . $conn->error);
        }
        $stmt_delete_wisata->bind_param("i", $id_wisata);
        $stmt_delete_wisata->execute();

        if ($stmt_delete_wisata->affected_rows > 0) {
            // Jika penghapusan dari DB berhasil, coba hapus file gambar dari server
            if ($image_to_delete && file_exists(dirname(__DIR__) . "/foto_objek/" . $image_to_delete)) {
                unlink(dirname(__DIR__) . "/foto_objek/" . $image_to_delete);
                // Jika ingin log jika gagal hapus file, bisa ditambahkan di sini
            }

            $conn->commit(); // Commit transaksi jika semua berhasil
            $response['success'] = true;
            $response['message'] = 'Data objek wisata berhasil dihapus.';
        } else {
            $conn->rollback(); // Rollback jika tidak ada baris yang terpengaruh (ID tidak ditemukan)
            $response['message'] = 'Data wisata tidak ditemukan atau sudah dihapus.';
        }
        $stmt_delete_wisata->close();

    } catch (Exception $e) {
        $conn->rollback(); // Rollback transaksi jika ada error
        $response['message'] = 'Gagal menghapus data wisata: ' . $e->getMessage();
        error_log("Error deleting wisata (ID: $id_wisata): " . $e->getMessage());
    } finally {
        $conn->close();
    }
} else {
    $response['message'] = 'Permintaan tidak valid.';
}

echo json_encode($response);
exit;
?>