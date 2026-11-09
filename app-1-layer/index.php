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
    <title>Non-Layered — Customer Management</title>
    <style>
        /* ================================================================
           Accessible & Ethical + Organic Biophilic — full-bleed revision
           Native CSS only (no styling frameworks). Fonts via Google Fonts.
           SVG icons inline — no emoji, no external icon library.
           ================================================================ */
        @import url('https://fonts.googleapis.com/css2?family=Lora:wght@400;500;600;700&family=Raleway:wght@300;400;500;600;700&display=swap');

        :root {
            --forest: #1b6b2e;
            --forest-dark: #104720;
            --forest-mist: #eaf4ec;
            --earth: #8b4513;
            --earth-soft: #c98a56;
            --cream: #f7f4ea;
            --sky: #0e7490;
            --sky-soft: #bfe3ee;
            --surface: #ffffff;
            --ink: #1c2420;
            --muted: #5b6760;
            --border: #d6cfb8;
            --ring: #0e7490;

            --radius-organic: 60% 40% 30% 70% / 60% 30% 70% 40%;
            --radius-card: 28px;
            --radius-input: 18px;
            --radius-btn: 30px;

            --shadow-card: 0 12px 34px rgba(28, 36, 32, 0.08);
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Raleway', system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            font-size: 17px;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            color: var(--ink);
            background:
                radial-gradient(1100px 700px at 20% -10%, #e3f0e5 0%, transparent 45%),
                radial-gradient(900px 600px at 110% 10%, #f0e6d2 0%, transparent 40%),
                var(--cream);
            min-height: 100vh;
            overflow-x: hidden;
        }
        .skip-link {
            position: absolute;
            left: -9999px;
            width: 1px; height: 1px; overflow: hidden;
        }
        .skip-link:focus {
            position: static;
            width: auto; height: auto;
            padding: 12px 18px;
            background: #fff;
            color: var(--forest-dark);
            font-weight: 800;
            font-family: 'Lora', serif;
            border: 3px solid var(--ring);
            border-radius: var(--radius-input);
            z-index: 10000;
        }
        /* Full-bleed app shell: no side borders, no top border */
        .app {
            width: 100%;
            min-height: 100vh;
            padding: 0 0 120px 0;
            position: relative;
        }
        /* Animated organic background blobs (full bleed) */
        .bg-blobs {
            position: fixed;
            inset: 0;
            z-index: -1;
            pointer-events: none;
            overflow: hidden;
        }
        .blob {
            position: absolute;
            filter: blur(72px);
            opacity: 0.55;
            border-radius: var(--radius-organic);
            animation: float 14s ease-in-out infinite;
        }
        .blob-1 {
            top: -8%; left: -6%;
            width: 62vw; height: 62vw;
            background: var(--forest-dark);
            animation-duration: 16s; animation-delay: -2s;
        }
        .blob-2 {
            top: 10%; right: -4%;
            width: 52vw; height: 52vw;
            background: var(--earth-soft);
            animation-duration: 13s; animation-delay: -5s;
        }
        .blob-3 {
            bottom: 12%; left: 8%;
            width: 44vw; height: 44vw;
            background: var(--sky-soft);
            animation-duration: 19s; animation-delay: -8s;
        }
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1) rotate(0deg); }
            33% { transform: translate(28px, -42px) scale(1.06) rotate(4deg); }
            66% { transform: translate(-20px, 30px) scale(0.94) rotate(-3deg); }
        }
        /* Header: cornered to top, full screen width */
        .header {
            width: 100%;
            padding: 36px 4vw 44px;
            color: #fff;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .header::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--forest) 0%, var(--forest-dark) 48%, #0a2e18 100%);
            z-index: 0;
        }
        .header-deco {
            position: absolute;
            z-index: 0;
            filter: blur(40px);
            opacity: 0.45;
        }
        .header-deco-1 {
            top: -10%; left: -8%; width: 56vw; height: 56vw;
            background: var(--forest-dark);
            border-radius: var(--radius-organic);
        }
        .header-deco-2 {
            top: 14%; right: -6%; width: 42vw; height: 42vw;
            background: var(--earth-soft);
            border-radius: var(--radius-organic);
        }
        .header > * { position: relative; z-index: 1; }
        .header h1 {
            font-family: 'Lora', serif;
            margin: 0 0 6px;
            font-size: clamp(1.7rem, 3vw, 2.5rem);
            font-weight: 700;
            letter-spacing: 0.3px;
            line-height: 1.15;
        }
        .header .lead {
            margin: 0 auto;
            max-width: 62ch;
            font-size: clamp(0.95rem, 1.3vw, 1.05rem);
            color: rgba(255, 255, 255, 0.9);
            font-weight: 400;
        }
        /* Leaf badge (header) — inline with the title, as tall as the title text */
        .leaf-badge {
            width: 0.85em; height: 0.85em;
            vertical-align: -0.08em;
            margin-right: 10px;
            animation: sway 5s ease-in-out infinite;
        }
        @keyframes sway {
            0%, 100% { transform: translateY(0) rotate(-2deg); }
            50% { transform: translateY(-12px) rotate(4deg); }
        }
        /* Full-width grid: cards span full width, no side boxing */
        .grid {
            display: grid;
            gap: 40px;
            grid-template-columns: 1fr;
            padding: 0 4vw;
            margin-top: 36px;   /* clear gap between header band and content */
        }
        @media (min-width: 900px) {
            .grid { grid-template-columns: 1fr 1fr; padding: 0 6vw; }
        }
        /* Glassmorphism organic cards */
        .card {
            padding: 10px;
            position: relative;
            background: linear-gradient(145deg, #ffffff 0%, #fcfaf2 100%);
            border: 1px solid var(--border);
            border-radius: var(--radius-card);
            box-shadow: var(--shadow-card);
            overflow: hidden;
            animation: card-rise 700ms cubic-bezier(0.18, 0.89, 0.32, 1.28) both;
        }
        .card:nth-child(2) { animation-delay: 140ms; }
        .card__blob {
            position: absolute;
            top: -46px; right: -46px;
            width: 210px; height: 210px;
            background: var(--forest-mist);
            filter: blur(52px);
            border-radius: var(--radius-organic);
            z-index: 0;
        }
        .card > * { position: relative; z-index: 1; }
        @keyframes card-rise {
            0% { opacity: 0; transform: translateY(26px) scale(0.97); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        .card h2 {
            font-family: 'Lora', serif;
            margin: 0 0 10px;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--forest-dark);
            letter-spacing: 0.2px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card h2 svg { width: 22px; height: 22px; flex-shrink: 0; }
        .pesan svg { width: 20px; height: 20px; flex-shrink: 0; }
        .section-lead {
            font-size: 0.95rem;
            color: var(--muted);
            margin: 0 0 22px;
            line-height: 1.5;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 30px 28px;
        }
        label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: 'Lora', serif;
            font-weight: 700;
            font-size: 0.92rem;
            color: var(--forest-dark);
            margin-top: 14px;
            letter-spacing: 0.1px;
        }
        label svg { width: 18px; height: 18px; flex-shrink: 0; }
        input, textarea {
            width: 100%;
            min-height: 52px;
            padding: 13px 16px;
            font: inherit;
            font-family: 'Raleway', sans-serif;
            color: var(--ink);
            background: #fff;
            border: 2px solid var(--border);
            border-radius: var(--radius-input);
        }
        textarea { min-height: 120px; resize: vertical; }
        input::placeholder, textarea::placeholder { color: #9fb0a8; }
        input:focus-visible, textarea:focus-visible {
            border-color: var(--ring);
            outline: 3px solid var(--ring);
            outline-offset: 2px;
            background: #fff;
        }
        .actions {
            display: flex;
            gap: 12px;
            margin-top: 28px;
        }
        .btn {
            flex: 1;
            min-height: 52px;
            font: inherit;
            font-family: 'Raleway', sans-serif;
            font-weight: 800;
            font-size: 1rem;
            cursor: pointer;
            border-radius: var(--radius-btn);
            border: none;
            letter-spacing: 0.5px;
            transition: transform 150ms ease, box-shadow 150ms ease, background 150ms ease, color 150ms ease;
        }
        .btn:active { transform: translateY(1px); }
        .btn--primary {
            background: linear-gradient(135deg, var(--forest) 0%, var(--forest-dark) 100%);
            color: #fff;
            box-shadow: 0 10px 24px rgba(26, 107, 46, 0.45);
        }
        .btn--primary:hover {
            background: linear-gradient(135deg, var(--forest-dark) 0%, #0a2e18 100%);
            box-shadow: 0 14px 30px rgba(26, 107, 46, 0.58);
            transform: translateY(-2px);
        }
        .btn--ghost {
            background: linear-gradient(135deg, var(--earth-soft) 0%, var(--earth) 100%);
            color: #fff;
            box-shadow: 0 10px 24px rgba(139, 69, 19, 0.30);
        }
        .btn--ghost:hover {
            background: linear-gradient(135deg, var(--earth) 0%, #6b330d 100%);
            box-shadow: 0 14px 30px rgba(139, 69, 19, 0.42);
            transform: translateY(-2px);
        }
        .btn[hidden] { display: none !important; }
        .pesan {
            margin: 0 0 26px;
            padding: 16px 20px;
            border-radius: var(--radius-input);
            border-left: 7px solid var(--forest);
            background: var(--forest-mist);
            color: var(--forest-dark);
            font-weight: 800;
            font-family: 'Lora', serif;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .pesan.error { border-left-color: #991b1b; background: #fef3f2; color: #7f1d1d; }
        .table-wrap {
            overflow: auto;
            border-radius: 16px;
            border: 1px solid var(--border);
            margin-bottom: 22px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 640px;
            position: relative;
            z-index: 1;
        }
        thead th {
            background: var(--forest-mist);
            color: var(--forest-dark);
            font-weight: 800;
            text-align: center;
            padding: 14px 20px;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 3px solid var(--border);
        }
        tbody td {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
            font-size: 0.95rem;
            text-align: left;
        }
        tbody tr:nth-child(even) { background: #fcfaf2; }
        tbody tr:hover { background: var(--forest-mist); }
        td a {
            color: var(--forest-dark);
            font-weight: 800;
            text-decoration: none;
            text-underline-offset: 3px;
            display: inline-block;
            padding: 6px 4px;
            border-radius: 6px;
        }
        td a:hover { color: var(--earth); background: rgba(139, 69, 19, 0.08); text-decoration: underline; }

        @media (max-width: 900px) {
            .app { padding: 0 0 80px 0; }
            .header { padding: 28px 4vw 36px; }
            .grid { gap: 30px; padding: 0 4vw; margin-top: 24px; }
            .actions { flex-direction: column; }
        }
        @media (max-width: 480px) {
            .header h1 { font-size: 2.2rem; }
            .card h2 { font-size: 1.3rem; }
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
    <div class="bg-blobs">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <header class="header">
        <h1><svg class="leaf-badge" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="#fff" d="M12 2c3.9 0 7 3.1 7 7s-3.1 7-7 7c-3.9 0-7-3.1-7-7s3.1-7 7-7m0 2c-2.8 0-5 2.2-5 5s2.2 5 5 5c2.8 0 5-2.2 5-5s-2.2-5-5-5"/></svg>Customer Management</h1>
        <p class="lead">Manajemen data pelanggan</p>
    </header>

    <main class="grid">
        <section class="card">
            <h2>
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="18" y1="8" x2="23" y2="13"/><line x1="18" y1="13" x2="23" y2="8"/></svg>
                <?php echo $dataEdit ? 'Edit Pelanggan' : 'Tambah Pelanggan'; ?>
            </h2>
            <?php if ($pesan !== '') { ?>
                <p class="pesan<?php echo strpos($pesan, 'Gagal') === 0 ? ' error' : ''; ?>" role="status" aria-live="polite">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <?php echo htmlspecialchars($pesan); ?>
                </p>
            <?php } ?>
            <form method="post" id="formPelanggan">
                <?php if ($dataEdit) { ?>
                    <input type="hidden" name="id" value="<?php echo $dataEdit['id']; ?>">
                <?php } ?>
                <label for="nama">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Nama <span style="font-weight:400; opacity:.85;">*</span>
                </label>
                <input type="text" id="nama" name="nama" required value="<?php echo $dataEdit ? htmlspecialchars($dataEdit['nama']) : ''; ?>" placeholder="Masukkan nama lengkap">

                <label for="email">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    Email <span style="font-weight:400; opacity:.85;">*</span>
                </label>
                <input type="email" id="email" name="email" required value="<?php echo $dataEdit ? htmlspecialchars($dataEdit['email']) : ''; ?>" placeholder="nama@contoh.com">

                <label for="no_hp">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    No HP <span style="font-weight:400; opacity:.85;">*</span>
                </label>
                <input type="text" id="no_hp" name="no_hp" required value="<?php echo $dataEdit ? htmlspecialchars($dataEdit['no_hp']) : ''; ?>" placeholder="08123456789">

                <label for="alamat">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    Alamat
                </label>
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
