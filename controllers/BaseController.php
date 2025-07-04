<?php
/**
 * Base Controller Class
 * Provides common functionality for all controllers
 */

abstract class BaseController {
    protected $db;
    protected $user;
    protected $isAdmin;
    
    public function __construct($db) {
        $this->db = $db;
        $this->initializeUser();
    }
    
    /**
     * Initialize current user
     */
    protected function initializeUser() {
        if (is_logged_in()) {
            $this->user = [
                'id' => session_user_id(),
                'name' => session_user_name(),
                'is_admin' => is_admin()
            ];
            $this->isAdmin = is_admin();
        } else {
            $this->user = null;
            $this->isAdmin = false;
        }
    }
    
    /**
     * Check if user is authenticated
     */
    protected function requireAuth() {
        if (!is_logged_in()) {
            $this->redirectToLogin();
        }
    }
    
    /**
     * Check if user is admin
     */
    protected function requireAdmin() {
        $this->requireAuth();
        if (!is_admin()) {
            $this->accessDenied();
        }
    }
    
    /**
     * Redirect to login page
     */
    protected function redirectToLogin() {
        flash('err', 'Silakan login terlebih dahulu.');
        header("Location: index.php");
        exit();
    }
    
    /**
     * Show access denied error
     */
    protected function accessDenied() {
        flash('err', 'Anda tidak memiliki akses untuk halaman ini.');
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit();
    }
    
    /**
     * Validate CSRF token
     */
    protected function validateCSRF($formName = 'default') {
        if (!csrf_validate($formName)) {
            flash('err', 'Token keamanan tidak valid. Silakan coba lagi.');
            $this->redirectBack();
        }
    }
    
    /**
     * Redirect back to previous page
     */
    protected function redirectBack() {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit();
    }
    
    /**
     * Redirect to specific URL
     */
    protected function redirect($url) {
        header("Location: $url");
        exit();
    }
    
    /**
     * Validate input data
     */
    protected function validateInput($data, $rules) {
        $validator = validate($data);
        
        foreach ($rules as $field => $fieldRules) {
            foreach ($fieldRules as $rule => $params) {
                switch ($rule) {
                    case 'required':
                        $validator->required($field, $params);
                        break;
                    case 'min_length':
                        $validator->minLength($field, $params['length'], $params['message'] ?? null);
                        break;
                    case 'max_length':
                        $validator->maxLength($field, $params['length'], $params['message'] ?? null);
                        break;
                    case 'email':
                        $validator->email($field, $params);
                        break;
                    case 'numeric':
                        $validator->numeric($field, $params);
                        break;
                    case 'username':
                        $validator->username($field, $params);
                        break;
                    case 'nip':
                        $validator->nip($field, $params);
                        break;
                }
            }
        }
        
        return $validator;
    }
    
    /**
     * Handle validation errors
     */
    protected function handleValidationErrors($validator, $redirectUrl = null) {
        if ($validator->fails()) {
            flash('err', $validator->getFirstError());
            if ($redirectUrl) {
                $this->redirect($redirectUrl);
            } else {
                $this->redirectBack();
            }
        }
    }
    
    /**
     * Get paginated data
     */
    protected function getPaginatedData($model, $page, $perPage = 10, $search = null) {
        $page = max(1, (int)$page);
        $perPage = max(1, min(100, (int)$perPage)); // Limit max per page
        
        if (method_exists($model, 'paginate')) {
            return $model->paginate($page, $perPage, $search);
        }
        
        // Fallback for models without paginate method
        $offset = ($page - 1) * $perPage;
        $data = $model->all($perPage, $offset);
        $total = $model->count();
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }
    
    /**
     * Render JSON response
     */
    protected function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
    
    /**
     * Handle file upload
     */
    protected function handleFileUpload($file, $type = 'image', $customName = null) {
        if (empty($file['name'])) {
            return null;
        }
        
        $result = upload_file($file, $type, $customName);
        
        if (!$result['success']) {
            throw new Exception($result['error']);
        }
        
        return $result;
    }
    
    /**
     * Log user activity
     */
    protected function logActivity($action, $description, $data = []) {
        $data['user_id'] = $this->user['id'] ?? null;
        log_activity($action, $description, $data);
    }
    
    /**
     * Check ownership of resource
     */
    protected function checkOwnership($resourceUserId, $allowAdmin = true) {
        if ($allowAdmin && $this->isAdmin) {
            return true;
        }
        
        return $this->user && $this->user['id'] == $resourceUserId;
    }
    
    /**
     * Sanitize output for display
     */
    protected function sanitizeOutput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeOutput'], $data);
        }
        
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Format date for display
     */
    protected function formatDate($date, $format = 'd/m/Y') {
        if (empty($date)) {
            return '-';
        }
        
        return date($format, strtotime($date));
    }
    
    /**
     * Format datetime for display
     */
    protected function formatDateTime($datetime, $format = 'd/m/Y H:i') {
        if (empty($datetime)) {
            return '-';
        }
        
        return date($format, strtotime($datetime));
    }
    
    /**
     * Generate pagination links
     */
    protected function generatePaginationLinks($paginationData, $baseUrl) {
        $links = [];
        $currentPage = $paginationData['current_page'];
        $lastPage = $paginationData['last_page'];
        
        // Previous link
        if ($currentPage > 1) {
            $links['prev'] = $baseUrl . '&page=' . ($currentPage - 1);
        }
        
        // Page links
        $start = max(1, $currentPage - 2);
        $end = min($lastPage, $currentPage + 2);
        
        for ($i = $start; $i <= $end; $i++) {
            $links['pages'][$i] = [
                'url' => $baseUrl . '&page=' . $i,
                'active' => $i == $currentPage
            ];
        }
        
        // Next link
        if ($currentPage < $lastPage) {
            $links['next'] = $baseUrl . '&page=' . ($currentPage + 1);
        }
        
        return $links;
    }
    
    /**
     * Handle exceptions
     */
    protected function handleException($e, $defaultMessage = 'Terjadi kesalahan sistem.') {
        app_log('error', $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        if (env_bool('APP_DEBUG', false)) {
            flash('err', $e->getMessage());
        } else {
            flash('err', $defaultMessage);
        }
        
        $this->redirectBack();
    }
}
?>
