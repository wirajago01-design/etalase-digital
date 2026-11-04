<?php
// Tambah / edit user
// - Super Admin bisa membuat & mengubah role apa saja
// - Admin hanya bisa menambah / mengedit siswa
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin', 'super_admin']);

$id = (int)($_GET['id'] ?? 0);
$isEdit = $id > 0;
$usr = ['nama' => '', 'email' => '', 'role' => 'siswa'];

if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $usr = $stmt->fetch();
    if (!$usr || !can_manage_user($usr)) {
        flash('error', 'Kamu tidak punya akses untuk mengedit akun ini.');
        redirect('admin/users.php');
    }
}

$roles = is_super_admin() ? ['siswa', 'admin', 'super_admin'] : ['siswa'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $usr['nama']  = trim($_POST['nama'] ?? '');
    $usr['email'] = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'siswa';
    $pass = $_POST['password'] ?? '';

    if (!in_array($role, $roles, true)) $role = 'siswa';
    // super admin tidak boleh menurunkan role dirinya sendiri (biar tidak terkunci)
    if ($isEdit && $id == current_user()['id']) $role = $usr['role'];
    $usr['role'] = $role;

    if ($usr['nama'] === '') $errors[] = 'Nama wajib diisi.';
    if (!filter_var($usr['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    if (!$isEdit && strlen($pass) < 6) $errors[] = 'Password minimal 6 karakter.';
    if ($isEdit && $pass !== '' && strlen($pass) < 6) $errors[] = 'Password baru minimal 6 karakter.';

    if (!$errors) {
        $cek = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ?');
        $cek->execute([$usr['email'], $id]);
        if ($cek->fetch()) $errors[] = 'Email sudah dipakai akun lain.';
    }

    if (!$errors) {
        if ($isEdit) {
            if ($pass !== '') {
                $stmt = $pdo->prepare('UPDATE users SET nama=?, email=?, role=?, password=? WHERE id=?');
                $stmt->execute([$usr['nama'], $usr['email'], $usr['role'], password_hash($pass, PASSWORD_DEFAULT), $id]);
            } else {
                $stmt = $pdo->prepare('UPDATE users SET nama=?, email=?, role=? WHERE id=?');
                $stmt->execute([$usr['nama'], $usr['email'], $usr['role'], $id]);
            }
            // update session kalau edit akun sendiri
            if ($id == current_user()['id']) {
                $_SESSION['user']['nama']  = $usr['nama'];
                $_SESSION['user']['email'] = $usr['email'];
            }
            flash('success', 'Data akun berhasil diperbarui.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO users (nama, email, password, role) VALUES (?,?,?,?)');
            $stmt->execute([$usr['nama'], $usr['email'], password_hash($pass, PASSWORD_DEFAULT), $usr['role']]);
            flash('success', role_label($usr['role']) . ' baru berhasil ditambahkan.');
        }
        redirect('admin/users.php');
    }
}

$title = ($isEdit ? 'Edit' : 'Tambah') . ' Akun - Stella';
include __DIR__ . '/../includes/header.php';
?>
<a href="<?= url('admin/users.php') ?>" class="back">← Kembali</a>
<form method="post" class="panel form-card narrow">
    <h1><?= $isEdit ? 'Edit Akun' : 'Tambah Akun' ?></h1>
    <?php if ($errors): ?>
        <div class="alert alert-error"><?= implode('<br>', array_map('e', $errors)) ?></div>
    <?php endif; ?>
    <?= csrf_field() ?>

    <label>Nama
        <input type="text" name="nama" value="<?= e($usr['nama']) ?>" required>
    </label>
    <label>Email
        <input type="email" name="email" value="<?= e($usr['email']) ?>" required>
    </label>
    <label>Role
        <select name="role" <?= ($isEdit && $id == current_user()['id']) || count($roles) === 1 ? 'disabled' : '' ?>>
            <?php foreach ($roles as $r): ?>
                <option value="<?= $r ?>" <?= $usr['role'] === $r ? 'selected' : '' ?>><?= role_label($r) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (count($roles) === 1): ?><input type="hidden" name="role" value="siswa"><?php endif; ?>
    </label>
    <label>Password <?= $isEdit ? '<small class="muted">(kosongkan jika tidak diganti)</small>' : '' ?>
        <input type="password" name="password" minlength="6" <?= $isEdit ? '' : 'required' ?>>
    </label>
    <button class="btn btn-block"><?= $isEdit ? 'Simpan' : 'Tambah Akun' ?></button>
</form>
<?php include __DIR__ . '/../includes/footer.php'; ?>
