<?php
// Tambah & Edit karya (satu form)
// - Semua role boleh menambah karya
// - Edit: pemilik karya, admin, super admin
// - Admin/Super Admin bisa memilih pemilik karya (tambah karya untuk siswa)
require_once __DIR__ . '/includes/auth.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$isEdit = $id > 0;
$p = [
    'nama_project' => '', 'deskripsi' => '', 'gambar' => null,
    'link_project' => '', 'link_demo' => '', 'user_id' => current_user()['id'],
];

if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
    $stmt->execute([$id]);
    $p = $stmt->fetch();
    if (!$p) {
        flash('error', 'Karya tidak ditemukan.');
        redirect('projects.php');
    }
    if (!can_manage_project($p)) {
        flash('error', 'Kamu hanya bisa mengedit karya milikmu sendiri.');
        redirect('projects.php');
    }
}

// daftar siswa untuk dropdown pemilik (khusus admin)
$owners = is_admin()
    ? $pdo->query('SELECT id, nama, role FROM users ORDER BY role DESC, nama')->fetchAll()
    : [];

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $p['nama_project'] = trim($_POST['nama_project'] ?? '');
    $p['deskripsi']    = trim($_POST['deskripsi'] ?? '');
    $p['link_project'] = trim($_POST['link_project'] ?? '');
    $p['link_demo']    = trim($_POST['link_demo'] ?? '');

    if (is_admin() && !empty($_POST['user_id'])) {
        $ownerIds = array_column($owners, 'id');
        if (in_array((int)$_POST['user_id'], array_map('intval', $ownerIds), true)) {
            $p['user_id'] = (int)$_POST['user_id'];
        }
    }

    if ($p['nama_project'] === '') $errors[] = 'Nama projek wajib diisi.';
    if ($p['deskripsi'] === '')    $errors[] = 'Deskripsi wajib diisi.';
    if (!valid_url($p['link_project'])) $errors[] = 'Link pendukung harus diawali http:// atau https://';
    if (!valid_url($p['link_demo']))    $errors[] = 'Link demo harus diawali http:// atau https://';
    if (!$isEdit && (empty($_FILES['gambar']) || $_FILES['gambar']['error'] === UPLOAD_ERR_NO_FILE)) {
        $errors[] = 'Gambar projek wajib diupload.';
    }

    $newImg = null;
    if (!$errors) {
        try {
            $newImg = upload_image('gambar');
        } catch (Exception $ex) {
            $errors[] = $ex->getMessage();
        }
    }

    if (!$errors) {
        if ($isEdit) {
            $gambar = $p['gambar'];
            if ($newImg) {
                delete_image($p['gambar']);
                $gambar = $newImg;
            }
            $stmt = $pdo->prepare('UPDATE projects SET user_id=?, nama_project=?, deskripsi=?, gambar=?, link_project=?, link_demo=? WHERE id=?');
            $stmt->execute([
                $p['user_id'], $p['nama_project'], $p['deskripsi'], $gambar,
                $p['link_project'] ?: null, $p['link_demo'] ?: null, $id,
            ]);
            flash('success', 'Karya berhasil diperbarui.');
            redirect('project_detail.php?id=' . $id);
        } else {
            $stmt = $pdo->prepare('INSERT INTO projects (user_id, nama_project, deskripsi, gambar, link_project, link_demo) VALUES (?,?,?,?,?,?)');
            $stmt->execute([
                $p['user_id'], $p['nama_project'], $p['deskripsi'], $newImg,
                $p['link_project'] ?: null, $p['link_demo'] ?: null,
            ]);
            flash('success', 'Karya berhasil ditambahkan!');
            redirect('project_detail.php?id=' . $pdo->lastInsertId());
        }
    }
}

$title = ($isEdit ? 'Edit' : 'Tambah') . ' Karya - Stella';
include __DIR__ . '/includes/header.php';
?>
<a href="<?= url($isEdit ? 'project_detail.php?id=' . $id : 'projects.php') ?>" class="back">← Kembali</a>
<form method="post" enctype="multipart/form-data" class="panel form-card">
    <h1><?= $isEdit ? 'Edit Karya' : 'Tambah Karya Baru' ?></h1>
    <?php if ($errors): ?>
        <div class="alert alert-error"><?= implode('<br>', array_map('e', $errors)) ?></div>
    <?php endif; ?>
    <?= csrf_field() ?>

    <?php if (is_admin()): ?>
        <label>Pemilik Karya
            <select name="user_id">
                <?php foreach ($owners as $o): ?>
                    <option value="<?= $o['id'] ?>" <?= $o['id'] == $p['user_id'] ? 'selected' : '' ?>>
                        <?= e($o['nama']) ?> (<?= e(role_label($o['role'])) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
    <?php endif; ?>

    <label>Nama Projek <span class="req">*</span>
        <input type="text" name="nama_project" value="<?= e($p['nama_project']) ?>" maxlength="150" required>
    </label>

    <label>Deskripsi <span class="req">*</span>
        <textarea name="deskripsi" rows="6" required><?= e($p['deskripsi']) ?></textarea>
    </label>

    <label>Gambar Projek <?= $isEdit ? '<small class="muted">(kosongkan jika tidak diganti)</small>' : '<span class="req">*</span>' ?>
        <?php if ($isEdit && project_img($p)): ?>
            <img src="<?= project_img($p) ?>" class="preview" alt="Gambar saat ini">
        <?php endif; ?>
        <input type="file" name="gambar" accept="image/png,image/jpeg,image/webp,image/gif" <?= $isEdit ? '' : 'required' ?>>
        <small class="muted">JPG, PNG, WEBP, atau GIF. Maksimal 2 MB.</small>
    </label>

    <div class="grid-2">
        <label>Link Pendukung
            <input type="url" name="link_project" value="<?= e($p['link_project']) ?>" placeholder="https://github.com/…">
        </label>
        <label>Link Demo <small class="muted">(opsional)</small>
            <input type="url" name="link_demo" value="<?= e($p['link_demo'] ?? '') ?>" placeholder="https://…">
        </label>
    </div>

    <button class="btn btn-block"><?= $isEdit ? 'Simpan Perubahan' : 'Upload Karya' ?></button>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>
