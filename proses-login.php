<?php
session_start();

include 'konektor.php';

$role = $_POST['role'];

if ($_POST['captcha'] !== strval($_SESSION['captcha'])) {
    $_SESSION['captcha'] = rand(10000, 99999);
    header("Location:index.php?alert=gagal");
    exit();
}


if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $password = $_POST['password'];

    $query = mysqli_query($db, "SELECT * FROM users WHERE username='$username'");
    $cek = mysqli_num_rows($query);

    if ($cek > 0) {
        $data = mysqli_fetch_assoc($query);
        if ($password == $data['password']) {
            $_SESSION['id'] = $data['id'];
            $_SESSION['username'] = $data['username'];
            $_SESSION['nama_lengkap'] = $data['nama_lengkap'];
            $_SESSION['role'] = $data['role'];
            $_SESSION['status'] = "login";

            header("Location:dashboard.php");
        } else {
            header("Location:index.php?alert=gagal");
        }
    } else {
        header("Location:index.php?alert=gagal");
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location:index.php?alert=logout");
}
