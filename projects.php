<?php
// Etalase: semua role yang login bisa melihat semua karya
require_once __DIR__ . '/includes/auth.php';
require_login();

$q = trim($_GET['q'] ?? '');
$sql = 'SELECT p.*, u.nama FROM projects p JOIN users u ON u.id = p.user_id';
$params = [];
if ($q !== '') {
    $sql .= ' WHERE p.nama_project LIKE ? OR p.deskripsi LIKE ? OR u.nama LIKE ?';
    $params = ["%$q%", "%$q%", "%$q%"];
}
$sql .= ' ORDER BY p.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

$title = 'Etalase Karya - Stella';
include __DIR__ . '/includes/header.php';
?>
<div class="page-head">
    <div>
        <h1>Etalase Karya Digital</h1>
        <p class="muted"><?= count($projects) ?> karya <?= $q ? 'ditemukan untuk "' . e($q) . '"' : 'dari seluruh siswa' ?></p>
    </div>
    <form class="search" method="get">
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Cari karya atau nama siswa…">
        <button class="btn btn-sm">Cari</button>
    </form>
</div>

<?php if (!$projects): ?>
    <div class="empty panel">
        <p>Belum ada karya<?= $q ? ' yang cocok' : '' ?>.</p>
        <a href="<?= url('project_form.php') ?>" class="btn">+ Tambah Karya Pertama</a>
    </div>
<?php else: ?>
    <div class="grid">
        <?php foreach ($projects as $i => $p) { $featured = ($i === 0 && $q === ''); include __DIR__ . '/includes/project_card.php'; } ?>
    </div>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
