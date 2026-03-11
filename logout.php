<?php
// Memulai sesi PHP
session_start();

// Hapus semua variabel sesi
$_SESSION = array();

// Jika ingin menghancurkan sesi, hapus juga cookie sesi.
// Catatan: Ini akan menghancurkan sesi, dan bukan hanya data sesi!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Terakhir, hancurkan sesi
session_destroy();

// Redirect ke halaman login
header("location: login.php");
exit;
?>