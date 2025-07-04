<?php
// Pastikan tidak ada output sebelum tag php pembuka

// Load environment variables
require_once __DIR__ . '/env.php';

// Configure session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

// Set session name and lifetime
$session_name = env('SESSION_NAME', 'EARSIP_SESSION');
$session_lifetime = env_int('SESSION_LIFETIME', 3600);

session_name($session_name);
session_set_cookie_params([
    'lifetime' => $session_lifetime,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Mulai session hanya jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check session timeout
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $session_lifetime) {
    session_destroy();
    header("Location: index.php?timeout=1");
    exit();
}

// Database configuration from environment variables
$host     = env('DB_HOST', 'localhost');
$username = env('DB_USERNAME', 'root');
$password = env('DB_PASSWORD', '');
$database = env('DB_NAME', 'e_arsip');

// Buat koneksi dengan error handling yang lebih baik
$config = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
if (!$config) {
    error_log("Database connection failed: " . mysqli_connect_error());
    if (env_bool('APP_DEBUG', false)) {
        die("Koneksi database gagal: " . mysqli_connect_error());
    } else {
        die("Terjadi kesalahan sistem. Silakan coba lagi nanti.");
    }
}

// Set charset to UTF-8 for better security and internationalization
mysqli_set_charset($config, 'utf8mb4');

// Set timezone
$timezone = env('APP_TIMEZONE', 'Asia/Jakarta');
date_default_timezone_set($timezone);

// Load CSRF protection
require_once __DIR__ . '/csrf.php';

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