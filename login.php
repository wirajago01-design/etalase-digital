<?php
require_once __DIR__ . '/includes/auth.php';
if (is_logged_in()) redirect('projects.php');

$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'   => $user['id'],
            'nama' => $user['nama'],
            'email'=> $user['email'],
            'role' => $user['role'],
        ];
        flash('success', 'Selamat datang, ' . $user['nama'] . '!');
        redirect('projects.php');
    }
    $error = 'Email atau password salah.';
}

$title = 'Masuk - Etalase Stella';
include __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
    <form method="post" class="panel auth-card">
        <h1>Masuk</h1>
        <p class="muted">Masuk sebagai Siswa, Admin, atau Super Admin.</p>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <?= csrf_field() ?>
        <label>Email
            <input type="email" name="email" value="<?= e($email) ?>" required autofocus>
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <button class="btn btn-block">Masuk</button>
        <p class="muted center small">Belum punya akun? <a href="<?= url('register.php') ?>">Daftar di sini</a></p>
    </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
