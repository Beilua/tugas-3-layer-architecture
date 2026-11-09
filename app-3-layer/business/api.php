<?php
// BUSINESS LOGIC LAYER - terima request JSON, validasi aturan bisnis,
// panggil data layer, kembalikan JSON. Tidak ada HTML di sini.
require_once __DIR__ . '/../data/pelanggan.php';

// paksa semua respons berbentuk json
header('Content-Type: application/json; charset=utf-8');

// kirim respons json ke klien
function kirimJson($status, $data = null, $pesan = '') {
    // set kode status http respons
    http_response_code($status);
    // tampilkan status pesan dan data
    echo json_encode([
        'status' => $status,
        'pesan' => $pesan,
        'data' => $data
    ]);
    // hentikan proses setelah kirim
    exit;
}

// baca isi body json request
function bacaBodyJson() {
    // ambil teks mentah dari request
    $isi = file_get_contents('php://input');
    // kosong bila body tidak ada
    if ($isi === '' || $isi === false) {
        return [];
    }
    // ubah teks jadi array php
    $decode = json_decode($isi, true);
    // pastikan hasil berupa array
    return is_array($decode) ? $decode : [];
}

// periksa aturan bisnis data pelanggan
function validasiData($koneksi, $input, $kecualiId = null) {
    // ambil lalu rapikan nama pelanggan
    $nama = isset($input['nama']) ? trim($input['nama']) : '';
    // ambil lalu rapikan email pelanggan
    $email = isset($input['email']) ? trim($input['email']) : '';
    // ambil lalu rapikan no hp pelanggan
    $noHp = isset($input['no_hp']) ? trim($input['no_hp']) : '';
    // ambil lalu rapikan alamat pelanggan
    $alamat = isset($input['alamat']) ? trim($input['alamat']) : '';

    // tolak bila field wajib kosong
    if ($nama === '' || $email === '' || $noHp === '') {
        return [false, 'Nama, email, dan no_hp wajib diisi.', null];
    }

    // tolak bila format email salah
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [false, 'Format email tidak valid.', null];
    }

    // tolak bila email sudah dipakai
    if (emailSudahDipakai($koneksi, $email, $kecualiId)) {
        return [false, 'Email sudah dipakai pelanggan lain.', null];
    }

    // tolak bila no hp sudah dipakai
    if (noHpSudahDipakai($koneksi, $noHp, $kecualiId)) {
        return [false, 'No HP sudah dipakai pelanggan lain.', null];
    }

    // lolos semua pemeriksaan bisnis
    return [true, '', compact('nama', 'email', 'noHp', 'alamat')];
}

// buat koneksi database untuk request
$koneksi = buatKoneksi();
// gagal bila koneksi tidak berhasil
if (!$koneksi) {
    kirimJson(500, null, 'Koneksi database gagal.');
}

// baca metode http dari request
$metode = $_SERVER['REQUEST_METHOD'];

// tangani permintaan ambil data
if ($metode === 'GET') {
    // ambil satu pelanggan bila ada id
    if (isset($_GET['id'])) {
        // cari pelanggan berdasar id
        $pelanggan = ambilSatuPelanggan($koneksi, $_GET['id']);
        // gagal bila pelanggan tidak ditemukan
        if (!$pelanggan) {
            kirimJson(404, null, 'Pelanggan tidak ditemukan.');
        }
        // kirim data pelanggan terpilih
        kirimJson(200, $pelanggan, 'Berhasil mengambil data.');
    }
    // kirim daftar pelanggan ke klien
    kirimJson(200, ambilSemuaPelanggan($koneksi, $_GET['cari'] ?? ''), 'Berhasil mengambil data.');
}

// tangani permintaan tambah pelanggan
if ($metode === 'POST') {
    // baca body json dari klien
    $input = bacaBodyJson();
    // periksa data sebelum simpan
    list($ok, $pesan, $dataBersih) = validasiData($koneksi, $input);

    // gagal bila validasi tidak lolos
    if (!$ok) {
        kirimJson(400, null, $pesan);
    }

    // simpan pelanggan baru ke database
    $berhasil = tambahPelanggan(
        $koneksi,
        $dataBersih['nama'],
        $dataBersih['email'],
        $dataBersih['noHp'],
        $dataBersih['alamat']
    );

    // gagal bila simpan ke database gagal
    if (!$berhasil) {
        kirimJson(500, null, 'Gagal menambah pelanggan: ' . mysqli_error($koneksi));
    }

    // beri tahu pelanggan berhasil ditambahkan
    kirimJson(201, null, 'Pelanggan berhasil ditambahkan.');
}

// tangani permintaan ubah pelanggan
if ($metode === 'PUT' || $metode === 'PATCH') {
    // baca body json dari klien
    $input = bacaBodyJson();
    // ambil id dari body request
    $id = isset($input['id']) ? (int)$input['id'] : 0;

    // tolak bila id tidak ada
    if ($id === 0) {
        kirimJson(400, null, 'ID pelanggan wajib ada.');
    }

    // tolak bila pelanggan tidak ditemukan
    if (!ambilSatuPelanggan($koneksi, $id)) {
        kirimJson(404, null, 'Pelanggan tidak ditemukan.');
    }

    // periksa data sebelum diubah
    list($ok, $pesan, $dataBersih) = validasiData($koneksi, $input, $id);

    // gagal bila validasi tidak lolos
    if (!$ok) {
        kirimJson(400, null, $pesan);
    }

    // ubah data pelanggan di database
    $berhasil = editPelanggan(
        $koneksi,
        $id,
        $dataBersih['nama'],
        $dataBersih['email'],
        $dataBersih['noHp'],
        $dataBersih['alamat']
    );

    // gagal bila ubah ke database gagal
    if (!$berhasil) {
        kirimJson(500, null, 'Gagal mengedit pelanggan: ' . mysqli_error($koneksi));
    }

    // beri tahu pelanggan berhasil diedit
    kirimJson(200, null, 'Pelanggan berhasil diedit.');
}

// tangani permintaan hapus pelanggan
if ($metode === 'DELETE') {
    // baca body json dari klien
    $input = bacaBodyJson();
    // ambil id dari body atau query
    $id = isset($input['id']) ? (int)$input['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

    // tolak bila id tidak ada
    if ($id === 0) {
        kirimJson(400, null, 'ID pelanggan wajib ada.');
    }

    // tolak bila pelanggan tidak ditemukan
    if (!ambilSatuPelanggan($koneksi, $id)) {
        kirimJson(404, null, 'Pelanggan tidak ditemukan.');
    }

    // hapus pelanggan dari database
    if (!hapusPelanggan($koneksi, $id)) {
        kirimJson(500, null, 'Gagal menghapus pelanggan: ' . mysqli_error($koneksi));
    }

    // beri tahu pelanggan berhasil dihapus
    kirimJson(200, null, 'Pelanggan berhasil dihapus.');
}

// tolak metode http yang tidak dikenal
kirimJson(405, null, 'Method tidak didukung.');
