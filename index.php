<?php
require_once __DIR__ . '/includes/auth.php';
if (is_logged_in()) redirect('projects.php');

$title = 'Etalase Digital Stella';
$total = $pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();
$siswa = $pdo->query("SELECT COUNT(*) FROM users WHERE role='siswa'")->fetchColumn();
// 3 karya terbaru untuk dipajang di etalase
$pajang = $pdo->query('SELECT p.id, p.nama_project, p.gambar, u.nama FROM projects p
                       JOIN users u ON u.id = p.user_id ORDER BY p.created_at DESC LIMIT 3')->fetchAll();
include __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div>
        <h1>Karya kamu layak dipajang.</h1>
        <p class="lead">Upload projek, tulis cerita di baliknya, tambahkan link dan demo. Teman satu sekolah bisa langsung lihat hasilnya.</p>
        <div class="hero-actions">
            <a href="<?= url('register.php') ?>" class="btn">Daftar sebagai siswa</a>
            <a href="<?= url('login.php') ?>" class="btn btn-ghost">Masuk</a>
        </div>
        <div class="stats">
            <div><strong><?= (int)$total ?></strong><span>karya dipajang</span></div>
            <div><strong><?= (int)$siswa ?></strong><span>siswa ikut</span></div>
        </div>
    </div>

    <div class="vitrine">
        <?php if ($pajang): foreach ($pajang as $p): ?>
            <a href="<?= url('login.php') ?>" class="polaroid">
                <div class="ph-img">
                    <?php if (project_img($p)): ?>
                        <img src="<?= project_img($p) ?>" alt="<?= e($p['nama_project']) ?>">
                    <?php else: ?>
                        <?= project_cover($p) ?>
                    <?php endif; ?>
                </div>
                <strong><?= e($p['nama_project']) ?></strong>
                <small>oleh <?= e($p['nama']) ?></small>
            </a>
        <?php endforeach; else: ?>
            <div class="polaroid"><div class="ph-img"><div class="no-img">?</div></div>
                <strong>Karyamu bisa jadi yang pertama</strong><small>Etalase masih kosong</small></div>
        <?php endif; ?>
    </div>
</section>

<section class="steps">
    <div class="step"><b>1</b><h3>Buat akun</h3><p>Daftar pakai email, langsung bisa masuk sebagai siswa.</p></div>
    <div class="step"><b>2</b><h3>Upload karya</h3><p>Isi nama projek, deskripsi, gambar, link pendukung, dan demo kalau ada.</p></div>
    <div class="step"><b>3</b><h3>Tampil di etalase</h3><p>Karyamu muncul di galeri dan bisa dilihat semua siswa.</p></div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
