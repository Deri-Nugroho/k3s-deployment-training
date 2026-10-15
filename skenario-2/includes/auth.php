<?php
// Pastikan config.php sudah di-include sebelum file ini (session_start ada di sana)

function isLogin() {
    return isset($_SESSION['user_id']);
}

function currentUser() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'nama' => $_SESSION['nama_lengkap'] ?? null,
        'role' => $_SESSION['role'] ?? null,
    ];
}

/**
 * Wajibkan login. Jika $roles diisi, hanya role tersebut yang diizinkan.
 * Contoh: requireLogin(['admin','gudang']);
 */
function requireLogin($roles = []) {
    if (!isLogin()) {
        header('Location: login.php');
        exit;
    }
    if (!empty($roles) && !in_array($_SESSION['role'], $roles)) {
        header('Location: dashboard.php?error=akses_ditolak');
        exit;
    }
}

function rupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
