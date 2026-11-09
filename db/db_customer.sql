-- buat database pelanggan bila belum ada
CREATE DATABASE IF NOT EXISTS db_customer;
-- pakai database pelanggan untuk kerja
USE db_customer;

-- hapus tabel lama bila sudah ada
DROP TABLE IF EXISTS pelanggan;
-- buat tabel pelanggan baru
CREATE TABLE pelanggan (
    -- nomor urut auto naik
    id INT AUTO_INCREMENT PRIMARY KEY,
    -- nama lengkap pelanggan
    nama VARCHAR(100) NOT NULL,
    -- surel unik pelanggan
    email VARCHAR(100) NOT NULL UNIQUE,
    -- no hp unik pelanggan
    no_hp VARCHAR(20) NOT NULL UNIQUE,
    -- alamat lengkap opsional
    alamat VARCHAR(255) DEFAULT NULL
);

-- isi data contoh tiga pelanggan
INSERT INTO pelanggan (nama, email, no_hp, alamat) VALUES
    ('Nadya', 'nadya@example.com', '081234567890', 'Jl. Merdeka No. 1, Bandung'),
    ('Sheva', 'sheva@example.com', '082345678901', 'Jl. Sudirman No. 2, Bandung'),
    ('Jillena', 'jillena@example.com', '083456789012', 'Jl. Asia Afrika No. 3, Bandung');
