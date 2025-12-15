<?php
// layout/navbar_mobile.php
?>
<!-- Mobile header -->
<div class="d-md-none w-100 bg-white shadow-sm">
    <div class="d-flex align-items-center justify-content-between p-3">
        <div class="fw-bold">
            <i class="fas fa-archive me-2"></i>E-Arsip
        </div>
        <button class="btn btn-outline-primary"
                data-bs-toggle="offcanvas"
                data-bs-target="#mobileSidebar">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</div>

<!-- Offcanvas mobile sidebar -->
<div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="mobileSidebar">

    <div class="offcanvas-header bg-primary text-white">
        <h5 class="offcanvas-title">Menu</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <nav class="mb-3">
            <a href="dashboard.php" class="d-flex align-items-center p-2 mb-1 rounded text-decoration-none text-dark">
                <i class="fas fa-home me-2"></i> Dashboard
            </a>
            <a href="kategori.php" class="d-flex align-items-center p-2 mb-1 rounded text-decoration-none text-dark">
                <i class="fas fa-folder me-2"></i> Data Kategori
            </a>
            <a href="arsip.php" class="d-flex align-items-center p-2 mb-1 rounded text-decoration-none text-dark">
                <i class="fas fa-file-alt me-2"></i> Data Arsip
            </a>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="user.php" class="d-flex align-items-center p-2 mb-1 rounded text-decoration-none text-dark">
                    <i class="fas fa-users me-2"></i> Data User
                </a>
            <?php endif; ?>

            <div class="border-top my-3"></div>

            <a href="auth.php?logout=true" class="d-flex align-items-center p-2 rounded text-decoration-none text-danger">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </nav>

        <div class="mt-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center"
                     style="width:36px; height:36px; font-weight:600;">
                    <?= isset($_SESSION['nama_lengkap']) ? strtoupper(substr($_SESSION['nama_lengkap'],0,1)) : 'U' ?>
                </div>
                <div class="ms-2">
                    <div class="small fw-semibold"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User') ?></div>
                    <div class="text-muted small text-capitalize"><?= htmlspecialchars($_SESSION['role'] ?? '') ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
