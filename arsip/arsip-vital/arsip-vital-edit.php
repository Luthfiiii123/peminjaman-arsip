<?php
session_start();
include '../../konektor.php';

// 1. Cek ID di URL
if (!isset($_GET['id'])) {
    echo "<script>alert('ID tidak ditemukan!'); window.location='arsip-vital.php';</script>";
    exit;
}

$id_arsip = $_GET['id'];

// 2. Ambil Data Lama (Single Row)
$query_data = mysqli_query($db, "SELECT * FROM arsip_vital WHERE id_arsip_vital = '$id_arsip'");
$data = mysqli_fetch_assoc($query_data);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='arsip-vital.php';</script>";
    exit;
}

// 3. Ambil Data Referensi untuk Dropdown
$q_kode   = mysqli_query($db, "SELECT * FROM kode_klasifikasi ORDER BY id_kode ASC");
$q_sub    = mysqli_query($db, "SELECT * FROM sub_klasifikasi ORDER BY id_sub ASC");
$q_jenis  = mysqli_query($db, "SELECT * FROM jenis_arsip WHERE kategori='Vital' ORDER BY nama_jenis ASC");
$q_metode = mysqli_query($db, "SELECT * FROM metode_perlindungan ORDER BY nama_metode ASC");

// 4. Proses Update Data saat Tombol ditekan
if (isset($_POST['update'])) {
    $uraian        = mysqli_real_escape_string($db, $_POST['uraian_informasi']);
    $asal          = $_POST['asal_arsip'];
    $id_kode       = $_POST['id_kode'];
    $id_sub        = $_POST['id_sub']; // Bisa kosong jika parent tidak punya anak
    $id_jenis      = $_POST['id_jenis'];
    $nomor_arsip   = mysqli_real_escape_string($db, $_POST['nomor_arsip']);
    $retensi       = $_POST['retensi'];
    $lokasi        = mysqli_real_escape_string($db, $_POST['lokasi_simpan']);
    $id_metode     = $_POST['id_metode'];
    $file_lama     = $_POST['file_lama'];

    // Logika Upload File Baru
    if ($_FILES['file_arsip']['error'] === 0) {
        $file_name = $_FILES['file_arsip']['name'];
        $file_tmp  = $_FILES['file_arsip']['tmp_name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Validasi Ekstensi
        if ($file_ext != 'pdf') {
            echo "<script>alert('Hanya file PDF yang diperbolehkan!');</script>";
        } else {
            // Hapus file lama jika ada
            if (!empty($file_lama) && file_exists("../../uploads/" . $file_lama)) {
                unlink("../../uploads/" . $file_lama);
            }

            // Upload file baru (Rename agar unik)
            $file_baru = uniqid() . '_' . $file_name;
            move_uploaded_file($file_tmp, '../../uploads/' . $file_baru);
        }
    } else {
        // Jika tidak upload file baru, pakai file lama
        $file_baru = $file_lama;
    }

    // Query Update
    $update = mysqli_query($db, "UPDATE arsip_vital SET 
        uraian_informasi = '$uraian',
        asal_arsip       = '$asal',
        id_kode          = '$id_kode',
        id_sub           = '$id_sub',
        id_jenis         = '$id_jenis',
        nomor_arsip      = '$nomor_arsip',
        retensi          = '$retensi',
        lokasi_simpan    = '$lokasi',
        id_metode        = '$id_metode',
        file_pdf         = '$file_baru'
        WHERE id_arsip_vital = '$id_arsip'
    ");

    if ($update) {
        echo "<script>alert('Data Berhasil Diperbarui!'); window.location='arsip-vital.php';</script>";
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
                        <i class="fas fa-edit me-2"></i> Edit Arsip Vital
                    </h5>
                    <a href="arsip-vital.php" class="btn btn-light btn-sm fw-bold shadow-sm">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>

                <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="file_lama" value="<?= $data['file_pdf'] ?>">

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Uraian Informasi Arsip</label>
                                <textarea name="uraian_informasi" class="form-control" rows="2" required><?= htmlspecialchars($data['uraian_informasi']) ?></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Asal Arsip</label>
                                <select name="asal_arsip" class="form-select" required>
                                    <option value="internal" <?= ($data['asal_arsip'] == 'internal') ? 'selected' : '' ?>>Internal</option>
                                    <option value="eksternal" <?= ($data['asal_arsip'] == 'eksternal') ? 'selected' : '' ?>>Eksternal</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Kode Klasifikasi</label>
                                <select name="id_kode" id="parent_kode" class="form-select" required>
                                    <option value="">-- Pilih Kode Induk --</option>
                                    <?php 
                                    mysqli_data_seek($q_kode, 0);
                                    while($induk = mysqli_fetch_assoc($q_kode)): 
                                        $selected = ($induk['id_kode'] == $data['id_kode']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $induk['id_kode'] ?>" <?= $selected ?>>
                                            <?= $induk['id_kode'] ?> - <?= $induk['kode_klasifikasi'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Sub Klasifikasi</label>
                                <select name="id_sub" id="child_kode" class="form-select" required>
                                    <option value="">-- Pilih Kode Induk Terlebih Dahulu --</option>
                                    <?php 
                                    mysqli_data_seek($q_sub, 0);
                                    while($anak = mysqli_fetch_assoc($q_sub)): 
                                        $selected = ($anak['id_sub'] == $data['id_sub']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $anak['id_sub'] ?>" data-parent="<?= $anak['id_kode'] ?>" <?= $selected ?>>
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
                                    mysqli_data_seek($q_jenis, 0);
                                    while($j = mysqli_fetch_assoc($q_jenis)): 
                                        $selected = ($j['id_jenis'] == $data['id_jenis']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $j['id_jenis'] ?>" <?= $selected ?>>
                                            <?= $j['nama_jenis'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Nomor Arsip</label>
                                <input type="text" name="nomor_arsip" class="form-control" value="<?= htmlspecialchars($data['nomor_arsip']) ?>" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Retensi (Tahun)</label>
                                <input type="number" name="retensi" class="form-control" value="<?= htmlspecialchars($data['retensi']) ?>" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Lokasi Simpan</label>
                                <input type="text" name="lokasi_simpan" class="form-control" value="<?= htmlspecialchars($data['lokasi_simpan']) ?>" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Metode Perlindungan</label>
                                <select name="id_metode" class="form-select" required>
                                    <option value="">-- Pilih Metode --</option>
                                    <?php 
                                    mysqli_data_seek($q_metode, 0);
                                    while($m = mysqli_fetch_assoc($q_metode)): 
                                        $selected = ($m['id_metode'] == $data['id_metode']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $m['id_metode'] ?>" <?= $selected ?>>
                                            <?= $m['nama_metode'] ?>
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
                            <a href="arsip-vital.php" class="btn btn-secondary me-2">Batal</a>
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

<!-- <?php include '../../layout/footer.php'; ?> -->

<script>
document.addEventListener("DOMContentLoaded", function() {
    const parentSelect = document.getElementById("parent_kode");
    const childSelect = document.getElementById("child_kode");
    
    // Simpan semua opsi asli dari childSelect
    const originalOptions = Array.from(childSelect.options);

    // Fungsi untuk memfilter Child berdasarkan Parent
    function filterChildOptions(selectedValue) {
        // Kosongkan child select
        childSelect.innerHTML = '<option value="">-- Pilih Sub Klasifikasi --</option>';
        childSelect.disabled = true;

        if (selectedValue) {
            // Filter opsi yang sesuai dengan data-parent
            const filteredOptions = originalOptions.filter(opt => {
                return opt.getAttribute("data-parent") === selectedValue;
            });

            if (filteredOptions.length > 0) {
                childSelect.disabled = false;
                filteredOptions.forEach(opt => {
                    // Clone node agar opsi asli tidak hilang dari memori
                    childSelect.add(opt.cloneNode(true));
                });
            } else {
                childSelect.innerHTML = '<option value="">-- Tidak ada sub klasifikasi --</option>';
            }
        }
    }

    // 1. Logika saat User mengubah Parent Dropdown
    if (parentSelect && childSelect) {
        parentSelect.addEventListener("change", function() {
            filterChildOptions(this.value);
        });
    }

    // 2. Logika Saat Halaman Pertama Kali Dimuat (Mode Edit)
    const currentParentValue = parentSelect.value;
    const currentChildValue = "<?= $data['id_sub'] ?>"; // Ambil dari PHP

    if (currentParentValue) {
        filterChildOptions(currentParentValue);
        
        // Setelah opsi difilter, set kembali nilai child yang tersimpan
        if (currentChildValue) {
            childSelect.value = currentChildValue;
        }
    }
});
</script>