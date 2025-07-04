<?php
/**
 * View Helper Functions
 * Provides utility functions for views and templates
 */

class ViewHelper {
    /**
     * Escape HTML output
     */
    public static function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Format date for display
     */
    public static function formatDate($date, $format = 'd/m/Y') {
        if (empty($date) || $date === '0000-00-00') {
            return '-';
        }
        
        return date($format, strtotime($date));
    }
    
    /**
     * Format datetime for display
     */
    public static function formatDateTime($datetime, $format = 'd/m/Y H:i') {
        if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
            return '-';
        }
        
        return date($format, strtotime($datetime));
    }
    
    /**
     * Format currency
     */
    public static function formatCurrency($amount, $currency = 'IDR') {
        if ($currency === 'IDR') {
            return 'Rp ' . number_format($amount, 0, ',', '.');
        }
        
        return $currency . ' ' . number_format($amount, 2);
    }
    
    /**
     * Format file size
     */
    public static function formatFileSize($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Truncate text
     */
    public static function truncate($text, $length = 100, $suffix = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . $suffix;
    }
    
    /**
     * Generate pagination HTML
     */
    public static function pagination($paginationData, $baseUrl) {
        if ($paginationData['last_page'] <= 1) {
            return '';
        }
        
        $currentPage = $paginationData['current_page'];
        $lastPage = $paginationData['last_page'];
        $total = $paginationData['total'];
        $perPage = $paginationData['per_page'];
        
        $html = '<ul class="pagination">';
        
        // Previous button
        if ($currentPage > 1) {
            $prevUrl = $baseUrl . '&page=' . ($currentPage - 1);
            $html .= '<li class="waves-effect"><a href="' . $prevUrl . '"><i class="material-icons">chevron_left</i></a></li>';
        } else {
            $html .= '<li class="disabled"><a href="#!"><i class="material-icons">chevron_left</i></a></li>';
        }
        
        // Page numbers
        $start = max(1, $currentPage - 2);
        $end = min($lastPage, $currentPage + 2);
        
        if ($start > 1) {
            $html .= '<li class="waves-effect"><a href="' . $baseUrl . '&page=1">1</a></li>';
            if ($start > 2) {
                $html .= '<li class="disabled"><a href="#!">...</a></li>';
            }
        }
        
        for ($i = $start; $i <= $end; $i++) {
            $pageUrl = $baseUrl . '&page=' . $i;
            $activeClass = $i == $currentPage ? 'active' : 'waves-effect';
            $html .= '<li class="' . $activeClass . '"><a href="' . $pageUrl . '">' . $i . '</a></li>';
        }
        
        if ($end < $lastPage) {
            if ($end < $lastPage - 1) {
                $html .= '<li class="disabled"><a href="#!">...</a></li>';
            }
            $html .= '<li class="waves-effect"><a href="' . $baseUrl . '&page=' . $lastPage . '">' . $lastPage . '</a></li>';
        }
        
        // Next button
        if ($currentPage < $lastPage) {
            $nextUrl = $baseUrl . '&page=' . ($currentPage + 1);
            $html .= '<li class="waves-effect"><a href="' . $nextUrl . '"><i class="material-icons">chevron_right</i></a></li>';
        } else {
            $html .= '<li class="disabled"><a href="#!"><i class="material-icons">chevron_right</i></a></li>';
        }
        
        $html .= '</ul>';
        
        // Add pagination info
        $start = ($currentPage - 1) * $perPage + 1;
        $end = min($currentPage * $perPage, $total);
        $html .= '<p class="pagination-info">Menampilkan ' . $start . ' sampai ' . $end . ' dari ' . $total . ' data</p>';
        
        return $html;
    }
    
    /**
     * Generate breadcrumb HTML
     */
    public static function breadcrumb($items) {
        if (empty($items)) {
            return '';
        }
        
        $html = '<nav><div class="nav-wrapper"><div class="col s12">';
        
        foreach ($items as $index => $item) {
            if ($index > 0) {
                $html .= '<i class="material-icons">chevron_right</i>';
            }
            
            if (isset($item['url']) && $index < count($items) - 1) {
                $html .= '<a href="' . $item['url'] . '" class="breadcrumb">' . self::escape($item['title']) . '</a>';
            } else {
                $html .= '<span class="breadcrumb">' . self::escape($item['title']) . '</span>';
            }
        }
        
        $html .= '</div></div></nav>';
        
        return $html;
    }
    
    /**
     * Generate alert HTML
     */
    public static function alert($type, $message, $dismissible = true) {
        $alertClass = 'card-panel ';
        
        switch ($type) {
            case 'success':
                $alertClass .= 'green lighten-4 green-text text-darken-4';
                $icon = 'check_circle';
                break;
            case 'error':
            case 'err':
                $alertClass .= 'red lighten-4 red-text text-darken-4';
                $icon = 'error';
                break;
            case 'warning':
                $alertClass .= 'orange lighten-4 orange-text text-darken-4';
                $icon = 'warning';
                break;
            case 'info':
                $alertClass .= 'blue lighten-4 blue-text text-darken-4';
                $icon = 'info';
                break;
            default:
                $alertClass .= 'grey lighten-4 grey-text text-darken-4';
                $icon = 'info';
        }
        
        $html = '<div class="' . $alertClass . '">';
        $html .= '<i class="material-icons left">' . $icon . '</i>';
        $html .= self::escape($message);
        
        if ($dismissible) {
            $html .= '<a href="#!" class="right" onclick="this.parentElement.style.display=\'none\'"><i class="material-icons">close</i></a>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Generate table HTML
     */
    public static function table($headers, $data, $options = []) {
        $responsive = $options['responsive'] ?? true;
        $striped = $options['striped'] ?? true;
        $highlight = $options['highlight'] ?? true;
        
        $tableClass = '';
        if ($striped) $tableClass .= ' striped';
        if ($highlight) $tableClass .= ' highlight';
        
        $html = '';
        if ($responsive) {
            $html .= '<div class="responsive-table">';
        }
        
        $html .= '<table class="' . trim($tableClass) . '">';
        
        // Headers
        $html .= '<thead><tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . self::escape($header) . '</th>';
        }
        $html .= '</tr></thead>';
        
        // Data
        $html .= '<tbody>';
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . self::escape($cell) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        
        $html .= '</table>';
        
        if ($responsive) {
            $html .= '</div>';
        }
        
        return $html;
    }
    
    /**
     * Generate form input HTML
     */
    public static function input($type, $name, $value = '', $options = []) {
        $id = $options['id'] ?? $name;
        $class = $options['class'] ?? '';
        $placeholder = $options['placeholder'] ?? '';
        $required = $options['required'] ?? false;
        $label = $options['label'] ?? '';
        
        $html = '<div class="input-field">';
        
        if ($type === 'textarea') {
            $html .= '<textarea id="' . $id . '" name="' . $name . '" class="materialize-textarea ' . $class . '"';
            if ($required) $html .= ' required';
            $html .= '>' . self::escape($value) . '</textarea>';
        } else {
            $html .= '<input id="' . $id . '" name="' . $name . '" type="' . $type . '" class="validate ' . $class . '"';
            $html .= ' value="' . self::escape($value) . '"';
            if ($placeholder) $html .= ' placeholder="' . self::escape($placeholder) . '"';
            if ($required) $html .= ' required';
            $html .= '>';
        }
        
        if ($label) {
            $html .= '<label for="' . $id . '">' . self::escape($label) . '</label>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Check if current page matches given path
     */
    public static function isCurrentPage($path) {
        $currentPath = $_SERVER['REQUEST_URI'] ?? '';
        return strpos($currentPath, $path) !== false;
    }
    
    /**
     * Generate active class for navigation
     */
    public static function activeClass($path, $class = 'active') {
        return self::isCurrentPage($path) ? $class : '';
    }
}

// Global helper functions
if (!function_exists('e')) {
    function e($string) {
        return ViewHelper::escape($string);
    }
}

if (!function_exists('format_date')) {
    function format_date($date, $format = 'd/m/Y') {
        return ViewHelper::formatDate($date, $format);
    }
}

if (!function_exists('format_datetime')) {
    function format_datetime($datetime, $format = 'd/m/Y H:i') {
        return ViewHelper::formatDateTime($datetime, $format);
    }
}

if (!function_exists('truncate')) {
    function truncate($text, $length = 100, $suffix = '...') {
        return ViewHelper::truncate($text, $length, $suffix);
    }
}

if (!function_exists('alert')) {
    function alert($type, $message, $dismissible = true) {
        return ViewHelper::alert($type, $message, $dismissible);
    }
}
?>
