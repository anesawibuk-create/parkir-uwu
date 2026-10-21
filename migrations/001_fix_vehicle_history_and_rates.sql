-- BACKUP database parkir_db sebelum menjalankan migrasi.
-- Jalankan migrasi ini satu kali pada database lama melalui phpMyAdmin.
-- 1. Izinkan plat nomor sama digunakan kembali setelah sesi parkir sebelumnya selesai.
ALTER TABLE kendaraan
    DROP INDEX plat_nomor,
    ADD COLUMN plat_aktif VARCHAR(15) GENERATED ALWAYS AS (CASE WHEN status = 'parkir' THEN UPPER(plat_nomor) ELSE NULL END) STORED,
    ADD UNIQUE KEY uq_kendaraan_plat_aktif (plat_aktif),
    ADD KEY idx_kendaraan_status_area (status, id_area);

-- 2. Simpan harga per jam pada setiap sesi agar perubahan tarif baru tidak mengubah harga
-- kendaraan yang sedang parkir maupun struk historisnya.
ALTER TABLE kendaraan ADD COLUMN tarif_per_jam_snapshot DECIMAL(10,0) NULL AFTER id_tarif;

-- Baris lama dengan id_tarif=0 tidak memiliki snapshot tarif. Ambil tarif terbaru per jenis
-- sebagai penyesuaian awal; verifikasi nilai ini karena tarif masuk asli tidak tersimpan.
UPDATE kendaraan k
JOIN (
    SELECT jenis_kendaraan, MAX(id_tarif) AS id_tarif
    FROM tarif
    GROUP BY jenis_kendaraan
) latest ON LOWER(latest.jenis_kendaraan) = LOWER(k.jenis_kendaraan)
SET k.id_tarif = latest.id_tarif
WHERE k.id_tarif = 0;

UPDATE kendaraan k
JOIN tarif t ON t.id_tarif = k.id_tarif
SET k.tarif_per_jam_snapshot = t.tarif_per_jam
WHERE k.tarif_per_jam_snapshot IS NULL OR k.tarif_per_jam_snapshot <= 0;
