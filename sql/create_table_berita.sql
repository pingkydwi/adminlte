-- SQL untuk membuat tabel berita
CREATE TABLE IF NOT EXISTS berita (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    isi TEXT NOT NULL,
    gambar VARCHAR(255),
    id_kategori INT NOT NULL,
    id_pengirim INT NOT NULL,
    status ENUM('draft','published','rejected') DEFAULT 'draft',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kategori) REFERENCES kategori(id),
    FOREIGN KEY (id_pengirim) REFERENCES tb_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
