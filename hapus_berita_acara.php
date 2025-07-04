<?php
//session
if(!isset($_SESSION['admin'])){
    $_SESSION['err'] = '<center>Anda harus login terlebih dahulu!</center>';
    header("Location: ./");
    die();
} else {

    if(isset($_REQUEST['act']) && $_REQUEST['act'] == 'del' && isset($_REQUEST['id'])){
        if($_SESSION['admin'] == 1 || $_SESSION['admin'] == 2){
            $id = mysqli_real_escape_string($config, $_REQUEST['id']);
            $query = mysqli_query($config, "DELETE FROM tbl_berita_acara WHERE id_berita_acara='$id'");
            
            if($query){
                $_SESSION['succDel'] = 'SUKSES! Data berita acara berhasil dihapus';

                // Check if headers have already been sent (when included in another page)
                if (headers_sent()) {
                    echo '<script>alert("SUKSES! Data berita acara berhasil dihapus"); window.location.href="?page=aset";</script>';
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
                $_SESSION['errDel'] = 'ERROR! Data berita acara gagal dihapus';

                // Check if headers have already been sent (when included in another page)
                if (headers_sent()) {
                    echo '<script>alert("ERROR! Data berita acara gagal dihapus"); window.location.href="?page=aset";</script>';
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
            }
        } else {
            echo '<script language="javascript">
                    window.alert("ERROR! Anda tidak memiliki hak akses untuk menghapus data");
                    window.location.href="?page=aset";
                  </script>';
        }
    }
}
?>