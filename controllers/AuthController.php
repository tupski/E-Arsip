<?php
require_once 'BaseController.php';
require_once 'models/User.php';

/**
 * Authentication Controller
 * Handles login, logout, and registration
 */
class AuthController extends BaseController {
    private $userModel;
    
    public function __construct($db) {
        parent::__construct($db);
        $this->userModel = new User($db);
    }
    
    /**
     * Handle login
     */
    public function login() {
        // Check if user is already logged in
        if (is_logged_in()) {
            if (is_admin()) {
                $this->redirect('admin.php');
            } else {
                $this->redirect('halaman_user.php');
            }
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processLogin();
        }
        
        // Show login form (handled by index.php)
    }
    
    /**
     * Process login form submission
     */
    private function processLogin() {
        $this->validateCSRF('login');
        
        // Validate input
        $validator = $this->validateInput($_POST, [
            'username' => [
                'required' => 'Username wajib diisi.',
                'min_length' => ['length' => 3, 'message' => 'Username minimal 3 karakter.'],
                'username' => 'Format username tidak valid.'
            ],
            'password' => [
                'required' => 'Password wajib diisi.',
                'min_length' => ['length' => 6, 'message' => 'Password minimal 6 karakter.']
            ]
        ]);
        
        $this->handleValidationErrors($validator, 'index.php');
        
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        try {
            $user = $this->userModel->authenticate($username, $password);
            
            if ($user) {
                // Start secure user session
                SessionManager::startUserSession($user);
                
                // Redirect based on user role
                if ($user['admin'] == 1 || $user['admin'] == 2) {
                    $this->redirect('admin.php');
                } else {
                    $this->redirect('halaman_user.php');
                }
            } else {
                flash('err', 'Username atau Password salah!');
                $this->redirect('index.php');
            }
        } catch (Exception $e) {
            $this->handleException($e, 'Terjadi kesalahan saat login.');
        }
    }
    
    /**
     * Handle logout
     */
    public function logout() {
        if (is_logged_in()) {
            $userId = session_user_id();
            $username = session_user_name();
            
            log_activity('logout', 'User logged out', [
                'user_id' => $userId,
                'username' => $username
            ]);
        }
        
        SessionManager::logout();
    }
    
    /**
     * Handle registration
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processRegistration();
        }
        
        // Show registration form (handled by register.php)
    }
    
    /**
     * Process registration form submission
     */
    private function processRegistration() {
        $this->validateCSRF('register');
        
        // Validate input
        $validator = $this->validateInput($_POST, [
            'username' => [
                'required' => 'Username wajib diisi.',
                'min_length' => ['length' => 3, 'message' => 'Username minimal 3 karakter.'],
                'max_length' => ['length' => 30, 'message' => 'Username maksimal 30 karakter.'],
                'username' => 'Username hanya boleh berisi huruf, angka, dan underscore.'
            ],
            'password' => [
                'required' => 'Password wajib diisi.',
                'min_length' => ['length' => env_int('PASSWORD_MIN_LENGTH', 8), 'message' => 'Password minimal 8 karakter.']
            ],
            'nama' => [
                'required' => 'Nama lengkap wajib diisi.',
                'max_length' => ['length' => 100, 'message' => 'Nama maksimal 100 karakter.']
            ],
            'nip' => [
                'required' => 'NIP wajib diisi.',
                'nip' => 'Format NIP tidak valid (harus 18 digit angka).'
            ]
        ]);
        
        $this->handleValidationErrors($validator, 'register.php');
        
        try {
            $userData = [
                'username' => $_POST['username'],
                'password' => $_POST['password'],
                'nama' => $_POST['nama'],
                'nip' => $_POST['nip'],
                'admin' => 0 // Regular user by default
            ];
            
            $userId = $this->userModel->createUser($userData);
            
            if ($userId) {
                flash('success', 'Registrasi berhasil! Silakan login dengan akun Anda.');
                $this->redirect('index.php');
            } else {
                flash('err', 'Gagal melakukan registrasi. Silakan coba lagi.');
                $this->redirect('register.php');
            }
        } catch (Exception $e) {
            flash('err', $e->getMessage());
            $this->redirect('register.php');
        }
    }
    
    /**
     * Change password
     */
    public function changePassword() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processChangePassword();
        }
    }
    
    /**
     * Process change password form
     */
    private function processChangePassword() {
        $this->validateCSRF('change_password');
        
        // Validate input
        $validator = $this->validateInput($_POST, [
            'current_password' => [
                'required' => 'Password saat ini wajib diisi.'
            ],
            'new_password' => [
                'required' => 'Password baru wajib diisi.',
                'min_length' => ['length' => env_int('PASSWORD_MIN_LENGTH', 8), 'message' => 'Password minimal 8 karakter.']
            ],
            'confirm_password' => [
                'required' => 'Konfirmasi password wajib diisi.'
            ]
        ]);
        
        $this->handleValidationErrors($validator);
        
        // Check if new password matches confirmation
        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            flash('err', 'Password baru dan konfirmasi password tidak cocok.');
            $this->redirectBack();
        }
        
        try {
            $userId = session_user_id();
            
            // Verify current password
            if (!$this->userModel->verifyPassword($userId, $_POST['current_password'])) {
                flash('err', 'Password saat ini salah.');
                $this->redirectBack();
            }
            
            // Update password
            $result = $this->userModel->updatePassword($userId, $_POST['new_password']);
            
            if ($result) {
                flash('success', 'Password berhasil diubah.');
            } else {
                flash('err', 'Gagal mengubah password.');
            }
            
            $this->redirectBack();
        } catch (Exception $e) {
            $this->handleException($e, 'Terjadi kesalahan saat mengubah password.');
        }
    }
    
    /**
     * Check username availability (AJAX)
     */
    public function checkUsername() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        $username = $_POST['username'] ?? '';
        $excludeUserId = $_POST['exclude_user_id'] ?? null;
        
        if (empty($username)) {
            $this->jsonResponse(['available' => false, 'message' => 'Username tidak boleh kosong']);
        }
        
        try {
            $available = $this->userModel->isUsernameAvailable($username, $excludeUserId);
            $this->jsonResponse([
                'available' => $available,
                'message' => $available ? 'Username tersedia' : 'Username sudah digunakan'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse(['error' => 'Terjadi kesalahan sistem'], 500);
        }
    }
    
    /**
     * Forgot password (placeholder for future implementation)
     */
    public function forgotPassword() {
        // TODO: Implement forgot password functionality
        flash('info', 'Fitur lupa password belum tersedia. Silakan hubungi administrator.');
        $this->redirect('index.php');
    }
    
    /**
     * Reset password (placeholder for future implementation)
     */
    public function resetPassword() {
        // TODO: Implement reset password functionality
        flash('info', 'Fitur reset password belum tersedia. Silakan hubungi administrator.');
        $this->redirect('index.php');
    }
}
?>
