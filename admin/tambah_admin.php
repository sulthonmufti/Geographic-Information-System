<?php
// tambah_admin.php

session_start();

// Cek apakah admin sudah login dan memiliki peran 'Master'
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'Master') {
    echo "<p>Akses ditolak. Anda tidak memiliki izin untuk melihat halaman ini.</p>";
    exit;
}

// Sertakan file koneksi database
require_once dirname(__DIR__) . '/koneksi.php';

// Inisialisasi variabel untuk menampung pesan error
$message = '';
$status = '';

// Proses penambahan data admin
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username_admin = $_POST['username_admin'] ?? '';
    $nama_lengkap_admin = $_POST['nama_lengkap_admin'] ?? '';
    $email_admin = $_POST['email_admin'] ?? '';
    $password_admin = $_POST['password_admin'] ?? '';
    $confirm_password_admin = $_POST['confirm_password_admin'] ?? '';
    $role = $_POST['role_admin'] ?? 'Regular'; // Default role jika tidak ada input

    // Validasi data
    if (empty($username_admin) || empty($nama_lengkap_admin) || empty($email_admin) || empty($password_admin) || empty($confirm_password_admin)) {
        $message = 'Semua field wajib diisi.';
        $status = 'error';
    } elseif ($password_admin !== $confirm_password_admin) {
        $message = 'Password baru dan konfirmasi password tidak cocok.';
        $status = 'error';
    } else {
        // Cek apakah username atau email sudah ada
        $check_query = "SELECT id_admin FROM admin WHERE username_admin = ? OR email_admin = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ss", $username_admin, $email_admin);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $message = 'Username atau Email sudah terdaftar.';
            $status = 'error';
        } else {
            // Hash password
            $hashed_password = md5($password_admin);

            // Masukkan data admin baru ke database
            $insert_query = "INSERT INTO admin (username_admin, nama_lengkap_admin, email_admin, password_admin, role) VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("sssss", $username_admin, $nama_lengkap_admin, $email_admin, $hashed_password, $role);

            if ($insert_stmt->execute()) {
                $message = 'Admin baru berhasil ditambahkan.';
                $status = 'success';
            } else {
                $message = 'Gagal menambahkan admin baru. Silakan coba lagi.';
                $status = 'error';
            }
        }
        $check_stmt->close();
    }

    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}
?>

<section class="edit-profile-section">
    <h2>Tambah Admin Baru</h2>
    <div class="admin-info-container">
        <div class="admin-image">
            <img src="../gambar/admin.png" alt="Admin Image">
        </div>
        <div class="admin-details">
            <form id="addAdminForm" method="POST" action="tambah_admin.php">
                <table class="admin-data-table">
                    <tr>
                        <th>Username</th>
                        <td><input type="text" name="username_admin" required></td>
                    </tr>
                    <tr>
                        <th>Nama Lengkap</th>
                        <td><input type="text" name="nama_lengkap_admin" required></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><input type="email" name="email_admin" required></td>
                    </tr>
                    <tr>
                        <th>Password</th>
                        <td><input type="password" name="password_admin" id="password_admin" required></td>
                    </tr>
                    <tr>
                        <th>Konfirmasi Password</th>
                        <td><input type="password" name="confirm_password_admin" id="confirm_password_admin" required></td>
                    </tr>
                    <tr>
                        <th>Role</th>
                        <td>
                            <select name="role_admin" class="role-select">
                                <option value="Regular">Regular</option>
                                <option value="Master">Master</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <button type="submit" class="btn-edit-profile">Simpan Admin</button>
            </form>
        </div>
    </div>
</section>