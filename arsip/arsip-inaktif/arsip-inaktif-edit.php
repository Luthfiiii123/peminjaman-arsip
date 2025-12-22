<?php
session_start();
include '../../konektor.php';

// 1. Cek ID di URL
if (!isset($_GET['id'])) {
    echo "<script>alert('ID tidak ditemukan!'); window.location='arsip-inaktif.php';</script>";
    exit;
}

$id_arsip = $_GET['id'];

// 2. Ambil Data Arsip Inaktif
$query_data = mysqli_query($db, "SELECT * FROM arsip_inaktif WHERE id_arsip_inaktif = '$id_arsip'");
$data = mysqli_fetch_assoc($query_data);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='arsip-inaktif.php';</script>";
    exit;
}

// 3. Ambil Data Referensi Dropdown
$q_kode    = mysqli_query($db, "SELECT * FROM kode_klasifikasi ORDER BY id_kode ASC");
$q_sub     = mysqli_query($db, "SELECT * FROM sub_klasifikasi ORDER BY id_sub ASC");
$q_subsub  = mysqli_query($db, "SELECT * FROM sub_sub_klasifikasi ORDER BY id_subsub ASC");
$q_tingkat = mysqli_query($db, "SELECT * FROM tingkat_perkembangan ORDER BY id_tingkat ASC");

// 4. Proses Update Data
if (isset($_POST['update'])) {
    $uraian      = mysqli_real_escape_string($db, $_POST['uraian_arsip']);
    $nomor       = mysqli_real_escape_string($db, $_POST['nomor_arsip']);
    
    // Klasifikasi
    $id_kode     = $_POST['id_kode'];
    $id_sub      = !empty($_POST['id_sub']) ? "'".$_POST['id_sub']."'" : "NULL";
    $id_subsub   = !empty($_POST['id_subsub']) ? "'".$_POST['id_subsub']."'" : "NULL";
    
    // Perhatikan: Di modal tambah Anda pakai name="jenis_arsip", tapi di filter search pakai "id_jenis".
    // Saya asumsikan kolom di database namanya 'id_jenis' tapi isinya teks (VARCHAR).
    $jenis_arsip = mysqli_real_escape_string($db, $_POST['jenis_arsip']);

    $kurun_waktu = $_POST['kurun_waktu'];
    $retensi     = mysqli_real_escape_string($db, $_POST['retensi']);
    $jumlah      = mysqli_real_escape_string($db, $_POST['jumlah']);
    $id_tingkat  = $_POST['id_tingkat'];
    $keterangan  = mysqli_real_escape_string($db, $_POST['keterangan']);
    $file_lama   = $_POST['file_lama'];

    // Logika Upload File
    if ($_FILES['file_arsip']['error'] === 0) {
        $file_name = $_FILES['file_arsip']['name'];
        $file_tmp  = $_FILES['file_arsip']['tmp_name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if ($file_ext != 'pdf') {
            echo "<script>alert('Hanya file PDF yang diperbolehkan!');</script>";
            $file_baru = $file_lama;
        } else {
            if (!empty($file_lama) && file_exists("../../uploads/" . $file_lama)) {
                unlink("../../uploads/" . $file_lama);
            }
            $file_baru = uniqid() . '_' . $file_name;
            move_uploaded_file($file_tmp, '../../uploads/' . $file_baru);
        }
    } else {
        $file_baru = $file_lama;
    }

    // Query Update
    // Pastikan nama kolom database sesuai (misal: id_jenis atau jenis_arsip)
    $query_update = "UPDATE arsip_inaktif SET 
        uraian_arsip     = '$uraian',
        nomor_arsip      = '$nomor',
        id_kode          = '$id_kode',
        id_sub           = $id_sub,
        id_subsub        = $id_subsub,
        id_jenis         = '$jenis_arsip', 
        kurun_waktu      = '$kurun_waktu',
        retensi          = '$retensi',
        jumlah           = '$jumlah',
        id_tingkat       = '$id_tingkat',
        keterangan       = '$keterangan',
        file_pdf         = '$file_baru'
        WHERE id_arsip_inaktif = '$id_arsip'";

    $update = mysqli_query($db, $query_update);

    if ($update) {
        echo "<script>alert('Data Berhasil Diperbarui!'); window.location='arsip-inaktif.php';</script>";
    } else {
        echo "<script>alert('Gagal Memperbarui Data: " . mysqli_error($db) . "');</script>";
    }
}

include '../../layout/header.php';
?>

<div class="d-flex">
    <?php include '../../layout/sidebar.php'; ?>
    
    <main class="flex-grow-1 p-4" style="background-color: #f3f4f6; min-height: 90vh;">
        <div class="container-fluid">
            <div class="card shadow-lg rounded-3">
                <div class="card-header text-white d-flex justify-content-between align-items-center"
                     style="background: linear-gradient(90deg, #4f46e5, #4338ca);">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-edit me-2"></i> Edit Arsip Inaktif
                    </h5>
                    <a href="arsip-inaktif.php" class="btn btn-light btn-sm fw-bold shadow-sm">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>

                <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="file_lama" value="<?= $data['file_pdf'] ?>">

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">Uraian Informasi Arsip</label>
                                <textarea name="uraian_arsip" class="form-control" rows="2" required><?= htmlspecialchars($data['uraian_arsip']) ?></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">No Arsip/Berkas</label>
                                <input type="text" name="nomor_arsip" class="form-control" value="<?= htmlspecialchars($data['nomor_arsip']) ?>" required>
                            </div>

                            <div class="col-12"><hr class="my-1 text-muted"></div>

                            <div class="col-12">
                                <label class="form-label fw-bold">1. Kode Klasifikasi Induk</label>
                                <select name="id_kode" id="parent_kode" class="form-select" required>
                                    <option value="">-- Pilih Kode --</option>
                                    <?php 
                                    mysqli_data_seek($q_kode, 0);
                                    while($k = mysqli_fetch_assoc($q_kode)): 
                                        $selected = ($k['id_kode'] == $data['id_kode']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $k['id_kode'] ?>" <?= $selected ?>>
                                            <?= $k['kode_klasifikasi'] ?> - <?= $k['deskripsi'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">2. Sub Klasifikasi</label>
                                <select name="id_sub" id="child_kode" class="form-select">
                                    <option value="">-- Pilih Induk Dulu --</option>
                                    <?php 
                                    mysqli_data_seek($q_sub, 0);
                                    while($s = mysqli_fetch_assoc($q_sub)): 
                                        $selected = ($s['id_sub'] == $data['id_sub']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $s['id_sub'] ?>" data-parent="<?= $s['id_kode'] ?>" <?= $selected ?>>
                                            <?= $s['id_sub'] ?> - <?= $s['nama_sub'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">3. Sub-Sub (Opsional)</label>
                                <select name="id_subsub" id="grandchild_kode" class="form-select">
                                    <option value="">-- Pilih Sub Dulu --</option>
                                    <?php 
                                    mysqli_data_seek($q_subsub, 0);
                                    while($ss = mysqli_fetch_assoc($q_subsub)): 
                                        $selected = ($ss['id_subsub'] == $data['id_subsub']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $ss['id_subsub'] ?>" data-parent="<?= $ss['id_sub'] ?>" <?= $selected ?>>
                                            <?= $ss['nama_subsub'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-12"><hr class="my-1 text-muted"></div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Jenis/Series Arsip</label>
                                <input type="text" name="jenis_arsip" id="input_jenis_arsip" class="form-control" 
                                    value="<?= htmlspecialchars($data['id_jenis'] ?? '') ?>" 
                                    placeholder="Terisi otomatis sesuai klasifikasi..." readonly>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Kurun Waktu (Tahun)</label>
                                <input type="number" name="kurun_waktu" class="form-control" value="<?= htmlspecialchars($data['kurun_waktu']) ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Retensi Arsip</label>
                                <input type="text" name="retensi" class="form-control" value="<?= htmlspecialchars($data['retensi']) ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Jumlah</label>
                                <input type="text" name="jumlah" class="form-control" value="<?= htmlspecialchars($data['jumlah']) ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Tingkat Perkembangan</label>
                                <select name="id_tingkat" class="form-select" required>
                                    <option value="">-- Pilih --</option>
                                    <?php 
                                    mysqli_data_seek($q_tingkat, 0);
                                    while($tp = mysqli_fetch_assoc($q_tingkat)): 
                                        $selected = ($tp['id_tingkat'] == $data['id_tingkat']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $tp['id_tingkat'] ?>" <?= $selected ?>><?= $tp['nama_tingkat'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="2"><?= htmlspecialchars($data['keterangan']) ?></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">File Arsip (Biarkan kosong jika tidak diubah)</label>
                                <input type="file" name="file_arsip" class="form-control" accept=".pdf">
                                <div class="mt-2 small">
                                    File saat ini: 
                                    <?php if (!empty($data['file_pdf'])): ?>
                                        <a href="../../uploads/<?= $data['file_pdf'] ?>" target="_blank" class="text-decoration-none fw-bold" style="color: #4f46e5;">
                                            <i class="fas fa-file-pdf"></i> Lihat PDF
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Tidak ada file</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <a href="arsip-inaktif.php" class="btn btn-secondary me-2">Batal</a>
                            <button type="submit" name="update" class="btn text-white fw-bold" style="background-color: #4f46e5; border-color: #4f46e5;">
                                <i class="fas fa-save me-1"></i> Update Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../../layout/footer.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const parentSelect = document.getElementById("parent_kode");
    const childSelect = document.getElementById("child_kode");
    const grandChildSelect = document.getElementById("grandchild_kode");
    const jenisInput = document.getElementById("input_jenis_arsip");

    // Simpan Opsi Asli untuk Filter
    const originalChildOptions = Array.from(childSelect.options);
    const originalGrandChildOptions = Array.from(grandChildSelect.options);

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

    // --- FUNGSI FILTER CHILD ---
    function filterChild(parentId) {
        childSelect.innerHTML = '<option value="">-- Pilih Sub Klasifikasi --</option>';
        childSelect.disabled = true;

        if (parentId) {
            const filtered = originalChildOptions.filter(opt => opt.getAttribute("data-parent") === parentId);
            if (filtered.length > 0) {
                childSelect.disabled = false;
                filtered.forEach(opt => childSelect.add(opt.cloneNode(true)));
            } else {
                childSelect.innerHTML = '<option value="">-- Tidak ada sub klasifikasi --</option>';
            }
        }
    }

    // --- FUNGSI FILTER GRANDCHILD ---
    function filterGrandChild(subId) {
        grandChildSelect.innerHTML = '<option value="">-- Pilih Sub-Sub Klasifikasi --</option>';
        grandChildSelect.disabled = true;

        if (subId) {
            const filtered = originalGrandChildOptions.filter(opt => opt.getAttribute("data-parent") === subId);
            if (filtered.length > 0) {
                grandChildSelect.disabled = false;
                filtered.forEach(opt => grandChildSelect.add(opt.cloneNode(true)));
            } else {
                grandChildSelect.innerHTML = '<option value="">-- Tidak ada sub-sub klasifikasi --</option>';
            }
        }
    }

    // --- EVENT LISTENERS ---

    // 1. Parent Changed
    parentSelect.addEventListener("change", function() {
        filterChild(this.value);
        autoFillJenis(this);
        grandChildSelect.innerHTML = '<option value="">-- Pilih Sub Terlebih Dahulu --</option>';
        grandChildSelect.disabled = true;
    });

    // 2. Sub Changed
    childSelect.addEventListener("change", function() {
        filterGrandChild(this.value);
        autoFillJenis(this);
    });

    // 3. Sub-Sub Changed
    grandChildSelect.addEventListener("change", function() {
        autoFillJenis(this);
    });

    // --- LOGIKA SAAT LOAD (MODE EDIT) ---
    // Mengembalikan posisi dropdown sesuai database
    const currentParentVal = parentSelect.value;
    const currentChildVal = "<?= $data['id_sub'] ?>";
    const currentGrandChildVal = "<?= $data['id_subsub'] ?>";

    if (currentParentVal) {
        filterChild(currentParentVal);
        if (currentChildVal) {
            childSelect.value = currentChildVal;
            
            filterGrandChild(currentChildVal);
            if (currentGrandChildVal) {
                grandChildSelect.value = currentGrandChildVal;
            }
        }
    }
});
</script>