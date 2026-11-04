<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('projects.php');
check_csrf();

$stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
$stmt->execute([(int)($_POST['id'] ?? 0)]);
$p = $stmt->fetch();

if (!$p) {
    flash('error', 'Karya tidak ditemukan.');
} elseif (!can_manage_project($p)) {
    flash('error', 'Kamu hanya bisa menghapus karya milikmu sendiri.');
} else {
    $pdo->prepare('DELETE FROM projects WHERE id = ?')->execute([$p['id']]);
    delete_image($p['gambar']);
    flash('success', 'Karya "' . $p['nama_project'] . '" berhasil dihapus.');
}

$back = $_SERVER['HTTP_REFERER'] ?? '';
// jangan balik ke halaman detail karya yang sudah dihapus
if ($back && strpos($back, 'project_detail.php') === false && strpos($back, $_SERVER['HTTP_HOST'] ?? '') !== false) {
    header('Location: ' . $back);
    exit;
}
redirect('projects.php');
