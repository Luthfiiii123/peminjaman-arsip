<?php
session_start();
include '../../konektor.php';

// 1. Cek ID di URL
if (!isset($_GET['id'])) {
    echo "<script>alert('ID tidak ditemukan!'); window.location='arsip-permanen.php';</script>";
    exit;
}

$id_arsip = $_GET['id'];

// 2. Ambil Data Arsip Permanen
$query_data = mysqli_query($db, "SELECT * FROM arsip_permanen WHERE id_arsip_permanen = '$id_arsip'");
$data = mysqli_fetch_assoc($query_data);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='arsip-permanen.php';</script>";
    exit;
}

// 3. LOGIKA PENTING: MENCARI ID_SUB (PARENT DARI SUB-SUB)
// Karena tabel arsip_permanen tidak menyimpan id_sub, kita harus mencarinya 
// dari tabel sub_sub_klasifikasi agar dropdown ke-2 bisa terpilih otomatis.
$current_sub_id = "";
if (!empty($data['id_subsub'])) {
    $q_cari_induk = mysqli_query($db, "SELECT id_sub FROM sub_sub_klasifikasi WHERE id_subsub = '".$data['id_subsub']."'");
    $d_cari_induk = mysqli_fetch_assoc($q_cari_induk);
    $current_sub_id = $d_cari_induk['id_sub'] ?? "";
}

// 4. Ambil Data Referensi Dropdown
$q_kode    = mysqli_query($db, "SELECT * FROM kode_klasifikasi ORDER BY id_kode ASC");
$q_sub     = mysqli_query($db, "SELECT * FROM sub_klasifikasi ORDER BY id_sub ASC");
$q_subsub  = mysqli_query($db, "SELECT * FROM sub_sub_klasifikasi ORDER BY id_subsub ASC");
$q_jenis   = mysqli_query($db, "SELECT * FROM jenis_arsip WHERE kategori='Permanen' ORDER BY nama_jenis ASC");
$q_tingkat = mysqli_query($db, "SELECT * FROM tingkat_perkembangan ORDER BY id_tingkat ASC");
$q_nasib   = mysqli_query($db, "SELECT * FROM nasib_akhir ORDER BY id_nasib ASC");

// 5. Proses Update Data
if (isset($_POST['update'])) {
    $uraian      = mysqli_real_escape_string($db, $_POST['uraian_informasi']);
    $id_kode     = $_POST['id_kode'];
    // id_sub tidak disimpan ke database arsip_permanen, hanya id_subsub
    $id_subsub   = !empty($_POST['id_subsub']) ? $_POST['id_subsub'] : 'NULL'; 
    $id_jenis    = $_POST['id_jenis'];
    $id_tingkat  = $_POST['id_tingkat'];
    $kurun_waktu = $_POST['kurun_waktu'];
    $jumlah      = mysqli_real_escape_string($db, $_POST['jumlah']);
    $id_nasib    = $_POST['id_nasib'];
    $file_lama   = $_POST['file_lama'];

    // Validasi id_subsub agar tidak error query jika kosong
    $val_subsub = ($id_subsub === 'NULL') ? "NULL" : "'$id_subsub'";

    // Logika Upload File
    if ($_FILES['file_arsip']['error'] === 0) {
        $file_name = $_FILES['file_arsip']['name'];
        $file_tmp  = $_FILES['file_arsip']['tmp_name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if ($file_ext != 'pdf') {
            echo "<script>alert('Hanya file PDF yang diperbolehkan!');</script>";
            $file_baru = $file_lama; // Fallback
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
    $query_update = "UPDATE arsip_permanen SET 
        uraian_informasi = '$uraian',
        id_kode          = '$id_kode',
        id_subsub        = $val_subsub,
        id_jenis         = '$id_jenis',
        id_tingkat       = '$id_tingkat',
        kurun_waktu      = '$kurun_waktu',
        jumlah           = '$jumlah',
        id_nasib         = '$id_nasib',
        file_pdf         = '$file_baru'
        WHERE id_arsip_permanen = '$id_arsip'";

    $update = mysqli_query($db, $query_update);

    if ($update) {
        echo "<script>alert('Data Berhasil Diperbarui!'); window.location='arsip-permanen.php';</script>";
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
                    <h5 class="mb-0 fw-bold text-white">
                        <i class="fas fa-edit me-2"></i> Edit Arsip Permanen
                    </h5>
                    <a href="arsip-permanen.php" class="btn btn-light btn-sm fw-bold shadow-sm">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>

                <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="file_lama" value="<?= $data['file_pdf'] ?>">

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Uraian Informasi Arsip</label>
                                <textarea name="uraian_informasi" class="form-control" rows="3" required><?= htmlspecialchars($data['uraian_informasi']) ?></textarea>
                            </div>

                            <div class="col-12"><hr class="my-1 text-muted"></div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">1. Kode Klasifikasi</label>
                                <select name="id_kode" id="parent_kode" class="form-select" required>
                                    <option value="">-- Pilih Kode Klasifikasi --</option>
                                    <?php 
                                    mysqli_data_seek($q_kode, 0);
                                    while($induk = mysqli_fetch_assoc($q_kode)): 
                                        $selected = ($induk['id_kode'] == $data['id_kode']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $induk['id_kode'] ?>" <?= $selected ?>>
                                            <?= $induk['kode_klasifikasi'] ?> - <?= $induk['deskripsi'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">2. Sub Klasifikasi</label>
                                <select name="id_sub" id="child_kode" class="form-select" required>
                                    <option value="">-- Pilih Induk Dulu --</option>
                                    <?php 
                                    mysqli_data_seek($q_sub, 0);
                                    while($s = mysqli_fetch_assoc($q_sub)): 
                                        // Pilih jika ID SUB cocok dengan hasil pencarian parent dari sub-sub
                                        $selected = ($s['id_sub'] == $current_sub_id) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $s['id_sub'] ?>" data-parent="<?= $s['id_kode'] ?>" <?= $selected ?>>
                                            <?= $s['id_sub'] ?> - <?= $s['nama_sub'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">3. Sub-Sub (Opsional)</label>
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
                                <label class="form-label fw-semibold">Jenis Arsip</label>
                                <select name="id_jenis" class="form-select" required>
                                    <option value="">-- Pilih Jenis --</option>
                                    <?php 
                                    mysqli_data_seek($q_jenis, 0);
                                    while($j = mysqli_fetch_assoc($q_jenis)): 
                                        $selected = ($j['id_jenis'] == $data['id_jenis']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $j['id_jenis'] ?>" <?= $selected ?>><?= $j['nama_jenis'] ?></option>
                                    <?php endwhile; ?>
                                </select>
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
                                <label class="form-label fw-semibold">Kurun Waktu (Tahun)</label>
                                <input type="number" name="kurun_waktu" class="form-control" value="<?= htmlspecialchars($data['kurun_waktu']) ?>" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Jumlah</label>
                                <input type="text" name="jumlah" class="form-control" value="<?= htmlspecialchars($data['jumlah']) ?>" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Nasib Akhir</label>
                                <select name="id_nasib" class="form-select" required>
                                    <option value="">-- Pilih Nasib --</option>
                                    <?php 
                                    mysqli_data_seek($q_nasib, 0);
                                    while($na = mysqli_fetch_assoc($q_nasib)): 
                                        $selected = ($na['id_nasib'] == $data['id_nasib']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $na['id_nasib'] ?>" <?= $selected ?>><?= $na['nama_nasib'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">File Arsip (Biarkan kosong jika tidak diubah)</label>
                                <input type="file" name="file_arsip" class="form-control" accept=".pdf">
                                <div class="mt-2 small">
                                    File saat ini: 
                                    <?php if (!empty($data['file_pdf'])): ?>
                                        <a href="../../uploads/<?= $data['file_pdf'] ?>" target="_blank" class="text-decoration-none text-danger fw-bold">
                                            <i class="fas fa-file-pdf"></i> Lihat PDF
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Tidak ada file</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <a href="arsip-permanen.php" class="btn btn-secondary me-2">Batal</a>
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

    // Simpan Opsi Asli
    const originalChildOptions = Array.from(childSelect.options);
    const originalGrandChildOptions = Array.from(grandChildSelect.options);

    // ==========================================
    // FUNGSI FILTER CHILD (Induk -> Sub)
    // ==========================================
    function filterChild(parentId) {
        childSelect.innerHTML = '<option value="">-- Pilih Sub Klasifikasi --</option>';
        childSelect.disabled = true;

        if (parentId) {
            const filtered = originalChildOptions.filter(opt => opt.getAttribute("data-parent") === parentId);
            if (filtered.length > 0) {
                childSelect.disabled = false;
                filtered.forEach(opt => childSelect.add(opt.cloneNode(true)));
            }
        }
    }

    // ==========================================
    // FUNGSI FILTER GRANDCHILD (Sub -> SubSub)
    // ==========================================
    function filterGrandChild(subId) {
        grandChildSelect.innerHTML = '<option value="">-- Pilih Sub-Sub Klasifikasi --</option>';
        grandChildSelect.disabled = true;

        if (subId) {
            const filtered = originalGrandChildOptions.filter(opt => opt.getAttribute("data-parent") === subId);
            if (filtered.length > 0) {
                grandChildSelect.disabled = false;
                filtered.forEach(opt => grandChildSelect.add(opt.cloneNode(true)));
            }
        }
    }

    // ==========================================
    // EVENT LISTENER
    // ==========================================
    
    // 1. Saat Induk Berubah
    parentSelect.addEventListener("change", function() {
        filterChild(this.value);
        grandChildSelect.innerHTML = '<option value="">-- Pilih Sub Terlebih Dahulu --</option>';
        grandChildSelect.disabled = true;
    });

    // 2. Saat Sub Berubah
    childSelect.addEventListener("change", function() {
        filterGrandChild(this.value);
    });

    // ==========================================
    // LOGIKA SAAT LOAD (MODE EDIT)
    // ==========================================
    const currentParentVal = parentSelect.value;
    const currentChildVal = "<?= $current_sub_id ?>"; // Diambil dari PHP
    const currentGrandChildVal = "<?= $data['id_subsub'] ?>"; // Diambil dari PHP

    if (currentParentVal) {
        filterChild(currentParentVal);
        // Set Nilai Child kembali setelah filter
        if (currentChildVal) {
            childSelect.value = currentChildVal;
            
            // Lanjut filter Grandchild
            filterGrandChild(currentChildVal);
            if (currentGrandChildVal) {
                grandChildSelect.value = currentGrandChildVal;
            }
        }
    }
});
</script>