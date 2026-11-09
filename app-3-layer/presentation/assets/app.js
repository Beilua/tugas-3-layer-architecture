// PRESENTATION LAYER - cuma tampilan + fetch() ke API JSON.
// Tidak ada query SQL / validasi bisnis di sini.
const alamatApi = '../business/api.php';
const form = document.getElementById('formPelanggan');
const isiTabel = document.getElementById('isiTabel');
const status = document.getElementById('status');
const judulForm = document.getElementById('judulForm');
const tombolSimpan = document.getElementById('tombolSimpan');
const tombolBatal = document.getElementById('tombolBatal');

function tampilkanStatus(pesan, modeError) {
    status.textContent = pesan;
    status.hidden = false;
    status.className = modeError ? 'pesan error' : 'pesan';
}

function bersihkanForm() {
    form.reset();
    document.getElementById('id').value = '';
    judulForm.textContent = 'Tambah Pelanggan';
    tombolSimpan.textContent = 'Tambah Pelanggan';
    tombolBatal.hidden = true;
    status.hidden = true;
}

async function muatDaftarPelanggan() {
    const hasil = await fetch(alamatApi);
    const json = await hasil.json();

    // Tanpa guard ini, json.data === null (respons error) membuat
    // .length melempar TypeError dan tabel terkunci di "Memuat data...".
    if (!Array.isArray(json.data)) {
        isiTabel.innerHTML = '<tr><td colspan="5">' + escapeHtml(json.pesan || 'Gagal memuat data.') + '</td></tr>';
        return;
    }

    if (json.data.length === 0) {
        isiTabel.innerHTML = '<tr><td colspan="5">Belum ada data.</td></tr>';
        return;
    }

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
    }).join('');
}

function escapeHtml(teks) {
    const div = document.createElement('div');
    div.textContent = teks;
    return div.innerHTML;
}

async function editPelanggan(id) {
    const hasil = await fetch(alamatApi + '?id=' + id);
    const json = await hasil.json();
    const pelanggan = json.data;
    if (!pelanggan) {
        tampilkanStatus(json.pesan || 'Pelanggan tidak ditemukan.', true);
        return;
    }

    document.getElementById('id').value = pelanggan.id;
    document.getElementById('nama').value = pelanggan.nama;
    document.getElementById('email').value = pelanggan.email;
    document.getElementById('no_hp').value = pelanggan.no_hp;
    document.getElementById('alamat').value = pelanggan.alamat || '';
    judulForm.textContent = 'Edit Pelanggan';
    tombolSimpan.textContent = 'Simpan Perubahan';
    tombolBatal.hidden = false;
    window.scrollTo(0, 0);
}

async function hapusPelanggan(id) {
    if (!confirm('Hapus pelanggan ini?')) {
        return;
    }

    const hasil = await fetch(alamatApi, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    });
    const json = await hasil.json();
    tampilkanStatus(json.pesan, hasil.status >= 400);
    muatDaftarPelanggan();
}

form.addEventListener('submit', async function (event) {
    event.preventDefault();

    const id = document.getElementById('id').value;
    const kirim = {
        nama: document.getElementById('nama').value.trim(),
        email: document.getElementById('email').value.trim(),
        no_hp: document.getElementById('no_hp').value.trim(),
        alamat: document.getElementById('alamat').value.trim()
    };

    const metode = id === '' ? 'POST' : 'PUT';
    if (id !== '') {
        kirim.id = Number(id);
    }

    const hasil = await fetch(alamatApi, {
        method: metode,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(kirim)
    });
    const json = await hasil.json();
    tampilkanStatus(json.pesan, hasil.status >= 400);

    if (hasil.status < 400) {
        bersihkanForm();
        muatDaftarPelanggan();
    }
});

tombolBatal.addEventListener('click', bersihkanForm);

muatDaftarPelanggan();
