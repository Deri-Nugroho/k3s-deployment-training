<?php
require_once 'config.php';
require_once 'includes/auth.php';
requireLogin(['admin']);

$pageTitle = 'Laporan Penjualan';

$tglAwal = $_GET['tgl_awal'] ?? date('Y-m-01');
$tglAkhir = $_GET['tgl_akhir'] ?? date('Y-m-d');

$sql = "SELECT p.*, u.nama_lengkap FROM penjualan p JOIN users u ON p.user_id = u.id
        WHERE DATE(p.created_at) BETWEEN ? AND ? ORDER BY p.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $tglAwal, $tglAkhir);
$stmt->execute();
$penjualans = $stmt->get_result();

$totalOmset = 0;
$rows = [];
while ($r = $penjualans->fetch_assoc()) {
    $rows[] = $r;
    $totalOmset += $r['total'];
}

// Barang terlaris pada rentang tanggal
$stmt2 = $conn->prepare("
    SELECT pd.nama_barang, SUM(pd.qty) total_qty, SUM(pd.subtotal) total_nilai
    FROM penjualan_detail pd
    JOIN penjualan p ON pd.penjualan_id = p.id
    WHERE DATE(p.created_at) BETWEEN ? AND ?
    GROUP BY pd.nama_barang
    ORDER BY total_qty DESC
    LIMIT 5
");
$stmt2->bind_param('ss', $tglAwal, $tglAkhir);
$stmt2->execute();
$terlaris = $stmt2->get_result();

include 'includes/header.php';
?>

<div class="card shadow-sm mb-3">
  <div class="card-body">
    <form method="get" class="row g-2 align-items-end">
      <div class="col-auto">
        <label class="form-label small mb-0">Dari Tanggal</label>
        <input type="date" name="tgl_awal" class="form-control" value="<?= e($tglAwal) ?>">
      </div>
      <div class="col-auto">
        <label class="form-label small mb-0">Sampai Tanggal</label>
        <input type="date" name="tgl_akhir" class="form-control" value="<?= e($tglAkhir) ?>">
      </div>
      <div class="col-auto">
        <button class="btn btn-primary"><i class="bi bi-filter"></i> Tampilkan</button>
      </div>
    </form>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="card card-stat shadow-sm">
      <div class="card-body">
        <div class="text-muted small">Total Transaksi</div>
        <div class="fs-4 fw-bold"><?= count($rows) ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-stat shadow-sm" style="border-left-color:#198754">
      <div class="card-body">
        <div class="text-muted small">Total Omset</div>
        <div class="fs-4 fw-bold"><?= rupiah($totalOmset) ?></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-md-7">
    <div class="card shadow-sm">
      <div class="card-body">
        <h6>Daftar Transaksi</h6>
        <div class="table-responsive">
        <table class="table table-sm table-hover">
          <thead><tr><th>No Transaksi</th><th>Tanggal</th><th>Kasir</th><th>Total</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= e($r['no_transaksi']) ?></td>
              <td><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
              <td><?= e($r['nama_lengkap']) ?></td>
              <td><?= rupiah($r['total']) ?></td>
              <td><a href="struk.php?id=<?= $r['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Lihat</a></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($rows)): ?>
            <tr><td colspan="5" class="text-center text-muted">Tidak ada transaksi pada rentang tanggal ini.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <h6>Barang Terlaris</h6>
        <table class="table table-sm">
          <thead><tr><th>Nama Barang</th><th>Qty Terjual</th><th>Total Nilai</th></tr></thead>
          <tbody>
          <?php while ($t = $terlaris->fetch_assoc()): ?>
            <tr>
              <td><?= e($t['nama_barang']) ?></td>
              <td><?= $t['total_qty'] ?></td>
              <td><?= rupiah($t['total_nilai']) ?></td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
