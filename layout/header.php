<?php
// 1. LOGIKA: Cek Halaman untuk Menampilkan Search Bar
$file_sekarang = basename($_SERVER['PHP_SELF']);
$halaman_arsip = [
    'dashboard.php', 
    'arsip-aktif.php', 
    'arsip-inaktif.php', 
    'arsip-permanen.php', 
    'arsip-vital.php'
];
$tampil_search = in_array($file_sekarang, $halaman_arsip);

// 2. Logic Kategori Aktif untuk Dropdown
$kategori_aktif = 'semua';
if ($file_sekarang == 'arsip-aktif.php') $kategori_aktif = 'aktif';
elseif ($file_sekarang == 'arsip-inaktif.php') $kategori_aktif = 'inaktif';
elseif ($file_sekarang == 'arsip-vital.php') $kategori_aktif = 'vital';
elseif ($file_sekarang == 'arsip-permanen.php') $kategori_aktif = 'permanen';

$current_search = htmlspecialchars($_GET['search'] ?? '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>E-Arsip Terpadu</title>

    <link href="/peminjaman-arsip/vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6; /* Abu-abu sangat muda untuk body */
            font-size: 0.9rem;
        }

        /* --- NAVBAR PUTIH --- */
        .navbar-white {
            background-color: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            padding: 0.7rem 1rem;
        }

        /* Search Group Styling */
        .search-group {
            background-color: #f3f4f6; /* Abu-abu muda */
            border: 1px solid #e5e7eb;
            border-radius: 8px; /* Sudut tumpul */
            display: flex;
            align-items: center;
            width: 100%;
            max-width: 600px;
            padding: 2px;
            transition: all 0.2s;
        }
        .search-group:focus-within {
            background-color: #fff;
            border-color: #4f46e5; /* Biru saat diklik */
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .search-select {
            background-color: transparent;
            border: none;
            font-size: 0.85rem;
            font-weight: 500;
            color: #4b5563;
            padding-left: 10px;
            border-right: 1px solid #d1d5db;
            max-width: 150px;
            cursor: pointer;
        }
        .search-select:focus {
            box-shadow: none;
        }

        .search-input {
            background-color: transparent;
            border: none;
            padding: 6px 12px;
            width: 100%;
            color: #1f2937;
        }
        .search-input:focus {
            background-color: transparent;
            box-shadow: none;
        }

        .search-btn {
            border: none;
            background: transparent;
            color: #6b7280;
            padding: 0 15px;
        }
        .search-btn:hover {
            color: #4f46e5;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-white sticky-top shadow-sm">
    <div class="container-fluid">
        
        <div class="d-flex align-items-center">
    
    <button class="btn btn-light text-secondary me-3 border-0" type="button" id="btnToggleSidebar">
        <i class="fas fa-bars fa-lg"></i>
    </button>
    
    <a class="navbar-brand fw-bold text-primary tracking-wide fs-5" href="#">
        <i class="fas fa-archive me-2"></i>E-ARSIP
    </a>
</div>

        <?php if ($tampil_search): ?>
            <div class="mx-auto flex-grow-1 d-flex justify-content-center px-3 d-none d-lg-flex">
                <form id="globalSearchForm" method="GET" class="search-group">
                    
                    <select id="kategoriArsip" class="form-select search-select">
                        <option value="" disabled selected hidden>Kategori</option>

                        <?php if ($kategori_aktif !== 'semua'): ?>
                            <option value="semua">Semua</option>
                        <?php endif; ?>

                        <?php if ($kategori_aktif !== 'aktif'): ?>
                            <option value="aktif">Arsip Aktif</option>
                        <?php endif; ?>

                        <?php if ($kategori_aktif !== 'inaktif'): ?>
                            <option value="inaktif">Arsip Inaktif</option>
                        <?php endif; ?>

                        <?php if ($kategori_aktif !== 'vital'): ?>
                            <option value="vital">Arsip Vital</option>
                        <?php endif; ?>

                        <?php if ($kategori_aktif !== 'permanen'): ?>
                            <option value="permanen">Arsip Permanen</option>
                        <?php endif; ?>
                    </select>

                    <input type="text" name="search" class="form-control search-input" 
                           placeholder="Cari arsip disini..." 
                           value="<?= $current_search ?>">

                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <div class="dropdown ms-auto">
            <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle p-1 rounded hover-bg-light" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="d-none d-md-block text-end me-2" style="line-height: 1.2;">
                    <span class="d-block fw-bold small"><?= $_SESSION['nama_lengkap'] ?? 'User' ?></span>
                    <span class="d-block text-muted" style="font-size: 0.7rem;">Admin</span>
                </div>
                <img src="https://ui-avatars.com/api/?name=<?= $_SESSION['nama_lengkap'] ?? 'User' ?>&background=0D8ABC&color=fff" 
                     alt="" width="38" height="38" class="rounded-circle">
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2 text-muted"></i> Profil Saya</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="/peminjaman-arsip/index.php?logout=true"><i class="fas fa-sign-out-alt me-2"></i> Keluar</a></li>
            </ul>
        </div>

    </div>
</nav>

<?php if ($tampil_search): ?>
<div class="d-lg-none bg-white p-2 border-bottom">
    <div class="input-group input-group-sm">
        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search"></i></span>
        <input type="text" class="form-control bg-light border-start-0" placeholder="Gunakan Laptop untuk filter lengkap..." disabled>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById('globalSearchForm');
    const select = document.getElementById('kategoriArsip');

    if(form && select) {
        // GANTI INI SESUAI FOLDER PROYEK ANDA
        const baseURL = "/peminjaman-arsip/arsip"; 

        form.addEventListener('submit', function(e) {
            e.preventDefault(); 
            const keyword = form.querySelector('input[name="search"]').value;
            const kategori = select.value;
            
            // Ambil URL halaman saat ini untuk default (jika user tidak memilih kategori)
            let currentPath = window.location.pathname;
            let targetURL = "";

            if (kategori === "") {
                // Jika user tidak memilih kategori (tetap di tulisan "Kategori")
                // Maka cari di halaman ini saja
                targetURL = currentPath;
            } else {
                // Jika user memilih kategori lain
                switch (kategori) {
                    case 'aktif': targetURL = baseURL + "/arsip-aktif/arsip-aktif.php"; break;
                    case 'inaktif': targetURL = baseURL + "/arsip-inaktif/arsip-inaktif.php"; break;
                    case 'vital': targetURL = baseURL + "/arsip-vital/arsip-vital.php"; break;
                    case 'permanen': targetURL = baseURL + "/arsip-permanen/arsip-permanen.php"; break;
                    case 'semua': 
                        // Asumsi ada halaman dashboard atau pencarian global
                        // Sesuaikan dengan halaman 'Semua' Anda
                        targetURL = "/peminjaman-arsip/dashboard.php"; 
                        break;
                    default: targetURL = baseURL + "/arsip-aktif/arsip-aktif.php"; break;
                }
            }

            // Redirect
            window.location.href = targetURL + "?search=" + encodeURIComponent(keyword);
        });
    }
});
</script>