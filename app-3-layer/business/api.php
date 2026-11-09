<?php
// BUSINESS LOGIC LAYER - terima request JSON, validasi aturan bisnis,
// panggil data layer, kembalikan JSON. Tidak ada HTML di sini.
require_once __DIR__ . '/../data/pelanggan.php';

header('Content-Type: application/json; charset=utf-8');

function kirimJson($status, $data = null, $pesan = '') {
    http_response_code($status);
    echo json_encode([
        'status' => $status,
        'pesan' => $pesan,
        'data' => $data
    ]);
    exit;
}

function bacaBodyJson() {
    $isi = file_get_contents('php://input');
    if ($isi === '' || $isi === false) {
        return [];
    }
    $decode = json_decode($isi, true);
    return is_array($decode) ? $decode : [];
}

function validasiData($koneksi, $input, $kecualiId = null) {
    $nama = isset($input['nama']) ? trim($input['nama']) : '';
    $email = isset($input['email']) ? trim($input['email']) : '';
    $noHp = isset($input['no_hp']) ? trim($input['no_hp']) : '';
    $alamat = isset($input['alamat']) ? trim($input['alamat']) : '';

    if ($nama === '' || $email === '' || $noHp === '') {
        return [false, 'Nama, email, dan no_hp wajib diisi.', null];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [false, 'Format email tidak valid.', null];
    }

    if (emailSudahDipakai($koneksi, $email, $kecualiId)) {
        return [false, 'Email sudah dipakai pelanggan lain.', null];
    }

    if (noHpSudahDipakai($koneksi, $noHp, $kecualiId)) {
        return [false, 'No HP sudah dipakai pelanggan lain.', null];
    }

    return [true, '', compact('nama', 'email', 'noHp', 'alamat')];
}

$koneksi = buatKoneksi();
if (!$koneksi) {
    kirimJson(500, null, 'Koneksi database gagal.');
}

$metode = $_SERVER['REQUEST_METHOD'];

if ($metode === 'GET') {
    if (isset($_GET['id'])) {
        $pelanggan = ambilSatuPelanggan($koneksi, $_GET['id']);
        if (!$pelanggan) {
            kirimJson(404, null, 'Pelanggan tidak ditemukan.');
        }
        kirimJson(200, $pelanggan, 'Berhasil mengambil data.');
    }
    kirimJson(200, ambilSemuaPelanggan($koneksi), 'Berhasil mengambil data.');
}

if ($metode === 'POST') {
    $input = bacaBodyJson();
    list($ok, $pesan, $dataBersih) = validasiData($koneksi, $input);

    if (!$ok) {
        kirimJson(400, null, $pesan);
    }

    $berhasil = tambahPelanggan(
        $koneksi,
        $dataBersih['nama'],
        $dataBersih['email'],
        $dataBersih['noHp'],
        $dataBersih['alamat']
    );

    if (!$berhasil) {
        kirimJson(500, null, 'Gagal menambah pelanggan: ' . mysqli_error($koneksi));
    }

    kirimJson(201, null, 'Pelanggan berhasil ditambahkan.');
}

if ($metode === 'PUT' || $metode === 'PATCH') {
    $input = bacaBodyJson();
    $id = isset($input['id']) ? (int)$input['id'] : 0;

    if ($id === 0) {
        kirimJson(400, null, 'ID pelanggan wajib ada.');
    }

    if (!ambilSatuPelanggan($koneksi, $id)) {
        kirimJson(404, null, 'Pelanggan tidak ditemukan.');
    }

    list($ok, $pesan, $dataBersih) = validasiData($koneksi, $input, $id);

    if (!$ok) {
        kirimJson(400, null, $pesan);
    }

    $berhasil = editPelanggan(
        $koneksi,
        $id,
        $dataBersih['nama'],
        $dataBersih['email'],
        $dataBersih['noHp'],
        $dataBersih['alamat']
    );

    if (!$berhasil) {
        kirimJson(500, null, 'Gagal mengedit pelanggan: ' . mysqli_error($koneksi));
    }

    kirimJson(200, null, 'Pelanggan berhasil diedit.');
}

if ($metode === 'DELETE') {
    $input = bacaBodyJson();
    $id = isset($input['id']) ? (int)$input['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

    if ($id === 0) {
        kirimJson(400, null, 'ID pelanggan wajib ada.');
    }

    if (!ambilSatuPelanggan($koneksi, $id)) {
        kirimJson(404, null, 'Pelanggan tidak ditemukan.');
    }

    if (!hapusPelanggan($koneksi, $id)) {
        kirimJson(500, null, 'Gagal menghapus pelanggan: ' . mysqli_error($koneksi));
    }

    kirimJson(200, null, 'Pelanggan berhasil dihapus.');
}

kirimJson(405, null, 'Method tidak didukung.');
