<?php
// Panel Admin: kelola semua karya (Admin & Super Admin)
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin', 'super_admin']);

$stat = [
    'karya' => $pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn(),
    'siswa' => $pdo->query("SELECT COUNT(*) FROM users WHERE role='siswa'")->fetchColumn(),
    'admin' => $pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('admin','super_admin')")->fetchColumn(),
];

$q = trim($_GET['q'] ?? '');
$sql = 'SELECT p.*, u.nama FROM projects p JOIN users u ON u.id = p.user_id';
$params = [];
if ($q !== '') {
    $sql .= ' WHERE p.nama_project LIKE ? OR u.nama LIKE ?';
    $params = ["%$q%", "%$q%"];
}
$sql .= ' ORDER BY p.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

$title = 'Panel Admin - Stella';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-head">
    <div>
        <h1>Panel Admin</h1>
        <p class="muted">Kelola seluruh karya dan data siswa.</p>
    </div>
    <div class="admin-tabs">
        <a href="<?= url('admin/index.php') ?>" class="tab active">Karya</a>
        <a href="<?= url('admin/users.php') ?>" class="tab"><?= is_super_admin() ? 'User & Admin' : 'Data Siswa' ?></a>
    </div>
</div>

<div class="stat-row">
    <div class="stat panel"><span>Total Karya</span><strong><?= (int)$stat['karya'] ?></strong></div>
    <div class="stat panel"><span>Siswa</span><strong><?= (int)$stat['siswa'] ?></strong></div>
    <div class="stat panel"><span>Admin</span><strong><?= (int)$stat['admin'] ?></strong></div>
</div>

<div class="panel table-panel">
    <div class="table-toolbar">
        <form class="search" method="get">
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="Cari karya / siswa…">
            <button class="btn btn-sm">Cari</button>
        </form>
        <a href="<?= url('project_form.php') ?>" class="btn btn-sm">+ Tambah Karya</a>
    </div>
    <div class="table-scroll">
        <table>
            <thead>
            <tr><th>Karya</th><th>Pemilik</th><th>Link</th><th>Tanggal</th><th class="right">Aksi</th></tr>
            </thead>
            <tbody>
            <?php if (!$projects): ?>
                <tr><td colspan="5" class="center muted">Belum ada karya.</td></tr>
            <?php endif; ?>
            <?php foreach ($projects as $p): ?>
                <tr>
                    <td>
                        <div class="cell-project">
                            <?php if (project_img($p)): ?>
                                <img src="<?= project_img($p) ?>" alt="">
                            <?php else: ?>
                                <span class="thumb-ph cover-<?= $p['id'] % 4 ?>"><?= e(strtoupper(substr($p['nama_project'], 0, 2))) ?></span>
                            <?php endif; ?>
                            <a href="<?= url('project_detail.php?id=' . $p['id']) ?>"><?= e($p['nama_project']) ?></a>
                        </div>
                    </td>
                    <td><?= e($p['nama']) ?></td>
                    <td class="nowrap">
                        <?= $p['link_project'] ? '<a href="' . e($p['link_project']) . '" target="_blank" rel="noopener">Link</a>' : '<span class="muted">-</span>' ?>
                        <?= ($p['link_demo'] ?? '') ? ' · <a href="' . e(($p['link_demo'] ?? '')) . '" target="_blank" rel="noopener">Demo</a>' : '' ?>
                    </td>
                    <td class="nowrap"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                    <td class="right nowrap">
                        <a href="<?= url('project_form.php?id=' . $p['id']) ?>" class="btn btn-ghost btn-xs">Edit</a>
                        <form action="<?= url('project_delete.php') ?>" method="post" class="inline" onsubmit="return confirm('Hapus karya ini?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button class="btn btn-danger btn-xs">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
