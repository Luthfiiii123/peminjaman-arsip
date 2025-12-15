<?php
include 'konektor.php';

if (isset($_POST['reset'])) {
    $email = mysqli_real_escape_string($db, $_POST['email']);

    // cek email
    $cek = mysqli_query($db, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($cek) == 0) {
        header("location:index.php?alert=email_not_found");
        exit();
    }

    // token random
    $token = bin2hex(random_bytes(16));

    // simpan token
    mysqli_query($db, "UPDATE users SET reset_token='$token' WHERE email='$email'");

    // link reset
    $link = "http://localhost/peminjaman-arsip/reset-password.php?token=$token";

    // kirim email paling sederhana
    $subject = "Reset Password E-Arsip";
    $message = "Klik link berikut untuk reset password:\n\n$link";
    $headers = "From: no-reply@earsip.com";

    mail($email, $subject, $message, $headers);

    header("location:index.php?alert=reset_success");
}
?>