<?php
include 'konektor.php';

if (isset($_POST['token']) && isset($_POST['password'])) {

    $token = $_POST['token'];
    $password = $_POST['password'];

    // hash password
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // update password
    $update = mysqli_query($db,
        "UPDATE users SET password='$hash', reset_token=NULL WHERE reset_token='$token'"
    );

    if ($update) {
        echo "<script>alert('Password berhasil diubah. Silakan login.'); window.location='index.php';</script>";
    } else {
        echo "Gagal menyimpan password.";
    }
}
?>
