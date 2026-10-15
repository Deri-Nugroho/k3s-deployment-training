<?php
require_once 'config.php';
require_once 'includes/auth.php';
requireLogin(['admin','kasir']);

$pageTitle = 'Kasir (POS)';

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// Tambah ke keranjang
if (isset($_POST['tambah_cart'])) {
    $id = (int)$_POST['barang_id'];
    $b = $conn->query("SELECT * FROM barang WHERE id=$id")->fetch_assoc();
    if ($b) {
        if (isset($_SESSION['cart'][$id])) {
            if ($_SESSION['cart'][$id]['qty'] < $b['stok']) {
                $_SESSION['cart'][$id]['qty']++;
            }
        } else {
            if ($b['stok'] > 0) {
                $_SESSION['cart'][$id] = [
                    'nama' => $b['nama_barang'],
                    'harga' => $b['harga_jual'],
                    'qty' => 1,
                    'stok' => $b['stok'],
                ];
            }
        }
    }
    header('Location: pos.php');
    exit;
}

// Update qty
if (isset($_POST['update_qty'])) {
    foreach ($_POST['qty'] as $id => $qty) {
        $qty = (int)$qty;
        if (isset($_SESSION['cart'][$id])) {
            if ($qty <= 0) {
                unset($_SESSION['cart'][$id]);
            } else {
                $max = $_SESSION['cart'][$id]['stok'];
                $_SESSION['cart'][$id]['qty'] = min($qty, $max);
            }
        }
    }
    header('Location: pos.php');
    exit;
}

// Hapus item
if (isset($_GET['hapus_item'])) {
    unset($_SESSION['cart'][$_GET['hapus_item']]);
    header('Location: pos.php');
    exit;
}

// Kosongkan keranjang
if (isset($_GET['kosongkan'])) {
    $_SESSION['cart'] = [];
    header('Location: pos.php');
    exit;
}

// Proses checkout
$errCheckout = '';
if (isset($_POST['checkout'])) {
    if (empty($_SESSION['cart'])) {
        $errCheckout = 'Keranjang masih kosong.';
    } else {
        $bayar = (float)$_POST['bayar'];
        $total = 0;
        foreach ($_SESSION['cart'] as $item) $total += $item['harga'] * $item['qty'];

        if ($bayar < $total) {
            $errCheckout = 'Jumlah bayar kurang dari total belanja.';
        } else {
            $kembalian = $bayar - $total;
            $no_transaksi = 'TRX' . date('YmdHis') . rand(10,99);
            $user = currentUser();

            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("INSERT INTO penjualan (no_transaksi, user_id, total, bayar, kembalian) VALUES (?,?,?,?,?)");
                $stmt->bind_param('siddd', $no_transaksi, $user['id'], $total, $bayar, $kembalian);
                $stmt->execute();
                $penjualan_id = $conn->insert_id;

                $stmtDetail = $conn->prepare("INSERT INTO penjualan_detail (penjualan_id, barang_id, nama_barang, harga, qty, subtotal) VALUES (?,?,?,?,?,?)");
                $stmtStok = $conn->prepare("UPDATE barang SET stok = stok - ? WHERE id = ? AND stok >= ?");

                foreach ($_SESSION['cart'] as $barang_id => $item) {
                    $subtotal = $item['harga'] * $item['qty'];
                    $stmtDetail->bind_param('iisdid', $penjualan_id, $barang_id, $item['nama'], $item['harga'], $item['qty'], $subtotal);
                    $stmtDetail->execute();

                    $stmtStok->bind_param('iii', $item['qty'], $barang_id, $item['qty']);
                    $stmtStok->execute();
                    if ($stmtStok->affected_rows === 0) {
                        throw new Exception('Stok tidak mencukupi untuk ' . $item['nama']);
                    }
                }

                $conn->commit();
                $_SESSION['cart'] = [];
                header('Location: struk.php?id=' . $penjualan_id);
                exit;
            } catch (Exception $e) {
                $conn->rollback();
                $errCheckout = 'Transaksi gagal: ' . $e->getMessage();
            }
        }
    }
}

$keyword = trim($_GET['q'] ?? '');
$sql = "SELECT * FROM barang WHERE stok > 0";
if ($keyword !== '') {
    $kw = $conn->real_escape_string($keyword);
    $sql .= " AND (nama_barang LIKE '%$kw%' OR kode_barang LIKE '%$kw%')";
}
$sql .= " ORDER BY nama_barang LIMIT 60";
$barangs = $conn->query($sql);

$total = 0;
foreach ($_SESSION['cart'] as $item) $total += $item['harga'] * $item['qty'];

include 'includes/header.php';
?>

<?php if ($errCheckout): ?><div class="alert alert-danger"><?= e($errCheckout) ?></div><?php endif; ?>

<div class="row g-3">
  <div class="col-md-7">
    <div class="card shadow-sm">
      <div class="card-body">
        <form method="get" class="mb-3">
          <input type="text" name="q" class="form-control" placeholder="Cari barang..." value="<?= e($keyword) ?>" onchange="this.form.submit()">
        </form>
        <div class="row row-cols-2 row-cols-md-3 g-2">
          <?php while ($b = $barangs->fetch_assoc()): ?>
            <div class="col">
              <form method="post">
                <input type="hidden" name="barang_id" value="<?= $b['id'] ?>">
                <button type="submit" name="tambah_cart" class="btn btn-outline-primary w-100 text-start p-2 h-100">
                  <?php if ($b['foto']): ?>
                    <img src="<?= UPLOAD_URL . e($b['foto']) ?>" class="w-100 mb-1" style="height:80px;object-fit:cover;border-radius:4px;">
                  <?php endif; ?>
                  <div class="small fw-semibold"><?= e($b['nama_barang']) ?></div>
                  <div class="small text-muted"><?= rupiah($b['harga_jual']) ?></div>
                  <div class="small text-muted">Stok: <?= $b['stok'] ?></div>
                </button>
              </form>
            </div>
          <?php endwhile; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <h6>Keranjang Belanja</h6>
        <?php if (empty($_SESSION['cart'])): ?>
          <p class="text-muted">Belum ada barang dipilih.</p>
        <?php else: ?>
        <form method="post">
          <table class="table table-sm">
            <thead><tr><th>Nama</th><th>Harga</th><th>Qty</th><th>Subtotal</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($_SESSION['cart'] as $id => $item): ?>
              <tr>
                <td><?= e($item['nama']) ?></td>
                <td><?= rupiah($item['harga']) ?></td>
                <td style="width:70px">
                  <input type="number" name="qty[<?= $id ?>]" value="<?= $item['qty'] ?>" min="0" max="<?= $item['stok'] ?>" class="form-control form-control-sm">
                </td>
                <td><?= rupiah($item['harga'] * $item['qty']) ?></td>
                <td><a href="pos.php?hapus_item=<?= $id ?>" class="text-danger"><i class="bi bi-x-circle"></i></a></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
          <button type="submit" name="update_qty" class="btn btn-sm btn-outline-secondary mb-3">Update Qty</button>
        </form>

        <hr>
        <h5 class="d-flex justify-content-between">Total <span><?= rupiah($total) ?></span></h5>

        <form method="post" class="mt-3">
          <div class="mb-2">
            <label class="form-label">Jumlah Bayar</label>
            <input type="number" step="0.01" name="bayar" class="form-control" required>
          </div>
          <button type="submit" name="checkout" class="btn btn-success w-100">
            <i class="bi bi-check2-circle"></i> Proses & Cetak Struk
          </button>
        </form>
        <a href="pos.php?kosongkan=1" class="btn btn-sm btn-outline-danger w-100 mt-2"
           onclick="return confirm('Kosongkan keranjang?')">Kosongkan Keranjang</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
