<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin', 'super_admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('admin/users.php');
check_csrf();

$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$id]);
$usr = $stmt->fetch();

if (!$usr) {
    flash('error', 'Akun tidak ditemukan.');
} elseif ($id == current_user()['id']) {
    flash('error', 'Kamu tidak bisa menghapus akunmu sendiri.');
} elseif (!can_manage_user($usr)) {
    flash('error', 'Kamu tidak punya akses untuk menghapus akun ini.');
} else {
    // hapus file gambar karya milik user ini dulu
    $img = $pdo->prepare('SELECT gambar FROM projects WHERE user_id = ?');
    $img->execute([$id]);
    foreach ($img->fetchAll(PDO::FETCH_COLUMN) as $g) delete_image($g);

    // karya ikut terhapus otomatis (ON DELETE CASCADE)
    $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
    flash('success', 'Akun "' . $usr['nama'] . '" berhasil dihapus.');
}
redirect('admin/users.php');
