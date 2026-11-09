<?php
// DATA LAYER - query CRUD saja. Tidak tahu soal validasi bisnis.
require_once __DIR__ . '/db.php';

// simpan pelanggan baru ke database
function tambahPelanggan($koneksi, $nama, $email, $noHp, $alamat) {
    // siapkan perintah simpan aman
    $stmt = mysqli_prepare($koneksi, 'INSERT INTO pelanggan (nama, email, no_hp, alamat) VALUES (?, ?, ?, ?)');
    // ikat nilai dari parameter fungsi
    mysqli_stmt_bind_param($stmt, 'ssss', $nama, $email, $noHp, $alamat);
    // jalankan perintah simpan data
    $ok = mysqli_stmt_execute($stmt);
    // tutup perintah dan kembalikan status
    mysqli_stmt_close($stmt);
    return $ok;
}

// ambil semua data pelanggan
function ambilSemuaPelanggan($koneksi, $kataKunci = '') {
    // rapikan kata kunci pencarian
    $kataKunci = trim($kataKunci);
    // ambil semua bila tanpa pencarian
    if ($kataKunci === '') {
        // ambil semua baris terurut
        $hasil = mysqli_query($koneksi, 'SELECT * FROM pelanggan ORDER BY id');
        // kosong bila query gagal
        if (!$hasil) {
            return [];
        }
        // kembalikan hasil sebagai array
        return mysqli_fetch_all($hasil, MYSQLI_ASSOC);
    }
    // siapkan pola like pencarian
    $like = '%' . strtr($kataKunci, ['\\' => '\\\\', '%' => '\\%', '_' => '\\_']) . '%';
    // siapkan perintah cari aman
    $stmt = mysqli_prepare($koneksi, 'SELECT * FROM pelanggan WHERE nama LIKE ? ESCAPE \'\\\\\' OR email LIKE ? ESCAPE \'\\\\\' OR no_hp LIKE ? ESCAPE \'\\\\\' OR alamat LIKE ? ESCAPE \'\\\\\' ORDER BY id');
    // ikat pola ke semua kolom
    mysqli_stmt_bind_param($stmt, 'ssss', $like, $like, $like, $like);
    // jalankan pencarian ke database
    $ok = mysqli_stmt_execute($stmt);
    // ambil hasil pencarian bila berhasil
    $hasil = $ok ? mysqli_stmt_get_result($stmt) : false;
    // simpan hasil sebagai array
    $data = $hasil ? mysqli_fetch_all($hasil, MYSQLI_ASSOC) : [];
    // tutup dan kembalikan hasil
    mysqli_stmt_close($stmt);
    return $data;
}

// ambil satu pelanggan berdasar id
function ambilSatuPelanggan($koneksi, $id) {
    // pastikan id berupa angka
    $id = (int)$id;
    // cari baris pelanggan tertentu
    $hasil = mysqli_query($koneksi, "SELECT * FROM pelanggan WHERE id=$id");
    // kembalikan null bila gagal
    if (!$hasil) {
        return null;
    }
    // kembalikan satu baris data
    return mysqli_fetch_assoc($hasil);
}

// ubah data pelanggan berdasar id
function editPelanggan($koneksi, $id, $nama, $email, $noHp, $alamat) {
    // pastikan id berupa angka
    $id = (int)$id;
    // siapkan perintah ubah aman
    $stmt = mysqli_prepare($koneksi, 'UPDATE pelanggan SET nama=?, email=?, no_hp=?, alamat=? WHERE id=?');
    // ikat nilai baru dan id
    mysqli_stmt_bind_param($stmt, 'ssssi', $nama, $email, $noHp, $alamat, $id);
    // jalankan perintah ubah data
    $ok = mysqli_stmt_execute($stmt);
    // tutup dan kembalikan status
    mysqli_stmt_close($stmt);
    return $ok;
}

// hapus pelanggan berdasar id
function hapusPelanggan($koneksi, $id) {
    // pastikan id berupa angka
    $id = (int)$id;
    // hapus baris pelanggan tertentu
    return mysqli_query($koneksi, "DELETE FROM pelanggan WHERE id=$id");
}

// cek email sudah dipakai pelanggan lain
function emailSudahDipakai($koneksi, $email, $kecualiId = null) {
    // siapkan query tanpa pengecualian
    // pilih query kecuali id sendiri
    if ($kecualiId) {
        // pastikan pengecualian berupa angka
        $kecualiId = (int)$kecualiId;
        $stmt = mysqli_prepare($koneksi, 'SELECT id FROM pelanggan WHERE email=? AND id != ?');
        mysqli_stmt_bind_param($stmt, 'si', $email, $kecualiId);
    } else {
        // siapkan query untuk pelanggan baru
        $stmt = mysqli_prepare($koneksi, 'SELECT id FROM pelanggan WHERE email=?');
        mysqli_stmt_bind_param($stmt, 's', $email);
    }
    // jalankan cek email unik
    $ok = mysqli_stmt_execute($stmt);
    // ambil hasil cek email
    $hasil = $ok ? mysqli_stmt_get_result($stmt) : false;
    // true bila ada baris cocok
    $dipakai = $hasil && mysqli_num_rows($hasil) > 0;
    // tutup dan kembalikan status
    mysqli_stmt_close($stmt);
    return $dipakai;
}

// cek no hp sudah dipakai pelanggan lain
function noHpSudahDipakai($koneksi, $noHp, $kecualiId = null) {
    // siapkan query tanpa pengecualian
    if ($kecualiId) {
        // siapkan query kecuali id sendiri
        $kecualiId = (int)$kecualiId;
        $stmt = mysqli_prepare($koneksi, 'SELECT id FROM pelanggan WHERE no_hp=? AND id != ?');
        mysqli_stmt_bind_param($stmt, 'si', $noHp, $kecualiId);
    } else {
        // siapkan query untuk pelanggan baru
        $stmt = mysqli_prepare($koneksi, 'SELECT id FROM pelanggan WHERE no_hp=?');
        mysqli_stmt_bind_param($stmt, 's', $noHp);
    }
    // jalankan cek no hp unik
    $ok = mysqli_stmt_execute($stmt);
    // ambil hasil cek no hp
    $hasil = $ok ? mysqli_stmt_get_result($stmt) : false;
    // true bila ada baris cocok
    $dipakai = $hasil && mysqli_num_rows($hasil) > 0;
    // tutup dan kembalikan status
    mysqli_stmt_close($stmt);
    return $dipakai;
}
