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

// A. Filter Pencarian
if ($kata_kunci !== '') {
    $kondisi[] = "(arsip_vital.uraian_informasi LIKE '%$kata_kunci%' 
                   OR arsip_vital.nomor_arsip LIKE '%$kata_kunci%' 
                   OR arsip_vital.lokasi_simpan LIKE '%$kata_kunci%')";
}

// B. Filter Kategori
if ($klasifikasi !== '' && $klasifikasi !== 'semua' && $klasifikasi !== 'vital') {
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
    SELECT arsip_vital.*, 
        sub_klasifikasi.nama_sub, 
        sub_klasifikasi.id_sub,
        kode_klasifikasi.kode_klasifikasi,
        kode_klasifikasi.deskripsi,
        jenis_arsip.nama_jenis, 
        metode_perlindungan.nama_metode
    FROM arsip_vital
    LEFT JOIN kode_klasifikasi ON arsip_vital.id_kode = kode_klasifikasi.id_kode
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
$q_kode   = mysqli_query($db, "SELECT * FROM kode_klasifikasi ORDER BY id_kode ASC");
$q_sub    = mysqli_query($db, "SELECT * FROM sub_klasifikasi ORDER BY id_sub ASC");
$q_jenis  = mysqli_query($db, "SELECT * FROM jenis_arsip WHERE kategori='Vital' ORDER BY nama_jenis ASC");
$q_metode = mysqli_query($db, "SELECT * FROM metode_perlindungan ORDER BY nama_metode ASC");

include '../../layout/header.php';
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">

<div class="d-flex">
    <?php include '../../layout/sidebar.php'; ?>
    
    <main class="flex-grow-1 p-4" style="background-color: #f3f4f6; min-height: 90vh; min-width: 0;">
        <div class="container-fluid">
            <div class="card shadow-lg rounded-3 overflow-hidden">
                
                <div class="card-header text-white d-flex justify-content-between align-items-center"
                     style="background: linear-gradient(90deg, #4f46e5, #4338ca);">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-archive me-2"></i> Data Arsip Vital
                    </h5>
                    <button type="button" class="btn btn-light btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="fas fa-plus me-1"></i> Tambah Data
                    </button>
                </div>

                <div class="card-body">
                    
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <div id="buttons-container"></div>
                            
                            <div id="length-container"></div>
                        </div>
                        
                        <div id="search-container"></div>
                    </div>

                    <div class="table-responsive">
                        <table id="tableArsipVital" class="table table-bordered table-hover align-middle w-100" style="white-space: nowrap;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>No</th>                                  
                                    <th>Uraian Informasi</th>
                                    <th>Asal</th>
                                    <th>Kode Klasifikasi</th>
                                    <th>Jenis</th>
                                    <th>Nomor Arsip</th>
                                    <th>Retensi</th>
                                    <th>Lokasi</th>
                                    <th>Metode</th>
                                    <th>File</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($query_arsip) > 0) { ?>
                                    <?php while ($data = mysqli_fetch_assoc($query_arsip)) { ?>
                                    <tr>
                                        <td class="text-center"><?= $nomor_urut++ ?></td>
                                        <td><?= htmlspecialchars($data['uraian_informasi']) ?></td>
                                        <td class="text-center"><span class="badge <?= $data['asal_arsip'] == 'internal' ? 'bg-primary' : 'bg-success' ?>"><?= ucfirst($data['asal_arsip']) ?></span></td>
                                        <td>
                                            <?php if (!empty($data['nama_sub'])) { ?>
                                                <div class="fw-bold text-primary"><?= $data['id_sub'] ?></div><div class="small text-muted"><?= $data['nama_sub'] ?></div>
                                            <?php } else { ?>
                                                <div class="fw-bold text-primary"><?= $data['kode_klasifikasi'] ?></div><div class="small text-muted"><?= $data['deskripsi'] ?></div>
                                            <?php } ?>
                                        </td>
                                        <td><?= htmlspecialchars($data['nama_jenis']) ?></td>
                                        <td><?= htmlspecialchars($data['nomor_arsip']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($data['retensi']) ?> Tahun</td>
                                        <td><?= htmlspecialchars($data['lokasi_simpan']) ?></td>
                                        <td><?= htmlspecialchars($data['nama_metode']) ?></td>
                                        <td class="text-center">
                                            <?php if (!empty($data['file_pdf'])): ?>
                                                <a href="../../uploads/<?= $data['file_pdf'] ?>" target="_blank" class="btn btn-sm btn-outline-danger"><i class="fas fa-file-pdf"></i></a>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="arsip-vital-edit.php?id=<?= $data['id_arsip_vital'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                                <a href="arsip-vital-hapus.php?id=<?= $data['id_arsip_vital'] ?>" onclick="return confirm('Hapus?')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                        <div id="info-container"></div>
                        <div id="pagination-container"></div>
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
                                if (isset($q_jenis)) {
                                    mysqli_data_seek($q_jenis, 0); 
                                    while($j = mysqli_fetch_assoc($q_jenis)): 
                                ?>
                                    <option value="<?= $j['id_jenis'] ?>">
                                        <?= $j['nama_jenis'] ?>
                                    </option>
                                <?php endwhile; } ?>
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

<?php include '../../layout/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.5.1.js"></script>

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js"></script>

<script>
    $(document).ready(function() {
        if ($.fn.DataTable.isDataTable('#tableArsipVital')) {
            $('#tableArsipVital').DataTable().destroy();
        }

        var table = $('#tableArsipVital').DataTable({
            // DOM Configuration:
            // l = length, B = buttons, f = search, t = table, i = info, p = pagination
            // Kita load semuanya ('lBfrtip'), tapi nanti kita pindah posisinya pakai Javascript
            dom: 'lBfrtip', 
            
            buttons: [
                {
                    extend: 'copy',
                    text: '<i class="fas fa-copy text-secondary"></i> Copy',
                    className: 'btn btn-light border btn-sm me-2 shadow-sm fw-bold', 
                    exportOptions: { columns: ':not(:last-child)' }
                },
                {
                    extend: 'csv',
                    text: '<i class="fas fa-file-csv text-primary"></i> CSV',
                    className: 'btn btn-light border btn-sm me-2 shadow-sm fw-bold',
                    title: 'Data Arsip Vital',
                    exportOptions: { columns: ':not(:last-child)' }
                },
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel text-success"></i> Excel',
                    className: 'btn btn-light border btn-sm me-2 shadow-sm fw-bold',
                    title: 'Data Arsip Vital',
                    exportOptions: { columns: ':not(:last-child)' }
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf text-danger"></i> PDF',
                    className: 'btn btn-light border btn-sm me-2 shadow-sm fw-bold',
                    title: 'Data Arsip Vital',
                    orientation: 'landscape',
                    exportOptions: { columns: ':not(:last-child)' }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print text-dark"></i> Print',
                    className: 'btn btn-light border btn-sm me-2 shadow-sm fw-bold',
                    title: 'Data Arsip Vital',
                    exportOptions: { columns: ':not(:last-child)' }
                }
            ],

            // Aktifkan Menu Pilihan Jumlah Data
            lengthChange: true,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],

            // === BAGIAN INI YANG MEMBUAT TOMBOL STAY ===
            initComplete: function () {
                var api = this.api();

                // 1. Pindah Tombol Ekspor ke wadah khusus
                api.buttons().container().appendTo('#buttons-container');

                // 2. Pindah "Tampilkan 10 data" (Length Menu) ke wadah khusus
                $('.dataTables_length').appendTo('#length-container');

                // 3. Pindah "Menampilkan 1-10" (Info) ke bawah
                $('.dataTables_info').appendTo('#info-container');

                // 4. Pindah Pagination (Next/Prev) ke bawah
                $('.dataTables_paginate').appendTo('#pagination-container');

                // 5. Buat Custom Search di kanan atas
                $('#search-container').html(`
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="customSearch" class="form-control border-start-0" placeholder="Cari data...">
                    </div>
                `);
                
                // Sembunyikan search bawaan agar tidak double
                $('.dataTables_filter').hide();

                // Fungsi Search Custom
                $('#customSearch').on('keyup', function() {
                    table.search(this.value).draw();
                });
            },
            
            language: {
                search: "", 
                lengthMenu: "_MENU_", // Hapus teks "Tampilkan", sisakan dropdown angkanya saja biar rapi
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                infoEmpty: "Data kosong",
                zeroRecords: "Tidak ditemukan",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: '<i class="fas fa-chevron-right"></i>',
                    previous: '<i class="fas fa-chevron-left"></i>'
                }
            },
            pageLength: 10
        });

        // Script Dropdown Berjenjang (Tidak berubah)
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

<style>
    /* Merapikan dropdown length menu */
    #length-container select {
        padding: 0.25rem 2rem 0.25rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 0.25rem;
        border: 1px solid #dee2e6;
        background-color: #fff;
        cursor: pointer;
        display: inline-block;
    }
    /* Menghilangkan margin bawaan DataTables */
    div.dataTables_length {
        margin-bottom: 0 !important;
        float: none !important;
    }
    div.dataTables_length label {
        font-weight: normal;
        margin-bottom: 0;
    }
</style>