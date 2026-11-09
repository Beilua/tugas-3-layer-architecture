# Tugas 3-Layer Web Application Architecture

Implementasi Customer Management System dengan 2 pendekatan:

- `app-non-layered/` — PHP satu file, semua bercampur.
- `app-3-layer/` — presentation / business / data terpisah, komunikasi REST API + JSON.

## Cara menjalankan

1. Import `db_customer.sql` di phpMyAdmin (atau `mysql < db_customer.sql`).
2. Samakan kredensial koneksi di:
   - `app-non-layered/index.php` (`mysqli_connect`)
   - `app-3-layer/data/db.php` (`mysqli_connect`)
   Default: host `localhost`, user `root`, password kosong, database `db_customer` (sesuai isi `db_customer.sql`).
3. Nyalakan Apache + MySQL (XAMPP/Laragon), lalu buka:
   - Non-layered: `http://localhost/tugas-3-layer-architecture/app-non-layered/`
     (sesuaikan folder htdocs kamu)
   - 3-layer: `http://localhost/tugas-3-layer-architecture/app-3-layer/presentation/`
4. Uji CRUD: tambah, edit, hapus pelanggan.

## Stratifikasi

Lihat `ANALISIS.md` untuk perbandingan & jawaban pertanyaan di slide.
