<?php
require_once 'config.php';
require_once 'includes/auth.php';
requireLogin();

$pageTitle = 'Dashboard';
$user = currentUser();

$totalBarang = $conn->query("SELECT COUNT(*) c FROM barang")->fetch_assoc()['c'];
$stokMenipis = $conn->query("SELECT COUNT(*) c FROM barang WHERE stok <= 5")->fetch_assoc()['c'];
$totalTransaksiHariIni = $conn->query("SELECT COUNT(*) c FROM penjualan WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['c'];
$omsetHariIni = $conn->query("SELECT COALESCE(SUM(total),0) t FROM penjualan WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['t'];

include 'includes/header.php';
?>

<?php if (isset($_GET['error']) && $_GET['error']==='akses_ditolak'): ?>
  <div class="alert alert-danger">Anda tidak memiliki akses ke halaman tersebut.</div>
<?php endif; ?>

<h4 class="mb-4">Selamat datang, <?= e($user['nama']) ?> 👋</h4>

<div class="row g-3">
  <div class="col-md-3">
    <div class="card card-stat shadow-sm">
      <div class="card-body">
        <div class="text-muted small">Total Barang</div>
        <div class="fs-3 fw-bold"><?= $totalBarang ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card card-stat shadow-sm" style="border-left-color:#dc3545">
      <div class="card-body">
        <div class="text-muted small">Stok Menipis (&le;5)</div>
        <div class="fs-3 fw-bold text-danger"><?= $stokMenipis ?></div>
      </div>
    </div>
  </div>
  <?php if (in_array($user['role'], ['admin','kasir'])): ?>
  <div class="col-md-3">
    <div class="card card-stat shadow-sm" style="border-left-color:#198754">
      <div class="card-body">
        <div class="text-muted small">Transaksi Hari Ini</div>
        <div class="fs-3 fw-bold"><?= $totalTransaksiHariIni ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card card-stat shadow-sm" style="border-left-color:#ffc107">
      <div class="card-body">
        <div class="text-muted small">Omset Hari Ini</div>
        <div class="fs-5 fw-bold"><?= rupiah($omsetHariIni) ?></div>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<div class="mt-4">
  <div class="card shadow-sm">
    <div class="card-body">
      <h6>Menu Cepat</h6>
      <div class="d-flex gap-2 flex-wrap">
        <?php if (in_array($user['role'], ['admin','gudang'])): ?>
          <a href="barang.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-box-seam"></i> Kelola Barang</a>
        <?php endif; ?>
        <?php if (in_array($user['role'], ['admin','kasir'])): ?>
          <a href="pos.php" class="btn btn-outline-success btn-sm"><i class="bi bi-cart-check"></i> Mulai Transaksi</a>
        <?php endif; ?>
        <?php if ($user['role']=='admin'): ?>
          <a href="laporan.php" class="btn btn-outline-warning btn-sm"><i class="bi bi-graph-up"></i> Lihat Laporan</a>
          <a href="users.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-people"></i> Kelola User</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
