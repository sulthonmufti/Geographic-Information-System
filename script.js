/**
 * =============================================================================
 * Inisialisasi Aplikasi
 * Kode ini akan dijalankan setelah seluruh konten DOM halaman dimuat.
 * Memastikan semua elemen HTML tersedia sebelum diinteraksi oleh JavaScript.
 * =============================================================================
 */
document.addEventListener('DOMContentLoaded', function() {

    /**
     * =========================================================================
     * Fitur: Menu Hamburger (Navigasi Mobile)
     * Mengelola fungsionalitas tombol hamburger untuk menampilkan/menyembunyikan
     * overlay menu navigasi pada tampilan mobile.
     * =========================================================================
     */
    const hamburgerMenu = document.querySelector('.hamburger-menu');
    const mobileNavOverlay = document.querySelector('.mobile-nav-overlay');
    const body = document.body;

    // Toggle menu mobile saat tombol hamburger diklik
    hamburgerMenu.addEventListener('click', function() {
        mobileNavOverlay.classList.toggle('active');
        body.classList.toggle('no-scroll'); // Mencegah scrolling body saat menu aktif
    });

    // Tutup menu mobile saat area overlay di luar menu diklik
    mobileNavOverlay.addEventListener('click', function(event) {
        if (event.target === mobileNavOverlay) {
            mobileNavOverlay.classList.remove('active');
            body.classList.remove('no-scroll');
        }
    });
    // Akhir Fitur: Menu Hamburger
    // =========================================================================

});
// Akhir Inisialisasi Aplikasi
// =============================================================================