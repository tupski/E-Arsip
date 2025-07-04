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

    // Hapus data (hanya admin yang bisa)
    if(isset($_GET['act']) && $_GET['act'] == 'del' && isset($_GET['id'])){
        if($_SESSION['admin'] == 1 || $_SESSION['admin'] == 2){
            $id = mysqli_real_escape_string($config, $_GET['id']);
            $query = mysqli_query($config, "DELETE FROM tbl_berita_acara WHERE id_berita_acara='$id'");
            
            if($query){
                $_SESSION['succDel'] = 'Data berhasil dihapus';

                // Check if headers have already been sent (when included in another page)
                if (headers_sent()) {
                    echo '<script>alert("Data berhasil dihapus"); window.location.href="?page=aset";</script>';
                    return;
                } else {
                    // Redirect to appropriate page based on user type
                    if($_SESSION['admin'] == 1 || $_SESSION['admin'] == 2) {
                        header("Location: ./admin.php?page=aset");
                    } else {
                        header("Location: ./halaman_user.php?page=aset");
                    }
                    die();
                }
            } else {
                $_SESSION['errDel'] = 'ERROR: Gagal menghapus data';
            }
        } else {
            echo '<script language="javascript">
                    window.alert("ERROR! Anda tidak memiliki hak akses untuk menghapus data");
                    window.location.href="?page=aset";
                  </script>';
        }
    }

    // Paging
    $limit = 10;
    $pg = @$_GET['pg'];
    $curr = ($pg && $pg > 1) ? ($pg - 1) * $limit : 0;
    $pg = $pg ?: 1;

    // Query untuk menampilkan data
    if($_SESSION['admin'] == 1 || $_SESSION['admin'] == 2){
        // Admin bisa lihat semua data
        $query = mysqli_query($config, "SELECT * FROM tbl_berita_acara ORDER BY tgl_pembuatan DESC LIMIT $curr, $limit");
    } else {
        // User biasa hanya bisa lihat data yang mereka buat
        $id_user = $_SESSION['id_user'];
        $query = mysqli_query($config, "SELECT * FROM tbl_berita_acara WHERE id_user='$id_user' ORDER BY tgl_pembuatan DESC LIMIT $curr, $limit");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pemakai Aset</title>
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

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
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
            padding: 10px 16px;
            border-radius: var(--border-radius);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 2px;
            white-space: nowrap;
            text-decoration: none;
            min-height: 36px;
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
            position: relative;
        }

        .search-container i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #777;
        }

        .search-container input {
            padding-left: 35px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            height: 40px;
            font-size: 14px;
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

        .modal {
            border-radius: var(--border-radius);
            max-width: 800px;
        }

        .modal-content {
            padding: 20px;
        }

        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #eee;
        }

        .detail-row {
            margin-bottom: 15px;
            display: flex;
        }

        .detail-label {
            font-weight: 600;
            min-width: 200px;
            color: #555;
        }

        .detail-value {
            flex: 1;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark-color);
            margin: 20px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            th, td {
                padding: 8px 10px;
                font-size: 13px;
            }
            
            .btn {
                padding: 8px 12px;
                font-size: 13px;
                min-height: 32px;
            }
            
            .detail-row {
                flex-direction: column;
            }
            
            .detail-label {
                margin-bottom: 5px;
                min-width: auto;
            }

            .form-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .form-header h1 {
                font-size: 16px;
                margin-bottom: 0;
            }

            .form-header .btn {
                width: 100%;
                justify-content: center;
                max-width: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="form-container">
            <div class="form-card">
                <div class="form-header">
                    <h1><i class="material-icons">list</i> DAFTAR PEMAKAI BARANG/ASET MILIK DAERAH OPD: DINAS TENAGA KERJA</h1>
                    <a href="?page=berita&act=add" class="btn btn-primary">
                        <i class="material-icons">add</i> Buat Berita Acara
                    </a>
                </div>
                
                <div class="form-body">
                    <?php
                    // Notifikasi
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
                        <input type="text" id="searchInput" placeholder="Cari data...">
                    </div>
                    
                    <div class="table-container">
                        <table id="tbl">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Pemakai</th>
                                    <th>NIP</th>
                                    <th>Unit Kerja</th>
                                    <th>Jabatan</th>
                                    <th>Kendaraan</th>
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
                                            <td>'.$row['nama_pemakai'].'</td>
                                            <td>'.$row['nip'].'</td>
                                            <td>'.$row['unit_kerja'].'</td>
                                            <td>'.$row['jabatan_pembina'].'</td>
                                            <td>'.($row['nama_kendaraan'] ? $row['nama_kendaraan'] : '-').'</td>
                                            <td>
                                                <div style="display: flex; gap: 5px;">';
                                        
                                        // Tombol aksi
                                        if($_SESSION['admin'] == 1 || $_SESSION['admin'] == 2 || $row['id_user'] == $_SESSION['id_user']){
                                            echo '<button class="btn btn-primary modal-trigger" data-target="detailModal'.$row['id_berita_acara'].'">
                                                    <i class="material-icons" style="font-size: 16px;">visibility</i>
                                                  </button>';
                                            
                                            echo '<a href="?page=berita&act=edit&id='.$row['id_berita_acara'].'" class="btn btn-success">
                                                    <i class="material-icons" style="font-size: 16px;">edit</i>
                                                  </a>';
                                            
                                            // Hanya admin yang bisa hapus
                                            if($_SESSION['admin'] == 1 || $_SESSION['admin'] == 2){
                                                echo '<a href="?page=aset&act=del&id='.$row['id_berita_acara'].'" onclick="return confirm(\'Yakin ingin menghapus data ini?\')" class="btn btn-danger">
                                                        <i class="material-icons" style="font-size: 16px;">delete</i>
                                                      </a>';
                                            }
                                            
                                            echo '<a href="download_pdf.php?id='.$row['id_berita_acara'].'" target="_blank" class="btn btn-secondary" title="Download PDF">
                                                    <i class="material-icons" style="font-size: 16px;">file_download</i>
                                                  </a>';
                                        } else {
                                            echo '<span class="btn btn-secondary">No Action</span>';
                                        }
                                        
                                        echo '</div>
                                            </td>
                                        </tr>';
                                        
                                        // Modal untuk detail lengkap
                                        echo '
                                        <div id="detailModal'.$row['id_berita_acara'].'" class="modal">
                                            <div class="modal-content">
                                                <h4>Detail Lengkap Berita Acara</h4>
                                                <div class="divider"></div>
                                                
                                                <div class="section-title">Informasi Dasar</div>
                                                <div class="detail-row">
                                                    <span class="detail-label">No. Berita Acara:</span>
                                                    <span class="detail-value">'.$row['no_berita_acara'].'</span>
                                                </div>
                                                <div class="detail-row">
                                                    <span class="detail-label">Nama Pemakai:</span>
                                                    <span class="detail-value">'.$row['nama_pemakai'].'</span>
                                                </div>
                                                <div class="detail-row">
                                                    <span class="detail-label">NIP:</span>
                                                    <span class="detail-value">'.$row['nip'].'</span>
                                                </div>
                                                <div class="detail-row">
                                                    <span class="detail-label">Unit Kerja:</span>
                                                    <span class="detail-value">'.$row['unit_kerja'].'</span>
                                                </div>
                                                <div class="detail-row">
                                                    <span class="detail-label">Jabatan Pembina:</span>
                                                    <span class="detail-value">'.$row['jabatan_pembina'].'</span>
                                                </div>
                                                <div class="detail-row">
                                                    <span class="detail-label">No. Pakta Integritas:</span>
                                                    <span class="detail-value">'.$row['no_pakta_integritas'].'</span>
                                                </div>
                                                
                                                <div class="section-title">Data Kendaraan</div>
                                                <div class="detail-row">
                                                    <span class="detail-label">Nama Kendaraan:</span>
                                                    <span class="detail-value">'.($row['nama_kendaraan'] ? $row['nama_kendaraan'] : '-').'</span>
                                                </div>
                                                <div class="detail-row">
                                                    <span class="detail-label">Status BPKB:</span>
                                                    <span class="detail-value">'.($row['status_bpkb'] ? $row['status_bpkb'] : '-').'</span>
                                                </div>
                                                <div class="detail-row">
                                                    <span class="detail-label">No. BPKB:</span>
                                                    <span class="detail-value">'.($row['no_bpkb'] ? $row['no_bpkb'] : '-').'</span>
                                                </div>
                                                
                                                <div class="section-title">Data Barang</div>';
                                        
                                        // Tampilkan data barang jika ada
                                        $barang_displayed = false;
                                        for ($i = 1; $i <= 4; $i++) {
                                            if (!empty($row['barang'.$i.'_nama']) || !empty($row['barang'.$i.'_qty'])) {
                                                echo '
                                                <div class="detail-row">
                                                    <span class="detail-label">Barang '.$i.':</span>
                                                    <span class="detail-value">'.($row['barang'.$i.'_qty'] ? $row['barang'.$i.'_qty'].' x ' : '').$row['barang'.$i.'_nama'].'</span>
                                                </div>';
                                                $barang_displayed = true;
                                            }
                                        }
                                        
                                        if (!$barang_displayed) {
                                            echo '<div class="detail-row">Tidak ada data barang</div>';
                                        }
                                        
                                        echo '
                                                <div class="detail-row">
                                                    <span class="detail-label">Keterangan:</span>
                                                    <span class="detail-value">'.($row['keterangan'] ? $row['keterangan'] : '-').'</span>
                                                </div>
                                                <div class="detail-row">
                                                    <span class="detail-label">Tanggal Pembuatan:</span>
                                                    <span class="detail-value">'.date('d/m/Y H:i', strtotime($row['tgl_pembuatan'])).'</span>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary modal-close">Tutup</button>
                                            </div>
                                        </div>';
                                    }
                                } else {
                                    echo '<tr><td colspan="7"><center>Tidak ada data untuk ditampilkan</center></td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php
                    // Pagination
                    if($_SESSION['admin'] == 1 || $_SESSION['admin'] == 2){
                        $query = mysqli_query($config, "SELECT * FROM tbl_berita_acara");
                    } else {
                        $id_user = $_SESSION['id_user'];
                        $query = mysqli_query($config, "SELECT * FROM tbl_berita_acara WHERE id_user='$id_user'");
                    }
                    
                    $cdata = mysqli_num_rows($query);
                    $cpg = ceil($cdata/$limit);
                    
                    if($cdata > $limit):
                    ?>
                    <ul class="pagination">
                        <?php if($pg > 1): ?>
                            <li><a href="?page=aset&pg=1"><i class="material-icons">first_page</i></a></li>
                            <li><a href="?page=aset&pg=<?php echo $pg-1 ?>"><i class="material-icons">chevron_left</i></a></li>
                        <?php else: ?>
                            <li class="disabled"><a href="#"><i class="material-icons">first_page</i></a></li>
                            <li class="disabled"><a href="#"><i class="material-icons">chevron_left</i></a></li>
                        <?php endif; ?>
                        
                        <?php for($i=1; $i <= $cpg; $i++): ?>
                            <li class="<?php echo ($i == $pg) ? 'active' : '' ?>">
                                <a href="?page=aset&pg=<?php echo $i ?>"><?php echo $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if($pg < $cpg): ?>
                            <li><a href="?page=aset&pg=<?php echo $pg+1 ?>"><i class="material-icons">chevron_right</i></a></li>
                            <li><a href="?page=aset&pg=<?php echo $cpg ?>"><i class="material-icons">last_page</i></a></li>
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
        // Inisialisasi modal
        var elems = document.querySelectorAll('.modal');
        var instances = M.Modal.init(elems);
        
        // Fungsi pencarian
        document.getElementById('searchInput').addEventListener('keyup', function() {
            var input = this.value.toLowerCase();
            var table = document.getElementById("tbl");
            var tr = table.getElementsByTagName("tr");

            for (var i = 1; i < tr.length; i++) {
                var show = false;
                var td = tr[i].getElementsByTagName("td");
                for (var j = 0; j < td.length; j++) {
                    if (td[j]) {
                        var txt = td[j].textContent || td[j].innerText;
                        if (txt.toLowerCase().indexOf(input) > -1) {
                            show = true;
                            break;
                        }
                    }
                }
                tr[i].style.display = show ? "" : "none";
            }
        });
    });
    </script>
</body>
</html>

<?php
}
?>