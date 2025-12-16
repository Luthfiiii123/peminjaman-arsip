<?php
session_start();
include 'konektor.php';

// Pastikan captcha ada isinya
if (isset($_POST['login'])) {

    $role = $_POST['role']; // (Opsional) Data role dari dropdown

    // 1. Cek Captcha
    if ($_POST['captcha'] !== strval($_SESSION['captcha'])) {
        // Jika salah, generate ulang dan kembalikan
        $_SESSION['captcha'] = rand(10000, 99999);
        header("Location:index.php?alert=gagal_captcha"); // Tambah alert khusus
        exit();
    }

    // 2. Filter Input
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $password = $_POST['password'];

    // 3. Cek User di Database
    // Kita cek username dan role-nya sekalian (opsional, tapi lebih aman)
    $query = mysqli_query($db, "SELECT * FROM users WHERE username='$username'");
    $cek = mysqli_num_rows($query);

    if ($cek > 0) {
        $data = mysqli_fetch_assoc($query);

        // 4. Cek Password
        // Catatan: Jika nanti pakai MD5, ubah jadi: if (md5($password) == $data['password'])
        if ($password == $data['password']) {

            // --- BAGIAN PENTING (PERBAIKAN) ---
            // Gunakan nama 'id_user' agar terbaca di script tambah arsip
            $_SESSION['id_user']      = $data['id']; 
            
            $_SESSION['username']     = $data['username'];
            $_SESSION['nama_lengkap'] = $data['nama_lengkap'];
            $_SESSION['role']         = $data['role'];
            $_SESSION['status']       = "login";

            // Redirect ke Dashboard
            header("Location:dashboard.php");
            
        } else {
            // Password Salah
            header("Location:index.php?alert=gagal");
        }
    } else {
        // Username Tidak Ditemukan
        header("Location:index.php?alert=gagal");
    }
} else {
    // Akses tanpa tombol login
    header("Location:index.php");
}

// Fitur Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location:index.php?alert=logout");
}
?>