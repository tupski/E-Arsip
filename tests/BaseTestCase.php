<?php
/**
 * Base Test Case
 * Provides common functionality for all test cases
 */

class BaseTestCase {
    protected $db;
    
    public function setUp() {
        $this->db = TestHelper::$db;
        TestHelper::cleanDatabase();
        TestHelper::seedTestData();
    }
    
    public function tearDown() {
        TestHelper::cleanDatabase();
    }
    
    /**
     * Assert that two values are equal
     */
    protected function assertEquals($expected, $actual, $message = '') {
        if ($expected !== $actual) {
            $this->fail($message ?: "Expected '$expected', got '$actual'");
        }
        return true;
    }
    
    /**
     * Assert that a value is true
     */
    protected function assertTrue($condition, $message = '') {
        if (!$condition) {
            $this->fail($message ?: "Expected true, got false");
        }
        return true;
    }
    
    /**
     * Assert that a value is false
     */
    protected function assertFalse($condition, $message = '') {
        if ($condition) {
            $this->fail($message ?: "Expected false, got true");
        }
        return true;
    }
    
    /**
     * Assert that a value is null
     */
    protected function assertNull($value, $message = '') {
        if ($value !== null) {
            $this->fail($message ?: "Expected null, got " . var_export($value, true));
        }
        return true;
    }
    
    /**
     * Assert that a value is not null
     */
    protected function assertNotNull($value, $message = '') {
        if ($value === null) {
            $this->fail($message ?: "Expected non-null value, got null");
        }
        return true;
    }
    
    /**
     * Assert that an array contains a value
     */
    protected function assertContains($needle, $haystack, $message = '') {
        if (!in_array($needle, $haystack)) {
            $this->fail($message ?: "Array does not contain expected value");
        }
        return true;
    }
    
    /**
     * Assert that an array has a specific key
     */
    protected function assertArrayHasKey($key, $array, $message = '') {
        if (!array_key_exists($key, $array)) {
            $this->fail($message ?: "Array does not have expected key '$key'");
        }
        return true;
    }
    
    /**
     * Assert that a string contains a substring
     */
    protected function assertStringContains($needle, $haystack, $message = '') {
        if (strpos($haystack, $needle) === false) {
            $this->fail($message ?: "String does not contain expected substring");
        }
        return true;
    }
    
    /**
     * Assert that an exception is thrown
     */
    protected function expectException($exceptionClass, $callable) {
        try {
            call_user_func($callable);
            $this->fail("Expected exception $exceptionClass was not thrown");
        } catch (Exception $e) {
            if (!($e instanceof $exceptionClass)) {
                $this->fail("Expected exception $exceptionClass, got " . get_class($e));
            }
        }
        return true;
    }
    
    /**
     * Fail the test with a message
     */
    protected function fail($message) {
        throw new Exception("Test failed: $message");
    }
    
    /**
     * Create a test user
     */
    protected function createTestUser($data = []) {
        return TestHelper::createTestUser($data);
    }
    
    /**
     * Create a test berita acara
     */
    protected function createTestBeritaAcara($data = []) {
        return TestHelper::createTestBeritaAcara($data);
    }
    
    /**
     * Create a test kendaraan
     */
    protected function createTestKendaraan($data = []) {
        return TestHelper::createTestKendaraan($data);
    }
    
    /**
     * Get database connection
     */
    protected function getDb() {
        return $this->db;
    }
    
    /**
     * Execute SQL query
     */
    protected function query($sql, $params = []) {
        if (empty($params)) {
            return mysqli_query($this->db, $sql);
        }
        
        $stmt = mysqli_prepare($this->db, $sql);
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * Fetch single row from query
     */
    protected function fetchRow($sql, $params = []) {
        $result = $this->query($sql, $params);
        return mysqli_fetch_assoc($result);
    }
    
    /**
     * Fetch all rows from query
     */
    protected function fetchAll($sql, $params = []) {
        $result = $this->query($sql, $params);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }
    
    /**
     * Count rows in table
     */
    protected function countRows($table, $where = '', $params = []) {
        $sql = "SELECT COUNT(*) as count FROM $table";
        if ($where) {
            $sql .= " WHERE $where";
        }
        
        $result = $this->fetchRow($sql, $params);
        return (int)$result['count'];
    }
    
    /**
     * Mock session data
     */
    protected function mockSession($data) {
        foreach ($data as $key => $value) {
            $_SESSION[$key] = $value;
        }
    }
    
    /**
     * Clear session data
     */
    protected function clearSession() {
        $_SESSION = [];
    }
    
    /**
     * Mock POST data
     */
    protected function mockPost($data) {
        $_POST = $data;
        $_SERVER['REQUEST_METHOD'] = 'POST';
    }
    
    /**
     * Mock GET data
     */
    protected function mockGet($data) {
        $_GET = $data;
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }
    
    /**
     * Clear request data
     */
    protected function clearRequest() {
        $_POST = [];
        $_GET = [];
        $_REQUEST = [];
        unset($_SERVER['REQUEST_METHOD']);
    }
}
?>
