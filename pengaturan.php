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

    if(isset($_REQUEST['submit'])){

        $nama = mysqli_real_escape_string($config, $_REQUEST['nama']);
        $alamat = mysqli_real_escape_string($config, $_REQUEST['alamat']);
        $kepala_dinas = mysqli_real_escape_string($config, $_REQUEST['kepala_dinas']);
        $nip = mysqli_real_escape_string($config, $_REQUEST['nip']);
        $website = mysqli_real_escape_string($config, $_REQUEST['website']);
        $email = mysqli_real_escape_string($config, $_REQUEST['email']);
        $id_user = $_SESSION['id_user'];

        //jika ada file logo yang diupload
        if(!empty($_FILES['logo']['name'])){
            $ekstensi = array('png','jpg','jpeg','gif');
            $file = $_FILES['logo']['name'];
            $x = explode('.', $file);
            $eks = strtolower(end($x));
            $ukuran = $_FILES['logo']['size'];
            $file_tmp = $_FILES['logo']['tmp_name'];

            if(in_array($eks, $ekstensi) === true){
                if($ukuran < 1044070){
                    $query = mysqli_query($config, "SELECT logo FROM tbl_instansi");
                    $data = mysqli_fetch_array($query);
                    $logo_lama = $data['logo'];
                    unlink($logo_lama);
                    move_uploaded_file($file_tmp, 'upload/'.$file);
                    $query = mysqli_query($config, "UPDATE tbl_instansi SET nama='$nama', alamat='$alamat', kepala_dinas='$kepala_dinas', nip='$nip', website='$website', email='$email', logo='upload/$file', id_user='$id_user' WHERE id_instansi='1'");
                    if($query == true){
                        $_SESSION['succEdit'] = 'SUKSES! Data instansi berhasil diupdate';

                        // Check if headers have already been sent (when included in another page)
                        if (headers_sent()) {
                            echo '<script>alert("SUKSES! Data instansi berhasil diupdate"); window.location.href="?page=png";</script>';
                            return;
                        } else {
                            header("Location: ./admin.php?page=png");
                            die();
                        }
                    } else {
                        $_SESSION['errQ'] = 'ERROR! Ada masalah dengan query';
                        echo '<script language="javascript">window.history.back();</script>';
                    }
                } else {
                    $_SESSION['errSize'] = 'Ukuran file terlalu besar!';
                    echo '<script language="javascript">window.history.back();</script>';
                }
            } else {
                $_SESSION['errFormat'] = 'Format file tidak didukung!';
                echo '<script language="javascript">window.history.back();</script>';
            }
        } else {
            $query = mysqli_query($config, "UPDATE tbl_instansi SET nama='$nama', alamat='$alamat', kepala_dinas='$kepala_dinas', nip='$nip', website='$website', email='$email', id_user='$id_user' WHERE id_instansi='1'");
            if($query == true){
                $_SESSION['succEdit'] = 'SUKSES! Data instansi berhasil diupdate';

                // Check if headers have already been sent (when included in another page)
                if (headers_sent()) {
                    echo '<script>alert("SUKSES! Data instansi berhasil diupdate"); window.location.href="?page=png";</script>';
                    return;
                } else {
                    header("Location: ./admin.php?page=png");
                    die();
                }
            } else {
                $_SESSION['errQ'] = 'ERROR! Ada masalah dengan query';
                echo '<script language="javascript">window.history.back();</script>';
            }
        }
    } else {

        $query = mysqli_query($config, "SELECT * FROM tbl_instansi WHERE id_instansi='1'");
        if(mysqli_num_rows($query) > 0){
            $data = mysqli_fetch_array($query);
        }
?>

<!-- Row Start -->
<div class="row">
    <!-- Secondary Nav START -->
    <div class="col s12">
        <nav class="secondary-nav">
            <div class="nav-wrapper blue-grey darken-1">
                <ul class="left">
                    <li class="waves-effect waves-light"><a href="#" class="judul"><i class="material-icons">settings</i> Pengaturan Instansi</a></li>
                </ul>
            </div>
        </nav>
    </div>
    <!-- Secondary Nav END -->
</div>
<!-- Row END -->

<?php
    if(isset($_SESSION['errQ'])){
        $errQ = $_SESSION['errQ'];
        echo '<div id="alert-message" class="row">
                <div class="col m12">
                    <div class="card red lighten-5">
                        <div class="card-content notif">
                            <span class="card-title red-text"><i class="material-icons md-36">clear</i> '.$errQ.'</span>
                        </div>
                    </div>
                </div>
            </div>';
        unset($_SESSION['errQ']);
    }
    if(isset($_SESSION['errFormat'])){
        $errFormat = $_SESSION['errFormat'];
        echo '<div id="alert-message" class="row">
                <div class="col m12">
                    <div class="card red lighten-5">
                        <div class="card-content notif">
                            <span class="card-title red-text"><i class="material-icons md-36">clear</i> '.$errFormat.'</span>
                        </div>
                    </div>
                </div>
            </div>';
        unset($_SESSION['errFormat']);
    }
    if(isset($_SESSION['errSize'])){
        $errSize = $_SESSION['errSize'];
        echo '<div id="alert-message" class="row">
                <div class="col m12">
                    <div class="card red lighten-5">
                        <div class="card-content notif">
                            <span class="card-title red-text"><i class="material-icons md-36">clear</i> '.$errSize.'</span>
                        </div>
                    </div>
                </div>
            </div>';
        unset($_SESSION['errSize']);
    }
    if(isset($_SESSION['succEdit'])){
        $succEdit = $_SESSION['succEdit'];
        echo '<div id="alert-message" class="row">
                <div class="col m12">
                    <div class="card green lighten-5">
                        <div class="card-content notif">
                            <span class="card-title green-text"><i class="material-icons md-36">done</i> '.$succEdit.'</span>
                        </div>
                    </div>
                </div>
            </div>';
        unset($_SESSION['succEdit']);
    }
?>

<!-- Row form Start -->
<div class="row jarak-form">

    <!-- Form START -->
    <form class="col s12" method="post" action="?page=png" enctype="multipart/form-data">

        <!-- Row in form START -->
        <div class="row">
            <div class="input-field col s6">
                <i class="material-icons prefix md-prefix">business</i>
                <input id="nama" type="text" class="validate" name="nama" value="<?php echo $data['nama']; ?>" required>
                <label for="nama">Nama Instansi</label>
            </div>
            <div class="input-field col s6">
                <i class="material-icons prefix md-prefix">location_on</i>
                <input id="alamat" type="text" class="validate" name="alamat" value="<?php echo $data['alamat']; ?>" required>
                <label for="alamat">Alamat</label>
            </div>
            <div class="input-field col s6">
                <i class="material-icons prefix md-prefix">person</i>
                <input id="kepala_dinas" type="text" class="validate" name="kepala_dinas" value="<?php echo $data['kepala_dinas']; ?>" required>
                <label for="kepala_dinas">Kepala Dinas</label>
            </div>
            <div class="input-field col s6">
                <i class="material-icons prefix md-prefix">looks_one</i>
                <input id="nip" type="text" class="validate" name="nip" value="<?php echo $data['nip']; ?>" required>
                <label for="nip">NIP</label>
            </div>
            <div class="input-field col s6">
                <i class="material-icons prefix md-prefix">language</i>
                <input id="website" type="text" class="validate" name="website" value="<?php echo $data['website']; ?>" required>
                <label for="website">Website</label>
            </div>
            <div class="input-field col s6">
                <i class="material-icons prefix md-prefix">email</i>
                <input id="email" type="email" class="validate" name="email" value="<?php echo $data['email']; ?>" required>
                <label for="email">Email</label>
            </div>
            <div class="col s12">
                <div class="file-field input-field">
                    <div class="btn light-green darken-1">
                        <span>File</span>
                        <input type="file" name="logo">
                    </div>
                    <div class="file-path-wrapper">
                        <input class="file-path validate" type="text" placeholder="Upload logo instansi">
                    </div>
                </div>
                <small>Format file yang diperbolehkan *.JPG, *.PNG, *.GIF</small><br>
                <small>Ukuran maksimal file 1 MB</small>
            </div>
            <div class="col s12">
                <img src="<?php echo $data['logo']; ?>" width="200">
            </div>
        </div>
        <!-- Row in form END -->

        <div class="row">
            <div class="col 6">
                <button type="submit" name="submit" class="btn-large blue waves-effect waves-light">SIMPAN <i class="material-icons">done</i></button>
            </div>
            <div class="col 6">
                <a href="?page=png" class="btn-large deep-orange waves-effect waves-light">BATAL <i class="material-icons">clear</i></a>
            </div>
        </div>

    </form>
    <!-- Form END -->

</div>
<!-- Row form END -->

<?php
    }
}
?>