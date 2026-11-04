<?php
// $title bisa di-set sebelum include file ini
$u = current_user();
$flash = get_flash();
$page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Etalase Digital Stella') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=Bricolage+Grotesque:opsz,wght@12..96,600..800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>?v=<?= @filemtime(__DIR__ . '/../assets/css/style.css') ?>">
</head>
<body class="<?= tema_kelas() ?>">
<div class="deko" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i></div>
<header class="navbar">
    <div class="container nav-inner">
        <a href="<?= url($u ? 'projects.php' : 'index.php') ?>" class="brand">
            <span class="brand-mark">ES</span> Etalase <b>Stella</b>
        </a>

        <input type="checkbox" id="nav-toggle" class="nav-toggle">
        <label for="nav-toggle" class="nav-burger" aria-label="Menu"><span></span><span></span><span></span></label>

        <nav class="nav-links">
            <?php if ($u): ?>
                <a href="<?= url('projects.php') ?>" class="<?= $page === 'projects.php' ? 'active' : '' ?>">Etalase</a>
                <a href="<?= url('my_projects.php') ?>" class="<?= $page === 'my_projects.php' ? 'active' : '' ?>">Karyaku</a>
                <?php if (is_admin()): ?>
                    <a href="<?= url('admin/index.php') ?>" class="<?= strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? 'active' : '' ?>">Panel Admin</a>
                <?php endif; ?>
                <a href="<?= url('project_form.php') ?>" class="btn btn-sm">+ Tambah Karya</a>
                <div class="user-chip">
                    <span class="avatar"><?= e(strtoupper(substr($u['nama'], 0, 1))) ?></span>
                    <span class="user-meta">
                        <strong><?= e($u['nama']) ?></strong>
                        <small><?= e(role_label($u['role'])) ?></small>
                    </span>
                    <a href="<?= url('logout.php') ?>" class="logout" title="Keluar">Keluar</a>
                </div>
            <?php else: ?>
                <a href="<?= url('login.php') ?>">Masuk</a>
                <a href="<?= url('register.php') ?>" class="btn btn-sm">Daftar</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container main">
<?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
<?php endif; ?>
