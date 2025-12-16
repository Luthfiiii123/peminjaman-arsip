/* 1. Tambahkan kolom id_jenis ke tabel arsip_permanen */
ALTER TABLE arsip_permanen 
ADD COLUMN id_jenis INT(11) NULL AFTER uraian_informasi;

/* 2. (Opsional) Hubungkan dengan tabel jenis_arsip agar aman */
ALTER TABLE arsip_permanen
ADD CONSTRAINT fk_ap_jenis
FOREIGN KEY (id_jenis) REFERENCES jenis_arsip(id_jenis)
ON DELETE SET NULL 
ON UPDATE CASCADE;