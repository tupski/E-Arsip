<?php
/**
 * Test Bootstrap File
 * Sets up the testing environment
 */

// Set error reporting for tests
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define test environment
define('TESTING', true);

// Load application configuration
require_once __DIR__ . '/../include/config.php';

// Set test database configuration
$_ENV['DB_NAME'] = 'e_arsip_test';
$_ENV['APP_ENV'] = 'testing';
$_ENV['APP_DEBUG'] = 'true';

// Create test database connection
$test_config = mysqli_connect(
    env('DB_HOST', 'localhost'),
    env('DB_USERNAME', 'root'),
    env('DB_PASSWORD', ''),
    env('DB_NAME', 'e_arsip_test')
);

if (!$test_config) {
    die("Test database connection failed: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($test_config, 'utf8mb4');

/**
 * Test Helper Class
 */
class TestHelper {
    public static $db;
    
    public static function setUpDatabase() {
        global $test_config;
        self::$db = $test_config;
        
        // Create test database if it doesn't exist
        $createDb = mysqli_query(self::$db, "CREATE DATABASE IF NOT EXISTS e_arsip_test");
        if (!$createDb) {
            die("Failed to create test database: " . mysqli_error(self::$db));
        }
        
        // Use test database
        mysqli_select_db(self::$db, 'e_arsip_test');
        
        // Run migrations
        self::runMigrations();
    }
    
    public static function runMigrations() {
        // Read and execute the main SQL file
        $sqlFile = __DIR__ . '/../database/e_arsip.sql';
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            
            // Split SQL into individual statements
            $statements = explode(';', $sql);
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $result = mysqli_query(self::$db, $statement);
                    if (!$result && !empty(mysqli_error(self::$db))) {
                        echo "Migration error: " . mysqli_error(self::$db) . "\n";
                        echo "Statement: " . $statement . "\n";
                    }
                }
            }
        }
    }
    
    public static function cleanDatabase() {
        // Clean all tables
        $tables = ['tbl_berita_acara', 'tbl_kendaraan', 'tbl_instansi', 'tbl_user', 'tbl_audit_log'];
        
        foreach ($tables as $table) {
            mysqli_query(self::$db, "DELETE FROM $table");
        }
    }
    
    public static function seedTestData() {
        // Insert test users
        $testUsers = [
            [
                'username' => 'testadmin',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'nama' => 'Test Administrator',
                'nip' => '12345678901234567890',
                'admin' => 1
            ],
            [
                'username' => 'testuser',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'nama' => 'Test User',
                'nip' => '09876543210987654321',
                'admin' => 0
            ]
        ];
        
        foreach ($testUsers as $user) {
            $stmt = mysqli_prepare(self::$db, 
                "INSERT INTO tbl_user (username, password, nama, nip, admin) VALUES (?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt, "ssssi", 
                $user['username'], $user['password'], $user['nama'], $user['nip'], $user['admin']
            );
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        
        // Insert test instansi
        $stmt = mysqli_prepare(self::$db,
            "INSERT INTO tbl_instansi (nama, alamat, kepala_dinas, nip, website, email, logo, id_user) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $instansiData = [
            'Test Instansi',
            'Jl. Test No. 123',
            'Test Kepala Dinas',
            '12345678901234567890',
            'https://test.com',
            'test@test.com',
            'test_logo.png',
            1
        ];
        mysqli_stmt_bind_param($stmt, "sssssssi", ...$instansiData);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    public static function createTestUser($data = []) {
        $defaultData = [
            'username' => 'testuser_' . uniqid(),
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'nama' => 'Test User',
            'nip' => '12345678901234567890',
            'admin' => 0
        ];
        
        $userData = array_merge($defaultData, $data);
        
        $stmt = mysqli_prepare(self::$db,
            "INSERT INTO tbl_user (username, password, nama, nip, admin) VALUES (?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "ssssi",
            $userData['username'], $userData['password'], $userData['nama'], 
            $userData['nip'], $userData['admin']
        );
        mysqli_stmt_execute($stmt);
        $userId = mysqli_insert_id(self::$db);
        mysqli_stmt_close($stmt);
        
        return $userId;
    }
    
    public static function createTestBeritaAcara($data = []) {
        $defaultData = [
            'no_berita_acara' => 'BA/TEST/' . uniqid(),
            'nama_pemakai' => 'Test Pemakai',
            'nip' => '12345678901234567890',
            'unit_kerja' => 'Test Unit',
            'jabatan_pembina' => 'Test Jabatan',
            'no_pakta_integritas' => 'PI/TEST/' . uniqid(),
            'tgl_pembuatan' => date('Y-m-d'),
            'id_user' => 1
        ];
        
        $beritaAcaraData = array_merge($defaultData, $data);
        
        $fields = array_keys($beritaAcaraData);
        $placeholders = str_repeat('?,', count($fields) - 1) . '?';
        $sql = "INSERT INTO tbl_berita_acara (" . implode(',', $fields) . ") VALUES ($placeholders)";
        
        $stmt = mysqli_prepare(self::$db, $sql);
        $types = str_repeat('s', count($beritaAcaraData));
        $values = array_values($beritaAcaraData);
        mysqli_stmt_bind_param($stmt, $types, ...$values);
        mysqli_stmt_execute($stmt);
        $id = mysqli_insert_id(self::$db);
        mysqli_stmt_close($stmt);
        
        return $id;
    }
    
    public static function createTestKendaraan($data = []) {
        $defaultData = [
            'jenis_kendaraan' => 'Motor',
            'merk_type' => 'Test Motor',
            'tahun' => date('Y'),
            'no_polisi' => 'TEST' . rand(1000, 9999),
            'warna' => 'Hitam',
            'no_mesin' => 'TEST_MESIN_' . uniqid(),
            'no_rangka' => 'TEST_RANGKA_' . uniqid(),
            'penanggung_jawab' => 'Test PJ',
            'pemakai' => 'Test Pemakai',
            'id_user' => 1,
            'status' => 'Aktif'
        ];
        
        $kendaraanData = array_merge($defaultData, $data);
        
        $fields = array_keys($kendaraanData);
        $placeholders = str_repeat('?,', count($fields) - 1) . '?';
        $sql = "INSERT INTO tbl_kendaraan (" . implode(',', $fields) . ") VALUES ($placeholders)";
        
        $stmt = mysqli_prepare(self::$db, $sql);
        $types = str_repeat('s', count($kendaraanData));
        $values = array_values($kendaraanData);
        mysqli_stmt_bind_param($stmt, $types, ...$values);
        mysqli_stmt_execute($stmt);
        $id = mysqli_insert_id(self::$db);
        mysqli_stmt_close($stmt);
        
        return $id;
    }
    
    public static function tearDown() {
        self::cleanDatabase();
    }
}

// Initialize test database
TestHelper::setUpDatabase();
?>
