<?php
/**
 * Test Runner
 * Simple test runner for running all tests
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/BaseTestCase.php';

class TestRunner {
    private $testFiles = [];
    private $results = [];
    private $totalTests = 0;
    private $passedTests = 0;
    private $failedTests = 0;
    
    public function __construct() {
        $this->findTestFiles();
    }
    
    /**
     * Find all test files
     */
    private function findTestFiles() {
        $testDirs = [
            __DIR__ . '/models',
            __DIR__ . '/controllers',
            __DIR__ . '/integration'
        ];
        
        foreach ($testDirs as $dir) {
            if (is_dir($dir)) {
                $files = glob($dir . '/*Test.php');
                $this->testFiles = array_merge($this->testFiles, $files);
            }
        }
    }
    
    /**
     * Run all tests
     */
    public function runAll() {
        echo "E-Arsip Test Suite\n";
        echo "==================\n\n";
        
        $startTime = microtime(true);
        
        foreach ($this->testFiles as $testFile) {
            $this->runTestFile($testFile);
        }
        
        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);
        
        $this->printSummary($duration);
        
        return $this->failedTests === 0;
    }
    
    /**
     * Run tests in a single file
     */
    private function runTestFile($testFile) {
        $className = $this->getClassNameFromFile($testFile);
        
        echo "Running $className...\n";
        
        require_once $testFile;
        
        if (!class_exists($className)) {
            echo "  ERROR: Class $className not found\n\n";
            return;
        }
        
        $testClass = new $className();
        $methods = get_class_methods($testClass);
        $testMethods = array_filter($methods, function($method) {
            return strpos($method, 'test') === 0;
        });
        
        foreach ($testMethods as $method) {
            $this->runTestMethod($testClass, $method);
        }
        
        echo "\n";
    }
    
    /**
     * Run a single test method
     */
    private function runTestMethod($testClass, $method) {
        $this->totalTests++;
        
        try {
            // Setup
            if (method_exists($testClass, 'setUp')) {
                $testClass->setUp();
            }
            
            // Run test
            $testClass->$method();
            
            // Teardown
            if (method_exists($testClass, 'tearDown')) {
                $testClass->tearDown();
            }
            
            echo "  ✓ $method\n";
            $this->passedTests++;
            
        } catch (Exception $e) {
            echo "  ✗ $method\n";
            echo "    Error: " . $e->getMessage() . "\n";
            if (isset($_ENV['VERBOSE_TESTS']) && $_ENV['VERBOSE_TESTS']) {
                echo "    File: " . $e->getFile() . ":" . $e->getLine() . "\n";
            }
            $this->failedTests++;
            
            $this->results[] = [
                'method' => $method,
                'class' => get_class($testClass),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
        }
    }
    
    /**
     * Get class name from file path
     */
    private function getClassNameFromFile($filePath) {
        $fileName = basename($filePath, '.php');
        return $fileName;
    }
    
    /**
     * Print test summary
     */
    private function printSummary($duration) {
        echo "Test Summary\n";
        echo "============\n";
        echo "Total Tests: {$this->totalTests}\n";
        echo "Passed: {$this->passedTests}\n";
        echo "Failed: {$this->failedTests}\n";
        echo "Duration: {$duration}s\n\n";
        
        if ($this->failedTests > 0) {
            echo "Failed Tests:\n";
            echo "-------------\n";
            foreach ($this->results as $result) {
                echo "{$result['class']}::{$result['method']}\n";
                echo "  Error: {$result['error']}\n";
                if (isset($_ENV['VERBOSE_TESTS']) && $_ENV['VERBOSE_TESTS']) {
                    echo "  Location: {$result['file']}:{$result['line']}\n";
                }
                echo "\n";
            }
        }
        
        if ($this->failedTests === 0) {
            echo "🎉 All tests passed!\n";
        } else {
            echo "❌ {$this->failedTests} test(s) failed.\n";
        }
    }
    
    /**
     * Run specific test class
     */
    public function runClass($className) {
        $testFile = null;
        
        foreach ($this->testFiles as $file) {
            if ($this->getClassNameFromFile($file) === $className) {
                $testFile = $file;
                break;
            }
        }
        
        if (!$testFile) {
            echo "Test class $className not found.\n";
            return false;
        }
        
        echo "Running $className...\n\n";
        
        $startTime = microtime(true);
        $this->runTestFile($testFile);
        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);
        
        $this->printSummary($duration);
        
        return $this->failedTests === 0;
    }
    
    /**
     * List all available test classes
     */
    public function listTests() {
        echo "Available Test Classes:\n";
        echo "=======================\n";
        
        foreach ($this->testFiles as $file) {
            $className = $this->getClassNameFromFile($file);
            echo "- $className\n";
        }
    }
}

// Command line interface
if (php_sapi_name() === 'cli') {
    $runner = new TestRunner();
    
    if (isset($argv[1])) {
        switch ($argv[1]) {
            case 'list':
                $runner->listTests();
                break;
            case 'class':
                if (isset($argv[2])) {
                    $success = $runner->runClass($argv[2]);
                    exit($success ? 0 : 1);
                } else {
                    echo "Usage: php run_tests.php class <ClassName>\n";
                    exit(1);
                }
                break;
            default:
                echo "Unknown command: {$argv[1]}\n";
                echo "Available commands: list, class <ClassName>\n";
                exit(1);
        }
    } else {
        $success = $runner->runAll();
        exit($success ? 0 : 1);
    }
} else {
    // Web interface
    echo "<pre>";
    $runner = new TestRunner();
    $runner->runAll();
    echo "</pre>";
}
?>
