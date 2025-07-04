<?php
// Pastikan tidak ada output sebelum tag php pembuka

// Mulai session hanya jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$host     = "localhost";
$username = "root";
$password = "masrud.com";
$database = "e_arsip";

// Buat koneksi
$config = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
if (!$config) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Cek apakah fungsi sudah didefinisikan
if (!function_exists('isLoggedIn')) {
    // Fungsi untuk memeriksa login
    function isLoggedIn() {
        return isset($_SESSION['admin']);
    }

    // Fungsi untuk redirect jika belum login
    function checkLogin() {
        if (!isLoggedIn()) {
            $_SESSION['err'] = '<center>Anda harus login terlebih dahulu!</center>';
            header("Location: index.php");
            exit();
        }
    }

    // Fungsi untuk redirect admin ke halaman admin
    function checkAdmin() {
        checkLogin();
        if ($_SESSION['admin'] != 1) {
            header("Location: admin.php");
            exit();
        }
    }

    // Fungsi untuk redirect user biasa ke halaman user
    function checkRegularUser() {
        checkLogin();
        if ($_SESSION['admin'] != 0) {
            header("Location: halaman_user.php");
            exit();
        }
    }

    function get_record_owner($table, $id) {
    global $config;
    // Extract the table name without 'tbl_' prefix for the primary key column
    $table_name = str_replace('tbl_', '', $table);
    $query = mysqli_query($config, "SELECT id_user FROM $table WHERE id_{$table_name}='$id'");
    if(mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_array($query);
        return $data['id_user'];
    }
    return null;
    }
}
?>