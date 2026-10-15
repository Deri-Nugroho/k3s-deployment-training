<?php
require_once 'config.php';
require_once 'includes/auth.php';
requireLogin(['admin','gudang']);

$pageTitle = 'Data Barang';
$msg = ''; $err = '';

function uploadFoto($file) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) return [null, null];
    if ($file['error'] !== UPLOAD_ERR_OK) return [null, 'Upload gagal (kode error: ' . $file['error'] . ')'];

    $allowed = ['jpg','jpeg','png','webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return [null, 'Format foto harus jpg/jpeg/png/webp'];
    if ($file['size'] > 3 * 1024 * 1024) return [null, 'Ukuran foto maksimal 3MB'];

    $newName = 'brg_' . uniqid() . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $newName)) {
        return [null, 'Gagal menyimpan file foto ke server'];
    }
    return [$newName, null];
}

// TAMBAH BARANG
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $kode = trim($_POST['kode_barang']);
    $nama = trim($_POST['nama_barang']);
    $kategori_id = $_POST['kategori_id'] ?: null;
    $harga_beli = (float)$_POST['harga_beli'];
    $harga_jual = (float)$_POST['harga_jual'];
    $stok = (int)$_POST['stok'];
    $satuan = trim($_POST['satuan']) ?: 'pcs';

    [$foto, $uploadErr] = uploadFoto($_FILES['foto'] ?? null);
    if ($uploadErr) {
        $err = $uploadErr;
    } else {
        $stmt = $conn->prepare("INSERT INTO barang (kode_barang, nama_barang, kategori_id, harga_beli, harga_jual, stok, satuan, foto) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param('ssiddiss', $kode, $nama, $kategori_id, $harga_beli, $harga_jual, $stok, $satuan, $foto);
        if ($stmt->execute()) {
            $msg = 'Barang berhasil ditambahkan.';
        } else {
            $err = 'Gagal menambah barang: ' . $conn->error;
        }
    }
}

// EDIT BARANG
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $id = (int)$_POST['id'];
    $kode = trim($_POST['kode_barang']);
    $nama = trim($_POST['nama_barang']);
    $kategori_id = $_POST['kategori_id'] ?: null;
    $harga_beli = (float)$_POST['harga_beli'];
    $harga_jual = (float)$_POST['harga_jual'];
    $stok = (int)$_POST['stok'];
    $satuan = trim($_POST['satuan']) ?: 'pcs';

    [$fotoBaru, $uploadErr] = uploadFoto($_FILES['foto'] ?? null);
    if ($uploadErr) {
        $err = $uploadErr;
    } else {
        if ($fotoBaru) {
            // hapus foto lama
            $old = $conn->query("SELECT foto FROM barang WHERE id=$id")->fetch_assoc();
            if ($old && $old['foto'] && file_exists(UPLOAD_DIR . $old['foto'])) {
                unlink(UPLOAD_DIR . $old['foto']);
            }
            $stmt = $conn->prepare("UPDATE barang SET kode_barang=?, nama_barang=?, kategori_id=?, harga_beli=?, harga_jual=?, stok=?, satuan=?, foto=? WHERE id=?");
            $stmt->bind_param('ssiddissi', $kode, $nama, $kategori_id, $harga_beli, $harga_jual, $stok, $satuan, $fotoBaru, $id);
        } else {
            $stmt = $conn->prepare("UPDATE barang SET kode_barang=?, nama_barang=?, kategori_id=?, harga_beli=?, harga_jual=?, stok=?, satuan=? WHERE id=?");
            $stmt->bind_param('ssiddisi', $kode, $nama, $kategori_id, $harga_beli, $harga_jual, $stok, $satuan, $id);
        }
        if ($stmt->execute()) {
            $msg = 'Barang berhasil diperbarui.';
        } else {
            $err = 'Gagal update barang: ' . $conn->error;
        }
    }
}

// HAPUS BARANG
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $b = $conn->query("SELECT foto FROM barang WHERE id=$id")->fetch_assoc();
    if ($b && $b['foto'] && file_exists(UPLOAD_DIR . $b['foto'])) {
        unlink(UPLOAD_DIR . $b['foto']);
    }
    $conn->query("DELETE FROM barang WHERE id = $id");
    header('Location: barang.php?deleted=1');
    exit;
}

$keyword = trim($_GET['q'] ?? '');
$sql = "SELECT b.*, k.nama_kategori FROM barang b LEFT JOIN kategori k ON b.kategori_id = k.id";
if ($keyword !== '') {
    $kw = $conn->real_escape_string($keyword);
    $sql .= " WHERE b.nama_barang LIKE '%$kw%' OR b.kode_barang LIKE '%$kw%'";
}
$sql .= " ORDER BY b.id DESC";
$barangs = $conn->query($sql);

$kategoris = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori");
$kategoriList = [];
while ($k = $kategoris->fetch_assoc()) $kategoriList[] = $k;

include 'includes/header.php';
?>

<?php if ($msg): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Barang dihapus.</div><?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <form method="get" class="d-flex gap-2">
    <input type="text" name="q" class="form-control" placeholder="Cari nama/kode barang..." value="<?= e($keyword) ?>">
    <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
  </form>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
    <i class="bi bi-plus-lg"></i> Tambah Barang
  </button>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>Foto</th><th>Kode</th><th>Nama Barang</th><th>Kategori</th>
          <th>Harga Beli</th><th>Harga Jual</th><th>Stok</th><th>Satuan</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php while ($b = $barangs->fetch_assoc()): ?>
        <tr>
          <td>
            <?php if ($b['foto']): ?>
              <img src="<?= UPLOAD_URL . e($b['foto']) ?>" class="foto-barang">
            <?php else: ?>
              <div class="foto-barang bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center">
                <i class="bi bi-image text-secondary"></i>
              </div>
            <?php endif; ?>
          </td>
          <td><?= e($b['kode_barang']) ?></td>
          <td><?= e($b['nama_barang']) ?></td>
          <td><?= e($b['nama_kategori'] ?? '-') ?></td>
          <td><?= rupiah($b['harga_beli']) ?></td>
          <td><?= rupiah($b['harga_jual']) ?></td>
          <td>
            <span class="badge <?= $b['stok'] <= 5 ? 'bg-danger' : 'bg-success' ?>"><?= $b['stok'] ?></span>
          </td>
          <td><?= e($b['satuan']) ?></td>
          <td class="text-nowrap">
            <button class="btn btn-sm btn-outline-warning"
              data-bs-toggle="modal" data-bs-target="#modalEdit<?= $b['id'] ?>">
              <i class="bi bi-pencil"></i>
            </button>
            <a href="barang.php?hapus=<?= $b['id'] ?>" class="btn btn-sm btn-outline-danger"
               onclick="return confirm('Hapus barang ini?')">
              <i class="bi bi-trash"></i>
            </a>
          </td>
        </tr>

        <!-- Modal Edit per baris -->
        <div class="modal fade" id="modalEdit<?= $b['id'] ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="post" enctype="multipart/form-data">
                <div class="modal-header">
                  <h6 class="modal-title">Edit Barang</h6>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="id" value="<?= $b['id'] ?>">
                  <div class="text-center mb-3">
                    <?php if ($b['foto']): ?>
                      <img src="<?= UPLOAD_URL . e($b['foto']) ?>" class="foto-preview">
                    <?php endif; ?>
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Kode Barang</label>
                    <input type="text" name="kode_barang" class="form-control" value="<?= e($b['kode_barang']) ?>" required>
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Nama Barang</label>
                    <input type="text" name="nama_barang" class="form-control" value="<?= e($b['nama_barang']) ?>" required>
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Kategori</label>
                    <select name="kategori_id" class="form-select">
                      <option value="">-- Pilih Kategori --</option>
                      <?php foreach ($kategoriList as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $k['id']==$b['kategori_id']?'selected':'' ?>><?= e($k['nama_kategori']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="row">
                    <div class="col-6 mb-2">
                      <label class="form-label">Harga Beli</label>
                      <input type="number" step="0.01" name="harga_beli" class="form-control" value="<?= $b['harga_beli'] ?>" required>
                    </div>
                    <div class="col-6 mb-2">
                      <label class="form-label">Harga Jual</label>
                      <input type="number" step="0.01" name="harga_jual" class="form-control" value="<?= $b['harga_jual'] ?>" required>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-6 mb-2">
                      <label class="form-label">Stok</label>
                      <input type="number" name="stok" class="form-control" value="<?= $b['stok'] ?>" required>
                    </div>
                    <div class="col-6 mb-2">
                      <label class="form-label">Satuan</label>
                      <input type="text" name="satuan" class="form-control" value="<?= e($b['satuan']) ?>">
                    </div>
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Ganti Foto (opsional)</label>
                    <input type="file" name="foto" class="form-control" accept="image/*">
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="submit" name="edit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" enctype="multipart/form-data">
        <div class="modal-header">
          <h6 class="modal-title">Tambah Barang</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label">Kode Barang</label>
            <input type="text" name="kode_barang" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Nama Barang</label>
            <input type="text" name="nama_barang" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Kategori</label>
            <select name="kategori_id" class="form-select">
              <option value="">-- Pilih Kategori --</option>
              <?php foreach ($kategoriList as $k): ?>
                <option value="<?= $k['id'] ?>"><?= e($k['nama_kategori']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="row">
            <div class="col-6 mb-2">
              <label class="form-label">Harga Beli</label>
              <input type="number" step="0.01" name="harga_beli" class="form-control" required>
            </div>
            <div class="col-6 mb-2">
              <label class="form-label">Harga Jual</label>
              <input type="number" step="0.01" name="harga_jual" class="form-control" required>
            </div>
          </div>
          <div class="row">
            <div class="col-6 mb-2">
              <label class="form-label">Stok</label>
              <input type="number" name="stok" class="form-control" value="0" required>
            </div>
            <div class="col-6 mb-2">
              <label class="form-label">Satuan</label>
              <input type="text" name="satuan" class="form-control" value="pcs">
            </div>
          </div>
          <div class="mb-2">
            <label class="form-label">Foto Barang</label>
            <input type="file" name="foto" class="form-control" accept="image/*">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
