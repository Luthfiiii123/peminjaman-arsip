<?php
// Pastikan session sudah start di file induk
// session_start(); 

$base_url = "http://localhost/peminjaman-arsip/";

// Helper function untuk active state
$active = function($file) {
    return strpos($_SERVER['PHP_SELF'], $file) !== false ? 'active' : '';
};

// Cek file saat ini untuk membuka dropdown otomatis
$current_file = basename($_SERVER['PHP_SELF']);
$arsip_active = in_array($current_file, ['arsip-vital.php','arsip_permanen.php','arsip_aktif.php','arsip_inaktif.php']);
$pengawasan_active = in_array($current_file, ['unit_pengolah.php','unit_kearsipan.php']);
$rekap_active = in_array($current_file, ['rekap_arsip.php','rekap_peminjaman.php']);
?>

<style>
    /* --- SIDEBAR STYLE UTAMA --- */
    .sidebar {
        width: 260px;          /* Lebar dasar */
        min-width: 260px;      /* PENTING: Mencegah sidebar mengecil saat konten kanan lebar */
        height: 100vh;         /* Tinggi sepenuh layar */
        background: #ffffff;
        border-right: 1px solid #e5e7eb;
        
        /* Agar sidebar tetap diam saat konten di scroll (opsional, tapi disarankan) */
        position: sticky; 
        top: 0;
        
        /* Flex setup */
        display: flex;
        flex-direction: column;
        flex-shrink: 0;        /* PENTING: Mencegah sidebar 'gepeng' */
        z-index: 100;          /* Agar selalu di atas jika ada elemen numpuk */
    }

    /* --- MENU ITEM STYLE --- */
    .menu-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 10px 12px;
        font-size: 0.95rem;
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

    .menu-item:hover {
        background: #EEF2FF;
        color: #4F46E5;
    }

    .menu-item.active {
        background: #EEF2FF;
        color: #4F46E5;
        font-weight: 600;
    }

    .menu-item.active i {
        color: #4F46E5;
    }

    .menu-item .icon-start {
        width: 20px;
        text-align: center;
        margin-right: 0.75rem;
    }

    .rotate {
        transform: rotate(90deg);
        transition: transform 0.2s;
    }

    /* Indentasi khusus Submenu */
    .submenu .menu-item {
        padding-left: 2.75rem !important; /* Memberi jarak menjorok ke dalam */
        font-size: 0.9rem;                /* Ukuran font sedikit lebih kecil */
        
        display: flex;                    /* Menggunakan flexbox */
        align-items: center;              /* Vertikal: Rata tengah */
        justify-content: flex-start;      /* Horizontal: Rata KIRI (PENTING) */
        text-align: left;                 /* Memastikan teks rata kiri */
    }

    /* Styling khusus Icon di dalam Submenu */
    .submenu .menu-item i {
        font-size: 0.85rem; 
        width: 20px;        /* Lebar tetap agar teks di sebelahnya lurus rapi */
        text-align: center; /* Icon di tengah kotaknya sendiri */
        margin-right: 12px; /* Jarak antara icon dan teks */
        flex-shrink: 0;     /* Mencegah icon gepeng */
        opacity: 0.75;
    }
    
    .submenu .menu-item:hover i {
        opacity: 1;
    }
    
    .menu-logout {
        color: #dc2626;
    }
    .menu-logout:hover {
        background: #fef2f2;
        color: #b91c1c;
    }
</style>

<aside class="sidebar d-none d-md-flex">

    <div class="p-3 text-white text-center fw-bold d-flex align-items-center justify-content-center" 
         style="background: #4F46E5; min-height: 60px;">
        <i class="fas fa-archive me-2"></i> E-Arsip
    </div>

    <nav class="flex-grow-1 overflow-auto p-3 custom-scrollbar">

        <p class="text-uppercase small text-muted fw-semibold mb-2" style="font-size: 0.75rem;">Menu Utama</p>

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
            <i id="icon-arsipDinamis" class="fas fa-chevron-right small <?= $arsip_active ? 'rotate' : '' ?>"></i>
        </button>

        <div id="arsipDinamis" class="submenu <?= $arsip_active ? '' : 'd-none' ?>">
            
            <a href="<?= $base_url ?>/arsip/arsip-vital/arsip-vital.php" class="menu-item <?= strpos($current_file, 'arsip-vital') !== false ? 'active' : '' ?>">
                <i class="fas fa-file-alt"></i>
                <span>Arsip Vital</span>
            </a>

            <a href="<?= $base_url ?>/arsip_permanen.php" class="menu-item <?= $current_file == 'arsip_permanen.php' ? 'active' : '' ?>">
                <i class="fas fa-file-alt"></i>
                <span>Arsip Permanen</span>
            </a>

            <a href="<?= $base_url ?>/arsip_aktif.php" class="menu-item <?= $current_file == 'arsip_aktif.php' ? 'active' : '' ?>">
                <i class="fas fa-file-alt"></i>
                <span>Arsip Aktif</span>
            </a>

            <a href="<?= $base_url ?>/arsip_inaktif.php" class="menu-item <?= $current_file == 'arsip_inaktif.php' ? 'active' : '' ?>">
                <i class="fas fa-file-alt"></i>
                <span>Arsip Inaktif</span>
            </a>

        </div>

        <button class="menu-item <?= $pengawasan_active ? 'active' : '' ?>" onclick="toggleDropdown('pengawasan')">
            <div class="d-flex align-items-center">
                <i class="fas fa-eye icon-start"></i>
                <span>Pengawasan</span>
            </div>
            <i id="icon-pengawasan" class="fas fa-chevron-right small <?= $pengawasan_active ? 'rotate' : '' ?>"></i>
        </button>

        <div id="pengawasan" class="submenu <?= $pengawasan_active ? '' : 'd-none' ?>">
            <a href="<?= $base_url ?>/unit_pengolah.php" class="menu-item <?= $current_file == 'unit_pengolah.php' ? 'active' : '' ?>">
                <i class="fas fa-clipboard-check icon-start"></i> Unit Pengolah
            </a>
            <a href="<?= $base_url ?>/unit_kearsipan.php" class="menu-item <?= $current_file == 'unit_kearsipan.php' ? 'active' : '' ?>">
                <i class="fas fa-clipboard-check icon-start"></i> Unit Kearsipan
            </a>
        </div>

        <button class="menu-item <?= $rekap_active ? 'active' : '' ?>" onclick="toggleDropdown('rekap')">
            <div class="d-flex align-items-center">
                <i class="fas fa-list icon-start"></i>
                <span>Rekap</span>
            </div>
            <i id="icon-rekap" class="fas fa-chevron-right small <?= $rekap_active ? 'rotate' : '' ?>"></i>
        </button>

        <div id="rekap" class="submenu <?= $rekap_active ? '' : 'd-none' ?>">
            <a href="<?= $base_url ?>/rekap_arsip.php" class="menu-item <?= $current_file == 'rekap_arsip.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-bar icon-start"></i> Rekap Arsip
            </a>
            <a href="<?= $base_url ?>/rekap_peminjaman.php" class="menu-item <?= $current_file == 'rekap_peminjaman.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-bar icon-start"></i> Rekap Peminjaman
            </a>
        </div>
        
        <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <p class="text-uppercase small text-muted fw-semibold mt-4 mb-2" style="font-size: 0.75rem;">Administrator</p>
            <a href="<?= $base_url ?>/user.php" class="menu-item <?= $active('user.php') ?>">
                <div class="d-flex align-items-center">
                    <i class="fas fa-users icon-start"></i>
                    <span>Data User</span>
                </div>
            </a>
        <?php endif; ?>

        <div class="border-top my-3"></div>

        <a href="<?= $base_url ?>/auth.php?logout=true" class="menu-item menu-logout">
            <div class="d-flex align-items-center">
                <i class="fas fa-sign-out-alt icon-start"></i>
                <span>Logout</span>
            </div>
        </a>

    </nav>

    <div class="p-3 border-top bg-light mt-auto">
        <div class="d-flex align-items-center">
            <div class="rounded-circle text-white d-flex justify-content-center align-items-center flex-shrink-0"
                style="background:#4F46E5; width:36px; height:36px; font-weight:600;">
                <?= strtoupper(substr($_SESSION['nama_lengkap'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="ms-2 overflow-hidden">
                <div class="small fw-semibold text-truncate"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User') ?></div>
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