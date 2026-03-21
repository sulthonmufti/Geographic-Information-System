<?php
require_once dirname(__DIR__) . '/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id_saran']);
    $status = ($_POST['status_saran'] === 'Baru') ? 'Baru' : 'Dibaca';

    $stmt = $conn->prepare("UPDATE kritik_saran SET status_saran = ? WHERE id_saran = ?");
    $stmt->bind_param("si", $status, $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengubah status']);
    }
    $stmt->close();
    $conn->close();
}
