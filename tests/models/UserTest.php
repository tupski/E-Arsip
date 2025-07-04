<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../BaseTestCase.php';

/**
 * User Model Test
 */
class UserTest extends BaseTestCase {
    private $userModel;
    
    public function setUp() {
        parent::setUp();
        $this->userModel = new User($this->db);
    }
    
    public function testCreateUser() {
        $userData = [
            'username' => 'newuser',
            'password' => 'password123',
            'nama' => 'New User',
            'nip' => '11111111111111111111',
            'admin' => 0
        ];
        
        $userId = $this->userModel->createUser($userData);
        
        $this->assertNotNull($userId, 'User ID should not be null');
        $this->assertTrue($userId > 0, 'User ID should be greater than 0');
        
        // Verify user was created
        $user = $this->userModel->find($userId);
        $this->assertNotNull($user, 'User should exist in database');
        $this->assertEquals('newuser', $user['username']);
        $this->assertEquals('New User', $user['nama']);
        $this->assertEquals('11111111111111111111', $user['nip']);
        $this->assertEquals(0, $user['admin']);
        $this->assertEquals(1, $user['is_active']);
        
        // Verify password was hashed
        $this->assertTrue(password_verify('password123', $user['password']), 'Password should be properly hashed');
    }
    
    public function testCreateUserWithDuplicateUsername() {
        $userData = [
            'username' => 'testadmin', // This username already exists in seed data
            'password' => 'password123',
            'nama' => 'Duplicate User',
            'nip' => '11111111111111111111',
            'admin' => 0
        ];
        
        $this->expectException('Exception', function() use ($userData) {
            $this->userModel->createUser($userData);
        });
    }
    
    public function testFindByUsername() {
        $user = $this->userModel->findByUsername('testadmin');
        
        $this->assertNotNull($user, 'User should be found');
        $this->assertEquals('testadmin', $user['username']);
        $this->assertEquals('Test Administrator', $user['nama']);
        $this->assertEquals(1, $user['admin']);
    }
    
    public function testFindByUsernameNotFound() {
        $user = $this->userModel->findByUsername('nonexistent');
        
        $this->assertNull($user, 'User should not be found');
    }
    
    public function testAuthenticate() {
        $user = $this->userModel->authenticate('testadmin', 'password123');
        
        $this->assertNotNull($user, 'Authentication should succeed');
        $this->assertEquals('testadmin', $user['username']);
        $this->assertEquals('Test Administrator', $user['nama']);
    }
    
    public function testAuthenticateInvalidPassword() {
        $user = $this->userModel->authenticate('testadmin', 'wrongpassword');
        
        $this->assertFalse($user, 'Authentication should fail with wrong password');
    }
    
    public function testAuthenticateInvalidUsername() {
        $user = $this->userModel->authenticate('nonexistent', 'password123');
        
        $this->assertFalse($user, 'Authentication should fail with non-existent username');
    }
    
    public function testUpdatePassword() {
        // Get test user
        $user = $this->userModel->findByUsername('testuser');
        $userId = $user['id_user'];
        
        // Update password
        $result = $this->userModel->updatePassword($userId, 'newpassword123');
        
        $this->assertTrue($result, 'Password update should succeed');
        
        // Verify new password works
        $authResult = $this->userModel->authenticate('testuser', 'newpassword123');
        $this->assertNotNull($authResult, 'Authentication with new password should succeed');
        
        // Verify old password doesn't work
        $oldAuthResult = $this->userModel->authenticate('testuser', 'password123');
        $this->assertFalse($oldAuthResult, 'Authentication with old password should fail');
    }
    
    public function testVerifyPassword() {
        $user = $this->userModel->findByUsername('testuser');
        $userId = $user['id_user'];
        
        // Test correct password
        $result = $this->userModel->verifyPassword($userId, 'password123');
        $this->assertTrue($result, 'Password verification should succeed with correct password');
        
        // Test incorrect password
        $result = $this->userModel->verifyPassword($userId, 'wrongpassword');
        $this->assertFalse($result, 'Password verification should fail with incorrect password');
    }
    
    public function testGetActiveUsers() {
        $users = $this->userModel->getActiveUsers();
        
        $this->assertTrue(count($users) >= 2, 'Should have at least 2 active users from seed data');
        
        foreach ($users as $user) {
            $this->assertEquals(1, $user['is_active'], 'All returned users should be active');
        }
    }
    
    public function testGetAdminUsers() {
        $admins = $this->userModel->getAdminUsers();
        
        $this->assertTrue(count($admins) >= 1, 'Should have at least 1 admin user from seed data');
        
        foreach ($admins as $admin) {
            $this->assertTrue(in_array($admin['admin'], [1, 2]), 'All returned users should be admins');
            $this->assertEquals(1, $admin['is_active'], 'All returned users should be active');
        }
    }
    
    public function testGetRegularUsers() {
        $users = $this->userModel->getRegularUsers();
        
        $this->assertTrue(count($users) >= 1, 'Should have at least 1 regular user from seed data');
        
        foreach ($users as $user) {
            $this->assertEquals(0, $user['admin'], 'All returned users should be regular users');
            $this->assertEquals(1, $user['is_active'], 'All returned users should be active');
        }
    }
    
    public function testDeactivateUser() {
        $user = $this->userModel->findByUsername('testuser');
        $userId = $user['id_user'];
        
        $result = $this->userModel->deactivateUser($userId);
        $this->assertTrue($result, 'User deactivation should succeed');
        
        // Verify user is deactivated
        $updatedUser = $this->userModel->find($userId);
        $this->assertEquals(0, $updatedUser['is_active'], 'User should be deactivated');
    }
    
    public function testActivateUser() {
        $user = $this->userModel->findByUsername('testuser');
        $userId = $user['id_user'];
        
        // First deactivate
        $this->userModel->deactivateUser($userId);
        
        // Then activate
        $result = $this->userModel->activateUser($userId);
        $this->assertTrue($result, 'User activation should succeed');
        
        // Verify user is activated
        $updatedUser = $this->userModel->find($userId);
        $this->assertEquals(1, $updatedUser['is_active'], 'User should be activated');
    }
    
    public function testUpdateProfile() {
        $user = $this->userModel->findByUsername('testuser');
        $userId = $user['id_user'];
        
        $updateData = [
            'nama' => 'Updated Name',
            'nip' => '99999999999999999999'
        ];
        
        $result = $this->userModel->updateProfile($userId, $updateData);
        $this->assertTrue($result, 'Profile update should succeed');
        
        // Verify update
        $updatedUser = $this->userModel->find($userId);
        $this->assertEquals('Updated Name', $updatedUser['nama']);
        $this->assertEquals('99999999999999999999', $updatedUser['nip']);
    }
    
    public function testIsUsernameAvailable() {
        // Test with existing username
        $available = $this->userModel->isUsernameAvailable('testadmin');
        $this->assertFalse($available, 'Existing username should not be available');
        
        // Test with new username
        $available = $this->userModel->isUsernameAvailable('newusername');
        $this->assertTrue($available, 'New username should be available');
        
        // Test excluding current user
        $user = $this->userModel->findByUsername('testadmin');
        $available = $this->userModel->isUsernameAvailable('testadmin', $user['id_user']);
        $this->assertTrue($available, 'Username should be available when excluding current user');
    }
    
    public function testSearch() {
        $results = $this->userModel->search('Test');
        
        $this->assertTrue(count($results) >= 2, 'Should find at least 2 users with "Test" in name');
        
        foreach ($results as $user) {
            $this->assertTrue(
                strpos($user['nama'], 'Test') !== false || 
                strpos($user['username'], 'test') !== false,
                'Search results should contain search term'
            );
        }
    }
    
    public function testGetStatistics() {
        $stats = $this->userModel->getStatistics();
        
        $this->assertArrayHasKey('total_users', $stats);
        $this->assertArrayHasKey('active_users', $stats);
        $this->assertArrayHasKey('admin_users', $stats);
        $this->assertArrayHasKey('regular_users', $stats);
        
        $this->assertTrue($stats['total_users'] >= 2, 'Should have at least 2 total users');
        $this->assertTrue($stats['active_users'] >= 2, 'Should have at least 2 active users');
        $this->assertTrue($stats['admin_users'] >= 1, 'Should have at least 1 admin user');
        $this->assertTrue($stats['regular_users'] >= 1, 'Should have at least 1 regular user');
    }
}
?>
