<?php
// Pastikan tidak ada output sebelum tag php pembuka

// Load environment variables
require_once __DIR__ . '/env.php';

// Load session management
require_once __DIR__ . '/session.php';

// Database configuration from environment variables
$host     = env('DB_HOST', 'localhost');
$username = env('DB_USERNAME', 'root');
$password = env('DB_PASSWORD', 'mysql');
$database = env('DB_NAME', 'earsip_db');

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

// Load input validation
require_once __DIR__ . '/validation.php';

// Load file handler
require_once __DIR__ . '/file_handler.php';

// Load error handler
require_once __DIR__ . '/error_handler.php';

// Load autoloader
require_once __DIR__ . '/autoloader.php';

// Load view helpers
require_once __DIR__ . '/view_helpers.php';

// Load database monitor
require_once __DIR__ . '/database_monitor.php';

// Load cache manager
require_once __DIR__ . '/cache_manager.php';

// Load session cache
require_once __DIR__ . '/session_cache.php';

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