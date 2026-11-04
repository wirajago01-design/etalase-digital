<?php
// =========================================================
// Konfigurasi koneksi database & aplikasi
// =========================================================

// BASE_URL dideteksi otomatis dari lokasi folder project di htdocs,
// jadi nama folder bebas (mis. /etalase-digital-stella atau /stella).
// Kalau tetap salah, isi manual: define('BASE_URL', '/nama-folder-kamu');
$__root = str_replace('\\', '/', realpath(__DIR__ . '/..'));
$__doc  = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '');
$__base = ($__doc !== '' && stripos($__root, $__doc) === 0) ? substr($__root, strlen($__doc)) : '';
define('BASE_URL', rtrim('/' . ltrim($__base, '/'), '/'));
unset($__root, $__doc, $__base);

define('DB_HOST', 'localhost');
define('DB_NAME', 'etalase_stella');
define('DB_USER', 'root');
define('DB_PASS', '');

define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_UPLOAD', 2 * 1024 * 1024); // 2 MB

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Koneksi database gagal: ' . htmlspecialchars($e->getMessage()));
}

// Otomatis menambah kolom link_demo kalau database lama belum punya
try {
    if (!$pdo->query("SHOW COLUMNS FROM projects LIKE 'link_demo'")->fetch()) {
        $pdo->exec('ALTER TABLE projects ADD link_demo VARCHAR(255) NULL AFTER link_project');
    }
} catch (Exception $e) {
    // abaikan, tabel mungkin belum dibuat
}
