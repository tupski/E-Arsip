<?php
// session check
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    die();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Surat</title>
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
            bottom: 20px;
            width: 250px;
            padding: 10px 20px;
            border-top: 1px solid #34495e;
            display: flex;
            align-items: center;
        }
        
        .user-info i {
            margin-right: 10px;
            font-size: 20px;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            min-height: 100vh;
            padding: 20px;
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
                margin-left: 0;
                width: 100%;
                padding: 15px;
            }

            .mobile-menu-toggle {
                display: block;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 10px;
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
                padding: 5px;
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
                margin-left: var(--sidebar-width) !important;
                width: calc(100% - var(--sidebar-width)) !important;
            }
        }

        /* Prevent layout shifts on zoom */
        .sidebar,
        .main-content {
            will-change: auto;
            transform: translateZ(0);
        }
    </style>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <!-- Mobile menu toggle button -->
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">
        <i class="material-icons">menu</i>
    </button>

    <div class="app-container">
        <!-- Sidebar Menu -->
        <div class="sidebar">
            <div class="app-title">
                <h1>Aplikasi Surat</h1>
            </div>
            
            <div class="menu-items">
                <a href="?page=beranda" class="menu-item">
                    <i class="material-icons">home</i>
                    <span>Beranda</span>
                </a>
                
                <a href="?page=berita" class="menu-item">
                    <i class="material-icons">note_add</i>
                    <span>Buat Berita Acara</span>
                </a>
                
                <a href="?page=aset" class="menu-item">
                    <i class="material-icons">list_alt</i>
                    <span>Daftar Pemakai Aset</span>
                </a>
                
                <a href="?page=kendaraan" class="menu-item">
                    <i class="material-icons">directions_car</i>
                    <span>Kendaraan Dinas</span>
                </a>
                
                <a href="?page=user" class="menu-item">
                    <i class="material-icons">people</i>
                    <span>Manajemen User</span>
                </a>
            </div>
            
            <div class="user-info">
                <i class="material-icons">account_circle</i>
                <span>Administrator</span>
            </div>
        </div>
        
        <!-- Main Content Area -->
        <div class="main-content">
            <?php 
            // Konten halaman akan dimuat di sini
            if(isset($_GET['page'])) {
                $page = $_GET['page'];
                include($page.'.php');
            } else {
                include('beranda.php');
            }
            ?>
        </div>
    </div>

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
                mainContent.style.marginLeft = '0';
                mainContent.style.width = '100%';
            }
        } else {
            // Desktop layout
            if (sidebar) {
                sidebar.style.position = 'fixed';
                sidebar.style.transform = 'translateX(0)';
                sidebar.classList.remove('show');
            }
            if (mainContent) {
                mainContent.style.marginLeft = '250px';
                mainContent.style.width = 'calc(100% - 250px)';
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