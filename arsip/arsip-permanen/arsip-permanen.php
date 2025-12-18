<?php
session_start();
include '../../konektor.php';

// Default User ID
$user_id = $_SESSION['user_id'] ?? 1;

/* ==============================================
   1. LOGIKA PENCARIAN & FILTER
============================================== */
$kata_kunci  = mysqli_real_escape_string($db, $_GET['search'] ?? '');
$klasifikasi = $_GET['klasifikasi'] ?? '';

$kondisi = [];

// A. Filter Kata Kunci
if ($kata_kunci !== '') {
    $kondisi[] = "(arsip_permanen.uraian_informasi LIKE '%$kata_kunci%' 
                   OR arsip_permanen.nomor_arsip LIKE '%$kata_kunci%' 
                   OR arsip_permanen.no_box LIKE '%$kata_kunci%')";
}

// B. Filter Klasifikasi
if ($klasifikasi !== '' && $klasifikasi !== 'semua') {
    $kondisi[] = "jenis_arsip.nama_jenis = '$klasifikasi'";
}

$where = '';
if (!empty($kondisi)) {
    $where = "WHERE " . implode(" AND ", $kondisi);
}

/* ==============================================
   2. QUERY DATA UTAMA
============================================== */
$nomor_urut = 1;
$query_string = "
    SELECT arsip_permanen.*, 
        kode_klasifikasi.kode_klasifikasi,
        sub_klasifikasi.nama_sub,
        sub_sub_klasifikasi.nama_subsub, 
        tingkat_perkembangan.nama_tingkat,
        nasib_akhir.nama_nasib,
        jenis_arsip.nama_jenis
    FROM arsip_permanen
    LEFT JOIN kode_klasifikasi ON arsip_permanen.id_kode = kode_klasifikasi.id_kode
    LEFT JOIN sub_klasifikasi ON arsip_permanen.id_sub = sub_klasifikasi.id_sub
    LEFT JOIN sub_sub_klasifikasi ON arsip_permanen.id_subsub = sub_sub_klasifikasi.id_subsub

    LEFT JOIN tingkat_perkembangan ON arsip_permanen.id_tingkat = tingkat_perkembangan.id_tingkat
    LEFT JOIN nasib_akhir ON arsip_permanen.id_nasib = nasib_akhir.id_nasib
    
    /* JOIN INI SEKARANG AKAN BERHASIL KARENA KOLOMNYA SUDAH ADA */
    LEFT JOIN jenis_arsip ON arsip_permanen.id_jenis = jenis_arsip.id_jenis
    
    $where
    ORDER BY arsip_permanen.id_arsip_permanen DESC
";
$query_arsip = mysqli_query($db, $query_string);

/* ==============================================
   3. QUERY OPSI MODAL
============================================== */
$q_kode     = mysqli_query($db, "SELECT * FROM kode_klasifikasi ORDER BY id_kode ASC");
$q_sub      = mysqli_query($db, "SELECT * FROM sub_klasifikasi ORDER BY id_sub ASC");
$q_subsub   = mysqli_query($db, "SELECT * FROM sub_sub_klasifikasi ORDER BY id_subsub ASC");
// Query KHUSUS Permanen
$q_jenis = mysqli_query($db, "SELECT * FROM jenis_arsip WHERE kategori='Permanen' ORDER BY nama_jenis ASC");
// $q_metode   = mysqli_query($db, "SELECT * FROM metode_perlindungan ORDER BY nama_metode ASC");
$q_tingkat  = mysqli_query($db, "SELECT * FROM tingkat_perkembangan ORDER BY id_tingkat ASC");
$q_nasib    = mysqli_query($db, "SELECT * FROM nasib_akhir ORDER BY id_nasib ASC");
// $q_keamanan = mysqli_query($db, "SELECT * FROM klasifikasi_keamanan ORDER BY id_keamanan ASC");

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
                        <i class="fas fa-save me-2"></i> Data Arsip Permanen
                    </h5>
                    <button type="button" class="btn btn-light btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="fas fa-plus"></i> Tambah Data
                    </button>
                </div>

                <div class="card-body">  
                        

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>No</th>
                                    <th style="min-width: 300px;">Uraian Informasi Arsip</th>
                                    <th style="min-width: 150px;">Kode Klasifikasi</th>
                                    <th style="min-width: 250px;">Jenis/Series Arsip</th>
                                    <th style="min-width: 150px;">Tingkat Perkembangan</th>
                                    <th style="min-width: 100px;">Kurun Waktu</th>
                                    <th style="min-width: 120px;">Jumlah</th>
                                    <th style="min-width: 150px;">Ket. Nasib Akhir / No Box</th>
                                    <!-- <th style="min-width: 100px;">No Box</th> -->
                                    <th style="min-width: 130px;">File</th>
                                    <th style="min-width: 130px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (mysqli_num_rows($query_arsip) > 0) { ?>
                                <?php while ($data = mysqli_fetch_assoc($query_arsip)) { ?>
                                <tr>
                                    <td class="text-center"><?= $nomor_urut++ ?></td>
                                    
                                    <td style="white-space: normal; min-width: 300px;">
                                        <?= htmlspecialchars($data['uraian_informasi']) ?>
                                    </td>

                                    <td>
                                        <?php 
                                        // PRIORITAS 1: Cek Sub-Sub
                                        if (!empty($data['nama_subsub'])) { 
                                        ?>
                                            <div class="fw-bold text-primary">
                                                <?= htmlspecialchars($data['id_subsub']) ?>
                                            </div>
                                            <div class="small text-muted">
                                                <?= htmlspecialchars($data['nama_subsub']) ?>
                                            </div>

                                        <?php 
                                        // PRIORITAS 2: Cek Sub (Jika Sub-Sub kosong)
                                        } elseif (!empty($data['nama_sub'])) { 
                                        ?>
                                            <div class="fw-bold text-success">
                                                <?= htmlspecialchars($data['id_sub']) ?>
                                            </div>
                                            <div class="small text-muted">
                                                <?= htmlspecialchars($data['nama_sub']) ?>
                                            </div>

                                        <?php 
                                        // PRIORITAS 3: Induk (Jika keduanya kosong)
                                        } else { 
                                        ?>
                                            <div class="fw-bold text-primary">
                                                <?= htmlspecialchars($data['kode_klasifikasi']) ?>
                                            </div>
                                            <div class="small text-muted">
                                                <?= htmlspecialchars($data['deskripsi']) ?>
                                            </div>
                                        <?php } ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($data['nama_jenis'] ?? '-') ?>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge bg-primary text-light"><?= htmlspecialchars($data['nama_tingkat']) ?></span>
                                    </td>
                                    
                                    <td class="text-center"><?= htmlspecialchars($data['kurun_waktu']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($data['jumlah']) ?></td>
                                    
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark mb-1"><?= htmlspecialchars($data['nama_nasib']) ?></span><br>
                                    </td>
                                    
                                    <!-- <td class="text-center fw-bold"><?= htmlspecialchars($data['no_box']) ?></td> -->

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
                                            <a href="#" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                            <a href="arsip-permanen-hapus.php?id=<?= $data['id_arsip_permanen'] ?>" onclick="return confirm('Anda Yakin Ingin Menghapus File ini?')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr><td colspan="10" class="text-center py-5 text-muted">Data Kosong</td></tr>
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
    <div class="modal-dialog modal-lg modal-dialog-scrollable"> 
        <div class="modal-content">
            
            <div class="modal-header text-white" style="background: linear-gradient(90deg, #d946ef, #c026d3);">
                <h5 class="modal-title fw-bold"><i class="fas fa-folder-plus me-2"></i>Tambah Arsip Permanen</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <form action="arsip-permanen-tambah.php" method="POST" enctype="multipart/form-data" id="formTambah">
                    <input type="hidden" name="user_id" value="<?= $user_id ?>">

                    <div class="row g-3">
                        
                        <div class="col-12">
                            <label class="form-label fw-semibold">Uraian Informasi Arsip</label>
                            <textarea name="uraian_informasi" class="form-control" rows="3" placeholder="Jelaskan isi arsip..." required></textarea>
                        </div>
                        
                        <!-- <div class="col-12">
                            <label class="form-label fw-semibold">Asal Arsip</label>
                            <select name="asal_arsip" class="form-select" required>
                                <option value="internal">Internal</option>
                                <option value="eksternal">Eksternal</option>
                            </select>
                        </div> -->

                        <div class="col-12">
                            <hr class="my-1">
                            <small class="text-primary fw-bold">KLASIFIKASI</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Kode Klasifikasi</label>
                            <select name="id_kode" id="parent_kode" class="form-select" required>
                                <option value="">-- Pilih Kode Klasifikasi --</option>
                                <?php 
                                // Reset pointer data ke awal (biar bisa di-loop ulang jika perlu)
                                mysqli_data_seek($q_kode, 0); 
                                
                                while($induk = mysqli_fetch_assoc($q_kode)): 
                                ?>
                                    <option value="<?= $induk['id_kode'] ?>">
                                        <?= $induk['kode_klasifikasi'] ?> - <?= $induk['deskripsi'] ?>
                                    </option>
                                    
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">2. Sub Klasifikasi</label>
                            <select name="id_sub" id="child_kode" class="form-select" required disabled>
                                <option value="">-- Pilih Induk Dulu --</option>
                                <?php mysqli_data_seek($q_sub, 0); while($s = mysqli_fetch_assoc($q_sub)): ?>
                                    <option value="<?= $s['id_sub'] ?>" data-parent="<?= $s['id_kode'] ?>"><?= $s['id_sub'] ?> - <?= $s['nama_sub'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">3. Sub-Sub (Opsional)</label>
                            <select name="id_subsub" id="grandchild_kode" class="form-select" disabled>
                                <option value="">-- Pilih Sub Dulu --</option>
                                <?php mysqli_data_seek($q_subsub, 0); while($ss = mysqli_fetch_assoc($q_subsub)): ?>
                                    <option value="<?= $ss['id_subsub'] ?>" data-parent="<?= $ss['id_sub'] ?>"><?= $ss['nama_subsub'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <hr class="my-1">
                            <small class="text-primary fw-bold">DETAIL ARSIP</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Jenis Arsip</label>
                            <select name="id_jenis" class="form-select" required>
                                <option value="">-- Pilih Jenis --</option>
                                <?php mysqli_data_seek($q_jenis, 0); while($j = mysqli_fetch_assoc($q_jenis)): ?>
                                    <option value="<?= $j['id_jenis'] ?>"><?= $j['nama_jenis'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Tingkat Perkembangan</label>
                            <select name="id_tingkat" class="form-select" required>
                                <option value="">-- Pilih --</option>
                                <?php mysqli_data_seek($q_tingkat, 0); while($tp = mysqli_fetch_assoc($q_tingkat)): ?>
                                    <option value="<?= $tp['id_tingkat'] ?>"><?= $tp['nama_tingkat'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Kurun Waktu (Tahun)</label>
                            <input type="number" name="kurun_waktu" class="form-control" placeholder="2022" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Jumlah</label>
                            <input type="text" name="jumlah" class="form-control" placeholder="1 Bundel" required>
                        </div>

                        <div class="col-12">
                            <hr class="my-1">
                            <small class="text-primary fw-bold">KEAMANAN & LOKASI</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Nasib Akhir</label>
                            <select name="id_nasib" class="form-select" required>
                                <option value="">-- Pilih Nasib --</option>
                                <?php mysqli_data_seek($q_nasib, 0); while($na = mysqli_fetch_assoc($q_nasib)): ?>
                                    <option value="<?= $na['id_nasib'] ?>"><?= $na['nama_nasib'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <hr class="my-1">
                            <small class="text-primary fw-bold">FILE DIGITAL</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Upload File (PDF)</label>
                            <input type="file" name="file_arsip" class="form-control" accept=".pdf" required>
                            <div class="form-text text-danger">*Wajib format PDF</div>
                        </div>

                    </div>
                </form> </div>
            
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="formTambah" name="simpan" class="btn btn-primary" style="background-color: #c026d3; border-color: #c026d3;">Simpan Data</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const parentSelect = document.getElementById("parent_kode");
    const childSelect = document.getElementById("child_kode");
    const grandChildSelect = document.getElementById("grandchild_kode");

    if (parentSelect && childSelect && grandChildSelect) {
        
        const childOptions = Array.from(childSelect.options);
        const grandChildOptions = Array.from(grandChildSelect.options);

        // INDUK -> SUB
        parentSelect.addEventListener("change", function() {
            const selectedParentID = this.value; 
            
            childSelect.innerHTML = '<option value="">-- Pilih Sub Klasifikasi --</option>';
            childSelect.disabled = true;
            grandChildSelect.innerHTML = '<option value="">-- Pilih Sub Terlebih Dahulu --</option>';
            grandChildSelect.disabled = true;

            if (selectedParentID) {
                const filtered = childOptions.filter(opt => opt.getAttribute("data-parent") === selectedParentID);
                if (filtered.length > 0) {
                    childSelect.disabled = false;
                    filtered.forEach(opt => childSelect.add(opt.cloneNode(true)));
                } else {
                    childSelect.innerHTML = '<option value="">-- Tidak ada sub klasifikasi --</option>';
                }
            }
        });

        // SUB -> SUBSUB
        childSelect.addEventListener("change", function() {
            const selectedSubID = this.value;

            grandChildSelect.innerHTML = '<option value="">-- Pilih Sub-Sub Klasifikasi --</option>';
            grandChildSelect.disabled = true;

            if (selectedSubID) {
                const filtered = grandChildOptions.filter(opt => opt.getAttribute("data-parent") === selectedSubID);
                if (filtered.length > 0) {
                    grandChildSelect.disabled = false;
                    filtered.forEach(opt => grandChildSelect.add(opt.cloneNode(true)));
                } else {
                    grandChildSelect.innerHTML = '<option value="">-- Tidak ada sub-sub klasifikasi --</option>';
                }
            }
        });
    }
});
</script>