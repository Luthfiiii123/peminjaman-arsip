<?php
session_start();
include '../../konektor.php';

if (isset($_POST['simpan'])) {
    
    $user_id = $_SESSION['user_id'] ?? 1;
    
    // =======================================================
    // 1. TANGKAP DATA (VERSI PENYESUAIAN OTOMATIS)
    // =======================================================
    
    // Menangkap 'nomor_arsip' (sesuai pesan error Anda sebelumnya)
    // Menggunakan operator '??' agar jika HTML mengirim 'no_arsip' atau 'nomor_arsip', tetap tertangkap.
    $no_arsip = mysqli_real_escape_string($db, $_POST['nomor_arsip'] ?? $_POST['no_arsip'] ?? '');
    
    // Menangkap 'retensi' (sesuai pesan error Anda)
    $retensi = mysqli_real_escape_string($db, $_POST['retensi'] ?? $_POST['retensi_arsip'] ?? '');

    $uraian_arsip = mysqli_real_escape_string($db, $_POST['uraian_arsip']);
    $kurun_waktu  = mysqli_real_escape_string($db, $_POST['kurun_waktu']);
    $jumlah       = mysqli_real_escape_string($db, $_POST['jumlah']);
    $keterangan   = mysqli_real_escape_string($db, $_POST['keterangan']);
    
    // Menangkap Jenis & Perkembangan (Input Text)
    // Kita simpan ke variabel PHP
    $jenis_input  = mysqli_real_escape_string($db, $_POST['jenis_arsip'] ?? $_POST['id_jenis'] ?? '');
    $tingkat_input= mysqli_real_escape_string($db, $_POST['tingkat_perkembangan'] ?? $_POST['id_tingkat'] ?? '');

    $lokasi_simpan = isset($_POST['lokasi_simpan']) ? mysqli_real_escape_string($db, $_POST['lokasi_simpan']) : '';

    $id_kode = $_POST['id_kode'];
    
    // Validasi ID SUB
    if (empty($_POST['id_sub'])) {
        echo "<script>alert('Gagal! Sub Klasifikasi harus dipilih.'); window.history.back();</script>";
        exit;
    }
    $id_sub = $_POST['id_sub'];

    // Logika Sub-Sub
    if (empty($_POST['id_subsub'])) {
        $id_subsub = "NULL"; 
    } else {
        $clean_subsub = mysqli_real_escape_string($db, $_POST['id_subsub']);
        $id_subsub = "'$clean_subsub'";
    }

    // =======================================================
    // 2. PROSES UPLOAD FILE
    // =======================================================
    $file_name  = $_FILES['file_arsip']['name'];
    $file_tmp   = $_FILES['file_arsip']['tmp_name'];
    $file_ext   = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $target_dir = "../../uploads/";

    if ($file_ext != 'pdf') {
        echo "<script>alert('Gagal! Hanya format PDF yang diperbolehkan.'); window.history.back();</script>";
        exit;
    }

    $new_file_name = "INAKTIF_" . uniqid() . ".pdf";

    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    if (move_uploaded_file($file_tmp, $target_dir . $new_file_name)) {
        
        // =======================================================
        // 3. INSERT KE DATABASE (FIXED)
        // =======================================================
        // Format: NamaKolomDB, NamaKolomDB ... VALUES ('$VariablePHP', '$VariablePHP' ...)
        
        $query = "INSERT INTO arsip_inaktif (
                    user_id, 
                    uraian_arsip, 
                    nomor_arsip,   /* Nama Kolom di DB Anda */
                    id_kode, 
                    id_sub, 
                    id_subsub, 
                    id_jenis,      /* Nama Kolom di DB Anda */
                    kurun_waktu, 
                    retensi,       /* Nama Kolom di DB Anda */
                    jumlah, 
                    id_tingkat,    /* Nama Kolom di DB Anda */
                    keterangan, 
                    file_pdf
                  ) VALUES (
                    '$user_id', 
                    '$uraian_arsip', 
                    '$no_arsip',     /* Isi Data */
                    '$id_kode', 
                    '$id_sub', 
                    $id_subsub, 
                    '$jenis_input',  /* Isi Data */
                    '$kurun_waktu', 
                    '$retensi',      /* Isi Data */
                    '$jumlah', 
                    '$tingkat_input',/* Isi Data */
                    '$keterangan', 
                    '$new_file_name'
                  )";

        if (mysqli_query($db, $query)) {
            echo "<script>alert('Berhasil menyimpan data Arsip Inaktif!'); window.location='arsip-inaktif.php';</script>";
        } else {
            // Debugging: Tampilkan pesan error detail jika gagal
            unlink($target_dir . $new_file_name);
            echo "<h3>Gagal Menyimpan ke Database</h3>";
            echo "<p>Pesan Error: " . mysqli_error($db) . "</p>";
            echo "<p>Periksa kembali nama kolom di database Anda.</p>";
        }

    } else {
        echo "<script>alert('Gagal mengupload file.'); window.history.back();</script>";
    }

} else {
    header("Location: arsip-inaktif.php");
}
?>