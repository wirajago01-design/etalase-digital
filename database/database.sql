-- =========================================================
-- Etalase Digital Stella - Database
-- Import file ini lewat phpMyAdmin atau: mysql -u root -p < database.sql
-- =========================================================

CREATE DATABASE IF NOT EXISTS etalase_stella
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE etalase_stella;

DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  nama       VARCHAR(100) NOT NULL,
  email      VARCHAR(150) NOT NULL UNIQUE,
  password   VARCHAR(255) NOT NULL,
  role       ENUM('super_admin','admin','siswa') NOT NULL DEFAULT 'siswa',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE projects (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  user_id      INT NOT NULL,
  nama_project VARCHAR(150) NOT NULL,
  deskripsi    TEXT NOT NULL,
  gambar       VARCHAR(255) DEFAULT NULL,
  link_project VARCHAR(255) DEFAULT NULL,
  link_demo    VARCHAR(255) DEFAULT NULL,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_projects_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Akun uji (password semua: password123)
INSERT INTO users (nama, email, password, role) VALUES
('Super Admin', 'superadmin@stella.sch.id', '$2y$10$u9k.RpHuvAIvI0R8T1297eghOyNkLsSDzdWDqZv66uQmdQM.15OMq', 'super_admin'),
('Admin Stella', 'admin@stella.sch.id',     '$2y$10$u9k.RpHuvAIvI0R8T1297eghOyNkLsSDzdWDqZv66uQmdQM.15OMq', 'admin'),
('Siswa Stella', 'siswa@stella.sch.id',     '$2y$10$u9k.RpHuvAIvI0R8T1297eghOyNkLsSDzdWDqZv66uQmdQM.15OMq', 'siswa');

INSERT INTO projects (user_id, nama_project, deskripsi, link_project, link_demo) VALUES
(3, 'Website Profil Sekolah', 'Website profil sekolah sederhana dengan HTML, CSS dan PHP. Berisi halaman beranda, profil, dan galeri kegiatan.', 'https://github.com/', NULL);
