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
                    $query = mysqli_query($config, "DELETE FROM tbl_user WHERE id_user='$id'");
                    if($query == true){
                        $_SESSION['succDel'] = 'SUKSES! Data user berhasil dihapus';
                        echo '<script>window.location.href="./admin.php?page=usr";</script>';
                        die();
                    } else {
                        $_SESSION['errDel'] = 'ERROR! Data user gagal dihapus';
                        echo '<script>window.location.href="./admin.php?page=usr";</script>';
                        die();
                    }
                }
                break;
        }
    }
}
?>