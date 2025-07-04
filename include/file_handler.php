<?php
/**
 * Secure File Handler Class
 * Provides secure file upload and management functionality
 */

class SecureFileHandler {
    private static $allowed_types = [
        'image' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'gif'],
            'mime_types' => ['image/jpeg', 'image/png', 'image/gif'],
            'max_size' => 2097152 // 2MB
        ],
        'document' => [
            'extensions' => ['pdf', 'doc', 'docx'],
            'mime_types' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'max_size' => 5242880 // 5MB
        ]
    ];
    
    /**
     * Upload file securely
     */
    public static function uploadFile($file, $type = 'image', $custom_name = null) {
        if (!isset(self::$allowed_types[$type])) {
            return ['success' => false, 'error' => 'Tipe file tidak didukung.'];
        }
        
        $config = self::$allowed_types[$type];
        
        // Validate file
        $validator = validate_file($file);
        $validator->validate()
                 ->maxSize($config['max_size'])
                 ->allowedExtensions($config['extensions'])
                 ->allowedMimeTypes($config['mime_types']);
        
        if (!$validator->passes()) {
            return ['success' => false, 'error' => implode(' ', $validator->getErrors())];
        }
        
        // Generate secure filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = $custom_name ? $custom_name : self::generateSecureFilename();
        $secure_filename = $filename . '.' . $extension;
        
        // Determine upload path
        $upload_dir = self::getUploadPath($type);
        $upload_path = $upload_dir . $secure_filename;
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                return ['success' => false, 'error' => 'Gagal membuat direktori upload.'];
            }
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            // Set proper file permissions
            chmod($upload_path, 0644);
            
            return [
                'success' => true,
                'filename' => $secure_filename,
                'path' => $upload_path,
                'url' => self::getFileUrl($type, $secure_filename)
            ];
        } else {
            return ['success' => false, 'error' => 'Gagal mengupload file.'];
        }
    }
    
    /**
     * Delete file securely
     */
    public static function deleteFile($filepath) {
        if (empty($filepath) || !file_exists($filepath)) {
            return false;
        }
        
        // Ensure file is within allowed upload directories
        $real_path = realpath($filepath);
        $upload_base = realpath(env('UPLOAD_PATH', 'upload/'));
        
        if (strpos($real_path, $upload_base) !== 0) {
            return false; // File is outside upload directory
        }
        
        return unlink($filepath);
    }
    
    /**
     * Generate secure filename
     */
    private static function generateSecureFilename() {
        return uniqid() . '_' . time() . '_' . bin2hex(random_bytes(8));
    }
    
    /**
     * Get upload path for file type
     */
    private static function getUploadPath($type) {
        $base_path = env('UPLOAD_PATH', 'upload/');
        return $base_path . $type . '/';
    }
    
    /**
     * Get file URL
     */
    private static function getFileUrl($type, $filename) {
        $base_url = env('UPLOAD_URL', 'upload/');
        return $base_url . $type . '/' . $filename;
    }
    
    /**
     * Validate image file
     */
    public static function validateImage($filepath) {
        if (!file_exists($filepath)) {
            return false;
        }
        
        // Check if it's a valid image
        $image_info = getimagesize($filepath);
        if ($image_info === false) {
            return false;
        }
        
        // Check MIME type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($image_info['mime'], $allowed_types)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Resize image if needed
     */
    public static function resizeImage($filepath, $max_width = 800, $max_height = 600, $quality = 85) {
        if (!self::validateImage($filepath)) {
            return false;
        }
        
        $image_info = getimagesize($filepath);
        $width = $image_info[0];
        $height = $image_info[1];
        $mime = $image_info['mime'];
        
        // Check if resize is needed
        if ($width <= $max_width && $height <= $max_height) {
            return true; // No resize needed
        }
        
        // Calculate new dimensions
        $ratio = min($max_width / $width, $max_height / $height);
        $new_width = intval($width * $ratio);
        $new_height = intval($height * $ratio);
        
        // Create image resource
        switch ($mime) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($filepath);
                break;
            case 'image/png':
                $source = imagecreatefrompng($filepath);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($filepath);
                break;
            default:
                return false;
        }
        
        if (!$source) {
            return false;
        }
        
        // Create new image
        $destination = imagecreatetruecolor($new_width, $new_height);
        
        // Preserve transparency for PNG and GIF
        if ($mime == 'image/png' || $mime == 'image/gif') {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
            $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
            imagefilledrectangle($destination, 0, 0, $new_width, $new_height, $transparent);
        }
        
        // Resize image
        imagecopyresampled($destination, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        
        // Save resized image
        switch ($mime) {
            case 'image/jpeg':
                $result = imagejpeg($destination, $filepath, $quality);
                break;
            case 'image/png':
                $result = imagepng($destination, $filepath, 9);
                break;
            case 'image/gif':
                $result = imagegif($destination, $filepath);
                break;
            default:
                $result = false;
        }
        
        // Clean up memory
        imagedestroy($source);
        imagedestroy($destination);
        
        return $result;
    }
    
    /**
     * Get file size in human readable format
     */
    public static function formatFileSize($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Scan uploaded file for malware (basic check)
     */
    public static function scanFile($filepath) {
        if (!file_exists($filepath)) {
            return false;
        }
        
        // Basic checks for malicious content
        $content = file_get_contents($filepath, false, null, 0, 1024); // Read first 1KB
        
        // Check for PHP tags
        if (strpos($content, '<?php') !== false || strpos($content, '<?=') !== false) {
            return false;
        }
        
        // Check for script tags
        if (stripos($content, '<script') !== false) {
            return false;
        }
        
        // Check for executable signatures
        $signatures = [
            "\x4D\x5A", // PE executable
            "\x7F\x45\x4C\x46", // ELF executable
            "\xCA\xFE\xBA\xBE", // Java class file
        ];
        
        foreach ($signatures as $signature) {
            if (strpos($content, $signature) === 0) {
                return false;
            }
        }
        
        return true;
    }
}

// Helper functions
if (!function_exists('upload_file')) {
    function upload_file($file, $type = 'image', $custom_name = null) {
        return SecureFileHandler::uploadFile($file, $type, $custom_name);
    }
}

if (!function_exists('delete_file')) {
    function delete_file($filepath) {
        return SecureFileHandler::deleteFile($filepath);
    }
}
?>
