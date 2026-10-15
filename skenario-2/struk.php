<?php
require_once 'config.php';
require_once 'includes/auth.php';
requireLogin(['admin','kasir']);

$id = (int)($_GET['id'] ?? 0);
$penjualan = $conn->query("SELECT p.*, u.nama_lengkap FROM penjualan p JOIN users u ON p.user_id=u.id WHERE p.id=$id")->fetch_assoc();
if (!$penjualan) {
    die('Transaksi tidak ditemukan.');
}
$details = $conn->query("SELECT * FROM penjualan_detail WHERE penjualan_id=$id");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Struk - <?= e($penjualan['no_transaksi']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background:#f4f6f9; }
.struk { max-width: 380px; margin: 30px auto; background:#fff; padding:20px; font-family: 'Courier New', monospace; }
.struk hr { border-top: 1px dashed #333; }
@media print { .no-print { display:none; } body{background:#fff;} }
</style>
</head>
<body>
<div class="struk shadow-sm">
  <div class="text-center">
    <h5 class="mb-0"><?= APP_NAME ?></h5>
    <small>Jl. Contoh No. 123</small><br>
    <small>Telp: 0800-0000-0000</small>
  </div>
  <hr>
  <div class="small">
    No: <?= e($penjualan['no_transaksi']) ?><br>
    Tanggal: <?= date('d/m/Y H:i', strtotime($penjualan['created_at'])) ?><br>
    Kasir: <?= e($penjualan['nama_lengkap']) ?>
  </div>
  <hr>
  <table class="table table-sm small mb-0">
    <?php while ($d = $details->fetch_assoc()): ?>
      <tr>
        <td colspan="3"><?= e($d['nama_barang']) ?></td>
      </tr>
      <tr>
        <td><?= $d['qty'] ?> x <?= number_format($d['harga'],0,',','.') ?></td>
        <td></td>
        <td class="text-end"><?= number_format($d['subtotal'],0,',','.') ?></td>
      </tr>
    <?php endwhile; ?>
  </table>
  <hr>
  <div class="d-flex justify-content-between fw-bold">
    <span>TOTAL</span><span><?= rupiah($penjualan['total']) ?></span>
  </div>
  <div class="d-flex justify-content-between">
    <span>BAYAR</span><span><?= rupiah($penjualan['bayar']) ?></span>
  </div>
  <div class="d-flex justify-content-between">
    <span>KEMBALI</span><span><?= rupiah($penjualan['kembalian']) ?></span>
  </div>
  <hr>
  <p class="text-center small mb-0">Terima kasih atas kunjungan Anda</p>

  <div class="no-print text-center mt-3">
    <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Cetak</button>
    <a href="pos.php" class="btn btn-secondary btn-sm">Transaksi Baru</a>
  </div>
</div>
</body>
</html>
