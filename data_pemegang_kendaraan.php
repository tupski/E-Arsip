<?php
//session
if(!isset($_SESSION['admin'])){
    $_SESSION['err'] = '<center>Anda harus login terlebih dahulu!</center>';

    // Check if headers have already been sent (when included in another page)
    if (headers_sent()) {
        echo '<script>alert("Anda harus login terlebih dahulu!"); window.location.href="./";</script>';
        return;
    } else {
        header("Location: ./");
        die();
    }
} else {

    if(isset($_REQUEST['act'])){
        $act = $_REQUEST['act'];
        switch ($act) {
            case 'add': 
                // Allow both admin and regular users to add data
                include "tambah_kendaraan.php"; 
                break;
            case 'edit':
                // Allow both admin and regular users to edit their own data
                include "edit_kendaraan.php";
                break;
            case 'del': 
                if($_SESSION['admin'] == 1 || $_SESSION['admin'] == 2){
                    include "hapus_kendaraan.php"; 
                } else {
                    echo '<script language="javascript">
                            window.alert("ERROR! Anda tidak memiliki hak akses untuk menghapus data");
                            window.location.href="?page=kendaraan";
                          </script>';
                }
                break;
        }
    } else {

        // Pagination
        $limit = 10;
        $pg = @$_GET['pg'];
        $curr = empty($pg) ? 0 : ($pg - 1) * $limit;

        // Pencarian
        $keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($config, $_GET['keyword']) : '';
        $sql = "SELECT * FROM tbl_kendaraan";
        
        // Filter untuk user biasa
        if($_SESSION['admin'] == 0){
            $id_user = $_SESSION['id_user'];
            $sql .= " WHERE id_user='$id_user'";
            if(!empty($keyword)) {
                $sql .= " AND (jenis_kendaraan LIKE '%$keyword%' OR merk_type LIKE '%$keyword%' OR no_polisi LIKE '%$keyword%' OR no_mesin LIKE '%$keyword%' OR no_rangka LIKE '%$keyword%')";
            }
        } else {
            if(!empty($keyword)) {
                $sql .= " WHERE jenis_kendaraan LIKE '%$keyword%' OR merk_type LIKE '%$keyword%' OR no_polisi LIKE '%$keyword%' OR no_mesin LIKE '%$keyword%' OR no_rangka LIKE '%$keyword%'";
            }
        }
        
        $sql .= " ORDER BY jenis_kendaraan ASC LIMIT $curr, $limit";
        $query = mysqli_query($config, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pemegang Kendaraan Dinas</title>
    <style>

        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #34495e;
            --danger-color: #e74c3c;            
            --success-color: #34495e;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --border-radius: 6px;
            --box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            --transition: all 0.2s ease;
            --sidebar-width: 250px;
        }

        /* Reset and base styles */
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            overflow-x: auto;
        }

        /* Ensure sidebar stays fixed and doesn't shrink on zoom */
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
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            z-index: 1000;
            overflow-y: auto;
        }

        /* Main content area adjustments */
        .main-content {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            min-height: 100vh;
            padding: 0;
            background-color: #f0f2f5;
        }

        .page-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: 100%;
            padding: 0;
        }

        .form-container {
            width: 100%;
            max-width: 100%;
            padding: 20px;
            box-sizing: border-box;
            flex: 1;
        }

        .form-header {
            background-color: var(--primary-color);
            color: white;
            padding: 15px 20px;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            margin-bottom: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form-header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 20px;
            width: 100%;
        }

        .form-body {
            padding: 20px;
        }

        .alert {
            padding: 12px 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .table-container {
            overflow-x: auto;
            margin-bottom: 20px;
            -webkit-overflow-scrolling: touch;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            background: white;
        }

        table {
            width: 100%;
            min-width: 1000px;
            border-collapse: collapse;
            margin-bottom: 0;
            font-size: 14px;
        }

        th, td {
            padding: 12px 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 150px;
        }

        th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        td:first-child,
        th:first-child {
            max-width: 50px;
            text-align: center;
        }

        td:last-child,
        th:last-child {
            max-width: 120px;
            text-align: center;
        }

        tr:hover {
            background-color: rgba(72, 149, 239, 0.1);
        }

        /* Tooltip for truncated text */
        td[title] {
            cursor: help;
        }

        .btn {
            padding: 8px 12px;
            border-radius: var(--border-radius);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin: 2px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .btn-success {
            background-color: var(--success-color);
            color: white;
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .search-container {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .search-input {
            flex: 1;
            max-width: 400px;
            position: relative;
        }

        .search-input i {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #777;
            pointer-events: none;
        }

        .search-input input {
            padding: 10px 35px 10px 15px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            height: 40px;
            font-size: 14px;
            transition: var(--transition);
        }

        .search-input input:focus {
            border-color: var(--accent-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(72, 149, 239, 0.2);
        }

        .search-actions {
            display: flex;
            gap: 10px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }

        .pagination li {
            margin: 0 5px;
        }

        .pagination li a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: white;
            color: var(--primary-color);
            border: 1px solid #ddd;
            transition: var(--transition);
        }

        .pagination li.active a {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .pagination li.disabled a {
            color: #ccc;
            cursor: not-allowed;
        }

        .pagination li a:hover:not(.disabled) {
            background-color: #f0f0f0;
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
            }

            .mobile-menu-toggle {
                display: block;
                position: fixed;
                top: 15px;
                left: 15px;
                z-index: 1001;
                background-color: var(--primary-color);
                color: white;
                border: none;
                padding: 10px;
                border-radius: var(--border-radius);
                cursor: pointer;
            }
        }

        @media (min-width: 993px) {
            .mobile-menu-toggle {
                display: none;
            }
        }

        @media (max-width: 768px) {
            th, td {
                padding: 8px 6px;
                font-size: 12px;
            }

            .btn {
                padding: 6px 8px;
                font-size: 11px;
            }

            .search-container {
                flex-direction: column;
                gap: 10px;
            }

            .search-input {
                max-width: 100%;
            }

            .search-actions {
                justify-content: flex-start;
                flex-wrap: wrap;
            }

            .form-header h1 {
                font-size: 16px;
            }

            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            table {
                min-width: 800px;
            }
        }

        @media (max-width: 480px) {
            .form-container {
                padding: 10px;
            }

            th, td {
                padding: 6px 4px;
                font-size: 11px;
            }

            .btn {
                padding: 4px 6px;
                font-size: 10px;
            }

            .form-header {
                padding: 10px 15px;
            }

            .form-header h1 {
                font-size: 14px;
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

        /* Additional zoom stability */
        @supports (zoom: 1) {
            .sidebar {
                zoom: 1;
            }
        }

        /* Ensure proper scrolling on all devices */
        .table-container::-webkit-scrollbar {
            height: 8px;
        }

        .table-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        .table-container::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }

        /* Improve button responsiveness */
        .btn {
            min-width: 36px;
            min-height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Better form responsiveness */
        .search-input input {
            min-width: 200px;
        }

        /* Ensure proper spacing on all screen sizes */
        .form-body {
            padding: 15px 20px;
        }

        @media (max-width: 480px) {
            .form-body {
                padding: 10px 15px;
            }

            .search-input input {
                min-width: 150px;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile menu toggle button -->
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">
        <i class="material-icons">menu</i>
    </button>

    <div class="page-container">
        <div class="form-container">
            <div class="form-card">
                <div class="form-header">
                    <h1><i class="material-icons">directions_car</i> DATA PEMEGANG KENDARAAN DINAS</h1>
                    <!-- Allow both admin and regular users to add data -->
                    <a href="?page=kendaraan&act=add" class="btn btn-primary">
                        <i class="material-icons">add</i> Tambah Data
                    </a>
                </div>
                
                <div class="form-body">
                    <?php
                    // Notifikasi
                    foreach (['succAdd', 'succEdit', 'succDel', 'errEmpty', 'errQ'] as $notif) {
                        if(isset($_SESSION[$notif])){
                            $color = (strpos($notif, 'err') === 0) ? 'danger' : 'success';
                            echo '<div class="alert alert-'.$color.'">
                                    <i class="material-icons">'.($color=='danger'?'error':'done').'</i>
                                    '.$_SESSION[$notif].'
                                  </div>';
                            unset($_SESSION[$notif]);
                        }
                    }
                    ?>
                    
                    <div class="search-container">
                        <form method="get" action="" class="search-input">
                            <input type="hidden" name="page" value="kendaraan">
                            <input type="text" name="keyword" placeholder="Cari kendaraan..." value="<?php echo $keyword; ?>">
                            <i class="material-icons">search</i>
                        </form>
                        <div class="search-actions">
                            <button type="submit" form="search-form" class="btn btn-primary">
                                <i class="material-icons">search</i> Cari
                            </button>
                            <a href="?page=kendaraan" class="btn btn-secondary">
                                <i class="material-icons">refresh</i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="table-container">
                        <table id="tbl">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Jenis</th>
                                    <th>Merk/Type</th>
                                    <th>Tahun</th>
                                    <th>No. Polisi</th>
                                    <th>Warna</th>
                                    <th>No. Mesin</th>
                                    <th>No. Rangka</th>
                                    <th>Penanggung Jawab</th>
                                    <th>Pemakai</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if(mysqli_num_rows($query) > 0){
                                    $no = $curr + 1;
                                    while($row = mysqli_fetch_array($query)){
                                        echo '
                                        <tr>
                                            <td>'.$no++.'</td>
                                            <td title="'.htmlspecialchars($row['jenis_kendaraan']).'">'.htmlspecialchars($row['jenis_kendaraan']).'</td>
                                            <td title="'.htmlspecialchars($row['merk_type']).'">'.htmlspecialchars($row['merk_type']).'</td>
                                            <td>'.$row['tahun'].'</td>
                                            <td title="'.htmlspecialchars($row['no_polisi']).'">'.htmlspecialchars($row['no_polisi']).'</td>
                                            <td title="'.htmlspecialchars($row['warna']).'">'.htmlspecialchars($row['warna']).'</td>
                                            <td title="'.htmlspecialchars($row['no_mesin']).'">'.htmlspecialchars($row['no_mesin']).'</td>
                                            <td title="'.htmlspecialchars($row['no_rangka']).'">'.htmlspecialchars($row['no_rangka']).'</td>
                                            <td title="'.htmlspecialchars($row['penanggung_jawab']).'">'.htmlspecialchars($row['penanggung_jawab']).'</td>
                                            <td title="'.htmlspecialchars($row['pemakai']).'">'.htmlspecialchars($row['pemakai']).'</td>
                                            <td title="'.htmlspecialchars($row['keterangan']).'">'.htmlspecialchars($row['keterangan']).'</td>
                                            <td>
                                                <div style="display: flex; gap: 5px; justify-content: center;">';
                                        
                                        // Tombol aksi
                                        if($_SESSION['admin'] == 1 || $_SESSION['admin'] == 2){
                                            // Admin can edit and delete all records
                                            echo '<a href="?page=kendaraan&act=edit&id='.$row['id_kendaraan'].'" class="btn btn-success">
                                                    <i class="material-icons" style="font-size: 16px;">edit</i>
                                                  </a>
                                                  <a href="?page=kendaraan&act=del&id='.$row['id_kendaraan'].'" onclick="return confirm(\'Yakin ingin menghapus data ini?\')" class="btn btn-danger">
                                                    <i class="material-icons" style="font-size: 16px;">delete</i>
                                                  </a>';
                                        } else if($row['id_user'] == $_SESSION['id_user']) {
                                            // Regular users can edit their own records
                                            echo '<a href="?page=kendaraan&act=edit&id='.$row['id_kendaraan'].'" class="btn btn-success">
                                                    <i class="material-icons" style="font-size: 16px;">edit</i>
                                                  </a>';
                                        } else {
                                            // Regular users can only view other users' records
                                            echo '<a href="?page=kendaraan&act=edit&id='.$row['id_kendaraan'].'" class="btn btn-primary">
                                                    <i class="material-icons" style="font-size: 16px;">visibility</i>
                                                  </a>';
                                        }
                                        
                                        echo '</div>
                                            </td>
                                        </tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="12"><center>Tidak ada data untuk ditampilkan</center></td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php
                    // Pagination
                    $sql_total = "SELECT * FROM tbl_kendaraan";
                    if($_SESSION['admin'] == 0){
                        $id_user = $_SESSION['id_user'];
                        $sql_total .= " WHERE id_user='$id_user'";
                        if(!empty($keyword)) {
                            $sql_total .= " AND (jenis_kendaraan LIKE '%$keyword%' OR merk_type LIKE '%$keyword%' OR no_polisi LIKE '%$keyword%' OR no_mesin LIKE '%$keyword%' OR no_rangka LIKE '%$keyword%')";
                        }
                    } else {
                        if(!empty($keyword)) {
                            $sql_total .= " WHERE jenis_kendaraan LIKE '%$keyword%' OR merk_type LIKE '%$keyword%' OR no_polisi LIKE '%$keyword%' OR no_mesin LIKE '%$keyword%' OR no_rangka LIKE '%$keyword%'";
                        }
                    }
                    
                    $query = mysqli_query($config, $sql_total);
                    $cdata = mysqli_num_rows($query);
                    $cpg = ceil($cdata/$limit);
                    
                    if($cdata > $limit):
                    ?>
                    <ul class="pagination">
                        <?php if($pg > 1): ?>
                            <li><a href="?page=kendaraan&pg=1&keyword=<?php echo $keyword; ?>"><i class="material-icons">first_page</i></a></li>
                            <li><a href="?page=kendaraan&pg=<?php echo $pg-1 ?>&keyword=<?php echo $keyword; ?>"><i class="material-icons">chevron_left</i></a></li>
                        <?php else: ?>
                            <li class="disabled"><a href="#"><i class="material-icons">first_page</i></a></li>
                            <li class="disabled"><a href="#"><i class="material-icons">chevron_left</i></a></li>
                        <?php endif; ?>
                        
                        <?php for($i=1; $i <= $cpg; $i++): ?>
                            <li class="<?php echo ($i == $pg) ? 'active' : '' ?>">
                                <a href="?page=kendaraan&pg=<?php echo $i ?>&keyword=<?php echo $keyword; ?>"><?php echo $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if($pg < $cpg): ?>
                            <li><a href="?page=kendaraan&pg=<?php echo $pg+1 ?>&keyword=<?php echo $keyword; ?>"><i class="material-icons">chevron_right</i></a></li>
                            <li><a href="?page=kendaraan&pg=<?php echo $cpg ?>&keyword=<?php echo $keyword; ?>"><i class="material-icons">last_page</i></a></li>
                        <?php else: ?>
                            <li class="disabled"><a href="#"><i class="material-icons">chevron_right</i></a></li>
                            <li class="disabled"><a href="#"><i class="material-icons">last_page</i></a></li>
                        <?php endif; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fungsi pencarian
        document.querySelector('input[name="keyword"]').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                document.querySelector('form').submit();
            }
        });

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

<?php
    }
}
?>