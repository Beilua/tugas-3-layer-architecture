<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3-Layer — Customer Management</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<a class="skip-link" href="#formPelanggan">Lewati ke formulir pelanggan</a>
<div class="app">
    <div class="bg-blobs">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <header class="header">
        <h1><svg class="leaf-badge" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="#fff" d="M12 2c3.9 0 7 3.1 7 7s-3.1 7-7 7c-3.9 0-7-3.1-7-7s3.1-7 7-7m0 2c-2.8 0-5 2.2-5 5s2.2 5 5 5c2.8 0 5-2.2 5-5s-2.2-5-5-5"/></svg>Customer Management</h1>
        <p class="lead">Manajemen data pelanggan. Presentasi di halaman ini, bisnis lewat <code>api.php</code> (JSON), data di <code>data/pelanggan.php</code>.</p>
    </header>

    <main class="grid">
        <section class="card">
            <h2 id="judulForm">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="18" y1="8" x2="23" y2="13"/><line x1="18" y1="13" x2="23" y2="8"/></svg>
                Tambah Pelanggan
            </h2>
            <p class="section-lead">Isi data pelanggan baru, atau ubah data yang sudah ada.</p>
            <p id="status" class="pesan" role="status" aria-live="polite" hidden>
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                <span id="statusTeks"></span>
            </p>
            <form id="formPelanggan">
                <input type="hidden" id="id" name="id" value="">

                <label for="nama">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Nama <span style="font-weight:400; opacity:.85;">*</span>
                </label>
                <input type="text" id="nama" name="nama" required placeholder="Masukkan nama lengkap">

                <label for="email">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    Email <span style="font-weight:400; opacity:.85;">*</span>
                </label>
                <input type="email" id="email" name="email" required placeholder="nama@contoh.com">

                <label for="no_hp">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    No HP <span style="font-weight:400; opacity:.85;">*</span>
                </label>
                <input type="text" id="no_hp" name="no_hp" required placeholder="08123456789">

                <label for="alamat">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    Alamat
                </label>
                <textarea id="alamat" name="alamat" placeholder="Alamat lengkap"></textarea>

                <div class="actions">
                    <button type="submit" id="tombolSimpan" class="btn btn--primary">Simpan Pelanggan</button>
                    <button type="button" id="tombolBatal" class="btn btn--ghost" hidden>Batal</button>
                </div>
            </form>
        </section>

        <section class="card">
            <h2>
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                Daftar Pelanggan
            </h2>
            <div class="table-wrap">
                <table>
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
                        <tr><td colspan="5">Memuat data…</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
<script src="assets/app.js"></script>
</body>
</html>
