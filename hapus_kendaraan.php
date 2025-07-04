<?php
//session
if(!isset($_SESSION['admin'])){
    $_SESSION['err'] = '<center>Anda harus login terlebih dahulu!</center>';
    echo '<script>alert("Anda harus login terlebih dahulu!"); window.location.href="./";</script>';
    die();
} else {

    if(isset($_REQUEST['act'])){
        $act = $_REQUEST['act'];
        switch ($act) {
            case 'del':
                if(isset($_REQUEST['id'])){
                    $id = mysqli_real_escape_string($config, $_REQUEST['id']);
                    $query = mysqli_query($config, "DELETE FROM tbl_kendaraan WHERE id_kendaraan='$id'");
                    if($query == true){
                        $_SESSION['succDel'] = 'SUKSES! Data kendaraan berhasil dihapus';
                        // Redirect to appropriate page based on user type
                        if($_SESSION['admin'] == 1 || $_SESSION['admin'] == 2) {
                            echo '<script>window.location.href="./admin.php?page=kendaraan";</script>';
                        } else {
                            echo '<script>window.location.href="./halaman_user.php?page=kendaraan";</script>';
                        }
                        die();
                    } else {
                        $_SESSION['errDel'] = 'ERROR! Data kendaraan gagal dihapus';
                        // Redirect to appropriate page based on user type
                        if($_SESSION['admin'] == 1 || $_SESSION['admin'] == 2) {
                            echo '<script>window.location.href="./admin.php?page=kendaraan";</script>';
                        } else {
                            echo '<script>window.location.href="./halaman_user.php?page=kendaraan";</script>';
                        }
                        die();
                    }
                }
                break;
        }
    }
}
?>