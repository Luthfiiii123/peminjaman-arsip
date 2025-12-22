<?php
session_start();
include '../../konektor.php';

// 1. Cek ID di URL
if (!isset($_GET['id'])) {
    echo "<script>alert('ID tidak ditemukan!'); window.location='arsip-aktif.php';</script>";
    exit;
}

$id_arsip = mysqli_real_escape_string($db, $_GET['id']);

// 2. Ambil Data Arsip Aktif
$query_data = mysqli_query($db, "SELECT * FROM arsip_aktif WHERE id_arsip_aktif = '$id_arsip'");
$data = mysqli_fetch_assoc($query_data);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='arsip-aktif.php';</script>";
    exit;
}

// 3. LOGIKA PENTING: MENENTUKAN ID_SUB (PARENT DARI SUB-SUB)
// Kita cek apakah id_sub ada di tabel. Jika kosong tapi id_subsub ada, kita cari induknya.
$current_sub_id = $data['id_sub'] ?? "";

if (empty($current_sub_id) && !empty($data['id_subsub'])) {
    $q_cari_induk = mysqli_query($db, "SELECT id_sub FROM sub_sub_klasifikasi WHERE id_subsub = '".$data['id_subsub']."'");
    $d_cari_induk = mysqli_fetch_assoc($q_cari_induk);
    $current_sub_id = $d_cari_induk['id_sub'] ?? "";
}

// 4. Ambil Data Referensi Dropdown
$q_kode     = mysqli_query($db, "SELECT * FROM kode_klasifikasi ORDER BY id_kode ASC");
$q_sub      = mysqli_query($db, "SELECT * FROM sub_klasifikasi ORDER BY id_sub ASC");
$q_subsub   = mysqli_query($db, "SELECT * FROM sub_sub_klasifikasi ORDER BY id_subsub ASC");
// Arsip Aktif biasanya menggunakan Klasifikasi Keamanan
$q_keamanan = mysqli_query($db, "SELECT * FROM klasifikasi_keamanan ORDER BY id_keamanan ASC");

// 5. Proses Update Data
if (isset($_POST['update'])) {
    // Sanitasi Input
    $uraian_arsip   = mysqli_real_escape_string($db, $_POST['uraian_arsip']);
    $uraian_berkas  = mysqli_real_escape_string($db, $_POST['uraian_berkas']);
    $no_berkas      = mysqli_real_escape_string($db, $_POST['no_berkas']);
    
    $id_kode        = $_POST['id_kode'];
    // Handle NULL values for dropdowns
    $id_sub         = !empty($_POST['id_sub']) ? "'".$_POST['id_sub']."'" : 'NULL';
    $id_subsub      = !empty($_POST['id_subsub']) ? "'".$_POST['id_subsub']."'" : 'NULL';
    
    $kurun_waktu    = $_POST['kurun_waktu'];
    $jumlah         = mysqli_real_escape_string($db, $_POST['jumlah']);
    $id_keamanan    = $_POST['id_keamanan'];
    $file_lama      = $_POST['file_lama'];
    $user_id        = $_SESSION['user_id'] ?? 1;

    // Logika Upload File
    if ($_FILES['file_arsip']['error'] === 0) {
        $file_name = $_FILES['file_arsip']['name'];
        $file_tmp  = $_FILES['file_arsip']['tmp_name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if ($file_ext != 'pdf') {
            echo "<script>alert('Hanya file PDF yang diperbolehkan!');</script>";
            $file_baru = $file_lama; // Fallback ke file lama
        } else {
            // Hapus file lama jika ada
            if (!empty($file_lama) && file_exists("../../uploads/" . $file_lama)) {
                unlink("../../uploads/" . $file_lama);
            }
            // Upload file baru
            $file_baru = uniqid() . '_' . $file_name;
            move_uploaded_file($file_tmp, '../../uploads/' . $file_baru);
        }
    } else {
        $file_baru = $file_lama;
    }

    // Query Update
    $query_update = "UPDATE arsip_aktif SET 
        uraian_arsip    = '$uraian_arsip',
        uraian_berkas   = '$uraian_berkas',
        no_berkas       = '$no_berkas',
        id_kode         = '$id_kode',
        id_sub          = $id_sub,
        id_subsub       = $id_subsub,
        kurun_waktu     = '$kurun_waktu',
        jumlah          = '$jumlah',
        id_keamanan     = '$id_keamanan',
        file_pdf        = '$file_baru',
        user_id         = '$user_id'
        WHERE id_arsip_aktif = '$id_arsip'";

    $update = mysqli_query($db, $query_update);

    if ($update) {
        echo "<script>alert('Data Arsip Aktif Berhasil Diperbarui!'); window.location='arsip-aktif.php';</script>";
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
                        <i class="fas fa-edit me-2"></i> Edit Arsip Aktif
                    </h5>
                    <a href="arsip-aktif.php" class="btn btn-light btn-sm fw-bold shadow-sm">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>

                <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="file_lama" value="<?= $data['file_pdf'] ?>">

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Uraian Informasi Arsip</label>
                                <textarea name="uraian_arsip" class="form-control" rows="2" required><?= htmlspecialchars($data['uraian_arsip']) ?></textarea>
                            </div>

                            <div class="col-12"><hr class="my-1 text-muted"></div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">1. Kode Klasifikasi</label>
                                <select name="id_kode" id="parent_kode" class="form-select" required>
                                    <option value="">-- Pilih Kode --</option>
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
                                <label class="form-label fw-semibold">Uraian Informasi Berkas</label>
                                <input type="text" name="uraian_berkas" class="form-control" value="<?= htmlspecialchars($data['uraian_berkas']) ?>" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">No. Berkas</label>
                                <input type="text" name="no_berkas" class="form-control" value="<?= htmlspecialchars($data['no_berkas']) ?>" required>
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
                                <label class="form-label fw-semibold">Klasifikasi Keamanan</label>
                                <select name="id_keamanan" class="form-select" required>
                                    <option value="">-- Pilih --</option>
                                    <?php 
                                    mysqli_data_seek($q_keamanan, 0);
                                    while($km = mysqli_fetch_assoc($q_keamanan)): 
                                        $selected = ($km['id_keamanan'] == $data['id_keamanan']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $km['id_keamanan'] ?>" <?= $selected ?>>
                                            <?= $km['nama_keamanan'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">File Arsip (Biarkan kosong jika tidak diubah)</label>
                                <input type="file" name="file_arsip" class="form-control" accept=".pdf">
                                <div class="mt-2 small">
                                    File saat ini: 
                                    <?php if (!empty($data['file_pdf'])): ?>
                                        <a href="../../uploads/<?= $data['file_pdf'] ?>" target="_blank" class="text-decoration-none text-success fw-bold">
                                            <i class="fas fa-file-pdf"></i> Lihat PDF
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Tidak ada file</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <a href="arsip-aktif.php" class="btn btn-secondary me-2">Batal</a>
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

    // Simpan Opsi Asli saat halaman dimuat
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

    // Jalankan filter otomatis berdasarkan data database
    if (currentParentVal) {
        filterChild(currentParentVal);
        
        // Kembalikan nilai Child terpilih
        if (currentChildVal) {
            childSelect.value = currentChildVal;
            
            // Lanjut filter Grandchild
            filterGrandChild(currentChildVal);
            
            // Kembalikan nilai GrandChild terpilih
            if (currentGrandChildVal) {
                grandChildSelect.value = currentGrandChildVal;
            }
        }
    }
});
</script>