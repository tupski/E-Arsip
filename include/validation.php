<?php
/**
 * Input Validation and Sanitization Class
 * Provides comprehensive input validation and sanitization
 */

class InputValidator {
    private $errors = [];
    private $data = [];
    
    /**
     * Constructor
     */
    public function __construct($data = []) {
        $this->data = $data;
        $this->errors = [];
    }
    
    /**
     * Validate required field
     */
    public function required($field, $message = null) {
        if (!isset($this->data[$field]) || empty(trim($this->data[$field]))) {
            $this->errors[$field] = $message ?? "Field {$field} wajib diisi.";
        }
        return $this;
    }
    
    /**
     * Validate minimum length
     */
    public function minLength($field, $min, $message = null) {
        if (isset($this->data[$field]) && strlen(trim($this->data[$field])) < $min) {
            $this->errors[$field] = $message ?? "Field {$field} minimal {$min} karakter.";
        }
        return $this;
    }
    
    /**
     * Validate maximum length
     */
    public function maxLength($field, $max, $message = null) {
        if (isset($this->data[$field]) && strlen(trim($this->data[$field])) > $max) {
            $this->errors[$field] = $message ?? "Field {$field} maksimal {$max} karakter.";
        }
        return $this;
    }
    
    /**
     * Validate email format
     */
    public function email($field, $message = null) {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?? "Format email tidak valid.";
        }
        return $this;
    }
    
    /**
     * Validate numeric value
     */
    public function numeric($field, $message = null) {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = $message ?? "Field {$field} harus berupa angka.";
        }
        return $this;
    }
    
    /**
     * Validate integer value
     */
    public function integer($field, $message = null) {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_INT)) {
            $this->errors[$field] = $message ?? "Field {$field} harus berupa bilangan bulat.";
        }
        return $this;
    }
    
    /**
     * Validate alphanumeric
     */
    public function alphanumeric($field, $message = null) {
        if (isset($this->data[$field]) && !ctype_alnum($this->data[$field])) {
            $this->errors[$field] = $message ?? "Field {$field} hanya boleh berisi huruf dan angka.";
        }
        return $this;
    }
    
    /**
     * Validate username format
     */
    public function username($field, $message = null) {
        if (isset($this->data[$field])) {
            $username = $this->data[$field];
            if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
                $this->errors[$field] = $message ?? "Username hanya boleh berisi huruf, angka, dan underscore (3-30 karakter).";
            }
        }
        return $this;
    }
    
    /**
     * Validate NIP format (Indonesian civil servant ID)
     */
    public function nip($field, $message = null) {
        if (isset($this->data[$field])) {
            $nip = $this->data[$field];
            if (!preg_match('/^[0-9]{18}$/', $nip)) {
                $this->errors[$field] = $message ?? "NIP harus berupa 18 digit angka.";
            }
        }
        return $this;
    }
    
    /**
     * Validate date format
     */
    public function date($field, $format = 'Y-m-d', $message = null) {
        if (isset($this->data[$field])) {
            $date = DateTime::createFromFormat($format, $this->data[$field]);
            if (!$date || $date->format($format) !== $this->data[$field]) {
                $this->errors[$field] = $message ?? "Format tanggal tidak valid.";
            }
        }
        return $this;
    }
    
    /**
     * Custom validation with callback
     */
    public function custom($field, $callback, $message = null) {
        if (isset($this->data[$field])) {
            if (!call_user_func($callback, $this->data[$field])) {
                $this->errors[$field] = $message ?? "Validasi gagal untuk field {$field}.";
            }
        }
        return $this;
    }
    
    /**
     * Check if validation passed
     */
    public function passes() {
        return empty($this->errors);
    }
    
    /**
     * Check if validation failed
     */
    public function fails() {
        return !$this->passes();
    }
    
    /**
     * Get all errors
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Get first error
     */
    public function getFirstError() {
        return !empty($this->errors) ? reset($this->errors) : null;
    }
    
    /**
     * Get sanitized data
     */
    public function getSanitizedData() {
        $sanitized = [];
        foreach ($this->data as $key => $value) {
            $sanitized[$key] = $this->sanitize($value);
        }
        return $sanitized;
    }
    
    /**
     * Sanitize input data
     */
    public function sanitize($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitize'], $input);
        }
        
        // Remove null bytes
        $input = str_replace(chr(0), '', $input);
        
        // Trim whitespace
        $input = trim($input);
        
        // Convert special characters to HTML entities
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        return $input;
    }
    
    /**
     * Sanitize for database (without HTML encoding)
     */
    public function sanitizeForDb($input, $connection) {
        if (is_array($input)) {
            return array_map(function($item) use ($connection) {
                return $this->sanitizeForDb($item, $connection);
            }, $input);
        }
        
        // Remove null bytes
        $input = str_replace(chr(0), '', $input);
        
        // Trim whitespace
        $input = trim($input);
        
        // Escape for database
        return mysqli_real_escape_string($connection, $input);
    }
}

/**
 * File Upload Validator
 */
class FileValidator {
    private $file;
    private $errors = [];
    
    public function __construct($file) {
        $this->file = $file;
        $this->errors = [];
    }
    
    /**
     * Validate file upload
     */
    public function validate() {
        // Check if file was uploaded
        if (!isset($this->file['tmp_name']) || empty($this->file['tmp_name'])) {
            $this->errors[] = "Tidak ada file yang diupload.";
            return $this;
        }
        
        // Check for upload errors
        if ($this->file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = "Terjadi kesalahan saat upload file.";
            return $this;
        }
        
        return $this;
    }
    
    /**
     * Validate file size
     */
    public function maxSize($maxSize, $message = null) {
        if (isset($this->file['size']) && $this->file['size'] > $maxSize) {
            $this->errors[] = $message ?? "Ukuran file terlalu besar. Maksimal " . $this->formatBytes($maxSize) . ".";
        }
        return $this;
    }
    
    /**
     * Validate file extension
     */
    public function allowedExtensions($extensions, $message = null) {
        if (isset($this->file['name'])) {
            $fileExt = strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION));
            if (!in_array($fileExt, $extensions)) {
                $this->errors[] = $message ?? "Ekstensi file tidak diizinkan. Hanya: " . implode(', ', $extensions);
            }
        }
        return $this;
    }
    
    /**
     * Validate MIME type
     */
    public function allowedMimeTypes($mimeTypes, $message = null) {
        if (isset($this->file['tmp_name'])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $this->file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $mimeTypes)) {
                $this->errors[] = $message ?? "Tipe file tidak diizinkan.";
            }
        }
        return $this;
    }
    
    /**
     * Check if validation passed
     */
    public function passes() {
        return empty($this->errors);
    }
    
    /**
     * Get errors
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

// Helper functions
if (!function_exists('validate')) {
    function validate($data) {
        return new InputValidator($data);
    }
}

if (!function_exists('validate_file')) {
    function validate_file($file) {
        return new FileValidator($file);
    }
}
?>
