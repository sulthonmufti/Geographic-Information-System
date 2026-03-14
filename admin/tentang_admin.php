<?php
// tentang_admin.php

// Pastikan session_start() ada di baris paling awal
session_start();

// Cek apakah admin sudah login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: ../login.php"); // Arahkan ke halaman login jika belum login
    exit;
}

// Ambil data admin dari session
$username_admin = $_SESSION['username_admin'] ?? 'Tidak Diketahui';
$nama_lengkap_admin = $_SESSION['nama_lengkap_admin'] ?? 'Tidak Diketahui';
$email_admin = $_SESSION['email_admin'] ?? 'Tidak Diketahui';
$role_admin = $_SESSION['role'] ?? 'Tidak Diketahui';
?>

<section class="tentang-admin-section">
    <div class="admin-info-container">
        <div class="admin-image">
            <img src="../gambar/admin.png" alt="Admin Image">
        </div>

        <div class="admin-details">
            <table class="admin-data-table">
                <tr>
                    <th>Username</th>
                    <td id="username-admin">@<?php echo htmlspecialchars($username_admin); ?></td>
                </tr>
                <tr>
                    <th>Nama Lengkap</th>
                    <td id="nama-lengkap-admin"><?php echo htmlspecialchars($nama_lengkap_admin); ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td id="email-admin"><?php echo htmlspecialchars($email_admin); ?></td>
                </tr>
                <tr>
                    <th>Role</th>
                    <td id="role-admin" style="color: <?php echo ($role_admin === 'Master') ? 'var(--primary-color)' : '#555'; ?>">
                        <?php echo htmlspecialchars($role_admin); ?>
                    </td>
                </tr>
            </table>

            <div class="btn-container">
                <button id="editProfileBtn" class="btn-edit-profile">Edit Profile</button>
                <?php if ($role_admin === 'Master'): ?>
                    <button id="lihatDaftarAdminBtn" class="btn-view-admin">Lihat Daftar Admin</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>