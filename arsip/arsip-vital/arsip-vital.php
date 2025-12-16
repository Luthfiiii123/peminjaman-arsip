<?php
session_start();
include '../../konektor.php';

/* ==============================================
   1. LOGIKA PENCARIAN & FILTER
============================================== */
$kata_kunci  = mysqli_real_escape_string($db, $_GET['search'] ?? '');
$klasifikasi = $_GET['klasifikasi'] ?? '';
$kondisi = [];

if ($kata_kunci !== '') {
    $kondisi[] = "(arsip_vital.uraian_informasi LIKE '%$kata_kunci%' 
                   OR arsip_vital.nomor_arsip LIKE '%$kata_kunci%' 
                   OR arsip_vital.lokasi_simpan LIKE '%$kata_kunci%')";
}
if ($klasifikasi !== '' && $klasifikasi !== 'semua') {
    $kondisi[] = "jenis_arsip.nama_jenis = '$klasifikasi'";
}
$where = '';
if (!empty($kondisi)) {
    $where = "WHERE " . implode(" AND ", $kondisi);
}

/* ==============================================
   2. QUERY DATA
============================================== */
$nomor_urut = 1;
$query_string = "
    SELECT arsip_vital.*, 
        sub_klasifikasi.nama_sub, 
        jenis_arsip.nama_jenis, 
        metode_perlindungan.nama_metode
    FROM arsip_vital
    LEFT JOIN sub_klasifikasi ON arsip_vital.id_sub = sub_klasifikasi.id_sub
    LEFT JOIN jenis_arsip ON arsip_vital.id_jenis = jenis_arsip.id_jenis
    LEFT JOIN metode_perlindungan ON arsip_vital.id_metode = metode_perlindungan.id_metode
    $where
    ORDER BY arsip_vital.id_arsip_vital DESC
";
$query_arsip = mysqli_query($db, $query_string);

/* ==============================================
   3. QUERY OPSI MODAL
============================================== */
$q_kode = mysqli_query($db, "SELECT * FROM kode_klasifikasi ORDER BY id_kode ASC");
$q_sub  = mysqli_query($db, "SELECT * FROM sub_klasifikasi ORDER BY id_sub ASC");
$q_jenis = mysqli_query($db, "SELECT * FROM jenis_arsip WHERE kategori='Vital' ORDER BY nama_jenis ASC");
$q_metode = mysqli_query($db, "SELECT * FROM metode_perlindungan ORDER BY nama_metode ASC");

include '../../layout/header.php';
?>

<div class="d-flex">
    <?php include '../../layout/sidebar.php'; ?>

    <main class="flex-grow-1 p-4" style="background-color: #f3f4f6; min-height:100vh; min-width: 0;">
        <div class="container-fluid">
            <div class="card shadow-lg rounded-3 overflow-hidden">
                
                <div class="card-header text-white d-flex justify-content-between align-items-center"
                     style="background: linear-gradient(90deg, #4f46e5, #4338ca);">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-archive me-2"></i> Data Arsip Vital
                    </h5>
                    <button type="button" class="btn btn-light btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="fas fa-plus"></i> Tambah Arsip
                    </button>
                </div>

                <div class="card-body">
                    <form method="GET" class="row g-2 mb-4 align-items-center">
                        <div class="col-md-7">
                            <input type="text" name="search" class="form-control" placeholder="Cari uraian, nomor, lokasi..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="klasifikasi" class="form-select">
                                <option value="semua" <?= ($klasifikasi == 'semua') ? 'selected' : '' ?>>Semua Arsip</option>
                                <option value="Vital" <?= ($klasifikasi == 'Vital') ? 'selected' : '' ?>>Arsip Vital</option>
                                <option value="Permanen" <?= ($klasifikasi == 'Permanen') ? 'selected' : '' ?>>Arsip Permanen</option>
                                <option value="Aktif" <?= ($klasifikasi == 'Aktif') ? 'selected' : '' ?>>Arsip Aktif</option>
                                <option value="Inaktif" <?= ($klasifikasi == 'Inaktif') ? 'selected' : '' ?>>Arsip Inaktif</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Cari</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>No</th>                                  
                                    <th style="min-width: 150px;">Uraian Informasi</th>
                                    <th style="min-width: 100px;">Asal</th>
                                    <th style="min-width: 150px;">Kode Klasifikasi</th>
                                    <th style="min-width: 150px;">Jenis</th>
                                    <th style="min-width: 130px;">Nomor Arsip</th>
                                    <th style="min-width: 100px;">Retensi</th>
                                    <th style="min-width: 150px;">Lokasi</th>
                                    <th style="min-width: 130px;">Metode</th>
                                    <th style="min-width: 130px;">File</th>
                                    <th style="min-width: 130px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (mysqli_num_rows($query_arsip) > 0) { ?>
                                <?php while ($data = mysqli_fetch_assoc($query_arsip)) { ?>
                                <tr>
                                    <td class="text-center"><?= $nomor_urut++ ?></td>
                                    <td><?= htmlspecialchars($data['uraian_informasi']) ?></td>
                                    
                                    <td class="text-center">
                                        <span class="badge <?= $data['asal_arsip'] == 'internal' ? 'bg-primary' : 'bg-success' ?>">
                                            <?= ucfirst($data['asal_arsip']) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="text-primary fw-bold"><?= htmlspecialchars($data['id_sub']) ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($data['nama_sub']) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($data['nama_jenis']) ?></td>
                                    <td><?= htmlspecialchars($data['nomor_arsip']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($data['retensi']) ?> Tahun</td>
                                    <td><?= htmlspecialchars($data['lokasi_simpan']) ?></td>
                                    <td><?= htmlspecialchars($data['nama_metode']) ?></td>
                                    
                                    <td class="text-center">
                                        <?php if (!empty($data['file_pdf'])): ?>
                                            <a href="../../uploads/<?= $data['file_pdf'] ?>" target="_blank" class="btn btn-sm btn-outline-danger" title="Lihat PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="arsip-vital-edit.php?id=<?= $data['id_arsip_vital'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                            <a href="arsip-vital-hapus.php?id=<?= $data['id_arsip_vital'] ?>" onclick="return confirm('Anda Yakin Ingin Menghapus File ini?')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr><td colspan="11" class="text-center py-5 text-muted">Data Kosong</td></tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-folder-plus me-2"></i>Tambah Arsip Vital</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="arsip-vital-tambah.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Uraian Informasi Arsip</label>
                            <textarea name="uraian_informasi" class="form-control" rows="2" placeholder="Jelaskan isi arsip..." required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Asal Arsip</label>
                            <select name="asal_arsip" class="form-select" required>
                                <option value="internal">Internal</option>
                                <option value="eksternal">Eksternal</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kode Klasifikasi</label>
                            <select name="id_kode" id="parent_kode" class="form-select" required>
                                <option value="">-- Pilih Kode Induk --</option>
                                <?php 
                                mysqli_data_seek($q_kode, 0); 
                                while($induk = mysqli_fetch_assoc($q_kode)): 
                                ?>
                                    <option value="<?= $induk['id_kode'] ?>">
                                        <?= $induk['id_kode'] ?> - <?= $induk['kode_klasifikasi'] ?> (<?= $induk['deskripsi'] ?? '' ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Sub Klasifikasi (Pilih Induk Dulu)</label>
                            <select name="id_sub" id="child_kode" class="form-select" required>
                                <option value="">-- Pilih Kode Induk Terlebih Dahulu --</option>
                                <?php 
                                mysqli_data_seek($q_sub, 0); 
                                while($anak = mysqli_fetch_assoc($q_sub)): 
                                ?>
                                    <option value="<?= $anak['id_sub'] ?>" data-parent="<?= $anak['id_kode'] ?>">
                                        <?= $anak['id_sub'] ?> - <?= $anak['nama_sub'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Jenis Arsip</label>
                            <select name="id_jenis" class="form-select" required>
                                <option value="">-- Pilih Jenis Arsip --</option>
                                <?php 
                                // Pastikan variabel $q_jenis sudah didefinisikan sesuai halaman (Vital/Permanen)
                                if (isset($q_jenis)) {
                                    mysqli_data_seek($q_jenis, 0); 
                                    while($j = mysqli_fetch_assoc($q_jenis)): 
                                ?>
                                    <option value="<?= $j['id_jenis'] ?>">
                                        <?= $j['nama_jenis'] ?>
                                    </option>
                                <?php 
                                    endwhile; 
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nomor Arsip</label>
                            <input type="text" name="nomor_arsip" class="form-control" placeholder="Contoh: W.16.PB.05.01-1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Retensi (Tahun)</label>
                            <input type="number" name="retensi" class="form-control" placeholder="10" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Lokasi Simpan</label>
                            <input type="text" name="lokasi_simpan" class="form-control" placeholder="Contoh: Brangkas BMN" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Metode Perlindungan</label>
                            <select name="id_metode" class="form-select" required>
                                <option value="">-- Pilih Metode --</option>
                                <?php 
                                mysqli_data_seek($q_metode, 0);
                                while($m = mysqli_fetch_assoc($q_metode)): ?>
                                    <option value="<?= $m['id_metode'] ?>"><?= $m['nama_metode'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Upload File (PDF)</label>
                            <input type="file" name="file_arsip" class="form-control" accept=".pdf" required>
                            <div class="form-text text-danger">*Wajib PDF</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan" class="btn btn-primary">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const parentSelect = document.getElementById("parent_kode");
    const childSelect = document.getElementById("child_kode");
    
    if (parentSelect && childSelect) {
        const originalOptions = Array.from(childSelect.options);

        parentSelect.addEventListener("change", function() {
            const selectedParentID = this.value; 

            childSelect.innerHTML = '<option value="">-- Pilih Sub Klasifikasi --</option>';
            childSelect.disabled = true;

            if (selectedParentID) {
                const filteredOptions = originalOptions.filter(opt => opt.getAttribute("data-parent") === selectedParentID);

                if (filteredOptions.length > 0) {
                    childSelect.disabled = false;
                    filteredOptions.forEach(opt => childSelect.add(opt.cloneNode(true)));
                } else {
                    childSelect.innerHTML = '<option value="">-- Tidak ada sub klasifikasi --</option>';
                }
            }
        });
    }
});
</script>