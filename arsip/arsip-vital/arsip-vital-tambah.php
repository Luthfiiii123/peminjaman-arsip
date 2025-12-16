<?php
session_start();
include '../../konektor.php';

// Pastikan tombol 'simpan' ditekan
if (isset($_POST['simpan'])) {

    // 1. AMBIL ID USER (Solusi Error user_id)
    $id_user_login = $_SESSION['id_user'] ?? 1; 

    // 2. AMBIL DATA DARI FORM
    // Pastikan Form HTML sudah ditambahkan name="id_kode" pada select parent
    $kode    = $_POST['id_kode'];       // <-- INI PERBAIKANNYA (Menangkap id_kode)
    $sub     = $_POST['id_sub'];        
    $uraian  = mysqli_real_escape_string($db, $_POST['uraian_informasi']);
    $asal    = $_POST['asal_arsip'];
    $jenis   = $_POST['id_jenis'];      
    $nomor   = $_POST['nomor_arsip'];
    $retensi = $_POST['retensi'];
    $lokasi  = $_POST['lokasi_simpan'];
    $metode  = $_POST['id_metode'];

    // 3. PROSES UPLOAD FILE
    $nama_file = ''; 
    
    if (isset($_FILES['file_arsip']) && $_FILES['file_arsip']['error'] == 0) {
        $file_tmp  = $_FILES['file_arsip']['tmp_name'];
        $file_orig = $_FILES['file_arsip']['name'];
        
        // Buat nama unik
        $nama_file = time() . '_' . $file_orig;
        
        $target_dir = "../../uploads/";
        
        if (!move_uploaded_file($file_tmp, $target_dir . $nama_file)) {
            echo "<script>alert('Gagal upload file!'); window.history.back();</script>";
            exit;
        }
    } else {
        echo "<script>alert('File PDF wajib diupload!'); window.history.back();</script>";
        exit;
    }

    // 4. QUERY INSERT DATABASE
    // Tambahkan 'id_kode' ke dalam kolom dan values
    $query = "INSERT INTO arsip_vital 
              (user_id, id_kode, id_sub, uraian_informasi, asal_arsip, id_jenis, nomor_arsip, retensi, lokasi_simpan, id_metode, file_pdf) 
              VALUES 
              ('$id_user_login', '$kode', '$sub', '$uraian', '$asal', '$jenis', '$nomor', '$retensi', '$lokasi', '$metode', '$nama_file')";

    // Eksekusi Query
    if (mysqli_query($db, $query)) {
        echo "<script>alert('Data Berhasil Disimpan!'); window.location='arsip-vital.php';</script>";
    } else {
        // Tampilkan error lengkap jika masih gagal
        echo "Error: " . mysqli_error($db);
    }

} else {
    header("Location: index.php");
}
?>