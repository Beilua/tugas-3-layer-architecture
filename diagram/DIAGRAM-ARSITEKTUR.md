# Diagram Arsitektur: 1-Layer vs 3-Layer (Customer Management System)

> PHP native + `mysqli` + MySQL, database `db_customer`, tabel `pelanggan(id, nama, email, no_hp, alamat)`

---

## 1. Arsitektur 1-Layer (Non-Layered) — `app-1-layer/index.php`

### Diagram (Mermaid — tempel ke GitHub/GitLab/Mermaid Live)

```mermaid
flowchart TB
    U([User / Browser]) -->|GET / POST form<br/>(method=post, ?hapus=, ?edit=, ?cari=)| A
    A[/app-1-layer/index.php — SEMUA JADI SATU FILE
    ── Tampilan: HTML + CSS + JS di file yang sama
    ── Bisnis: validasi nama/email/no_hp wajib, format email
    ── Data: mysqli_connect + query INSERT/UPDATE/DELETE/SELECT langsung/]
    A -->|mysqli_query / prepared stmt| DB[(MySQL<br/>db_customer.pelanggan)]
    A -->|HTML render langsung| U
```

### Diagram teks (untuk Word / presntasi tanpa Mermaid)

```
┌─────────┐   GET/POST form    ┌──────────────────────────────────────────────┐   SQL (mysqli)  ┌────────┐
│  User   │ ────────────────▶  │  app-1-layer/index.php  (SATU FILE)          │ ─────────────▶ │ MySQL  │
│ Browser │                    │  ┌────────────────────────────────────────┐  │                │ db_cust│
└─────────┘                    │  │ Presentation: HTML + CSS + tabel       │  │                └────────┘
      ▲                        │  │ Business: validasi wajib + format email │  │
      │  HTML render langsung  │  │ Data: connect + INSERT/SELECT/UPDATE/   │  │
      └──────────────────────── │  │       DELETE (INSERT/UPDATE pakai      │  │
                               │  │       prepared stmt; DELETE/SELECT by  │  │
                               │  │       id via string interpolation)     │  │
                               │  └────────────────────────────────────────┘  │
                               └──────────────────────────────────────────────┘
```

### Ciri kunci

| Aspek | 1-Layer |
|---|---|
| File | 1 file `app-1-layer/index.php` (~780 baris) |
| Alur request | Browser submit form biasa → PHP di file yang sama proses + render HTML |
| Komunikasi | `$_GET` / `$_POST` langsung, tanpa JSON/API |
| Edit tampilan | Edit file yang sama dengan logika + SQL |
| Reuse | Tidak bisa dipakai mobile — logika menyatu dengan HTML |

---

## 2. Arsitektur 3-Layer — `app-3-layer/`

### Diagram (Mermaid)

```mermaid
flowchart TB
    U([User / Browser]) -->|buka halaman, klik, ketik| P
    P[/presentation/index.php + assets/app.js + assets/style.css
    PRESENTATION LAYER
    — HTML murni, CSS terpisah
    — JS: fetch() REST API + render tabel
    — TIDAK ada SQL, TIDAK ada validasi bisnis] -->|REST API + JSON<br/>fetch(../business/api.php)<br/>GET/POST/PUT/DELETE| B
    B[/business/api.php
    BUSINESS LAYER
    — Terima JSON, validasi aturan bisnis
      (wajib / format email / email & no_hp unik)
    — HTTP status: 201/400/404/500/405
    — TIDAK ada HTML] -->|panggil fungsi CRUD| D
    D[/data/pelanggan.php + data/db.php
    DATA LAYER
    — buatKoneksi() + query CRUD
      (prepared stmt untuk INSERT/UPDATE/search)
    — TIDAK tahu validasi bisnis] -->|mysqli| DB[(MySQL<br/>db_customer.pelanggan)]
    DB -->|row / result set| D -->|data bersih| B
    B -->|JSON {status, pesan, data}| P -->|render tabel/notif| U
```

### Diagram teks (untuk Word / presentasi tanpa Mermaid)

```
┌─────────┐
│  User   │
└────┬────┘
     │ 1. buka halaman
     ▼
┌──────────────────────────────────────────────────────────────────────────────┐
│ LAYER 1 — PRESENTATION  (presentation/index.php, assets/app.js, style.css)  │
│  HTML + CSS + JS. fetch() ke API. Tidak ada SQL.                            │
└──────────────────────────────┬───────────────────────────────────────────────┘
                               │ 2. REST API + JSON (fetch)
                               │    GET    business/api.php
                               │    POST   {nama,email,no_hp,alamat}
                               │    PUT    {id,nama,email,no_hp,alamat}
                               │    DELETE {id} / ?id=
                               ▼
┌──────────────────────────────────────────────────────────────────────────────┐
│ LAYER 2 — BUSINESS  (business/api.php)                                      │
│  Validasi: wajib diisi, format email, unik email/no_hp. Kirim JSON.         │
│  Status: 200 OK / 201 Created / 400 Bad Request / 404 Not Found / 500 / 405 │
└──────────────────────────────┬───────────────────────────────────────────────┘
                               │ 3. panggil fungsi CRUD
                               ▼
┌──────────────────────────────────────────────────────────────────────────────┐
│ LAYER 3 — DATA  (data/db.php, data/pelanggan.php)                          │
│  buatKoneksi() + tambah/ambil/edit/hapus + cek unik. Query SQL di sini.     │
└──────────────────────────────┬───────────────────────────────────────────────┘
                               │ 4. mysqli / prepared statement
                               ▼
                      ┌────────┐
                      │ MySQL  │
                      │db_cust │
                      └────────┘
```

### Alur request contoh (Tambah Pelanggan, 3-layer)

```
User isi form ─▶ fetch POST ../business/api.php (JSON)
  ─▶ business: validasiData() → lolos
  ─▶ data: tambahPelanggan() → INSERT prepared stmt
  ─▶ MySQL: row tersimpan
  ─▶ business: kirimJson(201, "Pelanggan berhasil ditambahkan.")
  ─▶ presentation: tampilkan notif + reload tabel via fetch GET
```

---

## 3. Tabel Hasil Pengujian

> Hasil di bawah adalah **lint + inspeksi kode** (MySQL tidak tersedia di environment ini — tidak ada server MySQL/Apache). Uji fungsional CRUD perlu dijalankan ulang di mesin lokal setelah `mysql < db_customer.sql`.

### 3a. Uji sintaks (sudah dijalankan 2026-11-09)

| No | Test case | Target | Hasil | Keterangan |
|---|---|---|---|---|
| 1 | `php -l` | `app-1-layer/index.php` | ✅ PASS | No syntax errors detected |
| 2 | `php -l` | `app-3-layer/business/api.php` | ✅ PASS | No syntax errors detected |
| 3 | `php -l` | `app-3-layer/data/pelanggan.php` | ✅ PASS | No syntax errors detected |
| 4 | `php -l` | `app-3-layer/data/db.php` | ✅ PASS | No syntax errors detected |
| 5 | `php -l` | `app-3-layer/presentation/index.php` | ✅ PASS | No syntax errors detected |
| 6 | Cek ekstensi PHP | `mysqli`, `json`, `PDO` | ✅ PASS | Semua tersedia |
| 7 | Koneksi MySQL | `mysqli_connect('localhost','root','','db_customer')` | ⚠️ NOT RUN | Tidak ada MySQL (`mysql: command not found`) di env ini |
| 8 | Import `db_customer.sql` | phpMyAdmin / `mysql < db_customer.sql` | ⚠️ NOT RUN | Harus di mesin lokal |

### 3b. Uji fungsional (harus dijalankan di lokal — dibuka via http://localhost/...)

| No | Test case | 1-Layer (dugaan dari kode) | 3-Layer REST API (dugaan dari kode) | Status di env ini |
|---|---|---|---|---|
| 1 | Tampilkan daftar pelanggan | `SELECT * FROM pelanggan ORDER BY id` → render tabel | `GET api.php` → JSON `200, data[]` → `fetch` render tabel | ⚠️ Not run (butuh MySQL) |
| 2 | Tambah pelanggan valid | `POST tambah` → INSERT prepared stmt → "berhasil ditambahkan" | `POST api.php` → validasi lolos → INSERT → `201` | ⚠️ Not run |
| 3 | Tambah field wajib kosong | `$pesan='Nama, email, dan no_hp wajib diisi.'` | `kirimJson(400,'Nama, email, dan no_hp wajib diisi.')` | ⚠️ Not run (logika terkonfirmasi via inspeksi) |
| 4 | Tambah email format salah | `$pesan='Format email tidak valid.'` | `kirimJson(400,'Format email tidak valid.')` | ⚠️ Not run |
| 5 | Tambah email/no_hp duplikat | **Tidak dicek** — mengandalkan UNIQUE constraint DB → pesan "Gagal menambah: Duplicate entry..." | `emailSudahDipakai/noHpSudahDipakai` → `400 'Email/No HP sudah dipakai pelanggan lain.'` | ⚠️ Not run (perbedaan terlihat dari kode) |
| 6 | Cari pelanggan (`cari`) | `GET cari=` → `LIKE %kw%` prepared stmt (aman) di file tampilan | `GET api.php?cari=` → `ambilSemuaPelanggan(kw)` → `LIKE` prepared stmt di data layer | ⚠️ Not run |
| 7 | Edit pelanggan | `POST edit` (UPDATE prepared stmt) → kembali mode tambah | `PUT api.php` (validasi + kecuali ID sendiri) → UPDATE → `200` | ⚠️ Not run |
| 8 | Hapus pelanggan | `GET ?hapus=id` → `DELETE WHERE id=$id` (interpolasi, id di-cast int) | `DELETE api.php` (id int, cek 404) → `200 'Pelanggan berhasil dihapus.'` | ⚠️ Not run |
| 9 | Akses ID tidak ada | `dataEdit=null` → form mode tambah; pesan tidak tampil | `GET ?id=404`, `PUT/DELETE id tdak ada → 404` | ⚠️ Not run |
| 10 | Method tidak didukung (3-layer) | — | `kirimJson(405,'Method tidak didukung.')` | ⚠️ Not run |

Cara menjalankan uji fungsional di lokal:
1. Import `db_customer.sql`, nyalakan Apache + MySQL.
2. Buka 1-layer: `http://localhost/tugas-3-layer-architecture/app-1-layer/`
3. Buka 3-layer: `http://localhost/tugas-3-layer-architecture/app-3-layer/presentation/`
4. Uji CRUD + cari + validasi; untuk 3-layer cek Response JSON via Network tab / `curl -i http://localhost/.../business/api.php`.

---

## 4. Tabel Perbandingan

### 4a. Arsitektur

| Aspek | 1-Layer (`app-1-layer/index.php`) | 3-Layer (`app-3-layer/`) |
|---|---|---|
| Struktur file | 1 file, semua bercampur | `presentation/` + `business/` + `data/` terpisah |
| Presentation layer | HTML+CSS+JS inline dalam `index.php` | `presentation/index.php` + `assets/app.js` + `assets/style.css` |
| Business layer | Bercampur di file yang sama (validasi wajib + format email) | `business/api.php`: validasi wajib, format email, email/no_hp unik |
| Data layer | `mysqli_connect` + query di file tampilan | `data/db.php` (`buatKoneksi`) + `data/pelanggan.php` (CRUD) |
| Komunikasi FE–BE | Submit form biasa (`POST`/`GET`) | REST API + JSON (`fetch()` ke `api.php`) |
| Kontrak API | Tidak ada | JSON `{status, pesan, data}` + HTTP status code |

### 4b. Operasional

| Aspek | 1-Layer | 3-Layer |
|---|---|---|
| Mudah dibuat awal | ✅ Lebih mudah — 1 file, tanpa kontrak JSON | ❌ Perlu pisah layer + desain endpoint |
| Mudah diperbaiki | ❌ Edit 1 file berisiko merusak bagian lain | ✅ Per layer (tampilan / bisnis / data) |
| Mudah ditambah fitur | ❌ File terus membesar, sulit dipelihara | ✅ Tambah endpoint + query + elemen per layer |
| Ganti tampilan | Harus sentuh file logika | Cukup edit `presentation/` |
| Ganti aturan bisnis | Harus sentuh file tampilan | Cukup edit `business/api.php` |
| Ganti query / DB | Harus sentuh file tampilan | Cukup edit `data/` |
| Reusability (mobile/desktop lain) | Rendah — tidak ada API | Tinggi — API yang sama dipakai ulang |
| Kerja tim | ❌ Semua edit file yang sama (konflik) | ✅ Tiap orang pegang 1 layer |
| Validasi duplikat email/no_hp | Tidak ada (andalkan UNIQUE DB) | Ada (`emailSudahDipakai`, `noHpSudahDipakai`) |
| Cocok untuk | Prototipe cepat, app sangat kecil, sekali pakai | App tumbuh, kerja tim, butuh reuse / mobile |

### Catatan

- **Bukan berarti 1-layer selalu buruk.** Untuk tugas cepat / prototipe, 1-layer lebih cepat dan cukup. 3-layer layak ketika aplikasi tumbuh, dikerjakan tim, atau butuh dipakai ulang.
- Keduanya pakai **prepared statement** untuk INSERT/UPDATE/pencarian; `DELETE`/`SELECT by id` memakai interpolasi dengan cast `(int)` — aman dari SQL injection untuk input angka, tapi lebih baik diganti prepared statement di produksi.
