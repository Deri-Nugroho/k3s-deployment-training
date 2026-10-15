<?php
require_once 'config.php';
require_once 'includes/auth.php';
requireLogin(['admin']);

$pageTitle = 'Kelola User';
$msg = ''; $err = '';
$me = currentUser();

// Tambah user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $nama = trim($_POST['nama_lengkap']);
    $role = $_POST['role'];

    $cek = $conn->query("SELECT id FROM users WHERE username='" . $conn->real_escape_string($username) . "'");
    if ($cek->num_rows > 0) {
        $err = 'Username sudah digunakan.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, nama_lengkap, role) VALUES (?,?,?,?)");
        $stmt->bind_param('ssss', $username, $hash, $nama, $role);
        $stmt->execute();
        $msg = 'User berhasil ditambahkan.';
    }
}

// Edit user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $id = (int)$_POST['id'];
    $nama = trim($_POST['nama_lengkap']);
    $role = $_POST['role'];
    $password = $_POST['password'];

    if ($password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET nama_lengkap=?, role=?, password=? WHERE id=?");
        $stmt->bind_param('sssi', $nama, $role, $hash, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET nama_lengkap=?, role=? WHERE id=?");
        $stmt->bind_param('ssi', $nama, $role, $id);
    }
    $stmt->execute();
    $msg = 'User berhasil diperbarui.';
}

// Hapus user
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if ($id === (int)$me['id']) {
        header('Location: users.php?err=self');
        exit;
    }
    $conn->query("DELETE FROM users WHERE id=$id");
    header('Location: users.php?deleted=1');
    exit;
}

$users = $conn->query("SELECT * FROM users ORDER BY id");

include 'includes/header.php';
?>

<?php if ($msg): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">User dihapus.</div><?php endif; ?>
<?php if (isset($_GET['err']) && $_GET['err']==='self'): ?><div class="alert alert-danger">Anda tidak bisa menghapus akun Anda sendiri.</div><?php endif; ?>

<div class="d-flex justify-content-between mb-3">
  <h6>Daftar User</h6>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
    <i class="bi bi-plus-lg"></i> Tambah User
  </button>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <table class="table table-hover">
      <thead><tr><th>Username</th><th>Nama Lengkap</th><th>Role</th><th>Dibuat</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php while ($u = $users->fetch_assoc()): ?>
        <tr>
          <td><?= e($u['username']) ?></td>
          <td><?= e($u['nama_lengkap']) ?></td>
          <td><span class="badge bg-primary"><?= e($u['role']) ?></span></td>
          <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
          <td>
            <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditUser<?= $u['id'] ?>">
              <i class="bi bi-pencil"></i>
            </button>
            <?php if ($u['id'] != $me['id']): ?>
              <a href="users.php?hapus=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus user ini?')">
                <i class="bi bi-trash"></i>
              </a>
            <?php endif; ?>
          </td>
        </tr>

        <div class="modal fade" id="modalEditUser<?= $u['id'] ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="post">
                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                <div class="modal-header">
                  <h6 class="modal-title">Edit User: <?= e($u['username']) ?></h6>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-2">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-control" value="<?= e($u['nama_lengkap']) ?>" required>
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select">
                      <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Admin</option>
                      <option value="kasir" <?= $u['role']=='kasir'?'selected':'' ?>>Kasir</option>
                      <option value="gudang" <?= $u['role']=='gudang'?'selected':'' ?>>Gudang</option>
                    </select>
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Password Baru (opsional)</label>
                    <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diganti">
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="submit" name="edit" class="btn btn-primary">Simpan</button>
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

<div class="modal fade" id="modalTambahUser" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header">
          <h6 class="modal-title">Tambah User</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" name="nama_lengkap" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Role</label>
            <select name="role" class="form-select">
              <option value="admin">Admin</option>
              <option value="kasir">Kasir</option>
              <option value="gudang">Gudang</option>
            </select>
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
