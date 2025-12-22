<?php
session_start();

// 1. Cek Login (Security)
if (!isset($_SESSION['status']) || $_SESSION['status'] !== "login") {
    header("Location: index.php?alert=belum_login");
    exit();
}

// 2. Koneksi (Jika nanti dibutuhkan untuk ambil data)
include '../konektor.php';

// 3. Set Judul Halaman
$title = "Unit Pengolah";

// 4. Include Header & CSS
include __DIR__ . '/../layout/header.php';
?>

<div class="d-flex">

    <?php include __DIR__ . '/../layout/sidebar.php'; ?>

    <main class="flex-grow-1 p-4" style="background-color: #f3f4f6; min-height: 100vh;">
        <div class="container-fluid">
            <div class="row justify-content-center mt-5">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-sm border-0 rounded-3 text-center p-5">
                        <div class="card-body">
                            
                            <div class="mb-4">
                                <span class="fa-stack fa-4x">
                                    <i class="fas fa-circle fa-stack-2x text-light"></i>
                                    <i class="fas fa-tools fa-stack-1x text-secondary"></i>
                                </span>
                            </div>

                            <h3 class="fw-bold text-dark">Fitur Belum Tersedia</h3>
                            <p class="text-muted mb-4">
                                Halaman <strong>Unit Pengolah</strong> sedang dalam tahap pengembangan.<br>
                                Silakan kembali lagi nanti untuk mengakses fitur ini.
                            </p>

                            <div class="d-flex justify-content-center gap-2">
                                <a href="../dashboard.php" class="btn btn-primary px-4">
                                    <i class="fas fa-home me-2"></i>Ke Dashboard
                                </a>
                                <button onclick="history.back()" class="btn btn-outline-secondary px-4">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>