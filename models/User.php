<?php
require_once 'BaseModel.php';

/**
 * User Model
 * Handles user-related database operations
 */
class User extends BaseModel {
    protected $table = 'tbl_user';
    protected $primaryKey = 'id_user';
    protected $fillable = ['username', 'password', 'nama', 'nip', 'admin', 'is_active'];
    
    /**
     * Find user by username
     */
    public function findByUsername($username) {
        $stmt = mysqli_prepare($this->db, "SELECT * FROM {$this->table} WHERE username = ? AND is_active = 1");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return $data;
    }
    
    /**
     * Create new user with password hashing
     */
    public function createUser($data) {
        // Validate required fields
        $required = ['username', 'password', 'nama', 'nip'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field $field is required");
            }
        }
        
        // Check if username already exists
        if ($this->findByUsername($data['username'])) {
            throw new Exception("Username already exists");
        }
        
        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Set default admin level if not provided
        if (!isset($data['admin'])) {
            $data['admin'] = 0;
        }
        
        // Set active status
        $data['is_active'] = 1;
        
        return $this->create($data);
    }
    
    /**
     * Update user password
     */
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = mysqli_prepare($this->db, "UPDATE {$this->table} SET password = ?, updated_at = NOW() WHERE {$this->primaryKey} = ?");
        mysqli_stmt_bind_param($stmt, "si", $hashedPassword, $userId);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        if ($result) {
            log_activity('password_change', 'User password updated', ['user_id' => $userId]);
        }
        
        return $result;
    }
    
    /**
     * Verify user password
     */
    public function verifyPassword($userId, $password) {
        $user = $this->find($userId);
        if (!$user) {
            return false;
        }
        
        // Handle legacy MD5 passwords
        if (strlen($user['password']) === 32) {
            if (md5($password) === $user['password']) {
                // Upgrade to new password hash
                $this->updatePassword($userId, $password);
                return true;
            }
            return false;
        }
        
        return password_verify($password, $user['password']);
    }
    
    /**
     * Authenticate user
     */
    public function authenticate($username, $password) {
        $user = $this->findByUsername($username);
        if (!$user) {
            log_security('failed_login', 'Login attempt with non-existent username', ['username' => $username]);
            return false;
        }
        
        $isValid = false;
        
        // Handle legacy MD5 passwords
        if (strlen($user['password']) === 32) {
            if (md5($password) === $user['password']) {
                $isValid = true;
                // Upgrade to new password hash
                $this->updatePassword($user['id_user'], $password);
            }
        } else {
            $isValid = password_verify($password, $user['password']);
        }
        
        if ($isValid) {
            // Update last login
            $this->updateLastLogin($user['id_user']);
            log_activity('login', 'User logged in successfully', [
                'user_id' => $user['id_user'],
                'username' => $username
            ]);
            return $user;
        } else {
            log_security('failed_login', 'Invalid password attempt', [
                'username' => $username,
                'user_id' => $user['id_user']
            ]);
            return false;
        }
    }
    
    /**
     * Update last login timestamp
     */
    public function updateLastLogin($userId) {
        $stmt = mysqli_prepare($this->db, "UPDATE {$this->table} SET last_login = NOW() WHERE {$this->primaryKey} = ?");
        mysqli_stmt_bind_param($stmt, "i", $userId);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * Get active users
     */
    public function getActiveUsers() {
        return $this->where(['is_active' => 1]);
    }
    
    /**
     * Get admin users
     */
    public function getAdminUsers() {
        $sql = "SELECT * FROM {$this->table} WHERE admin IN (1, 2) AND is_active = 1";
        return $this->query($sql);
    }
    
    /**
     * Get regular users
     */
    public function getRegularUsers() {
        return $this->where(['admin' => 0, 'is_active' => 1]);
    }
    
    /**
     * Deactivate user
     */
    public function deactivateUser($userId) {
        $result = $this->update($userId, ['is_active' => 0]);
        
        if ($result) {
            log_activity('user_deactivated', 'User account deactivated', ['user_id' => $userId]);
        }
        
        return $result;
    }
    
    /**
     * Activate user
     */
    public function activateUser($userId) {
        $result = $this->update($userId, ['is_active' => 1]);
        
        if ($result) {
            log_activity('user_activated', 'User account activated', ['user_id' => $userId]);
        }
        
        return $result;
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($userId, $data) {
        // Remove password from profile update
        unset($data['password']);
        
        $result = $this->update($userId, $data);
        
        if ($result) {
            log_activity('profile_update', 'User profile updated', [
                'user_id' => $userId,
                'updated_fields' => array_keys($data)
            ]);
        }
        
        return $result;
    }
    
    /**
     * Check if username is available
     */
    public function isUsernameAvailable($username, $excludeUserId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE username = ?";
        $params = [$username];
        
        if ($excludeUserId) {
            $sql .= " AND {$this->primaryKey} != ?";
            $params[] = $excludeUserId;
        }
        
        $result = $this->query($sql, $params);
        return $result[0]['count'] == 0;
    }
    
    /**
     * Get user statistics
     */
    public function getStatistics() {
        $stats = [];
        
        $stats['total_users'] = $this->count();
        $stats['active_users'] = $this->count(['is_active' => 1]);
        $stats['admin_users'] = count($this->getAdminUsers());
        $stats['regular_users'] = $this->count(['admin' => 0, 'is_active' => 1]);
        
        return $stats;
    }
    
    /**
     * Search users
     */
    public function search($keyword, $limit = 10) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE (nama LIKE ? OR username LIKE ? OR nip LIKE ?) 
                AND is_active = 1 
                ORDER BY nama ASC 
                LIMIT ?";
        
        $searchTerm = "%$keyword%";
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "sssi", $searchTerm, $searchTerm, $searchTerm, $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        mysqli_stmt_close($stmt);
        
        return $data;
    }
}
?>
