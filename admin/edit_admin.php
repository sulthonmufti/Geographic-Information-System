<?php
// edit_admin.php

session_start();

// Cek apakah admin sudah login dan memiliki peran 'Master'
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'Master') {
    echo "<p>Akses ditolak. Anda tidak memiliki izin untuk melihat halaman ini.</p>";
    exit;
}

// Sertakan file koneksi database
require_once dirname(__DIR__) . '/koneksi.php';

$message = '';
$status = '';

// Variabel untuk menampung data admin
$admin_data = null;
$id_admin_to_edit = null;

// Cek apakah ada ID admin yang dikirim melalui parameter GET
if (isset($_GET['id_admin'])) {
    $id_admin_to_edit = $_GET['id_admin'];

    // Ambil data admin dari database berdasarkan ID
    $query = "SELECT id_admin, username_admin, nama_lengkap_admin, email_admin, role FROM admin WHERE id_admin = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_admin_to_edit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $admin_data = $result->fetch_assoc();
    } else {
        echo "<p>Data admin tidak ditemukan.</p>";
        exit;
    }
}

// Proses pengeditan data admin
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_admin_post = $_POST['id_admin'] ?? null;
    $new_nama_lengkap = $_POST['nama_lengkap_admin'] ?? '';
    $new_username = $_POST['username_admin'] ?? '';
    $new_email = $_POST['email_admin'] ?? '';
    $new_password = $_POST['password_admin'] ?? '';
    $confirm_password = $_POST['confirm_password_admin'] ?? '';
    $new_role = $_POST['role_admin'] ?? 'Regular';

    // Lakukan validasi password
    if (!empty($new_password) && $new_password !== $confirm_password) {
        echo json_encode(['status' => 'error', 'message' => 'Password baru dan konfirmasi password tidak cocok.']);
        exit;
    }

    // Cek apakah ada password yang diinput
    if (!empty($new_password)) {
        $hashed_password = md5($new_password);
        $query = "UPDATE admin SET nama_lengkap_admin = ?, username_admin = ?, email_admin = ?, password_admin = ?, role = ? WHERE id_admin = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssi", $new_nama_lengkap, $new_username, $new_email, $hashed_password, $new_role, $id_admin_post);
    } else {
        $query = "UPDATE admin SET nama_lengkap_admin = ?, username_admin = ?, email_admin = ?, role = ? WHERE id_admin = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssi", $new_nama_lengkap, $new_username, $new_email, $new_role, $id_admin_post);
    }

    // Eksekusi query
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Profil admin berhasil diperbarui']);
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui profil. Coba lagi nanti.']);
        exit;
    }
}
?>

<section class="edit-profile-section">
    <h2>Edit Data Admin</h2>
    <div class="admin-info-container">
        <div class="admin-image">
            <img src="../gambar/admin.png" alt="Admin Image">
        </div>
        <div class="admin-details">
            <form id="editAdminForm" method="POST" action="edit_admin.php">
                <input type="hidden" name="id_admin" value="<?php echo htmlspecialchars($admin_data['id_admin']); ?>">
                <table class="admin-data-table">
                    <tr>
                        <th>Username</th>
                        <td><input type="text" name="username_admin" value="<?php echo htmlspecialchars($admin_data['username_admin']); ?>" required></td>
                    </tr>
                    <tr>
                        <th>Nama Lengkap</th>
                        <td><input type="text" name="nama_lengkap_admin" value="<?php echo htmlspecialchars($admin_data['nama_lengkap_admin']); ?>" required></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><input type="email" name="email_admin" value="<?php echo htmlspecialchars($admin_data['email_admin']); ?>" required></td>
                    </tr>
                    <tr>
                        <th>Password Baru (Kosongkan jika tidak ingin mengubah)</th>
                        <td><input type="password" name="password_admin" id="password_admin_edit" placeholder="Masukkan password baru jika ingin mengubah"></td>
                    </tr>
                    <tr id="confirmPasswordRowEdit" style="display: none;">
                        <th>Konfirmasi Password Baru</th>
                        <td><input type="password" name="confirm_password_admin" id="confirm_password_admin_edit" placeholder="Konfirmasi password baru"></td>
                    </tr>
                    <tr>
                        <th>Role</th>
                        <td>
                            <select name="role_admin" class="role-select">
                                <option value="Master" <?php echo ($admin_data['role'] == 'Master') ? 'selected' : ''; ?>>Master</option>
                                <option value="Regular" <?php echo ($admin_data['role'] == 'Regular') ? 'selected' : ''; ?>>Regular</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <button type="submit" class="btn-edit-profile">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</section>