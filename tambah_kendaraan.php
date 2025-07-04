<?php
//session
if(!isset($_SESSION['admin'])){
    $_SESSION['err'] = '<center>Anda harus login terlebih dahulu!</center>';
    echo '<script>window.location.href="./";</script>';
    die();
} else {

    if(isset($_REQUEST['submit'])){

        //validasi form kosong
        if($_REQUEST['jenis_kendaraan'] == "" || $_REQUEST['merk_type'] == "" || $_REQUEST['tahun'] == "" || 
           $_REQUEST['no_polisi'] == "" || $_REQUEST['warna'] == "" || $_REQUEST['no_mesin'] == "" || 
           $_REQUEST['no_rangka'] == "" || $_REQUEST['penanggung_jawab'] == "" || $_REQUEST['pemakai'] == ""){
            $_SESSION['errEmpty'] = 'ERROR! Semua form wajib diisi';
            echo '<script>window.history.back();</script>';
            die();
        } else {

            $jenis_kendaraan = mysqli_real_escape_string($config, $_REQUEST['jenis_kendaraan']);
            $merk_type = mysqli_real_escape_string($config, $_REQUEST['merk_type']);
            $tahun = mysqli_real_escape_string($config, $_REQUEST['tahun']);
            $no_polisi = mysqli_real_escape_string($config, $_REQUEST['no_polisi']);
            $warna = mysqli_real_escape_string($config, $_REQUEST['warna']);
            $no_mesin = mysqli_real_escape_string($config, $_REQUEST['no_mesin']);
            $no_rangka = mysqli_real_escape_string($config, $_REQUEST['no_rangka']);
            $penanggung_jawab = mysqli_real_escape_string($config, $_REQUEST['penanggung_jawab']);
            $pemakai = mysqli_real_escape_string($config, $_REQUEST['pemakai']);
            $keterangan = mysqli_real_escape_string($config, $_REQUEST['keterangan']);
            $id_user = $_SESSION['id_user'];

            $query = mysqli_query($config, "INSERT INTO tbl_kendaraan(jenis_kendaraan, merk_type, tahun, no_polisi, warna, no_mesin, no_rangka, penanggung_jawab, pemakai, keterangan, id_user) 
            VALUES('$jenis_kendaraan', '$merk_type', '$tahun', '$no_polisi', '$warna', '$no_mesin', '$no_rangka', '$penanggung_jawab', '$pemakai', '$keterangan', '$id_user')");

            if($query == true){
                $_SESSION['succAdd'] = 'SUKSES! Data kendaraan berhasil ditambahkan';
                // Redirect to appropriate page based on user type
                if($_SESSION['admin'] == 1 || $_SESSION['admin'] == 2) {
                    echo '<script>window.location.href="./admin.php?page=kendaraan";</script>';
                } else {
                    echo '<script>window.location.href="./halaman_user.php?page=kendaraan";</script>';
                }
                die();
            } else {
                $_SESSION['errQ'] = 'ERROR! Ada masalah dengan query';
                echo '<script>window.history.back();</script>';
                die();
            }
        }
    } else {
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Kendaraan</title>
    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --danger-color: #e74c3c;
            --transition: all 0.3s ease;
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

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 15px;
            gap: 15px;
        }

        .form-group {
            flex: 1;
            min-width: 250px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 14px;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--accent-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(72, 149, 239, 0.2);
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: var(--border-radius);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #e91e63;
        }

        .icon-prefix {
            margin-right: 10px;
            color: #777;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .form-group {
                min-width: 100%;
            }
            
            .btn {
                padding: 8px 15px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="form-container">
            <div class="form-card">
                <div class="form-header">
                    <h1><i class="material-icons">directions_car</i> TAMBAH DATA KENDARAAN</h1>
                </div>
                
                <div class="form-body">
                    <?php
                    if(isset($_SESSION['errEmpty'])){
                        echo '<div class="alert alert-danger">
                                <i class="material-icons">error</i>
                                '.$_SESSION['errEmpty'].'
                              </div>';
                        unset($_SESSION['errEmpty']);
                    }
                    if(isset($_SESSION['errQ'])){
                        echo '<div class="alert alert-danger">
                                <i class="material-icons">error</i>
                                '.$_SESSION['errQ'].'
                              </div>';
                        unset($_SESSION['errQ']);
                    }
                    ?>
                    
                    <form method="post" action="?page=kendaraan&act=add">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="jenis_kendaraan"><i class="material-icons icon-prefix">directions_car</i> Jenis Kendaraan</label>
                                <input type="text" id="jenis_kendaraan" name="jenis_kendaraan" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="merk_type"><i class="material-icons icon-prefix">branding_watermark</i> Merk/Type</label>
                                <input type="text" id="merk_type" name="merk_type" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="tahun"><i class="material-icons icon-prefix">event</i> Tahun</label>
                                <input type="text" id="tahun" name="tahun" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="no_polisi"><i class="material-icons icon-prefix">format_list_numbered</i> No. Polisi</label>
                                <input type="text" id="no_polisi" name="no_polisi" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="warna"><i class="material-icons icon-prefix">invert_colors</i> Warna</label>
                                <input type="text" id="warna" name="warna" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="no_mesin"><i class="material-icons icon-prefix">settings</i> No. Mesin</label>
                                <input type="text" id="no_mesin" name="no_mesin" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="no_rangka"><i class="material-icons icon-prefix">settings_input_component</i> No. Rangka</label>
                                <input type="text" id="no_rangka" name="no_rangka" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="penanggung_jawab"><i class="material-icons icon-prefix">person</i> Penanggung Jawab</label>
                                <input type="text" id="penanggung_jawab" name="penanggung_jawab" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="pemakai"><i class="material-icons icon-prefix">person_outline</i> Pemakai</label>
                                <input type="text" id="pemakai" name="pemakai" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="keterangan"><i class="material-icons icon-prefix">note</i> Keterangan</label>
                                <textarea id="keterangan" name="keterangan" class="form-control"></textarea>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="submit" class="btn btn-primary">
                                <i class="material-icons">save</i> Simpan
                            </button>
                            <a href="?page=kendaraan" class="btn btn-danger">
                                <i class="material-icons">cancel</i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
    }
}
?>