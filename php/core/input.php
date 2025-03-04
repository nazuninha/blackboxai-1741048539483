<?php
require_once __DIR__ . '/error_handler.php';

class Input {
    /**
     * Get sanitized POST data
     * @param string $key Input key
     * @param mixed $default Default value if key doesn't exist
     * @param array $rules Validation rules
     * @return mixed Sanitized input value
     */
    public static function post($key = null, $default = null, $rules = []) {
        if ($key === null) {
            return self::sanitizeInput($_POST);
        }

        $value = isset($_POST[$key]) ? $_POST[$key] : $default;

        if (!empty($rules)) {
            $validation = ErrorHandler::validateInput([$key => $value], [$key => $rules]);
            if (!$validation['isValid']) {
                return $default;
            }
        }

        return self::sanitizeInput($value);
    }

    /**
     * Get sanitized GET data
     * @param string $key Input key
     * @param mixed $default Default value if key doesn't exist
     * @param array $rules Validation rules
     * @return mixed Sanitized input value
     */
    public static function get($key = null, $default = null, $rules = []) {
        if ($key === null) {
            return self::sanitizeInput($_GET);
        }

        $value = isset($_GET[$key]) ? $_GET[$key] : $default;

        if (!empty($rules)) {
            $validation = ErrorHandler::validateInput([$key => $value], [$key => $rules]);
            if (!$validation['isValid']) {
                return $default;
            }
        }

        return self::sanitizeInput($value);
    }

    /**
     * Get sanitized FILES data
     * @param string $key Input key
     * @param array $rules Validation rules
     * @return array|null Sanitized file data
     */
    public static function file($key, $rules = []) {
        if (!isset($_FILES[$key])) {
            return null;
        }

        $file = $_FILES[$key];

        // Basic file validation
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Apply validation rules
        if (!empty($rules)) {
            if (isset($rules['max_size']) && $file['size'] > $rules['max_size']) {
                return null;
            }

            if (isset($rules['types']) && !in_array($file['type'], $rules['types'])) {
                return null;
            }

            if (isset($rules['extensions'])) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $rules['extensions'])) {
                    return null;
                }
            }
        }

        return [
            'name' => self::sanitizeFilename($file['name']),
            'type' => $file['type'],
            'tmp_name' => $file['tmp_name'],
            'error' => $file['error'],
            'size' => $file['size']
        ];
    }

    /**
     * Sanitize input data
     * @param mixed $input Input data
     * @return mixed Sanitized data
     */
    private static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }

        if (is_string($input)) {
            // Remove any HTML tags
            $input = strip_tags($input);
            
            // Convert special characters to HTML entities
            $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
            
            // Remove any potential SQL injection attempts
            $input = str_replace(['--', ';'], '', $input);
            
            // Trim whitespace
            $input = trim($input);
            
            return $input;
        }

        return $input;
    }

    /**
     * Sanitize filename
     * @param string $filename Original filename
     * @return string Sanitized filename
     */
    private static function sanitizeFilename($filename) {
        // Remove any directory components
        $filename = basename($filename);
        
        // Remove any non-alphanumeric characters except dots and dashes
        $filename = preg_replace('/[^a-zA-Z0-9\-\.]/', '_', $filename);
        
        // Remove multiple consecutive dots
        $filename = preg_replace('/\.{2,}/', '.', $filename);
        
        // Ensure filename doesn't start or end with a dot
        $filename = trim($filename, '.');
        
        // Generate a unique prefix
        $prefix = substr(md5(uniqid(rand(), true)), 0, 8);
        
        return $prefix . '_' . $filename;
    }

    /**
     * Check if request is POST
     * @return bool
     */
    public static function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Check if request is GET
     * @return bool
     */
    public static function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Check if request is AJAX
     * @return bool
     */
    public static function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Get client IP address
     * @return string
     */
    public static function getIpAddress() {
        $ip_keys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER)) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, 
                        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }

        return '0.0.0.0';
    }

    /**
     * Get request headers
     * @return array
     */
    public static function getHeaders() {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = self::sanitizeInput($value);
            }
        }
        return $headers;
    }
}