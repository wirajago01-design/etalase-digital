<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$stmt = $pdo->prepare('SELECT p.*, u.nama, u.email FROM projects p JOIN users u ON u.id = p.user_id WHERE p.id = ?');
$stmt->execute([(int)($_GET['id'] ?? 0)]);
$p = $stmt->fetch();
if (!$p) {
    flash('error', 'Karya tidak ditemukan.');
    redirect('projects.php');
}

$title = $p['nama_project'] . ' - Stella';
include __DIR__ . '/includes/header.php';
?>
<a href="<?= url('projects.php') ?>" class="back">← Kembali ke etalase</a>
<div class="detail panel">
    <div class="detail-img">
        <?php if (project_img($p)): ?>
            <img src="<?= project_img($p) ?>" alt="<?= e($p['nama_project']) ?>">
        <?php else: ?>
            <?= project_cover($p, true) ?>
        <?php endif; ?>
    </div>
    <div class="detail-body">
        <h1><?= e($p['nama_project']) ?></h1>
        <p class="muted">oleh <b><?= e($p['nama']) ?></b> &middot; diunggah <?= date('d M Y, H:i', strtotime($p['created_at'])) ?></p>
        <div class="desc"><?= nl2br(e($p['deskripsi'])) ?></div>

        <div class="detail-links">
            <?php if ($p['link_project']): ?>
                <a href="<?= e($p['link_project']) ?>" target="_blank" rel="noopener" class="btn btn-ghost">🔗 Link Pendukung</a>
            <?php endif; ?>
            <?php if (($p['link_demo'] ?? '')): ?>
                <a href="<?= e(($p['link_demo'] ?? '')) ?>" target="_blank" rel="noopener" class="btn">▶ Lihat Demo</a>
            <?php endif; ?>
        </div>

        <?php if (can_manage_project($p)): ?>
            <div class="detail-manage">
                <a href="<?= url('project_form.php?id=' . $p['id']) ?>" class="btn btn-ghost btn-sm">✎ Edit</a>
                <form action="<?= url('project_delete.php') ?>" method="post" onsubmit="return confirm('Hapus karya ini?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button class="btn btn-danger btn-sm">🗑 Hapus</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
