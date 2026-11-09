<?php
// DATA LAYER - koneksi database. Tidak ada logika bisnis di sini.
function buatKoneksi() {
    // Tanpa ini, PHP 8+ melempar mysqli_sql_exception dan API mengirim
    // halaman HTML 500 (bukan JSON), sehingga fetch().json() gagal di browser.
    mysqli_report(MYSQLI_REPORT_OFF);
    $koneksi = mysqli_connect('localhost', 'root', '', 'db_customer');

    if (!$koneksi) {
        return null;
    }

    return $koneksi;
}
