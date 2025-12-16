<?php
session_start();
include '../../konektor.php';

// Cek apakah ada ID yang dikirim
if (isset($_GET['id'])) {
    
    // Amankan ID dari SQL Injection
    $id = mysqli_real_escape_string($db, $_GET['id']);

    /* ==============================================
       TAHAP 1: CARI DAN HAPUS FILE FISIK (PDF)
    ============================================== */
    $query_cek = mysqli_query($db, "SELECT file_pdf FROM arsip_permanen WHERE id_arsip_permanen = '$id'");
    $data = mysqli_fetch_assoc($query_cek);

    // Jika data ditemukan dan nama filenya tidak kosong
    if ($data && !empty($data['file_pdf'])) {
        $lokasi_file = "../../uploads/" . $data['file_pdf'];
        
        // Cek apakah file ada di folder, lalu hapus
        if (file_exists($lokasi_file)) {
            unlink($lokasi_file); // Perintah unlink untuk menghapus file
        }
    }

    /* ==============================================
       TAHAP 2: HAPUS DATA DI DATABASE
    ============================================== */
    $hapus = mysqli_query($db, "DELETE FROM arsip_permanen WHERE id_arsip_permanen = '$id'");

    if ($hapus) {
        // Berhasil -> Kembali ke halaman arsip (bukan index login)
        echo "<script>alert('Data Berhasil Dihapus!'); window.location='arsip-permanen.php';</script>";
    } else {
        // Gagal
        echo "<script>alert('Gagal menghapus data: " . mysqli_error($db) . "'); window.location='arsip-permanen.php';</script>";
    }

} else {
    // Jika tidak ada ID, kembalikan saja
    header("Location: arsip-permanen.php");
}
?>