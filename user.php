<?php
// Check if admin is logged in
if(!isset($_SESSION['admin']) || $_SESSION['admin'] != 1){
    $_SESSION['err'] = '<center>Anda tidak memiliki akses ke halaman ini!</center>';

    // Check if headers have already been sent (when included in another page)
    if (headers_sent()) {
        echo '<script>alert("Anda tidak memiliki akses ke halaman ini!"); window.location.href="index.php";</script>';
        return;
    } else {
        header("Location: index.php");
        die();
    }
}

// Handle user deletion
if(isset($_GET['act']) && $_GET['act'] == 'del' && isset($_GET['id'])){
    $id = mysqli_real_escape_string($config, $_GET['id']);
    
    // Prevent admin from deleting themselves
    if($id == $_SESSION['id_user']){
        $_SESSION['errDel'] = 'ERROR! Anda tidak dapat menghapus akun sendiri';
    } else {
        $query = mysqli_query($config, "DELETE FROM tbl_user WHERE id_user='$id'");
        
        if($query){
            $_SESSION['succDel'] = 'SUKSES! User berhasil dihapus';
        } else {
            $_SESSION['errDel'] = 'ERROR! Gagal menghapus user';
        }
    }

    // Check if headers have already been sent (when included in another page)
    if (headers_sent()) {
        echo '<script>window.location.href="?page=usr";</script>';
        return;
    } else {
        header("Location: ?page=usr");
        die();
    }
}

// Pagination setup
$limit = 10;
$pg = isset($_GET['pg']) ? (int)$_GET['pg'] : 1;
$curr = ($pg > 1) ? ($pg - 1) * $limit : 0;

// Search functionality
$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($config, $_GET['keyword']) : '';
$sql = "SELECT * FROM tbl_user";

if(!empty($keyword)) {
    $sql .= " WHERE username LIKE '%$keyword%' OR nama LIKE '%$keyword%' OR nip LIKE '%$keyword%'";
}

$sql .= " ORDER BY admin DESC, nama ASC LIMIT $curr, $limit";
$query = mysqli_query($config, $sql);

$count_query = mysqli_query($config, "SELECT COUNT(*) as total FROM tbl_user");
$count_data = mysqli_fetch_array($count_query);
$cdata = $count_data['total'];
$cpg = ceil($cdata/$limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User</title>
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
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
        }

        tr:hover {
            background-color: rgba(72, 149, 239, 0.1);
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

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            color: white !important; /* Tambahkan !important untuk memastikan warna putih */
        }

        .badge-admin {
            background-color: var(--danger-color);
        }

        .badge-supervisor {
            background-color: #ff9800;
        }

        .badge-user {
            background-color: var(--success-color);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            th, td {
                padding: 8px 10px;
                font-size: 13px;
            }
            
            .btn {
                padding: 6px 8px;
                font-size: 12px;
            }
            
            .search-container {
                flex-direction: column;
            }
            
            .search-input {
                max-width: 100%;
            }
            
            .search-actions {
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="form-container">
            <div class="form-card">
                <div class="form-header">
                    <h1><i class="material-icons">people</i> MANAJEMEN USER</h1>
                    <a href="?page=usr&act=add" class="btn btn-primary">
                        <i class="material-icons">add</i> Tambah User
                    </a>
                </div>
                
                <div class="form-body">
                    <?php
                    // Show notifications
                    foreach (['succAdd', 'succEdit', 'succDel', 'errDel'] as $notif) {
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
                            <input type="hidden" name="page" value="usr">
                            <input type="text" name="keyword" placeholder="Cari user..." value="<?php echo $keyword; ?>">
                            <i class="material-icons">search</i>
                        </form>
                        <div class="search-actions">
                            <button type="submit" form="search-form" class="btn btn-primary">
                                <i class="material-icons">search</i> Cari
                            </button>
                            <a href="?page=usr" class="btn btn-secondary">
                                <i class="material-icons">refresh</i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="table-container">
                        <table id="tbl">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Username</th>
                                    <th>Nama</th>
                                    <th>NIP</th>
                                    <th>Role</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if(mysqli_num_rows($query) > 0){
                                    $no = $curr + 1;
                                    while($row = mysqli_fetch_array($query)){
                                        echo '<tr>
                                            <td>'.$no++.'</td>
                                            <td>'.$row['username'].'</td>
                                            <td>'.$row['nama'].'</td>
                                            <td>'.$row['nip'].'</td>
                                            <td>';
                                        
                                        // Display role badge
                                        if($row['admin'] == 1) {
                                            echo '<span class="badge badge-admin">Admin</span>';
                                        } elseif($row['admin'] == 2) {
                                            echo '<span class="badge badge-supervisor">Supervisor</span>';
                                        } else {
                                            echo '<span class="badge badge-user">User</span>';
                                        }
                                        
                                        echo '</td>
                                            <td>
                                                <div style="display: flex; gap: 5px;">
                                                    <a href="?page=usr&act=edit&id='.$row['id_user'].'" class="btn btn-success">
                                                        <i class="material-icons" style="font-size: 16px;">edit</i>
                                                    </a>';
                                        
                                        // Don't show delete button for current user
                                        if($row['id_user'] != $_SESSION['id_user']) {
                                            echo '<a href="?page=usr&act=del&id='.$row['id_user'].'" class="btn btn-danger" onclick="return confirm(\'Yakin ingin menghapus user ini?\')">
                                                    <i class="material-icons" style="font-size: 16px;">delete</i>
                                                  </a>';
                                        }
                                        
                                        echo '</div>
                                            </td>
                                        </tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="6"><center>Tidak ada data user</center></td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if($cdata > $limit): ?>
                    <ul class="pagination">
                        <?php if($pg > 1): ?>
                            <li><a href="?page=usr&pg=1&keyword=<?php echo $keyword; ?>"><i class="material-icons">first_page</i></a></li>
                            <li><a href="?page=usr&pg=<?php echo $pg-1 ?>&keyword=<?php echo $keyword; ?>"><i class="material-icons">chevron_left</i></a></li>
                        <?php else: ?>
                            <li class="disabled"><a href="#"><i class="material-icons">first_page</i></a></li>
                            <li class="disabled"><a href="#"><i class="material-icons">chevron_left</i></a></li>
                        <?php endif; ?>
                        
                        <?php
                        $start = max(1, $pg - 2);
                        $end = min($cpg, $pg + 2);
                        
                        if($start > 1) {
                            echo '<li><a href="?page=usr&pg=1&keyword='.$keyword.'">1</a></li>';
                            if($start > 2) echo '<li class="disabled"><a href="#!">...</a></li>';
                        }
                        
                        for($i = $start; $i <= $end; $i++) {
                            echo ($i == $pg) ? 
                                '<li class="active"><a href="?page=usr&pg='.$i.'&keyword='.$keyword.'">'.$i.'</a></li>' : 
                                '<li><a href="?page=usr&pg='.$i.'&keyword='.$keyword.'">'.$i.'</a></li>';
                        }
                        
                        if($end < $cpg) {
                            if($end < $cpg - 1) echo '<li class="disabled"><a href="#!">...</a></li>';
                            echo '<li><a href="?page=usr&pg='.$cpg.'&keyword='.$keyword.'">'.$cpg.'</a></li>';
                        }
                        ?>
                        
                        <?php if($pg < $cpg): ?>
                            <li><a href="?page=usr&pg=<?php echo $pg+1 ?>&keyword=<?php echo $keyword; ?>"><i class="material-icons">chevron_right</i></a></li>
                            <li><a href="?page=usr&pg=<?php echo $cpg ?>&keyword=<?php echo $keyword; ?>"><i class="material-icons">last_page</i></a></li>
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
    });
    </script>
</body>
</html>