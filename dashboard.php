<?php
session_start();

if (!isset($_SESSION['status']) || $_SESSION['status'] !== "login") {
    header("Location: ../index.php?alert=belum_login");
    exit();
}

$title = "Dashboard";
include __DIR__ . '/layout/header.php';
?>

<div class="d-flex min-vh-100">

    <?php include __DIR__ . '/layout/sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-grow-1 p-4" style="background-color:#f3f4f6;">
        <div class="container-fluid">

            <!-- Welcome Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h4 class="fw-bold mb-1">
                                Selamat Datang, <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>!
                            </h4>
                            <p class="mb-0 text-muted">
                                Anda login sebagai
                                <span class="fw-semibold text-primary">
                                    <?= htmlspecialchars($_SESSION['role']) ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cards -->
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6>Total Arsip</h6>
                            <p class="display-6 mb-0">123</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6>Kategori</h6>
                            <p class="display-6 mb-0">12</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6>Users</h6>
                            <p class="display-6 mb-0">8</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
