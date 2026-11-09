// PRESENTATION LAYER - cuma tampilan + fetch() ke API JSON.
// Tidak ada query SQL / validasi bisnis di sini.
// simpan alamat api business layer
const alamatApi = '../business/api.php';
// ambil elemen formulir pelanggan
const form = document.getElementById('formPelanggan');
// ambil elemen isi tabel pelanggan
const isiTabel = document.getElementById('isiTabel');
// ambil elemen kotak notifikasi
const notif = document.getElementById('notif');
// ambil elemen teks notifikasi
const notifTeks = document.getElementById('notifTeks');
// ambil elemen ikon notifikasi
const notifIkon = document.getElementById('notifIkon');
// ambil tombol tutup notifikasi
const notifTutup = document.getElementById('notifTutup');
// ambil judul formulir pelanggan
const judulForm = document.getElementById('judulForm');
// ambil tombol simpan pelanggan
const tombolSimpan = document.getElementById('tombolSimpan');
// ambil tombol batal edit
const tombolBatal = document.getElementById('tombolBatal');
// ambil elemen formulir pencarian
const formPencarian = document.getElementById('formPencarian');
// ambil kotak input kata kunci
const kataKunci = document.getElementById('kataKunci');
// ambil tombol reset pencarian
const tombolReset = document.getElementById('tombolReset');

// simpan timer hidup notifikasi
let timerNotif = null;
// simpan kata kunci pencarian aktif
let kataKunciAktif = '';
// simpan timer tunda pencarian
let timerCari = null;

// ikon svg untuk notif sukses
const IKON_SUKSES = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
// ikon svg untuk notif gagal
const IKON_GAGAL = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';

// sembunyikan kotak notifikasi status
function sembunyikanStatus() {
    // susutkan kotak notifikasi
    notif.hidden = true;
    // hapus kelas animasi masuk
    notif.classList.remove('notif--masuk');
    // hentikan timer hidup notifikasi
    if (timerNotif) {
        clearTimeout(timerNotif);
        timerNotif = null;
    }
}

// tampilkan pesan status ke pengguna
function tampilkanStatus(pesan, modeError) {
    // isi teks pesan notifikasi
    notifTeks.textContent = pesan;
    // pilih ikon sukses atau gagal
    notifIkon.innerHTML = modeError ? IKON_GAGAL : IKON_SUKSES;
    // terapkan gaya sukses atau gagal
    notif.className = 'notif' + (modeError ? ' error' : '');
    // munculkan kotak notifikasi
    notif.hidden = false;
    // Paksa reflow supaya animasi slide-in jalan lagi tiap notif baru.
    void notif.offsetWidth;
    // jalankan animasi notifikasi masuk
    notif.classList.add('notif--masuk');
    // hentikan timer lama bila ada
    if (timerNotif) {
        clearTimeout(timerNotif);
    }
    // sembunyikan otomatis setelah empat detik
    timerNotif = setTimeout(sembunyikanStatus, 4000);
}

// tutup notif saat tombol diklik
notifTutup.addEventListener('click', sembunyikanStatus);

// kosongkan formulir ke mode tambah
function bersihkanForm() {
    // reset semua isian formulir
    form.reset();
    // kosongkan id tersembunyi
    document.getElementById('id').value = '';
    // kembalikan judul ke tambah
    judulForm.childNodes[judulForm.childNodes.length - 1].textContent = ' Tambah Pelanggan';
    // kembalikan teks tombol simpan
    tombolSimpan.textContent = 'Tambah Pelanggan';
    // sembunyikan tombol batal edit
    tombolBatal.hidden = true;
    // sembunyikan notif yang ada
    sembunyikanStatus();
}

// muat ulang daftar pelanggan dari api
async function muatDaftarPelanggan() {
    // susun url sesuai kata kunci aktif
    const url = kataKunciAktif !== '' ? alamatApi + '?cari=' + encodeURIComponent(kataKunciAktif) : alamatApi;
    // minta daftar pelanggan ke api
    const hasil = await fetch(url);
    // ubah respons jadi json
    const json = await hasil.json();

    // Tanpa guard ini, json.data === null (respons error) membuat
    // .length melempar TypeError dan tabel terkunci di "Memuat data...".
    // tampilkan pesan gagal bila data bukan array
    if (!Array.isArray(json.data)) {
        isiTabel.innerHTML = '<tr><td colspan="5">' + escapeHtml(json.pesan || 'Gagal memuat data.') + '</td></tr>';
        return;
    }

    // tampilkan pesan kosong bila daftar kosong
    if (json.data.length === 0) {
        isiTabel.innerHTML = '<tr><td colspan="5">' + (kataKunciAktif !== '' ? 'Tidak ada pelanggan yang cocok dengan &ldquo;' + escapeHtml(kataKunciAktif) + '&rdquo;.' : 'Belum ada data.') + '</td></tr>';
        return;
    }

    // ubah tiap pelanggan jadi baris tabel
    isiTabel.innerHTML = json.data.map(function (pelanggan) {
        return '<tr>'
            + '<td>' + escapeHtml(pelanggan.nama) + '</td>'
            + '<td>' + escapeHtml(pelanggan.email) + '</td>'
            + '<td>' + escapeHtml(pelanggan.no_hp) + '</td>'
            + '<td>' + escapeHtml(pelanggan.alamat || '') + '</td>'
            + '<td>'
            + '<a href="#" onclick="editPelanggan(' + pelanggan.id + '); return false;">Edit</a>'
            + ' | '
            + '<a href="#" onclick="hapusPelanggan(' + pelanggan.id + '); return false;">Hapus</a>'
            + '</td>'
            + '</tr>';
    // gabung semua baris jadi satu html
    }).join('');
}

// ubah teks agar aman ditampilkan html
function escapeHtml(teks) {
    // buat elemen div sementara
    const div = document.createElement('div');
    // isi teks mentah ke elemen
    div.textContent = teks;
    // kembalikan html yang sudah aman
    return div.innerHTML;
}

// isi formulir untuk edit pelanggan
async function editPelanggan(id) {
    // minta detail pelanggan ke api
    const hasil = await fetch(alamatApi + '?id=' + id);
    // ubah respons jadi json
    const json = await hasil.json();
    // ambil data pelanggan dari respons
    const pelanggan = json.data;
    // beri tahu bila data tidak ditemukan
    if (!pelanggan) {
        tampilkanStatus(json.pesan || 'Pelanggan tidak ditemukan.', true);
        return;
    }

    // isi id ke field tersembunyi
    document.getElementById('id').value = pelanggan.id;
    // isi nama ke kotak input
    document.getElementById('nama').value = pelanggan.nama;
    // isi email ke kotak input
    document.getElementById('email').value = pelanggan.email;
    // isi no hp ke kotak input
    document.getElementById('no_hp').value = pelanggan.no_hp;
    // isi alamat ke kotak textarea
    document.getElementById('alamat').value = pelanggan.alamat || '';
    // ubah judul ke mode edit
    judulForm.childNodes[judulForm.childNodes.length - 1].textContent = ' Edit Pelanggan';
    // ubah teks tombol jadi simpan
    tombolSimpan.textContent = 'Simpan Perubahan';
    // tampilkan tombol batal edit
    tombolBatal.hidden = false;
    // geser halaman ke atas
    window.scrollTo(0, 0);
}

// hapus pelanggan lewat api
async function hapusPelanggan(id) {
    // tanya konfirmasi sebelum menghapus
    if (!confirm('Hapus pelanggan ini?')) {
        return;
    }

    // kirim perintah hapus ke api
    const hasil = await fetch(alamatApi, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    });
    // ubah respons jadi json
    const json = await hasil.json();
    // tampilkan hasil hapus ke pengguna
    tampilkanStatus(json.pesan, hasil.status >= 400);
    // muat ulang daftar pelanggan
    muatDaftarPelanggan();
}

// kirim formulir tambah atau edit
form.addEventListener('submit', async function (event) {
    // cegah muat ulang halaman
    event.preventDefault();

    // ambil id dari field tersembunyi
    const id = document.getElementById('id').value;
    // kumpulkan isian formulir pelanggan
    const kirim = {
        nama: document.getElementById('nama').value.trim(),
        email: document.getElementById('email').value.trim(),
        no_hp: document.getElementById('no_hp').value.trim(),
        alamat: document.getElementById('alamat').value.trim()
    };

    // pilih metode post atau put
    const metode = id === '' ? 'POST' : 'PUT';
    // sertakan id bila mode edit
    if (id !== '') {
        kirim.id = Number(id);
    }

    // kirim data ke api
    const hasil = await fetch(alamatApi, {
        method: metode,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(kirim)
    });
    // ubah respons jadi json
    const json = await hasil.json();

    // berhasil bila status di atas empat ratus
    if (hasil.status < 400) {
        // Reset dulu, baru tampilkan pesan — kalau dibalik, bersihkanForm()
        // menyembunyikan status yang baru saja ditampilkan.
        // kosongkan formulir setelah tersimpan
        bersihkanForm();
        // tampilkan pesan sukses ke pengguna
        tampilkanStatus(json.pesan, false);
        // muat ulang daftar pelanggan
        muatDaftarPelanggan();
    }
    else {
        // tampilkan pesan gagal ke pengguna
        tampilkanStatus(json.pesan, true);
    }
});

// kosongkan formulir saat tombol batal diklik
tombolBatal.addEventListener('click', bersihkanForm);

// terapkan kata kunci lalu muat ulang
function terapkanPencarian() {
    // simpan kata kunci aktif dari input
    kataKunciAktif = kataKunci.value.trim();
    // tampilkan tombol reset bila ada kata kunci
    tombolReset.hidden = kataKunciAktif === '';
    // muat ulang daftar pelanggan
    muatDaftarPelanggan();
}

// cari saat formulir pencarian dikirim
formPencarian.addEventListener('submit', function (event) {
    // cegah muat ulang halaman
    event.preventDefault();
    // terapkan pencarian yang dikirim
    terapkanPencarian();
});

// tunda pencarian otomatis saat mengetik
kataKunci.addEventListener('input', function () {
    // hentikan timer ketikan lama
    if (timerCari) {
        clearTimeout(timerCari);
    }
    // cari ulang tiga ratus milidetik
    timerCari = setTimeout(terapkanPencarian, 300);
});

// kosongkan pencarian saat tombol reset diklik
tombolReset.addEventListener('click', function () {
    // kosongkan kotak kata kunci
    kataKunci.value = '';
    // terapkan pencarian kosong
    terapkanPencarian();
    // kembalikan fokus ke input
    kataKunci.focus();
});

// muat daftar pelanggan saat halaman dibuka
muatDaftarPelanggan();
