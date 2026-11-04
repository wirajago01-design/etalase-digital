# Etalase Digital Stella

Website etalase (galeri) karya digital siswa, dibuat dengan **PHP Native**, **CSS**, dan **MySQL**.
Siswa bisa memajang karya, melihat karya teman, serta mengedit atau menghapus karyanya sendiri.
Admin dan Super Admin bisa mengelola semua karya dan data pengguna.

Dibuat oleh: **Muhammad Wira Andika** — XI RPL

## Fitur

- Login multi-role: Super Admin, Admin, Siswa
- Daftar akun khusus siswa
- Etalase semua karya + pencarian
- Tambah, edit, hapus karya (nama, deskripsi, gambar, link pendukung, link demo)
- Panel admin: kelola semua karya dan data siswa
- Super Admin: kelola akun admin
- Pilihan tema tampilan (Merah Putih, Biru Laut, Bawah Laut, Galaksi, Pixel Retro, Batik, Cyber Neon, dll.)

## Hak Akses

| Fitur | Super Admin | Admin | Siswa |
|---|:---:|:---:|:---:|
| Kelola user & admin | ✅ | Data siswa saja | ❌ |
| Kelola semua karya | ✅ | ✅ | ❌ |
| Lihat semua karya | ✅ | ✅ | ✅ |
| Tambah karya sendiri | ✅ | ✅ | ✅ |
| Edit / hapus karya sendiri | ✅ | ✅ | ✅ |

## Struktur Folder

```
etalase-digital-stella/
├── aset/            # Gambar sampul karya
├── screenshot/      # Screenshot tampilan website
├── database/        # File database (.sql)
├── admin/           # File PHP panel admin
├── assets/css/      # File CSS (style.css)
├── config/          # Koneksi database
├── includes/        # File PHP bersama (auth, header, footer)
├── demo/            # Demo karya
├── uploads/         # Gambar yang diupload lewat website
└── *.php            # Halaman utama (login, etalase, form karya, dll.)
```

## Cara Menjalankan (XAMPP)

1. Copy folder ini ke `htdocs`.
2. Nyalakan **Apache** dan **MySQL** di XAMPP.
3. Buka `http://localhost/phpmyadmin` → **Import** → pilih `database/database.sql`.
4. Buka `http://localhost/etalase-digital-stella`.

## Akun Uji

| Role | Email |
|---|---|
| Super Admin | superadmin@stella.sch.id |
| Admin | admin@stella.sch.id |
| Siswa | siswa@stella.sch.id |

Password awal: `password123` (segera ganti setelah login).
