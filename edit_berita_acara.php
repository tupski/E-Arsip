<?php
//session
if(!isset($_SESSION['admin'])){
    $_SESSION['err'] = '<center>Anda harus login terlebih dahulu!</center>';
    header("Location: ./");
    die();
} else {

    if(isset($_REQUEST['id'])){

        $id = mysqli_real_escape_string($config, $_REQUEST['id']);
        
        // Periksa kepemilikan data jika user biasa
        if($_SESSION['admin'] == 0){
            $owner = get_record_owner('tbl_berita_acara', $id);
            if($owner != $_SESSION['id_user']){
                echo '<script>alert("Anda hanya bisa mengedit data milik sendiri"); window.location.href="?page=berita";</script>';
                die();
            }
        }

        $query = mysqli_query($config, "SELECT * FROM tbl_berita_acara WHERE id_berita_acara='$id'");
        if(mysqli_num_rows($query) > 0){
            $row = mysqli_fetch_array($query);
        } else {
            $_SESSION['err'] = '<center>Data tidak ditemukan!</center>';

            // Check if headers have already been sent (when included in another page)
            if (headers_sent()) {
                echo '<script>alert("Data tidak ditemukan!"); window.location.href="?page=aset";</script>';
                return;
            } else {
                header("Location: ./admin.php?page=aset");
                die();
            }
        }

        if(isset($_REQUEST['submit'])){

            //validasi form kosong
            if($_REQUEST['nama_pemakai'] == "" || $_REQUEST['nip'] == "" || $_REQUEST['unit_kerja'] == "" || 
               $_REQUEST['jabatan_pembina'] == "" || $_REQUEST['no_pakta_integritas'] == ""){
                $_SESSION['errEmpty'] = 'ERROR! Semua form wajib diisi kecuali kendaraan dan barang jika tidak ada';
                echo '<script language="javascript">window.history.back();</script>';
            } else {

                $nama_pemakai = mysqli_real_escape_string($config, $_REQUEST['nama_pemakai']);
                $nip = mysqli_real_escape_string($config, $_REQUEST['nip']);
                $unit_kerja = mysqli_real_escape_string($config, $_REQUEST['unit_kerja']);
                $jabatan_pembina = mysqli_real_escape_string($config, $_REQUEST['jabatan_pembina']);
                $no_pakta_integritas = mysqli_real_escape_string($config, $_REQUEST['no_pakta_integritas']);
                $nama_kendaraan = mysqli_real_escape_string($config, $_REQUEST['nama_kendaraan']);
                $status_bpkb = mysqli_real_escape_string($config, $_REQUEST['status_bpkb']);
                $no_bpkb = mysqli_real_escape_string($config, $_REQUEST['no_bpkb']);
                $barang1_qty = mysqli_real_escape_string($config, $_REQUEST['barang1_qty']);
                $barang1_nama = mysqli_real_escape_string($config, $_REQUEST['barang1_nama']);
                $barang2_qty = mysqli_real_escape_string($config, $_REQUEST['barang2_qty']);
                $barang2_nama = mysqli_real_escape_string($config, $_REQUEST['barang2_nama']);
                $barang3_qty = mysqli_real_escape_string($config, $_REQUEST['barang3_qty']);
                $barang3_nama = mysqli_real_escape_string($config, $_REQUEST['barang3_nama']);
                $barang4_qty = mysqli_real_escape_string($config, $_REQUEST['barang4_qty']);
                $barang4_nama = mysqli_real_escape_string($config, $_REQUEST['barang4_nama']);
                $keterangan = mysqli_real_escape_string($config, $_REQUEST['keterangan']);

                $query = mysqli_query($config, "UPDATE tbl_berita_acara SET nama_pemakai='$nama_pemakai', nip='$nip', unit_kerja='$unit_kerja', jabatan_pembina='$jabatan_pembina', no_pakta_integritas='$no_pakta_integritas', nama_kendaraan='$nama_kendaraan', status_bpkb='$status_bpkb', no_bpkb='$no_bpkb', barang1_qty='$barang1_qty', barang1_nama='$barang1_nama', barang2_qty='$barang2_qty', barang2_nama='$barang2_nama', barang3_qty='$barang3_qty', barang3_nama='$barang3_nama', barang4_qty='$barang4_qty', barang4_nama='$barang4_nama', keterangan='$keterangan' WHERE id_berita_acara='$id'");

                if($query == true){
                    $_SESSION['succEdit'] = 'SUKSES! Data berita acara berhasil diupdate';

                    // Check if headers have already been sent (when included in another page)
                    if (headers_sent()) {
                        echo '<script>alert("SUKSES! Data berita acara berhasil diupdate"); window.location.href="?page=aset";</script>';
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
                    $_SESSION['errQ'] = 'ERROR! Ada masalah dengan query';
                    echo '<script language="javascript">window.history.back();</script>';
                }
            }
        }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Berita Acara</title>
    <style>
:root {
            
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --border-radius: 6px;
            --box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            --transition: all 0.2s ease;
            --sidebar-width: 250px;
        }

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

        .form-section {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark-color);
            margin: 0 0 15px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
            width: 100%;
        }

        .form-field {
            margin-bottom: 15px;
        }

        .field-box {
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            padding: 12px;
            background-color: #f9f9f9;
        }

        .field-box label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #555;
            font-size: 14px;
        }

        .field-box input, 
        .field-box textarea, 
        .field-box select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background-color: white;
            box-sizing: border-box;
        }

        .field-box input:focus, 
        .field-box textarea:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
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
            gap: 6px;
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

        .required:after {
            content: " *";
            color: var(--danger-color);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-body {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="form-container">
            <div class="form-card">
                <div class="form-header">
                    <h1><i class="material-icons">description</i> Edit Berita Acara</h1>
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
                    if(isset($_SESSION['succEdit'])){
                        echo '<div class="alert alert-success">
                                <i class="material-icons">check_circle</i>
                                '.$_SESSION['succEdit'].'
                              </div>';
                        unset($_SESSION['succEdit']);
                    }
                    ?>
                    
                    <form method="post" action="?page=berita&act=edit&id=<?php echo $id; ?>">
                        <!-- Basic Information Section -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="material-icons">info</i>
                                Informasi Dasar
                            </h3>
                            
                            <div class="form-grid">
                                <div class="form-field">
                                    <div class="field-box">
                                        <label for="nama_pemakai" class="required">Nama Pemakai</label>
                                        <input type="text" id="nama_pemakai" name="nama_pemakai" value="<?php echo $row['nama_pemakai']; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-field">
                                    <div class="field-box">
                                        <label for="nip" class="required">NIP</label>
                                        <input type="text" id="nip" name="nip" value="<?php echo $row['nip']; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-field">
                                    <div class="field-box">
                                        <label for="unit_kerja" class="required">Unit Kerja</label>
                                        <input type="text" id="unit_kerja" name="unit_kerja" value="<?php echo $row['unit_kerja']; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-field">
                                    <div class="field-box">
                                        <label for="jabatan_pembina" class="required">Jabatan Pembina</label>
                                        <input type="text" id="jabatan_pembina" name="jabatan_pembina" value="<?php echo $row['jabatan_pembina']; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-field">
                                    <div class="field-box">
                                        <label for="no_pakta_integritas" class="required">No. Pakta Integritas</label>
                                        <input type="text" id="no_pakta_integritas" name="no_pakta_integritas" value="<?php echo $row['no_pakta_integritas']; ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Vehicle Information Section -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="material-icons">directions_car</i>
                                Data Kendaraan (Jika Ada)
                            </h3>
                            
                            <div class="form-grid">
                                <div class="form-field">
                                    <div class="field-box">
                                        <label for="nama_kendaraan">Nama Kendaraan</label>
                                        <input type="text" id="nama_kendaraan" name="nama_kendaraan" value="<?php echo $row['nama_kendaraan']; ?>">
                                    </div>
                                </div>
                                
                                <div class="form-field">
                                    <div class="field-box">
                                        <label for="status_bpkb">Status BPKB</label>
                                        <input type="text" id="status_bpkb" name="status_bpkb" value="<?php echo $row['status_bpkb']; ?>">
                                    </div>
                                </div>
                                
                                <div class="form-field">
                                    <div class="field-box">
                                        <label for="no_bpkb">No. BPKB</label>
                                        <input type="text" id="no_bpkb" name="no_bpkb" value="<?php echo $row['no_bpkb']; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Items Information Section -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="material-icons">inventory</i>
                                Data Barang (Jika Ada)
                            </h3>
                            
                            <div class="form-grid">
                                <div class="form-field">
                                    <div class="field-box">
                                        <label for="barang1_qty">Qty Barang 1</label>
                                        <input type="number" id="barang1_qty" name="barang1_qty" value="<?php echo $row['barang1_qty']; ?>">
                                    </div>
                                </div>
                                
                                <div class="form-field">
                                    <div class="field-box">
                                        <label for="barang1_nama">Nama Barang 1</label>
                                        <input type="text" id="barang1_nama" name="barang1_nama" value="<?php echo $row['barang1_nama']; ?>">
                                    </div>
                                </div>
                                
                                <div class="form-field">
                                    <div class="field-box">
                                        <label for="barang2_qty">Qty Barang 2</label>
                                        <input type="number" id="barang2_qty" name="barang2_qty" value="<?php echo $row['barang2_qty']; ?>">
                                    </div>
                                </div>
                                
                                <div class="form-field">
                                    <div class="field-box">
                                        <label for="barang2_nama">Nama Barang 2</label>
                                        <input type="text" id="barang2_nama" name="barang2_nama" value="<?php echo $row['barang2_nama']; ?>">
                                    </div>
                                </div>
                                
                                <div class="form-field">
                                    <div class="field-box">
                                        <label for="barang3_qty">Qty Barang 3</label>
                                        <input type="number" id="barang3_qty" name="barang3_qty" value="<?php echo $row['barang3_qty']; ?>">
                                    </div>
                                </div>
                                
                                <div class="form-field">
                                    <div class="field-box">
                                        <label for="barang3_nama">Nama Barang 3</label>
                                        <input type="text" id="barang3_nama" name="barang3_nama" value="<?php echo $row['barang3_nama']; ?>">
                                    </div>
                                </div>
                                
                                <div class="form-field">
                                    <div class="field-box">
                                        <label for="barang4_qty">Qty Barang 4</label>
                                        <input type="number" id="barang4_qty" name="barang4_qty" value="<?php echo $row['barang4_qty']; ?>">
                                    </div>
                                </div>
                                
                                <div class="form-field">
                                    <div class="field-box">
                                        <label for="barang4_nama">Nama Barang 4</label>
                                        <input type="text" id="barang4_nama" name="barang4_nama" value="<?php echo $row['barang4_nama']; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Additional Information Section -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="material-icons">notes</i>
                                Keterangan Tambahan
                            </h3>
                            
                            <div class="form-field">
                                <div class="field-box">
                                    <label for="keterangan">Keterangan</label>
                                    <textarea id="keterangan" name="keterangan" rows="4"><?php echo $row['keterangan']; ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="form-actions">
                            <button type="button" onclick="window.history.back()" class="btn btn-secondary">
                                <i class="material-icons">arrow_back</i> Batal
                            </button>
                            <button type="submit" name="submit" class="btn btn-primary">
                                <i class="material-icons">save</i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Simple form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const requiredFields = document.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    const fieldBox = field.closest('.field-box');
                    if (fieldBox) {
                        fieldBox.style.borderColor = 'var(--danger-color)';
                        fieldBox.style.boxShadow = '0 0 0 2px rgba(247, 37, 133, 0.2)';
                    }
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Harap isi semua field yang wajib diisi!');
            }
        });

        // Reset field styles when user starts typing
        document.querySelectorAll('input, textarea').forEach(field => {
            field.addEventListener('input', function() {
                const fieldBox = this.closest('.field-box');
                if (fieldBox) {
                    fieldBox.style.borderColor = '';
                    fieldBox.style.boxShadow = '';
                }
            });
        });
    </script>
</body>
</html>

<?php
    } else {
        header("Location: ./admin.php?page=berita");
        die();
    }
}
?>