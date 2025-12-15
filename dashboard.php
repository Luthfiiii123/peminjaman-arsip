<?php
session_start();

// 1. Cek Login
if (!isset($_SESSION['status']) || $_SESSION['status'] !== "login") {
    header("Location: ../index.php?alert=belum_login");
    exit();
}

// 2. Koneksi Database (Untuk menghitung jumlah data)
// Sesuaikan path ini dengan struktur folder Anda. 
// Jika dashboard ada di root folder, pakai 'konektor.php'. 
// Jika di dalam folder, pakai '../konektor.php'
include 'konektor.php'; 

$title = "Dashboard";
include __DIR__ . '/layout/header.php';

// 3. Fungsi Sederhana untuk Hitung Data
function hitungData($db, $tabel) {
    // Cek dulu apakah tabel ada agar tidak error fatal
    $cek = mysqli_query($db, "SHOW TABLES LIKE '$tabel'");
    if(mysqli_num_rows($cek) > 0){
        $query = mysqli_query($db, "SELECT COUNT(*) as total FROM $tabel");
        $data = mysqli_fetch_assoc($query);
        return $data['total'];
    }
    return 0; // Jika tabel belum dibuat, return 0
}

// Hitung data real-time
$jml_vital    = hitungData($db, 'arsip_vital');
$jml_aktif    = hitungData($db, 'arsip_aktif'); // Asumsi nama tabel
$jml_inaktif  = hitungData($db, 'arsip_inaktif'); // Asumsi nama tabel
$jml_permanen = hitungData($db, 'arsip_permanen'); // Asumsi nama tabel
$jml_user     = hitungData($db, 'user');

?>

<div class="d-flex">

    <?php include __DIR__ . '/layout/sidebar.php'; ?>

    <main class="flex-grow-1 p-4" style="background-color:#f3f4f6; min-height:100vh;">
        
        <div class="container-fluid">

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow border-0" style="background: linear-gradient(to right, #4F46E5, #818cf8);">
                        <div class="card-body p-4 text-white d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="fw-bold mb-1">
                                    Halo, <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>! 👋
                                </h2>
                                <p class="mb-0 opacity-75">
                                    Selamat datang di Sistem E-Arsip. Anda login sebagai 
                                    <span class="badge bg-warning text-dark text-uppercase"><?= htmlspecialchars($_SESSION['role']) ?></span>
                                </p>
                            </div>
                            <div class="d-none d-md-block">
                                <i class="fas fa-calendar-alt fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <h5 class="fw-bold text-dark mb-3">Ringkasan Data</h5>
            
            <div class="row g-3">
                
                <div class="col-md-6 col-xl-3">
                    <div class="card shadow-sm border-0 border-start border-4 border-warning h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-uppercase small fw-bold text-muted mb-1">Arsip Vital</div>
                                    <div class="h3 fw-bold mb-0 text-dark"><?= $jml_vital ?></div>
                                </div>
                                <div class="bg-warning bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-file-alt fa-2x text-warning"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="arsip/arsip-vital/arsip-vital.php" class="text-decoration-none small text-warning fw-semibold">
                                    Lihat Detail <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card shadow-sm border-0 border-start border-4 border-success h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-uppercase small fw-bold text-muted mb-1">Arsip Aktif</div>
                                    <div class="h3 fw-bold mb-0 text-dark"><?= $jml_aktif ?></div>
                                </div>
                                <div class="bg-success bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-file-contract fa-2x text-success"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="arsip_aktif.php" class="text-decoration-none small text-success fw-semibold">
                                    Lihat Detail <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card shadow-sm border-0 border-start border-4 border-secondary h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-uppercase small fw-bold text-muted mb-1">Arsip Inaktif</div>
                                    <div class="h3 fw-bold mb-0 text-dark"><?= $jml_inaktif ?></div>
                                </div>
                                <div class="bg-secondary bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-archive fa-2x text-secondary"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="arsip_inaktif.php" class="text-decoration-none small text-secondary fw-semibold">
                                    Lihat Detail <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card shadow-sm border-0 border-start border-4 border-danger h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-uppercase small fw-bold text-muted mb-1">Arsip Permanen</div>
                                    <div class="h3 fw-bold mb-0 text-dark"><?= $jml_permanen ?></div>
                                </div>
                                <div class="bg-danger bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-university fa-2x text-danger"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="arsip_permanen.php" class="text-decoration-none small text-danger fw-semibold">
                                    Lihat Detail <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div> <?php if ($_SESSION['role'] === 'admin'): ?>
            <div class="row g-3 mt-2">
                <div class="col-md-6 col-xl-3">
                    <div class="card shadow-sm border-0 border-start border-4 border-primary h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-uppercase small fw-bold text-muted mb-1">Total Users</div>
                                    <div class="h3 fw-bold mb-0 text-dark"><?= $jml_user ?></div>
                                </div>
                                <div class="bg-primary bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-users fa-2x text-primary"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="user.php" class="text-decoration-none small text-primary fw-semibold">
                                    Kelola User <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </main>

</div>

<?php include __DIR__ . '/layout/footer.php'; ?>