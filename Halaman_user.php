<?php
// Mulai session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'include/config.php';
include 'include/head.php';

// Cek apakah user sudah login
if (!isset($_SESSION['admin'])) {
    $_SESSION['err'] = '<center>Anda harus login terlebih dahulu!</center>';
    header("Location: index.php");
    exit();
}

// Ambil data user dari session
$id_user = $_SESSION['id_user'];
$query = mysqli_query($config, "SELECT * FROM tbl_user WHERE id_user='$id_user'");
$user_data = mysqli_fetch_array($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPAK PMDTK - User</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --danger-color: #e74c3c;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            overflow-x: auto;
        }

        .app-container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        .sidebar {
            width: var(--sidebar-width) !important;
            min-width: var(--sidebar-width) !important;
            max-width: var(--sidebar-width) !important;
            flex-shrink: 0 !important;
            position: fixed !important;
            top: 0;
            left: 0;
            height: 100vh;
            background-color: var(--primary-color);
            color: white;
            padding: 20px 0;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            z-index: 1000;
            overflow-y: auto;
            transition: var(--transition);
        }
        .app-title {
            padding: 0 20px 20px;
            border-bottom: 1px solid #34495e;
            margin-bottom: 20px;
        }
        .app-title h1 {
            font-size: 20px;
            font-weight: 600;
        }
        .menu-item {
            padding: 12px 20px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
        }
        .menu-item:hover {
            background-color: #34495e;
        }
        .menu-item.active {
            background-color: #3498db;
        }
        .menu-item i {
            margin-right: 10px;
            font-size: 18px;
        }
        .user-info {
            position: absolute;
            bottom: 60px;
            width: 100%;
            padding: 15px 20px;
            border-top: 1px solid #34495e;
            display: flex;
            align-items: center;
        }
        .logout-item {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }
        .logout-item:hover {
            background-color: #e74c3c;
        }
        .logout-item i {
            margin-right: 10px;
            font-size: 18px;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            min-height: 100vh;
            padding: 20px;
            background-color: white;
            margin-top: 0;
            margin-right: 20px;
            margin-bottom: 20px;
            margin-left: calc(var(--sidebar-width) + 20px);
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        /* Mobile menu toggle button */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1001;
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: var(--transition);
        }

        .mobile-menu-toggle:hover {
            background-color: var(--secondary-color);
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 20px;
                width: calc(100% - 40px);
            }

            .mobile-menu-toggle {
                display: block;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin: 10px;
                width: calc(100% - 20px);
                padding: 15px;
            }

            .app-title h1 {
                font-size: 18px;
            }

            .menu-item {
                padding: 10px 15px;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                margin: 5px;
                width: calc(100% - 10px);
                padding: 10px;
            }
        }

        /* High zoom level support */
        @media (min-resolution: 150dpi) and (min-width: 993px) {
            .sidebar {
                width: var(--sidebar-width) !important;
                min-width: var(--sidebar-width) !important;
                flex-shrink: 0 !important;
            }

            .main-content {
                margin-left: calc(var(--sidebar-width) + 20px) !important;
                width: calc(100% - var(--sidebar-width) - 40px) !important;
            }
        }

        /* Prevent layout shifts on zoom */
        .sidebar,
        .main-content {
            will-change: auto;
            transform: translateZ(0);
        }
    </style>
</head>
<body>
    <!-- Mobile menu toggle button -->
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">
        <i class="material-icons">menu</i>
    </button>

    <div class="app-container">
        <div class="sidebar">
            <div class="app-title">
                <h1>SIPAK PMDTK</h1>
            </div>

            <div class="menu-items">
                <a href="?page=beranda" class="menu-item <?= (isset($_GET['page']) && $_GET['page'] == 'beranda') ? 'active' : '' ?>">
                    <i class="material-icons">home</i><span>Beranda</span>
                </a>
                <a href="?page=berita" class="menu-item <?= (isset($_GET['page']) && $_GET['page'] == 'berita') ? 'active' : '' ?>">
                    <i class="material-icons">note_add</i><span>Buat Berita Acara</span>
                </a>
                <a href="?page=aset" class="menu-item <?= (isset($_GET['page']) && $_GET['page'] == 'aset') ? 'active' : '' ?>">
                    <i class="material-icons">list_alt</i><span>Daftar Pemakai Aset</span>
                </a>
                <a href="?page=kendaraan" class="menu-item <?= (isset($_GET['page']) && $_GET['page'] == 'kendaraan') ? 'active' : '' ?>">
                    <i class="material-icons">directions_car</i><span>Kendaraan Dinas</span>
                </a>

                <?php if ($_SESSION['admin'] == 1 || $_SESSION['admin'] == 2): ?>
                <a href="?page=usr" class="menu-item <?= (isset($_GET['page']) && $_GET['page'] == 'usr') ? 'active' : '' ?>">
                    <i class="material-icons">people</i><span>Manajemen User</span>
                </a>
                <?php endif; ?>

                <?php if ($_SESSION['admin'] == 1): ?>
                <a href="?page=png" class="menu-item <?= (isset($_GET['page']) && $_GET['page'] == 'png') ? 'active' : '' ?>">
                    <i class="material-icons">settings</i><span>Pengaturan</span>
                </a>
                <?php endif; ?>

                <a href="?page=profil" class="menu-item <?= (isset($_GET['page']) && $_GET['page'] == 'profil') ? 'active' : '' ?>">
                    <i class="material-icons">account_circle</i><span>Profil Saya</span>
                </a>
            </div>

            <div class="user-info">
                <i class="material-icons">account_circle</i>
                <span><?= htmlspecialchars($user_data['nama']) ?></span>
            </div>

            <a href="logout.php" class="logout-item">
                <i class="material-icons">exit_to_app</i><span>Logout</span>
            </a>
        </div>

        <div class="main-content">
            <?php
            $page = $_GET['page'] ?? 'beranda';

            switch($page){
                case 'beranda':
                    include 'beranda_user.php';
                    break;
                case 'berita':
                    if(isset($_GET['act'])){
                        $act = $_GET['act'];
                        switch($act){
                            case 'add':
                                include 'buat_berita_acara.php';
                                break;
                            case 'edit':
                                include 'edit_berita_acara.php';
                                break;
                            case 'del':
                                include 'hapus_berita_acara.php';
                                break;
                            default:
                                include 'buat_berita_acara.php';
                        }
                    } else {
                        include 'buat_berita_acara.php';
                    }
                    break;
                case 'aset':
                    include 'daftar_pemakai_aset.php';
                    break;
                case 'kendaraan':
                    if(isset($_GET['act'])){
                        $act = $_GET['act'];
                        switch($act){
                            case 'add':
                                include 'tambah_kendaraan.php';
                                break;
                            case 'edit':
                                include 'edit_kendaraan.php';
                                break;
                            case 'del':
                                include 'hapus_kendaraan.php';
                                break;
                            default:
                                include 'data_pemegang_kendaraan.php';
                        }
                    } else {
                        include 'data_pemegang_kendaraan.php';
                    }
                    break;
                case 'profil':
                    include 'profil.php';
                    break;
                case 'usr':
                    if($_SESSION['admin'] == 1 || $_SESSION['admin'] == 2) {
                        include 'user.php';
                    } else {
                        echo '<script>alert("Hanya admin yang bisa mengakses manajemen user!"); window.location.href="?page=beranda";</script>';
                    }
                    break;
                case 'png':
                    if($_SESSION['admin'] == 1) {
                        include 'pengaturan.php';
                    } else {
                        echo '<script>alert("Hanya super admin yang bisa mengakses pengaturan!"); window.location.href="?page=beranda";</script>';
                    }
                    break;
                default:
                    include 'beranda_user.php';
            }
            ?>
        </div>
    </div>

    <?php include 'include/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle window resize to ensure proper layout
        window.addEventListener('resize', function() {
            adjustLayout();
        });

        // Handle zoom changes
        window.addEventListener('orientationchange', function() {
            setTimeout(adjustLayout, 100);
        });

        // Initial layout adjustment
        adjustLayout();
    });

    // Function to toggle sidebar on mobile
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.toggle('show');
        }
    }

    // Function to adjust layout based on screen size and zoom
    function adjustLayout() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');

        if (window.innerWidth <= 992) {
            // Mobile layout
            if (sidebar) {
                sidebar.style.position = 'fixed';
                sidebar.style.transform = sidebar.classList.contains('show') ? 'translateX(0)' : 'translateX(-100%)';
            }
            if (mainContent) {
                mainContent.style.marginLeft = '20px';
                mainContent.style.width = 'calc(100% - 40px)';
            }
        } else {
            // Desktop layout
            if (sidebar) {
                sidebar.style.position = 'fixed';
                sidebar.style.transform = 'translateX(0)';
                sidebar.classList.remove('show');
            }
            if (mainContent) {
                mainContent.style.marginLeft = 'calc(250px + 20px)';
                mainContent.style.width = 'calc(100% - 250px - 40px)';
            }
        }
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        const sidebar = document.querySelector('.sidebar');
        const toggleBtn = document.querySelector('.mobile-menu-toggle');

        if (window.innerWidth <= 992 && sidebar && sidebar.classList.contains('show')) {
            if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        }
    });
    </script>
</body>
</html>