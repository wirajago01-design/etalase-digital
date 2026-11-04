</main>
<footer class="footer">
    <div class="container">
        <span><b>Etalase Stella</b>, tempat pajang karya digital siswa</span>
        <span class="tema-picker">
            <span>Tema:</span>
            <?php foreach (daftar_tema() as $kode => [$nama, $warna]): ?>
                <a href="<?= e(tema_url($kode)) ?>" class="tema-dot<?= tema_aktif() === $kode ? ' aktif' : '' ?>"
                   style="background: <?= $warna ?>" title="<?= e($nama) ?>" aria-label="Tema <?= e($nama) ?>"></a>
            <?php endforeach; ?>
        </span>
    </div>
</footer>
</body>
</html>
