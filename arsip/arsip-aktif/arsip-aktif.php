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
    $kondisi[] = "(arsip_aktif.uraian_arsip LIKE '%$kata_kunci%' 
                   OR arsip_aktif.uraian_berkas LIKE '%$kata_kunci%' 
                   OR arsip_aktif.no_berkas LIKE '%$kata_kunci%')";
}

// B. Filter Klasifikasi
if ($klasifikasi !== '' && $klasifikasi !== 'semua') {
    $kondisi[] = "arsip_aktif.id_kode = '$klasifikasi'";
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
    SELECT arsip_aktif.*, 
        kode_klasifikasi.kode_klasifikasi,
        kode_klasifikasi.deskripsi,
        sub_klasifikasi.nama_sub,
        sub_klasifikasi.id_sub,
        sub_sub_klasifikasi.id_subsub AS kode_subsub,
        sub_sub_klasifikasi.nama_subsub,
        klasifikasi_keamanan.nama_keamanan
    FROM arsip_aktif
    LEFT JOIN kode_klasifikasi ON arsip_aktif.id_kode = kode_klasifikasi.id_kode
    LEFT JOIN sub_klasifikasi ON arsip_aktif.id_sub = sub_klasifikasi.id_sub
    LEFT JOIN sub_sub_klasifikasi ON arsip_aktif.id_subsub = sub_sub_klasifikasi.id_subsub
    LEFT JOIN klasifikasi_keamanan ON arsip_aktif.id_keamanan = klasifikasi_keamanan.id_keamanan
    $where
    ORDER BY arsip_aktif.id_arsip_aktif DESC
";
$query_arsip = mysqli_query($db, $query_string);

/* ==============================================
   3. QUERY UNTUK DROPDOWN (MODAL)
============================================== */
$q_kode     = mysqli_query($db, "SELECT * FROM kode_klasifikasi ORDER BY id_kode ASC");
$q_sub      = mysqli_query($db, "SELECT * FROM sub_klasifikasi ORDER BY id_sub ASC");
$q_subsub   = mysqli_query($db, "SELECT * FROM sub_sub_klasifikasi ORDER BY id_subsub ASC");
$q_keamanan = mysqli_query($db, "SELECT * FROM klasifikasi_keamanan ORDER BY id_keamanan ASC");

include '../../layout/header.php';
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">

<div class="d-flex">
    <?php include '../../layout/sidebar.php'; ?>

    <main class="flex-grow-1 p-4" style="background-color: #f3f4f6; min-height:100vh; min-width: 0;">
        <div class="container-fluid">
            <div class="card shadow-lg rounded-3 overflow-hidden">
                
                <div class="card-header text-white d-flex justify-content-between align-items-center"
                     style="background: linear-gradient(90deg, #4f46e5, #4338ca);"> 
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-file-invoice me-2"></i> Data Arsip Aktif
                    </h5>
                    <button type="button" class="btn btn-light btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="fas fa-plus"></i> Tambah Data
                    </button>
                </div>

                <div class="card-body">                  
                    
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2" id="top-container">
                        <div class="d-flex align-items-center gap-2">
                            <div id="buttons-container"></div>
                            <div id="length-container"></div>
                        </div>
                        <div id="search-container"></div>
                    </div>

                    <div class="table-responsive">
                        <table id="tableArsipAktif" class="table table-bordered table-hover align-middle w-100" style="white-space: nowrap;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>No</th>
                                    <th>Uraian Informasi Arsip</th>
                                    <th>Kode Klasifikasi</th>
                                    <th>Uraian Informasi Berkas</th>
                                    <th>No Berkas</th>
                                    <th>Jumlah</th>
                                    <th>Tahun</th>
                                    <th>Keamanan & Akses</th>
                                    <th>File</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (mysqli_num_rows($query_arsip) > 0) { ?>
                                <?php while ($data = mysqli_fetch_assoc($query_arsip)) { ?>
                                <tr>
                                    <td class="text-center"><?= $nomor_urut++ ?></td>
                                    
                                    <td style="white-space: normal; min-width: 300px;">
                                        <?= htmlspecialchars($data['uraian_arsip']) ?>
                                    </td>

                                    <td>
                                        <?php if (!empty($data['nama_subsub'])) { ?>
                                            <div class="fw-bold text-primary"><?= htmlspecialchars($data['kode_subsub']) ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($data['nama_subsub']) ?></div>
                                        <?php } elseif (!empty($data['nama_sub'])) { ?>
                                            <div class="fw-bold text-primary"><?= htmlspecialchars($data['id_sub']) ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($data['nama_sub']) ?></div>
                                        <?php } else { ?>
                                            <div class="fw-bold text-primary"><?= htmlspecialchars($data['kode_klasifikasi']) ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($data['deskripsi']) ?></div>
                                        <?php } ?>
                                    </td>

                                    <td style="white-space: normal; min-width: 300px;">
                                        <?= htmlspecialchars($data['uraian_berkas']) ?>
                                    </td>
                                    
                                    <td class="text-center fw-bold"><?= htmlspecialchars($data['no_berkas']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($data['jumlah']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($data['kurun_waktu']) ?></td>

                                    <td class="text-center">
                                        <span class="badge bg-secondary mb-1">
                                            <?= htmlspecialchars($data['nama_keamanan'] ?? '-') ?>
                                        </span>
                                    </td>

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
                                            <a href="arsip-aktif-edit.php?id=<?= $data['id_arsip_aktif'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                            <a href="arsip-aktif-hapus.php?id=<?= $data['id_arsip_aktif'] ?>" 
                                               onclick="return confirm('Hapus data arsip aktif ini?')" 
                                               class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap" id="bottom-container">
                        <div id="info-container"></div>
                        <div id="pagination-container"></div>
                    </div>

                </div>
            </div>
        </div>
    </main>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"> 
        <div class="modal-content">
            <div class="modal-header text-white" style="background: linear-gradient(90deg, #10b981, #059669);">
                <h5 class="modal-title fw-bold"><i class="fas fa-folder-plus me-2"></i>Tambah Arsip Aktif</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="arsip-aktif-tambah.php" method="POST" enctype="multipart/form-data" id="formTambah">
                    <input type="hidden" name="user_id" value="<?= $user_id ?>">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Uraian Informasi Arsip</label>
                            <textarea name="uraian_arsip" class="form-control" rows="2" placeholder="Contoh: Laporan Kegiatan Tahunan..." required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">1. Kode Klasifikasi Induk</label>
                            <select name="id_kode" id="parent_kode" class="form-select" required>
                                <option value="">-- Pilih Kode --</option>
                                <?php mysqli_data_seek($q_kode, 0); while($k = mysqli_fetch_assoc($q_kode)): ?>
                                    <option value="<?= $k['id_kode'] ?>"><?= $k['kode_klasifikasi'] ?> - <?= $k['deskripsi'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">2. Sub Klasifikasi</label>
                            <select name="id_sub" id="child_kode" class="form-select" required disabled>
                                <option value="">-- Pilih Induk Dulu --</option>
                                <?php mysqli_data_seek($q_sub, 0); while($s = mysqli_fetch_assoc($q_sub)): ?>
                                    <option value="<?= $s['id_sub'] ?>" data-parent="<?= $s['id_kode'] ?>">
                                        <?= $s['id_sub'] ?> - <?= $s['nama_sub'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">3. Sub-Sub (Opsional)</label>
                            <select name="id_subsub" id="grandchild_kode" class="form-select" disabled>
                                <option value="">-- Pilih Sub Dulu --</option>
                                <?php mysqli_data_seek($q_subsub, 0); while($ss = mysqli_fetch_assoc($q_subsub)): ?>
                                    <option value="<?= $ss['id_subsub'] ?>" data-parent="<?= $ss['id_sub'] ?>">
                                        <?= $ss['nama_subsub'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Uraian Informasi Berkas</label>
                            <input type="text" name="uraian_berkas" id="input_uraian_berkas" class="form-control" placeholder="Terisi otomatis sesuai klasifikasi..." required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">No. Berkas</label>
                            <input type="text" name="no_berkas" class="form-control" placeholder="Contoh: 001/B/2024" required>
                        </div> 
                        <div class="col-12">
                            <label class="form-label fw-semibold">Kurun Waktu (Tahun)</label>
                            <input type="number" name="kurun_waktu" class="form-control" placeholder="2024" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Jumlah</label>
                            <input type="text" name="jumlah" class="form-control" placeholder="1 Berkas" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Keterangan Klasifikasi Keamanan</label>
                            <select name="id_keamanan" class="form-select" required>
                                <option value="">-- Pilih Tingkat Keamanan --</option>
                                <?php mysqli_data_seek($q_keamanan, 0); while($km = mysqli_fetch_assoc($q_keamanan)): ?>
                                    <option value="<?= $km['id_keamanan'] ?>"><?= $km['nama_keamanan'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Upload File (PDF)</label>
                            <input type="file" name="file_arsip" class="form-control" accept=".pdf" required>
                            <div class="form-text text-danger">*Wajib format PDF</div>
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

<script>
    $(document).ready(function() {
        if ($.fn.DataTable.isDataTable('#tableArsipAktif')) {
            $('#tableArsipAktif').DataTable().destroy();
        }

        var table = $('#tableArsipAktif').DataTable({
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
                    title: 'Data Arsip Aktif', 
                    exportOptions: { columns: ':not(:last-child)' } 
                },
                { 
                    extend: 'pdf', 
                    text: '<i class="fas fa-file-pdf text-danger"></i> PDF', 
                    className: 'btn btn-light border btn-sm me-2 shadow-sm fw-bold', 
                    title: 'Data Arsip Aktif', 
                    orientation: 'landscape', 
                    exportOptions: { columns: ':not(:last-child)' } 
                },
                { 
                    extend: 'print', 
                    text: '<i class="fas fa-print text-dark"></i> Print', 
                    className: 'btn btn-light border btn-sm me-2 shadow-sm fw-bold', 
                    title: 'Data Arsip Aktif', 
                    exportOptions: { columns: ':not(:last-child)' } 
                }
            ],

            lengthChange: true,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],

            initComplete: function () {
                var api = this.api();
                api.buttons().container().appendTo('#buttons-container');
                $('.dataTables_length').appendTo('#length-container');
                $('.dataTables_info').appendTo('#info-container');
                $('.dataTables_paginate').appendTo('#pagination-container');

                $('#search-container').html(`
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="customSearch" class="form-control border-start-0" placeholder="Cari data...">
                    </div>
                `);
                $('.dataTables_filter').hide();
                $('#customSearch').on('keyup', function() {
                    table.search(this.value).draw();
                });
            },
            
            language: {
                search: "",
                lengthMenu: "_MENU_",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                infoEmpty: "Data kosong",
                zeroRecords: "Tidak ditemukan",
                paginate: { first: "Awal", last: "Akhir", next: '<i class="fas fa-chevron-right"></i>', previous: '<i class="fas fa-chevron-left"></i>' }
            },
            pageLength: 10
        });

        // Script Dropdown Berjenjang (Tidak Berubah)
        const parentSelect = document.getElementById("parent_kode");
        const childSelect = document.getElementById("child_kode");
        const grandChildSelect = document.getElementById("grandchild_kode");
        const uraianInput = document.getElementById("input_uraian_berkas");
        const formTambah = document.getElementById("formTambah");

        function autoFillUraian(selectElement) {
            if (selectElement.value === "") return;
            const selectedText = selectElement.options[selectElement.selectedIndex].text;
            const parts = selectedText.split(' - ');
            if (parts.length > 1) {
                uraianInput.value = parts.slice(1).join(' - ');
            } else {
                uraianInput.value = selectedText;
            }
        }

        if (parentSelect && childSelect && grandChildSelect) {
            const childOptions = Array.from(childSelect.options);
            const grandChildOptions = Array.from(grandChildSelect.options);

            parentSelect.addEventListener("change", function() {
                const selectedParentID = this.value; 
                autoFillUraian(this);
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

            childSelect.addEventListener("change", function() {
                const selectedSubID = this.value;
                autoFillUraian(this);
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

            grandChildSelect.addEventListener("change", function() {
                autoFillUraian(this);
            });

            formTambah.addEventListener("submit", function() {
                childSelect.disabled = false;
                grandChildSelect.disabled = false;
            });
        }
    });
</script>

<style>
    #length-container select {
        padding: 0.25rem 2rem 0.25rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 0.25rem;
        border: 1px solid #dee2e6;
        background-color: #fff;
        cursor: pointer;
        display: inline-block;
    }
    div.dataTables_length { margin-bottom: 0 !important; float: none !important; }
    div.dataTables_length label { font-weight: normal; margin-bottom: 0; }
</style>