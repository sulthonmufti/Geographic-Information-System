<?php
session_start();
require_once 'koneksi.php';

$username = $nama_lengkap = $email = "";
$username_err = $nama_lengkap_err = $email_err = $validation_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Mohon masukkan username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Validate nama_lengkap
    if (empty(trim($_POST["nama_lengkap"]))) {
        $nama_lengkap_err = "Mohon masukkan nama lengkap.";
    } else {
        $nama_lengkap = trim($_POST["nama_lengkap"]);
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Mohon masukkan email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Check input errors before querying database
    if (empty($username_err) && empty($nama_lengkap_err) && empty($email_err)) {
        $sql = "SELECT id_admin FROM admin WHERE username_admin = ? AND nama_lengkap_admin = ? AND email_admin = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sss", $param_username, $param_nama_lengkap, $param_email);

            $param_username = $username;
            $param_nama_lengkap = $nama_lengkap;
            $param_email = $email;

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    // Validasi berhasil, simpan id_admin di sesi dan arahkan ke ubah_sandi.php
                    $stmt->bind_result($id_admin);
                    $stmt->fetch();
                    $_SESSION['reset_id_admin'] = $id_admin;
                    header("location: ubah_sandi.php");
                    exit;
                } else {
                    $validation_err = "Data tidak cocok. Mohon periksa kembali username, nama lengkap, dan email Anda.";
                }
            } else {
                $validation_err = "Terjadi kesalahan sistem. Silakan coba lagi nanti.";
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
    <title>Validasi Akun - Pemetaan Objek Wisata Kabupaten Magelang</title>
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
                <h2>Verifikasi Akun Anda</h2>
                <p>Masukkan data akun Anda untuk melanjutkan proses reset password.</p>

                <?php if (!empty($validation_err)): ?>
                    <div class="form-message error">
                        <?php echo $validation_err; ?>
                    </div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="login-form">
                    <div class="form-field">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Username" value="<?php echo $username; ?>" required>
                        <?php if (!empty($username_err)): ?><span class="error-text"><?php echo $username_err; ?></span><?php endif; ?>
                    </div>
                    <div class="form-field">
                        <label for="nama_lengkap">Nama Lengkap</label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Nama Lengkap" value="<?php echo $nama_lengkap; ?>" required>
                        <?php if (!empty($nama_lengkap_err)): ?><span class="error-text"><?php echo $nama_lengkap_err; ?></span><?php endif; ?>
                    </div>
                    <div class="form-field">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Email" value="<?php echo $email; ?>" required>
                        <?php if (!empty($email_err)): ?><span class="error-text"><?php echo $email_err; ?></span><?php endif; ?>
                    </div>

                    <button type="submit" class="login-button-submit">Verifikasi</button>
                    <div class="login-options-simple" style="text-align: center; margin-top: 15px;">
                        <a href="login.php">Kembali ke Login</a>
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