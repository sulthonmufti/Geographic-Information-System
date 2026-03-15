<?php
require_once dirname(__DIR__) . '/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['waktu_percobaan'])) {
    $waktu_percobaan = $_POST['waktu_percobaan'];

    // Gunakan prepared statement untuk keamanan
    $stmt = $conn->prepare("DELETE FROM aktivitas_login WHERE waktu_percobaan = ?");
    $stmt->bind_param("s", $waktu_percobaan);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
    exit;
}

echo "invalid";
?>
