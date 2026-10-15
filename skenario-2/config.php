<?php
/**
 * KONFIGURASI APLIKASI TOKO
 * Ubah bagian di bawah sesuai kondisi server Anda.
 */
error_reporting(E_ALL & ~E_DEPRECATED);
session_start();

// ==== KONFIGURASI DATABASE ====
// Support environment variables for Kubernetes deployment
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'toko_db');

// ==== KONFIGURASI APLIKASI ====
define('APP_NAME', 'Toko Sederhana');
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', 'uploads/');

// ================================================================
// KONEKSI + AUTO SETUP DATABASE (dijalankan otomatis jika belum ada)
// ================================================================
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
if ($conn->connect_error) {
    die('Koneksi ke MySQL gagal: ' . $conn->connect_error .
        '<br>Periksa konfigurasi DB_HOST/DB_USER/DB_PASS di config.php');
}

// Buat database jika belum ada
$conn->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4");
$conn->select_db(DB_NAME);
$conn->set_charset('utf8mb4');

// Cek apakah tabel sudah ada, jika belum -> buat struktur + data dummy
$check = $conn->query("SHOW TABLES LIKE 'users'");
if ($check->num_rows === 0) {

    $conn->query("
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            nama_lengkap VARCHAR(100) NOT NULL,
            role ENUM('admin','kasir','gudang') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");

    $conn->query("
        CREATE TABLE kategori (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama_kategori VARCHAR(100) NOT NULL
        ) ENGINE=InnoDB
    ");

    $conn->query("
        CREATE TABLE barang (
            id INT AUTO_INCREMENT PRIMARY KEY,
            kode_barang VARCHAR(30) UNIQUE NOT NULL,
            nama_barang VARCHAR(150) NOT NULL,
            kategori_id INT NULL,
            harga_beli DECIMAL(15,2) NOT NULL DEFAULT 0,
            harga_jual DECIMAL(15,2) NOT NULL DEFAULT 0,
            stok INT NOT NULL DEFAULT 0,
            satuan VARCHAR(20) DEFAULT 'pcs',
            foto VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE SET NULL
        ) ENGINE=InnoDB
    ");

    $conn->query("
        CREATE TABLE penjualan (
            id INT AUTO_INCREMENT PRIMARY KEY,
            no_transaksi VARCHAR(30) UNIQUE NOT NULL,
            user_id INT NOT NULL,
            total DECIMAL(15,2) NOT NULL DEFAULT 0,
            bayar DECIMAL(15,2) NOT NULL DEFAULT 0,
            kembalian DECIMAL(15,2) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        ) ENGINE=InnoDB
    ");

    $conn->query("
        CREATE TABLE penjualan_detail (
            id INT AUTO_INCREMENT PRIMARY KEY,
            penjualan_id INT NOT NULL,
            barang_id INT NOT NULL,
            nama_barang VARCHAR(150) NOT NULL,
            harga DECIMAL(15,2) NOT NULL,
            qty INT NOT NULL,
            subtotal DECIMAL(15,2) NOT NULL,
            FOREIGN KEY (penjualan_id) REFERENCES penjualan(id) ON DELETE CASCADE,
            FOREIGN KEY (barang_id) REFERENCES barang(id)
        ) ENGINE=InnoDB
    ");

    // Data dummy user (password di-hash)
    $stmt = $conn->prepare("INSERT INTO users (username, password, nama_lengkap, role) VALUES (?,?,?,?)");
    $dummyUsers = [
        ['admin', password_hash('123', PASSWORD_DEFAULT), 'Administrator', 'admin'],
        ['kasir', password_hash('123', PASSWORD_DEFAULT), 'Kasir Toko', 'kasir'],
        ['gudang', password_hash('123', PASSWORD_DEFAULT), 'Petugas Gudang', 'gudang'],
    ];
    foreach ($dummyUsers as $u) {
        $stmt->bind_param('ssss', $u[0], $u[1], $u[2], $u[3]);
        $stmt->execute();
    }
    $stmt->close();

    // Data dummy kategori
    $conn->query("INSERT INTO kategori (nama_kategori) VALUES ('Makanan'),('Minuman'),('ATK'),('Lainnya')");

    // Data dummy barang
    $conn->query("
        INSERT INTO barang (kode_barang, nama_barang, kategori_id, harga_beli, harga_jual, stok, satuan) VALUES
        ('BRG001','Indomie Goreng',1,2500,3000,100,'pcs'),
        ('BRG002','Aqua Botol 600ml',2,2500,4000,80,'pcs'),
        ('BRG003','Pulpen Standard',3,1500,2500,50,'pcs'),
        ('BRG004','Teh Botol Sosro',2,3000,5000,60,'pcs'),
        ('BRG005','Buku Tulis 38 Lembar',3,2000,3500,40,'pcs')
    ");
}

// Buat folder upload jika belum ada
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
