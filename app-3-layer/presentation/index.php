<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3-Layer - Customer Management</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<a class="skip-link" href="#formPelanggan">Lewati ke formulir pelanggan</a>
<div class="app">
    <header class="header">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <h1>Customer Management</h1>
        <p class="lead">Manajemen data pelanggan — presentasi di halaman ini, bisnis lewat api.php (JSON), data di data/pelanggan.php.</p>
    </header>

    <main class="grid">
        <section class="card">
            <h2 id="judulForm">Tambah Pelanggan</h2>
            <p id="status" class="pesan" role="status" aria-live="polite" hidden></p>
            <form id="formPelanggan">
                <input type="hidden" id="id" name="id" value="">

                <label for="nama">Nama *</label>
                <input type="text" id="nama" name="nama" required placeholder="Masukkan nama lengkap">

                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required placeholder="nama@contoh.com">

                <label for="no_hp">No HP *</label>
                <input type="text" id="no_hp" name="no_hp" required placeholder="08123456789">

                <label for="alamat">Alamat</label>
                <textarea id="alamat" name="alamat" placeholder="Alamat lengkap"></textarea>

                <div class="actions">
                    <button type="submit" id="tombolSimpan" class="btn btn--primary">Simpan Pelanggan</button>
                    <button type="button" id="tombolBatal" class="btn btn--ghost" hidden>Batal</button>
                </div>
            </form>
        </section>

        <section class="card">
            <h2>Daftar Pelanggan</h2>
            <div class="table-wrap">
                <table>
                    <caption>Daftar pelanggan yang tersimpan</caption>
                    <thead>
                        <tr>
                            <th scope="col">Nama</th>
                            <th scope="col">Email</th>
                            <th scope="col">No HP</th>
                            <th scope="col">Alamat</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="isiTabel">
                        <tr><td colspan="5">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
<script src="assets/app.js"></script>
</body>
</html>
