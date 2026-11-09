# Analisis Perbandingan: Non-Layered vs 3-Layer

## Tabel perbandingan

| Aspek | Non-Layered (`app-non-layered/`) | 3-Layer (`app-3-layer/`) |
|---|---|---|
| Struktur file | 1 file `index.php` berisi semua | `presentation/` (tampilan), `business/api.php` (bisnis), `data/` (database) |
| Tampilan | HTML + CSS digabung dalam PHP | HTML terpisah (`presentation/index.php`), CSS/JS di `assets/` |
| Bisnis | Validasi dicampur dengan tampilan & query | `api.php`: validasi (nama/email/no_hp wajib, format email, duplikat) |
|_database | Query SQL ditulis langsung di file tampilan | `data/pelanggan.php` berisi query CRUD, `data/db.php` koneksi |
| Komunikasi frontend-backend | Submit form biasa (POST/GET) | REST API + JSON (`fetch()` ke `api.php`) |
| Perubahan tampilan | Harus edit file yang sama dengan logika & SQL | Cukup edit `presentation/` saja |
| Perubahan aturan bisnis | Harus edit file tampilan juga | Cukup edit `business/api.php` |
| Reusability | Rendah: backend tidak bisa dipakai ulang (mobile, dll) | Tinggi: API yang sama bisa dipakai web & mobile |
| Keamanan | Pengguna tidak akses DB langsung (sama), tapi SQL rentan injection karena query dirangkai langsung | Sama-sama harus dihindari di üretim; layering memudahkan sanitasi terpusat |

## Jawaban pertanyaan di slide

### Mana yang lebih mudah dibuat?
**Non-layered.** Semua ada dalam satu file, tanpa perlu memikirkan kontrak API JSON atau memisahkan fungsi. Cocok untuk tugas cepat dan aplikasi sangat kecil.

### Mana yang lebih mudah diperbaiki ketika terjadi kesalahan?
**3-Layer.** Kalau error tampilan rusak, yang diubah hanya presentation. Kalau aturan bisnis salah, yang diubah hanya business. Di non-layered, satu edit di file tunggal berisiko merusak bagian lain yang bercampur—misalnya mengubah HTML bisa tanpa sengaja menghapus logika form.

### Mana yang lebih mudah dikembangkan ketika fitur bertambah?
**3-Layer.** Fitur baru berarti menambah fungsi di layer yang sesuai (misalnya endpoint baru di business + query baru di data + elemen di presentation), tanpa menyentuh layer lain. Di non-layered, file terus membesar dan sulit dipahami.

### Apa risiko jika seluruh kode ditempatkan dalam satu file?
- File makin panjang dan sulit dibaca/diperbaiki.
- Perubahan di satu bagian mudah merusak bagian lain.
- Tidak bisa dipakai ulang (API tidak terpisah).
- Duplikasi kode (misalnya validasi yang sama ditulis ulang di setiap form).
- Kerja tim sulit: semua orang mengedit file yang sama.

### Apakah aplikasi tanpa 3-layer selalu lebih buruk?
**Tidak selalu.** Untuk proyek kecil / sekali pakai / prototipe, non-layered lebih cepat dan cukup. Arsitektur 3-layer baru layak ketika aplikasi tumbuh, dikerjakan tim, atau butuh dipakai ulang (mobile + web). Keduanya punya trade-off kecepatan vs keteraturan.

## Catatan implementasi (studi kasus Customer Management System)

- Keduanya pakai **PHP native + `mysqli` + MySQL**, database `db_customer`, tabel `pelanggan(id, nama, email, no_hp, alamat)`.
- Fitur CRUD lengkap di keduanya: tambah, tampil, edit, hapus.
- Validasi dibuat sederhana (wajib diisi, format email, email/no_hp tidak duplikat) supaya perbedaannya terlihat: di non-layered validasi ada di file tampilan, di 3-layer validasi ada di `business/api.php`.
- File SQL ada di `db_customer.sql` (import manual).
