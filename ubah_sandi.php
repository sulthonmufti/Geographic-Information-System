<?php
session_start();
require_once 'koneksi.php';

// Cek apakah ada id_admin di sesi untuk reset password
if (!isset($_SESSION['reset_id_admin'])) {
    header("location: validasi.php"); // Jika tidak ada, kembalikan ke halaman validasi
    exit;
}

$new_password = $confirm_password = "";
$new_password_err = $confirm_password_err = $update_err = $update_success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate new password
    if (empty(trim($_POST["new_password"]))) {
        $new_password_err = "Mohon masukkan password baru.";
    } elseif (strlen(trim($_POST["new_password"])) < 6) {
        $new_password_err = "Password minimal harus 6 karakter.";
    } else {
        $new_password = trim($_POST["new_password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Mohon konfirmasi password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($new_password_err) && ($new_password != $confirm_password)) {
            $confirm_password_err = "Password tidak cocok.";
        }
    }

    // Check input errors before updating password
    if (empty($new_password_err) && empty($confirm_password_err)) {
        // Hash password with MD5
        $hashed_password = md5($new_password);
        $id_admin_to_update = $_SESSION['reset_id_admin'];

        $sql = "UPDATE admin SET password_admin = ? WHERE id_admin = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $hashed_password, $id_admin_to_update);

            if ($stmt->execute()) {
                // Password berhasil diubah, hapus sesi reset dan redirect ke login
                unset($_SESSION['reset_id_admin']);
                $_SESSION['login_message'] = "Password Anda berhasil diubah. Silakan login dengan password baru.";
                header("location: login.php");
                exit;
            } else {
                $update_err = "Terjadi kesalahan saat mengubah password. Silakan coba lagi nanti.";
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Password - Pemetaan Objek Wisata Kabupaten Magelang</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a57f5fcdf1.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style/untuk_kontak.css">
    <link rel="stylesheet" href="style/untuk_login.css"> </head>
<body>
    <header class="main-header">
        <div class="container">
            <a href="index.php" class="logo-container">
                <img src="gambar/logo_magelang.png" alt="Logo Disparpora" class="logo">
                <div class="logo-text">
                    <p>Disparpora</p>
                    <p>Kabupaten Magelang</p>
                </div>
            </a>
            <nav class="main-nav">
                <ul>
                    <li><a href="halaman_utama.php">Home</a></li>
                    <li><a href="galeri.php">Galeri</a></li>
                    <li><a href="tentang.php">Tentang</a></li>
                    <li><a href="kontak.php">Kontak</a></li>
                </ul>
                <a href="login.php" class="login-button">
                    <i class="fas fa-user"></i> Login
                </a>
            </nav>
            <button class="hamburger-menu">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <div class="mobile-nav-overlay">
        <nav class="mobile-nav">
            <ul>
                <li><a href="halaman_utama.php">Home</a></li>
                <li><a href="galeri.php">Galeri</a></li>
                <li><a href="tentang.php">Tentang</a></li>
                <li><a href="kontak.php">Kontak</a></li>
            </ul>
            <a href="login.php" class="mobile-login-button">
                <i class="fas fa-user"></i> Login
            </a>
        </nav>
    </div>

    <main>
        <div class="login-container">
            <div class="login-image-section">
                <div class="login-quote">
                    "Memetakan keindahan Magelang, satu titik lokasi pada satu waktu."
                    <strong>- Tim SIG Magelang</strong>
                </div>
            </div>
            <div class="login-form-section">
                <h2>Ubah Password Anda</h2>
                <p>Silakan masukkan password baru Anda.</p>

                <?php if (!empty($update_err)): ?>
                    <div class="form-message error">
                        <?php echo $update_err; ?>
                    </div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="login-form">
                    <div class="form-field">
                        <label for="new_password">Password Baru</label>
                        <input type="password" id="new_password" name="new_password" placeholder="Password Baru" required>
                        <?php if (!empty($new_password_err)): ?><span class="error-text"><?php echo $new_password_err; ?></span><?php endif; ?>
                    </div>
                    <div class="form-field">
                        <label for="confirm_password">Konfirmasi Password Baru</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Konfirmasi Password Baru" required>
                        <?php if (!empty($confirm_password_err)): ?><span class="error-text"><?php echo $confirm_password_err; ?></span><?php endif; ?>
                    </div>

                    <button type="submit" class="login-button-submit">Ubah Password</button>
                    <div class="login-options-simple" style="text-align: center; margin-top: 15px;">
                        <a href="login.php">Batal dan Kembali ke Login</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div>© 2025 – Pemetaan Objek Wisata Kabupaten Magelang. All rights reserved.</div>
        <div>Muhammad Sulthon Mufti (2100018213)</div>
    </footer>

    <script src="script.js"></script>
</body>
</html>