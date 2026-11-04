<?php
// Kelola user
// - Super Admin: semua user (super admin, admin, siswa)
// - Admin     : hanya data siswa
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin', 'super_admin']);

$q = trim($_GET['q'] ?? '');
$where = [];
$params = [];
if (!is_super_admin()) $where[] = "u.role = 'siswa'";
if ($q !== '') {
    $where[] = '(u.nama LIKE ? OR u.email LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
}
$sql = 'SELECT u.*, (SELECT COUNT(*) FROM projects p WHERE p.user_id = u.id) AS jml_karya FROM users u'
     . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
     . " ORDER BY FIELD(u.role,'super_admin','admin','siswa'), u.nama";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$title = 'Kelola User - Stella';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-head">
    <div>
        <h1><?= is_super_admin() ? 'Kelola User & Admin' : 'Kelola Data Siswa' ?></h1>
        <p class="muted"><?= count($users) ?> akun</p>
    </div>
    <div class="admin-tabs">
        <a href="<?= url('admin/index.php') ?>" class="tab">Karya</a>
        <a href="<?= url('admin/users.php') ?>" class="tab active"><?= is_super_admin() ? 'User & Admin' : 'Data Siswa' ?></a>
    </div>
</div>

<div class="panel table-panel">
    <div class="table-toolbar">
        <form class="search" method="get">
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="Cari nama / email…">
            <button class="btn btn-sm">Cari</button>
        </form>
        <a href="<?= url('admin/user_form.php') ?>" class="btn btn-sm">+ Tambah <?= is_super_admin() ? 'User' : 'Siswa' ?></a>
    </div>
    <div class="table-scroll">
        <table>
            <thead>
            <tr><th>Nama</th><th>Email</th><th>Role</th><th>Karya</th><th class="right">Aksi</th></tr>
            </thead>
            <tbody>
            <?php if (!$users): ?>
                <tr><td colspan="5" class="center muted">Tidak ada data.</td></tr>
            <?php endif; ?>
            <?php foreach ($users as $usr): ?>
                <tr>
                    <td><strong><?= e($usr['nama']) ?></strong></td>
                    <td><?= e($usr['email']) ?></td>
                    <td><span class="badge badge-<?= e($usr['role']) ?>"><?= e(role_label($usr['role'])) ?></span></td>
                    <td><?= (int)$usr['jml_karya'] ?></td>
                    <td class="right nowrap">
                        <?php if (can_manage_user($usr)): ?>
                            <a href="<?= url('admin/user_form.php?id=' . $usr['id']) ?>" class="btn btn-ghost btn-xs">Edit</a>
                            <?php if ($usr['id'] != current_user()['id']): ?>
                                <form action="<?= url('admin/user_delete.php') ?>" method="post" class="inline"
                                      onsubmit="return confirm('Hapus akun ini? Semua karyanya juga akan terhapus.')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= $usr['id'] ?>">
                                    <button class="btn btn-danger btn-xs">Hapus</button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
