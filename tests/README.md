# E-Arsip Testing Framework

This directory contains the testing framework for the E-Arsip application.

## Overview

The testing framework provides:
- Unit tests for models and controllers
- Integration tests for complete workflows
- Test database setup and teardown
- Simple test runner without external dependencies

## Structure

```
tests/
├── bootstrap.php          # Test environment setup
├── BaseTestCase.php       # Base test class with assertions
├── run_tests.php          # Test runner
├── README.md             # This file
├── models/               # Model tests
│   ├── UserTest.php
│   ├── BeritaAcaraTest.php
│   └── KendaraanTest.php
├── controllers/          # Controller tests
│   ├── AuthControllerTest.php
│   └── UserControllerTest.php
└── integration/          # Integration tests
    └── AuthFlowTest.php
```

## Setup

### Database Setup

1. Create a test database:
```sql
CREATE DATABASE e_arsip_test;
```

2. Update your `.env` file or create a test-specific configuration:
```env
DB_NAME=e_arsip_test
APP_ENV=testing
APP_DEBUG=true
```

### Running Tests

#### Run All Tests
```bash
php tests/run_tests.php
```

#### Run Specific Test Class
```bash
php tests/run_tests.php class UserTest
```

#### List Available Tests
```bash
php tests/run_tests.php list
```

#### Verbose Output
```bash
VERBOSE_TESTS=1 php tests/run_tests.php
```

## Writing Tests

### Basic Test Structure

```php
<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../BaseTestCase.php';

class MyTest extends BaseTestCase {
    public function setUp() {
        parent::setUp();
        // Additional setup
    }
    
    public function testSomething() {
        // Test code
        $this->assertEquals('expected', 'actual');
    }
    
    public function tearDown() {
        parent::tearDown();
        // Additional cleanup
    }
}
```

### Available Assertions

- `assertEquals($expected, $actual, $message = '')`
- `assertTrue($condition, $message = '')`
- `assertFalse($condition, $message = '')`
- `assertNull($value, $message = '')`
- `assertNotNull($value, $message = '')`
- `assertContains($needle, $haystack, $message = '')`
- `assertArrayHasKey($key, $array, $message = '')`
- `assertStringContains($needle, $haystack, $message = '')`
- `expectException($exceptionClass, $callable)`

### Test Helpers

#### Database Helpers
```php
// Create test data
$userId = $this->createTestUser(['username' => 'testuser']);
$beritaAcaraId = $this->createTestBeritaAcara(['nama_pemakai' => 'Test']);
$kendaraanId = $this->createTestKendaraan(['no_polisi' => 'TEST123']);

// Query database
$result = $this->query("SELECT * FROM tbl_user WHERE id_user = ?", [$userId]);
$user = $this->fetchRow("SELECT * FROM tbl_user WHERE username = ?", ['testuser']);
$users = $this->fetchAll("SELECT * FROM tbl_user");
$count = $this->countRows('tbl_user', 'admin = ?', [1]);
```

#### Mock Data
```php
// Mock session
$this->mockSession(['admin' => 1, 'id_user' => 1]);

// Mock POST request
$this->mockPost(['username' => 'test', 'password' => 'pass']);

// Mock GET request
$this->mockGet(['page' => 1, 'search' => 'test']);

// Clear request data
$this->clearRequest();
```

## Test Database

The test framework automatically:
1. Creates a separate test database (`e_arsip_test`)
2. Runs migrations to set up tables
3. Seeds basic test data before each test
4. Cleans up data after each test

### Test Data

Each test starts with:
- 2 test users (admin and regular user)
- 1 test institution record
- Clean database state

## Best Practices

### 1. Test Isolation
Each test should be independent and not rely on other tests:

```php
public function testCreateUser() {
    // Create test data within the test
    $userData = ['username' => 'newuser', ...];
    $userId = $this->userModel->createUser($userData);
    
    // Test the result
    $this->assertNotNull($userId);
}
```

### 2. Test One Thing
Each test method should test one specific behavior:

```php
public function testUserCanLogin() {
    // Test successful login
}

public function testUserCannotLoginWithWrongPassword() {
    // Test failed login
}
```

### 3. Descriptive Test Names
Use descriptive names that explain what is being tested:

```php
public function testCreateUserWithDuplicateUsernameThrowsException() {
    // Test duplicate username validation
}
```

### 4. Use Assertions Effectively
Choose the most appropriate assertion:

```php
// Good
$this->assertTrue($user->isActive());
$this->assertEquals('admin', $user->getRole());

// Less clear
$this->assertEquals(true, $user->isActive());
$this->assertEquals(true, $user->getRole() === 'admin');
```

### 5. Test Edge Cases
Test boundary conditions and error cases:

```php
public function testCreateUserWithEmptyUsername() {
    $this->expectException('Exception', function() {
        $this->userModel->createUser(['username' => '']);
    });
}
```

## Continuous Integration

To run tests in CI/CD pipelines:

```bash
# Setup test database
mysql -e "CREATE DATABASE e_arsip_test;"

# Run tests
php tests/run_tests.php

# Check exit code
echo $?  # 0 = success, 1 = failure
```

## Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check database credentials in `.env`
   - Ensure test database exists
   - Verify MySQL/MariaDB is running

2. **Class Not Found**
   - Check autoloader is working
   - Verify file paths are correct
   - Ensure all required files are included

3. **Test Data Issues**
   - Check database migrations ran successfully
   - Verify test data seeding
   - Ensure proper cleanup between tests

### Debug Mode

Enable verbose output for debugging:
```bash
VERBOSE_TESTS=1 php tests/run_tests.php
```

This will show:
- Full error messages
- File and line numbers
- Stack traces for failures
