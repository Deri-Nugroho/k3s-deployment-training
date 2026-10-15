<?php
require_once 'config.php';
require_once 'includes/auth.php';
requireLogin(['admin','gudang']);

$pageTitle = 'Kategori Barang';
$msg = '';

// Tambah kategori
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $nama = trim($_POST['nama_kategori']);
    if ($nama !== '') {
        $stmt = $conn->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
        $stmt->bind_param('s', $nama);
        $stmt->execute();
        $msg = 'Kategori berhasil ditambahkan.';
    }
}

// Hapus kategori
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $conn->query("DELETE FROM kategori WHERE id = $id");
    header('Location: kategori.php?deleted=1');
    exit;
}

$kategoris = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori");

include 'includes/header.php';
?>

<?php if ($msg): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Kategori dihapus.</div><?php endif; ?>

<div class="row">
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h6>Tambah Kategori</h6>
        <form method="post">
          <div class="mb-3">
            <input type="text" name="nama_kategori" class="form-control" placeholder="Nama kategori" required>
          </div>
          <button type="submit" name="tambah" class="btn btn-primary w-100">Simpan</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-md-8">
    <div class="card shadow-sm">
      <div class="card-body">
        <h6>Daftar Kategori</h6>
        <table class="table table-sm table-hover">
          <thead><tr><th>#</th><th>Nama Kategori</th><th></th></tr></thead>
          <tbody>
          <?php $no=1; while ($k = $kategoris->fetch_assoc()): ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= e($k['nama_kategori']) ?></td>
              <td>
                <a href="kategori.php?hapus=<?= $k['id'] ?>" class="btn btn-sm btn-outline-danger"
                   onclick="return confirm('Hapus kategori ini?')">Hapus</a>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
