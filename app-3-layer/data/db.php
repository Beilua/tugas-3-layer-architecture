<?php
// DATA LAYER - koneksi database. Tidak ada logika bisnis di sini.
// buat objek koneksi ke database
function buatKoneksi() {
    // Tanpa ini, PHP 8+ melempar mysqli_sql_exception dan API mengirim
    // halaman HTML 500 (bukan JSON), sehingga fetch().json() gagal di browser.
    mysqli_report(MYSQLI_REPORT_OFF);
    // hubungkan ke server mysql lokal
    $koneksi = mysqli_connect('localhost', 'root', '', 'db_customer');

    // kembalikan null bila koneksi gagal
    if (!$koneksi) {
        return null;
    }

    // kembalikan koneksi bila berhasil
    return $koneksi;
}
