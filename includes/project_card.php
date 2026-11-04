<?php // dipakai di projects.php & my_projects.php, butuh variabel $p ?>
<article class="card<?= !empty($featured) ? ' card-featured' : '' ?>">
    <a href="<?= url('project_detail.php?id=' . $p['id']) ?>" class="card-img">
        <?php if (project_img($p)): ?>
            <img src="<?= project_img($p) ?>" alt="<?= e($p['nama_project']) ?>" loading="lazy">
        <?php else: ?>
            <?= project_cover($p) ?>
        <?php endif; ?>
    </a>
    <div class="card-body">
        <h3><a href="<?= url('project_detail.php?id=' . $p['id']) ?>"><?= e($p['nama_project']) ?></a></h3>
        <p class="muted small">oleh <b><?= e($p['nama']) ?></b> &middot; <?= date('d M Y', strtotime($p['created_at'])) ?></p>
        <p class="excerpt"><?= e(mb_strimwidth($p['deskripsi'], 0, 110, '…')) ?></p>
        <div class="card-actions">
            <?php if ($p['link_project']): ?>
                <a href="<?= e($p['link_project']) ?>" target="_blank" rel="noopener" class="chip">🔗 Link</a>
            <?php endif; ?>
            <?php if (($p['link_demo'] ?? '')): ?>
                <a href="<?= e(($p['link_demo'] ?? '')) ?>" target="_blank" rel="noopener" class="chip chip-accent">▶ Demo</a>
            <?php endif; ?>
            <?php if (can_manage_project($p)): ?>
                <span class="spacer"></span>
                <a href="<?= url('project_form.php?id=' . $p['id']) ?>" class="icon-btn" title="Edit">✎</a>
                <form action="<?= url('project_delete.php') ?>" method="post" onsubmit="return confirm('Hapus karya ini?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button class="icon-btn danger" title="Hapus">🗑</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</article>
