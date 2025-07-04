<?php
function cek_session_admin(){
    if(!is_admin()){
        flash('err', '<center>Anda tidak memiliki hak akses!</center>');

        // Check if headers have already been sent (when included in another page)
        if (headers_sent()) {
            echo '<script>alert("Anda tidak memiliki hak akses!"); window.location.href="./";</script>';
            return;
        } else {
            header("Location: ./");
            die();
        }
    }
}

function cek_session_user(){
    if(!is_logged_in() || !SessionManager::isRegularUser()){
        flash('err', '<center>Anda tidak memiliki hak akses!</center>');

        // Check if headers have already been sent (when included in another page)
        if (headers_sent()) {
            echo '<script>alert("Anda tidak memiliki hak akses!"); window.location.href="./";</script>';
            return;
        } else {
            header("Location: ./");
            die();
        }
    }
}

function format_tanggal($date){
    if(empty($date) || $date == '0000-00-00'){
        return '-';
    }
    
    $bulan = array(
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    );
    
    $pecah = explode('-', $date);
    return $pecah[2].' '.$bulan[(int)$pecah[1]].' '.$pecah[0];
}

function format_nip($nip){
    if(empty($nip)){
        return '-';
    }
    return preg_replace("/(\d{8})(\d{6})(\d{1})(\d{3})/", "$1 $2 $3 $4", $nip);
}

function get_user_info($id_user, $config){
    $query = mysqli_query($config, "SELECT * FROM tbl_user WHERE id_user='$id_user'");
    if(mysqli_num_rows($query) > 0){
        return mysqli_fetch_array($query);
    }
    return false;
}

function generate_no_berita_acara(){
    return "BA-".date('YmdHis');
}

function sanitize_input($data, $config){
    return mysqli_real_escape_string($config, htmlspecialchars(strip_tags(trim($data))));
}

function redirect_with_message($page, $type, $message){
    $_SESSION[$type] = $message;

    // Check if headers have already been sent (when included in another page)
    if (headers_sent()) {
        echo '<script>window.location.href="?page='.$page.'";</script>';
        return;
    } else {
        header("Location: ./admin.php?page=".$page);
        die();
    }
}

function show_alert($type, $message){
    $color = ($type == 'error') ? 'red' : 'green';
    $icon = ($type == 'error') ? 'clear' : 'done';
    
    echo '<div id="alert-message" class="row">
            <div class="col m12">
                <div class="card '.$color.' lighten-5">
                    <div class="card-content notif">
                        <span class="card-title '.$color.'-text">
                            <i class="material-icons md-36">'.$icon.'</i> '.$message.'
                        </span>
                    </div>
                </div>
            </div>
        </div>';
}
?>