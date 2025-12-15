<?php
// Pastikan session sudah start di file induk, atau uncomment baris ini jika perlu
// session_start(); 

$base_url = "http://localhost/peminjaman-arsip/";

$active = function($file) {
    // Logic sederhana untuk active state
    return strpos($_SERVER['PHP_SELF'], $file) !== false 
        ? 'active' 
        : '';
};

$current_file = basename($_SERVER['PHP_SELF']);

$arsip_active = in_array($current_file, ['arsip-vital.php','arsip_permanen.php','arsip_aktif.php','arsip_inaktif.php']);
$pengawasan_active = in_array($current_file, ['unit_pengolah.php','unit_kearsipan.php']);
$rekap_active = in_array($current_file, ['rekap_arsip.php','rekap_peminjaman.php']);
?>

<style>
    .sidebar {
        width: 260px;
        height: 100vh;
        background: #ffffff;
        border-right: 1px solid #e5e7eb;
    }

    /* --- SATU CLASS UNTUK SEMUA MENU --- */
    .menu-item {
        display: flex;
        align-items: center;
        justify-content: space-between; /* Agar panah ada di kanan */
        width: 100%;
        padding: 10px 12px; /* Padding konsisten */
        font-size: 0.95rem; /* Ukuran font seragam (sekitar 15px) */
        font-weight: 500;
        color: #374151;
        text-decoration: none;
        border-radius: 0.375rem;
        background: transparent;
        border: none;
        transition: all 0.2s;
        margin-bottom: 4px;
        cursor: pointer;
        text-align: left;
    }

    /* Hover State */
    .menu-item:hover {
        background: #EEF2FF;
        color: #4F46E5;
    }

    /* Active State */
    .menu-item.active {
        background: #EEF2FF;
        color: #4F46E5;
        font-weight: 600;
    }

    .menu-item.active i {
        color: #4F46E5;
    }

    /* Icon Style */
    .menu-item .icon-start {
        width: 20px;
        text-align: center;
        margin-right: 0.75rem;
    }

    /* Rotate Icon untuk Dropdown */
    .rotate {
        transform: rotate(90deg);
        transition: transform 0.2s;
    }

    /* Indentasi khusus Submenu agar terlihat menjorok */
    .submenu .menu-item {
        padding-left: 2.5rem; /* Lebih menjorok ke dalam */
        font-size: 0.9rem;    /* Opsional: sedikit lebih kecil atau samakan 0.95rem */
    }
    
    /* Perbaikan link logout */
    .menu-logout {
        color: #dc2626; /* Merah */
    }
    .menu-logout:hover {
        background: #fef2f2;
        color: #b91c1c;
    }

</style>

<aside class="sidebar d-none d-md-flex flex-column">

    <div class="p-3 text-white text-center fw-bold" style="background: #4F46E5;">
        <i class="fas fa-archive me-2"></i> E-Arsip
    </div>

    <nav class="flex-grow-1 overflow-auto p-3">

        <p class="text-uppercase small text-muted fw-semibold mb-2">Menu Utama</p>

        <a href="<?= $base_url ?>/dashboard.php" class="menu-item <?= $active('dashboard.php') ?>">
            <div class="d-flex align-items-center">
                <i class="fas fa-home icon-start"></i>
                <span>Dashboard</span>
            </div>
        </a>

        <button class="menu-item <?= $arsip_active ? 'active' : '' ?>" onclick="toggleDropdown('arsipDinamis')">
            <div class="d-flex align-items-center">
                <i class="fas fa-folder icon-start"></i>
                <span>Penyimpanan Arsip</span>
            </div>
            <i id="icon-arsipDinamis" class="fas fa-chevron-right small"></i>
        </button>

        <div id="arsipDinamis" class="submenu <?= $arsip_active ? '' : 'd-none' ?>">
            <a href="arsip/arsip-vital/arsip-vital.php" class="menu-item <?= $current_file == 'arsip-vital.php' ? 'active' : '' ?>">
                Arsip Vital
            </a>
            <a href="arsip_permanen.php" class="menu-item <?= $current_file == 'arsip-permanen.php' ? 'active' : '' ?>">
                Arsip Permanen
            </a>
            <a href="arsip_aktif.php" class="menu-item <?= $current_file == 'arsip-aktif.php' ? 'active' : '' ?>">
                Arsip Aktif
            </a>
            <a href="arsip_inaktif.php" class="menu-item <?= $current_file == 'arsip-inaktif.php' ? 'active' : '' ?>">
                Arsip Inaktif
            </a>
        </div>


        <button class="menu-item" onclick="toggleDropdown('pengawasan')">
            <div class="d-flex align-items-center">
                <i class="fas fa-eye icon-start"></i>
                <span>Pengawasan</span>
            </div>
            <i id="icon-pengawasan" class="fas fa-chevron-right small"></i>
        </button>

        <div id="pengawasan" class="submenu d-none">
            <a href="unit_pengolah.php" class="menu-item">
                <i class="fas fa-clipboard-check icon-start"></i> Unit Pengolah
            </a>
            <a href="unit_kearsipan.php" class="menu-item">
                <i class="fas fa-clipboard-check icon-start"></i> Unit Kearsipan
            </a>
        </div>

        <button class="menu-item" onclick="toggleDropdown('rekap')">
            <div class="d-flex align-items-center">
                <i class="fas fa-list icon-start"></i>
                <span>Rekap</span>
            </div>
            <i id="icon-rekap" class="fas fa-chevron-right small"></i>
        </button>

        <div id="rekap" class="submenu d-none">
            <a href="rekap_arsip.php" class="menu-item">
                <i class="fas fa-chart-bar icon-start"></i> Rekap Arsip
            </a>
            <a href="rekap_peminjaman.php" class="menu-item">
                <i class="fas fa-chart-bar icon-start"></i> Rekap Peminjaman
            </a>
        </div>
        
        <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <p class="text-uppercase small text-muted fw-semibold mt-4 mb-2">Administrator</p>

            <a href="user.php" class="menu-item <?= $active('user.php') ?>">
                <div class="d-flex align-items-center">
                    <i class="fas fa-users icon-start"></i>
                    <span>Data User</span>
                </div>
            </a>
        <?php endif; ?>


        <div class="border-top my-3"></div>

        <a href="auth.php?logout=true" class="menu-item menu-logout">
            <div class="d-flex align-items-center">
                <i class="fas fa-sign-out-alt icon-start"></i>
                <span>Logout</span>
            </div>
        </a>

    </nav>

    <div class="p-3 border-top bg-light">
        <div class="d-flex align-items-center">
            <div class="rounded-circle text-white d-flex justify-content-center align-items-center"
                style="background:#4F46E5; width:36px; height:36px; font-weight:600;">
                <?= strtoupper(substr($_SESSION['nama_lengkap'] ?? 'U', 0, 1)) ?>
            </div>

            <div class="ms-2">
                <div class="small fw-semibold"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User') ?></div>
                <div class="text-muted small text-capitalize"><?= htmlspecialchars($_SESSION['role'] ?? '') ?></div>
            </div>
        </div>
    </div>
</aside>


<script>
    function toggleDropdown(id) {
        const menu = document.getElementById(id);
        const icon = document.getElementById("icon-" + id);

        menu.classList.toggle("d-none");
        icon.classList.toggle("rotate");
    }
</script>