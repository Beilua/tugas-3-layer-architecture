<?php
// DATA LAYER - query CRUD saja. Tidak tahu soal validasi bisnis.
require_once __DIR__ . '/db.php';

function tambahPelanggan($koneksi, $nama, $email, $noHp, $alamat) {
    $stmt = mysqli_prepare($koneksi, 'INSERT INTO pelanggan (nama, email, no_hp, alamat) VALUES (?, ?, ?, ?)');
    mysqli_stmt_bind_param($stmt, 'ssss', $nama, $email, $noHp, $alamat);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function ambilSemuaPelanggan($koneksi) {
    $hasil = mysqli_query($koneksi, 'SELECT * FROM pelanggan ORDER BY id');
    if (!$hasil) {
        return [];
    }
    return mysqli_fetch_all($hasil, MYSQLI_ASSOC);
}

function ambilSatuPelanggan($koneksi, $id) {
    $id = (int)$id;
    $hasil = mysqli_query($koneksi, "SELECT * FROM pelanggan WHERE id=$id");
    if (!$hasil) {
        return null;
    }
    return mysqli_fetch_assoc($hasil);
}

function editPelanggan($koneksi, $id, $nama, $email, $noHp, $alamat) {
    $id = (int)$id;
    $stmt = mysqli_prepare($koneksi, 'UPDATE pelanggan SET nama=?, email=?, no_hp=?, alamat=? WHERE id=?');
    mysqli_stmt_bind_param($stmt, 'ssssi', $nama, $email, $noHp, $alamat, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function hapusPelanggan($koneksi, $id) {
    $id = (int)$id;
    return mysqli_query($koneksi, "DELETE FROM pelanggan WHERE id=$id");
}

function emailSudahDipakai($koneksi, $email, $kecualiId = null) {
    if ($kecualiId) {
        $kecualiId = (int)$kecualiId;
        $stmt = mysqli_prepare($koneksi, 'SELECT id FROM pelanggan WHERE email=? AND id != ?');
        mysqli_stmt_bind_param($stmt, 'si', $email, $kecualiId);
    } else {
        $stmt = mysqli_prepare($koneksi, 'SELECT id FROM pelanggan WHERE email=?');
        mysqli_stmt_bind_param($stmt, 's', $email);
    }
    $ok = mysqli_stmt_execute($stmt);
    $hasil = $ok ? mysqli_stmt_get_result($stmt) : false;
    $dipakai = $hasil && mysqli_num_rows($hasil) > 0;
    mysqli_stmt_close($stmt);
    return $dipakai;
}

function noHpSudahDipakai($koneksi, $noHp, $kecualiId = null) {
    if ($kecualiId) {
        $kecualiId = (int)$kecualiId;
        $stmt = mysqli_prepare($koneksi, 'SELECT id FROM pelanggan WHERE no_hp=? AND id != ?');
        mysqli_stmt_bind_param($stmt, 'si', $noHp, $kecualiId);
    } else {
        $stmt = mysqli_prepare($koneksi, 'SELECT id FROM pelanggan WHERE no_hp=?');
        mysqli_stmt_bind_param($stmt, 's', $noHp);
    }
    $ok = mysqli_stmt_execute($stmt);
    $hasil = $ok ? mysqli_stmt_get_result($stmt) : false;
    $dipakai = $hasil && mysqli_num_rows($hasil) > 0;
    mysqli_stmt_close($stmt);
    return $dipakai;
}
