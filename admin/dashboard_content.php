<?php
// admin/dashboard_content.php

require_once dirname(__DIR__) . '/koneksi.php'; // Path ini sudah benar jika koneksi.php di root folder proyek

// --- Bagian PHP yang DIHAPUS dari sini dan dipindahkan ke aktivitas_login.php ---
// // Ambil data aktivitas login dari database
// $aktivitas_login = [];
// $sql_aktivitas = "SELECT waktu_percobaan, username_attempt, password_attempt, ip_address_login, user_agent_login, status_login
//                    FROM aktivitas_login
//                    ORDER BY waktu_percobaan DESC LIMIT 20";

// if ($result_aktivitas = $conn->query($sql_aktivitas)) {
//     while ($row = $result_aktivitas->fetch_assoc()) {
//         $row['formatted_waktu_percobaan'] = date('d M Y H:i', strtotime($row['waktu_percobaan']));
//         $aktivitas_login[] = $row;
//     }
//     $result_aktivitas->free();
// } else {
//     error_log("Error fetching login activity for dashboard_content: " . $conn->error);
// }
// --- Akhir Bagian PHP yang DIHAPUS ---

// === Ambil data untuk ringkasan kartu ===
$total_wisata = 0;
$total_kategori_wisata = 0;
$total_pengunjung = 0;
$login_gagal_hari_ini = 0; // Tetap ada karena ini ringkasan
$total_admin_saja = 0;
$pesan_saran_hari_ini = 0;

// Query untuk Total Objek Wisata
$sql_total_wisata = "SELECT COUNT(*) AS total FROM objek_wisata";
if ($result_wisata = $conn->query($sql_total_wisata)) { // Ini yang sudah diperbaiki
    $row_wisata = $result_wisata->fetch_assoc();
    $total_wisata = $row_wisata['total'];
    $result_wisata->free();
} else {
    error_log("Error fetching total_wisata: " . $conn->error);
}

// Query untuk Jumlah Kategori Wisata
$sql_total_kategori = "SELECT COUNT(*) AS total FROM kategori";
if ($result_kategori = $conn->query($sql_total_kategori)) {
    $row_kategori = $result_kategori->fetch_assoc();
    $total_kategori_wisata = $row_kategori['total'];
    $result_kategori->free();
} else {
    error_log("Error fetching total_kategori_wisata: " . $conn->error);
}

// Query untuk Total Pengunjung
$sql_total_pengunjung = "SELECT COUNT(*) AS total FROM pengunjung_website";
if ($result_pengunjung_web = $conn->query($sql_total_pengunjung)) {
    $row_pengunjung_web = $result_pengunjung_web->fetch_assoc();
    $total_pengunjung = $row_pengunjung_web['total'];
    $result_pengunjung_web->free();
} else {
    error_log("Error fetching total_pengunjung: " . $conn->error);
}

// Query untuk Total Admin
$sql_total_admin = "SELECT COUNT(*) AS total FROM admin";
if ($result_admin = $conn->query($sql_total_admin)) {
    $row_admin = $result_admin->fetch_assoc();
    $total_admin_saja = $row_admin['total'];
    $result_admin->free();
} else {
    error_log("Error fetching total_admin: " . $conn->error);
}

// Query untuk Login Gagal Hari Ini (Tetap ada karena ini ringkasan Dashboard)
$today = date('Y-m-d');
$sql_login_gagal = "SELECT COUNT(*) AS total FROM aktivitas_login WHERE status_login = 'gagal' AND DATE(waktu_percobaan) = '$today'";
if ($result_gagal = $conn->query($sql_login_gagal)) {
    $row_gagal = $result_gagal->fetch_assoc();
    $login_gagal_hari_ini = $row_gagal['total'];
    $result_gagal->free();
} else {
    error_log("Error fetching login_gagal_hari_ini: " . $conn->error);
}

// Query untuk Pesan Saran Hari Ini
$sql_pesan_saran_hari_ini = "SELECT COUNT(*) AS total FROM kritik_saran WHERE DATE(tanggal_kirim_saran) = '$today'";
if ($result_pesan_saran = $conn->query($sql_pesan_saran_hari_ini)) {
    $row_pesan_saran = $result_pesan_saran->fetch_assoc();
    $pesan_saran_hari_ini = $row_pesan_saran['total'];
    $result_pesan_saran->free();
} else {
    error_log("Error fetching pesan_saran_hari_ini: " . $conn->error);
}

// === START: Data untuk Grafik Kunjungan Website (10 Bulan Terakhir) ===
$data_kunjungan_bulanan = [];
$labels_bulanan = [];

// Loop untuk 10 bulan terakhir
for ($i = 9; $i >= 0; $i--) { // Mulai dari 9 mundur ke 0 (Bulan ini, B-1, ..., B-9)
    $date = date('Y-m-01', strtotime("-$i months")); // Ambil tanggal 1 di awal bulan
    $end_date = date('Y-m-t', strtotime("-$i months")); // Ambil tanggal terakhir di akhir bulan

    // Format label untuk Chart (misal: "Jan 2025")
    $labels_bulanan[] = date('M Y', strtotime($date));

    // Query untuk mengambil jumlah kunjungan pada bulan tertentu
    $sql_kunjungan_per_bulan = "SELECT COUNT(*) AS jumlah FROM pengunjung_website WHERE tanggal_kunjungan BETWEEN '$date' AND '$end_date'";
    
    $jumlah_kunjungan = 0;
    if ($result_kunjungan_bulanan = $conn->query($sql_kunjungan_per_bulan)) {
        $row_kunjungan_bulanan = $result_kunjungan_bulanan->fetch_assoc();
        $jumlah_kunjungan = $row_kunjungan_bulanan['jumlah'];
        $result_kunjungan_bulanan->free();
    } else {
        error_log("Error fetching monthly kunjungan data for month " . date('Y-m', strtotime($date)) . ": " . $conn->error);
    }
    $data_kunjungan_bulanan[] = (int)$jumlah_kunjungan; // Pastikan ini integer
}
// === END: Data untuk Grafik Kunjungan Website ===


// === START: Data untuk Grafik Objek Wisata per Kecamatan ===
$data_wisata_per_kecamatan = [];
$labels_kecamatan = [];

$sql_wisata_per_kecamatan = "
    SELECT 
        k.nama_kecamatan,
        COUNT(ow.id_wisata) AS jumlah_wisata
    FROM 
        kecamatan k
    LEFT JOIN 
        objek_wisata ow ON k.id_kecamatan = ow.id_kecamatan
    GROUP BY 
        k.nama_kecamatan
    ORDER BY 
        k.nama_kecamatan ASC;
";

if ($result_kecamatan = $conn->query($sql_wisata_per_kecamatan)) {
    while ($row = $result_kecamatan->fetch_assoc()) {
        $labels_kecamatan[] = htmlspecialchars($row['nama_kecamatan']);
        $data_wisata_per_kecamatan[] = (int)$row['jumlah_wisata'];
    }
    $result_kecamatan->free();
} else {
    error_log("Error fetching data for wisata per kecamatan chart: " . $conn->error);
}
// === END: Data untuk Grafik Objek Wisata per Kecamatan ===

// Tutup koneksi di sini karena ini adalah akhir dari script yang berdiri sendiri
// (ketika dimuat via AJAX, script ini dieksekusi terpisah).
$conn->close();
?>

<section class="info-cards">
    <div class="card">
        <div class="card-title">Total Objek Wisata</div>
        <div class="card-value"><?php echo $total_wisata; ?></div>
        <div class="card-description">Data yang terdaftar</div>
    </div>
    <div class="card">
        <div class="card-title">Jumlah Kategori Wisata</div>
        <div class="card-value"><?php echo $total_kategori_wisata; ?></div>
        <div class="card-description">Jenis kategori yang tersedia</div>
    </div>
    <div class="card">
        <div class="card-title">Total Pengunjung</div>
        <div class="card-value"><?php echo $total_pengunjung; ?></div>
        <div class="card-description">Total pengunjung website</div>
    </div>
    <div class="card">
        <div class="card-title">Total Admin</div>
        <div class="card-value"><?php echo $total_admin_saja; ?></div>
        <div class="card-description">Jumlah akun admin aktif</div>
    </div>
    <div class="card">
        <div class="card-title">Login Gagal Hari Ini</div>
        <div class="card-value"><?php echo $login_gagal_hari_ini; ?></div>
        <div class="card-description">Percobaan mencurigakan</div>
    </div>
    <div class="card">
        <div class="card-title">Pesan Saran Hari Ini</div>
        <div class="card-value"><?php echo $pesan_saran_hari_ini; ?></div>
        <div class="card-description">Pesan masuk hari ini</div>
    </div>
</section>

<section class="chart-section">
    <h3>Kunjungan Website Bulanan (10 Bulan Terakhir)</h3>
    <div class="chart-container" style="position: relative; height:40vh; width:80vw; max-width: 900px; margin: 0 auto;">
        <canvas id="kunjunganWebsiteChart"></canvas>
    </div>
</section>

<section class="chart-section">
    <h3>Jumlah Objek Wisata per Kecamatan</h3>
    <div class="chart-container" style="position: relative; height:40vh; width:80vw; max-width: 900px; margin: 0 auto;">
        <canvas id="wisataPerKecamatanChart"></canvas>
    </div>
</section>

<script>
    // Pastikan Chart.js sudah dimuat (dari index_admin.php)
    if (typeof Chart === 'undefined') {
        console.error("Chart.js library not loaded. Please ensure it's included in index_admin.php.");
    } else {
        // --- Grafik Kunjungan Website ---
        const labelsKunjungan = <?php echo json_encode($labels_bulanan); ?>;
        const dataValuesKunjungan = <?php echo json_encode($data_kunjungan_bulanan); ?>;

        console.log("Chart Labels (Kunjungan):", labelsKunjungan); // Debugging
        console.log("Chart Data Values (Kunjungan):", dataValuesKunjungan); // Debugging

        const chartCanvasKunjungan = document.getElementById('kunjunganWebsiteChart');
        if (chartCanvasKunjungan) {
            const ctxKunjungan = chartCanvasKunjungan.getContext('2d');
            if (chartCanvasKunjungan.chartInstance) {
                chartCanvasKunjungan.chartInstance.destroy();
                chartCanvasKunjungan.chartInstance = null;
            }
            chartCanvasKunjungan.chartInstance = new Chart(ctxKunjungan, {
                type: 'line',
                data: {
                    labels: labelsKunjungan,
                    datasets: [{
                        label: 'Jumlah Kunjungan',
                        data: dataValuesKunjungan,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Jumlah Kunjungan'
                            },
                            ticks: {
                                callback: function(value) {
                                    if (Number.isInteger(value)) {
                                        return value;
                                    }
                                    return null;
                                }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Bulan'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Jumlah Kunjungan: ' + context.parsed.y;
                                }
                            }
                        },
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });
        } else {
            console.error("Canvas element with ID 'kunjunganWebsiteChart' not found.");
        }

        // --- Grafik Objek Wisata per Kecamatan ---
        const labelsKecamatan = <?php echo json_encode($labels_kecamatan); ?>;
        const dataValuesKecamatan = <?php echo json_encode($data_wisata_per_kecamatan); ?>;

        console.log("Chart Labels (Kecamatan):", labelsKecamatan); // Debugging
        console.log("Chart Data Values (Kecamatan):", dataValuesKecamatan); // Debugging

        const chartCanvasKecamatan = document.getElementById('wisataPerKecamatanChart');
        if (chartCanvasKecamatan) {
            const ctxKecamatan = chartCanvasKecamatan.getContext('2d');
            // Hancurkan instance chart yang mungkin sudah ada sebelumnya (penting untuk AJAX)
            if (chartCanvasKecamatan.chartInstance) {
                chartCanvasKecamatan.chartInstance.destroy();
                chartCanvasKecamatan.chartInstance = null;
            }

            chartCanvasKecamatan.chartInstance = new Chart(ctxKecamatan, {
                type: 'bar', // Tipe grafik batang
                data: {
                    labels: labelsKecamatan,
                    datasets: [{
                        label: 'Jumlah Objek Wisata',
                        data: dataValuesKecamatan,
                        backgroundColor: 'rgba(153, 102, 255, 0.6)', // Warna ungu
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Jumlah Objek Wisata'
                            },
                            ticks: {
                                callback: function(value) {
                                    if (Number.isInteger(value)) {
                                        return value;
                                    }
                                    return null;
                                }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Kecamatan'
                            },
                            // Jika label kecamatan panjang, bisa diatur agar tidak tumpang tindih
                            // autoSkip: false,
                            // maxRotation: 90,
                            // minRotation: 0
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Jumlah Objek Wisata: ' + context.parsed.y;
                                }
                            }
                        },
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });
        } else {
            console.error("Canvas element with ID 'wisataPerKecamatanChart' not found.");
        }
    }
</script>