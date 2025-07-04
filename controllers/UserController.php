<?php
require_once 'BaseController.php';
require_once 'models/User.php';

/**
 * User Controller
 * Handles user management operations
 */
class UserController extends BaseController {
    private $userModel;
    
    public function __construct($db) {
        parent::__construct($db);
        $this->userModel = new User($db);
    }
    
    /**
     * List all users (admin only)
     */
    public function index() {
        $this->requireAdmin();
        
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? null;
        
        try {
            $users = $this->getPaginatedData($this->userModel, $page, 10, $search);
            
            // Remove password from results for security
            foreach ($users['data'] as &$user) {
                unset($user['password']);
            }
            
            return $users;
        } catch (Exception $e) {
            $this->handleException($e, 'Gagal memuat data user.');
        }
    }
    
    /**
     * Show user details
     */
    public function show($id) {
        $this->requireAuth();
        
        try {
            $user = $this->userModel->find($id);
            
            if (!$user) {
                flash('err', 'User tidak ditemukan.');
                $this->redirectBack();
            }
            
            // Check if user can view this profile
            if (!$this->checkOwnership($user['id_user'])) {
                $this->accessDenied();
            }
            
            // Remove password for security
            unset($user['password']);
            
            return $user;
        } catch (Exception $e) {
            $this->handleException($e, 'Gagal memuat data user.');
        }
    }
    
    /**
     * Create new user (admin only)
     */
    public function create() {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processCreate();
        }
        
        // Show create form
    }
    
    /**
     * Process create user form
     */
    private function processCreate() {
        $this->validateCSRF('tambah_user');
        
        // Validate input
        $validator = $this->validateInput($_REQUEST, [
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
            ],
            'admin' => [
                'required' => 'Level admin wajib dipilih.',
                'numeric' => 'Level admin harus berupa angka.'
            ]
        ]);
        
        $this->handleValidationErrors($validator, './admin.php?page=usr');
        
        try {
            $userData = [
                'username' => $_REQUEST['username'],
                'password' => $_REQUEST['password'],
                'nama' => $_REQUEST['nama'],
                'nip' => $_REQUEST['nip'],
                'admin' => (int)$_REQUEST['admin']
            ];
            
            $userId = $this->userModel->createUser($userData);
            
            if ($userId) {
                flash('succAdd', 'SUKSES! Data user berhasil ditambahkan');
                $this->redirect('./admin.php?page=usr');
            } else {
                flash('errQ', 'ERROR! Ada masalah dengan query');
                $this->redirectBack();
            }
        } catch (Exception $e) {
            flash('errUser', $e->getMessage());
            $this->redirectBack();
        }
    }
    
    /**
     * Edit user
     */
    public function edit($id) {
        $this->requireAuth();
        
        try {
            $user = $this->userModel->find($id);
            
            if (!$user) {
                flash('err', 'User tidak ditemukan.');
                $this->redirectBack();
            }
            
            // Check if user can edit this profile
            if (!$this->checkOwnership($user['id_user'])) {
                $this->accessDenied();
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->processEdit($id);
            }
            
            // Remove password for security
            unset($user['password']);
            
            return $user;
        } catch (Exception $e) {
            $this->handleException($e, 'Gagal memuat data user.');
        }
    }
    
    /**
     * Process edit user form
     */
    private function processEdit($id) {
        $this->validateCSRF('edit_user');
        
        // Validate input
        $validator = $this->validateInput($_POST, [
            'nama' => [
                'required' => 'Nama lengkap wajib diisi.',
                'max_length' => ['length' => 100, 'message' => 'Nama maksimal 100 karakter.']
            ],
            'nip' => [
                'required' => 'NIP wajib diisi.',
                'nip' => 'Format NIP tidak valid (harus 18 digit angka).'
            ]
        ]);
        
        // Only admin can change username and admin level
        if ($this->isAdmin) {
            $validator->username('username', 'Format username tidak valid.');
            $validator->numeric('admin', 'Level admin harus berupa angka.');
        }
        
        $this->handleValidationErrors($validator);
        
        try {
            $updateData = [
                'nama' => $_POST['nama'],
                'nip' => $_POST['nip']
            ];
            
            // Only admin can update these fields
            if ($this->isAdmin) {
                if (!empty($_POST['username'])) {
                    $updateData['username'] = $_POST['username'];
                }
                if (isset($_POST['admin'])) {
                    $updateData['admin'] = (int)$_POST['admin'];
                }
            }
            
            $result = $this->userModel->updateProfile($id, $updateData);
            
            if ($result) {
                flash('success', 'Data user berhasil diperbarui.');
            } else {
                flash('err', 'Gagal memperbarui data user.');
            }
            
            $this->redirectBack();
        } catch (Exception $e) {
            flash('err', $e->getMessage());
            $this->redirectBack();
        }
    }
    
    /**
     * Delete user (admin only)
     */
    public function delete($id) {
        $this->requireAdmin();
        
        try {
            $user = $this->userModel->find($id);
            
            if (!$user) {
                flash('err', 'User tidak ditemukan.');
                $this->redirectBack();
            }
            
            // Prevent deleting own account
            if ($user['id_user'] == session_user_id()) {
                flash('err', 'Tidak dapat menghapus akun sendiri.');
                $this->redirectBack();
            }
            
            $result = $this->userModel->delete($id);
            
            if ($result) {
                log_activity('user_deleted', 'User deleted', [
                    'deleted_user_id' => $id,
                    'deleted_username' => $user['username']
                ]);
                flash('success', 'User berhasil dihapus.');
            } else {
                flash('err', 'Gagal menghapus user.');
            }
            
            $this->redirect('./admin.php?page=usr');
        } catch (Exception $e) {
            $this->handleException($e, 'Gagal menghapus user.');
        }
    }
    
    /**
     * Activate/Deactivate user (admin only)
     */
    public function toggleStatus($id) {
        $this->requireAdmin();
        
        try {
            $user = $this->userModel->find($id);
            
            if (!$user) {
                flash('err', 'User tidak ditemukan.');
                $this->redirectBack();
            }
            
            // Prevent deactivating own account
            if ($user['id_user'] == session_user_id()) {
                flash('err', 'Tidak dapat mengubah status akun sendiri.');
                $this->redirectBack();
            }
            
            if ($user['is_active']) {
                $result = $this->userModel->deactivateUser($id);
                $message = 'User berhasil dinonaktifkan.';
            } else {
                $result = $this->userModel->activateUser($id);
                $message = 'User berhasil diaktifkan.';
            }
            
            if ($result) {
                flash('success', $message);
            } else {
                flash('err', 'Gagal mengubah status user.');
            }
            
            $this->redirectBack();
        } catch (Exception $e) {
            $this->handleException($e, 'Gagal mengubah status user.');
        }
    }
    
    /**
     * Search users (AJAX)
     */
    public function search() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        $keyword = $_GET['q'] ?? '';
        $limit = min(20, (int)($_GET['limit'] ?? 10));
        
        try {
            $users = $this->userModel->search($keyword, $limit);
            
            // Remove password from results
            foreach ($users as &$user) {
                unset($user['password']);
            }
            
            $this->jsonResponse(['users' => $users]);
        } catch (Exception $e) {
            $this->jsonResponse(['error' => 'Terjadi kesalahan sistem'], 500);
        }
    }
    
    /**
     * Get user statistics (admin only)
     */
    public function statistics() {
        $this->requireAdmin();
        
        try {
            $stats = $this->userModel->getStatistics();
            return $stats;
        } catch (Exception $e) {
            $this->handleException($e, 'Gagal memuat statistik user.');
        }
    }
    
    /**
     * Export users to CSV (admin only)
     */
    public function export() {
        $this->requireAdmin();
        
        try {
            $users = $this->userModel->getActiveUsers();
            
            // Remove password from export
            foreach ($users as &$user) {
                unset($user['password']);
            }
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="users_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($output, ['ID', 'Username', 'Nama', 'NIP', 'Admin', 'Dibuat', 'Login Terakhir']);
            
            // CSV data
            foreach ($users as $user) {
                fputcsv($output, [
                    $user['id_user'],
                    $user['username'],
                    $user['nama'],
                    $user['nip'],
                    $user['admin'] ? 'Ya' : 'Tidak',
                    $this->formatDateTime($user['created_at']),
                    $this->formatDateTime($user['last_login'])
                ]);
            }
            
            fclose($output);
            exit();
        } catch (Exception $e) {
            $this->handleException($e, 'Gagal mengekspor data user.');
        }
    }
}
?>
