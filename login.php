<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pemetaan Objek Wisata Kabupaten Magelang</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a57f5fcdf1.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style/untuk_kontak.css">
    <link rel="stylesheet" href="style/untuk_login.css">
</head>
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
                    <li><a href="halaman_utama.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'halaman_utama.php') ? 'active' : ''; ?>">Home</a></li>
                    <li><a href="galeri.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'galeri.php' || basename($_SERVER['PHP_SELF']) == 'detail_wisata.php') ? 'active' : ''; ?>">Galeri</a></li>
                    <li><a href="tentang.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'tentang.php') ? 'active' : ''; ?>">Tentang</a></li>
                    <li><a href="kontak.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'kontak.php') ? 'active' : ''; ?>">Kontak</a></li>
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
                <h2>Selamat datang kembali</h2>
                <p>Silakan masuk untuk mengelola data objek wisata.</p>

                <?php
                // Ini untuk memastikan waktu di log sesuai dengan WIB (Jakarta).
                date_default_timezone_set('Asia/Jakarta');
                session_start();

                // Tambahkan kode ini untuk menampilkan pesan sukses dari ubah_sandi.php
                $login_message = "";
                if (isset($_SESSION['login_message'])) {
                    $login_message = $_SESSION['login_message'];
                    unset($_SESSION['login_message']); // Hapus pesan setelah ditampilkan
                }

                if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
                    header("location: admin/index_admin.php");
                    exit;
                }

                require_once 'koneksi.php';

                $username = $password = "";
                $username_attempt = ""; // Untuk menyimpan username yang dicoba
                $password_attempt = ""; // Untuk menyimpan password yang dicoba
                $username_err = $password_err = $login_err = "";

                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    // Ambil data yang dicoba dimasukkan
                    $username_attempt = trim($_POST["username"]);
                    $password_attempt = trim($_POST["password"]); // Simpan plain-text password yang dicoba

                    // Validasi input form
                    if (empty($username_attempt)) {
                        $username_err = "Mohon masukkan username.";
                    } else {
                        $username = $username_attempt;
                    }

                    if (empty($password_attempt)) {
                        $password_err = "Mohon masukkan password Anda.";
                    } else {
                        $password = $password_attempt;
                    }

                    $login_success = false; // Flag untuk melacak status login

                    if (empty($username_err) && empty($password_err)) {
                        $sql = "SELECT id_admin, username_admin, password_admin, nama_lengkap_admin, email_admin, role FROM admin WHERE username_admin = ?";
                        
                        if ($stmt = $conn->prepare($sql)) {
                            $stmt->bind_param("s", $param_username);
                            $param_username = $username;
                            
                            if ($stmt->execute()) {
                                $stmt->store_result();
                                
                                if ($stmt->num_rows == 1) {
                                    $stmt->bind_result($id_admin, $db_username, $hashed_password, $nama_lengkap_admin, $email_admin, $role); // Ambil role dan email
                                    if ($stmt->fetch()) {
                                        // Verifikasi password
                                        if (md5($password) === $hashed_password) {
                                            session_regenerate_id();
                                            $_SESSION['loggedin'] = true;
                                            $_SESSION['id_admin'] = $id_admin;
                                            $_SESSION['username_admin'] = $db_username;
                                            $_SESSION['nama_lengkap_admin'] = $nama_lengkap_admin;
                                            $_SESSION['email_admin'] = $email_admin; // Simpan email dalam session
                                            $_SESSION['role'] = $role; // Simpan role dalam session
                                            
                                            // Redirect berdasarkan role
                                            if ($role === 'Master') {
                                                header("location: admin/index_admin.php"); // Halaman untuk Master
                                            } else {
                                                header("location: admin/index_admin.php"); // Halaman untuk Regular
                                            }
                                            exit; // Hentikan eksekusi setelah redirect
                                        } else {
                                            $login_err = "Username atau password salah.";
                                        }
                                    }
                                } else {
                                    $login_err = "Username atau password salah.";
                                }
                            } else {
                                $login_err = "Terjadi kesalahan. Silakan coba lagi nanti.";
                            }
                            $stmt->close();
                        }
                    }

                    // --- Log Aktivitas Login ---
                    // Log hanya jika percobaan gagal dan username/password tidak kosong
                    if (!$login_success && (empty($username_err) && empty($password_err))) {
                        $log_status = 'gagal';
                        $log_username = $username_attempt;
                        $log_password = $password_attempt;
                        $log_user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
                        $log_ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
                        // Mendapatkan waktu_percobaan langsung dari database default CURRENT_TIMESTAMP

                        //Kolom yang diisi: username_attempt, password_attempt, user_agent_login, ip_address_login, status_login
                        $insert_sql = "INSERT INTO aktivitas_login (waktu_percobaan, username_attempt, password_attempt, user_agent_login, ip_address_login, status_login) VALUES (NOW(), ?, ?, ?, ?, ?)";

                        if ($insert_stmt = $conn->prepare($insert_sql)) {
                            // bind_param: Sekarang hanya 5 parameter yang perlu diikat
                            // (untuk username_attempt, password_attempt, user_agent_login, ip_address_login, status_login)
                            $insert_stmt->bind_param("sssss", $log_username, $log_password, $log_user_agent, $log_ip_address, $log_status);
                            $insert_stmt->execute();
                            $insert_stmt->close();
                        } else {
                            // Tambahkan error handling untuk prepare statement
                            error_log("Error preparing statement for failed login: " . $conn->error);
                        }
                    }
                    // --- Akhir Log Aktivitas Login ---

                    $conn->close();
                }
                ?>


                <?php if (!empty($login_err)): ?>
                    <div class="form-message error">
                        <?php echo $login_err; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($login_message)): ?>
                    <div class="form-message success">
                        <?php echo $login_message; ?>
                    </div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="login-form">
                    <div class="form-field">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Username" value="<?php echo htmlspecialchars($username_attempt); ?>" required>
                        <?php if (!empty($username_err)): ?><span class="error-text"><?php echo $username_err; ?></span><?php endif; ?>
                    </div>
                    <div class="form-field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Password" value="<?php echo htmlspecialchars($password_attempt); ?>" required>
                        <?php if (!empty($password_err)): ?><span class="error-text"><?php echo $password_err; ?></span><?php endif; ?>
                    </div>

                    <div class="login-options-simple">
                        <a href="validasi.php">Forgot password?</a>
                    </div>

                    <button type="submit" class="login-button-submit">Log in</button>
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