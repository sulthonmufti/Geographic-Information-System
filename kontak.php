<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak Pemetaan Kabupaten Magelang</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a57f5fcdf1.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style/untuk_kontak.css">
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
        <div class="contact-form-wrapper">
            <h2>Kontak Kami</h2>
            <p>Hubungi kami dan kami akan menghubungi anda dalam 24 jam</p>

            <?php
            // --- PENTING: Set zona waktu ke Asia/Jakarta (WIB) ---
            date_default_timezone_set('Asia/Jakarta');
            // --------------------------------------------------

            $message = '';
            $message_type = ''; // 'success' or 'error'
            $errors = []; // Array untuk menyimpan pesan error validasi

            // Data yang akan dipertahankan di form setelah submit
            $old_input = [
                'nama_depan' => '',
                'nama_belakang' => '',
                'email' => '',
                'no_tlp' => '',
                'pesan' => '',
                'privacy_policy' => ''
            ];

            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Simpan data POST ke old_input untuk dipertahankan di form
                foreach ($old_input as $key => $value) {
                    if (isset($_POST[$key])) {
                        $old_input[$key] = htmlspecialchars(trim($_POST[$key]));
                    }
                }
                
                // Konfigurasi koneksi database
                $servername = "localhost"; // Ganti dengan nama server Anda
                $username = "root"; // Ganti dengan username database Anda
                $password = ""; // Ganti dengan password database Anda
                $dbname = "pemetaan_kab_magelang"; // Nama database Anda

                // Ambil data dari form
                $nama_depan = $old_input['nama_depan'];
                $nama_belakang = $old_input['nama_belakang'];
                $email = $old_input['email'];
                $no_tlp = $old_input['no_tlp'];
                $pesan = $old_input['pesan'];
                $privacy_policy = isset($_POST['privacy_policy']) ? 'on' : ''; // Hanya perlu tahu apakah tercentang atau tidak

                // --- Validasi Server-Side ---
                if (empty($nama_depan)) {
                    $errors['nama_depan'] = 'Nama depan wajib diisi.';
                }
                if (empty($nama_belakang)) { // Nama belakang juga wajib diisi
                    $errors['nama_belakang'] = 'Nama belakang wajib diisi.';
                }
                if (empty($email)) {
                    $errors['email'] = 'Alamat email wajib diisi.';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors['email'] = 'Format email tidak valid.';
                }
                if (empty($no_tlp)) {
                    $errors['no_tlp'] = 'Nomor telepon wajib diisi.';
                } elseif (!preg_match('/^\+[0-9]{1,13}$/', $no_tlp)) { // Regex: dimulai dengan +, diikuti 1-13 digit angka
                    $errors['no_tlp'] = 'Format nomor telepon tidak valid. Contoh: +62857xxxxxx (max 13 angka setelah +).';
                }
                if (empty($pesan)) {
                    $errors['pesan'] = 'Pesan wajib diisi.';
                }
                if (empty($privacy_policy)) {
                    $errors['privacy_policy'] = 'Anda harus menyetujui kebijakan privasi.';
                }

                // Jika tidak ada error validasi
                if (empty($errors)) {
                    // Buat koneksi
                    $conn = new mysqli($servername, $username, $password, $dbname);

                    // Cek koneksi
                    if ($conn->connect_error) {
                        $message = "Koneksi database gagal: " . $conn->connect_error;
                        $message_type = 'error';
                    } else {
                        // Gabungkan nama depan dan belakang
                        $nama_pengirim_saran = trim($nama_depan . " " . $nama_belakang);

                        // Dapatkan tanggal dan waktu saat ini (sekarang sudah sesuai WIB karena date_default_timezone_set)
                        $tanggal_kirim_saran = date('Y-m-d H:i:s');

                        // Set status_saran menjadi 'Baru'
                        $status_saran = 'Baru';

                        // Query SQL untuk memasukkan data menggunakan prepared statements (lebih aman)
                        $stmt = $conn->prepare("INSERT INTO kritik_saran (nama_pengirim_saran, email_pengirim_saran, no_tlp_pengirim_saran, pesan_saran, tanggal_kirim_saran, status_saran) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("ssssss", $nama_pengirim_saran, $email, $no_tlp, $pesan, $tanggal_kirim_saran, $status_saran);

                        if ($stmt->execute()) {
                            $message = "Pesan Anda berhasil terkirim! Terima kasih atas masukan Anda.";
                            $message_type = 'success';
                            // Clear form fields after successful submission
                            $old_input = [
                                'nama_depan' => '', 'nama_belakang' => '', 'email' => '',
                                'no_tlp' => '', 'pesan' => '', 'privacy_policy' => ''
                            ];
                        } else {
                            $message = "Error: " . $stmt->error;
                            $message_type = 'error';
                        }
                        $stmt->close();
                        $conn->close();
                    }
                } else {
                    $message = "Mohon lengkapi semua bidang yang wajib diisi.";
                    $message_type = 'error';
                }
            }
            ?>

            <?php if ($message): ?>
                <div class="form-message <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form action="kontak.php" method="POST" class="contact-form" novalidate>
                <div class="form-group-inline">
                    <div class="form-field <?php echo isset($errors['nama_depan']) ? 'has-error' : ''; ?>">
                        <label for="nama_depan">Nama depan</label>
                        <input type="text" id="nama_depan" name="nama_depan" placeholder="Nama depan" value="<?php echo $old_input['nama_depan']; ?>" required>
                        <?php if (isset($errors['nama_depan'])): ?><span class="error-text"><?php echo $errors['nama_depan']; ?></span><?php endif; ?>
                    </div>
                    <div class="form-field <?php echo isset($errors['nama_belakang']) ? 'has-error' : ''; ?>">
                        <label for="nama_belakang">Nama belakang</label>
                        <input type="text" id="nama_belakang" name="nama_belakang" placeholder="Nama belakang" value="<?php echo $old_input['nama_belakang']; ?>" required>
                        <?php if (isset($errors['nama_belakang'])): ?><span class="error-text"><?php echo $errors['nama_belakang']; ?></span><?php endif; ?>
                    </div>
                </div>

                <div class="form-field <?php echo isset($errors['email']) ? 'has-error' : ''; ?>">
                    <label for="email">Alamat email</label>
                    <input type="email" id="email" name="email" placeholder="example@gmail.com" value="<?php echo $old_input['email']; ?>" required>
                    <?php if (isset($errors['email'])): ?><span class="error-text"><?php echo $errors['email']; ?></span><?php endif; ?>
                </div>

                <div class="form-field <?php echo isset($errors['no_tlp']) ? 'has-error' : ''; ?>">
                    <label for="no_tlp">No. Tlp</label>
                    <input type="tel" id="no_tlp" name="no_tlp" placeholder="+62 857xxxxxx" value="<?php echo $old_input['no_tlp']; ?>" 
                           pattern="^\+[0-9]{1,13}$" maxlength="14" required>
                    <?php if (isset($errors['no_tlp'])): ?><span class="error-text"><?php echo $errors['no_tlp']; ?></span><?php endif; ?>
                </div>

                <div class="form-field <?php echo isset($errors['pesan']) ? 'has-error' : ''; ?>">
                    <label for="pesan">Pesan</label>
                    <textarea id="pesan" name="pesan" rows="6" placeholder="Tuliskan pesan anda..." required><?php echo $old_input['pesan']; ?></textarea>
                    <?php if (isset($errors['pesan'])): ?><span class="error-text"><?php echo $errors['pesan']; ?></span><?php endif; ?>
                </div>

                <div class="form-field form-checkbox-group <?php echo isset($errors['privacy_policy']) ? 'has-error' : ''; ?>">
                    <div class="form-checkbox">
                        <input type="checkbox" id="privacy_policy" name="privacy_policy" required <?php echo ($old_input['privacy_policy'] === 'on') ? 'checked' : ''; ?>>
                        <label for="privacy_policy">Dengan ini anda menyetujui dengan <a href="kebijakan_privasi.php" class="privacy-link">kebijakan privasi kami.</a></label>
                    </div>
                    <?php if (isset($errors['privacy_policy'])): ?><span class="error-text"><?php echo $errors['privacy_policy']; ?></span><?php endif; ?>
                </div>

                <button type="submit" class="submit-button">Kirim Pesan</button>
            </form>
        </div>
    </main>

    <footer class="footer">
        <div>© 2025 – Pemetaan Objek Wisata Kabupaten Magelang. All rights reserved.</div>
        <div>Muhammad Sulthon Mufti (2100018213)</div>
    </footer>

    <script src="script.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile navigation toggle
            const hamburgerMenu = document.querySelector('.hamburger-menu');
            const mobileNavOverlay = document.querySelector('.mobile-nav-overlay');
            const body = document.body;

            if (hamburgerMenu && mobileNavOverlay) {
                hamburgerMenu.addEventListener('click', function() {
                    mobileNavOverlay.classList.toggle('active');
                    body.classList.toggle('no-scroll');
                });

                // Close mobile nav when clicking outside (on the overlay itself)
                mobileNavOverlay.addEventListener('click', function(e) {
                    if (e.target === mobileNavOverlay) {
                        mobileNavOverlay.classList.remove('active');
                        body.classList.remove('no-scroll');
                    }
                });
            }

            // Input validation and filtering for phone number
            const noTlpInput = document.getElementById('no_tlp');

            if (noTlpInput) {
                noTlpInput.addEventListener('keydown', function(event) {
                    // Allow numbers (0-9), plus sign (+), backspace, delete, arrow keys, tab
                    // Key codes for digits 0-9: 48-57 (top row), 96-105 (numpad)
                    // Plus sign: 187 (for + on standard keyboard), 107 (numpad +)
                    // Backspace: 8, Delete: 46, Left Arrow: 37, Right Arrow: 39, Tab: 9
                    // Also allow Ctrl/Cmd + A, C, V, X for selecting, copying, pasting, cutting
                    if (
                        !(event.key >= '0' && event.key <= '9') && // Not a digit
                        event.key !== '+' && // Not a plus sign
                        event.key !== 'Backspace' &&
                        event.key !== 'Delete' &&
                        event.key !== 'ArrowLeft' &&
                        event.key !== 'ArrowRight' &&
                        event.key !== 'Tab' &&
                        !event.metaKey && // Allow Cmd on Mac
                        !event.ctrlKey // Allow Ctrl on Windows
                    ) {
                        event.preventDefault(); // Stop the key from being entered
                    }

                    // Ensure '+' is only at the beginning
                    if (event.key === '+' && this.selectionStart > 0) {
                        event.preventDefault(); // Prevent '+' if not at the beginning
                    }
                });

                noTlpInput.addEventListener('input', function(event) {
                    let value = this.value;

                    // Enforce '+' at the beginning and only digits after that
                    if (value.startsWith('+')) {
                        value = '+' + value.substring(1).replace(/[^0-9]/g, '');
                    } else {
                        // If '+' is somehow removed or not typed first, only allow digits
                        value = value.replace(/[^0-9]/g, '');
                    }

                    // Limit to 13 digits after '+' (total 14 characters including '+')
                    if (value.length > 14) {
                        value = value.substring(0, 14);
                    }

                    this.value = value;
                });

                // Optional: Handle paste events to ensure only valid characters are pasted
                noTlpInput.addEventListener('paste', function(event) {
                    const pasteData = event.clipboardData.getData('text');
                    // Clean the pasted data: allow '+' at start, then only digits
                    let cleanedData;
                    if (pasteData.startsWith('+')) {
                        cleanedData = '+' + pasteData.substring(1).replace(/[^0-9]/g, '');
                    } else {
                        cleanedData = pasteData.replace(/[^0-9]/g, '');
                    }

                    // Combine current value with cleaned pasted data
                    const currentValue = this.value.substring(0, this.selectionStart) + cleanedData + this.value.substring(this.selectionEnd);

                    // Apply max length constraint
                    if (currentValue.length > 14) {
                        event.preventDefault();
                        this.value = currentValue.substring(0, 14);
                    } else {
                        // Let the paste happen if valid, but also re-run input logic
                        // This is a bit redundant with the 'input' event, but good for robustness
                        // No need to prevent default if we're just letting it paste and then cleaning
                    }
                });
            }
        });
    </script>
    
</body>
</html>