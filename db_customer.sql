CREATE DATABASE IF NOT EXISTS db_customer;
USE db_customer;

DROP TABLE IF EXISTS pelanggan;
CREATE TABLE pelanggan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    no_hp VARCHAR(20) NOT NULL UNIQUE,
    alamat VARCHAR(255) DEFAULT NULL
);

INSERT INTO pelanggan (nama, email, no_hp, alamat) VALUES
    ('Nadya', 'nadya@example.com', '081234567890', 'Jl. Merdeka No. 1, Bandung'),
    ('Sheva', 'sheva@example.com', '082345678901', 'Jl. Sudirman No. 2, Bandung'),
    ('Jillena', 'jillena@example.com', '083456789012', 'Jl. Asia Afrika No. 3, Bandung');
