<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$stmt = $pdo->prepare('SELECT p.*, u.nama FROM projects p JOIN users u ON u.id = p.user_id
                       WHERE p.user_id = ? ORDER BY p.created_at DESC');
$stmt->execute([current_user()['id']]);
$projects = $stmt->fetchAll();

$title = 'Karyaku - Stella';
include __DIR__ . '/includes/header.php';
?>
<div class="page-head">
    <div>
        <h1>Karyaku</h1>
        <p class="muted">Kelola karya yang kamu upload sendiri.</p>
    </div>
    <a href="<?= url('project_form.php') ?>" class="btn">+ Tambah Karya</a>
</div>

<?php if (!$projects): ?>
    <div class="empty panel">
        <p>Kamu belum punya karya. Yuk upload yang pertama!</p>
        <a href="<?= url('project_form.php') ?>" class="btn">+ Tambah Karya</a>
    </div>
<?php else: ?>
    <div class="grid">
        <?php foreach ($projects as $p) include __DIR__ . '/includes/project_card.php'; ?>
    </div>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
