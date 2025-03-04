<?php
require_once __DIR__ . '/../../../core/error_handler.php';

class FirebaseDatabase {
    private $firebase_url;
    private $firebase_key;
    private static $instance = null;
    private $cache = [];
    private $cache_expiry = [];
    private $cache_duration = 300; // 5 minutes

    private function __construct($config) {
        $this->firebase_url = rtrim($config['firebase_url'], '/');
        $this->firebase_key = $config['firebase_key'];
    }

    public static function getInstance($config) {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    private function makeRequest($endpoint, $method = 'GET', $data = null) {
        $url = $this->firebase_url . '/' . ltrim($endpoint, '/') . '.json';
        $url .= '?auth=' . urlencode($this->firebase_key);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if ($data !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            }
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) {
            throw new Exception("Curl error: " . $curl_error);
        }

        if ($http_code >= 400) {
            throw new Exception("HTTP error {$http_code}: " . $response);
        }

        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response");
        }

        return $result;
    }

    public function query($path, $conditions = []) {
        try {
            // Check cache first
            $cache_key = $path . '_' . md5(json_encode($conditions));
            if ($this->isCacheValid($cache_key)) {
                return $this->cache[$cache_key];
            }

            $data = $this->makeRequest($path);
            
            if (!empty($conditions) && is_array($data)) {
                $data = array_filter($data, function($item) use ($conditions) {
                    foreach ($conditions as $key => $value) {
                        if (!isset($item[$key]) || $item[$key] !== $value) {
                            return false;
                        }
                    }
                    return true;
                });
            }

            // Cache the result
            $this->setCache($cache_key, $data);

            return $data;
        } catch (Exception $e) {
            return ErrorHandler::handleError($e, ErrorHandler::ERROR_QUERY);
        }
    }

    public function insert($path, $data) {
        try {
            // Validate data
            $validation = ErrorHandler::validateInput($data, $this->getValidationRules($path));
            if (!$validation['isValid']) {
                throw new Exception('Invalid data: ' . json_encode($validation['errors']));
            }

            // Sanitize data
            $data = ErrorHandler::sanitizeInput($data);

            // Add metadata
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');

            $result = $this->makeRequest($path, 'POST', $data);
            $this->invalidateCache($path);

            return $result;
        } catch (Exception $e) {
            return ErrorHandler::handleError($e, ErrorHandler::ERROR_QUERY);
        }
    }

    public function update($path, $data) {
        try {
            // Validate data
            $validation = ErrorHandler::validateInput($data, $this->getValidationRules($path));
            if (!$validation['isValid']) {
                throw new Exception('Invalid data: ' . json_encode($validation['errors']));
            }

            // Sanitize data
            $data = ErrorHandler::sanitizeInput($data);

            // Add metadata
            $data['updated_at'] = date('Y-m-d H:i:s');

            $result = $this->makeRequest($path, 'PATCH', $data);
            $this->invalidateCache($path);

            return $result;
        } catch (Exception $e) {
            return ErrorHandler::handleError($e, ErrorHandler::ERROR_QUERY);
        }
    }

    private function getValidationRules($path) {
        // Extract the base path (e.g., 'users' from 'users/123')
        $base_path = explode('/', $path)[0];

        switch ($base_path) {
            case 'users':
                return [
                    'name' => ['required' => true, 'type' => 'string', 'min' => 2, 'max' => 255],
                    'email' => ['required' => true, 'type' => 'email'],
                    'password' => ['required' => true, 'type' => 'string', 'min' => 8]
                ];
            case 'store_settings':
                return [
                    'setting_key' => ['required' => true, 'type' => 'string'],
                    'setting_value' => ['required' => true]
                ];
            default:
                return [];
        }
    }

    private function isCacheValid($key) {
        return isset($this->cache[$key]) && 
               isset($this->cache_expiry[$key]) && 
               time() < $this->cache_expiry[$key];
    }

    private function setCache($key, $data) {
        $this->cache[$key] = $data;
        $this->cache_expiry[$key] = time() + $this->cache_duration;
    }

    private function invalidateCache($path) {
        foreach ($this->cache as $key => $value) {
            if (strpos($key, $path) === 0) {
                unset($this->cache[$key]);
                unset($this->cache_expiry[$key]);
            }
        }
    }
}

function test_database_connection($config) {
    try {
        // Validate required configuration
        $validation = ErrorHandler::validateInput($config, [
            'firebase_url' => ['required' => true, 'type' => 'string'],
            'firebase_key' => ['required' => true, 'type' => 'string']
        ]);

        if (!$validation['isValid']) {
            throw new Exception('Configuração inválida: ' . json_encode($validation['errors']));
        }

        $db = FirebaseDatabase::getInstance($config);
        $result = $db->query('test');
        return ErrorHandler::formatSuccess($db);
    } catch (Exception $e) {
        return ErrorHandler::handleError($e, ErrorHandler::ERROR_CONNECTION);
    }
}

function get_connection($config) {
    try {
        return FirebaseDatabase::getInstance($config);
    } catch (Exception $e) {
        return ErrorHandler::handleError($e, ErrorHandler::ERROR_CONNECTION);
    }
}