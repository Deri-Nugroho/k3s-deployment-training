<?php
require_once 'config.php';
require_once 'includes/auth.php';

if (isLogin()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, username, password, nama_lengkap, role FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $u = $result->fetch_assoc();

    if ($u && password_verify($password, $u['password'])) {
        $_SESSION['user_id'] = $u['id'];
        $_SESSION['username'] = $u['username'];
        $_SESSION['nama_lengkap'] = $u['nama_lengkap'];
        $_SESSION['role'] = $u['role'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Username atau password salah.';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - <?= APP_NAME ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
body { background: linear-gradient(135deg,#0d6efd,#212529); min-height:100vh; display:flex; align-items:center; }
.login-card { max-width: 400px; margin: auto; }
</style>
</head>
<body>
<div class="container">
  <div class="card login-card shadow-lg border-0">
    <div class="card-body p-4">
      <div class="text-center mb-4">
        <i class="bi bi-shop fs-1 text-primary"></i>
        <h4 class="mt-2 fw-bold"><?= APP_NAME ?></h4>
        <p class="text-muted mb-0">Silakan login untuk melanjutkan</p>
      </div>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
      <?php endif; ?>
      <?php if (isset($_GET['logout'])): ?>
        <div class="alert alert-success">Anda berhasil logout.</div>
      <?php endif; ?>
      <form method="post">
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
      </form>
      <hr>
      <small class="text-muted">
        Akun demo:<br>
        admin / 123 (Admin)<br>
        kasir / 123 (Kasir)<br>
        gudang / 123 (Gudang)
      </small>
    </div>
  </div>
</div>
</body>
</html>
