<?php
// NON-LAYERED: tampilan, logika bisnis, dan query database semua di satu file.
// Ini contoh pendekatan monolitik sederhana (sesuai ketentuan tugas).
// Tanpa ini, PHP 8+ melempar mysqli_sql_exception saat koneksi/query gagal
// dan halaman menampilkan Internal Server Error (500), bukan pesan di bawah.
mysqli_report(MYSQLI_REPORT_OFF);
$koneksi = mysqli_connect('localhost', 'root', '', 'db_customer');

if (!$koneksi) {
    die('Koneksi gagal: ' . mysqli_connect_error());
}

$pesan = '';
$dataEdit = null;
$daftarPelanggan = [];

// Tambah pelanggan (form dikirim lewat POST)
if (isset($_POST['tambah'])) {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $noHp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    if ($nama === '' || $email === '' || $noHp === '') {
        $pesan = 'Nama, email, dan no_hp wajib diisi.';
    }
    else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan = 'Format email tidak valid.';
    }
    else {
        $stmt = mysqli_prepare($koneksi, 'INSERT INTO pelanggan (nama, email, no_hp, alamat) VALUES (?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'ssss', $nama, $email, $noHp, $alamat);
        if (mysqli_stmt_execute($stmt)) {
            $pesan = 'Pelanggan berhasil ditambahkan.';
        }
        else {
            $pesan = 'Gagal menambah: ' . mysqli_error($koneksi);
        }
        mysqli_stmt_close($stmt);
    }
}

// Edit pelanggan (form dikirim lewat POST dengan id)
if (isset($_POST['edit'])) {
    $id = (int)($_POST['id'] ?? 0);
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $noHp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    if ($nama === '' || $email === '' || $noHp === '') {
        $pesan = 'Nama, email, dan no_hp wajib diisi.';
    }
    else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan = 'Format email tidak valid.';
    }
    else {
        $stmt = mysqli_prepare($koneksi, 'UPDATE pelanggan SET nama=?, email=?, no_hp=?, alamat=? WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'ssssi', $nama, $email, $noHp, $alamat, $id);
        if (mysqli_stmt_execute($stmt)) {
            $pesan = 'Pelanggan berhasil diedit.';
        }
        else {
            $pesan = 'Gagal mengedit: ' . mysqli_error($koneksi);
        }
        mysqli_stmt_close($stmt);
    }
}

// Hapus pelanggan (lewat GET)
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $query = "DELETE FROM pelanggan WHERE id=$id";
    if (mysqli_query($koneksi, $query)) {
        $pesan = 'Pelanggan berhasil dihapus.';
    }
    else {
        $pesan = 'Gagal menghapus: ' . mysqli_error($koneksi);
    }
}

// Ambil data yang mau diedit (lewat GET)
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $hasil = mysqli_query($koneksi, "SELECT * FROM pelanggan WHERE id=$id");
    if ($hasil) {
        $dataEdit = mysqli_fetch_assoc($hasil);
    }
}

// Ambil semua data untuk ditampilkan
$hasil = mysqli_query($koneksi, 'SELECT * FROM pelanggan ORDER BY id');
if ($hasil) {
    $daftarPelanggan = mysqli_fetch_all($hasil, MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Non-Layered - Customer Management</title>
    <style>
        /* Accessible & Ethical + Organic Biophilic — reconstructed UI, native CSS only. */
        :root {
            --forest: #1a6b2e;
            --forest-dark: #124a24;
            --forest-light: #e9f5ec;
            --earth: #8b4513;
            --earth-light: #f0e6d2;
            --cream: #f5f3e0;
            --sky: #0e7490;
            --surface: #fffef9;
            --ink: #1c2420;
            --border: #d6cfb8;
            --ring: #0e7490;
            --radius-card: 34px;
            --radius-input: 16px;
            --radius-btn: 20px;
            --shadow-soft: 0 20px 44px rgba(28, 36, 32, 0.10);
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 16px;
            line-height: 1.55;
            margin: 0;
            padding: 0;
            color: var(--ink);
            background: radial-gradient(1200px 800px at 60% -10%, #eef3e6 0%, var(--cream) 52%, #fdfbf4 100%);
            min-height: 100vh;
        }
        .skip-link {
            position: absolute;
            left: -9999px;
            top: auto;
            width: 1px;
            height: 1px;
            overflow: hidden;
        }
        .skip-link:focus {
            position: static;
            width: auto;
            height: auto;
            padding: 10px 14px;
            background: #fff;
            color: var(--forest-dark);
            font-weight: 800;
            border: 3px solid var(--ring);
            border-radius: var(--radius-input);
            z-index: 10000;
        }
        .app {
            max-width: 1150px;
            margin: 44px auto;
            padding: 0 20px 96px;
            position: relative;
        }
        .header {
            position: relative;
            padding: 60px 0 72px;
            color: #fff;
            text-align: center;
            border-bottom-left-radius: 58% 22%;
            border-bottom-right-radius: 38% 22%;
            overflow: hidden;
        }
        .header::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--forest) 0%, var(--forest-dark) 55%, #0c351d 100%);
            z-index: 0;
        }
        .header .blob {
            position: absolute;
            z-index: 0;
            filter: blur(60px);
            opacity: 0.5;
        }
        .header .blob-1 {
            top: -14%;
            left: -8%;
            width: 52vw;
            height: 52vw;
            background: var(--forest-dark);
            border-radius: 62% 38% 42% 58% / 55% 42% 58% 45%;
        }
        .header .blob-2 {
            top: 12%;
            right: -6%;
            width: 40vw;
            height: 40vw;
            background: var(--earth-light);
            border-radius: 44% 66% 56% 44% / 62% 48% 52% 38%;
        }
        .header > * { position: relative; z-index: 1; }
        .header h1 { margin: 0 0 8px; font-size: 2.1rem; letter-spacing: 0.5px; font-weight: 800; }
        .header .lead { margin: 0; font-size: 1rem; color: rgba(255, 255, 255, 0.92); }

        .grid { display: grid; gap: 26px; grid-template-columns: 1fr; }
        @media (min-width: 768px) { .grid { grid-template-columns: 440px 1fr; } }

        .card {
            position: relative;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-card);
            box-shadow: var(--shadow-soft);
            overflow: hidden;
        }
        .card__blob {
            position: absolute;
            top: -40px;
            right: -40px;
            width: 170px;
            height: 170px;
            background: var(--forest-light);
            filter: blur(48px);
            border-radius: 56% 44% 62% 38% / 52% 60% 40% 48%;
            z-index: 0;
        }
        .card > header, .card > h2, .card > form, .card > .table-wrap, .card > .pesan {
            position: relative; z-index: 1;
        }
        .card h2 { margin: 0 0 16px; font-size: 1.3rem; color: var(--forest-dark); font-weight: 800; letter-spacing: 0.3px; }

        form { display: flex; flex-direction: column; gap: 8px; padding: 30px 28px 26px; }
        label { display: block; font-weight: 800; font-size: 0.88rem; color: var(--forest-dark); margin-top: 12px; letter-spacing: 0.2px; }
        input, textarea { width: 100%; min-height: 48px; padding: 12px 14px; font: inherit; color: var(--ink); background: #fff; border: 2px solid var(--border); border-radius: var(--radius-input); }
        textarea { min-height: 100px; resize: vertical; }
        input:focus-visible, textarea:focus-visible { border-color: var(--ring); outline: 3px solid var(--ring); outline-offset: 2px; }
        .actions { display: flex; gap: 12px; margin-top: 22px; }

        .btn {
            flex: 1; min-height: 48px; font: inherit; font-weight: 800; cursor: pointer;
            border-radius: var(--radius-btn); border: none; letter-spacing: 0.4px;
            transition: transform 120ms ease, box-shadow 120ms ease, background 120ms ease;
        }
        .btn:active { transform: translateY(1px); }
        .btn--primary { background: var(--forest); color: #fff; box-shadow: 0 8px 18px rgba(26, 107, 46, 0.40); }
        .btn--primary:hover { background: var(--forest-dark); box-shadow: 0 12px 24px rgba(26, 107, 46, 0.50); }
        .btn--ghost { background: var(--earth-light); color: var(--earth); border: 2px solid var(--earth-light); }
        .btn--ghost:hover { background: var(--earth); color: #fff; }

        .pesan {
            margin: 0 0 22px; padding: 14px 18px; border-radius: var(--radius-input);
            border-left: 6px solid var(--forest); background: var(--forest-light);
            color: var(--forest-dark); font-weight: 800;
        }
        .pesan.error { border-left-color: #991b1b; background: #fee2e2; color: #7f1d1d; }

        .table-wrap { overflow: auto; border-radius: 20px; border: 1px solid var(--border); margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; min-width: 620px; position: relative; z-index: 1; }
        caption { caption-side: top; text-align: left; font-weight: 800; color: var(--forest-dark); padding: 14px 20px 6px; font-size: 1rem; letter-spacing: 0.4px; position: relative; z-index: 1; }
        thead th { background: var(--forest-light); color: var(--forest-dark); font-weight: 800; text-align: left; padding: 12px 16px; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.7px; border-bottom: 3px solid var(--border); }
        tbody td { padding: 12px 16px; border-bottom: 1px solid var(--border); vertical-align: middle; font-size: 0.94rem; }
        tbody tr:nth-child(even) { background: #faf8f1; }
        tbody tr:hover { background: var(--forest-light); }
        td a { color: var(--forest-dark); font-weight: 800; text-decoration: none; text-underline-offset: 3px; display: inline-block; padding: 6px 4px; }
        td a:hover { color: var(--earth); text-decoration: underline; }

        @media (max-width: 768px) {
            .app { margin: 28px auto; padding: 0 14px 64px; }
            .header { padding: 44px 0 52px; border-bottom-left-radius: 44% 16%; border-bottom-right-radius: 28% 16%; }
            .header h1 { font-size: 1.6rem; }
            .card__blob { width: 130px; height: 130px; top: -26px; right: -26px; }
            .actions { flex-direction: column; }
        }
        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; animation: none !important; }
            html { scroll-behavior: auto; }
        }
    </style>
</head>
<body>
<a class="skip-link" href="#formPelanggan">Lewati ke formulir pelanggan</a>
<div class="app">
    <header class="header">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <h1>Customer Management</h1>
        <p class="lead">Manajemen data pelanggan — semua (tampilan + bisnis + database) dalam satu file non-layered.</p>
    </header>

    <main class="grid">
        <section class="card">
            <h2 id="judulForm"><?php echo $dataEdit ? 'Edit Pelanggan' : 'Tambah Pelanggan'; ?></h2>
            <?php if ($pesan !== '') { ?>
                <p class="pesan<?php echo strpos($pesan, 'Gagal') === 0 ? ' error' : ''; ?>" role="status" aria-live="polite"><?php echo htmlspecialchars($pesan); ?></p>
            <?php } ?>
            <form method="post" id="formPelanggan">
                <?php if ($dataEdit) { ?>
                    <input type="hidden" name="id" value="<?php echo $dataEdit['id']; ?>">
                <?php } ?>
                <label for="nama">Nama *</label>
                <input type="text" id="nama" name="nama" required value="<?php echo $dataEdit ? htmlspecialchars($dataEdit['nama']) : ''; ?>" placeholder="Masukkan nama lengkap">

                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required value="<?php echo $dataEdit ? htmlspecialchars($dataEdit['email']) : ''; ?>" placeholder="nama@contoh.com">

                <label for="no_hp">No HP *</label>
                <input type="text" id="no_hp" name="no_hp" required value="<?php echo $dataEdit ? htmlspecialchars($dataEdit['no_hp']) : ''; ?>" placeholder="08123456789">

                <label for="alamat">Alamat</label>
                <textarea id="alamat" name="alamat" placeholder="Alamat lengkap"><?php echo $dataEdit ? htmlspecialchars($dataEdit['alamat']) : ''; ?></textarea>

                <div class="actions">
                    <?php if ($dataEdit) { ?>
                        <button type="submit" name="edit" class="btn btn--primary">Simpan Perubahan</button>
                        <a href="index.php" class="btn btn--ghost">Batal</a>
                    <?php }
                    else { ?>
                        <button type="submit" name="tambah" class="btn btn--primary">Tambah Pelanggan</button>
                    <?php } ?>
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
                    <tbody>
                        <?php foreach ($daftarPelanggan as $pelanggan) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($pelanggan['nama']); ?></td>
                            <td><?php echo htmlspecialchars($pelanggan['email']); ?></td>
                            <td><?php echo htmlspecialchars($pelanggan['no_hp']); ?></td>
                            <td><?php echo htmlspecialchars($pelanggan['alamat']); ?></td>
                            <td>
                                <a href="?edit=<?php echo $pelanggan['id']; ?>">Edit</a>
                                |
                                <a href="?hapus=<?php echo $pelanggan['id']; ?>" onclick="return confirm('Hapus pelanggan ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
</body>
</html>
