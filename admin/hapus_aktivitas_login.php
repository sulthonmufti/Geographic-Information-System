<?php
require_once dirname(__DIR__) . '/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $waktu_percobaan = $_POST['waktu_percobaan'] ?? '';

    if ($waktu_percobaan) {
        $stmt = $conn->prepare("DELETE FROM aktivitas_login WHERE waktu_percobaan = ?");
        $stmt->bind_param("s", $waktu_percobaan);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus data']);
        }

        $stmt->close();
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    }

    $conn->close();
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
