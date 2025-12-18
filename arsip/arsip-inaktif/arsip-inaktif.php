<?php
session_start();
include '../../konektor.php';

// Cek Login
// if (!isset($_SESSION['user_id'])) {
//     header("Location: ../../index.php");
//     exit;
// }

// Default User ID
$user_id = $_SESSION['user_id'] ?? 1;

/* ==============================================
   1. LOGIKA PENCARIAN & FILTER
============================================== */
$kata_kunci  = mysqli_real_escape_string($db, $_GET['search'] ?? '');
$klasifikasi = $_GET['klasifikasi'] ?? '';

$kondisi = []; // Array penampung filter
$where = "";   // Default variabel where kosong agar tidak error

// A. Filter Kata Kunci
if ($kata_kunci !== '') {
    // Gunakan [] agar masuk ke array, dan HAPUS kata 'WHERE'
    $kondisi[] = "(arsip_aktif.uraian_arsip LIKE '%$kata_kunci%' 
                   OR arsip_aktif.uraian_berkas LIKE '%$kata_kunci%' 
                   OR arsip_aktif.no_berkas LIKE '%$kata_kunci%')";
}

// B. Filter Klasifikasi
if ($klasifikasi !== '' && $klasifikasi !== 'semua') {
    $kondisi[] = "arsip_aktif.id_kode = '$klasifikasi'";
}

// C. PENYUSUNAN VARIABEL WHERE (INI YANG HILANG DI KODE ANDA)
if (!empty($kondisi)) {
    // Gabungkan semua kondisi dengan 'AND' dan tambahkan kata 'WHERE' di depan
    $where = "WHERE " . implode(" AND ", $kondisi);
}

// ==========================================
// 2. QUERY DATA UTAMA
// ==========================================
$query_string = "
    SELECT arsip_inaktif.*, 
        kode_klasifikasi.kode_klasifikasi,
        kode_klasifikasi.deskripsi,
        sub_klasifikasi.nama_sub,
        sub_klasifikasi.id_sub,
        sub_sub_klasifikasi.nama_subsub,
        sub_sub_klasifikasi.id_subsub,
        tingkat_perkembangan.nama_tingkat  /* <--- TAMBAHAN 1: Ambil kolom nama */
    FROM arsip_inaktif
    LEFT JOIN kode_klasifikasi ON arsip_inaktif.id_kode = kode_klasifikasi.id_kode
    LEFT JOIN sub_klasifikasi ON arsip_inaktif.id_sub = sub_klasifikasi.id_sub
    LEFT JOIN sub_sub_klasifikasi ON arsip_inaktif.id_subsub = sub_sub_klasifikasi.id_subsub
    LEFT JOIN tingkat_perkembangan ON arsip_inaktif.id_tingkat = tingkat_perkembangan.id_tingkat /* <--- TAMBAHAN 2: Sambungkan tabelnya */
    $where
    ORDER BY arsip_inaktif.id_arsip_inaktif DESC
";

$query_arsip = mysqli_query($db, $query_string);

// --- 3. QUERY DROPDOWN (Untuk Modal & Filter) ---
$q_kode    = mysqli_query($db, "SELECT * FROM kode_klasifikasi ORDER BY id_kode ASC");
$q_sub     = mysqli_query($db, "SELECT * FROM sub_klasifikasi ORDER BY id_sub ASC");
$q_subsub  = mysqli_query($db, "SELECT * FROM sub_sub_klasifikasi ORDER BY id_subsub ASC");
$q_tingkat = mysqli_query($db, "SELECT * FROM tingkat_perkembangan ORDER BY id_tingkat ASC");

include '../../layout/header.php';
?>

<div class="d-flex">
    <?php include '../../layout/sidebar.php'; ?>

    <main class="flex-grow-1 p-4" style="background-color: #f3f4f6; min-height:100vh; min-width: 0;">
        <div class="container-fluid">
            <div class="card shadow-lg rounded-3 overflow-hidden">
                
                <div class="card-header text-white d-flex justify-content-between align-items-center"
                     style="background: linear-gradient(90deg, #4f46e5, #4338ca);"> <h5 class="mb-0 fw-bold">
                        <i class="fas fa-file-invoice me-2"></i> Data Arsip Inaktif
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
                                    <th style="min-width: 200px;">Uraian Informasi Arsip</th>
                                    <th style="min-width: 120px;">No Arsip/Berkas</th>
                                    <th style="min-width: 150px;">Kode Klasifikasi</th>
                                    <th style="min-width: 150px;">Jenis/Series Arsip</th>
                                    <th style="min-width: 120px;">Tahun</th>
                                    <th style="min-width: 120px;">Retensi</th>
                                    <th style="min-width: 120px;">Jumlah</th>
                                    <th style="min-width: 120px;"> Tingkat Perkembangan</th>
                                    <th style="min-width: 150px;">Keterangan</th>
                                    <th style="min-width: 120px;">File</th>
                                    <th style="min-width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $no = 1; if (mysqli_num_rows($query_arsip) > 0) { ?>
                                <?php while ($data = mysqli_fetch_assoc($query_arsip)) { ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    
                                    <td><?= htmlspecialchars($data['uraian_arsip'] ?? '') ?></td>
                                    
                                    <td class="text-center fw-bold"><?= htmlspecialchars($data['nomor_arsip'] ?? '') ?></td>
                                    
                                    <td>
                                        <?php if (!empty($data['nama_subsub'])) { ?>
                                            <div class="fw-bold text-primary"><?= $data['id_subsub'] ?? '' ?></div>
                                            <div class="text-muted"><?= $data['nama_subsub'] ?? '' ?></div>
                                        <?php } elseif (!empty($data['nama_sub'])) { ?>
                                            <div class="fw-bold text-primary"><?= $data['id_sub'] ?? '' ?></div>
                                            <div class="text-muted"><?= $data['nama_sub'] ?? '' ?></div>
                                        <?php } else { ?>
                                            <div class="fw-bold text-primary"><?= $data['kode_klasifikasi'] ?? '' ?></div>
                                            <div class="text-muted"><?= $data['deskripsi'] ?? '' ?></div>
                                        <?php } ?>
                                    </td>

                                    <td><?= htmlspecialchars($data['id_jenis'] ?? '') ?></td>
                                    
                                    <td class="text-center"><?= htmlspecialchars($data['kurun_waktu'] ?? '') ?></td>
                                    
                                    <td class="text-center"><?= htmlspecialchars($data['retensi'] ?? '') ?></td>
                                    
                                    <td class="text-center"><?= htmlspecialchars($data['jumlah'] ?? '') ?></td>
                                    
                                    <td class="text-center">
                                        <span class="badge bg-primary text-light">
                                            <?= htmlspecialchars($data['nama_tingkat'] ?? $data['id_tingkat'] ?? '-') ?>
                                        </span>
                                    </td>

                                    <td><?= htmlspecialchars($data['keterangan'] ?? '') ?></td>
                                    
                                    <td class="text-center">
                                        <?php if (!empty($data['file_pdf'])): ?>
                                            <a href="../../uploads/<?= $data['file_pdf'] ?>" target="_blank" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        <?php else: ?> - <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="#" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                            <a href="arsip-inaktif-hapus.php?id=<?= $data['id_arsip_inaktif'] ?>" onclick="return confirm('Hapus?')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr><td colspan="12" class="text-center py-4 text-muted">Data Kosong / Tidak Ditemukan</td></tr>
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
            <div class="modal-header text-white" style="background: linear-gradient(90deg, #6b7280, #374151);">
                <h5 class="modal-title fw-bold">Tambah Arsip Inaktif</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <form action="arsip-inaktif-tambah.php" method="POST" enctype="multipart/form-data" id="formTambah">
                    <input type="hidden" name="user_id" value="<?= $user_id ?>">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Uraian Informasi Arsip</label>
                            <textarea name="uraian_arsip" class="form-control" rows="2" required></textarea>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-bold">No Arsip/Berkas</label>
                            <input type="text" name="nomor_arsip" class="form-control" required>
                        </div>

                        <div class="col-12"><hr></div>

                        <div class="col-12">
                            <label class="form-label fw-bold">1. Kode Klasifikasi Induk</label>
                            <select name="id_kode" id="parent_kode" class="form-select" required>
                                <option value="">-- Pilih Kode --</option>
                                <?php mysqli_data_seek($q_kode, 0); while($k = mysqli_fetch_assoc($q_kode)): ?>
                                    <option value="<?= $k['id_kode'] ?>"><?= $k['kode_klasifikasi'] ?> - <?= $k['deskripsi'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">2. Sub Klasifikasi</label>
                            <select name="id_sub" id="child_kode" class="form-select" required disabled>
                                <option value="">-- Pilih Induk Dulu --</option>
                                <?php mysqli_data_seek($q_sub, 0); while($s = mysqli_fetch_assoc($q_sub)): ?>
                                    <option value="<?= $s['id_sub'] ?>" data-parent="<?= $s['id_kode'] ?>"><?= $s['id_sub'] ?> - <?= $s['nama_sub'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">3. Sub-Sub (Opsional)</label>
                            <select name="id_subsub" id="grandchild_kode" class="form-select" disabled>
                                <option value="">-- Pilih Sub Dulu --</option>
                                <?php mysqli_data_seek($q_subsub, 0); while($ss = mysqli_fetch_assoc($q_subsub)): ?>
                                    <option value="<?= $ss['id_subsub'] ?>" data-parent="<?= $ss['id_sub'] ?>"><?= $ss['nama_subsub'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-12"><hr></div>
 
                        <div class="col-12">
                            <label class="form-label fw-bold">Jenis/Series Arsip</label>
                            <input type="text" name="jenis_arsip" id="input_jenis_arsip" class="form-control" 
                                placeholder="Terisi otomatis sesuai klasifikasi..." readonly>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Kurun Waktu (Tahun)</label>
                            <input type="number" name="kurun_waktu" class="form-control" placeholder="2024">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Retensi Arsip</label>
                            <input type="text" name="retensi" class="form-control" placeholder="Contoh: 5 Tahun / 2029">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Jumlah</label>
                            <input type="text" name="jumlah" class="form-control" placeholder="Contoh: 1 Berkas / 5 Lembar">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Tingkat Perkembangan</label>
                            <select name="id_tingkat" class="form-select" required>
                                <option value="">-- Pilih --</option>
                                <?php 
                                // Reset pointer query tingkat perkembangan
                                if ($q_tingkat) { mysqli_data_seek($q_tingkat, 0); }
                                while($tp = mysqli_fetch_assoc($q_tingkat)): 
                                ?>
                                    <option value="<?= $tp['id_tingkat'] ?>"><?= $tp['nama_tingkat'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Upload File (PDF)</label>
                            <input type="file" name="file_arsip" class="form-control" accept=".pdf" required>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="formTambah" name="simpan" class="btn btn-success">Simpan Data</button>
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
    const formTambah = document.getElementById("formTambah");
    
    // Target Input yang akan diisi otomatis
    const jenisInput = document.getElementById("input_jenis_arsip");

    // --- FUNGSI AUTOFILL ---
    function autoFillJenis(selectElement) {
        if (selectElement.value === "") return;
        const selectedText = selectElement.options[selectElement.selectedIndex].text;
        const parts = selectedText.split(' - ');
        
        if (parts.length > 1) {
            jenisInput.value = parts.slice(1).join(' - ');
        } else {
            jenisInput.value = selectedText;
        }
    }

    if (parentSelect && childSelect && grandChildSelect) {
        
        const childOptions = Array.from(childSelect.options);
        const grandChildOptions = Array.from(grandChildSelect.options);

        // 1. INDUK BERUBAH
        parentSelect.addEventListener("change", function() {
            const selectedParentID = this.value;
            autoFillJenis(this);

            childSelect.innerHTML = '<option value="">-- Pilih Sub Klasifikasi --</option>';
            childSelect.disabled = true;
            grandChildSelect.innerHTML = '<option value="">-- Pilih Sub Dulu --</option>';
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

        // 2. SUB BERUBAH
        childSelect.addEventListener("change", function() {
            const selectedSubID = this.value;
            autoFillJenis(this);

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

        // 3. SUB-SUB BERUBAH
        grandChildSelect.addEventListener("change", function() {
            autoFillJenis(this);
        });

        // 4. Safety Net Submit
        formTambah.addEventListener("submit", function() {
            childSelect.disabled = false;
            grandChildSelect.disabled = false;
        });
    }
});
</script>