<?php
session_start();
include '../../konektor.php';

// Pastikan tombol simpan diklik
if (isset($_POST['simpan'])) {
    
    // ==========================================
    // 1. TANGKAP DATA DARI FORM
    // ==========================================
    $user_id = $_SESSION['user_id'] ?? 1; 
    
    // Gunakan real_escape_string untuk semua input teks
    $uraian_arsip   = mysqli_real_escape_string($db, $_POST['uraian_arsip']);
    $uraian_berkas  = mysqli_real_escape_string($db, $_POST['uraian_berkas']);
    $no_berkas      = mysqli_real_escape_string($db, $_POST['no_berkas']);
    $jumlah         = mysqli_real_escape_string($db, $_POST['jumlah']);
    
    $id_kode        = $_POST['id_kode'];
    $id_sub         = $_POST['id_sub'];
    $kurun_waktu    = $_POST['kurun_waktu'];
    $id_keamanan    = $_POST['id_keamanan'];

    // --- PERBAIKAN PENTING: LOGIKA SUB-SUB (AMAN & BENAR) ---
    // Cek apakah id_subsub dipilih atau kosong
    if (empty($_POST['id_subsub'])) {
        // Jika kosong, kita siapkan kata kunci NULL (tanpa kutip) untuk SQL
        $id_subsub = "NULL"; 
    } else {
        // Jika ada isinya, kita amankan datanya lalu apit dengan kutip
        $clean_subsub = mysqli_real_escape_string($db, $_POST['id_subsub']);
        $id_subsub = "'$clean_subsub'";
    }

    // ==========================================
    // 2. PROSES UPLOAD FILE
    // ==========================================
    $file_name  = $_FILES['file_arsip']['name'];
    $file_tmp   = $_FILES['file_arsip']['tmp_name'];
    $file_size  = $_FILES['file_arsip']['size'];
    $file_ext   = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Folder tujuan upload
    $target_dir = "../../uploads/";

    // Validasi Format
    if ($file_ext != 'pdf') {
        echo "<script>alert('Gagal! Hanya format PDF yang diperbolehkan.'); window.history.back();</script>";
        exit;
    }

    // Validasi Ukuran (Maks 5MB)
    if ($file_size > 5000000) {
        echo "<script>alert('Gagal! Ukuran file terlalu besar (Maks 5MB).'); window.history.back();</script>";
        exit;
    }

    // Buat nama file unik
    $new_file_name = "AKTIF_" . uniqid() . ".pdf";

    // Cek folder uploads
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Pindahkan file
    if (move_uploaded_file($file_tmp, $target_dir . $new_file_name)) {
        
        // ==========================================
        // 3. INSERT KE DATABASE
        // ==========================================
        // Perhatikan variabel $id_subsub tidak memakai kutip lagi di dalam query
        // karena sudah kita atur di atas (entah isinya "NULL" atau "'123'")
        
        $query = "INSERT INTO arsip_aktif (
                    user_id, 
                    uraian_arsip, 
                    id_kode, 
                    id_sub, 
                    id_subsub, 
                    uraian_berkas, 
                    no_berkas, 
                    jumlah, 
                    kurun_waktu, 
                    id_keamanan, 
                    file_pdf
                  ) VALUES (
                    '$user_id', 
                    '$uraian_arsip', 
                    '$id_kode', 
                    '$id_sub', 
                    $id_subsub, 
                    '$uraian_berkas', 
                    '$no_berkas', 
                    '$jumlah', 
                    '$kurun_waktu', 
                    '$id_keamanan', 
                    '$new_file_name'
                  )";

        if (mysqli_query($db, $query)) {
            echo "<script>
                    alert('Berhasil menyimpan data Arsip Aktif!'); 
                    window.location='arsip-aktif.php';
                  </script>";
        } else {
            // Jika Gagal Database, hapus file
            unlink($target_dir . $new_file_name);
            echo "Gagal Database: " . mysqli_error($db);
        }

    } else {
        echo "<script>alert('Gagal mengupload file ke server.'); window.history.back();</script>";
    }

} else {
    header("Location: arsip-aktif.php");
}
?>