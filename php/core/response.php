<?php
require_once __DIR__ . '/error_handler.php';

class Response {
    /**
     * Send JSON response
     * @param mixed $data Response data
     * @param int $status HTTP status code
     * @param array $headers Additional headers
     */
    public static function json($data, $status = 200, $headers = []) {
        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Set security headers
        self::setSecurityHeaders();

        // Set response headers
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($status);

        // Set additional headers
        foreach ($headers as $name => $value) {
            header("$name: $value");
        }

        // Format the response
        $response = self::formatResponse($data, $status);

        // Send response
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send error response
     * @param string $message Error message
     * @param int $status HTTP status code
     * @param array $details Additional error details
     */
    public static function error($message, $status = 400, $details = []) {
        $response = [
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $status
            ]
        ];

        if (!empty($details)) {
            $response['error']['details'] = $details;
        }

        self::json($response, $status);
    }

    /**
     * Redirect to another URL
     * @param string $url Destination URL
     * @param int $status HTTP status code
     */
    public static function redirect($url, $status = 302) {
        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Set security headers
        self::setSecurityHeaders();

        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL) && !str_starts_with($url, '/')) {
            throw new Exception('Invalid redirect URL');
        }

        header('Location: ' . $url, true, $status);
        exit;
    }

    /**
     * Send file download response
     * @param string $filepath File path
     * @param string $filename Download filename
     * @param string $mimetype MIME type
     */
    public static function download($filepath, $filename = null, $mimetype = null) {
        if (!file_exists($filepath)) {
            self::error('File not found', 404);
        }

        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Set security headers
        self::setSecurityHeaders();

        // Get file information
        $filename = $filename ?: basename($filepath);
        $mimetype = $mimetype ?: mime_content_type($filepath);
        $filesize = filesize($filepath);

        // Set headers
        header('Content-Type: ' . $mimetype);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $filesize);
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Send file
        readfile($filepath);
        exit;
    }

    /**
     * Format response data
     * @param mixed $data Response data
     * @param int $status HTTP status code
     * @return array Formatted response
     */
    private static function formatResponse($data, $status) {
        if (is_array($data) && isset($data['success'])) {
            return $data;
        }

        return [
            'success' => $status >= 200 && $status < 300,
            'data' => $data
        ];
    }

    /**
     * Set security headers
     */
    private static function setSecurityHeaders() {
        // Prevent clickjacking
        header('X-Frame-Options: DENY');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Enable XSS protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Set content security policy
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:; font-src 'self' https:; connect-src 'self' https:;");
        
        // Set referrer policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Set permissions policy
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        
        // Prevent caching of sensitive data
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
    }

    /**
     * Set response header
     * @param string $name Header name
     * @param string $value Header value
     */
    public static function setHeader($name, $value) {
        header("$name: $value");
    }

    /**
     * Set response status code
     * @param int $code HTTP status code
     */
    public static function setStatus($code) {
        http_response_code($code);
    }
}