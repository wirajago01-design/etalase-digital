<?php
// =========================================================
// Middleware: session, login check, role check, helper
// =========================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

/* ---------- Helper umum ---------- */
function e($str)
{
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function url($path = '')
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect($path)
{
    header('Location: ' . url($path));
    exit;
}

function flash($type, $msg)
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function get_flash()
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

/* ---------- Tema tampilan ---------- */
function daftar_tema()
{
    return [
        'merah'  => ['Merah Putih', '#e3141b'],
        'biru'   => ['Biru Laut', '#0a6fb5'],
        'langit' => ['Langit', '#2f9bff'],
        'es'     => ['Es Kristal', '#0eaec2'],
        'samudra'=> ['Samudra Malam', '#0b1f3d'],
        'hijau'  => ['Hijau Daun', '#13915a'],
        'ungu'   => ['Ungu', '#6c3bdb'],
        'oranye' => ['Oranye', '#f26a1b'],
        'malam'  => ['Malam (gelap)', '#15151b'],
        'bawahlaut' => ['Bawah Laut', 'linear-gradient(180deg,#0b6fa8,#021a33)'],
        'galaksi'   => ['Galaksi', 'radial-gradient(circle at 35% 35%,#f9a8d4,#8b5cf6 55%,#0b0920)'],
        'pixel'     => ['Pixel Retro', 'linear-gradient(180deg,#6ec3ff 55%,#3ccf4e 55% 72%,#a0612c 72%)'],
        'batik'     => ['Batik Kawung', 'radial-gradient(circle,#e3b45a 0 30%,#5a2e14 32%)'],
        'cyber'     => ['Cyber Neon', 'linear-gradient(135deg,#ff2fd6,#00f0ff)'],
    ];
}

// kelas tambahan: keluarga biru & tema gelap
function tema_kelas()
{
    $t = tema_aktif();
    $kelas = 'tema-' . $t;
    if (in_array($t, ['biru', 'langit', 'es', 'samudra', 'bawahlaut'], true)) $kelas .= ' biru';
    if (in_array($t, ['malam', 'samudra', 'bawahlaut', 'galaksi', 'cyber'], true)) $kelas .= ' gelap';
    if (in_array($t, ['bawahlaut', 'galaksi', 'pixel', 'batik', 'cyber'], true)) $kelas .= ' spesial';
    return $kelas;
}

function tema_aktif()
{
    $t = $_COOKIE['tema'] ?? 'biru';
    return isset(daftar_tema()[$t]) ? $t : 'biru';
}

// link untuk ganti tema, tetap di halaman yang sama
function tema_url($kode)
{
    $q = $_GET;
    $q['tema'] = $kode;
    return strtok($_SERVER['REQUEST_URI'], '?') . '?' . http_build_query($q);
}

// simpan pilihan tema di cookie (berlaku 1 tahun)
if (isset($_GET['tema']) && isset(daftar_tema()[$_GET['tema']])) {
    setcookie('tema', $_GET['tema'], time() + 365 * 24 * 3600, '/');
    $_COOKIE['tema'] = $_GET['tema'];
    $q = $_GET;
    unset($q['tema']);
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . ($q ? '?' . http_build_query($q) : ''));
    exit;
}

/* ---------- CSRF ---------- */
function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function check_csrf()
{
    if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
        http_response_code(400);
        die('Token tidak valid. Silakan muat ulang halaman.');
    }
}

/* ---------- Auth ---------- */
function current_user()
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in()
{
    return isset($_SESSION['user']);
}

function has_role(array $roles)
{
    return is_logged_in() && in_array($_SESSION['user']['role'], $roles, true);
}

function is_admin()
{
    return has_role(['admin', 'super_admin']);
}

function is_super_admin()
{
    return has_role(['super_admin']);
}

// Middleware: wajib login
function require_login()
{
    if (!is_logged_in()) {
        flash('error', 'Silakan login terlebih dahulu.');
        redirect('login.php');
    }
}

// Middleware: wajib punya salah satu role
function require_role(array $roles)
{
    require_login();
    if (!has_role($roles)) {
        http_response_code(403);
        flash('error', 'Kamu tidak punya akses ke halaman tersebut.');
        redirect('projects.php');
    }
}

// Boleh edit/hapus karya: pemilik, admin, super admin
function can_manage_project(array $project)
{
    $u = current_user();
    if (!$u) return false;
    return $project['user_id'] == $u['id'] || is_admin();
}

// Boleh kelola akun tertentu:
// super admin -> semua akun (kecuali dirinya dihapus), admin -> hanya siswa
function can_manage_user(array $target)
{
    if (is_super_admin()) return true;
    if (has_role(['admin'])) return $target['role'] === 'siswa';
    return false;
}

function role_label($role)
{
    return [
        'super_admin' => 'Super Admin',
        'admin'       => 'Admin',
        'siswa'       => 'Siswa',
    ][$role] ?? $role;
}

/* ---------- Upload gambar ---------- */
// Return: nama file baru | null (tidak ada upload). Lempar Exception kalau gagal.
function upload_image($field)
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Upload gambar gagal (kode ' . $f['error'] . ').');
    }
    if ($f['size'] > MAX_UPLOAD) {
        throw new Exception('Ukuran gambar maksimal 2 MB.');
    }
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $info = @getimagesize($f['tmp_name']);
    if (!$info || !isset($allowed[$info['mime']])) {
        throw new Exception('File harus berupa gambar JPG, PNG, WEBP, atau GIF.');
    }
    $name = date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $allowed[$info['mime']];
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . $name)) {
        throw new Exception('Gagal menyimpan gambar ke folder uploads.');
    }
    return $name;
}

// URL gambar karya, atau null kalau file gambarnya tidak ada
function project_img(array $p)
{
    if (!empty($p['gambar']) && is_file(UPLOAD_DIR . basename($p['gambar']))) {
        return url('uploads/' . rawurlencode(basename($p['gambar'])));
    }
    return null;
}

// Sampul otomatis untuk karya yang belum punya gambar
function project_cover(array $p, $big = false)
{
    $judul = $p['nama_project'] ?? 'Karya';
    $kata = preg_split('/\s+/', trim($judul));
    $inisial = strtoupper(substr($kata[0] ?? '', 0, 1) . substr($kata[1] ?? '', 0, 1));
    $variasi = ((int)($p['id'] ?? 0)) % 4;
    return '<div class="cover cover-' . $variasi . ($big ? ' cover-big' : '') . '">'
         . '<span class="cover-mark">' . e($inisial) . '</span>'
         . '<span class="cover-title">' . e($judul) . '</span>'
         . (!empty($p['nama']) ? '<span class="cover-by">karya ' . e($p['nama']) . '</span>' : '')
         . '</div>';
}

function delete_image($name)
{
    if ($name && is_file(UPLOAD_DIR . basename($name))) {
        @unlink(UPLOAD_DIR . basename($name));
    }
}

function valid_url($u)
{
    return $u === '' || (filter_var($u, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $u));
}
