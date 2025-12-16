<?php
session_start();
include '../../konektor.php';

if (isset($_POST['simpan'])) {
    
    // 1. TANGKAP DATA
    $user_id   = $_SESSION['id_user'] ?? 1;
    
    $uraian    = mysqli_real_escape_string($db, $_POST['uraian_informasi']);
    $id_kode   = $_POST['id_kode'];
    $id_sub    = $_POST['id_sub'];
    
    // --- [PERBAIKAN 1] TANGKAP ID JENIS ---
    $id_jenis  = $_POST['id_jenis']; 
    // --------------------------------------

    // Subsub (Boleh kosong/NULL)
    $id_subsub = !empty($_POST['id_subsub']) ? "'".$_POST['id_subsub']."'" : "NULL";

    // Data tambahan
    $id_tingkat = $_POST['id_tingkat'];
    $id_nasib   = $_POST['id_nasib'];
    $kurun      = $_POST['kurun_waktu'];
    $jumlah     = $_POST['jumlah'];

    // 2. UPLOAD FILE PDF
    $file_name = $_FILES['file_arsip']['name'];
    $file_tmp  = $_FILES['file_arsip']['tmp_name'];
    $new_file_name = uniqid() . '.pdf';
    $target_dir = "../../uploads/";

    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }

    if (move_uploaded_file($file_tmp, $target_dir . $new_file_name)) {
        
        // 3. INSERT KE DATABASE
        // --- [PERBAIKAN 2] TAMBAHKAN id_jenis KE DALAM QUERY ---
        $query = "INSERT INTO arsip_permanen (
                    user_id, 
                    uraian_informasi, 
                    id_kode, 
                    id_sub, 
                    id_subsub, 
                    id_jenis,    /* <--- TAMBAHAN DISINI */
                    id_tingkat, 
                    id_nasib, 
                    kurun_waktu, 
                    jumlah, 
                    file_pdf
                  ) VALUES (
                    '$user_id', 
                    '$uraian', 
                    '$id_kode', 
                    '$id_sub', 
                    $id_subsub, 
                    '$id_jenis', /* <--- TAMBAHAN DISINI */
                    '$id_tingkat', 
                    '$id_nasib', 
                    '$kurun', 
                    '$jumlah', 
                    '$new_file_name'
                  )";

        if (mysqli_query($db, $query)) {
            echo "<script>alert('Berhasil menyimpan Arsip Permanen!'); window.location='arsip-permanen.php';</script>";
        } else {
            echo "Gagal Database: " . mysqli_error($db);
        }
    } else {
        echo "<script>alert('Gagal Upload File PDF!'); window.history.back();</script>";
    }
} else {
    header("Location: arsip-permanen.php");
}
?>