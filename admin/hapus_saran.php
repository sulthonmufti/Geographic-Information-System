<?php
require_once dirname(__DIR__) . '/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_saran'])) {
    $id = intval($_POST['id_saran']);
    $stmt = $conn->prepare("DELETE FROM kritik_saran WHERE id_saran = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus data']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Permintaan tidak valid']);
}
$conn->close();
