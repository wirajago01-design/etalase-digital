<?php
// Register khusus role SISWA
require_once __DIR__ . '/includes/auth.php';
if (is_logged_in()) redirect('projects.php');

$errors = [];
$nama = $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $nama  = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if ($nama === '') $errors[] = 'Nama wajib diisi.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    if (strlen($pass) < 6) $errors[] = 'Password minimal 6 karakter.';
    if ($pass !== $pass2) $errors[] = 'Konfirmasi password tidak sama.';

    if (!$errors) {
        $cek = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $cek->execute([$email]);
        if ($cek->fetch()) $errors[] = 'Email sudah terdaftar.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, 'siswa')");
        $stmt->execute([$nama, $email, password_hash($pass, PASSWORD_DEFAULT)]);
        flash('success', 'Pendaftaran berhasil! Silakan login.');
        redirect('login.php');
    }
}

$title = 'Daftar - Etalase Stella';
include __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
    <form method="post" class="panel auth-card">
        <h1>Daftar Siswa</h1>
        <p class="muted">Buat akun untuk mulai upload karyamu.</p>
        <?php if ($errors): ?>
            <div class="alert alert-error"><?= implode('<br>', array_map('e', $errors)) ?></div>
        <?php endif; ?>
        <?= csrf_field() ?>
        <label>Nama Lengkap
            <input type="text" name="nama" value="<?= e($nama) ?>" required>
        </label>
        <label>Email
            <input type="email" name="email" value="<?= e($email) ?>" required>
        </label>
        <div class="grid-2">
            <label>Password
                <input type="password" name="password" minlength="6" required>
            </label>
            <label>Ulangi Password
                <input type="password" name="password2" minlength="6" required>
            </label>
        </div>
        <button class="btn btn-block">Daftar</button>
        <p class="muted center small">Sudah punya akun? <a href="<?= url('login.php') ?>">Masuk</a></p>
    </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
