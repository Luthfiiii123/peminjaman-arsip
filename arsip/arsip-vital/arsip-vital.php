<?php
session_start();
include '../../konektor.php';

/* =========================
   AMBIL INPUT
========================= */
$kata_kunci  = mysqli_real_escape_string($db, $_GET['search'] ?? '');
$klasifikasi = $_GET['klasifikasi'] ?? '';

$kondisi = [];

/* =========================
   SEARCH
========================= */
if ($kata_kunci !== '') {
    $kondisi[] = "(
        arsip_vital.uraian_informasi LIKE '%$kata_kunci%' OR
        arsip_vital.nomor_arsip LIKE '%$kata_kunci%' OR
        arsip_vital.lokasi_simpan LIKE '%$kata_kunci%'
    )";
}

/* =========================
   FILTER KLASIFIKASI
========================= */
if ($klasifikasi !== '' && $klasifikasi !== 'semua') {
    $kondisi[] = "arsip_vital.id_jenis = '$klasifikasi'";
}

/* =========================
   GABUNGKAN KONDISI
========================= */
$where = '';
if (!empty($kondisi)) {
    $where = "WHERE " . implode(" AND ", $kondisi);
}


/* =========================
   QUERY DATA ARSIP VITAL
========================= */
$nomor_urut = 1;

$query_string = "
    SELECT
        arsip_vital.*,
        
        -- AMBIL NAMA SUB KLASIFIKASI
        -- Pastikan nama kolom di tabel 'sub_klasifikasi' adalah 'nama_sub'
        sub_klasifikasi.nama_sub,
        
        -- AMBIL NAMA JENIS ARSIP
        jenis_arsip.nama_jenis,
        
        -- AMBIL NAMA METODE
        metode_perlindungan.nama_metode
        
    FROM arsip_vital
    
    -- PERBAIKAN UTAMA DI SINI:
    -- Gunakan 'id_sub' dari arsip_vital, BUKAN 'id_kode'
    LEFT JOIN sub_klasifikasi ON arsip_vital.id_sub = sub_klasifikasi.id_sub
    
    LEFT JOIN jenis_arsip ON arsip_vital.id_jenis = jenis_arsip.id_jenis
    LEFT JOIN metode_perlindungan ON arsip_vital.id_metode = metode_perlindungan.id_metode
    
    $where
    ORDER BY arsip_vital.id_arsip_vital DESC
";

$query_arsip = mysqli_query($db, $query_string);

if (!$query_arsip) {
    die("Query error: " . mysqli_error($db));
}

include '../../layout/header.php';
?>

<div class="d-flex">

    <?php include '../../layout/sidebar.php'; ?>

    <main class="flex-grow-1 p-4" style="background-color:#f3f4f6; min-height:100vh;">

        <div class="container-fluid">

            <div class="card shadow-lg rounded-3 overflow-hidden">

                <div class="card-header text-white d-flex justify-content-between align-items-center"
                     style="background: linear-gradient(90deg,#4f46e5,#4338ca);">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-archive me-2"></i> Data Arsip Vital
                    </h5>
                    <a href="arsip-vital-tambah.php" class="btn btn-light btn-sm">
                        <i class="fas fa-plus"></i> Tambah
                    </a>
                </div>

                <div class="card-body">

                    <form method="GET" class="row g-2 mb-4 align-items-center">
                        <div class="col-md-7">
                            <input type="text" name="search" class="form-control"
                                placeholder="Cari uraian, nomor arsip, atau lokasi simpan..."
                                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="klasifikasi" class="form-select">
                                <option value="" disabled <?= !isset($_GET['klasifikasi']) || $_GET['klasifikasi'] === '' ? 'selected' : '' ?>>
                                    Pilih Klasifikasi
                                </option>
                                <option value="semua" <?= ($_GET['klasifikasi'] ?? '') == 'semua' ? 'selected' : '' ?>>Semua Arsip</option>
                                <option value="vital" <?= ($_GET['klasifikasi'] ?? '') == 'vital' ? 'selected' : '' ?>>Arsip Vital</option>
                                <option value="permanen" <?= ($_GET['klasifikasi'] ?? '') == 'permanen' ? 'selected' : '' ?>>Arsip Permanen</option>
                                <option value="aktif" <?= ($_GET['klasifikasi'] ?? '') == 'aktif' ? 'selected' : '' ?>>Arsip Aktif</option>
                                <option value="inaktif" <?= ($_GET['klasifikasi'] ?? '') == 'inaktif' ? 'selected' : '' ?>>Arsip Inaktif</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> Cari
                            </button>
                        </div>
                        <?php if (!empty($_GET['search']) || !empty($_GET['klasifikasi'])): ?>
                        <div class="col-md-1 d-grid">
                            <a href="arsip-vital.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                        <?php endif; ?>
                    </form>


                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">

                            <thead class="table-light text-center">
                                <tr>
                                    <th style="width:5%">No</th>
                                    <th>Uraian Informasi Arsip</th>
                                    <th style="width:10%">Asal Arsip</th>
                                    <th style="width:15%">Sub Klasifikasi</th> <th style="width:12%">Jenis Arsip</th>
                                    <th style="width:12%">Nomor Arsip</th>
                                    <th style="width:10%">Retensi Arsip</th>
                                    <th>Lokasi Simpan</th>
                                    <th>Metode Perlindungan</th>
                                    <th style="width:12%">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                            <?php if (mysqli_num_rows($query_arsip) > 0) { ?>
                                <?php while ($data = mysqli_fetch_assoc($query_arsip)) { ?>
                                <tr>
                                    <td class="text-center"><?= $nomor_urut++ ?></td>

                                    <td class="fw-semibold">
                                        <?= htmlspecialchars($data['uraian_informasi']) ?>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge <?= $data['asal_arsip'] == 'internal' ? 'bg-primary' : 'bg-success' ?>">
                                            <?= ucfirst($data['asal_arsip']) ?>
                                        </span>
                                    </td>

                                    <td class="text-primary fw-bold">
                                        <?= htmlspecialchars($data['id_sub']) ?>
                                        
                                        <div class="text-dark fw-normal small mt-1">
                                            <?= htmlspecialchars($data['nama_sub'] ?? '- Nama tidak ditemukan -') ?>
                                        </div>
                                    </td>

                                    <td><?= htmlspecialchars($data['nama_jenis'] ?? '-') ?></td>

                                    <td><?= htmlspecialchars($data['nomor_arsip']) ?></td>

                                    <td class="text-center">
                                        <?= htmlspecialchars($data['retensi']) ?> Tahun
                                    </td>

                                    <td>
                                        <i class="fas fa-map-marker-alt text-muted me-1"></i>
                                        <?= htmlspecialchars($data['lokasi_simpan']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($data['nama_metode'] ?? '-') ?>
                                    </td>

                                    <td class="text-center">
                                        <a href="arsip-vital-edit.php?id=<?= $data['id_arsip_vital'] ?>"
                                           class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <a href="arsip-vital-hapus.php?id=<?= $data['id_arsip_vital'] ?>"
                                           onclick="return confirm('Yakin hapus data ini?')"
                                           class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-5">
                                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                        Tidak ada data arsip vital
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>

                        </table>
                    </div>

                </div>
            </div>

        </div>
    </main>
</div>