<?php
session_start();

// Generate captcha baru *setiap reload halaman*
$_SESSION['captcha'] = rand(10000, 99999);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | E-Arsip</title>
    <link rel="stylesheet" href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <script src="vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="gradient-bg d-flex align-items-center justify-content-center vh-100">
<div class="container px-3">
    <div class="glass-panel shadow-lg rounded-4 p-4 p-md-5 w-100 mx-auto" style="max-width: 520px;">

        <div class="text-center mb-4">
            <h1 class="fw-bold text-dark mb-1" style="font-size: 1.8rem;">E-Arsip</h1>
            <p class="text-secondary">Silakan login untuk melanjutkan</p>
        </div>

        <!-- validasi login -->

<?php
if (isset($_GET['alert'])) {

    if ($_GET['alert'] == "gagal") {
        echo "
        <div id='autoAlert' class='alert alert-danger alert-dismissible fade show mb-3' role='alert'>
            <strong>Gagal!</strong> Username atau Password salah.
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>";
    }

    else if ($_GET['alert'] == "logout") {
        echo "
        <div id='autoAlert' class='alert alert-success alert-dismissible fade show mb-3' role='alert'>
            Anda berhasil logout.
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>";
    }

    else if ($_GET['alert'] == "belum_login") {
        echo "
        <div id='autoAlert' class='alert alert-warning alert-dismissible fade show mb-3' role='alert'>
            Anda harus login terlebih dahulu.
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>";
    }

    else if ($_GET['alert'] == "reset_success") {
        echo "
        <div id='autoAlert' class='alert alert-success alert-dismissible fade show mb-3'>
            Password baru telah dikirim ke email Anda.
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>";
    }

    else if ($_GET['alert'] == "email_not_found") {
        echo "
        <div id='autoAlert' class='alert alert-danger alert-dismissible fade show mb-3'>
            Email tidak terdaftar!
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>";
    }
}
?>


        <form action="proses-login.php" method="POST">
            <div class="form-floating mb-3">
                <input type="text" name="username" id="floatingInput" class="form-control" placeholder="Username" required>
                <label for="floatingInput">Username</label>
            </div>

            <div class="form-floating mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
                <label>Password</label>
            </div>

            <div class="form-floating mb-3">
                <select name="role" class="form-select" id="roleSelect" required>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
                <label for="roleSelect">Pilih Role</label>
            </div>

             <div class="mb-3">
                <label class="form-label fw-semibold">Captcha</label>

                <div class="row g-2 align-items-stretch">

                    <!-- BOX CAPTCHA -->
                    <div class="col-4">
                        <div class="d-flex justify-content-center align-items-center 
                                    bg-light border rounded h-100 fw-bold fs-4">
                            <?= $_SESSION['captcha']; ?>
                        </div>
                    </div>

                    <!-- INPUT -->
                    <div class="col-8">
                        <input type="text"
                            name="captcha"
                            class="form-control h-100"
                            placeholder="Masukkan captcha"
                            required>
                    </div>

                </div>
            </div>


            <div class="text-left mt-2 mb-4">
                <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#forgotModal">
                    Lupa Password?
                </a>
            </div>

            <button type="submit" name="login" class="btn btn-primary w-100 py-2 fw-semibold">
                Login
            </button>
        </form>

        <div class="text-center mt-4">
            <p class="text-secondary small">&copy; <?php echo date('Y'); ?> E-Arsip Digital</p>
        </div>
    </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 shadow">

            <div class="modal-header">
                <h5 class="modal-title">Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="forgot-password.php" method="POST">
                <div class="modal-body">
                    <p class="text-secondary">Masukkan email Anda untuk menerima link reset password.</p>

                    <div class="form-floating">
                        <input type="email" name="email" class="form-control" id="resetEmail" placeholder="Email" required>
                        <label for="resetEmail">Email</label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" type="submit" name="reset">Kirim Link</button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
setTimeout(function () {
    const alert = document.getElementById('autoAlert');
    if (alert) {
        let bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
        bsAlert.close();
    }
}, 3000);
</script>

</body>
</html>
