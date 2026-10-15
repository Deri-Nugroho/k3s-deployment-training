<?php
// Variabel $pageTitle bisa diset sebelum include file ini
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? e($pageTitle) . ' - ' . APP_NAME : APP_NAME ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
    body { background:#f4f6f9; }
    .sidebar { min-height: 100vh; background:#212529; }
    .sidebar a { color:#c9ccd1; }
    .sidebar a.active, .sidebar a:hover { color:#fff; background:#343a40; }
    .brand { color:#fff; font-weight:600; }
    .card-stat { border-left: 4px solid #0d6efd; }
    .foto-barang { width:50px; height:50px; object-fit:cover; border-radius:6px; }
    .foto-preview { width:120px; height:120px; object-fit:cover; border-radius:8px; }
</style>
</head>
<body>
<div class="d-flex">
  <nav class="sidebar p-3" style="width:230px;">
    <div class="brand mb-4 fs-5"><i class="bi bi-shop"></i> <?= APP_NAME ?></div>
    <div class="nav flex-column">
      <a class="nav-link py-2 px-2 rounded <?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'active':'' ?>" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
      <?php if (in_array($user['role'], ['admin','gudang'])): ?>
      <a class="nav-link py-2 px-2 rounded <?= basename($_SERVER['PHP_SELF'])=='barang.php'?'active':'' ?>" href="barang.php"><i class="bi bi-box-seam"></i> Data Barang</a>
      <a class="nav-link py-2 px-2 rounded <?= basename($_SERVER['PHP_SELF'])=='kategori.php'?'active':'' ?>" href="kategori.php"><i class="bi bi-tags"></i> Kategori</a>
      <?php endif; ?>
      <?php if (in_array($user['role'], ['admin','kasir'])): ?>
      <a class="nav-link py-2 px-2 rounded <?= basename($_SERVER['PHP_SELF'])=='pos.php'?'active':'' ?>" href="pos.php"><i class="bi bi-cart-check"></i> Kasir (POS)</a>
      <?php endif; ?>
      <?php if ($user['role']=='admin'): ?>
      <a class="nav-link py-2 px-2 rounded <?= basename($_SERVER['PHP_SELF'])=='laporan.php'?'active':'' ?>" href="laporan.php"><i class="bi bi-graph-up"></i> Laporan Penjualan</a>
      <a class="nav-link py-2 px-2 rounded <?= basename($_SERVER['PHP_SELF'])=='users.php'?'active':'' ?>" href="users.php"><i class="bi bi-people"></i> Kelola User</a>
      <?php endif; ?>
      <hr class="text-secondary">
      <a class="nav-link py-2 px-2 rounded" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
  </nav>

  <div class="flex-grow-1">
    <nav class="navbar navbar-light bg-white border-bottom px-3">
      <span class="fw-semibold"><?= isset($pageTitle) ? e($pageTitle) : '' ?></span>
      <span class="ms-auto text-muted">
        <i class="bi bi-person-circle"></i> <?= e($user['nama']) ?>
        <span class="badge bg-primary ms-1"><?= e($user['role']) ?></span>
      </span>
    </nav>
    <div class="container-fluid p-4">
