# 🏪 Toko Sederhana — PHP, Bootstrap & MySQL

Aplikasi manajemen toko sederhana berbasis web menggunakan **PHP native (mysqli)**, **Bootstrap 5**, dan **MySQL**. Mendukung manajemen barang (dengan upload foto), transaksi kasir (POS), laporan penjualan, dan manajemen user dengan multi-role.

Database beserta struktur tabel dan data dummy **dibuat otomatis** saat aplikasi pertama kali diakses — tidak perlu import file SQL manual.

## ✨ Fitur

- 🔐 **Login multi-role**: `admin`, `kasir`, `gudang`
- 📦 **Manajemen Barang**: tambah, edit, hapus, upload foto, pencarian
- 🏷️ **Manajemen Kategori** barang
- 🧾 **Point of Sale (POS)**: keranjang belanja, hitung kembalian, cetak struk
- 📊 **Laporan Penjualan**: filter tanggal, total omset, barang terlaris
- 👥 **Manajemen User**: tambah/edit/hapus user & role (khusus admin)
- ⚙️ **Auto setup database**: tabel & data dummy dibuat otomatis jika belum ada
- 💻 Tampilan responsif dengan **Bootstrap 5** + **Bootstrap Icons**

## 🔑 Hak Akses per Role

| Menu                | Admin | Kasir | Gudang |
|---------------------|:-----:|:-----:|:------:|
| Dashboard           | ✅    | ✅    | ✅     |
| Data Barang         | ✅    | ❌    | ✅     |
| Kategori            | ✅    | ❌    | ✅     |
| Kasir (POS)         | ✅    | ✅    | ❌     |
| Laporan Penjualan   | ✅    | ❌    | ❌     |
| Kelola User         | ✅    | ❌    | ❌     |

## 🛠️ Teknologi

- PHP 7.4+ (native, tanpa framework)
- MySQL / MariaDB (ekstensi `mysqli`)
- Bootstrap 5 & Bootstrap Icons (via CDN)

## 📋 Persyaratan

- Web server dengan PHP (XAMPP, Laragon, WAMP, atau sejenisnya)
- MySQL/MariaDB aktif
- Ekstensi PHP: `mysqli`, `fileinfo` (untuk upload gambar)

## 🚀 Instalasi

1. Clone atau download repository ini:
   ```bash
   git clone https://github.com/paknux/apptoko.git
   ```
2. Pindahkan folder project ke direktori web server, contoh untuk XAMPP:
   ```
   /var/www/html
   ```
3. Sesuaikan koneksi database di **`config.php`** bila perlu (default sudah cocok untuk instalasi XAMPP standar):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'toko_db');
   ```
4. Pastikan MySQL sudah berjalan, lalu akses aplikasi lewat browser:
   ```
   http://IP-Publicmu
   ```
5. Database, tabel, dan data dummy akan **dibuat otomatis** pada akses pertama.

## 👤 Akun Demo (Dummy)

| Username | Password | Role   |
|----------|----------|--------|
| admin    | 123      | admin  |
| kasir    | 123      | kasir  |
| gudang   | 123      | gudang |

> ⚠️ **Penting:** Ganti password akun-akun ini sebelum digunakan di lingkungan produksi.

## 📁 Struktur Folder

```
/
├── config.php              # Konfigurasi DB + auto-setup tabel & data dummy
├── index.php                # Redirect ke login/dashboard
├── login.php / logout.php   # Autentikasi
├── dashboard.php             # Ringkasan statistik
├── barang.php                # CRUD barang + upload foto
├── kategori.php               # CRUD kategori
├── pos.php                    # Halaman kasir (POS)
├── struk.php                  # Cetak struk transaksi
├── laporan.php                # Laporan penjualan (admin)
├── users.php                  # Kelola user (admin)
├── includes/
│   ├── header.php             # Layout navbar + sidebar
│   ├── footer.php             # Penutup layout
│   └── auth.php                # Helper autentikasi & fungsi umum
└── uploads/                    # Folder penyimpanan foto barang
```

## 🗄️ Struktur Database

Tabel yang dibuat otomatis:

- `users` — data pengguna & role
- `kategori` — kategori barang
- `barang` — data barang (harga beli/jual, stok, foto)
- `penjualan` — header transaksi penjualan
- `penjualan_detail` — detail item per transaksi

## 📸 Screenshot

_Tambahkan screenshot aplikasi di sini setelah instalasi berhasil._

## 🤝 Kontribusi

Pull request dan saran perbaikan sangat diterima. Untuk perubahan besar, silakan buka issue terlebih dahulu untuk mendiskusikan apa yang ingin diubah.

## 📄 Lisensi

Proyek ini bebas digunakan dan dimodifikasi untuk keperluan pembelajaran maupun pengembangan lebih lanjut.
