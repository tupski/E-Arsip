<?php
// Query data statistik khusus user
$id_user = $_SESSION['id_user'];

// Count total berita acara milik user
$query = mysqli_query($config, "SELECT COUNT(*) as total FROM tbl_berita_acara WHERE id_user='$id_user'");
$data = mysqli_fetch_array($query);
$total_berita = $data['total'];

// Count total kendaraan milik user
$query = mysqli_query($config, "SELECT COUNT(*) as total FROM tbl_kendaraan WHERE id_user='$id_user'");
$data = mysqli_fetch_array($query);
$total_kendaraan = $data['total'];
?>

<!-- Page Heading -->
<div class="row">
    <div class="col s12">
        <h4 class="page-title">Dashboard User</h4>
        <div class="divider"></div>
    </div>
</div>

<!-- Statistik Cards -->
<div class="row">
    <!-- Berita Acara -->
    <div class="col s12 m6 l4">
        <div class="card hoverable">
            <div class="card-content blue white-text">
                <span class="card-title">Berita Acara</span>
                <h3><?php echo number_format($total_berita); ?></h3>
                <div class="progress white">
                    <div class="determinate blue lighten-2" style="width: 70%"></div>
                </div>
            </div>
            <div class="card-action blue darken-1" style="display: flex; justify-content: space-between; align-items: center;">
                <a href="?page=berita" class="white-text" style="display: inline-flex; align-items: center;">
                    <i class="material-icons" style="margin-right: 4px;">add</i> Buat Baru
                </a>
                <a href="?page=aset" class="white-text" style="display: inline-flex; align-items: center;">
                    <i class="material-icons" style="margin-right: 4px;">list</i> Lihat
                </a>
            </div>
        </div>
    </div>

    <!-- Kendaraan Card -->
    <div class="col s12 m6 l4">
        <div class="card hoverable">
            <div class="card-content teal white-text">
                <span class="card-title">Kendaraan</span>
                <h3><?php echo number_format($total_kendaraan); ?></h3>
                <div class="progress white">
                    <div class="determinate teal lighten-2" style="width: 50%"></div>
                </div>
            </div>
            <div class="card-action teal darken-1">
                <a href="?page=kendaraan" class="white-text">
                    <i class="material-icons left">directions_car</i> Kelola Data
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Aktivitas Terakhir -->
<div class="row">
    <div class="col s12">
        <div class="card hoverable">
            <div class="card-content">
                <div class="row">
                    <div class="col s8">
                        <span class="card-title">Aktivitas Terakhir</span>
                    </div>
                    <div class="col s4 right-align">
                        <a href="?page=aset" class="btn-flat waves-effect blue-text">Lihat Semua</a>
                    </div>
                </div>
                <div class="divider"></div>
                
                <table class="striped responsive-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Pemakai</th>
                            <th>Unit Kerja</th>
                            <th>Tanggal</th>
                            <th>No. Berita</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = mysqli_query($config, "SELECT * FROM tbl_berita_acara WHERE id_user='$id_user' ORDER BY tgl_pembuatan DESC LIMIT 5");
                        
                        if(mysqli_num_rows($query) > 0){
                            $no = 1;
                            while($row = mysqli_fetch_array($query)){
                                echo '
                                <tr>
                                    <td>'.$no++.'</td>
                                    <td>'.$row['nama_pemakai'].'</td>
                                    <td>'.$row['unit_kerja'].'</td>
                                    <td>'.date('d/m/Y', strtotime($row['tgl_pembuatan'])).'</td>
                                    <td>'.$row['no_berita_acara'].'</td>
                                    <td>
                                        <a href="?page=berita&act=edit&id='.$row['id_berita_acara'].'" class="btn-flat waves-effect blue-text">
                                            <i class="material-icons">edit</i>
                                        </a>
                                    </td>
                                </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="6" class="center-align">Tidak ada data aktivitas</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>